<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('doctor');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$did = current_user_id();

// Handle mark completed
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        die("Invalid CSRF token.");
    }

    $apptId = intval($_POST['appointment_id'] ?? 0);

    // Ensure appointment belongs to this doctor & is approved
    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id=:id AND doctor_id=:did AND status='approved'");
    $stmt->execute([':id' => $apptId, ':did' => $did]);
    $appt = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($appt) {
        // Update status → completed
        $pdo->prepare("UPDATE appointments SET status='completed' WHERE id=:id")->execute([':id' => $apptId]);

        // ✅ Notify patient
        $msg = "Your appointment on " . date('d M Y H:i', strtotime($appt['date_time'])) . " has been marked as completed.";
        $pdo->prepare("INSERT INTO notifications (user_id,message,link) VALUES (:uid,:msg,:link)")
            ->execute([
                ':uid'  => $appt['patient_id'],
                ':msg'  => $msg,
                ':link' => "/healsync/patient/appointments.php"
            ]);

        header("Location: approved_appointments.php?status=completed");
        exit;
    }
}

// Fetch approved appointments
$stmt = $pdo->prepare("
  SELECT a.*, u.name AS patient_name, u.email AS patient_email
  FROM appointments a
  JOIN users u ON a.patient_id = u.id
  WHERE a.doctor_id = :did AND a.status = 'approved'
  ORDER BY a.date_time ASC
");
$stmt->execute([':did' => $did]);
$apps = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Approved Appointments - Doctor</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50">
  <?php include __DIR__ . '/../includes/sidebar_doctor.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 max-w-5xl mx-auto">
    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
      <i data-lucide="calendar-check" class="w-6 h-6 text-green-600"></i>
      Approved Appointments
    </h2>

    <?php if (!$apps): ?>
      <div class="bg-yellow-100 text-yellow-800 p-4 rounded">No approved appointments yet.</div>
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
          <?php foreach ($apps as $a): ?>
          <tr class="border-t">
            <td class="px-4 py-2">
              <div class="font-semibold"><?=e($a['patient_name'])?></div>
              <div class="text-sm text-gray-500"><?=e($a['patient_email'])?></div>
            </td>
            <td class="px-4 py-2"><?=e(format_datetime($a['date_time']))?></td>
            <td class="px-4 py-2"><?=e($a['reason'])?></td>
            <td class="px-4 py-2 space-x-2">
              <a href="add_prescription.php?appointment_id=<?=e($a['id'])?>"
                class="px-3 py-1 bg-blue-600 text-white rounded flex items-center gap-1 hover:bg-blue-700 text-sm">
                <i data-lucide="pill" class="w-4 h-4"></i> Prescription
              </a>
              <a href="add_treatment.php?appointment_id=<?=e($a['id'])?>"
                class="px-3 py-1 bg-green-600 text-white rounded flex items-center gap-1 hover:bg-green-700 text-sm">
                <i data-lucide="stethoscope" class="w-4 h-4"></i> Treatment
              </a>
              <form method="post" class="inline">
                <input type="hidden" name="csrf" value="<?=csrf()?>">
                <input type="hidden" name="appointment_id" value="<?=e($a['id'])?>">
                <button type="submit"
                  class="px-3 py-1 bg-indigo-600 text-white rounded flex items-center gap-1 hover:bg-indigo-700 text-sm">
                  <i data-lucide="check-circle" class="w-4 h-4"></i> Mark Completed
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
