<?php
// reception/appointments.php
require_once __DIR__ . '/../includes/auth.php';
require_role('reception');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Filter: all | today | upcoming | past
$filter = $_GET['filter'] ?? 'all';
$where = "1=1";

if ($filter === 'today') {
    $where = "DATE(a.date_time) = CURDATE()";
} elseif ($filter === 'upcoming') {
    $where = "a.date_time >= NOW()";
} elseif ($filter === 'past') {
    $where = "a.date_time < NOW()";
}

// Fetch appointments
$stmt = $pdo->query("
    SELECT a.*, 
           p.name AS patient_name, p.email AS patient_email,
           d.name AS doctor_name
    FROM appointments a
    JOIN users p ON a.patient_id = p.id
    JOIN users d ON a.doctor_id = d.id
    WHERE $where
    ORDER BY a.date_time DESC
");
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Appointments - Reception</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50">
  <?php include __DIR__ . '/../includes/sidebar_reception.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 max-w-6xl mx-auto transition-all">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold flex items-center gap-2">
        <i data-lucide="calendar" class="w-6 h-6 text-indigo-600"></i>
        Appointments
      </h2>
      <!-- Filters -->
      <div class="space-x-2 text-sm">
        <a href="?filter=all" class="px-3 py-1 rounded <?= $filter==='all'?'bg-indigo-600 text-white':'bg-gray-200 hover:bg-indigo-100' ?>">All</a>
        <a href="?filter=today" class="px-3 py-1 rounded <?= $filter==='today'?'bg-indigo-600 text-white':'bg-gray-200 hover:bg-indigo-100' ?>">Today</a>
        <a href="?filter=upcoming" class="px-3 py-1 rounded <?= $filter==='upcoming'?'bg-indigo-600 text-white':'bg-gray-200 hover:bg-indigo-100' ?>">Upcoming</a>
        <a href="?filter=past" class="px-3 py-1 rounded <?= $filter==='past'?'bg-indigo-600 text-white':'bg-gray-200 hover:bg-indigo-100' ?>">Past</a>
      </div>
    </div>

    <?php if (!$appointments): ?>
      <p class="text-gray-500">No appointments found for this filter.</p>
    <?php else: ?>
      <div class="overflow-x-auto bg-white rounded shadow">
        <table class="w-full">
          <thead class="bg-gray-100 text-left">
            <tr>
              <th class="p-3">Date & Time</th>
              <th class="p-3">Patient</th>
              <th class="p-3">Doctor</th>
              <th class="p-3">Reason</th>
              <th class="p-3">Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($appointments as $a): ?>
              <tr class="border-t hover:bg-gray-50">
                <td class="p-3"><?= date('d M Y H:i', strtotime($a['date_time'])) ?></td>
                <td class="p-3">
                  <div class="font-medium"><?= e($a['patient_name']) ?></div>
                  <div class="text-sm text-gray-500"><?= e($a['patient_email']) ?></div>
                </td>
                <td class="p-3"><?= e($a['doctor_name']) ?></td>
                <td class="p-3"><?= e($a['reason']) ?></td>
                <td class="p-3">
                  <span class="px-2 py-1 text-xs rounded-full
                    <?= $a['status']==='approved' ? 'bg-green-100 text-green-700' : 
                       ($a['status']==='pending' ? 'bg-yellow-100 text-yellow-700' : 
                       ($a['status']==='rejected' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700')) ?>">
                    <?= ucfirst($a['status']) ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </main>

  <script>lucide.createIcons();</script>
</body>
</html>
