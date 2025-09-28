<?php
// patient/appointments.php
require_once __DIR__ . '/../includes/auth.php';
require_role('patient');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$pid = current_user_id();
$statusMsg = $_GET['status'] ?? "";
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>My Appointments - Healsync</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-100">
  <?php include __DIR__ . '/../includes/sidebar_patient.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300">
    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
      <i data-lucide="calendar" class="w-6 h-6 text-indigo-600"></i>
      My Appointments
    </h2>

    <?php if ($statusMsg === 'cancelled'): ?>
      <div class="bg-green-50 border border-green-200 text-green-700 p-3 rounded mb-4">
        Appointment cancelled successfully.
      </div>
    <?php endif; ?>

    <?php
    $stmt = $pdo->prepare("
        SELECT a.*, u.name AS doctor_name, d.specialty
        FROM appointments a
        JOIN users u ON a.doctor_id=u.id
        JOIN doctors d ON u.id=d.id
        WHERE a.patient_id=:pid
        ORDER BY a.date_time DESC
    ");
    $stmt->execute([':pid'=>$pid]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <?php if ($appointments): ?>
      <div class="overflow-x-auto">
        <table class="w-full bg-white rounded shadow">
          <thead class="bg-gray-100">
            <tr>
              <th class="text-left p-3">Date & Time</th>
              <th class="text-left p-3">Doctor</th>
              <th class="text-left p-3">Specialty</th>
              <th class="text-left p-3">Reason</th>
              <th class="text-left p-3">Status</th>
              <th class="text-left p-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($appointments as $a): ?>
              <tr class="border-t hover:bg-gray-50">
                <td class="p-3"><?= date('d M Y H:i', strtotime($a['date_time'])) ?></td>
                <td class="p-3"><?= e($a['doctor_name']) ?></td>
                <td class="p-3"><?= e($a['specialty']) ?></td>
                <td class="p-3"><?= e($a['reason']) ?></td>
                <td class="p-3">
                  <?php if ($a['status']==='pending'): ?>
                    <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded-full flex items-center gap-1 w-fit">
                      <i data-lucide="clock" class="w-3 h-3"></i> Pending
                    </span>
                  <?php elseif ($a['status']==='approved'): ?>
                    <span class="px-2 py-1 text-xs bg-green-100 text-green-600 rounded-full flex items-center gap-1 w-fit">
                      <i data-lucide="check-circle" class="w-3 h-3"></i> Approved
                    </span>
                  <?php elseif ($a['status']==='rejected'): ?>
                    <span class="px-2 py-1 text-xs bg-red-100 text-red-600 rounded-full flex items-center gap-1 w-fit">
                      <i data-lucide="x-circle" class="w-3 h-3"></i> Rejected
                    </span>
                  <?php elseif ($a['status']==='cancelled'): ?>
                    <span class="px-2 py-1 text-xs bg-gray-200 text-gray-600 rounded-full flex items-center gap-1 w-fit">
                      <i data-lucide="ban" class="w-3 h-3"></i> Cancelled
                    </span>
                  <?php else: ?>
                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-600 rounded-full flex items-center gap-1 w-fit">
                      <i data-lucide="check" class="w-3 h-3"></i> Completed
                    </span>
                  <?php endif; ?>
                </td>
                <td class="p-3">
                  <?php if (in_array($a['status'], ['pending','approved']) && strtotime($a['date_time']) > time()): ?>
                    <form method="post" action="cancel_appointment.php" onsubmit="return confirm('Cancel this appointment?');">
                      <input type="hidden" name="csrf" value="<?= csrf() ?>">
                      <input type="hidden" name="appointment_id" value="<?= $a['id'] ?>">
                      <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded text-sm flex items-center gap-1 hover:bg-red-700 transition">
                        <i data-lucide="trash-2" class="w-4 h-4"></i> Cancel
                      </button>
                    </form>
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
      <p class="text-gray-500">No appointments found.</p>
    <?php endif; ?>
  </main>

  <script>lucide.createIcons();</script>
</body>
</html>
