<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('patient');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$pid = current_user_id();

// Fetch bills
$stmt = $pdo->prepare("SELECT * FROM bills WHERE patient_id=:pid ORDER BY id DESC");
$stmt->execute([':pid' => $pid]);
$bills = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300">
    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
      <i data-lucide="credit-card" class="w-6 h-6 text-indigo-600"></i>
      My Bills & Payments
    </h2>

    <?php if ($bills): ?>
      <div class="overflow-x-auto">
        <table class="w-full bg-white rounded shadow overflow-hidden">
          <thead class="bg-gray-100">
            <tr>
              <th class="text-left p-3">Bill ID</th>
              <th class="text-left p-3">Total Amount</th>
              <th class="text-left p-3">Status</th>
              <th class="text-left p-3">Paid At</th>
              <th class="text-left p-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($bills as $b): ?>
              <tr class="border-t hover:bg-gray-50">
                <td class="p-3 font-medium">#<?=e($b['id'])?></td>
                <td class="p-3 text-indigo-600 font-semibold">$<?=number_format($b['total_amount'], 2)?></td>
                <td class="p-3">
                  <?php if ($b['status']==='paid'): ?>
                    <span class="px-2 py-1 text-xs bg-green-100 text-green-600 rounded-full flex items-center gap-1 w-fit">
                      <i data-lucide="check-circle" class="w-3 h-3"></i> Paid
                    </span>
                  <?php else: ?>
                    <span class="px-2 py-1 text-xs bg-red-100 text-red-600 rounded-full flex items-center gap-1 w-fit">
                      <i data-lucide="x-circle" class="w-3 h-3"></i> Unpaid
                    </span>
                  <?php endif; ?>
                </td>
                <td class="p-3"><?= $b['paid_at'] ? date('d M Y', strtotime($b['paid_at'])) : '-' ?></td>
                <td class="p-3 flex gap-2">
                  <a href="bill_view.php?id=<?=e($b['id'])?>" target="_blank" 
                     class="px-3 py-1 bg-indigo-600 text-white rounded text-sm flex items-center gap-1 hover:bg-indigo-700 transition">
                    <i data-lucide="file-text" class="w-4 h-4"></i> View
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-gray-500">No bills found.</p>
    <?php endif; ?>
  </main>

  <script>
    lucide.createIcons();
  </script>
</body>
</html>
