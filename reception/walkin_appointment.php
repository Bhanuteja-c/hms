<?php
// reception/walkin_appointment.php
require_once __DIR__ . '/../includes/auth.php';
require_role('receptionist');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$rid = current_user_id();

// Fetch doctors
$stmt = $pdo->prepare("
    SELECT u.id, u.name, COALESCE(d.specialty, '') AS specialty
    FROM users u
    LEFT JOIN doctors d ON u.id = d.id
    WHERE u.role = 'doctor'
    ORDER BY u.name
");
$stmt->execute();
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recent patients for quick-select
$stmt = $pdo->prepare("
    SELECT DISTINCT u.id, u.name, u.email, u.phone
    FROM users u
    JOIN appointments a ON a.patient_id = u.id
    WHERE u.role = 'patient'
    ORDER BY a.date_time DESC
    LIMIT 30
");
$stmt->execute();
$recentPatients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Add Walk-in Appointment - Healsync</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>
<body class="bg-gray-50">
  <?php include __DIR__ . '/../includes/sidebar_reception.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-24 p-6 md:ml-64 max-w-3xl mx-auto">
    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
      <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3"></path></svg>
      Add Walk-in Appointment
    </h2>

    <div id="alert" class="mb-4"></div>

    <form id="walkinForm" class="bg-white p-6 rounded shadow space-y-4" onsubmit="return submitWalkin(event)">
      <input type="hidden" name="csrf" value="<?= csrf() ?>">

      <div>
        <label class="block text-sm font-medium mb-1">Doctor</label>
        <select name="doctor_id" id="doctor_id" required class="w-full border rounded p-2">
          <option value="">-- Select Doctor --</option>
          <?php foreach($doctors as $d): ?>
            <option value="<?= e($d['id']) ?>"><?= e($d['name']) ?><?= $d['specialty'] ? ' (' . e($d['specialty']) . ')' : '' ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Date & Time</label>
        <input type="text" name="date_time" id="date_time" required class="w-full border rounded p-2" />
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Reason</label>
        <input type="text" name="reason" required class="w-full border rounded p-2" />
      </div>

      <hr class="my-2" />

      <div class="flex items-center gap-4">
        <label class="flex items-center gap-2">
          <input type="radio" name="patient_mode" value="existing" checked onchange="togglePatientMode()" />
          <span class="text-sm">Existing patient</span>
        </label>
        <label class="flex items-center gap-2">
          <input type="radio" name="patient_mode" value="new" onchange="togglePatientMode()" />
          <span class="text-sm">New patient</span>
        </label>
      </div>

      <div id="existingPatientArea">
        <label class="block text-sm font-medium mb-1">Choose Patient</label>
        <select name="patient_id" id="patient_id" class="w-full border rounded p-2">
          <option value="">-- Select recent patient --</option>
          <?php foreach($recentPatients as $p): ?>
            <option value="<?= e($p['id']) ?>"><?= e($p['name']) ?><?= $p['email'] ? ' (' . e($p['email']) . ')' : '' ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div id="newPatientArea" style="display:none;">
        <label class="block text-sm font-medium mb-1">Full Name</label>
        <input name="new_name" class="w-full border rounded p-2" />
        <label class="block text-sm font-medium mb-1 mt-2">Email (optional)</label>
        <input name="new_email" type="email" class="w-full border rounded p-2" />
        <label class="block text-sm font-medium mb-1 mt-2">Phone (optional)</label>
        <input name="new_phone" class="w-full border rounded p-2" />
      </div>

      <div class="flex justify-between items-center">
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
          Add Walk-in & Mark Arrived
        </button>
        <a href="dashboard.php" class="text-sm text-gray-600 hover:underline">Back to dashboard</a>
      </div>
    </form>
  </main>

<script>
  // init icons & datepicker after DOM
  document.addEventListener('DOMContentLoaded', function(){
    flatpickr("#date_time", { enableTime: true, dateFormat: "Y-m-d H:i", defaultDate: new Date() });
    if (window.lucide) lucide.createIcons();
  });

  function togglePatientMode(){
    const mode = document.querySelector('input[name="patient_mode"]:checked').value;
    document.getElementById('existingPatientArea').style.display = mode === 'existing' ? '' : 'none';
    document.getElementById('newPatientArea').style.display = mode === 'new' ? '' : 'none';
  }

  function showAlert(msg, type='success'){
    const a = document.getElementById('alert');
    a.innerHTML = `<div class="${type==='success'? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700'} border p-3 rounded">${msg}</div>`;
    setTimeout(()=> a.innerHTML = '', 5000);
  }

  async function submitWalkin(e){
    e.preventDefault();
    const form = document.getElementById('walkinForm');
    const data = new FormData(form);

    try {
      const res = await fetch('walkin_submit.php', {
        method: 'POST',
        credentials: 'same-origin',
        body: data
      });
      const json = await res.json();
      if (!json) throw new Error('Invalid response');

      if (json.ok) {
        showAlert(json.msg, 'success');

        // attempt to update dashboard table if present on same page
        const tableBody = document.querySelector('#appointmentsTable tbody');
        if (tableBody && json.appointment) {
          const ap = json.appointment;
          const tr = document.createElement('tr');
          tr.id = 'appt-' + ap.id;
          tr.className = 'border-t hover:bg-gray-50';
          tr.innerHTML = `
            <td class="p-3">${new Date(ap.date_time).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})}</td>
            <td class="p-3">${escapeHtml(ap.patient_name)}</td>
            <td class="p-3">${escapeHtml(ap.doctor_name)}</td>
            <td class="p-3">${escapeHtml(ap.status)}</td>
            <td class="p-3"><span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded-full">Yes</span></td>
            <td class="p-3"><span class="text-gray-400 text-sm">-</span></td>
          `;
          tableBody.prepend(tr);
          const arrivedEl = document.getElementById('arrivedCount');
          if (arrivedEl) arrivedEl.textContent = (parseInt(arrivedEl.textContent||'0')+1);
        }

        form.reset();
        togglePatientMode();
      } else {
        showAlert(json.msg || 'Failed to add walk-in', 'error');
      }
    } catch (err) {
      console.error(err);
      showAlert('Server error', 'error');
    }
    return false;
  }

  function escapeHtml(s){ return String(s||'').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
</script>

<!-- lucide icons -->
<script src="https://unpkg.com/lucide@latest"></script>
<script>if(window.lucide) lucide.createIcons();</script>
</body>
</html>
