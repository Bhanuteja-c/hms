<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('doctor');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$did = current_user_id();
$search = trim($_GET['search'] ?? '');

// Fetch prescriptions linked to doctor’s appointments
$sql = "
  SELECT p.*, a.date_time, u.name AS patient_name, u.email AS patient_email
  FROM prescriptions p
  JOIN appointments a ON p.appointment_id=a.id
  JOIN users u ON a.patient_id=u.id
  WHERE a.doctor_id=:did
";
$params = [':did'=>$did];

if ($search !== '') {
    $sql .= " AND (u.name LIKE :s OR u.email LIKE :s OR p.medicine LIKE :s)";
    $params[':s'] = "%$search%";
}

$sql .= " ORDER BY a.date_time DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>View Prescriptions - Healsync</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-100">
  <?php include __DIR__ . '/../includes/sidebar_doctor.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300">
    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
      <i data-lucide="pill" class="w-6 h-6 text-indigo-600"></i>
      My Prescriptions
    </h2>

    <!-- Search -->
    <form method="get" class="flex gap-2 mb-6 max-w-lg">
      <input type="text" name="search" value="<?=e($search)?>" placeholder="Search by patient, email, or medicine"
             class="flex-grow border rounded px-3 py-2">
      <button class="px-4 py-2 bg-indigo-600 text-white rounded flex items-center gap-2">
        <i data-lucide="search" class="w-4 h-4"></i> Search
      </button>
    </form>

    <?php if ($prescriptions): ?>
      <div class="overflow-x-auto">
        <table class="w-full bg-white rounded shadow">
          <thead class="bg-gray-100">
            <tr>
              <th class="text-left p-3">Date</th>
              <th class="text-left p-3">Patient</th>
              <th class="text-left p-3">Medicine</th>
              <th class="text-left p-3">Dosage</th>
              <th class="text-left p-3">Duration</th>
              <th class="text-left p-3">Instructions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($prescriptions as $p): ?>
              <tr class="border-t hover:bg-gray-50">
                <td class="p-3"><?=date('d M Y H:i', strtotime($p['date_time']))?></td>
                <td class="p-3">
                  <div class="font-medium"><?=e($p['patient_name'])?></div>
                  <div class="text-sm text-gray-500"><?=e($p['patient_email'])?></div>
                </td>
                <td class="p-3"><?=e($p['medicine'])?></td>
                <td class="p-3"><?=e($p['dosage'])?></td>
                <td class="p-3"><?=e($p['duration'])?></td>
                <td class="p-3"><?=e($p['instructions'])?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-gray-500">No prescriptions found.</p>
    <?php endif; ?>
  </main>

  <script> lucide.createIcons(); </script>
</body>
</html>
