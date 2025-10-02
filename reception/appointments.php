<?php
// reception/appointments.php
require_once __DIR__ . '/../includes/auth.php';
require_role('receptionist');
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
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Appointments - Reception</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    .glass-effect {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    .filter-btn {
      transition: all 0.3s ease;
    }
    .filter-btn:hover {
      transform: translateY(-1px);
    }
  </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 min-h-screen">
  <?php include __DIR__ . '/../includes/sidebar_reception.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300">
    <!-- Header Section -->
    <div class="mb-8">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
          <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
            Appointments Management
          </h1>
          <p class="text-gray-600 mt-1">View and manage all patient appointments</p>
        </div>
        
        <!-- Filter Tabs -->
        <div class="flex flex-wrap gap-2">
          <a href="?filter=all" class="filter-btn px-4 py-2.5 rounded-xl font-medium text-sm transition-all duration-300 <?= $filter==='all' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg' : 'glass-effect text-gray-700 hover:bg-white/50' ?>">
            <i data-lucide="list" class="w-4 h-4 inline mr-2"></i>All
          </a>
          <a href="?filter=today" class="filter-btn px-4 py-2.5 rounded-xl font-medium text-sm transition-all duration-300 <?= $filter==='today' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg' : 'glass-effect text-gray-700 hover:bg-white/50' ?>">
            <i data-lucide="calendar-days" class="w-4 h-4 inline mr-2"></i>Today
          </a>
          <a href="?filter=upcoming" class="filter-btn px-4 py-2.5 rounded-xl font-medium text-sm transition-all duration-300 <?= $filter==='upcoming' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg' : 'glass-effect text-gray-700 hover:bg-white/50' ?>">
            <i data-lucide="calendar-plus" class="w-4 h-4 inline mr-2"></i>Upcoming
          </a>
          <a href="?filter=past" class="filter-btn px-4 py-2.5 rounded-xl font-medium text-sm transition-all duration-300 <?= $filter==='past' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg' : 'glass-effect text-gray-700 hover:bg-white/50' ?>">
            <i data-lucide="calendar-x" class="w-4 h-4 inline mr-2"></i>Past
          </a>
        </div>
      </div>
    </div>

    <!-- Appointments List -->
    <div class="glass-effect rounded-2xl border border-white/20 overflow-hidden">
      <div class="p-6 border-b border-gray-200/50">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center">
              <i data-lucide="calendar" class="w-5 h-5 text-white"></i>
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-900">
                <?php 
                $filterTitles = [
                  'all' => 'All Appointments',
                  'today' => "Today's Appointments", 
                  'upcoming' => 'Upcoming Appointments',
                  'past' => 'Past Appointments'
                ];
                echo $filterTitles[$filter] ?? 'Appointments';
                ?>
              </h3>
              <p class="text-gray-600 text-sm">Manage patient appointments and schedules</p>
            </div>
          </div>
          <div class="text-right">
            <p class="text-2xl font-bold text-gray-900"><?= count($appointments) ?></p>
            <p class="text-gray-600 text-sm">Total found</p>
          </div>
        </div>
      </div>

      <?php if (!$appointments): ?>
        <div class="p-12 text-center">
          <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i data-lucide="calendar-x" class="w-8 h-8 text-gray-400"></i>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No appointments found</h3>
          <p class="text-gray-600">No appointments match the selected filter criteria.</p>
        </div>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date & Time</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Patient</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Doctor</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Reason</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <?php foreach ($appointments as $a): ?>
                <tr class="hover:bg-gray-50/50 transition-colors duration-200">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="w-2 h-2 bg-indigo-500 rounded-full mr-3"></div>
                      <div>
                        <div class="text-sm font-medium text-gray-900"><?= e(date('d M Y', strtotime($a['date_time']))) ?></div>
                        <div class="text-sm text-gray-500"><?= e(date('H:i', strtotime($a['date_time']))) ?></div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-full flex items-center justify-center mr-3">
                        <span class="text-white text-xs font-semibold"><?= e(strtoupper(substr($a['patient_name'], 0, 1))) ?></span>
                      </div>
                      <div>
                        <div class="text-sm font-medium text-gray-900"><?= e($a['patient_name']) ?></div>
                        <div class="text-sm text-gray-500"><?= e($a['patient_email']) ?></div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-emerald-500 rounded-full flex items-center justify-center mr-3">
                        <i data-lucide="stethoscope" class="w-4 h-4 text-white"></i>
                      </div>
                      <span class="text-sm font-medium text-gray-900"><?= e($a['doctor_name']) ?></span>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-gray-900 max-w-xs truncate" title="<?= e($a['reason']) ?>">
                      <?= e($a['reason'] ?: 'No reason provided') ?>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <?php
                    $statusColors = [
                      'pending' => 'bg-yellow-100 text-yellow-800',
                      'approved' => 'bg-green-100 text-green-800', 
                      'rejected' => 'bg-red-100 text-red-800',
                      'completed' => 'bg-blue-100 text-blue-800',
                      'cancelled' => 'bg-gray-100 text-gray-800'
                    ];
                    $colorClass = $statusColors[$a['status']] ?? 'bg-gray-100 text-gray-800';
                    ?>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= $colorClass ?>">
                      <?= e(ucfirst($a['status'])) ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <script>lucide.createIcons();</script>
</body>
</html>
