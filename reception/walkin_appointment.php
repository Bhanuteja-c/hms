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
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Walk-in Appointment - Healsync</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <style>
    .glass-effect {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    .form-input {
      transition: all 0.3s ease;
    }
    .form-input:focus {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(99, 102, 241, 0.15);
    }
    @keyframes fade-in { 
      from { opacity:0; transform:translateY(-6px);} 
      to {opacity:1; transform:none;} 
    }
    .animate-fade-in { 
      animation: fade-in .28s ease-out; 
    }
  </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 min-h-screen">
  <?php include __DIR__ . '/../includes/sidebar_reception.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300">
    <!-- Header Section -->
    <div class="mb-8">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
            Walk-in Appointment
          </h1>
          <p class="text-gray-600 mt-1">Register a new patient and create an immediate appointment</p>
        </div>
        <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center">
          <i data-lucide="user-plus" class="w-6 h-6 text-white"></i>
        </div>
      </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" class="fixed top-5 right-5 space-y-3 z-50"></div>

    <!-- Walk-in Form -->
    <div class="glass-effect rounded-2xl border border-white/20 overflow-hidden">
      <div class="p-6 border-b border-gray-200/50">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center">
            <i data-lucide="clipboard-list" class="w-5 h-5 text-white"></i>
          </div>
          <div>
            <h3 class="text-xl font-bold text-gray-900">Appointment Details</h3>
            <p class="text-gray-600 text-sm">Fill in the appointment information</p>
          </div>
        </div>
      </div>

      <form id="walkinForm" class="p-6 space-y-6" onsubmit="return submitWalkin(event)">
        <input type="hidden" name="csrf" value="<?= csrf() ?>">

        <!-- Doctor Selection -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            <i data-lucide="stethoscope" class="w-4 h-4 inline mr-2"></i>
            Doctor
          </label>
          <select name="doctor_id" id="doctor_id" required class="form-input w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            <option value="">-- Select Doctor --</option>
            <?php foreach($doctors as $d): ?>
              <option value="<?= e($d['id']) ?>"><?= e($d['name']) ?><?= $d['specialty'] ? ' (' . e($d['specialty']) . ')' : '' ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Date & Time -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            <i data-lucide="calendar-clock" class="w-4 h-4 inline mr-2"></i>
            Date & Time
          </label>
          <input type="text" name="date_time" id="date_time" required 
                 class="form-input w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent" 
                 placeholder="Select date and time" />
        </div>

        <!-- Reason -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            <i data-lucide="file-text" class="w-4 h-4 inline mr-2"></i>
            Reason for Visit
          </label>
          <input type="text" name="reason" required 
                 class="form-input w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent" 
                 placeholder="Brief description of the visit purpose" />
        </div>

        <!-- Divider -->
        <div class="border-t border-gray-200 pt-6">
          <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <i data-lucide="user" class="w-5 h-5 text-indigo-600"></i>
            Patient Information
          </h4>
        </div>

        <!-- Patient Mode Selection -->
        <div class="flex items-center gap-6 p-4 bg-gray-50 rounded-xl">
          <label class="flex items-center gap-3 cursor-pointer">
            <input type="radio" name="patient_mode" value="existing" checked onchange="togglePatientMode()" 
                   class="w-4 h-4 text-indigo-600 focus:ring-indigo-500" />
            <span class="text-sm font-medium text-gray-700">
              <i data-lucide="users" class="w-4 h-4 inline mr-1"></i>
              Existing Patient
            </span>
          </label>
          <label class="flex items-center gap-3 cursor-pointer">
            <input type="radio" name="patient_mode" value="new" onchange="togglePatientMode()" 
                   class="w-4 h-4 text-indigo-600 focus:ring-indigo-500" />
            <span class="text-sm font-medium text-gray-700">
              <i data-lucide="user-plus" class="w-4 h-4 inline mr-1"></i>
              New Patient
            </span>
          </label>
        </div>

        <!-- Existing Patient Selection -->
        <div id="existingPatientArea">
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            <i data-lucide="search" class="w-4 h-4 inline mr-2"></i>
            Choose Patient
          </label>
          <select name="patient_id" id="patient_id" class="form-input w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            <option value="">-- Select recent patient --</option>
            <?php foreach($recentPatients as $p): ?>
              <option value="<?= e($p['id']) ?>"><?= e($p['name']) ?><?= $p['email'] ? ' (' . e($p['email']) . ')' : '' ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- New Patient Form -->
        <div id="newPatientArea" style="display:none;" class="space-y-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              <i data-lucide="user" class="w-4 h-4 inline mr-2"></i>
              Full Name
            </label>
            <input name="new_name" class="form-input w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent" 
                   placeholder="Enter patient's full name" />
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i data-lucide="mail" class="w-4 h-4 inline mr-2"></i>
                Email <span class="text-gray-400">(optional)</span>
              </label>
              <input name="new_email" type="email" class="form-input w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent" 
                     placeholder="patient@example.com" />
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i data-lucide="phone" class="w-4 h-4 inline mr-2"></i>
                Phone <span class="text-gray-400">(optional)</span>
              </label>
              <input name="new_phone" class="form-input w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent" 
                     placeholder="+1 (555) 123-4567" />
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-between items-center pt-6 border-t border-gray-200">
          <a href="dashboard.php" class="inline-flex items-center gap-2 px-4 py-2.5 text-gray-600 hover:text-gray-800 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Back to Dashboard
          </a>
          <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-medium shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            Add Walk-in & Mark Arrived
          </button>
        </div>
      </form>
    </div>
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

  // Modern toast notification
  function showToast(msg, type='success') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `animate-fade-in border p-4 rounded-xl shadow-lg ${type==='success' ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'}`;
    toast.innerHTML = `
      <div class="flex items-center gap-2">
        <i data-lucide="${type==='success' ? 'check-circle' : 'alert-circle'}" class="w-5 h-5"></i>
        <span>${msg}</span>
      </div>`;
    container.appendChild(toast);
    if (window.lucide) lucide.createIcons();
    setTimeout(() => toast.remove(), 5000);
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
        showToast(json.msg || 'Walk-in appointment added successfully!');

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
        showToast(json.msg || 'Failed to add walk-in appointment', 'error');
      }
    } catch (err) {
      console.error(err);
      showToast('Server error', 'error');
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
