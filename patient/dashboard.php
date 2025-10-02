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
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Patient Dashboard - Healsync</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    .glass-effect {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    .stat-card {
      transition: all 0.3s ease;
    }
    .stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 min-h-screen">
  <?php include __DIR__ . '/../includes/sidebar_patient.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300">
    <!-- Header Section -->
    <div class="mb-8">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
            Welcome back, <?= e(current_user_name()) ?>! 👋
          </h1>
          <p class="text-gray-600 mt-1">Here's an overview of your healthcare journey</p>
        </div>
        <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center">
          <i data-lucide="heart" class="w-6 h-6 text-white"></i>
        </div>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="stat-card glass-effect rounded-2xl p-6 border border-white/20">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm font-medium">Total Appointments</p>
            <p class="text-3xl font-bold text-gray-900 mt-1"><?= $totalAppointments ?></p>
            <p class="text-indigo-600 text-sm mt-1">
              <i data-lucide="trending-up" class="w-4 h-4 inline"></i>
              All time
            </p>
          </div>
          <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center">
            <i data-lucide="calendar" class="w-6 h-6 text-white"></i>
          </div>
        </div>
      </div>

      <div class="stat-card glass-effect rounded-2xl p-6 border border-white/20">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm font-medium">Upcoming</p>
            <p class="text-3xl font-bold text-gray-900 mt-1"><?= $upcomingCount ?></p>
            <p class="text-green-600 text-sm mt-1">
              <i data-lucide="clock" class="w-4 h-4 inline"></i>
              Scheduled
            </p>
          </div>
          <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-500 rounded-xl flex items-center justify-center">
            <i data-lucide="calendar-check" class="w-6 h-6 text-white"></i>
          </div>
        </div>
      </div>

      <div class="stat-card glass-effect rounded-2xl p-6 border border-white/20">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm font-medium">Unpaid Bills</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">₹<?= number_format($unpaid, 2) ?></p>
            <p class="text-red-600 text-sm mt-1">
              <i data-lucide="alert-circle" class="w-4 h-4 inline"></i>
              Pending payment
            </p>
          </div>
          <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-pink-500 rounded-xl flex items-center justify-center">
            <i data-lucide="credit-card" class="w-6 h-6 text-white"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="glass-effect rounded-2xl border border-white/20 overflow-hidden">
      <div class="p-6 border-b border-gray-200/50">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center">
            <i data-lucide="zap" class="w-5 h-5 text-white"></i>
          </div>
          <div>
            <h3 class="text-xl font-bold text-gray-900">Quick Actions</h3>
            <p class="text-gray-600 text-sm">Access your most used features</p>
          </div>
        </div>
      </div>
      
      <div class="p-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <!-- Book Appointment -->
          <a href="book_appointment.php"
             class="group glass-effect p-4 rounded-xl border border-white/20 hover:bg-white/50 transition-all duration-300 hover:scale-105 flex flex-col items-center gap-3">
            <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
              <i data-lucide="plus-circle" class="w-6 h-6 text-white"></i>
            </div>
            <span class="font-medium text-gray-900 text-sm text-center">Book Appointment</span>
          </a>

          <!-- My Appointments -->
          <a href="appointments.php"
             class="group glass-effect p-4 rounded-xl border border-white/20 hover:bg-white/50 transition-all duration-300 hover:scale-105 flex flex-col items-center gap-3">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
              <i data-lucide="calendar-days" class="w-6 h-6 text-white"></i>
            </div>
            <span class="font-medium text-gray-900 text-sm text-center">My Appointments</span>
          </a>

          <!-- Prescriptions -->
          <a href="prescriptions.php"
             class="group glass-effect p-4 rounded-xl border border-white/20 hover:bg-white/50 transition-all duration-300 hover:scale-105 flex flex-col items-center gap-3">
            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
              <i data-lucide="pill" class="w-6 h-6 text-white"></i>
            </div>
            <span class="font-medium text-gray-900 text-sm text-center">Prescriptions</span>
          </a>

          <!-- Treatments -->
          <a href="treatments.php"
             class="group glass-effect p-4 rounded-xl border border-white/20 hover:bg-white/50 transition-all duration-300 hover:scale-105 flex flex-col items-center gap-3">
            <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-red-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
              <i data-lucide="stethoscope" class="w-6 h-6 text-white"></i>
            </div>
            <span class="font-medium text-gray-900 text-sm text-center">Treatments</span>
          </a>

          <!-- Bills -->
          <a href="bills.php"
             class="group glass-effect p-4 rounded-xl border border-white/20 hover:bg-white/50 transition-all duration-300 hover:scale-105 flex flex-col items-center gap-3">
            <div class="w-12 h-12 bg-gradient-to-br from-yellow-500 to-orange-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
              <i data-lucide="credit-card" class="w-6 h-6 text-white"></i>
            </div>
            <span class="font-medium text-gray-900 text-sm text-center">My Bills</span>
          </a>

          <!-- Profile -->
          <a href="profile.php"
             class="group glass-effect p-4 rounded-xl border border-white/20 hover:bg-white/50 transition-all duration-300 hover:scale-105 flex flex-col items-center gap-3">
            <div class="w-12 h-12 bg-gradient-to-br from-teal-500 to-cyan-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
              <i data-lucide="user" class="w-6 h-6 text-white"></i>
            </div>
            <span class="font-medium text-gray-900 text-sm text-center">My Profile</span>
          </a>

          <!-- Notifications -->
          <a href="notifications.php"
             class="group glass-effect p-4 rounded-xl border border-white/20 hover:bg-white/50 transition-all duration-300 hover:scale-105 flex flex-col items-center gap-3">
            <div class="w-12 h-12 bg-gradient-to-br from-pink-500 to-rose-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
              <i data-lucide="bell" class="w-6 h-6 text-white"></i>
            </div>
            <span class="font-medium text-gray-900 text-sm text-center">Notifications</span>
          </a>
        </div>
      </div>

      </div>
    </div>

  </main>

  <script> lucide.createIcons(); </script>
</body>
</html>
