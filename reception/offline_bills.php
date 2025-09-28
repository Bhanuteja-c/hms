<?php
// reception/offline_bills.php
require_once __DIR__ . '/../includes/auth.php';
require_role('receptionist'); // ✅ fixed role name
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$message = "";

// Handle mark paid (cash)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $message = "<div class='bg-red-50 border border-red-200 text-red-600 px-4 py-2 rounded mb-4'>
                      Invalid CSRF token.
                    </div>";
    } else {
        $billId = intval($_POST['bill_id'] ?? 0);

        $stmt = $pdo->prepare("SELECT * FROM bills WHERE id=:id AND status='unpaid'");
        $stmt->execute([':id' => $billId]);
        $bill = $stmt->fetch();

        if ($bill) {
            try {
                $pdo->beginTransaction();

                // Mark bill paid
                $pdo->prepare("UPDATE bills SET status='paid', paid_at=NOW() WHERE id=:id")
                    ->execute([':id' => $billId]);

                // Insert payment record
                $pdo->prepare("INSERT INTO payments (bill_id, amount, method, transaction_id, created_at)
                               VALUES (:bid, :amt, 'cash', :tx, NOW())")
                    ->execute([
                        ':bid' => $billId,
                        ':amt' => $bill['total_amount'],
                        ':tx'  => 'CASH-' . strtoupper(bin2hex(random_bytes(4)))
                    ]);

                // Audit log
                audit_log($pdo, current_user_id(), 'bill_paid_cash', json_encode(['bill_id' => $billId]));

                $pdo->commit();

                $message = "<div class='bg-green-50 border border-green-200 text-green-700 px-4 py-2 rounded mb-4'>
                              Bill #".e($billId)." marked as <strong>paid (Cash)</strong>.
                            </div>";
            } catch (Exception $e) {
                $pdo->rollBack();
                $message = "<div class='bg-red-50 border border-red-200 text-red-600 px-4 py-2 rounded mb-4'>
                              Error: ".e($e->getMessage())."
                            </div>";
            }
        } else {
            $message = "<div class='bg-red-50 border border-red-200 text-red-600 px-4 py-2 rounded mb-4'>
                          Invalid or already paid bill.
                        </div>";
        }
    }
}

// Fetch all bills
$stmt = $pdo->query("
    SELECT b.*, u.name AS patient_name, d.name AS doctor_name
    FROM bills b
    JOIN users u ON b.patient_id = u.id
    JOIN users d ON b.doctor_id = d.id
    ORDER BY b.created_at DESC
");
$bills = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Offline Bills - Reception</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50">
  <?php include __DIR__ . '/../includes/sidebar_reception.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300">
    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
      <i data-lucide="wallet" class="w-6 h-6 text-indigo-600"></i>
      Offline Bill Management
    </h2>

    <?= $message ?>

    <?php if (!$bills): ?>
      <p class="text-gray-500">No bills found.</p>
    <?php else: ?>
      <div class="overflow-x-auto bg-white rounded shadow">
        <table class="min-w-full">
          <thead class="bg-gray-100 text-sm">
            <tr>
              <th class="px-4 py-2 text-left">Bill ID</th>
              <th class="px-4 py-2">Patient</th>
              <th class="px-4 py-2">Doctor</th>
              <th class="px-4 py-2">Total</th>
              <th class="px-4 py-2">Status</th>
              <th class="px-4 py-2">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($bills as $b): ?>
              <tr class="border-t hover:bg-gray-50">
                <td class="px-4 py-2 font-medium">#<?= e($b['id']) ?></td>
                <td class="px-4 py-2"><?= e($b['patient_name']) ?></td>
                <td class="px-4 py-2"><?= e($b['doctor_name']) ?></td>
                <td class="px-4 py-2">₹<?= number_format((float)$b['total_amount'], 2) ?></td>
                <td class="px-4 py-2">
                  <?php if ($b['status']==='paid'): ?>
                    <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded">Paid</span>
                  <?php else: ?>
                    <span class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded">Unpaid</span>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-2 flex gap-2">
                  <?php if ($b['status']!=='paid'): ?>
                    <a href="offline_bill_view.php?id=<?= e($b['id']) ?>"
                       class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 flex items-center gap-1">
                      <i data-lucide="file-text" class="w-4 h-4"></i> View
                    </a>
                    <form method="post" class="inline"
                          onsubmit="return confirm('Confirm cash received for Bill #<?= e($b['id']) ?>?')">
                      <input type="hidden" name="csrf" value="<?= csrf() ?>">
                      <input type="hidden" name="bill_id" value="<?= e($b['id']) ?>">
                      <button class="px-3 py-1 bg-green-600 text-white rounded text-sm hover:bg-green-700 flex items-center gap-1">
                        <i data-lucide="check-circle" class="w-4 h-4"></i> Mark Paid
                      </button>
                    </form>
                  <?php else: ?>
                    <a href="receipt_pdf.php?id=<?= e($b['id']) ?>" target="_blank"
                       class="px-3 py-1 bg-indigo-600 text-white rounded text-sm hover:bg-indigo-700 flex items-center gap-1">
                      <i data-lucide="printer" class="w-4 h-4"></i> Print Receipt
                    </a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </main>

  <script>lucide.createIcons();</script>
</body>
</html>
