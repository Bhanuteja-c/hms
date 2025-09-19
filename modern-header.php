<?php
error_reporting(0);
include("dbconnection.php");
date_default_timezone_set("asia/kolkata");
$dt = date("Y-m-d");
$tim = date("H:i:sa");
?>
<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="HealSync Team">
    <meta name="description" content="HealSync - Modern Healthcare Management System">
    
    <!-- Document Title -->
    <title>HealSync - Healthcare Management System</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="images/healsync-favicon.ico" type="image/x-icon">
    <link rel="icon" href="images/healsync-favicon.ico" type="image/x-icon">

    <!-- Modern CSS Framework -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'healsync': {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            900: '#0c4a6e'
                        }
                    },
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Modern Styles -->
    <link rel="stylesheet" href="css/modern-styles.css">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Legacy Support (for existing functionality) -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    
    <!-- SweetAlert2 for Modern Alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Modern JavaScript -->
    <script src="js/vendors/modernizr.js"></script>
    
    <!-- Custom Styles for Smooth Transition -->
    <style>
        /* Hide old styles during transition */
        .legacy-hide {
            display: none !important;
        }
        
        /* Modern page loader */
        #modern-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.5s ease-out;
        }
        
        .loader-content {
            text-align: center;
            color: white;
        }
        
        .loader-spinner {
            width: 50px;
            height: 50px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Modern navigation styles */
        .nav-modern {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .nav-modern.scrolled {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        /* Brand logo animation */
        .brand-logo {
            transition: transform 0.3s ease;
        }
        
        .brand-logo:hover {
            transform: scale(1.05);
        }
        
        /* Mobile menu animation */
        .mobile-menu {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }
        
        .mobile-menu.open {
            transform: translateX(0);
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900">

    <!-- Modern Page Loader -->
    <div id="modern-loader">
        <div class="loader-content">
            <div class="loader-spinner"></div>
            <h3 class="text-xl font-semibold mb-2">HealSync</h3>
            <p class="text-sm opacity-90">Loading Healthcare Management System...</p>
        </div>
    </div>

    <!-- Modern Navigation -->
    <nav class="nav-modern fixed w-full top-0 z-50">
        <div class="container-modern">
            <div class="flex items-center justify-between py-4">
                <!-- Brand Logo -->
                <div class="flex items-center space-x-3">
                    <div class="brand-logo">
                        <div class="w-10 h-10 bg-gradient-to-br from-healsync-500 to-healsync-600 rounded-xl flex items-center justify-center">
                            <i data-lucide="heart-pulse" class="w-6 h-6 text-white"></i>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-healsync-700">HealSync</h1>
                        <p class="text-xs text-slate-500">Healthcare Management</p>
                    </div>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="index.php" class="nav-link-modern active">
                        <i data-lucide="home" class="w-4 h-4"></i>
                        Home
                    </a>
                    <a href="patientlogin.php" class="nav-link-modern">
                        <i data-lucide="user" class="w-4 h-4"></i>
                        Patient Login
                    </a>
                    <a href="doctorlogin.php" class="nav-link-modern">
                        <i data-lucide="stethoscope" class="w-4 h-4"></i>
                        Doctor Login
                    </a>
                    <a href="adminlogin.php" class="nav-link-modern">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                        Admin Login
                    </a>
                    <a href="about.php" class="nav-link-modern">
                        <i data-lucide="info" class="w-4 h-4"></i>
                        About
                    </a>
                    <a href="contact.php" class="nav-link-modern">
                        <i data-lucide="phone" class="w-4 h-4"></i>
                        Contact
                    </a>
                </div>

                <!-- CTA Button -->
                <div class="hidden md:block">
                    <a href="appointment.php" class="btn-modern btn-primary">
                        <i data-lucide="calendar-plus" class="w-4 h-4"></i>
                        Book Appointment
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-slate-100 transition-colors">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="mobile-menu md:hidden fixed inset-y-0 left-0 w-64 bg-white shadow-xl z-50">
            <div class="p-6">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-gradient-to-br from-healsync-500 to-healsync-600 rounded-lg flex items-center justify-center">
                            <i data-lucide="heart-pulse" class="w-5 h-5 text-white"></i>
                        </div>
                        <span class="text-lg font-bold text-healsync-700">HealSync</span>
                    </div>
                    <button id="mobile-menu-close" class="p-2 rounded-lg hover:bg-slate-100">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                
                <nav class="space-y-4">
                    <a href="index.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-slate-100 transition-colors">
                        <i data-lucide="home" class="w-5 h-5 text-slate-500"></i>
                        <span>Home</span>
                    </a>
                    <a href="patientlogin.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-slate-100 transition-colors">
                        <i data-lucide="user" class="w-5 h-5 text-slate-500"></i>
                        <span>Patient Login</span>
                    </a>
                    <a href="doctorlogin.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-slate-100 transition-colors">
                        <i data-lucide="stethoscope" class="w-5 h-5 text-slate-500"></i>
                        <span>Doctor Login</span>
                    </a>
                    <a href="adminlogin.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-slate-100 transition-colors">
                        <i data-lucide="shield-check" class="w-5 h-5 text-slate-500"></i>
                        <span>Admin Login</span>
                    </a>
                    <a href="about.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-slate-100 transition-colors">
                        <i data-lucide="info" class="w-5 h-5 text-slate-500"></i>
                        <span>About</span>
                    </a>
                    <a href="contact.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-slate-100 transition-colors">
                        <i data-lucide="phone" class="w-5 h-5 text-slate-500"></i>
                        <span>Contact</span>
                    </a>
                    <div class="pt-4 border-t border-slate-200">
                        <a href="appointment.php" class="btn-modern btn-primary w-full justify-center">
                            <i data-lucide="calendar-plus" class="w-4 h-4"></i>
                            Book Appointment
                        </a>
                    </div>
                </nav>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu Overlay -->
    <div id="mobile-menu-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden md:hidden"></div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
        
        // Modern page loader
        window.addEventListener('load', function() {
            const loader = document.getElementById('modern-loader');
            setTimeout(() => {
                loader.style.opacity = '0';
                setTimeout(() => {
                    loader.style.display = 'none';
                }, 500);
            }, 1000);
        });
        
        // Navigation scroll effect
        window.addEventListener('scroll', function() {
            const nav = document.querySelector('.nav-modern');
            if (window.scrollY > 50) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
        });
        
        // Mobile menu functionality
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        const mobileMenuClose = document.getElementById('mobile-menu-close');
        const mobileMenuOverlay = document.getElementById('mobile-menu-overlay');
        
        function openMobileMenu() {
            mobileMenu.classList.add('open');
            mobileMenuOverlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        function closeMobileMenu() {
            mobileMenu.classList.remove('open');
            mobileMenuOverlay.classList.add('hidden');
            document.body.style.overflow = '';
        }
        
        mobileMenuBtn.addEventListener('click', openMobileMenu);
        mobileMenuClose.addEventListener('click', closeMobileMenu);
        mobileMenuOverlay.addEventListener('click', closeMobileMenu);
        
        // Close mobile menu on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMobileMenu();
            }
        });
    </script>