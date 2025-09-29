<?php
require_once __DIR__ . '/auth.php';
require_role('patient');
require_once __DIR__ . '/functions.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$baseUrl = '/healsync/patient/';

function isActive($file) {
    global $currentPage;
    return $currentPage === $file ? 'bg-indigo-50 text-indigo-700 font-medium border-l-4 border-indigo-500' : 'hover:bg-gray-50 text-gray-700';
}
?>
<aside id="sidebar"
       class="fixed top-0 left-0 h-full w-64 bg-white shadow-lg transform -translate-x-full md:translate-x-0 transition-transform duration-300 z-40">
  <!-- Logo -->
  <div class="p-4 flex items-center gap-2 border-b border-gray-200">
    <img src="<?= $baseUrl ?>../assets/img/logo.png" alt="Healsync Logo" class="h-8 w-8 rounded">
    <span class="font-semibold text-lg text-gray-800">Healsync</span>
  </div>

  <!-- Navigation -->
  <nav class="p-4 space-y-1">

    <!-- Dashboard -->
    <a href="<?= $baseUrl ?>dashboard.php"
       class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 <?= isActive('dashboard.php') ?>">
      <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
      <span>Dashboard</span>
    </a>

    <!-- Appointments -->
    <div class="mt-6">
      <p class="text-gray-500 text-xs uppercase font-semibold mb-3 px-3 tracking-wider">Appointments</p>
      <a href="<?= $baseUrl ?>book_appointment.php"
         class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 <?= isActive('book_appointment.php') ?>">
        <i data-lucide="plus-circle" class="w-5 h-5"></i>
        <span>Book Appointment</span>
      </a>
      <a href="<?= $baseUrl ?>appointments.php"
         class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 <?= isActive('appointments.php') ?>">
        <i data-lucide="calendar" class="w-5 h-5"></i>
        <span>My Appointments</span>
      </a>
    </div>

    <!-- Prescriptions -->
    <div class="mt-6">
      <p class="text-gray-500 text-xs uppercase font-semibold mb-3 px-3 tracking-wider">Prescriptions</p>
      <a href="<?= $baseUrl ?>prescriptions.php"
         class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 <?= isActive('prescriptions.php') ?>">
        <i data-lucide="pill" class="w-5 h-5"></i>
        <span>My Prescriptions</span>
      </a>
    </div>

    <!-- Treatments -->
    <div class="mt-6">
      <p class="text-gray-500 text-xs uppercase font-semibold mb-3 px-3 tracking-wider">Treatments</p>
      <a href="<?= $baseUrl ?>treatments.php"
         class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 <?= isActive('treatments.php') ?>">
        <i data-lucide="stethoscope" class="w-5 h-5"></i>
        <span>My Treatments</span>
      </a>
    </div>

    <!-- Bills & Payments -->
    <div class="mt-6">
      <p class="text-gray-500 text-xs uppercase font-semibold mb-3 px-3 tracking-wider">Billing</p>
      <a href="<?= $baseUrl ?>bills.php"
         class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 <?= isActive('bills.php') ?>">
        <i data-lucide="credit-card" class="w-5 h-5"></i>
        <span>My Bills</span>
      </a>
    </div>

    <!-- Profile -->
    <div class="mt-6">
      <p class="text-gray-500 text-xs uppercase font-semibold mb-3 px-3 tracking-wider">Profile</p>
      <a href="<?= $baseUrl ?>profile.php"
         class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 <?= isActive('profile.php') ?>">
        <i data-lucide="user" class="w-5 h-5"></i>
        <span>Profile</span>
      </a>
    </div>

    <!-- Logout -->
    <div class="mt-auto pt-6 border-t border-gray-200">
      <a href="<?= $baseUrl ?>../auth/logout.php"
         class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 hover:bg-red-50 text-red-600 hover:text-red-700">
        <i data-lucide="log-out" class="w-5 h-5"></i>
        <span>Logout</span>
      </a>
    </div>
  </nav>
</aside>

<!-- Mobile Sidebar Toggle -->
<button id="sidebarToggle"
        class="md:hidden fixed top-4 left-4 z-50 bg-indigo-600 text-white p-3 rounded-lg shadow-lg hover:bg-indigo-700 transition-colors duration-200">
  <i data-lucide="menu" class="w-5 h-5"></i>
</button>

<!-- Mobile Overlay -->
<div id="sidebarOverlay" 
     class="md:hidden fixed inset-0 bg-black bg-opacity-50 z-30 hidden"></div>

<script>
  lucide.createIcons();
  const sidebar = document.getElementById('sidebar');
  const toggle = document.getElementById('sidebarToggle');
  const overlay = document.getElementById('sidebarOverlay');
  toggle.addEventListener('click', () => {
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
  });
  overlay.addEventListener('click', () => {
    sidebar.classList.add('-translate-x-full');
    overlay.classList.add('hidden');
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !sidebar.classList.contains('-translate-x-full')) {
      sidebar.classList.add('-translate-x-full');
      overlay.classList.add('hidden');
    }
  });
  window.addEventListener('resize', () => {
    if (window.innerWidth >= 768) {
      sidebar.classList.remove('-translate-x-full');
      overlay.classList.add('hidden');
    }
  });
</script>
