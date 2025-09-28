<?php
// doctor/view_treatments.php
require_once __DIR__ . '/../includes/auth.php';
require_role('doctor');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$did = current_user_id();
$search = trim($_GET['search'] ?? '');

// Fetch treatments linked to doctor’s appointments
$sql = "
  SELECT t.*, a.date_time,
         u.name AS patient_name, u.email AS patient_email
  FROM treatments t
  JOIN appointments a ON t.appointment_id = a.id
  JOIN users u ON a.patient_id = u.id
  WHERE a.doctor_id = :did
";
$params = [':did' => $did];

if ($search !== '') {
    $sql .= " AND (
      u.name LIKE :s OR 
      u.email LIKE :s OR 
      t.treatment_name LIKE :s OR 
      t.notes LIKE :s
    )";
    $params[':s'] = "%$search%";
}

$sql .= " ORDER BY a.date_time DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
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
      <input type="text" name="search" value="<?= e($search) ?>"
             placeholder="Search by patient, email, treatment, or notes"
             class="flex-grow border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-400">
      <button class="px-4 py-2 bg-indigo-600 text-white rounded flex items-center gap-2 hover:bg-indigo-700">
        <i data-lucide="search" class="w-4 h-4"></i> Search
      </button>
    </form>

    <?php if ($treatments): ?>
      <div class="overflow-x-auto bg-white rounded shadow">
        <table class="min-w-full border-collapse">
          <thead class="bg-gray-100 text-sm text-gray-700">
            <tr>
              <th class="px-4 py-2 text-left">Date</th>
              <th class="px-4 py-2 text-left">Patient</th>
              <th class="px-4 py-2 text-left">Treatment</th>
              <th class="px-4 py-2 text-left">Notes</th>
              <th class="px-4 py-2 text-left">Cost</th>
              <th class="px-4 py-2 text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($treatments as $t): ?>
              <tr class="border-t hover:bg-gray-50">
                <td class="px-4 py-2"><?= e(format_datetime($t['date_time'])) ?></td>
                <td class="px-4 py-2">
                  <div class="font-medium"><?= e($t['patient_name']) ?></div>
                  <div class="text-sm text-gray-500"><?= e($t['patient_email']) ?></div>
                </td>
                <td class="px-4 py-2 font-medium"><?= e($t['treatment_name']) ?></td>
                <td class="px-4 py-2 text-sm text-gray-600 max-w-xs truncate" title="<?= e($t['notes']) ?>">
                  <?= e($t['notes'] ?: '-') ?>
                </td>
                <td class="px-4 py-2 text-green-600 font-semibold">
                  ₹<?= number_format($t['cost'], 2) ?>
                </td>
                <td class="px-4 py-2 text-center">
                  <a href="treatment_pdf.php?id=<?= e($t['id']) ?>" target="_blank"
                     class="inline-flex items-center gap-1 px-3 py-1 bg-indigo-600 text-white rounded text-sm hover:bg-indigo-700 transition">
                    <i data-lucide="download" class="w-4 h-4"></i> PDF
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 p-4 rounded flex items-center gap-2">
        <i data-lucide="info" class="w-5 h-5"></i>
        <span>No treatments found.</span>
      </div>
    <?php endif; ?>
  </main>

  <script>lucide.createIcons();</script>
</body>
</html>
