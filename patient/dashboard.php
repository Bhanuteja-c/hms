<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('patient');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$pid = current_user_id();

// Quick Stats
$totalAppointments = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE patient_id=:pid");
$totalAppointments->execute([':pid' => $pid]);
$totalAppointments = $totalAppointments->fetchColumn();

$upcoming = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE patient_id=:pid AND date_time > NOW() AND status='approved'");
$upcoming->execute([':pid' => $pid]);
$upcomingCount = $upcoming->fetchColumn();

$bills = $pdo->prepare("SELECT SUM(total_amount) FROM bills WHERE patient_id=:pid AND status='unpaid'");
$bills->execute([':pid' => $pid]);
$unpaid = $bills->fetchColumn() ?: 0;
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Patient Dashboard - Healsync</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-100">
  <?php include __DIR__ . '/../includes/sidebar_patient.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300">
    <!-- Welcome -->
    <h2 class="text-2xl font-bold mb-8 flex items-center gap-2">
      <i data-lucide="layout-dashboard" class="w-7 h-7 text-indigo-600"></i>
      Welcome, <?= e(current_user_name()) ?> 👋
    </h2>

    <!-- Quick Stats -->
    <div class="grid md:grid-cols-3 gap-6">
      <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition flex items-center gap-4">
        <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
          <i data-lucide="calendar" class="w-6 h-6"></i>
        </div>
        <div>
          <p class="text-gray-500 text-sm">Total Appointments</p>
          <p class="text-2xl font-bold"><?= $totalAppointments ?></p>
        </div>
      </div>

      <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition flex items-center gap-4">
        <div class="p-3 rounded-full bg-green-100 text-green-600">
          <i data-lucide="clock" class="w-6 h-6"></i>
        </div>
        <div>
          <p class="text-gray-500 text-sm">Upcoming Appointments</p>
          <p class="text-2xl font-bold"><?= $upcomingCount ?></p>
        </div>
      </div>

      <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition flex items-center gap-4">
        <div class="p-3 rounded-full bg-red-100 text-red-600">
          <i data-lucide="credit-card" class="w-6 h-6"></i>
        </div>
        <div>
          <p class="text-gray-500 text-sm">Unpaid Bills</p>
          <p class="text-2xl font-bold">₹<?= number_format($unpaid, 2) ?></p>
        </div>
      </div>
    </div>

    <!-- Quick Links -->
    <div class="mt-12">
      <h3 class="text-xl font-semibold mb-4 flex items-center gap-2">
        <i data-lucide="zap" class="w-6 h-6 text-indigo-600"></i>
        Quick Links
      </h3>
      <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        
        <!-- Book Appointment -->
        <a href="book_appointment.php"
           class="bg-white hover:bg-indigo-50 border border-indigo-100 p-5 rounded-xl flex flex-col items-center gap-3 shadow transition">
          <i data-lucide="plus-circle" class="w-7 h-7 text-indigo-600"></i>
          <span class="font-medium">Book Appointment</span>
        </a>

        <!-- My Appointments -->
        <a href="appointments.php"
           class="bg-white hover:bg-indigo-50 border border-indigo-100 p-5 rounded-xl flex flex-col items-center gap-3 shadow transition">
          <i data-lucide="calendar-days" class="w-7 h-7 text-indigo-600"></i>
          <span class="font-medium">My Appointments</span>
        </a>

        <!-- Prescriptions -->
        <a href="prescriptions.php"
           class="bg-white hover:bg-indigo-50 border border-indigo-100 p-5 rounded-xl flex flex-col items-center gap-3 shadow transition">
          <i data-lucide="pill" class="w-7 h-7 text-indigo-600"></i>
          <span class="font-medium">Prescriptions</span>
        </a>

        <!-- Treatments -->
        <a href="treatments.php"
           class="bg-white hover:bg-indigo-50 border border-indigo-100 p-5 rounded-xl flex flex-col items-center gap-3 shadow transition">
          <i data-lucide="stethoscope" class="w-7 h-7 text-indigo-600"></i>
          <span class="font-medium">Treatments</span>
        </a>

        <!-- Bills -->
        <a href="bills.php"
           class="bg-white hover:bg-indigo-50 border border-indigo-100 p-5 rounded-xl flex flex-col items-center gap-3 shadow transition">
          <i data-lucide="credit-card" class="w-7 h-7 text-indigo-600"></i>
          <span class="font-medium">My Bills</span>
        </a>

        <!-- Profile -->
        <a href="profile.php"
           class="bg-white hover:bg-indigo-50 border border-indigo-100 p-5 rounded-xl flex flex-col items-center gap-3 shadow transition">
          <i data-lucide="user" class="w-7 h-7 text-indigo-600"></i>
          <span class="font-medium">My Profile</span>
        </a>

      </div>
    </div>

  </main>

  <script> lucide.createIcons(); </script>
</body>
</html>
