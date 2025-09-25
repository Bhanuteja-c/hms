<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Stats
$totalDoctors = $pdo->query("SELECT COUNT(*) FROM users WHERE role='doctor'")->fetchColumn();
$totalPatients = $pdo->query("SELECT COUNT(*) FROM users WHERE role='patient'")->fetchColumn();
$totalAppointments = $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
$totalBills = $pdo->query("SELECT SUM(total_amount) FROM bills")->fetchColumn() ?: 0;
$paidBills = $pdo->query("SELECT SUM(total_amount) FROM bills WHERE status='paid'")->fetchColumn() ?: 0;
$unpaidBills = $pdo->query("SELECT SUM(total_amount) FROM bills WHERE status='unpaid'")->fetchColumn() ?: 0;
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
    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
      <i data-lucide="layout-dashboard" class="w-6 h-6 text-indigo-600"></i>
      Admin Dashboard
    </h2>

    <!-- Quick Stats -->
    <div class="grid md:grid-cols-4 gap-6 mb-10">
      <div class="bg-white p-6 rounded-xl shadow flex items-center gap-4 hover:shadow-lg transition">
        <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
          <i data-lucide="user-plus" class="w-6 h-6"></i>
        </div>
        <div>
          <p class="text-gray-500">Doctors</p>
          <p class="text-2xl font-bold"><?= $totalDoctors ?></p>
        </div>
      </div>
      <div class="bg-white p-6 rounded-xl shadow flex items-center gap-4 hover:shadow-lg transition">
        <div class="p-3 rounded-full bg-green-100 text-green-600">
          <i data-lucide="users" class="w-6 h-6"></i>
        </div>
        <div>
          <p class="text-gray-500">Patients</p>
          <p class="text-2xl font-bold"><?= $totalPatients ?></p>
        </div>
      </div>
      <div class="bg-white p-6 rounded-xl shadow flex items-center gap-4 hover:shadow-lg transition">
        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
          <i data-lucide="calendar" class="w-6 h-6"></i>
        </div>
        <div>
          <p class="text-gray-500">Appointments</p>
          <p class="text-2xl font-bold"><?= $totalAppointments ?></p>
        </div>
      </div>
      <div class="bg-white p-6 rounded-xl shadow flex items-center gap-4 hover:shadow-lg transition">
        <div class="p-3 rounded-full bg-red-100 text-red-600">
          <i data-lucide="credit-card" class="w-6 h-6"></i>
        </div>
        <div>
          <p class="text-gray-500">Revenue</p>
          <p class="text-2xl font-bold">$<?= number_format($paidBills,2) ?></p>
        </div>
      </div>
    </div>

    <!-- Billing Breakdown -->
    <div class="grid md:grid-cols-2 gap-6 mb-10">
      <div class="bg-white p-6 rounded-xl shadow flex items-center gap-4 hover:shadow-lg transition">
        <div class="p-3 rounded-full bg-green-100 text-green-600">
          <i data-lucide="check-circle" class="w-6 h-6"></i>
        </div>
        <div>
          <p class="text-gray-500">Paid Bills</p>
          <p class="text-2xl font-bold">$<?= number_format($paidBills,2) ?></p>
        </div>
      </div>
      <div class="bg-white p-6 rounded-xl shadow flex items-center gap-4 hover:shadow-lg transition">
        <div class="p-3 rounded-full bg-red-100 text-red-600">
          <i data-lucide="x-circle" class="w-6 h-6"></i>
        </div>
        <div>
          <p class="text-gray-500">Unpaid Bills</p>
          <p class="text-2xl font-bold">$<?= number_format($unpaidBills,2) ?></p>
        </div>
      </div>
    </div>

    <!-- Quick Links -->
    <div>
      <h3 class="text-xl font-semibold mb-4 flex items-center gap-2">
        <i data-lucide="zap" class="w-5 h-5 text-indigo-600"></i>
        Quick Links
      </h3>
      <div class="grid md:grid-cols-4 gap-6">
        <a href="manage_doctors.php"
           class="bg-indigo-50 hover:bg-indigo-100 p-4 rounded-xl flex flex-col items-center gap-2 shadow transition">
          <i data-lucide="user-plus" class="w-6 h-6 text-indigo-600"></i>
          <span class="font-medium">Manage Doctors</span>
        </a>
        <a href="manage_patients.php"
           class="bg-indigo-50 hover:bg-indigo-100 p-4 rounded-xl flex flex-col items-center gap-2 shadow transition">
          <i data-lucide="users" class="w-6 h-6 text-indigo-600"></i>
          <span class="font-medium">Manage Patients</span>
        </a>
        <a href="manage_treatments.php"
           class="bg-indigo-50 hover:bg-indigo-100 p-4 rounded-xl flex flex-col items-center gap-2 shadow transition">
          <i data-lucide="stethoscope" class="w-6 h-6 text-indigo-600"></i>
          <span class="font-medium">Manage Treatments</span>
        </a>
        <a href="reports.php"
           class="bg-indigo-50 hover:bg-indigo-100 p-4 rounded-xl flex flex-col items-center gap-2 shadow transition">
          <i data-lucide="bar-chart-2" class="w-6 h-6 text-indigo-600"></i>
          <span class="font-medium">Reports</span>
        </a>
      </div>
    </div>
  </main>

  <script> lucide.createIcons(); </script>
</body>
</html>
