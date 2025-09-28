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
<html>
<head>
  <meta charset="utf-8">
  <title>My Prescriptions - Healsync</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50">
  <?php include __DIR__ . '/../includes/sidebar_patient.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold flex items-center gap-2">
        <i data-lucide="pill" class="w-6 h-6 text-indigo-600"></i>
        My Prescriptions
      </h2>

      <?php if ($prescriptions): ?>
        <a href="prescriptions_pdf.php" target="_blank"
           class="px-4 py-2 bg-indigo-600 text-white rounded text-sm hover:bg-indigo-700 flex items-center gap-2">
          <i data-lucide="download" class="w-4 h-4"></i> Download All (PDF)
        </a>
      <?php endif; ?>
    </div>

    <?php if (!$prescriptions): ?>
      <div class="bg-white rounded-lg shadow p-8 text-center">
        <i data-lucide="pill" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Prescriptions Yet</h3>
        <p class="text-gray-500 mb-4">You don't have any prescriptions yet. Prescriptions will appear here after your appointments.</p>
        <a href="book_appointment.php" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
          <i data-lucide="calendar-plus" class="w-4 h-4"></i>
          Book Appointment
        </a>
      </div>
    <?php else: ?>
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead class="bg-gray-50 border-b">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doctor</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Medicines</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <?php foreach ($prescriptions as $p): ?>
                <tr class="hover:bg-gray-50 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900"><?= e(format_date($p['date_time'])) ?></div>
                    <div class="text-sm text-gray-500"><?= e(format_time($p['date_time'])) ?></div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900"><?= e($p['doctor_name']) ?></div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-gray-900">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                        <?= e($p['medicine_count']) ?> medicine<?= $p['medicine_count'] > 1 ? 's' : '' ?>
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex items-center gap-2">
                      <a href="prescription_pdf.php?id=<?= e($p['id']) ?>" target="_blank"
                         class="inline-flex items-center gap-1 px-3 py-1 bg-indigo-600 text-white rounded text-sm hover:bg-indigo-700 transition-colors">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        PDF
                      </a>
                      <button onclick="showPrescriptionDetails(<?= e($p['appointment_id']) ?>)" 
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
