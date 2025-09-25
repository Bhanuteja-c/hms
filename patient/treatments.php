<?php
// patient/treatments.php
require_once __DIR__ . '/../includes/auth.php';
require_role('patient');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$pid = current_user_id();

// Fetch treatments with doctor + appointment info
$stmt = $pdo->prepare("
  SELECT t.*, a.date_time, u.name AS doctor_name
  FROM treatments t
  JOIN appointments a ON t.appointment_id = a.id
  JOIN users u ON a.doctor_id = u.id
  WHERE a.patient_id = :pid
  ORDER BY a.date_time DESC
");
$stmt->execute([':pid' => $pid]);
$treatments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>My Treatments - Healsync</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50">
  <?php include __DIR__ . '/../includes/sidebar_patient.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300">
    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
      <i data-lucide="stethoscope" class="w-6 h-6 text-indigo-600"></i>
      My Treatments
    </h2>

    <?php if (!$treatments): ?>
      <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 p-4 rounded flex items-center gap-2">
        <i data-lucide="info" class="w-5 h-5"></i>
        <span>You don’t have any treatments yet.</span>
      </div>
    <?php else: ?>
      <div class="overflow-x-auto bg-white rounded shadow">
        <table class="min-w-full border-collapse">
          <thead class="bg-gray-100 text-gray-700 text-sm">
            <tr>
              <th class="px-4 py-2 text-left">Date</th>
              <th class="px-4 py-2 text-left">Doctor</th>
              <th class="px-4 py-2 text-left">Treatment</th>
              <th class="px-4 py-2 text-left">Notes</th>
              <th class="px-4 py-2 text-left">Cost</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($treatments as $t): ?>
              <tr class="border-t hover:bg-gray-50">
                <td class="px-4 py-2"><?= e(format_datetime($t['date_time'])) ?></td>
                <td class="px-4 py-2"><?= e($t['doctor_name']) ?></td>
                <td class="px-4 py-2 font-medium"><?= e($t['treatment_name']) ?></td>
                <td class="px-4 py-2 text-sm text-gray-600"><?= e($t['notes']) ?></td>
                <td class="px-4 py-2 text-green-600 font-semibold">
                  $<?= number_format($t['cost'], 2) ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </main>

  <script> lucide.createIcons(); </script>
</body>
</html>
