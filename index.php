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
<body class="bg-gray-50 text-gray-800">
  <header class="bg-white shadow">
    <div class="container mx-auto px-4 py-6 flex justify-between items-center">
      <div class="flex items-center gap-3">
        <img src="assets/img/logo.png" alt="Healsync" class="h-10 w-10 rounded-full"/>
        <div>
          <h1 class="text-xl font-semibold">Healsync</h1>
          <p class="text-sm text-gray-500">Hospital Management System</p>
        </div>
      </div>
      <nav class="space-x-4">
        <a href="auth/login.php" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Login</a>
        <a href="auth/register_patient.php" class="px-4 py-2 rounded border">Patient Register</a>
      </nav>
    </div>
  </header>

  <main class="container mx-auto px-4 py-10">
    <section class="grid md:grid-cols-2 gap-8 items-center">
      <div class="animate__animated animate__fadeInLeft">
        <h2 class="text-3xl font-bold mb-4">Streamline hospital operations with Healsync</h2>
        <p class="text-gray-600 mb-6">Book appointments, manage patient records, process bills — all with a modern, secure web app.</p>
        <div class="flex gap-3">
          <a href="auth/register_patient.php" class="px-5 py-3 rounded bg-indigo-600 text-white">Get Started</a>
          <a href="auth/login.php" class="px-5 py-3 rounded border">Login</a>
        </div>
      </div>
      <div class="animate__animated animate__fadeInRight">
        <div class="bg-white rounded-xl shadow-lg p-6">
          <h3 class="font-semibold mb-2">Quick Features</h3>
          <ul class="list-disc list-inside text-gray-600">
            <li>Patient appointment booking & doctor approvals</li>
            <li>Prescriptions, treatments & billing</li>
            <li>Admin dashboards & reports</li>
          </ul>
        </div>
      </div>
    </section>
  </main>

  <footer class="bg-white border-t mt-12">
    <div class="container mx-auto px-4 py-6 text-sm text-gray-500">
      © <?=date('Y')?> Healsync — Demo app. For demo use only.
    </div>
  </footer>
</body>
</html>
