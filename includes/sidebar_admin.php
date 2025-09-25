<?php
require_once __DIR__ . '/auth.php';
require_role('admin');
require_once __DIR__ . '/functions.php';
?>
<aside id="sidebar"
       class="fixed top-0 left-0 h-full w-64 bg-white shadow-lg transform -translate-x-full md:translate-x-0 transition-transform duration-300 z-40">
  <div class="p-4 flex items-center gap-2 border-b">
    <img src="/healsync/assets/img/logo.png" alt="logo" class="h-8 w-8 rounded">
    <span class="font-semibold text-lg">Healsync</span>
  </div>
  <nav class="p-4 space-y-2">

    <!-- Dashboard -->
    <a href="/healsync/admin/dashboard.php"
       class="flex items-center gap-2 px-3 py-2 rounded hover:bg-indigo-100 <?=basename($_SERVER['PHP_SELF'])==='dashboard.php'?'bg-indigo-50 text-indigo-700 font-medium':''?>">
      <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
      Dashboard
    </a>

    <!-- Doctors -->
    <div class="mt-4">
      <p class="text-gray-500 text-xs uppercase mb-1 px-3">Doctors</p>
      <a href="/healsync/admin/manage_doctors.php"
         class="flex items-center gap-2 px-3 py-2 rounded hover:bg-indigo-100 <?=basename($_SERVER['PHP_SELF'])==='manage_doctors.php'?'bg-indigo-50 text-indigo-700 font-medium':''?>">
        <i data-lucide="user-plus" class="w-5 h-5"></i>
        Manage Doctors
      </a>
    </div>

    <!-- Patients -->
    <div class="mt-4">
      <p class="text-gray-500 text-xs uppercase mb-1 px-3">Patients</p>
      <a href="/healsync/admin/manage_patients.php"
         class="flex items-center gap-2 px-3 py-2 rounded hover:bg-indigo-100 <?=basename($_SERVER['PHP_SELF'])==='manage_patients.php'?'bg-indigo-50 text-indigo-700 font-medium':''?>">
        <i data-lucide="users" class="w-5 h-5"></i>
        Manage Patients
      </a>
    </div>

    <!-- Treatments -->
    <div class="mt-4">
      <p class="text-gray-500 text-xs uppercase mb-1 px-3">Treatments</p>
      <a href="/healsync/admin/manage_treatments.php"
         class="flex items-center gap-2 px-3 py-2 rounded hover:bg-indigo-100 <?=basename($_SERVER['PHP_SELF'])==='manage_treatments.php'?'bg-indigo-50 text-indigo-700 font-medium':''?>">
        <i data-lucide="stethoscope" class="w-5 h-5"></i>
        Manage Treatments
      </a>
    </div>

    <!-- Reports -->
    <div class="mt-4">
      <p class="text-gray-500 text-xs uppercase mb-1 px-3">Reports</p>
      <a href="/healsync/admin/reports.php"
         class="flex items-center gap-2 px-3 py-2 rounded hover:bg-indigo-100 <?=basename($_SERVER['PHP_SELF'])==='reports.php'?'bg-indigo-50 text-indigo-700 font-medium':''?>">
        <i data-lucide="bar-chart-2" class="w-5 h-5"></i>
        Reports
      </a>
    </div>

    <!-- Logout -->
    <a href="/healsync/auth/logout.php"
       class="mt-6 flex items-center gap-2 px-3 py-2 rounded hover:bg-red-100 text-red-600">
      <i data-lucide="log-out" class="w-5 h-5"></i>
      Logout
    </a>
  </nav>
</aside>

<!-- Mobile Sidebar Toggle -->
<button id="sidebarToggle"
        class="md:hidden fixed top-4 left-4 z-50 bg-indigo-600 text-white p-2 rounded shadow">
  <i data-lucide="menu" class="w-5 h-5"></i>
</button>

<script>
  lucide.createIcons();
  const sidebar = document.getElementById('sidebar');
  const toggle = document.getElementById('sidebarToggle');
  toggle.addEventListener('click', () => {
    sidebar.classList.toggle('-translate-x-full');
  });
</script>
