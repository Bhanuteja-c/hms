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
");
$stmt->execute([':did'=>$did]);
$apps = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Convert appointments to FullCalendar event objects
$events = [];
foreach ($apps as $a) {
    $events[] = [
        'id'    => $a['id'],
        'title' => $a['patient_name'],
        'start' => date('c', strtotime($a['date_time'])),
        'backgroundColor' => 'blue', // default color
        'extendedProps' => [
            'reason' => $a['reason'],
        ]
    ];
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Appointments Calendar - Doctor</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/main.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/main.min.js"></script>
</head>
<body class="bg-gray-50">
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="container mx-auto p-6">
  <h2 class="text-2xl font-bold mb-4">Appointments Calendar</h2>

  <!-- Legend -->
  <div class="flex gap-4 mb-4">
    <div class="flex items-center gap-2"><span class="w-4 h-4 bg-blue-500 inline-block"></span> Approved</div>
    <div class="flex items-center gap-2"><span class="w-4 h-4 bg-green-500 inline-block"></span> Prescription</div>
    <div class="flex items-center gap-2"><span class="w-4 h-4 bg-orange-500 inline-block"></span> Treatment</div>
    <div class="flex items-center gap-2"><span class="w-4 h-4 bg-purple-500 inline-block"></span> Both</div>
  </div>

  <!-- Calendar -->
  <div id="calendar" class="bg-white p-4 rounded shadow"></div>
</main>

<!-- Modal -->
<div id="modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-xl shadow-lg w-full max-w-lg p-6 relative">
    <button onclick="closeModal()" class="absolute top-2 right-2 text-gray-500">&times;</button>
    <div id="modal-content">Loading...</div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
let calendar;

document.addEventListener('DOMContentLoaded', function() {
  var calendarEl = document.getElementById('calendar');
  calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    height: 'auto',
    events: <?=json_encode($events)?>,

    eventClick: function(info) {
      const appId = info.event.id;
      openModal();
      fetch('/healsync/api/get_appointment.php?id=' + appId)
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
  });
  calendar.render();
});

function openModal() {
  document.getElementById('modal').classList.remove('hidden');
  document.getElementById('modal').classList.add('flex');
}
function closeModal() {
  document.getElementById('modal').classList.add('hidden');
  document.getElementById('modal').classList.remove('flex');
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
      <input type="number" step="0.01" name="cost" placeholder="Cost" class="border p-2 w-full rounded">
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
    .then(data => {
      alert(data.msg);
      if (data.ok) {
        recolorEvent(appId, data.status);
        closeModal();
      }
    });
}

function submitTreatment(e, appId) {
  e.preventDefault();
  const formData = new FormData(e.target);
  formData.append('appointment_id', appId);
  fetch('/healsync/api/add_treatment_ajax.php', {method:'POST', body: formData})
    .then(r => r.json())
    .then(data => {
      alert(data.msg);
      if (data.ok) {
        recolorEvent(appId, data.status);
        closeModal();
      }
    });
}

function recolorEvent(appId, type) {
  let event = calendar.getEventById(String(appId));
  if (!event) return;

  let currentColor = event.backgroundColor || 'blue';

  if (type === 'prescription' && currentColor === 'orange') {
    event.setProp('backgroundColor', 'purple');
  } else if (type === 'treatment' && currentColor === 'green') {
    event.setProp('backgroundColor', 'purple');
  } else if (type === 'prescription') {
    event.setProp('backgroundColor', 'green');
  } else if (type === 'treatment') {
    event.setProp('backgroundColor', 'orange');
  }
}
</script>
</body>
</html>
