<?php
// reception/offline_payments.php
require_once __DIR__ . '/../includes/auth.php';
require_role('receptionist');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Fetch unpaid bills for offline payment processing
$stmt = $pdo->query("
  SELECT b.*, u.name AS patient_name, u.email AS patient_email, u.phone AS patient_phone,
         d.name AS doctor_name, COALESCE(doc.specialty, '') AS doctor_specialty
  FROM bills b
  JOIN users u ON b.patient_id = u.id
  JOIN users d ON b.doctor_id = d.id
  LEFT JOIN doctors doc ON d.id = doc.id
  WHERE b.status = 'unpaid'
  ORDER BY b.created_at ASC
");
$bills = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total amounts for stats
$totalUnpaid = array_sum(array_column($bills, 'total_amount'));
$billCount = count($bills);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Offline Payments - Reception</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    .glass-effect {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
  </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 min-h-screen">
  <?php include __DIR__ . '/../includes/sidebar_reception.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300">
    <!-- Header Section -->
    <div class="mb-8">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
          <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
            Offline Payment Processing
          </h1>
          <p class="text-gray-600 mt-1">Process cash payments for unpaid bills</p>
        </div>
        <div class="flex items-center gap-3">
          <a href="offline_bills.php" class="inline-flex items-center gap-2 px-4 py-2.5 glass-effect text-gray-700 rounded-xl font-medium hover:bg-white/50 transition-all duration-300">
            <i data-lucide="file-text" class="w-4 h-4"></i>
            All Bills
          </a>
        </div>
      </div>
    </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-red-100 text-red-600">
              <i data-lucide="credit-card" class="w-6 h-6"></i>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Pending Bills</p>
              <p class="text-2xl font-semibold text-gray-900"><?= $billCount ?></p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
              <i data-lucide="dollar-sign" class="w-6 h-6"></i>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Total Amount</p>
              <p class="text-2xl font-semibold text-gray-900"><?= money($totalUnpaid) ?></p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
              <i data-lucide="clock" class="w-6 h-6"></i>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Today's Date</p>
              <p class="text-2xl font-semibold text-gray-900"><?= date('d M Y') ?></p>
            </div>
          </div>
        </div>
      </div>

      <?php if (!$bills): ?>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
          <i data-lucide="check-circle" class="w-16 h-16 text-green-300 mx-auto mb-4"></i>
          <h3 class="text-lg font-medium text-gray-900 mb-2">All Bills Paid</h3>
          <p class="text-gray-500 mb-4">There are no pending bills for offline payment processing.</p>
          <a href="offline_bills.php" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            <i data-lucide="file-text" class="w-4 h-4"></i>
            View All Bills
          </a>
        </div>
      <?php else: ?>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
              <i data-lucide="list" class="w-5 h-5 text-indigo-600"></i>
              Pending Bills (<?= $billCount ?>)
            </h3>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full">
              <thead class="bg-gray-50 border-b">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bill Details</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doctor</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($bills as $b): ?>
                  <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">#<?= e($b['id']) ?></div>
                      <div class="text-sm text-gray-500">Bill ID</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900"><?= e($b['patient_name']) ?></div>
                      <div class="text-sm text-gray-500"><?= e($b['patient_email']) ?></div>
                      <?php if ($b['patient_phone']): ?>
                        <div class="text-sm text-gray-500"><?= e($b['patient_phone']) ?></div>
                      <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900"><?= e($b['doctor_name']) ?></div>
                      <?php if ($b['doctor_specialty']): ?>
                        <div class="text-sm text-gray-500"><?= e($b['doctor_specialty']) ?></div>
                      <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-bold text-gray-900"><?= money($b['total_amount']) ?></div>
                      <div class="text-xs text-gray-500">Total Amount</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900"><?= e(format_date($b['created_at'])) ?></div>
                      <div class="text-sm text-gray-500"><?= e(format_time($b['created_at'])) ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <div class="flex items-center gap-2">
                        <button onclick="showBillDetails(<?= e($b['id']) ?>)" 
                                class="inline-flex items-center gap-1 px-3 py-1 bg-gray-100 text-gray-700 rounded text-sm hover:bg-gray-200 transition-colors">
                          <i data-lucide="eye" class="w-4 h-4"></i>
                          View
                        </button>
                        <form method="post" action="mark_offline_paid.php" class="inline" onsubmit="return confirmPayment(<?= e($b['id']) ?>, <?= e($b['total_amount']) ?>)">
                          <input type="hidden" name="csrf" value="<?= csrf() ?>">
                          <input type="hidden" name="bill_id" value="<?= e($b['id']) ?>">
                          <div class="flex items-center gap-2">
                            <select name="method" class="border border-gray-300 rounded px-2 py-1 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                              <option value="cash">Cash</option>
                              <option value="upi">UPI</option>
                              <option value="bank_transfer">Bank Transfer</option>
                              <option value="card">Card</option>
                            </select>
                            <button type="submit" 
                                    class="inline-flex items-center gap-1 px-3 py-1 bg-green-600 text-white rounded text-sm hover:bg-green-700 transition-colors">
                              <i data-lucide="check-circle" class="w-4 h-4"></i>
                              Mark Paid
                            </button>
                          </div>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <!-- Bill Details Modal -->
  <div id="billModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[80vh] overflow-hidden">
      <div class="flex items-center justify-between p-6 border-b">
        <h3 class="text-lg font-semibold text-gray-900">Bill Details</h3>
        <button onclick="closeBillModal()" class="text-gray-400 hover:text-gray-600">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>
      <div id="billContent" class="p-6 overflow-y-auto">
        <!-- Content will be loaded here -->
      </div>
    </div>
  </div>

  <script>
    lucide.createIcons();
    
    // Show bill details modal
    async function showBillDetails(billId) {
      const modal = document.getElementById('billModal');
      const content = document.getElementById('billContent');
      
      content.innerHTML = '<div class="flex items-center justify-center py-8"><i data-lucide="loader" class="w-6 h-6 animate-spin"></i></div>';
      modal.classList.remove('hidden');
      
      try {
        const response = await fetch(`get_bill_details.php?id=${billId}`);
        const data = await response.json();
        
        if (data.ok) {
          content.innerHTML = data.html;
          lucide.createIcons();
        } else {
          content.innerHTML = '<div class="text-red-600 text-center py-4">Error loading bill details</div>';
        }
      } catch (error) {
        content.innerHTML = '<div class="text-red-600 text-center py-4">Error loading bill details</div>';
      }
    }
    
    // Close bill modal
    function closeBillModal() {
      document.getElementById('billModal').classList.add('hidden');
    }
    
    // Confirm payment before submission
    function confirmPayment(billId, amount) {
      const method = document.querySelector(`form[action="mark_offline_paid.php"] input[name="bill_id"][value="${billId}"]`).closest('form').querySelector('select[name="method"]').value;
      
      return confirm(`Confirm payment of ${amount} via ${method.toUpperCase()}?\n\nThis action cannot be undone.`);
    }
    
    // Close modal on outside click
    document.getElementById('billModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeBillModal();
      }
    });
    
    // Auto-refresh page after successful payment (if redirected back)
    if (window.location.search.includes('paid=1')) {
      setTimeout(() => {
        window.location.href = window.location.pathname;
      }, 2000);
    }
  </script>
</body>
</html>
