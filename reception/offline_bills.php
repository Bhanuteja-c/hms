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
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Offline Bills - Reception</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    .glass-effect {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    @keyframes fade-in { 
      from { opacity:0; transform:translateY(-6px);} 
      to {opacity:1; transform:none;} 
    }
    .animate-fade-in { 
      animation: fade-in .28s ease-out; 
    }
  </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 min-h-screen">
  <?php include __DIR__ . '/../includes/sidebar_reception.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <!-- Toast container -->
  <div id="toast-container" class="fixed top-5 right-5 space-y-3 z-50"></div>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300">
    <!-- Header Section -->
    <div class="mb-8">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
            Offline Bills Management
          </h1>
          <p class="text-gray-600 mt-1">Manage and process offline bill payments</p>
        </div>
        <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center">
          <i data-lucide="credit-card" class="w-6 h-6 text-white"></i>
        </div>
      </div>
    </div>

    <?php if ($message): ?>
      <div class="mb-6"><?= $message ?></div>
    <?php endif; ?>

    <!-- Bills List -->
    <div class="glass-effect rounded-2xl border border-white/20 overflow-hidden">
      <div class="p-6 border-b border-gray-200/50">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center">
              <i data-lucide="receipt" class="w-5 h-5 text-white"></i>
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-900">All Bills</h3>
              <p class="text-gray-600 text-sm">Manage patient bills and payments</p>
            </div>
          </div>
          <div class="text-right">
            <p class="text-2xl font-bold text-gray-900"><?= count($bills) ?></p>
            <p class="text-gray-600 text-sm">Total bills</p>
          </div>
        </div>
      </div>

      <?php if (!$bills): ?>
        <div class="p-12 text-center">
          <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i data-lucide="receipt" class="w-8 h-8 text-gray-400"></i>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No bills found</h3>
          <p class="text-gray-600">No bills have been generated yet.</p>
        </div>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Bill ID</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Patient</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Doctor</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Amount</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <?php foreach ($bills as $b): ?>
                <tr class="hover:bg-gray-50/50 transition-colors duration-200">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="w-2 h-2 bg-indigo-500 rounded-full mr-3"></div>
                      <span class="text-sm font-medium text-gray-900">#<?= e($b['id']) ?></span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-full flex items-center justify-center mr-3">
                        <span class="text-white text-xs font-semibold"><?= e(strtoupper(substr($b['patient_name'], 0, 1))) ?></span>
                      </div>
                      <span class="text-sm font-medium text-gray-900"><?= e($b['patient_name']) ?></span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-emerald-500 rounded-full flex items-center justify-center mr-3">
                        <i data-lucide="stethoscope" class="w-4 h-4 text-white"></i>
                      </div>
                      <span class="text-sm font-medium text-gray-900"><?= e($b['doctor_name']) ?></span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-bold text-gray-900">₹<?= number_format((float)$b['total_amount'], 2) ?></div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <?php if ($b['status']==='paid'): ?>
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i>
                        Paid
                      </span>
                    <?php else: ?>
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        <i data-lucide="alert-circle" class="w-3 h-3 mr-1"></i>
                        Unpaid
                      </span>
                    <?php endif; ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-2">
                      <a href="offline_bill_view.php?id=<?= e($b['id']) ?>"
                         class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-xs font-medium rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 hover:scale-105 shadow-sm">
                        <i data-lucide="eye" class="w-3 h-3 mr-1"></i>
                        View
                      </a>
                      <?php if ($b['status']!=='paid'): ?>
                        <form method="post" class="inline"
                              onsubmit="return confirm('Confirm cash received for Bill #<?= e($b['id']) ?>?')">
                          <input type="hidden" name="csrf" value="<?= csrf() ?>">
                          <input type="hidden" name="bill_id" value="<?= e($b['id']) ?>">
                          <button type="submit"
                                  class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white text-xs font-medium rounded-lg hover:from-green-700 hover:to-emerald-700 transition-all duration-200 hover:scale-105 shadow-sm">
                            <i data-lucide="banknote" class="w-3 h-3 mr-1"></i>
                            Mark Paid
                          </button>
                        </form>
                      <?php else: ?>
                        <a href="receipt_pdf.php?id=<?= e($b['id']) ?>" target="_blank"
                           class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-purple-600 to-indigo-600 text-white text-xs font-medium rounded-lg hover:from-purple-700 hover:to-indigo-700 transition-all duration-200 hover:scale-105 shadow-sm">
                          <i data-lucide="printer" class="w-3 h-3 mr-1"></i>
                          Receipt
                        </a>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <script>lucide.createIcons();</script>
</body>
</html>
