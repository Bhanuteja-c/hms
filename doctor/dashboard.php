<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('doctor');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$did = current_user_id();
$message = "";

// Approve / Reject Appointment (POST for security)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['appointment_id'])) {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $message = "<p class='text-red-600 mb-4'>Invalid CSRF token.</p>";
    } else {
        $appt_id = intval($_POST['appointment_id']);
        $action = $_POST['action'];

        if (in_array($action, ['approve','reject'])) {
            $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id=:id AND doctor_id=:did AND status='pending'");
            $stmt->execute([':id'=>$appt_id, ':did'=>$did]);
            $appt = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($appt) {
                $newStatus = $action === 'approve' ? 'approved' : 'rejected';
                $pdo->prepare("UPDATE appointments SET status=:status WHERE id=:id")
                    ->execute([':status'=>$newStatus, ':id'=>$appt_id]);

                // Notify patient
                $msg = "Your appointment on " . date('d M Y H:i', strtotime($appt['date_time'])) .
                       " with Dr. " . e(current_user_name()) . " has been $newStatus.";
                $link = "/healsync/patient/appointments.php";
                $pdo->prepare("INSERT INTO notifications (user_id, message, link) VALUES (:uid,:msg,:link)")
                    ->execute([':uid'=>$appt['patient_id'], ':msg'=>$msg, ':link'=>$link]);

                $message = "<p class='text-green-600 mb-4'>Appointment " . ucfirst($newStatus) . " successfully.</p>";
            } else {
                $message = "<p class='text-red-600 mb-4'>Invalid request.</p>";
            }
        }
    }
}

// Pending Appointments
$stmt = $pdo->prepare("
    SELECT a.*, u.name AS patient_name, u.email AS patient_email
    FROM appointments a
    JOIN users u ON a.patient_id=u.id
    WHERE a.doctor_id=:did AND a.status='pending'
    ORDER BY a.date_time ASC
");
$stmt->execute([':did'=>$did]);
$pending = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Approved Upcoming Appointments
$stmt = $pdo->prepare("
    SELECT a.id, a.date_time, u.name AS patient_name
    FROM appointments a
    JOIN users u ON a.patient_id=u.id
    WHERE a.doctor_id=:did AND a.status='approved' AND a.date_time >= NOW()
    ORDER BY a.date_time ASC
");
$stmt->execute([':did'=>$did]);
$upcoming = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Convert to FullCalendar events
$events = [];
foreach ($upcoming as $u) {
    $events[] = [
        'id' => $u['id'],
        'title' => $u['patient_name'],
        'start' => date('c', strtotime($u['date_time']))
    ];
}

// Quick Stats
$totalAppointments = $pdo->query("SELECT COUNT(*) FROM appointments WHERE doctor_id=$did")->fetchColumn();
$todayAppointments = $pdo->query("SELECT COUNT(*) FROM appointments WHERE doctor_id=$did AND DATE(date_time)=CURDATE()")->fetchColumn();
$pendingCount = count($pending);

// Recent Patients (last 5)
$stmt = $pdo->prepare("
    SELECT DISTINCT u.id, u.name, u.email 
    FROM appointments a 
    JOIN users u ON a.patient_id=u.id 
    WHERE a.doctor_id=:did 
    ORDER BY a.date_time DESC 
    LIMIT 5
");
$stmt->execute([':did'=>$did]);
$recentPatients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Doctor Dashboard - Healsync</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/main.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/main.min.js"></script>
  <style>
    .glass-effect {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    .stat-card {
      transition: all 0.3s ease;
    }
    .stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .fc-theme-standard .fc-scrollgrid {
      border-radius: 12px;
      overflow: hidden;
    }
  </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 min-h-screen">
  <?php include __DIR__ . '/../includes/sidebar_doctor.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300">
    <!-- Header Section -->
    <div class="mb-8">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
            Welcome, Dr. <?= e(current_user_name()) ?>! 👨‍⚕️
          </h1>
          <p class="text-gray-600 mt-1">Manage your appointments and patient care</p>
        </div>
        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-500 rounded-xl flex items-center justify-center">
          <i data-lucide="stethoscope" class="w-6 h-6 text-white"></i>
        </div>
      </div>
    </div>

    <?= $message ?>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="stat-card glass-effect rounded-2xl p-6 border border-white/20">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm font-medium">Total Appointments</p>
            <p class="text-3xl font-bold text-gray-900 mt-1"><?= $totalAppointments ?></p>
            <p class="text-indigo-600 text-sm mt-1">
              <i data-lucide="trending-up" class="w-4 h-4 inline"></i>
              All time
            </p>
          </div>
          <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center">
            <i data-lucide="calendar" class="w-6 h-6 text-white"></i>
          </div>
        </div>
      </div>

      <div class="stat-card glass-effect rounded-2xl p-6 border border-white/20">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm font-medium">Today's Appointments</p>
            <p class="text-3xl font-bold text-gray-900 mt-1"><?= $todayAppointments ?></p>
            <p class="text-green-600 text-sm mt-1">
              <i data-lucide="calendar-check" class="w-4 h-4 inline"></i>
              Scheduled today
            </p>
          </div>
          <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-500 rounded-xl flex items-center justify-center">
            <i data-lucide="clock" class="w-6 h-6 text-white"></i>
          </div>
        </div>
      </div>

      <div class="stat-card glass-effect rounded-2xl p-6 border border-white/20">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm font-medium">Pending Approvals</p>
            <p class="text-3xl font-bold text-gray-900 mt-1"><?= $pendingCount ?></p>
            <p class="text-orange-600 text-sm mt-1">
              <i data-lucide="alert-circle" class="w-4 h-4 inline"></i>
              Awaiting review
            </p>
          </div>
          <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-red-500 rounded-xl flex items-center justify-center">
            <i data-lucide="alert-circle" class="w-6 h-6 text-white"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Patients -->
    <div class="bg-white p-6 rounded shadow mb-8">
      <h3 class="text-xl font-semibold mb-4 flex items-center gap-2">
        <i data-lucide="users" class="w-5 h-5 text-indigo-600"></i>
        Recent Patients
      </h3>
      <?php if ($recentPatients): ?>
        <ul class="space-y-3">
          <?php foreach ($recentPatients as $p): ?>
            <li class="flex justify-between items-center border-b pb-2">
              <div>
                <p class="font-medium"><?=e($p['name'])?></p>
                <p class="text-sm text-gray-500"><?=e($p['email'])?></p>
              </div>
              <a href="patient_history.php?query=<?=urlencode($p['email'])?>"
                 class="px-3 py-1 bg-indigo-600 text-white rounded text-sm flex items-center gap-1 hover:bg-indigo-700">
                <i data-lucide="search" class="w-4 h-4"></i> View
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p class="text-gray-500">No recent patients found.</p>
      <?php endif; ?>
    </div>

    <!-- Upcoming Appointments Calendar -->
    <div class="bg-white p-6 rounded shadow mb-8">
      <h3 class="text-xl font-semibold mb-4 flex items-center gap-2">
        <i data-lucide="calendar-days" class="w-5 h-5 text-green-600"></i>
        Upcoming Appointments (Calendar View)
      </h3>
      <div id="calendar" class="rounded border"></div>
    </div>

    <!-- Pending Appointments -->
    <h3 class="text-xl font-semibold mb-4 flex items-center gap-2">
      <i data-lucide="clock" class="w-5 h-5 text-yellow-600"></i>
      Pending Appointments
    </h3>

    <?php if ($pending): ?>
      <div class="overflow-x-auto">
        <table class="w-full bg-white rounded shadow">
          <thead class="bg-gray-100">
            <tr>
              <th class="text-left p-3">Date & Time</th>
              <th class="text-left p-3">Patient</th>
              <th class="text-left p-3">Email</th>
              <th class="text-left p-3">Reason</th>
              <th class="text-left p-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($pending as $a): ?>
              <tr class="border-t hover:bg-gray-50">
                <td class="p-3"><?=date('d M Y H:i', strtotime($a['date_time']))?></td>
                <td class="p-3"><?=e($a['patient_name'])?></td>
                <td class="p-3"><?=e($a['patient_email'])?></td>
                <td class="p-3"><?=e($a['reason'])?></td>
                <td class="p-3">
                  <form method="post" class="flex gap-2">
                    <input type="hidden" name="csrf" value="<?=csrf()?>">
                    <input type="hidden" name="appointment_id" value="<?=e($a['id'])?>">
                    <button name="action" value="approve" class="px-3 py-1 bg-green-600 text-white rounded text-sm flex items-center gap-1 hover:bg-green-700">
                      <i data-lucide="check-circle" class="w-4 h-4"></i> Approve
                    </button>
                    <button name="action" value="reject" class="px-3 py-1 bg-red-600 text-white rounded text-sm flex items-center gap-1 hover:bg-red-700">
                      <i data-lucide="x-circle" class="w-4 h-4"></i> Reject
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-gray-500">No pending appointments.</p>
    <?php endif; ?>
  </main>

  <script>
    lucide.createIcons();
    document.addEventListener('DOMContentLoaded', function() {
      var calendarEl = document.getElementById('calendar');
      var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 'auto',
        events: <?=json_encode($events)?>,
      });
      calendar.render();
    });
  </script>
</body>
</html>
