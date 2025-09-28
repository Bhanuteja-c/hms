<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('patient');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$pid = current_user_id();

// Fetch all bills for this patient
$stmt = $pdo->prepare("
    SELECT b.*, u.name AS doctor_name
    FROM bills b
    JOIN users u ON b.doctor_id = u.id
    WHERE b.patient_id = :pid
    ORDER BY b.created_at DESC
");
$stmt->execute([':pid' => $pid]);
$bills = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch bill items
$billItems = [];
if ($bills) {
    $ids = array_column($bills, 'id');
    $in  = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM bill_items WHERE bill_id IN ($in)");
    $stmt->execute($ids);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        $billItems[$r['bill_id']][] = $r;
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>My Bills - Healsync</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-100">
  <?php include __DIR__ . '/../includes/sidebar_patient.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 max-w-4xl mx-auto">
    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
      <i data-lucide="credit-card" class="w-6 h-6 text-indigo-600"></i>
      My Bills
    </h2>

    <?php if (!$bills): ?>
      <p class="text-gray-500">No bills found.</p>
    <?php else: ?>
      <div class="space-y-6">
        <?php foreach ($bills as $b): ?>
          <div class="bg-white shadow rounded-lg overflow-hidden">
            <!-- Header -->
            <div class="flex justify-between items-center p-4 border-b">
              <div>
                <h3 class="font-semibold text-lg">Bill #<?= e($b['id']) ?></h3>
                <p class="text-sm text-gray-500">
                  Doctor: <?= e($b['doctor_name']) ?> | 
                  Date: <?= date('d M Y H:i', strtotime($b['created_at'])) ?>
                </p>
              </div>
              <?php
                $statusClass = match($b['status']) {
                  'paid' => 'bg-green-100 text-green-700',
                  'offline_pending' => 'bg-yellow-100 text-yellow-700',
                  default => 'bg-red-100 text-red-700'
                };
              ?>
              <span class="px-3 py-1 rounded-full text-sm <?= $statusClass ?>">
                <?= ucfirst(str_replace('_',' ', $b['status'])) ?>
              </span>
            </div>

            <!-- Bill items -->
            <div class="divide-y">
              <?php if (!empty($billItems[$b['id']])): ?>
                <?php foreach ($billItems[$b['id']] as $it): ?>
                  <div class="flex justify-between p-4">
                    <div class="text-gray-700"><?= e($it['description']) ?></div>
                    <div class="font-medium">₹<?= number_format($it['amount'], 2) ?></div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="p-4 text-sm text-gray-500">No bill items found.</div>
              <?php endif; ?>
            </div>

            <!-- Total -->
            <div class="flex justify-between p-4 border-t font-bold">
              <div>Total</div>
              <div>₹<?= number_format($b['total_amount'], 2) ?></div>
            </div>

            <!-- Actions -->
            <div class="p-4 border-t flex justify-between items-center">
              <a href="bill_view.php?id=<?= e($b['id']) ?>"
                 class="px-3 py-1 bg-gray-200 rounded text-sm hover:bg-gray-300 flex items-center gap-1">
                <i data-lucide="file-text" class="w-4 h-4"></i> View
              </a>

              <?php if ($b['status'] === 'unpaid'): ?>
                <a href="pay.php?bill_id=<?= e($b['id']) ?>"
                   class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 flex items-center gap-2">
                  <i data-lucide="credit-card" class="w-4 h-4"></i> Pay Now
                </a>
              <?php elseif ($b['status'] === 'offline_pending'): ?>
                <span class="text-yellow-700 text-sm">
                  Awaiting payment at hospital reception
                </span>
              <?php else: ?>
                <span class="text-green-600 text-sm">
                  Paid on <?= date('d M Y', strtotime($b['paid_at'] ?? $b['updated_at'] ?? $b['created_at'])) ?>
                </span>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>

  <script>lucide.createIcons();</script>
</body>
</html>
