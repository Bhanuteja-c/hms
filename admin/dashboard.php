<?php
// admin/dashboard.php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Basic Stats
$totalDoctors = $pdo->query("SELECT COUNT(*) FROM users WHERE role='doctor'")->fetchColumn();
$totalPatients = $pdo->query("SELECT COUNT(*) FROM users WHERE role='patient'")->fetchColumn();
$totalReceptionists = $pdo->query("SELECT COUNT(*) FROM users WHERE role='receptionist'")->fetchColumn();
$totalAppointments = $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();

// Financial Stats
$totalBills = $pdo->query("SELECT SUM(total_amount) FROM bills")->fetchColumn() ?: 0;
$paidBills = $pdo->query("SELECT SUM(total_amount) FROM bills WHERE status='paid'")->fetchColumn() ?: 0;
$unpaidBills = $pdo->query("SELECT SUM(total_amount) FROM bills WHERE status='unpaid'")->fetchColumn() ?: 0;

// Today's Stats
$todayAppointments = $pdo->query("SELECT COUNT(*) FROM appointments WHERE DATE(date_time) = CURDATE()")->fetchColumn();
$todayRevenue = $pdo->query("SELECT SUM(total_amount) FROM bills WHERE DATE(paid_at) = CURDATE() AND status='paid'")->fetchColumn() ?: 0;
$pendingAppointments = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status='pending'")->fetchColumn();

// Recent Activity
$recentAppointments = $pdo->query("
    SELECT a.*, u1.name as patient_name, u2.name as doctor_name, a.status
    FROM appointments a
    JOIN users u1 ON a.patient_id = u1.id
    JOIN users u2 ON a.doctor_id = u2.id
    ORDER BY a.created_at DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

$recentPatients = $pdo->query("
    SELECT id, name, email, created_at
    FROM users 
    WHERE role='patient' 
    ORDER BY created_at DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Additional stats for dashboard
$completedToday = $pdo->query("SELECT COUNT(*) FROM appointments WHERE DATE(date_time) = CURDATE() AND status='completed'")->fetchColumn();

// Top Doctors by Appointments
$topDoctors = $pdo->query("
    SELECT u.name, d.specialty, COUNT(a.id) as appointment_count
    FROM users u
    JOIN doctors d ON u.id = d.id
    LEFT JOIN appointments a ON u.id = a.doctor_id
    WHERE u.role='doctor'
    GROUP BY u.id, u.name, d.specialty
    ORDER BY appointment_count DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin Dashboard - Healsync</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-100">
  <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300">
    <div class="max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-8">
        <h2 class="text-3xl font-bold flex items-center gap-3">
          <i data-lucide="layout-dashboard" class="w-8 h-8 text-indigo-600"></i>
          Admin Dashboard
        </h2>
        <div class="text-sm text-gray-500">
          Last updated: <?= date('M d, Y H:i') ?>
        </div>
      </div>

      <!-- Today's Overview -->
      <div class="grid md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 rounded-xl text-white">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-blue-100">Today's Appointments</p>
              <p class="text-3xl font-bold"><?= $todayAppointments ?></p>
            </div>
            <i data-lucide="calendar" class="w-12 h-12 text-blue-200"></i>
          </div>
        </div>
        <div class="bg-gradient-to-r from-green-500 to-green-600 p-6 rounded-xl text-white">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-green-100">Today's Revenue</p>
              <p class="text-3xl font-bold">₹<?= number_format($todayRevenue, 0) ?></p>
            </div>
            <i data-lucide="dollar-sign" class="w-12 h-12 text-green-200"></i>
          </div>
        </div>
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 p-6 rounded-xl text-white">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-orange-100">Pending Appointments</p>
              <p class="text-3xl font-bold"><?= $pendingAppointments ?></p>
            </div>
            <i data-lucide="clock" class="w-12 h-12 text-orange-200"></i>
          </div>
        </div>
      </div>

      <!-- Main Stats Grid -->
      <div class="grid md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border-l-4 border-indigo-500">
          <div class="flex items-center gap-4">
            <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
              <i data-lucide="user-plus" class="w-6 h-6"></i>
            </div>
            <div>
              <p class="text-gray-500 text-sm">Total Doctors</p>
              <p class="text-2xl font-bold text-gray-900"><?= $totalDoctors ?></p>
            </div>
          </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border-l-4 border-green-500">
          <div class="flex items-center gap-4">
            <div class="p-3 rounded-full bg-green-100 text-green-600">
              <i data-lucide="users" class="w-6 h-6"></i>
            </div>
            <div>
              <p class="text-gray-500 text-sm">Total Patients</p>
              <p class="text-2xl font-bold text-gray-900"><?= $totalPatients ?></p>
            </div>
          </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border-l-4 border-purple-500">
          <div class="flex items-center gap-4">
            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
              <i data-lucide="user-check" class="w-6 h-6"></i>
            </div>
            <div>
              <p class="text-gray-500 text-sm">Receptionists</p>
              <p class="text-2xl font-bold text-gray-900"><?= $totalReceptionists ?></p>
            </div>
          </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border-l-4 border-yellow-500">
          <div class="flex items-center gap-4">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
              <i data-lucide="calendar" class="w-6 h-6"></i>
            </div>
            <div>
              <p class="text-gray-500 text-sm">Total Appointments</p>
              <p class="text-2xl font-bold text-gray-900"><?= $totalAppointments ?></p>
            </div>
          </div>
        </div>
      </div>

      <!-- Financial Overview -->
      <div class="grid md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow-lg">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Total Revenue</h3>
            <i data-lucide="trending-up" class="w-5 h-5 text-green-500"></i>
          </div>
          <p class="text-3xl font-bold text-green-600">₹<?= number_format($paidBills, 2) ?></p>
          <p class="text-sm text-gray-500 mt-2">From <?= $totalAppointments ?> appointments</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Outstanding Bills</h3>
            <i data-lucide="alert-circle" class="w-5 h-5 text-red-500"></i>
          </div>
          <p class="text-3xl font-bold text-red-600">₹<?= number_format($unpaidBills, 2) ?></p>
          <p class="text-sm text-gray-500 mt-2">Requires attention</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Collection Rate</h3>
            <i data-lucide="percent" class="w-5 h-5 text-blue-500"></i>
          </div>
          <p class="text-3xl font-bold text-blue-600">
            <?= $totalBills > 0 ? number_format(($paidBills / $totalBills) * 100, 1) : 0 ?>%
          </p>
          <p class="text-sm text-gray-500 mt-2">Payment success rate</p>
        </div>
      </div>

      <!-- Quick Stats and Recent Activity -->
      <div class="grid lg:grid-cols-2 gap-8 mb-8">
        <!-- Recent Activity Summary -->
        <div class="bg-white p-6 rounded-xl shadow-lg">
          <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <i data-lucide="activity" class="w-5 h-5 text-indigo-600"></i>
            Today's Activity Summary
          </h3>
          <div class="space-y-4">
            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
              <div class="flex items-center gap-3">
                <i data-lucide="calendar-check" class="w-5 h-5 text-blue-600"></i>
                <span class="font-medium text-gray-900">Completed Today</span>
              </div>
              <span class="text-2xl font-bold text-blue-600">
                <?= $pdo->query("SELECT COUNT(*) FROM appointments WHERE DATE(date_time) = CURDATE() AND status='completed'")->fetchColumn() ?>
              </span>
            </div>
            <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
              <div class="flex items-center gap-3">
                <i data-lucide="dollar-sign" class="w-5 h-5 text-green-600"></i>
                <span class="font-medium text-gray-900">Revenue Today</span>
              </div>
              <span class="text-2xl font-bold text-green-600">
                ₹<?= number_format($todayRevenue, 0) ?>
              </span>
            </div>
            <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
              <div class="flex items-center gap-3">
                <i data-lucide="clock" class="w-5 h-5 text-yellow-600"></i>
                <span class="font-medium text-gray-900">Pending Approval</span>
              </div>
              <span class="text-2xl font-bold text-yellow-600">
                <?= $pendingAppointments ?>
              </span>
            </div>
          </div>
        </div>

        <!-- Appointment Status Overview -->
        <div class="bg-white p-6 rounded-xl shadow-lg">
          <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <i data-lucide="pie-chart" class="w-5 h-5 text-indigo-600"></i>
            Appointment Status Overview
          </h3>
          <div class="space-y-3">
            <?php
            $statusCounts = $pdo->query("
              SELECT 
                CASE 
                  WHEN status IS NULL OR status = '' THEN 'unknown'
                  ELSE status 
                END as status, 
                COUNT(*) as count 
              FROM appointments 
              GROUP BY status 
              ORDER BY count DESC
            ")->fetchAll(PDO::FETCH_ASSOC);
            
            $statusColors = [
              'completed' => 'bg-green-100 text-green-800',
              'approved' => 'bg-blue-100 text-blue-800', 
              'pending' => 'bg-yellow-100 text-yellow-800',
              'cancelled' => 'bg-red-100 text-red-800',
              'rejected' => 'bg-gray-100 text-gray-800'
            ];
            
            foreach ($statusCounts as $status): 
              $colorClass = $statusColors[$status['status']] ?? 'bg-gray-100 text-gray-800';
            ?>
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <div class="flex items-center gap-3">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $colorClass ?>">
                  <?= ucfirst($status['status']) ?>
                </span>
              </div>
              <span class="text-xl font-bold text-gray-900"><?= $status['count'] ?></span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Recent Activity and Top Doctors -->
      <div class="grid lg:grid-cols-2 gap-8 mb-8">
        <!-- Recent Appointments -->
        <div class="bg-white rounded-xl shadow-lg">
          <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
              <i data-lucide="activity" class="w-5 h-5 text-indigo-600"></i>
              Recent Appointments
            </h3>
          </div>
          <div class="p-6">
            <?php if ($recentAppointments): ?>
              <div class="space-y-4">
                <?php foreach (array_slice($recentAppointments, 0, 5) as $appt): ?>
                  <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                      <p class="font-medium text-gray-900"><?= e($appt['patient_name']) ?></p>
                      <p class="text-sm text-gray-500">with Dr. <?= e($appt['doctor_name']) ?></p>
                    </div>
                    <div class="text-right">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        <?= $appt['status'] === 'completed' ? 'bg-green-100 text-green-800' : 
                           ($appt['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                           ($appt['status'] === 'approved' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800')) ?>">
                        <?= ucfirst($appt['status']) ?>
                      </span>
                      <p class="text-xs text-gray-500 mt-1"><?= format_datetime($appt['date_time']) ?></p>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <p class="text-gray-500 text-center py-8">No recent appointments</p>
            <?php endif; ?>
          </div>
        </div>

        <!-- Top Doctors -->
        <div class="bg-white rounded-xl shadow-lg">
          <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
              <i data-lucide="award" class="w-5 h-5 text-yellow-600"></i>
              Top Doctors
            </h3>
          </div>
          <div class="p-6">
            <?php if ($topDoctors): ?>
              <div class="space-y-4">
                <?php foreach ($topDoctors as $index => $doctor): ?>
                  <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center gap-3">
                      <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                        <span class="text-sm font-bold text-indigo-600"><?= $index + 1 ?></span>
                      </div>
                      <div>
                        <p class="font-medium text-gray-900"><?= e($doctor['name']) ?></p>
                        <p class="text-sm text-gray-500"><?= e($doctor['specialty']) ?></p>
                      </div>
                    </div>
                    <div class="text-right">
                      <p class="font-semibold text-gray-900"><?= $doctor['appointment_count'] ?></p>
                      <p class="text-xs text-gray-500">appointments</p>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <p class="text-gray-500 text-center py-8">No doctor data available</p>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center gap-2">
          <i data-lucide="zap" class="w-5 h-5 text-indigo-600"></i>
          Quick Actions
        </h3>
        <div class="grid md:grid-cols-4 gap-6">
          <a href="manage_doctors.php"
             class="group bg-gradient-to-r from-indigo-50 to-indigo-100 hover:from-indigo-100 hover:to-indigo-200 p-6 rounded-xl flex flex-col items-center gap-3 shadow transition-all duration-300 hover:shadow-lg">
            <i data-lucide="user-plus" class="w-8 h-8 text-indigo-600 group-hover:scale-110 transition-transform"></i>
            <span class="font-medium text-gray-900">Manage Doctors</span>
            <span class="text-sm text-gray-500">Add, edit, or remove doctors</span>
          </a>
          <a href="manage_patients.php"
             class="group bg-gradient-to-r from-green-50 to-green-100 hover:from-green-100 hover:to-green-200 p-6 rounded-xl flex flex-col items-center gap-3 shadow transition-all duration-300 hover:shadow-lg">
            <i data-lucide="users" class="w-8 h-8 text-green-600 group-hover:scale-110 transition-transform"></i>
            <span class="font-medium text-gray-900">Manage Patients</span>
            <span class="text-sm text-gray-500">View and manage patient records</span>
          </a>
          <a href="manage_reception.php"
             class="group bg-gradient-to-r from-purple-50 to-purple-100 hover:from-purple-100 hover:to-purple-200 p-6 rounded-xl flex flex-col items-center gap-3 shadow transition-all duration-300 hover:shadow-lg">
            <i data-lucide="user-check" class="w-8 h-8 text-purple-600 group-hover:scale-110 transition-transform"></i>
            <span class="font-medium text-gray-900">Manage Reception</span>
            <span class="text-sm text-gray-500">Manage reception staff</span>
          </a>
          <a href="reports.php"
             class="group bg-gradient-to-r from-orange-50 to-orange-100 hover:from-orange-100 hover:to-orange-200 p-6 rounded-xl flex flex-col items-center gap-3 shadow transition-all duration-300 hover:shadow-lg">
            <i data-lucide="bar-chart-2" class="w-8 h-8 text-orange-600 group-hover:scale-110 transition-transform"></i>
            <span class="font-medium text-gray-900">Reports</span>
            <span class="text-sm text-gray-500">View detailed analytics</span>
          </a>
        </div>
      </div>
    </div>
  </main>

  <script>
    lucide.createIcons();
  </script>
</body>
</html>
