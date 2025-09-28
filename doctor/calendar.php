<?php
// doctor/calendar.php
require_once __DIR__ . '/../includes/auth.php';
require_role('doctor');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$did = current_user_id();

// Fetch approved appointments for doctor
$stmt = $pdo->prepare("
  SELECT a.id, a.date_time, a.reason, u.name AS patient_name
  FROM appointments a
  JOIN users u ON a.patient_id = u.id
  WHERE a.doctor_id = :did AND a.status = 'approved'
  ORDER BY a.date_time ASC
");
$stmt->execute([':did' => $did]);
$apps = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Convert appointments to FullCalendar event objects
$events = [];
foreach ($apps as $a) {
    $events[] = [
        'id'    => $a['id'],
        'title' => $a['patient_name'],
        'start' => date('c', strtotime($a['date_time'])),
        'backgroundColor' => '#3b82f6', // Tailwind indigo-500 (approved default)
        'extendedProps' => [
            'reason' => $a['reason'],
        ]
    ];
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Appointments Calendar - Doctor</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/main.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/main.min.js"></script>
</head>
<body class="bg-gray-50">
  <?php include __DIR__ . '/../includes/header.php'; ?>
  <?php include __DIR__ . '/../includes/sidebar_doctor.php'; ?>

  <main class="pt-20 p-6 md:ml-64 max-w-6xl mx-auto transition-all duration-300">
    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
      <i data-lucide="calendar-days" class="w-6 h-6 text-indigo-600"></i>
      Appointments Calendar
    </h2>

    <!-- Legend -->
    <div class="flex flex-wrap gap-4 mb-6 text-sm">
      <div class="flex items-center gap-2"><span class="w-4 h-4 bg-blue-500 rounded"></span> Approved</div>
      <div class="flex items-center gap-2"><span class="w-4 h-4 bg-green-500 rounded"></span> Prescription</div>
      <div class="flex items-center gap-2"><span class="w-4 h-4 bg-orange-500 rounded"></span> Treatment</div>
      <div class="flex items-center gap-2"><span class="w-4 h-4 bg-purple-500 rounded"></span> Both</div>
    </div>

    <!-- Calendar -->
    <div id="calendar" class="bg-white p-4 rounded shadow"></div>
  </main>

  <!-- Modal -->
  <div id="modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-lg p-6 relative">
      <button onclick="closeModal()" 
              class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">&times;</button>
      <div id="modal-content">Loading...</div>
    </div>
  </div>

  <?php include __DIR__ . '/../includes/footer.php'; ?>

  <script src="https://unpkg.com/lucide@latest"></script>
  <script>
  let calendar;

  document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      height: 'auto',
      events: <?= json_encode($events) ?>,
      eventClick: function(info) {
        openModal();
        loadAppointment(info.event.id);
      }
    });
    calendar.render();
    lucide.createIcons();
  });

  function openModal() {
    document.getElementById('modal').classList.remove('hidden');
    document.getElementById('modal').classList.add('flex');
  }
  function closeModal() {
    document.getElementById('modal').classList.add('hidden');
    document.getElementById('modal').classList.remove('flex');
  }

  function loadAppointment(appId) {
    fetch(`/healsync/api/get_appointment.php?id=${appId}`)
      .then(r => r.json())
      .then(data => {
        if (data.ok) {
          document.getElementById('modal-content').innerHTML = `
            <h3 class="text-lg font-semibold mb-2">${data.patient_name}</h3>
            <p class="text-sm text-gray-600 mb-4">Reason: ${data.reason}</p>
            <div class="flex gap-2">
              <button onclick="showPrescriptionForm(${data.id})" class="px-3 py-2 bg-blue-600 text-white rounded">Add Prescription</button>
              <button onclick="showTreatmentForm(${data.id})" class="px-3 py-2 bg-green-600 text-white rounded">Add Treatment</button>
            </div>
            <div id="form-area" class="mt-4"></div>
          `;
        } else {
          document.getElementById('modal-content').innerHTML = `<div class="text-red-600">Error loading appointment.</div>`;
        }
      });
  }

  function showPrescriptionForm(appId) {
    document.getElementById('form-area').innerHTML = `
      <h4 class="font-semibold mb-2">Prescription</h4>
      <form onsubmit="submitPrescription(event, ${appId})" class="space-y-2">
        <input name="medicine" placeholder="Medicine" class="border p-2 w-full rounded" required>
        <input name="dosage" placeholder="Dosage" class="border p-2 w-full rounded">
        <input name="duration" placeholder="Duration" class="border p-2 w-full rounded">
        <textarea name="instructions" placeholder="Instructions" class="border p-2 w-full rounded"></textarea>
        <button class="px-4 py-2 bg-blue-600 text-white rounded">Save</button>
      </form>
    `;
  }

  function showTreatmentForm(appId) {
    document.getElementById('form-area').innerHTML = `
      <h4 class="font-semibold mb-2">Treatment</h4>
      <form onsubmit="submitTreatment(event, ${appId})" class="space-y-2">
        <input name="treatment_name" placeholder="Treatment Name" class="border p-2 w-full rounded" required>
        <input type="date" name="date" class="border p-2 w-full rounded" required>
        <input type="number" step="0.01" name="cost" placeholder="Cost (₹)" class="border p-2 w-full rounded" required>
        <textarea name="notes" placeholder="Notes" class="border p-2 w-full rounded"></textarea>
        <button class="px-4 py-2 bg-green-600 text-white rounded">Save</button>
      </form>
    `;
  }

  function submitPrescription(e, appId) {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('appointment_id', appId);
    fetch('/healsync/api/add_prescription_ajax.php', {method:'POST', body: formData})
      .then(r => r.json())
      .then(data => handleFormResponse(data, appId));
  }

  function submitTreatment(e, appId) {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('appointment_id', appId);
    fetch('/healsync/api/add_treatment_ajax.php', {method:'POST', body: formData})
      .then(r => r.json())
      .then(data => handleFormResponse(data, appId));
  }

  function handleFormResponse(data, appId) {
    alert(data.msg);
    if (data.ok) {
      recolorEvent(appId, data.status);
      closeModal();
    }
  }

  function recolorEvent(appId, type) {
    const event = calendar.getEventById(String(appId));
    if (!event) return;

    const colorMap = {
      'prescription': '#22c55e', // green-500
      'treatment': '#f97316',    // orange-500
      'both': '#9333ea'          // purple-600
    };

    const currentColor = event.backgroundColor;
    if ((type === 'prescription' && currentColor === '#f97316') ||
        (type === 'treatment' && currentColor === '#22c55e')) {
      event.setProp('backgroundColor', colorMap['both']);
    } else {
      event.setProp('backgroundColor', colorMap[type]);
    }
  }
  </script>
</body>
</html>
