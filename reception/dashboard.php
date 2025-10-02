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
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Reception Dashboard - Healsync</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    @keyframes fade-in { from { opacity:0; transform:translateY(-6px);} to {opacity:1; transform:none;} }
    .animate-fade-in { animation: fade-in .28s ease-out; }
    .glass-effect {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    .gradient-card {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .stat-card {
      transition: all 0.3s ease;
    }
    .stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 min-h-screen">
  <?php include __DIR__ . '/../includes/sidebar_reception.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <!-- Toast container -->
  <div id="toast-container" class="fixed top-5 right-5 space-y-3 z-50"></div>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300">
    <!-- Header Section -->
    <div class="mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
            Reception Dashboard
          </h1>
          <p class="text-gray-600 mt-1">Manage appointments, patients, and billing operations</p>
        </div>
        <div class="flex gap-3">
          <a href="walkin_appointment.php"
             class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-medium shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105">
            <i data-lucide="user-plus" class="w-5 h-5"></i>
            Walk-in Appointment
          </a>
        </div>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="stat-card glass-effect rounded-2xl p-6 border border-white/20">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm font-medium">Patients Today</p>
            <p class="text-3xl font-bold text-gray-900 mt-1"><?= e($totalPatientsToday) ?></p>
            <p class="text-indigo-600 text-sm mt-1">
              <i data-lucide="trending-up" class="w-4 h-4 inline"></i>
              Active visits
            </p>
          </div>
          <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center">
            <i data-lucide="users" class="w-6 h-6 text-white"></i>
          </div>
        </div>
      </div>

      <div class="stat-card glass-effect rounded-2xl p-6 border border-white/20">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm font-medium">Appointments</p>
            <p class="text-3xl font-bold text-gray-900 mt-1"><?= e($totalAppointmentsToday) ?></p>
            <p class="text-green-600 text-sm mt-1">
              <i data-lucide="calendar-check" class="w-4 h-4 inline"></i>
              Scheduled today
            </p>
          </div>
          <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-500 rounded-xl flex items-center justify-center">
            <i data-lucide="calendar" class="w-6 h-6 text-white"></i>
          </div>
        </div>
      </div>

      <div class="stat-card glass-effect rounded-2xl p-6 border border-white/20">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm font-medium">Arrived</p>
            <p class="text-3xl font-bold text-gray-900 mt-1" id="arrivedCount"><?= e($arrivedCount) ?></p>
            <p class="text-amber-600 text-sm mt-1">
              <i data-lucide="check-circle" class="w-4 h-4 inline"></i>
              Checked in
            </p>
          </div>
          <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-500 rounded-xl flex items-center justify-center">
            <i data-lucide="check-circle" class="w-6 h-6 text-white"></i>
          </div>
        </div>
      </div>

      <div class="stat-card glass-effect rounded-2xl p-6 border border-white/20">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm font-medium">Unpaid Bills</p>
            <p class="text-3xl font-bold text-gray-900 mt-1"><?= e($unpaidBills) ?></p>
            <p class="text-red-600 text-sm mt-1">
              <i data-lucide="alert-circle" class="w-4 h-4 inline"></i>
              Pending payment
            </p>
          </div>
          <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-pink-500 rounded-xl flex items-center justify-center">
            <i data-lucide="credit-card" class="w-6 h-6 text-white"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Today's Appointments -->
    <div class="glass-effect rounded-2xl border border-white/20 overflow-hidden">
      <div class="p-6 border-b border-gray-200/50">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center">
              <i data-lucide="calendar-days" class="w-5 h-5 text-white"></i>
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-900">Today's Appointments</h3>
              <p class="text-gray-600 text-sm">Manage patient check-ins and appointments</p>
            </div>
          </div>
          <div class="text-right">
            <p class="text-2xl font-bold text-gray-900"><?= count($todayAppointments) ?></p>
            <p class="text-gray-600 text-sm">Total scheduled</p>
          </div>
        </div>
      </div>

      <?php if ($todayAppointments): ?>
        <div class="overflow-x-auto">
          <table id="appointmentsTable" class="w-full">
            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Time</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Patient</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Doctor</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Arrived</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <?php foreach ($todayAppointments as $a): ?>
                <tr id="appt-row-<?= e($a['id']) ?>" class="hover:bg-gray-50/50 transition-colors duration-200">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="w-2 h-2 bg-indigo-500 rounded-full mr-3"></div>
                      <span class="text-sm font-medium text-gray-900"><?= e(date('H:i', strtotime($a['date_time']))) ?></span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-full flex items-center justify-center mr-3">
                        <span class="text-white text-xs font-semibold"><?= e(strtoupper(substr($a['patient_name'], 0, 1))) ?></span>
                      </div>
                      <span class="text-sm font-medium text-gray-900"><?= e($a['patient_name']) ?></span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= e($a['doctor_name']) ?></td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <?php
                    $statusColors = [
                      'pending' => 'bg-yellow-100 text-yellow-800',
                      'approved' => 'bg-green-100 text-green-800', 
                      'rejected' => 'bg-red-100 text-red-800',
                      'completed' => 'bg-blue-100 text-blue-800',
                      'cancelled' => 'bg-gray-100 text-gray-800'
                    ];
                    $colorClass = $statusColors[$a['status']] ?? 'bg-gray-100 text-gray-800';
                    ?>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= $colorClass ?>">
                      <?= e(ucfirst($a['status'])) ?>
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap" data-arrived="<?= e($a['arrived']) ?>">
                    <?php if ($a['arrived']): ?>
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <i data-lucide="check" class="w-3 h-3 mr-1"></i>
                        Arrived
                      </span>
                    <?php else: ?>
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        <i data-lucide="clock" class="w-3 h-3 mr-1"></i>
                        Waiting
                      </span>
                    <?php endif; ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <?php if (!$a['arrived']): ?>
                      <button onclick="markArrived(<?= e($a['id']) ?>)" 
                              class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white text-xs font-medium rounded-lg hover:from-green-700 hover:to-emerald-700 transition-all duration-200 hover:scale-105 shadow-sm">
                        <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i>
                        Mark Arrived
                      </button>
                    <?php else: ?>
                      <span class="text-gray-400 text-xs">Checked in</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="p-12 text-center">
          <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i data-lucide="calendar-x" class="w-8 h-8 text-gray-400"></i>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No appointments today</h3>
          <p class="text-gray-600">All appointments for today have been completed or there are no scheduled appointments.</p>
        </div>
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
          row.querySelector('td[data-arrived]').innerHTML = `
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
              <i data-lucide="check" class="w-3 h-3 mr-1"></i>
              Arrived
            </span>`;
          const actionCell = row.querySelector('td:last-child');
          if (actionCell) actionCell.innerHTML = '<span class="text-gray-400 text-xs">Checked in</span>';
          
          // Re-initialize lucide icons for the new elements
          if (window.lucide) lucide.createIcons();
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
