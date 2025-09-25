<?php
require_once __DIR__ . '/auth.php';
require_role('patient');
require_once __DIR__ . '/functions.php';

// Helper to highlight active page
function isActive($file) {
    return basename($_SERVER['PHP_SELF']) === $file 
        ? 'bg-indigo-50 text-indigo-700 font-medium' 
        : '';
}
?>
<aside id="sidebar"
       class="fixed top-0 left-0 h-full w-64 bg-white shadow-lg transform -translate-x-full md:translate-x-0 transition-transform duration-300 z-40">
  <!-- Logo -->
  <div class="p-4 flex items-center gap-2 border-b">
    <img src="/healsync/assets/img/logo.png" alt="logo" class="h-8 w-8 rounded">
    <span class="font-semibold text-lg">Healsync</span>
  </div>

  <!-- Navigation -->
  <nav class="p-4 space-y-2">

    <!-- Dashboard -->
    <a href="/healsync/patient/dashboard.php"
       class="flex items-center gap-2 px-3 py-2 rounded hover:bg-indigo-100 <?=isActive('dashboard.php')?>">
      <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
      Dashboard
    </a>

    <!-- Appointments -->
    <div class="mt-4">
      <p class="text-gray-500 text-xs uppercase mb-1 px-3">Appointments</p>
      <a href="/healsync/patient/book_appointment.php"
         class="flex items-center gap-2 px-3 py-2 rounded hover:bg-indigo-100 <?=isActive('book_appointment.php')?>">
        <i data-lucide="plus-circle" class="w-5 h-5"></i>
        Book Appointment
      </a>
      <a href="/healsync/patient/appointments.php"
         class="flex items-center gap-2 px-3 py-2 rounded hover:bg-indigo-100 <?=isActive('appointments.php')?>">
        <i data-lucide="calendar" class="w-5 h-5"></i>
        My Appointments
      </a>
    </div>

    <!-- Prescriptions -->
    <div class="mt-4">
      <p class="text-gray-500 text-xs uppercase mb-1 px-3">Prescriptions</p>
      <a href="/healsync/patient/prescriptions.php"
         class="flex items-center gap-2 px-3 py-2 rounded hover:bg-indigo-100 <?=isActive('prescriptions.php')?>">
        <i data-lucide="pill" class="w-5 h-5"></i>
        My Prescriptions
      </a>
    </div>

    <!-- Treatments -->
    <div class="mt-4">
      <p class="text-gray-500 text-xs uppercase mb-1 px-3">Treatments</p>
      <a href="/healsync/patient/treatments.php"
         class="flex items-center gap-2 px-3 py-2 rounded hover:bg-indigo-100 <?=isActive('treatments.php')?>">
        <i data-lucide="stethoscope" class="w-5 h-5"></i>
        My Treatments
      </a>
    </div>

    <!-- Bills & Payments -->
    <div class="mt-4">
      <p class="text-gray-500 text-xs uppercase mb-1 px-3">Billing</p>
      <a href="/healsync/patient/bills.php"
         class="flex items-center gap-2 px-3 py-2 rounded hover:bg-indigo-100 <?=isActive('bills.php')?>">
        <i data-lucide="credit-card" class="w-5 h-5"></i>
        My Bills
      </a>
    </div>

    <!-- Profile -->
    <div class="mt-4">
      <p class="text-gray-500 text-xs uppercase mb-1 px-3">Profile</p>
      <a href="/healsync/patient/profile.php"
         class="flex items-center gap-2 px-3 py-2 rounded hover:bg-indigo-100 <?=isActive('profile.php')?>">
        <i data-lucide="user" class="w-5 h-5"></i>
        Profile
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
