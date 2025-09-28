<?php
// includes/sidebar_reception.php
require_once __DIR__ . '/auth.php';
require_role('reception');
?>
<aside class="fixed top-0 left-0 w-64 h-full bg-white shadow-lg z-40 hidden md:block">
  <div class="flex items-center gap-2 px-6 py-4 border-b">
    <img src="<?= e(BASE_URL . '/assets/img/logo.png') ?>" alt="Healsync Logo" class="h-8 w-8">
    <span class="text-lg font-bold text-indigo-600">Healsync</span>
  </div>

  <nav class="mt-4 space-y-1">
    <a href="<?= e(BASE_URL . '/reception/dashboard.php') ?>" 
       class="flex items-center gap-3 px-6 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600">
      <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
      <span>Dashboard</span>
    </a>

    <a href="<?= e(BASE_URL . '/reception/offline_bills.php') ?>" 
       class="flex items-center gap-3 px-6 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600">
      <i data-lucide="credit-card" class="w-5 h-5"></i>
      <span>Offline Bills</span>
    </a>

    <a href="<?= e(BASE_URL . '/reception/receipt_pdf.php') ?>" 
       class="flex items-center gap-3 px-6 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600">
      <i data-lucide="file-text" class="w-5 h-5"></i>
      <span>Receipts</span>
    </a>

    <a href="<?= e(BASE_URL . '/reception/appointments.php') ?>" 
       class="flex items-center gap-3 px-6 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600">
      <i data-lucide="calendar" class="w-5 h-5"></i>
      <span>Appointments</span>
    </a>
  </nav>

  <div class="absolute bottom-0 w-full border-t">
    <a href="<?= e(BASE_URL . '/auth/logout.php') ?>"
       class="flex items-center gap-3 px-6 py-3 text-red-600 hover:bg-red-50">
      <i data-lucide="log-out" class="w-5 h-5"></i>
      <span>Logout</span>
    </a>
  </div>
</aside>

<!-- Mobile Sidebar (toggle if needed) -->
<div id="sidebarMobile" class="fixed inset-0 bg-black/50 z-50 hidden">
  <aside class="absolute top-0 left-0 w-64 h-full bg-white shadow-lg">
    <div class="flex items-center gap-2 px-6 py-4 border-b">
      <img src="<?= e(BASE_URL . '/assets/img/logo.png') ?>" alt="Healsync Logo" class="h-8 w-8">
      <span class="text-lg font-bold text-indigo-600">Healsync</span>
    </div>
    <nav class="mt-4 space-y-1">
      <a href="<?= e(BASE_URL . '/reception/dashboard.php') ?>" class="flex items-center gap-3 px-6 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600"><i data-lucide="layout-dashboard" class="w-5 h-5"></i>Dashboard</a>
      <a href="<?= e(BASE_URL . '/reception/offline_bills.php') ?>" class="flex items-center gap-3 px-6 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600"><i data-lucide="credit-card" class="w-5 h-5"></i>Offline Bills</a>
      <a href="<?= e(BASE_URL . '/reception/receipt_pdf.php') ?>" class="flex items-center gap-3 px-6 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600"><i data-lucide="file-text" class="w-5 h-5"></i>Receipts</a>
      <a href="<?= e(BASE_URL . '/reception/appointments.php') ?>" class="flex items-center gap-3 px-6 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600"><i data-lucide="calendar" class="w-5 h-5"></i>Appointments</a>
    </nav>
    <div class="absolute bottom-0 w-full border-t">
      <a href="<?= e(BASE_URL . '/auth/logout.php') ?>" class="flex items-center gap-3 px-6 py-3 text-red-600 hover:bg-red-50"><i data-lucide="log-out" class="w-5 h-5"></i>Logout</a>
    </div>
  </aside>
</div>

<script>
  lucide.createIcons();
</script>
