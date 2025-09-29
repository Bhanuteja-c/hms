<?php
// index.php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/db.php';

// Realtime metrics
$appointmentsThisWeek = $pdo->query("SELECT COUNT(*) FROM appointments WHERE YEARWEEK(date_time, 1) = YEARWEEK(CURDATE(), 1)")->fetchColumn();
$totalPatients = $pdo->query("SELECT COUNT(*) FROM users WHERE role='patient'")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$revenueThisMonth = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM bills WHERE status='paid' AND paid_at IS NOT NULL AND paid_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01') AND paid_at < DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01')")->fetchColumn();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Healsync - Hospital Management System</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <meta name="description" content="Modern hospital management system for appointments, patient records, billing, and healthcare workflows." />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <style>
    .gradient-bg {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .glass-effect {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    .floating {
      animation: floating 3s ease-in-out infinite;
    }
    @keyframes floating {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
    }
    .pulse-slow {
      animation: pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    .hero-pattern {
      background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.15) 1px, transparent 0);
      background-size: 20px 20px;
    }
  </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 text-gray-800 overflow-x-hidden">
  
  <!-- Navigation -->
  <nav class="fixed top-0 w-full z-50 glass-effect">
    <div class="container mx-auto px-6 py-4">
      <div class="flex justify-between items-center">
        <div class="flex items-center gap-3 group">
          <div class="relative">
            <div class="absolute inset-0 bg-indigo-600 rounded-full blur opacity-75 group-hover:opacity-100 transition"></div>
            <img src="assets/img/logo.png" alt="Healsync" class="relative h-12 w-12 rounded-full shadow-lg"/>
          </div>
        <div>
            <h1 class="text-xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Healsync</h1>
            <p class="text-xs text-gray-600">Healthcare Management</p>
          </div>
        </div>
        
        <div class="hidden md:flex items-center gap-8">
          <a href="#features" class="text-gray-700 hover:text-indigo-600 transition-all duration-300 hover:scale-105">Features</a>
          <a href="#about" class="text-gray-700 hover:text-indigo-600 transition-all duration-300 hover:scale-105">About</a>
          <a href="#demo" class="text-gray-700 hover:text-indigo-600 transition-all duration-300 hover:scale-105">Demo</a>
          <div class="flex gap-3">
            <a href="auth/login.php" class="px-4 py-2 rounded-lg bg-white/20 text-gray-700 hover:bg-white/30 transition-all duration-300 hover:scale-105 shadow-lg">
              <i data-lucide="log-in" class="w-4 h-4 inline mr-2"></i>Login
            </a>
            <a href="auth/register_patient.php" class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition-all duration-300 hover:scale-105 shadow-lg">
              <i data-lucide="user-plus" class="w-4 h-4 inline mr-2"></i>Register
            </a>
          </div>
        </div>
        
        <!-- Mobile Menu Button -->
        <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg bg-white/20 hover:bg-white/30 transition">
          <i data-lucide="menu" class="w-6 h-6"></i>
        </button>
      </div>
      
      <!-- Mobile Menu -->
      <div id="mobile-menu" class="hidden md:hidden mt-4 p-4 bg-white/10 rounded-lg backdrop-blur">
        <div class="flex flex-col gap-4">
          <a href="#features" class="text-gray-700 hover:text-indigo-600 transition">Features</a>
          <a href="#about" class="text-gray-700 hover:text-indigo-600 transition">About</a>
          <a href="#demo" class="text-gray-700 hover:text-indigo-600 transition">Demo</a>
          <div class="flex flex-col gap-2 pt-4 border-t border-white/20">
            <a href="auth/login.php" class="px-4 py-2 rounded-lg bg-white/20 text-gray-700 hover:bg-white/30 transition text-center">
              <i data-lucide="log-in" class="w-4 h-4 inline mr-2"></i>Login
            </a>
            <a href="auth/register_patient.php" class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition text-center">
              <i data-lucide="user-plus" class="w-4 h-4 inline mr-2"></i>Register
            </a>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="relative min-h-screen flex items-center pt-20">
    <!-- Background Elements -->
    <div class="absolute inset-0 hero-pattern opacity-30"></div>
    <div class="absolute top-20 left-10 w-72 h-72 bg-indigo-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-pulse"></div>
    <div class="absolute top-40 right-10 w-72 h-72 bg-purple-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-pulse" style="animation-delay: 2s;"></div>
    <div class="absolute -bottom-8 left-20 w-72 h-72 bg-pink-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-pulse" style="animation-delay: 4s;"></div>
    
    <div class="container mx-auto px-6 py-20 grid lg:grid-cols-2 gap-16 items-center relative z-10">
      <div class="space-y-8 animate__animated animate__fadeInLeft">
        <!-- Badge -->
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-indigo-100 text-indigo-700 text-sm font-medium">
          <i data-lucide="sparkles" class="w-4 h-4"></i>
          Trusted by 500+ Healthcare Providers
        </div>
        
        <h1 class="text-5xl lg:text-7xl font-extrabold leading-tight">
          <span class="bg-gradient-to-r from-gray-900 via-indigo-800 to-purple-800 bg-clip-text text-transparent">
            Smarter
          </span>
          <br>
          <span class="bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
            Healthcare
          </span>
        </h1>
        
        <p class="text-xl text-gray-600 leading-relaxed max-w-lg">
          Transform your hospital operations with our comprehensive management platform. 
          Streamline appointments, patient records, billing, and more.
        </p>
        
        <!-- Stats -->
        <div class="flex gap-8 text-sm">
          <div class="flex items-center gap-2">
            <div class="w-2 h-2 bg-green-500 rounded-full pulse-slow"></div>
            <span class="text-gray-600">99.9% Uptime</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-2 h-2 bg-blue-500 rounded-full pulse-slow"></div>
            <span class="text-gray-600">HIPAA Compliant</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-2 h-2 bg-purple-500 rounded-full pulse-slow"></div>
            <span class="text-gray-600">24/7 Support</span>
          </div>
        </div>
        
        <!-- CTA Buttons -->
        <div class="flex flex-col sm:flex-row gap-4">
          <a href="auth/register_patient.php" class="group relative px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-semibold shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-105 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-indigo-700 to-purple-700 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <span class="relative flex items-center justify-center gap-2">
              <i data-lucide="rocket" class="w-5 h-5"></i>
              Get Started Free
            </span>
          </a>
          <a href="auth/login.php" class="group px-8 py-4 bg-white/20 backdrop-blur text-gray-700 rounded-xl font-semibold border border-white/30 hover:bg-white/30 transition-all duration-300 hover:scale-105 flex items-center justify-center gap-2">
            <i data-lucide="log-in" class="w-5 h-5"></i>
          Login
        </a>
      </div>
        
        <!-- Trust Indicators -->
        <div class="flex items-center gap-6 text-sm text-gray-500">
          <div class="flex items-center gap-1">
            <i data-lucide="shield-check" class="w-4 h-4 text-green-500"></i>
            <span>Secure</span>
          </div>
          <div class="flex items-center gap-1">
            <i data-lucide="zap" class="w-4 h-4 text-yellow-500"></i>
            <span>Fast</span>
          </div>
          <div class="flex items-center gap-1">
            <i data-lucide="heart" class="w-4 h-4 text-red-500"></i>
            <span>Reliable</span>
          </div>
        </div>
    </div>

      <!-- Hero Visual -->
      <div class="relative animate__animated animate__fadeInRight">
        <!-- Main Image Container -->
        <div class="relative">
          <div class="absolute inset-0 bg-gradient-to-r from-indigo-400 to-purple-400 rounded-3xl blur-3xl opacity-20 scale-110"></div>
          <img src="assets/img/hospital.png" alt="Hospital Management Dashboard" class="relative rounded-3xl shadow-2xl floating"/>
        </div>
        
        <!-- Floating Cards -->
        <div class="absolute -top-4 -left-4 bg-white/90 backdrop-blur rounded-2xl p-4 shadow-xl floating" style="animation-delay: 1s;">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
              <i data-lucide="calendar-check" class="w-5 h-5 text-green-600"></i>
            </div>
            <div>
              <p class="font-semibold text-gray-800"><?= number_format((int)$appointmentsThisWeek) ?> Appointments</p>
              <p class="text-sm text-gray-500">This week</p>
            </div>
          </div>
        </div>
        
        <div class="absolute -bottom-4 -right-4 bg-white/90 backdrop-blur rounded-2xl p-4 shadow-xl floating" style="animation-delay: 2s;">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
              <i data-lucide="users" class="w-5 h-5 text-blue-600"></i>
            </div>
            <div>
              <p class="font-semibold text-gray-800"><?= number_format((int)$totalPatients) ?> Patients</p>
              <p class="text-sm text-gray-500">Registered</p>
            </div>
          </div>
        </div>
        
        <div class="absolute top-1/2 -left-8 bg-white/90 backdrop-blur rounded-2xl p-4 shadow-xl floating" style="animation-delay: 3s;">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
              <i data-lucide="credit-card" class="w-5 h-5 text-purple-600"></i>
            </div>
            <div>
              <p class="font-semibold text-gray-800"><?= money($revenueThisMonth) ?> Revenue</p>
              <p class="text-sm text-gray-500">This month</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Features -->
  <section id="features" class="relative py-24 bg-gradient-to-b from-white to-gray-50">
    <div class="container mx-auto px-6">
      <!-- Section Header -->
      <div class="text-center mb-20">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-indigo-100 text-indigo-700 text-sm font-medium mb-6">
          <i data-lucide="star" class="w-4 h-4"></i>
          Why Choose Healsync?
        </div>
        <h2 class="text-4xl lg:text-5xl font-bold mb-6">
          <span class="bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
            Everything you need
          </span>
          <br>
          <span class="bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
            in one platform
          </span>
        </h2>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
          Streamline your healthcare operations with our comprehensive suite of tools designed for modern medical practices.
        </p>
      </div>

      <!-- Features Grid -->
      <div class="grid lg:grid-cols-3 gap-8 mb-20">
        <!-- Feature 1 -->
        <div class="group relative p-8 bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-gray-100">
          <div class="absolute inset-0 bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
          <div class="relative">
            <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
              <i data-lucide="calendar" class="w-8 h-8 text-white"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-4">Smart Appointments</h3>
            <p class="text-gray-600 mb-6">Patients can book, reschedule, or cancel appointments online in seconds with our intuitive scheduling system.</p>
            <ul class="space-y-2 text-sm text-gray-500">
              <li class="flex items-center gap-2">
                <i data-lucide="check" class="w-4 h-4 text-green-500"></i>
                Real-time availability
              </li>
              <li class="flex items-center gap-2">
                <i data-lucide="check" class="w-4 h-4 text-green-500"></i>
                Automated reminders
              </li>
              <li class="flex items-center gap-2">
                <i data-lucide="check" class="w-4 h-4 text-green-500"></i>
                Waitlist management
              </li>
            </ul>
          </div>
        </div>

        <!-- Feature 2 -->
        <div class="group relative p-8 bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-gray-100">
          <div class="absolute inset-0 bg-gradient-to-br from-green-50 to-blue-50 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
          <div class="relative">
            <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-blue-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
              <i data-lucide="stethoscope" class="w-8 h-8 text-white"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-4">Doctor Dashboard</h3>
            <p class="text-gray-600 mb-6">Comprehensive patient management with secure access to medical records, prescriptions, and treatment history.</p>
            <ul class="space-y-2 text-sm text-gray-500">
              <li class="flex items-center gap-2">
                <i data-lucide="check" class="w-4 h-4 text-green-500"></i>
                Digital prescriptions
              </li>
              <li class="flex items-center gap-2">
                <i data-lucide="check" class="w-4 h-4 text-green-500"></i>
                Patient history
              </li>
              <li class="flex items-center gap-2">
                <i data-lucide="check" class="w-4 h-4 text-green-500"></i>
                Treatment tracking
              </li>
            </ul>
          </div>
        </div>

        <!-- Feature 3 -->
        <div class="group relative p-8 bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-gray-100">
          <div class="absolute inset-0 bg-gradient-to-br from-purple-50 to-pink-50 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
          <div class="relative">
            <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
              <i data-lucide="credit-card" class="w-8 h-8 text-white"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-4">Billing & Payments</h3>
            <p class="text-gray-600 mb-6">Automated billing system with multiple payment options and comprehensive financial reporting.</p>
            <ul class="space-y-2 text-sm text-gray-500">
              <li class="flex items-center gap-2">
                <i data-lucide="check" class="w-4 h-4 text-green-500"></i>
                Online payments
              </li>
              <li class="flex items-center gap-2">
                <i data-lucide="check" class="w-4 h-4 text-green-500"></i>
                Invoice generation
              </li>
              <li class="flex items-center gap-2">
                <i data-lucide="check" class="w-4 h-4 text-green-500"></i>
                Financial reports
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Additional Features -->
      <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="text-center p-6 bg-white/50 backdrop-blur rounded-xl border border-white/20">
          <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mx-auto mb-4">
            <i data-lucide="shield-check" class="w-6 h-6 text-indigo-600"></i>
          </div>
          <h4 class="font-semibold text-gray-900 mb-2">HIPAA Compliant</h4>
          <p class="text-sm text-gray-600">Enterprise-grade security</p>
        </div>
        
        <div class="text-center p-6 bg-white/50 backdrop-blur rounded-xl border border-white/20">
          <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mx-auto mb-4">
            <i data-lucide="smartphone" class="w-6 h-6 text-green-600"></i>
          </div>
          <h4 class="font-semibold text-gray-900 mb-2">Mobile Ready</h4>
          <p class="text-sm text-gray-600">Access anywhere, anytime</p>
        </div>
        
        <div class="text-center p-6 bg-white/50 backdrop-blur rounded-xl border border-white/20">
          <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mx-auto mb-4">
            <i data-lucide="zap" class="w-6 h-6 text-purple-600"></i>
          </div>
          <h4 class="font-semibold text-gray-900 mb-2">Lightning Fast</h4>
          <p class="text-sm text-gray-600">Optimized performance</p>
        </div>
        
        <div class="text-center p-6 bg-white/50 backdrop-blur rounded-xl border border-white/20">
          <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center mx-auto mb-4">
            <i data-lucide="headphones" class="w-6 h-6 text-orange-600"></i>
          </div>
          <h4 class="font-semibold text-gray-900 mb-2">24/7 Support</h4>
          <p class="text-sm text-gray-600">Always here to help</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Demo Section -->
  <section id="demo" class="relative py-24 bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600 overflow-hidden">
    <div class="absolute inset-0 bg-black/20"></div>
    <div class="container mx-auto px-6 relative z-10">
      <div class="text-center mb-16">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/20 text-white text-sm font-medium mb-6">
          <i data-lucide="play" class="w-4 h-4"></i>
          Live Demo
        </div>
        <h2 class="text-4xl lg:text-5xl font-bold text-white mb-6">
          See Healsync in Action
        </h2>
        <p class="text-xl text-white/80 max-w-3xl mx-auto mb-12">
          Experience the power of our platform with interactive demos and real-world scenarios.
        </p>
      </div>

      <div class="grid lg:grid-cols-3 gap-8">
        <!-- Demo Card 1 -->
        <div class="group bg-white/10 backdrop-blur rounded-2xl p-8 hover:bg-white/20 transition-all duration-300 hover:scale-105">
          <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
            <i data-lucide="user" class="w-8 h-8 text-white"></i>
          </div>
          <h3 class="text-xl font-bold text-white mb-4">Patient Portal</h3>
          <p class="text-white/80 mb-6">Book appointments, view medical records, and manage your healthcare journey.</p>
          <a href="auth/register_patient.php" class="inline-flex items-center gap-2 px-6 py-3 bg-white/20 text-white rounded-lg hover:bg-white/30 transition">
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
            Try as Patient
          </a>
        </div>

        <!-- Demo Card 2 -->
        <div class="group bg-white/10 backdrop-blur rounded-2xl p-8 hover:bg-white/20 transition-all duration-300 hover:scale-105">
          <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
            <i data-lucide="stethoscope" class="w-8 h-8 text-white"></i>
          </div>
          <h3 class="text-xl font-bold text-white mb-4">Doctor Dashboard</h3>
          <p class="text-white/80 mb-6">Manage patients, create prescriptions, and track treatments efficiently.</p>
          <a href="auth/login.php" class="inline-flex items-center gap-2 px-6 py-3 bg-white/20 text-white rounded-lg hover:bg-white/30 transition">
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
            Doctor Login
          </a>
        </div>

        <!-- Demo Card 3 -->
        <div class="group bg-white/10 backdrop-blur rounded-2xl p-8 hover:bg-white/20 transition-all duration-300 hover:scale-105">
          <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
            <i data-lucide="building" class="w-8 h-8 text-white"></i>
          </div>
          <h3 class="text-xl font-bold text-white mb-4">Admin Panel</h3>
          <p class="text-white/80 mb-6">Comprehensive system management with analytics and user administration.</p>
          <a href="auth/login.php" class="inline-flex items-center gap-2 px-6 py-3 bg-white/20 text-white rounded-lg hover:bg-white/30 transition">
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
            Admin Access
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- About -->
  <section id="about" class="relative py-24 bg-white">
    <div class="container mx-auto px-6">
      <div class="grid lg:grid-cols-2 gap-16 items-center">
        <div class="space-y-8">
          <div>
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-indigo-100 text-indigo-700 text-sm font-medium mb-6">
              <i data-lucide="info" class="w-4 h-4"></i>
              About Healsync
            </div>
            <h2 class="text-4xl lg:text-5xl font-bold mb-6">
              <span class="bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
                Built for Modern
              </span>
              <br>
              <span class="bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                Healthcare
              </span>
            </h2>
            <p class="text-xl text-gray-600 leading-relaxed">
              Healsync is a comprehensive hospital management system designed to streamline healthcare operations. 
              Built with modern web technologies and security best practices.
            </p>
          </div>

          <div class="grid grid-cols-2 gap-6">
            <div class="p-6 bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl">
              <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mb-4">
                <i data-lucide="shield" class="w-6 h-6 text-indigo-600"></i>
              </div>
              <h4 class="font-bold text-gray-900 mb-2">Secure</h4>
              <p class="text-sm text-gray-600">HIPAA compliant with enterprise-grade security</p>
            </div>
            
            <div class="p-6 bg-gradient-to-br from-green-50 to-blue-50 rounded-xl">
              <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-4">
                <i data-lucide="zap" class="w-6 h-6 text-green-600"></i>
              </div>
              <h4 class="font-bold text-gray-900 mb-2">Fast</h4>
              <p class="text-sm text-gray-600">Optimized for speed and performance</p>
            </div>
          </div>

          <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900">Key Features</h3>
            <div class="grid grid-cols-2 gap-4">
              <div class="flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5 text-green-500"></i>
                <span class="text-gray-700">Role-based Access</span>
              </div>
              <div class="flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5 text-green-500"></i>
                <span class="text-gray-700">Appointment Scheduling</span>
              </div>
              <div class="flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5 text-green-500"></i>
                <span class="text-gray-700">Digital Prescriptions</span>
              </div>
              <div class="flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5 text-green-500"></i>
                <span class="text-gray-700">Billing & Payments</span>
              </div>
              <div class="flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5 text-green-500"></i>
                <span class="text-gray-700">Patient Records</span>
              </div>
              <div class="flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5 text-green-500"></i>
                <span class="text-gray-700">Analytics & Reports</span>
              </div>
            </div>
          </div>
        </div>

        <div class="relative">
          <div class="absolute inset-0 bg-gradient-to-r from-indigo-400 to-purple-400 rounded-3xl blur-3xl opacity-20 scale-110"></div>
          <img src="assets/img/dashboard.png" alt="Healsync Dashboard" class="relative rounded-3xl shadow-2xl floating"/>
          
          <!-- Floating Elements -->
          <div class="absolute -top-4 -right-4 bg-white rounded-2xl p-4 shadow-xl floating" style="animation-delay: 1s;">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                <i data-lucide="trending-up" class="w-5 h-5 text-green-600"></i>
              </div>
              <div>
                <p class="font-semibold text-gray-800">95% Efficiency</p>
                <p class="text-sm text-gray-500">Workflow improvement</p>
              </div>
            </div>
          </div>
          
          <div class="absolute -bottom-4 -left-4 bg-white rounded-2xl p-4 shadow-xl floating" style="animation-delay: 2s;">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                <i data-lucide="users" class="w-5 h-5 text-blue-600"></i>
              </div>
              <div>
                <p class="font-semibold text-gray-800">500+ Users</p>
                <p class="text-sm text-gray-500">Active daily</p>
              </div>
            </div>
          </div>
        </div>
    </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="relative py-24 bg-gradient-to-br from-gray-900 via-indigo-900 to-purple-900 overflow-hidden">
    <div class="absolute inset-0 bg-black/40"></div>
    <div class="absolute inset-0 hero-pattern opacity-10"></div>
    <div class="container mx-auto px-6 relative z-10">
      <div class="text-center max-w-4xl mx-auto">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 text-white text-sm font-medium mb-8">
          <i data-lucide="rocket" class="w-4 h-4"></i>
          Ready to Transform Your Healthcare?
        </div>
        <h2 class="text-4xl lg:text-6xl font-bold text-white mb-8">
          Start Your Journey with
          <span class="bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">
            Healsync
          </span>
        </h2>
        <p class="text-xl text-white/80 mb-12 max-w-2xl mx-auto">
          Join thousands of healthcare providers who trust Healsync to streamline their operations and improve patient care.
        </p>
        
        <div class="flex flex-col sm:flex-row gap-6 justify-center items-center mb-12">
          <a href="auth/register_patient.php" class="group relative px-8 py-4 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-xl font-semibold shadow-2xl hover:shadow-3xl transition-all duration-300 hover:scale-105 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-purple-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <span class="relative flex items-center justify-center gap-2">
              <i data-lucide="user-plus" class="w-5 h-5"></i>
              Get Started Free
            </span>
          </a>
          <a href="auth/login.php" class="group px-8 py-4 bg-white/10 backdrop-blur text-white rounded-xl font-semibold border border-white/20 hover:bg-white/20 transition-all duration-300 hover:scale-105 flex items-center justify-center gap-2">
            <i data-lucide="log-in" class="w-5 h-5"></i>
            Login to Dashboard
          </a>
        </div>

        <!-- Trust Indicators -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-2xl mx-auto">
          <div class="text-center">
            <div class="text-3xl font-bold text-white mb-2"><?= number_format((int)$totalUsers) ?></div>
            <div class="text-white/60">Total Users</div>
          </div>
          <div class="text-center">
            <div class="text-3xl font-bold text-white mb-2"><?= number_format((int)$totalPatients) ?></div>
            <div class="text-white/60">Patients Served</div>
          </div>
          <div class="text-center">
            <div class="text-3xl font-bold text-white mb-2"><?= number_format((int)$appointmentsThisWeek) ?></div>
            <div class="text-white/60">Appointments This Week</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-gray-900 text-gray-400">
    <div class="container mx-auto px-6 py-16">
      <div class="grid md:grid-cols-4 gap-8 mb-12">
        <!-- Company Info -->
        <div class="md:col-span-2">
          <div class="flex items-center gap-3 mb-6">
            <img src="assets/img/logo.png" alt="Healsync" class="h-10 w-10 rounded-full"/>
            <div>
              <h3 class="text-xl font-bold text-white">Healsync</h3>
              <p class="text-sm text-gray-400">Healthcare Management</p>
            </div>
          </div>
          <p class="text-gray-400 mb-6 max-w-md">
            Transforming healthcare operations with modern technology. 
            Secure, efficient, and designed for the future of medicine.
          </p>
          <div class="flex gap-4">
            <a href="#" class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-indigo-600 transition">
              <i data-lucide="twitter" class="w-5 h-5"></i>
            </a>
            <a href="#" class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-indigo-600 transition">
              <i data-lucide="linkedin" class="w-5 h-5"></i>
            </a>
            <a href="#" class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-indigo-600 transition">
              <i data-lucide="github" class="w-5 h-5"></i>
            </a>
          </div>
        </div>

        <!-- Quick Links -->
        <div>
          <h4 class="text-white font-semibold mb-4">Quick Links</h4>
          <ul class="space-y-3">
            <li><a href="#features" class="hover:text-white transition">Features</a></li>
            <li><a href="#about" class="hover:text-white transition">About</a></li>
            <li><a href="#demo" class="hover:text-white transition">Demo</a></li>
            <li><a href="auth/login.php" class="hover:text-white transition">Login</a></li>
            <li><a href="auth/register_patient.php" class="hover:text-white transition">Register</a></li>
          </ul>
        </div>

        <!-- Support -->
        <div>
          <h4 class="text-white font-semibold mb-4">Support</h4>
          <ul class="space-y-3">
            <li><a href="#" class="hover:text-white transition">Documentation</a></li>
            <li><a href="#" class="hover:text-white transition">Help Center</a></li>
            <li><a href="#" class="hover:text-white transition">Contact Us</a></li>
            <li><a href="#" class="hover:text-white transition">Privacy Policy</a></li>
            <li><a href="#" class="hover:text-white transition">Terms of Service</a></li>
          </ul>
        </div>
      </div>

      <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
        <p class="text-gray-400">© <?=date('Y')?> Healsync. All rights reserved.</p>
        <p class="text-gray-400 text-sm">Built with ❤️ for healthcare professionals</p>
      </div>
    </div>
  </footer>

  <script>
    // Initialize Lucide icons
    lucide.createIcons();

    // Mobile menu toggle
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    
    mobileMenuBtn.addEventListener('click', () => {
      mobileMenu.classList.toggle('hidden');
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        }
      });
    });

    // Add scroll effect to navigation
    window.addEventListener('scroll', () => {
      const nav = document.querySelector('nav');
      if (window.scrollY > 100) {
        nav.classList.add('bg-white/90', 'backdrop-blur-md');
        nav.classList.remove('glass-effect');
      } else {
        nav.classList.remove('bg-white/90', 'backdrop-blur-md');
        nav.classList.add('glass-effect');
      }
    });

    // Intersection Observer for animations
    const observerOptions = {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('animate__animated', 'animate__fadeInUp');
        }
      });
    }, observerOptions);

    // Observe elements for animation
    document.querySelectorAll('.group, .floating').forEach(el => {
      observer.observe(el);
    });

    // Add loading animation
    window.addEventListener('load', () => {
      document.body.classList.add('loaded');
    });
  </script>
</body>
</html>
