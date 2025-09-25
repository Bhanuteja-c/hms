<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('doctor');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$did = current_user_id();
$search = trim($_GET['search'] ?? '');

// Fetch treatments linked to doctor’s appointments
$sql = "
  SELECT t.*, a.date_time, u.name AS patient_name, u.email AS patient_email
  FROM treatments t
  JOIN appointments a ON t.appointment_id=a.id
  JOIN users u ON a.patient_id=u.id
  WHERE a.doctor_id=:did
";
$params = [':did'=>$did];

if ($search !== '') {
    $sql .= " AND (u.name LIKE :s OR u.email LIKE :s OR t.treatment_name LIKE :s OR t.notes LIKE :s)";
    $params[':s'] = "%$search%";
}

$sql .= " ORDER BY t.date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$treatments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>View Treatments - Healsync</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-100">
  <?php include __DIR__ . '/../includes/sidebar_doctor.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300">
    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
      <i data-lucide="stethoscope" class="w-6 h-6 text-indigo-600"></i>
      My Treatments
    </h2>

    <!-- Search -->
    <form method="get" class="flex gap-2 mb-6 max-w-lg">
      <input type="text" name="search" value="<?=e($search)?>" placeholder="Search by patient, email, treatment, or notes"
             class="flex-grow border rounded px-3 py-2">
      <button class="px-4 py-2 bg-indigo-600 text-white rounded flex items-center gap-2">
        <i data-lucide="search" class="w-4 h-4"></i> Search
      </button>
    </form>

    <?php if ($treatments): ?>
      <div class="overflow-x-auto">
        <table class="w-full bg-white rounded shadow">
          <thead class="bg-gray-100">
            <tr>
              <th class="text-left p-3">Treatment Date</th>
              <th class="text-left p-3">Patient</th>
              <th class="text-left p-3">Treatment</th>
              <th class="text-left p-3">Notes</th>
              <th class="text-left p-3">Cost</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($treatments as $t): ?>
              <tr class="border-t hover:bg-gray-50">
                <td class="p-3"><?=date('d M Y', strtotime($t['date']))?></td>
                <td class="p-3">
                  <div class="font-medium"><?=e($t['patient_name'])?></div>
                  <div class="text-sm text-gray-500"><?=e($t['patient_email'])?></div>
                </td>
                <td class="p-3"><?=e($t['treatment_name'])?></td>
                <td class="p-3"><?=e($t['notes'])?></td>
                <td class="p-3">$<?=number_format($t['cost'], 2)?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-gray-500">No treatments found.</p>
    <?php endif; ?>
  </main>

  <script> lucide.createIcons(); </script>
</body>
</html>
