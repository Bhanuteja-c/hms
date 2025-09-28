<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('patient');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$pid = current_user_id();

// Fetch completed appointments for this patient
$stmt = $pdo->prepare("
  SELECT a.*, u.name AS doctor_name, d.specialty
  FROM appointments a
  JOIN users u ON a.doctor_id = u.id
  JOIN doctors d ON u.id = d.id
  WHERE a.patient_id = :pid AND a.status = 'completed'
  ORDER BY a.date_time DESC
");
$stmt->execute([':pid' => $pid]);
$completed = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Completed Appointments - Patient</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-100">
  <?php include __DIR__ . '/../includes/sidebar_patient.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 max-w-5xl mx-auto">
    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
      <i data-lucide="check-circle" class="w-6 h-6 text-green-600"></i>
      Completed Appointments
    </h2>

    <?php if (!$completed): ?>
      <div class="bg-yellow-100 text-yellow-800 p-4 rounded">No completed appointments yet.</div>
    <?php else: ?>
      <table class="min-w-full bg-white rounded shadow overflow-hidden">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-4 py-2 text-left">Doctor</th>
            <th class="px-4 py-2">Specialty</th>
            <th class="px-4 py-2">Date/Time</th>
            <th class="px-4 py-2">Reason</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($completed as $a): ?>
          <tr class="border-t">
            <td class="px-4 py-2"><?= e($a['doctor_name']) ?></td>
            <td class="px-4 py-2"><?= e($a['specialty']) ?></td>
            <td class="px-4 py-2"><?= e(format_datetime($a['date_time'])) ?></td>
            <td class="px-4 py-2"><?= e($a['reason']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </main>

  <script>lucide.createIcons();</script>
</body>
</html>
