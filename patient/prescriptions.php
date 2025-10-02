<?php
// patient/prescriptions.php
require_once __DIR__ . '/../includes/auth.php';
require_role('patient');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$pid = current_user_id();

// Fetch prescriptions with doctor + appointment info, grouped by appointment
$stmt = $pdo->prepare("
  SELECT pr.*, a.date_time, a.id as appointment_id, d.name AS doctor_name,
         COUNT(pr.id) as medicine_count
  FROM prescriptions pr
  JOIN appointments a ON pr.appointment_id = a.id
  JOIN users d ON a.doctor_id = d.id
  WHERE a.patient_id = :pid
  GROUP BY a.id, pr.appointment_id
  ORDER BY a.date_time DESC
");
$stmt->execute([':pid' => $pid]);
$prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>My Prescriptions - Healsync</title>
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
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 min-h-screen">
  <?php include __DIR__ . '/../includes/sidebar_patient.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300">
    <!-- Header Section -->
    <div class="mb-8">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
          <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
            My Prescriptions
          </h1>
          <p class="text-gray-600 mt-1">View and manage your medical prescriptions</p>
        </div>
        <div class="flex items-center gap-3">
          <?php if ($prescriptions): ?>
            <a href="prescriptions_pdf.php" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-medium shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105">
              <i data-lucide="download" class="w-4 h-4"></i>
              Download All (PDF)
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Prescriptions List -->
    <div class="glass-effect rounded-2xl border border-white/20 overflow-hidden">
      <div class="p-6 border-b border-gray-200/50">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl flex items-center justify-center">
              <i data-lucide="pill" class="w-5 h-5 text-white"></i>
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-900">Your Prescriptions</h3>
              <p class="text-gray-600 text-sm">Medical prescriptions from your appointments</p>
            </div>
          </div>
          <div class="text-right">
            <p class="text-2xl font-bold text-gray-900"><?= count($prescriptions) ?></p>
            <p class="text-gray-600 text-sm">Total prescriptions</p>
          </div>
        </div>
      </div>

      <?php if (!$prescriptions): ?>
        <div class="p-12 text-center">
          <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i data-lucide="pill" class="w-8 h-8 text-gray-400"></i>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No prescriptions yet</h3>
          <p class="text-gray-600 mb-4">Your medical prescriptions will appear here after your appointments.</p>
          <a href="book_appointment.php" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-medium hover:from-indigo-700 hover:to-purple-700 transition-all duration-300">
            <i data-lucide="calendar-plus" class="w-4 h-4"></i>
            Book Your First Appointment
          </a>
        </div>
      <?php else: ?>
        <div class="p-6">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date & Time</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Doctor</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Medicines</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <?php foreach ($prescriptions as $p): ?>
                  <tr class="hover:bg-gray-50/50 transition-colors duration-200">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="w-2 h-2 bg-purple-500 rounded-full mr-3"></div>
                        <div>
                          <div class="text-sm font-medium text-gray-900"><?= e(format_date($p['date_time'])) ?></div>
                          <div class="text-sm text-gray-500"><?= e(format_time($p['date_time'])) ?></div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-emerald-500 rounded-full flex items-center justify-center mr-3">
                          <i data-lucide="stethoscope" class="w-4 h-4 text-white"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-900"><?= e($p['doctor_name']) ?></span>
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gradient-to-r from-purple-100 to-pink-100 text-purple-800">
                        <i data-lucide="pill" class="w-4 h-4 mr-1"></i>
                        <?= e($p['medicine_count']) ?> medicine<?= $p['medicine_count'] > 1 ? 's' : '' ?>
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                      <div class="flex items-center gap-2">
                        <a href="prescription_pdf.php?id=<?= e($p['id']) ?>" target="_blank"
                           class="inline-flex items-center gap-1 px-3 py-1.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-xs font-medium rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 hover:scale-105 shadow-sm">
                          <i data-lucide="download" class="w-3 h-3"></i>
                          PDF
                        </a>
                        <button onclick="showPrescriptionDetails(<?= e($p['appointment_id']) ?>)" 
                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-white border border-gray-200 text-gray-700 text-xs font-medium rounded-lg hover:bg-gray-50 transition-colors">
                          <i data-lucide="eye" class="w-3 h-3"></i>
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
    <?php endif; ?>
  </main>

  <!-- Prescription Details Modal -->
  <div id="prescriptionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[80vh] overflow-hidden">
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
    
    async function showPrescriptionDetails(appointmentId) {
      const modal = document.getElementById('prescriptionModal');
      const content = document.getElementById('prescriptionContent');
      
      content.innerHTML = '<div class="flex items-center justify-center py-8"><i data-lucide="loader" class="w-6 h-6 animate-spin"></i></div>';
      modal.classList.remove('hidden');
      
      try {
        const response = await fetch(`get_prescription_details.php?appointment_id=${appointmentId}`);
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
