<?php
session_start();
include("dbconnection.php");

// Check if admin is logged in
if(!isset($_SESSION['adminid'])){
    echo "<script>window.location='adminlogin.php';</script>";
    exit();
}

// Get admin details
$adminid = $_SESSION['adminid'];
$sql = "SELECT * FROM admin WHERE adminid='$adminid'";
$qsql = mysqli_query($con, $sql);
$admin_data = mysqli_fetch_array($qsql);
?>
<!DOCTYPE html>
<html class="no-js" lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - HealSync</title>

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
          primary: '#0ea5e9',
          secondary: '#06b6d4',
          accent: '#8b5cf6',
          success: '#10b981',
          warning: '#f59e0b',
          error: '#ef4444',
        },
        fontFamily: {
          sans: ['Inter', 'system-ui', 'sans-serif'],
        },
      }
    }
  }
</script>

<!-- Modern StyleSheets -->
<link rel="stylesheet" href="css/modern-styles.css">

<!-- Modern Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<!-- Modern Icons -->
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-slate-50 min-h-screen">

<!-- Modern Admin Navigation -->
<nav class="bg-white shadow-sm border-b border-slate-200 sticky top-0 z-50">
    <div class="container-modern">
        <div class="flex items-center justify-between py-4">
            <!-- Brand -->
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                    <i data-lucide="shield-check" class="w-6 h-6 text-white"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900">HealSync Admin</h1>
                    <p class="text-xs text-slate-500">Healthcare Management</p>
                </div>
            </div>

            <!-- Admin Info & Actions -->
            <div class="flex items-center space-x-4">
                <div class="hidden md:block text-right">
                    <p class="text-sm font-medium text-slate-900">Welcome, <?php echo $admin_data['adminname']; ?></p>
                    <p class="text-xs text-slate-500">Administrator</p>
                </div>
                
                <!-- Logout Button -->
                <a href="logout.php" class="btn-modern btn-secondary">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Main Content -->
<main class="py-8">
    <div class="container-modern">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-h1 text-slate-900 mb-2">Admin Dashboard</h1>
            <p class="text-slate-600">Overview of hospital operations and key metrics</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid-modern grid-cols-1 md:grid-cols-2 lg:grid-cols-4 mb-8">
            <!-- Total Patients -->
            <div class="card-modern hover-lift">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">Total Patients</p>
                            <p class="text-3xl font-bold text-slate-900">
                                <?php
                                $sql = "SELECT * FROM patient WHERE status='Active'";
                                $qsql = mysqli_query($con,$sql);
                                echo mysqli_num_rows($qsql);
                                ?>
                            </p>
                            <p class="text-xs text-green-600 mt-1">
                                <i data-lucide="trending-up" class="w-3 h-3 inline"></i>
                                Active patients
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                            <i data-lucide="users" class="w-6 h-6 text-blue-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Doctors -->
            <div class="card-modern hover-lift">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">Total Doctors</p>
                            <p class="text-3xl font-bold text-slate-900">
                                <?php
                                $sql = "SELECT * FROM doctor WHERE status='Active'";
                                $qsql = mysqli_query($con,$sql);
                                echo mysqli_num_rows($qsql);
                                ?>
                            </p>
                            <p class="text-xs text-green-600 mt-1">
                                <i data-lucide="trending-up" class="w-3 h-3 inline"></i>
                                Active doctors
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                            <i data-lucide="stethoscope" class="w-6 h-6 text-emerald-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Administrators -->
            <div class="card-modern hover-lift">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">Administrators</p>
                            <p class="text-3xl font-bold text-slate-900">
                                <?php
                                $sql = "SELECT * FROM admin WHERE status='Active'";
                                $qsql = mysqli_query($con,$sql);
                                echo mysqli_num_rows($qsql);
                                ?>
                            </p>
                            <p class="text-xs text-purple-600 mt-1">
                                <i data-lucide="shield-check" class="w-3 h-3 inline"></i>
                                System admins
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                            <i data-lucide="shield" class="w-6 h-6 text-purple-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hospital Earnings -->
            <div class="card-modern hover-lift">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">Total Earnings</p>
                            <p class="text-3xl font-bold text-slate-900">
                                ₹<?php 
                                $sql = "SELECT sum(bill_amount) as total FROM `billing_records`";
                                $qsql = mysqli_query($con,$sql);
                                $row = mysqli_fetch_assoc($qsql);
                                echo number_format($row['total'] ?? 0);
                                ?>
                            </p>
                            <p class="text-xs text-green-600 mt-1">
                                <i data-lucide="trending-up" class="w-3 h-3 inline"></i>
                                Revenue generated
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                            <i data-lucide="indian-rupee" class="w-6 h-6 text-green-600"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid-modern grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Management Actions -->
            <div class="card-modern">
                <div class="card-header">
                    <h3 class="text-h3 text-slate-900 flex items-center">
                        <i data-lucide="settings" class="w-5 h-5 mr-2 text-primary"></i>
                        Management
                    </h3>
                    <p class="text-slate-600">Manage hospital resources and staff</p>
                </div>
                <div class="card-body">
                    <div class="grid-modern grid-cols-2 gap-4">
                        <a href="patient.php" class="flex flex-col items-center p-4 rounded-xl hover:bg-slate-50 transition-colors">
                            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-3">
                                <i data-lucide="user-plus" class="w-6 h-6 text-blue-600"></i>
                            </div>
                            <span class="text-sm font-medium text-slate-900">Add Patient</span>
                        </a>
                        
                        <a href="doctor.php" class="flex flex-col items-center p-4 rounded-xl hover:bg-slate-50 transition-colors">
                            <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center mb-3">
                                <i data-lucide="user-check" class="w-6 h-6 text-emerald-600"></i>
                            </div>
                            <span class="text-sm font-medium text-slate-900">Add Doctor</span>
                        </a>
                        
                        <a href="department.php" class="flex flex-col items-center p-4 rounded-xl hover:bg-slate-50 transition-colors">
                            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-3">
                                <i data-lucide="building" class="w-6 h-6 text-purple-600"></i>
                            </div>
                            <span class="text-sm font-medium text-slate-900">Departments</span>
                        </a>
                        
                        <a href="admin.php" class="flex flex-col items-center p-4 rounded-xl hover:bg-slate-50 transition-colors">
                            <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center mb-3">
                                <i data-lucide="shield-plus" class="w-6 h-6 text-orange-600"></i>
                            </div>
                            <span class="text-sm font-medium text-slate-900">Add Admin</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- View Reports -->
            <div class="card-modern">
                <div class="card-header">
                    <h3 class="text-h3 text-slate-900 flex items-center">
                        <i data-lucide="bar-chart-3" class="w-5 h-5 mr-2 text-primary"></i>
                        Reports & Views
                    </h3>
                    <p class="text-slate-600">View detailed reports and listings</p>
                </div>
                <div class="card-body">
                    <div class="grid-modern grid-cols-2 gap-4">
                        <a href="viewpatient.php" class="flex flex-col items-center p-4 rounded-xl hover:bg-slate-50 transition-colors">
                            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-3">
                                <i data-lucide="users" class="w-6 h-6 text-blue-600"></i>
                            </div>
                            <span class="text-sm font-medium text-slate-900">View Patients</span>
                        </a>
                        
                        <a href="viewdoctor.php" class="flex flex-col items-center p-4 rounded-xl hover:bg-slate-50 transition-colors">
                            <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center mb-3">
                                <i data-lucide="stethoscope" class="w-6 h-6 text-emerald-600"></i>
                            </div>
                            <span class="text-sm font-medium text-slate-900">View Doctors</span>
                        </a>
                        
                        <a href="viewappointment.php" class="flex flex-col items-center p-4 rounded-xl hover:bg-slate-50 transition-colors">
                            <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center mb-3">
                                <i data-lucide="calendar" class="w-6 h-6 text-yellow-600"></i>
                            </div>
                            <span class="text-sm font-medium text-slate-900">Appointments</span>
                        </a>
                        
                        <a href="viewbilling.php" class="flex flex-col items-center p-4 rounded-xl hover:bg-slate-50 transition-colors">
                            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-3">
                                <i data-lucide="receipt" class="w-6 h-6 text-green-600"></i>
                            </div>
                            <span class="text-sm font-medium text-slate-900">Billing</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// Initialize Lucide icons
lucide.createIcons();

// Add some interactive features
document.addEventListener('DOMContentLoaded', function() {
    // Add hover effects to stat cards
    const statCards = document.querySelectorAll('.hover-lift');
    statCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>

</body>
</html>
