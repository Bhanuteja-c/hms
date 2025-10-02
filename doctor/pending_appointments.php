<?php
// doctor/pending_appointments.php
require_once __DIR__ . '/../includes/auth.php';
require_role('doctor');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$doctorId = current_user_id();

// Handle approve/reject FIRST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        die("Invalid CSRF token.");
    }

    $apptId = intval($_POST['appointment_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id=:id AND doctor_id=:did");
    $stmt->execute([':id'=>$apptId, ':did'=>$doctorId]);
    $appt = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($appt) {
        if ($action === 'approve') {
            $pdo->prepare("UPDATE appointments SET status='approved' WHERE id=:id")
                ->execute([':id'=>$apptId]);

            audit_log($pdo, $doctorId, 'approve_appointment', json_encode(['appointment_id'=>$apptId]));

            // Notify patient
            $msg = "Your appointment on ".date('d M Y H:i', strtotime($appt['date_time']))." has been approved.";
            $pdo->prepare("INSERT INTO notifications (user_id,message,link) 
                           VALUES (:uid,:msg,:link)")
                ->execute([
                    ':uid'=>$appt['patient_id'], 
                    ':msg'=>$msg, 
                    ':link'=>"/healsync/patient/appointments.php"
                ]);
        } elseif ($action === 'reject') {
            $pdo->prepare("UPDATE appointments SET status='rejected' WHERE id=:id")
                ->execute([':id'=>$apptId]);

            audit_log($pdo, $doctorId, 'reject_appointment', json_encode(['appointment_id'=>$apptId]));

            // Notify patient
            $msg = "Your appointment on ".date('d M Y H:i', strtotime($appt['date_time']))." has been rejected.";
            $pdo->prepare("INSERT INTO notifications (user_id,message,link) 
                           VALUES (:uid,:msg,:link)")
                ->execute([
                    ':uid'=>$appt['patient_id'], 
                    ':msg'=>$msg, 
                    ':link'=>"/healsync/patient/appointments.php"
                ]);
        }
        header("Location: pending_appointments.php"); 
        exit;
    }
}

// Fetch pending appointments
$stmt = $pdo->prepare("
  SELECT a.*, u.name as patient_name, u.email as patient_email
  FROM appointments a
  JOIN users u ON a.patient_id = u.id
  WHERE a.doctor_id=:did AND a.status='pending'
  ORDER BY a.date_time ASC
");
$stmt->execute([':did'=>$doctorId]);
$pending = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Pending Appointments - Healsync</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    .glass-effect {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    .appointment-card {
      transition: all 0.3s ease;
    }
    .appointment-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
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
            Pending Appointments
          </h1>
          <p class="text-gray-600 mt-1">Review and manage appointment requests</p>
        </div>
        <div class="flex items-center gap-3">
          <div class="text-right">
            <p class="text-2xl font-bold text-orange-600"><?= count($pending) ?></p>
            <p class="text-gray-600 text-sm">Awaiting review</p>
          </div>
          <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-red-500 rounded-xl flex items-center justify-center">
            <i data-lucide="clock" class="w-6 h-6 text-white"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Appointments List -->
    <div class="glass-effect rounded-2xl border border-white/20 overflow-hidden">
      <div class="p-6 border-b border-gray-200/50">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-red-500 rounded-xl flex items-center justify-center">
            <i data-lucide="clock" class="w-5 h-5 text-white"></i>
          </div>
          <div>
            <h3 class="text-xl font-bold text-gray-900">Appointment Requests</h3>
            <p class="text-gray-600 text-sm">Review and approve or reject patient appointments</p>
          </div>
        </div>
      </div>

      <?php if (!$pending): ?>
        <div class="p-12 text-center">
          <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i data-lucide="check-circle" class="w-8 h-8 text-gray-400"></i>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">All caught up!</h3>
          <p class="text-gray-600 mb-4">No pending appointments to review at this time.</p>
        </div>
      <?php else: ?>
        <div class="p-6">
          <div class="space-y-4">
            <?php foreach($pending as $p): ?>
              <div class="appointment-card glass-effect rounded-xl border border-white/20 p-6">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                  <!-- Patient Info -->
                  <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-full flex items-center justify-center">
                      <i data-lucide="user" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                      <h4 class="font-bold text-lg text-gray-900"><?=e($p['patient_name'])?></h4>
                      <div class="flex items-center gap-4 text-sm text-gray-600 mt-1">
                        <div class="flex items-center gap-1">
                          <i data-lucide="mail" class="w-4 h-4"></i>
                          <?=e($p['patient_email'])?>
                        </div>
                        <div class="flex items-center gap-1">
                          <i data-lucide="calendar" class="w-4 h-4"></i>
                          <?=e(format_datetime($p['date_time']))?>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Appointment Details -->
                  <div class="flex-1 lg:max-w-md">
                    <div class="bg-gray-50 rounded-lg p-4">
                      <h5 class="font-medium text-gray-900 mb-2 flex items-center gap-2">
                        <i data-lucide="clipboard" class="w-4 h-4 text-indigo-600"></i>
                        Reason for Visit
                      </h5>
                      <p class="text-gray-700 text-sm"><?=e($p['reason'] ?: 'No reason provided')?></p>
                    </div>
                  </div>

                  <!-- Actions -->
                  <div class="flex gap-3">
                    <form method="post" class="inline">
                      <input type="hidden" name="csrf" value="<?=csrf()?>">
                      <input type="hidden" name="appointment_id" value="<?=e($p['id'])?>">
                      <button name="action" value="approve"
                              class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg font-medium hover:from-green-700 hover:to-emerald-700 transition-all duration-300 hover:scale-105 shadow-lg">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        Approve
                      </button>
                    </form>
                    <form method="post" class="inline">
                      <input type="hidden" name="csrf" value="<?=csrf()?>">
                      <input type="hidden" name="appointment_id" value="<?=e($p['id'])?>">
                      <button name="action" value="reject"
                              class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-red-600 to-pink-600 text-white rounded-lg font-medium hover:from-red-700 hover:to-pink-700 transition-all duration-300 hover:scale-105 shadow-lg">
                        <i data-lucide="x" class="w-4 h-4"></i>
                        Reject
                      </button>
                    </form>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
    <?php endif; ?>
  </main>

  <script>lucide.createIcons();</script>
</body>
</html>
