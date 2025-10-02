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
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>My Bills - Healsync</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    .glass-effect {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    .bill-card {
      transition: all 0.3s ease;
    }
    .bill-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 min-h-screen">
  <?php include __DIR__ . '/../includes/sidebar_patient.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300">
    <!-- Header Section -->
    <div class="mb-8">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
          <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
            My Bills & Payments
          </h1>
          <p class="text-gray-600 mt-1">Track your medical expenses and payment history</p>
        </div>
        <div class="flex items-center gap-3">
          <?php
          $totalUnpaid = 0;
          $totalPaid = 0;
          foreach ($bills as $bill) {
            if ($bill['status'] === 'unpaid') {
              $totalUnpaid += $bill['total_amount'];
            } else {
              $totalPaid += $bill['total_amount'];
            }
          }
          ?>
          <div class="text-right">
            <p class="text-sm text-gray-600">Outstanding</p>
            <p class="text-xl font-bold text-red-600">₹<?= number_format($totalUnpaid, 2) ?></p>
          </div>
        </div>
      </div>
    </div>

    <!-- Bills List -->
    <div class="glass-effect rounded-2xl border border-white/20 overflow-hidden">
      <div class="p-6 border-b border-gray-200/50">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-500 rounded-xl flex items-center justify-center">
              <i data-lucide="credit-card" class="w-5 h-5 text-white"></i>
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-900">Your Bills</h3>
              <p class="text-gray-600 text-sm">Manage your medical bills and payments</p>
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
          <h3 class="text-lg font-medium text-gray-900 mb-2">No bills yet</h3>
          <p class="text-gray-600 mb-4">Your medical bills will appear here once generated.</p>
        </div>
      <?php else: ?>
        <div class="p-6">
          <div class="space-y-4">
            <?php foreach ($bills as $b): ?>
              <div class="bill-card glass-effect rounded-xl border border-white/20 overflow-hidden">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-6 border-b border-gray-200/50">
                  <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-xl flex items-center justify-center">
                      <i data-lucide="receipt" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                      <h3 class="font-bold text-lg text-gray-900">Bill #<?= e($b['id']) ?></h3>
                      <div class="flex items-center gap-4 text-sm text-gray-600 mt-1">
                        <div class="flex items-center gap-1">
                          <i data-lucide="stethoscope" class="w-4 h-4"></i>
                          Dr. <?= e($b['doctor_name']) ?>
                        </div>
                        <div class="flex items-center gap-1">
                          <i data-lucide="calendar" class="w-4 h-4"></i>
                          <?= e(date('d M Y', strtotime($b['created_at']))) ?>
                        </div>
                      </div>
                    </div>
                  </div>
                  <?php
                    $statusConfig = [
                      'paid' => ['class' => 'bg-green-100 text-green-800', 'icon' => 'check-circle'],
                      'offline_pending' => ['class' => 'bg-yellow-100 text-yellow-800', 'icon' => 'clock'],
                      'unpaid' => ['class' => 'bg-red-100 text-red-800', 'icon' => 'alert-circle']
                    ];
                    $config = $statusConfig[$b['status']] ?? $statusConfig['unpaid'];
                  ?>
                  <div class="mt-4 sm:mt-0">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?= $config['class'] ?>">
                      <i data-lucide="<?= $config['icon'] ?>" class="w-3 h-3 mr-1"></i>
                      <?= e(ucfirst(str_replace('_', ' ', $b['status']))) ?>
                    </span>
                  </div>
                </div>

                <!-- Bill items -->
                <div class="p-6">
                  <?php if (!empty($billItems[$b['id']])): ?>
                    <div class="space-y-3 mb-4">
                      <?php foreach ($billItems[$b['id']] as $it): ?>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-0">
                          <div class="text-gray-700 font-medium"><?= e($it['description']) ?></div>
                          <div class="font-semibold text-gray-900">₹<?= number_format($it['amount'], 2) ?></div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php else: ?>
                    <div class="text-center py-4 text-gray-500">
                      <i data-lucide="file-x" class="w-8 h-8 mx-auto mb-2 text-gray-400"></i>
                      <p>No bill items found</p>
                    </div>
                  <?php endif; ?>

                  <!-- Total -->
                  <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                    <div class="text-lg font-bold text-gray-900">Total Amount</div>
                    <div class="text-2xl font-bold text-indigo-600">₹<?= number_format($b['total_amount'], 2) ?></div>
                  </div>
                </div>

                <!-- Actions -->
                <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-200/50 flex flex-wrap gap-3">
                  <a href="bill_view.php?id=<?= e($b['id']) ?>"
                     class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    <i data-lucide="eye" class="w-4 h-4"></i> View Details
                  </a>

                  <?php if ($b['status'] === 'unpaid'): ?>
                    <a href="pay.php?bill_id=<?= e($b['id']) ?>"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg text-sm font-medium hover:from-indigo-700 hover:to-purple-700 transition-all duration-300 hover:scale-105 shadow-sm">
                      <i data-lucide="credit-card" class="w-4 h-4"></i> Pay Now
                    </a>
                  <?php elseif ($b['status'] === 'offline_pending'): ?>
                    <div class="flex items-center gap-2 text-yellow-700 text-sm">
                      <i data-lucide="clock" class="w-4 h-4"></i>
                      <span>Awaiting payment at reception</span>
                    </div>
                  <?php else: ?>
                    <div class="flex items-center gap-2 text-green-600 text-sm">
                      <i data-lucide="check-circle" class="w-4 h-4"></i>
                      <span>Paid on <?= e(date('d M Y', strtotime($b['paid_at'] ?? $b['updated_at'] ?? $b['created_at']))) ?></span>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <script>lucide.createIcons();</script>
</body>
</html>
