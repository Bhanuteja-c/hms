<?php
// reception/dashboard.php
require_once __DIR__ . '/../includes/auth.php';
require_role('receptionist');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$rid = current_user_id();

// Quick stats using prepared statements for safety (no params here, but good practice)
$totalPatientsToday = (int)$pdo->query("
    SELECT COUNT(DISTINCT patient_id) 
    FROM appointments 
    WHERE DATE(date_time) = CURDATE()
")->fetchColumn();

$totalAppointmentsToday = (int)$pdo->query("
    SELECT COUNT(*) 
    FROM appointments 
    WHERE DATE(date_time) = CURDATE()
")->fetchColumn();

$arrivedCount = (int)$pdo->query("
    SELECT COUNT(*) 
    FROM appointments 
    WHERE DATE(date_time) = CURDATE() AND arrived=1
")->fetchColumn();

$unpaidBills = (int)$pdo->query("
    SELECT COUNT(*) 
    FROM bills 
    WHERE status='unpaid'
")->fetchColumn();

// Fetch today's appointments (prepared)
$stmt = $pdo->prepare("
    SELECT a.*, p.name AS patient_name, d.name AS doctor_name 
    FROM appointments a
    JOIN users p ON a.patient_id = p.id
    JOIN users d ON a.doctor_id = d.id
    WHERE DATE(a.date_time) = CURDATE()
    ORDER BY a.date_time ASC
");
$stmt->execute();
$todayAppointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Receptionist Dashboard - Healsync</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    @keyframes fade-in { from { opacity:0; transform:translateY(-6px);} to {opacity:1; transform:none;} }
    .animate-fade-in { animation: fade-in .28s ease-out; }
  </style>
</head>
<body class="bg-gray-100">
  <?php include __DIR__ . '/../includes/sidebar_reception.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <!-- Toast container -->
  <div id="toast-container" class="fixed top-5 right-5 space-y-3 z-50"></div>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300">
    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
      <i data-lucide="layout-dashboard" class="w-6 h-6 text-indigo-600"></i>
      Receptionist Dashboard
    </h2>

    <!-- Quick Stats -->
    <div class="grid md:grid-cols-4 gap-6 mb-8">
      <div class="bg-white p-4 rounded shadow flex items-center gap-4">
        <i data-lucide="users" class="w-8 h-8 text-indigo-600"></i>
        <div>
          <p class="text-gray-500">Patients Today</p>
          <p class="text-2xl font-bold"><?= e($totalPatientsToday) ?></p>
        </div>
      </div>
      <div class="bg-white p-4 rounded shadow flex items-center gap-4">
        <i data-lucide="calendar" class="w-8 h-8 text-green-600"></i>
        <div>
          <p class="text-gray-500">Appointments</p>
          <p class="text-2xl font-bold"><?= e($totalAppointmentsToday) ?></p>
        </div>
      </div>
      <div class="bg-white p-4 rounded shadow flex items-center gap-4">
        <i data-lucide="check-circle" class="w-8 h-8 text-yellow-600"></i>
        <div>
          <p class="text-gray-500">Arrived</p>
          <p class="text-2xl font-bold" id="arrivedCount"><?= e($arrivedCount) ?></p>
        </div>
      </div>
      <div class="bg-white p-4 rounded shadow flex items-center gap-4">
        <i data-lucide="credit-card" class="w-8 h-8 text-red-600"></i>
        <div>
          <p class="text-gray-500">Unpaid Bills</p>
          <p class="text-2xl font-bold"><?= e($unpaidBills) ?></p>
        </div>
      </div>
    </div>

    <div class="mb-6">
      <a href="walkin_appointment.php"
         class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 flex items-center gap-2 w-fit">
        <i data-lucide="plus-circle" class="w-5 h-5"></i>
        Add Walk-in Appointment
      </a>
    </div>

    <!-- Today's Appointments -->
    <div class="bg-white rounded shadow overflow-hidden">
      <div class="p-4 border-b font-semibold text-lg flex items-center gap-2">
        <i data-lucide="calendar-days" class="w-5 h-5 text-indigo-600"></i>
        Today's Appointments
      </div>

      <?php if ($todayAppointments): ?>
        <div class="overflow-x-auto">
        <table id="appointmentsTable" class="w-full">
          <thead class="bg-gray-100 text-sm">
            <tr>
              <th class="p-3 text-left">Time</th>
              <th class="p-3 text-left">Patient</th>
              <th class="p-3 text-left">Doctor</th>
              <th class="p-3 text-left">Status</th>
              <th class="p-3 text-left">Arrived?</th>
              <th class="p-3 text-left">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($todayAppointments as $a): ?>
              <tr id="appt-row-<?= e($a['id']) ?>" class="border-t hover:bg-gray-50">
                <td class="p-3"><?= e(date('H:i', strtotime($a['date_time']))) ?></td>
                <td class="p-3"><?= e($a['patient_name']) ?></td>
                <td class="p-3"><?= e($a['doctor_name']) ?></td>
                <td class="p-3"><?= e(ucfirst($a['status'])) ?></td>
                <td class="p-3" data-arrived="<?= e($a['arrived']) ?>">
                  <?php if ($a['arrived']): ?>
                    <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded-full">Yes</span>
                  <?php else: ?>
                    <span class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded-full">No</span>
                  <?php endif; ?>
                </td>
                <td class="p-3">
                  <?php if (!$a['arrived']): ?>
                    <button onclick="markArrived(<?= e($a['id']) ?>)" class="px-3 py-1 bg-green-600 text-white rounded text-sm hover:bg-green-700">
                      Mark Arrived
                    </button>
                  <?php else: ?>
                    <span class="text-gray-400 text-sm">-</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        </div>
      <?php else: ?>
        <div class="p-4 text-gray-500">No appointments today.</div>
      <?php endif; ?>
    </div>
  </main>

<script>
  // helper toast
  function showToast(msg, type='success') {
    const c = document.getElementById('toast-container');
    const el = document.createElement('div');
    el.className = `animate-fade-in border p-3 rounded ${type==='success' ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'}`;
    el.innerText = msg;
    c.appendChild(el);
    setTimeout(()=> el.remove(), 4500);
  }

  async function markArrived(apptId) {
    try {
      const body = new URLSearchParams();
      body.append('csrf', <?= json_encode(csrf()) ?>);
      body.append('appointment_id', apptId);

      const res = await fetch('mark_arrived.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: body.toString()
      });

      // try parse JSON; mark_arrived supports JSON response for AJAX
      const json = await res.json().catch(()=>({ ok: false }));

      if (json.ok) {
        showToast(json.msg || 'Marked arrived', 'success');

        // update row UI
        const row = document.getElementById('appt-row-' + apptId);
        if (row) {
          row.querySelector('td[data-arrived]').innerHTML = `<span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded-full">Yes</span>`;
          const actionCell = row.querySelector('td:last-child');
          if (actionCell) actionCell.innerHTML = '<span class="text-gray-400 text-sm">-</span>';
        }

        // increment arrived counter
        const countEl = document.getElementById('arrivedCount');
        if (countEl) countEl.textContent = (parseInt(countEl.textContent||'0') + 1);

      } else {
        showToast(json.msg || 'Failed to mark arrived', 'error');
      }
    } catch (err) {
      console.error(err);
      showToast('Server error', 'error');
    }
  }

  // initialize icons after DOM
  document.addEventListener('DOMContentLoaded', function(){ if (window.lucide) lucide.createIcons(); });
</script>
</body>
</html>
