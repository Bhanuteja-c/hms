<?php
// includes/sidebar_reception.php
require_once __DIR__ . '/auth.php';
require_role('receptionist');

$currentPage = basename($_SERVER['PHP_SELF']);
$baseUrl = BASE_URL . '/reception/';
?>
<aside id="sidebar" class="fixed top-0 left-0 w-64 h-full bg-white shadow-lg z-40 transform -translate-x-full md:translate-x-0 transition-transform duration-300">
  <div class="flex items-center gap-2 px-6 py-4 border-b border-gray-200">
    <img src="<?= e(BASE_URL . '/assets/img/logo.png') ?>" alt="Healsync Logo" class="h-8 w-8 rounded">
    <span class="text-lg font-semibold text-gray-800">Healsync</span>
  </div>

  <nav class="p-4 space-y-1">
    <a href="<?= e($baseUrl . 'dashboard.php') ?>" 
       class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 <?= $currentPage==='dashboard.php' ? 'bg-indigo-50 text-indigo-700 font-medium border-l-4 border-indigo-500' : 'hover:bg-gray-50 text-gray-700' ?>">
      <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
      <span>Dashboard</span>
    </a>

    <a href="<?= e($baseUrl . 'appointments.php') ?>" 
       class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 <?= $currentPage==='appointments.php' ? 'bg-indigo-50 text-indigo-700 font-medium border-l-4 border-indigo-500' : 'hover:bg-gray-50 text-gray-700' ?>">
      <i data-lucide="calendar" class="w-5 h-5"></i>
      <span>Appointments</span>
    </a>

    <a href="<?= e($baseUrl . 'walkin_appointment.php') ?>" 
       class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 <?= $currentPage==='walkin_appointment.php' ? 'bg-indigo-50 text-indigo-700 font-medium border-l-4 border-indigo-500' : 'hover:bg-gray-50 text-gray-700' ?>">
      <i data-lucide="user-plus" class="w-5 h-5"></i>
      <span>Walk-in</span>
    </a>

    <div class="pt-2 pb-1">
      <div class="px-3 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Billing</div>
    </div>

    <a href="<?= e($baseUrl . 'offline_bills.php') ?>" 
       class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 <?= $currentPage==='offline_bills.php' ? 'bg-indigo-50 text-indigo-700 font-medium border-l-4 border-indigo-500' : 'hover:bg-gray-50 text-gray-700' ?>">
      <i data-lucide="credit-card" class="w-5 h-5"></i>
      <span>Offline Bills</span>
    </a>

    <a href="<?= e($baseUrl . 'offline_payments.php') ?>" 
       class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 <?= $currentPage==='offline_payments.php' ? 'bg-indigo-50 text-indigo-700 font-medium border-l-4 border-indigo-500' : 'hover:bg-gray-50 text-gray-700' ?>">
      <i data-lucide="banknote" class="w-5 h-5"></i>
      <span>Payments</span>
    </a>

    <a href="<?= e($baseUrl . 'receipt_pdf.php') ?>" 
       class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 <?= $currentPage==='receipt_pdf.php' ? 'bg-indigo-50 text-indigo-700 font-medium border-l-4 border-indigo-500' : 'hover:bg-gray-50 text-gray-700' ?>">
      <i data-lucide="file-text" class="w-5 h-5"></i>
      <span>Receipts</span>
    </a>

    <div class="pt-2 pb-1 mt-4">
      <div class="px-3 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Account</div>
    </div>

    <!-- Notifications -->
    <a href="<?= e($baseUrl . 'notifications.php') ?>" 
       class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 <?= $currentPage==='notifications.php' ? 'bg-indigo-50 text-indigo-700 font-medium border-l-4 border-indigo-500' : 'hover:bg-gray-50 text-gray-700' ?>">
      <i data-lucide="bell" class="w-5 h-5"></i>
      <span>Notifications</span>
    </a>

    <!-- Profile -->
    <a href="<?= e($baseUrl . 'profile.php') ?>" 
       class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 <?= $currentPage==='profile.php' ? 'bg-indigo-50 text-indigo-700 font-medium border-l-4 border-indigo-500' : 'hover:bg-gray-50 text-gray-700' ?>">
      <i data-lucide="user" class="w-5 h-5"></i>
      <span>My Profile</span>
    </a>
  </nav>

  <div class="absolute bottom-0 w-full border-t border-gray-200">
    <a href="<?= e(BASE_URL . '/auth/logout.php') ?>"
       class="flex items-center gap-3 px-6 py-3 text-red-600 hover:bg-red-50">
      <i data-lucide="log-out" class="w-5 h-5"></i>
      <span>Logout</span>
    </a>
  </div>
</aside>

<!-- Mobile Sidebar Toggle -->
<button id="sidebarToggle"
        class="md:hidden fixed top-4 left-4 z-50 bg-indigo-600 text-white p-3 rounded-lg shadow-lg hover:bg-indigo-700 transition-colors duration-200">
  <i data-lucide="menu" class="w-5 h-5"></i>
</button>

<!-- Mobile Overlay -->
<div id="sidebarOverlay" class="md:hidden fixed inset-0 bg-black/50 z-30 hidden"></div>

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
