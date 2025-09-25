<?php
// index.php
require_once 'includes/config.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Healsync - Hospital Management System</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
</head>
<body class="bg-gradient-to-br from-indigo-50 via-white to-blue-50 text-gray-800">
  
  <!-- Header -->
  <header class="sticky top-0 bg-white/80 backdrop-blur shadow z-50">
    <div class="container mx-auto px-6 py-4 flex justify-between items-center">
      <div class="flex items-center gap-3">
        <img src="assets/img/logo.png" alt="Healsync" class="h-10 w-10 rounded-full shadow-md"/>
        <div>
          <h1 class="text-xl font-bold text-indigo-700">Healsync</h1>
          <p class="text-sm text-gray-500">Hospital Management System</p>
        </div>
      </div>
      <nav class="hidden md:flex gap-6 items-center text-sm font-medium">
        <a href="#features" class="hover:text-indigo-600 transition">Features</a>
        <a href="#about" class="hover:text-indigo-600 transition">About</a>
        <a href="auth/login.php" class="px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700 shadow">Login</a>
        <a href="auth/register_patient.php" class="px-4 py-2 rounded-md border border-indigo-600 text-indigo-600 hover:bg-indigo-50">Register</a>
      </nav>
    </div>
  </header>

  <!-- Hero Section -->
  <section class="container mx-auto px-6 py-20 grid md:grid-cols-2 gap-12 items-center">
    <div class="animate__animated animate__fadeInLeft space-y-6">
      <h2 class="text-4xl md:text-5xl font-extrabold leading-tight text-gray-900">
        Smarter Healthcare with <span class="text-indigo-600">Healsync</span>
      </h2>
      <p class="text-lg text-gray-600">
        A modern hospital management platform to book appointments, manage patients, and handle billing with ease.
      </p>
      <div class="flex gap-4">
        <a href="auth/register_patient.php" class="px-6 py-3 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 shadow-lg transition transform hover:scale-105">
          Get Started
        </a>
        <a href="auth/login.php" class="px-6 py-3 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition transform hover:scale-105">
          Login
        </a>
      </div>
    </div>

    <div class="animate__animated animate__fadeInRight relative">
      <img src="assets/img/hospital.png" alt="Hospital Illustration" class="rounded-xl shadow-2xl"/>
      <div class="absolute -bottom-6 -right-6 bg-white shadow-lg rounded-xl p-4 w-48 animate-bounce">
        <p class="font-medium text-gray-800">📅 120+ Appointments</p>
        <p class="text-sm text-gray-500">booked this week</p>
      </div>
    </div>
  </section>

  <!-- Features -->
  <section id="features" class="bg-white py-16">
    <div class="container mx-auto px-6 text-center">
      <h3 class="text-3xl font-bold mb-10">Why Choose Healsync?</h3>
      <div class="grid md:grid-cols-3 gap-10">
        <div class="p-6 bg-indigo-50 rounded-xl shadow hover:shadow-lg transition transform hover:-translate-y-1">
          <i data-lucide="calendar" class="w-10 h-10 text-indigo-600 mx-auto mb-4"></i>
          <h4 class="font-semibold text-lg mb-2">Easy Appointments</h4>
          <p class="text-gray-600 text-sm">Patients can book, reschedule, or cancel appointments online in seconds.</p>
        </div>
        <div class="p-6 bg-indigo-50 rounded-xl shadow hover:shadow-lg transition transform hover:-translate-y-1">
          <i data-lucide="stethoscope" class="w-10 h-10 text-indigo-600 mx-auto mb-4"></i>
          <h4 class="font-semibold text-lg mb-2">Doctor Dashboard</h4>
          <p class="text-gray-600 text-sm">Doctors manage patient records, prescriptions, and treatments securely.</p>
        </div>
        <div class="p-6 bg-indigo-50 rounded-xl shadow hover:shadow-lg transition transform hover:-translate-y-1">
          <i data-lucide="credit-card" class="w-10 h-10 text-indigo-600 mx-auto mb-4"></i>
          <h4 class="font-semibold text-lg mb-2">Billing & Reports</h4>
          <p class="text-gray-600 text-sm">Automated billing, payments, and detailed reports for administrators.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- About -->
  <section id="about" class="container mx-auto px-6 py-20 grid md:grid-cols-2 gap-12 items-center">
    <div class="space-y-6 animate__animated animate__fadeInLeft">
      <h3 class="text-3xl font-bold">About Healsync</h3>
      <p class="text-gray-600">
        Healsync is a demo hospital management system designed for learning and showcasing modern web development practices.
      </p>
      <ul class="space-y-2 text-gray-700">
        <li>✅ Secure authentication with roles</li>
        <li>✅ Appointment scheduling</li>
        <li>✅ Billing & payments</li>
        <li>✅ Patient records & reports</li>
      </ul>
    </div>
    <div class="animate__animated animate__fadeInRight">
      <img src="assets/img/dashboard.png" alt="Dashboard Preview" class="rounded-xl shadow-2xl"/>
    </div>
  </section>

  <!-- CTA -->
  <section class="bg-indigo-600 text-white py-16 text-center">
    <h3 class="text-3xl font-bold mb-4">Ready to try Healsync?</h3>
    <p class="mb-6 text-indigo-100">Register today and experience a smarter way to manage hospital workflows.</p>
    <a href="auth/register_patient.php" class="px-8 py-4 bg-white text-indigo-600 font-semibold rounded-lg shadow-lg hover:bg-gray-100 transition transform hover:scale-105">
      Get Started for Free
    </a>
  </section>

  <!-- Footer -->
  <footer class="bg-gray-900 text-gray-400 text-sm mt-12">
    <div class="container mx-auto px-6 py-8 flex flex-col md:flex-row justify-between items-center gap-4">
      <p>© <?=date('Y')?> Healsync — Demo app. For demo use only.</p>
      <div class="flex gap-4">
        <a href="#" class="hover:text-white">Privacy</a>
        <a href="#" class="hover:text-white">Terms</a>
        <a href="#" class="hover:text-white">Contact</a>
      </div>
    </div>
  </footer>

  <script src="https://unpkg.com/lucide@latest"></script>
  <script>lucide.createIcons();</script>
</body>
</html>
