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
<html>
<head>
  <meta charset="utf-8">
  <title>Pending Appointments - Doctor</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50">
  <?php include __DIR__ . '/../includes/sidebar_doctor.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 max-w-5xl mx-auto">
    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
      <i data-lucide="clock" class="w-6 h-6 text-indigo-600"></i>
      Pending Appointments
    </h2>

    <?php if (!$pending): ?>
      <div class="bg-yellow-100 text-yellow-800 p-4 rounded">No pending appointments.</div>
    <?php else: ?>
      <table class="min-w-full bg-white rounded shadow overflow-hidden">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-4 py-2 text-left">Patient</th>
            <th class="px-4 py-2">Date/Time</th>
            <th class="px-4 py-2">Reason</th>
            <th class="px-4 py-2">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($pending as $p): ?>
          <tr class="border-t hover:bg-gray-50">
            <td class="px-4 py-2">
              <div class="font-semibold"><?=e($p['patient_name'])?></div>
              <div class="text-sm text-gray-500"><?=e($p['patient_email'])?></div>
            </td>
            <td class="px-4 py-2"><?=e(format_datetime($p['date_time']))?></td>
            <td class="px-4 py-2"><?=e($p['reason'])?></td>
            <td class="px-4 py-2">
              <form method="post" class="flex gap-2">
                <input type="hidden" name="csrf" value="<?=csrf()?>">
                <input type="hidden" name="appointment_id" value="<?=e($p['id'])?>">
                <button name="action" value="approve"
                  class="px-3 py-1 bg-green-600 text-white rounded flex items-center gap-1 hover:bg-green-700">
                  <i data-lucide="check" class="w-4 h-4"></i> Approve
                </button>
                <button name="action" value="reject"
                  class="px-3 py-1 bg-red-600 text-white rounded flex items-center gap-1 hover:bg-red-700">
                  <i data-lucide="x" class="w-4 h-4"></i> Reject
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </main>

  <script>lucide.createIcons();</script>
</body>
</html>
