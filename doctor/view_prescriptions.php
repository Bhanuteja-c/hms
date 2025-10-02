<?php
// doctor/view_prescriptions.php
require_once __DIR__ . '/../includes/auth.php';
require_role('doctor');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$did = current_user_id();
$search = trim($_GET['search'] ?? '');

// Fetch prescriptions linked to doctor's appointments
$sql = "
  SELECT p.id, p.created_at, a.date_time, u.name AS patient_name, u.email AS patient_email,
         COUNT(p.id) AS medicines_count
  FROM prescriptions p
  JOIN appointments a ON p.appointment_id = a.id
  JOIN users u ON a.patient_id = u.id
  WHERE a.doctor_id = :did
";
$params = [':did' => $did];

if ($search !== '') {
    $sql .= " AND (u.name LIKE :s OR u.email LIKE :s OR p.medicine LIKE :s)";
    $params[':s'] = "%$search%";
}

$sql .= " GROUP BY p.appointment_id, a.date_time, u.name, u.email
          ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>View Prescriptions - Healsync</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    .glass-effect {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    .prescription-card {
      transition: all 0.3s ease;
    }
    .prescription-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body class="bg-gray-100">
  <?php include __DIR__ . '/../includes/sidebar_doctor.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold flex items-center gap-2">
        <i data-lucide="pill" class="w-6 h-6 text-indigo-600"></i>
        My Prescriptions
      </h2>
      <a href="add_prescription.php" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Add Prescription
      </a>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
      <form method="get" class="flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
          <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
          <div class="relative">
            <input type="text" name="search" value="<?= e($search) ?>" 
                   placeholder="Search by patient name, email, or medicine"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 pl-10 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2"></i>
          </div>
        </div>
        <div class="flex items-end gap-2">
          <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 flex items-center gap-2">
            <i data-lucide="search" class="w-4 h-4"></i>
            Search
          </button>
          <?php if ($search): ?>
            <a href="view_prescriptions.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 flex items-center gap-2">
              <i data-lucide="x" class="w-4 h-4"></i>
              Clear
            </a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <?php if ($prescriptions): ?>
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead class="bg-gray-50 border-b">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Appointment</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Medicines</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <?php foreach ($prescriptions as $p): ?>
                <tr class="hover:bg-gray-50 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900"><?= e(format_date($p['created_at'])) ?></div>
                    <div class="text-sm text-gray-500"><?= e(format_time($p['created_at'])) ?></div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900"><?= e($p['patient_name']) ?></div>
                    <div class="text-sm text-gray-500"><?= e($p['patient_email']) ?></div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900"><?= e(format_datetime($p['date_time'])) ?></div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                      <?= (int)$p['medicines_count'] ?> medicine<?= $p['medicines_count'] > 1 ? 's' : '' ?>
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex items-center gap-2">
                      <a href="prescription_pdf.php?id=<?= e($p['id']) ?>" target="_blank"
                         class="inline-flex items-center gap-1 px-3 py-1 bg-indigo-600 text-white rounded text-sm hover:bg-indigo-700 transition-colors">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        PDF
                      </a>
                      <button onclick="showPrescriptionDetails(<?= e($p['id']) ?>)" 
                              class="inline-flex items-center gap-1 px-3 py-1 bg-gray-100 text-gray-700 rounded text-sm hover:bg-gray-200 transition-colors">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                        View
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php else: ?>
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
        <i data-lucide="pill" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Prescriptions Found</h3>
        <p class="text-gray-500 mb-4">
          <?php if ($search): ?>
            No prescriptions match your search criteria.
          <?php else: ?>
            You haven't issued any prescriptions yet.
          <?php endif; ?>
        </p>
        <a href="add_prescription.php" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
          <i data-lucide="plus" class="w-4 h-4"></i>
          Add First Prescription
        </a>
      </div>
    <?php endif; ?>
  </main>

  <!-- Prescription Details Modal -->
  <div id="prescriptionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[80vh] overflow-hidden">
      <div class="flex items-center justify-between p-6 border-b">
        <h3 class="text-lg font-semibold text-gray-900">Prescription Details</h3>
        <button onclick="closePrescriptionModal()" class="text-gray-400 hover:text-gray-600">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>
      <div id="prescriptionContent" class="p-6 overflow-y-auto">
        <!-- Content will be loaded here -->
      </div>
    </div>
  </div>

  <script>
    lucide.createIcons();
    
    async function showPrescriptionDetails(prescriptionId) {
      const modal = document.getElementById('prescriptionModal');
      const content = document.getElementById('prescriptionContent');
      
      content.innerHTML = '<div class="flex items-center justify-center py-8"><i data-lucide="loader" class="w-6 h-6 animate-spin"></i></div>';
      modal.classList.remove('hidden');
      
      try {
        const response = await fetch(`get_prescription_details_doctor.php?id=${prescriptionId}`);
        const data = await response.json();
        
        if (data.ok) {
          content.innerHTML = data.html;
          lucide.createIcons();
        } else {
          content.innerHTML = '<div class="text-red-600 text-center py-4">Error loading prescription details</div>';
        }
      } catch (error) {
        content.innerHTML = '<div class="text-red-600 text-center py-4">Error loading prescription details</div>';
      }
    }
    
    function closePrescriptionModal() {
      document.getElementById('prescriptionModal').classList.add('hidden');
    }
    
    // Close modal on outside click
    document.getElementById('prescriptionModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closePrescriptionModal();
      }
    });
  </script>
</body>
</html>
