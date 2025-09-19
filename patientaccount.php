<?php
session_start();
include("dbconnection.php");

// Check if patient is logged in
if(!isset($_SESSION['patientid']))
{
	echo "<script>window.location='patientlogin.php';</script>";
	exit();
}

// Get patient details
$sqlpatient = "SELECT * FROM patient WHERE patientid='$_SESSION[patientid]'";
$qsqlpatient = mysqli_query($con,$sqlpatient);
$rspatient = mysqli_fetch_array($qsqlpatient);

// Get latest appointment
$sqlpatientappointment = "SELECT * FROM appointment WHERE patientid='$_SESSION[patientid]' order by appointmentid DESC limit 1";
$qsqlpatientappointment = mysqli_query($con,$sqlpatientappointment);
$rspatientappointment = mysqli_fetch_array($qsqlpatientappointment);

// Get appointment statistics
$sqlappointments = "SELECT COUNT(*) as total FROM appointment WHERE patientid='$_SESSION[patientid]'";
$qsqlappointments = mysqli_query($con,$sqlappointments);
$rsappointments = mysqli_fetch_array($qsqlappointments);

$sqlapproved = "SELECT COUNT(*) as approved FROM appointment WHERE patientid='$_SESSION[patientid]' AND status='Approved'";
$qsqlapproved = mysqli_query($con,$sqlapproved);
$rsapproved = mysqli_fetch_array($qsqlapproved);

$sqlpending = "SELECT COUNT(*) as pending FROM appointment WHERE patientid='$_SESSION[patientid]' AND status='Pending'";
$qsqlpending = mysqli_query($con,$sqlpending);
$rspending = mysqli_fetch_array($qsqlpending);
?>
<!DOCTYPE html>
<html class="no-js" lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Patient Dashboard - HealSync</title>

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

<!-- Modern Patient Navigation -->
<nav class="bg-white shadow-sm border-b border-slate-200 sticky top-0 z-50">
    <div class="container-modern">
        <div class="flex items-center justify-between py-4">
            <!-- Brand -->
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-primary to-secondary rounded-xl flex items-center justify-center">
                    <i data-lucide="user" class="w-6 h-6 text-white"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900">HealSync Patient</h1>
                    <p class="text-xs text-slate-500">Healthcare Portal</p>
                </div>
            </div>

            <!-- Patient Info & Actions -->
            <div class="flex items-center space-x-4">
                <div class="hidden md:block text-right">
                    <p class="text-sm font-medium text-slate-900">Welcome, <?php echo $rspatient['patientname']; ?></p>
                    <p class="text-xs text-slate-500">Patient ID: <?php echo $rspatient['patientid']; ?></p>
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
        <!-- Welcome Header -->
        <div class="mb-8">
            <div class="bg-gradient-to-r from-primary to-secondary rounded-2xl p-8 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold mb-2">Welcome back, <?php echo $rspatient['patientname']; ?>!</h1>
                        <p class="text-primary-100">Manage your healthcare journey with HealSync</p>
                    </div>
                    <div class="hidden md:block">
                        <div class="w-20 h-20 bg-white/20 rounded-2xl flex items-center justify-center">
                            <i data-lucide="heart-pulse" class="w-10 h-10 text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid-modern grid-cols-1 md:grid-cols-3 mb-8">
            <!-- Total Appointments -->
            <div class="card-modern hover-lift">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">Total Appointments</p>
                            <p class="text-3xl font-bold text-slate-900"><?php echo $rsappointments['total'] ?? 0; ?></p>
                            <p class="text-xs text-primary mt-1">
                                <i data-lucide="calendar" class="w-3 h-3 inline"></i>
                                All time
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                            <i data-lucide="calendar-check" class="w-6 h-6 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approved Appointments -->
            <div class="card-modern hover-lift">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">Approved</p>
                            <p class="text-3xl font-bold text-slate-900"><?php echo $rsapproved['approved'] ?? 0; ?></p>
                            <p class="text-xs text-success mt-1">
                                <i data-lucide="check-circle" class="w-3 h-3 inline"></i>
                                Confirmed
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-success/10 rounded-xl flex items-center justify-center">
                            <i data-lucide="check-circle" class="w-6 h-6 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Appointments -->
            <div class="card-modern hover-lift">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">Pending</p>
                            <p class="text-3xl font-bold text-slate-900"><?php echo $rspending['pending'] ?? 0; ?></p>
                            <p class="text-xs text-warning mt-1">
                                <i data-lucide="clock" class="w-3 h-3 inline"></i>
                                Awaiting approval
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-warning/10 rounded-xl flex items-center justify-center">
                            <i data-lucide="clock" class="w-6 h-6 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid-modern grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Patient Information -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Registration History -->
                <div class="card-modern">
                    <div class="card-header">
                        <h3 class="text-h3 text-slate-900 flex items-center">
                            <i data-lucide="user-check" class="w-5 h-5 mr-2 text-primary"></i>
                            Registration History
                        </h3>
                        <p class="text-slate-600">Your journey with HealSync</p>
                    </div>
                    <div class="card-body">
                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center flex-shrink-0">
                                <i data-lucide="calendar-plus" class="w-6 h-6 text-primary"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-slate-900 mb-2">Registered with HealSync</h4>
                                <p class="text-slate-600 mb-2">
                                    You joined our healthcare family on 
                                    <span class="font-medium text-slate-900">
                                        <?php echo date('F j, Y', strtotime($rspatient['admissiondate'])); ?>
                                    </span>
                                    at 
                                    <span class="font-medium text-slate-900">
                                        <?php echo date('g:i A', strtotime($rspatient['admissiontime'])); ?>
                                    </span>
                                </p>
                                <div class="flex items-center space-x-4 text-sm text-slate-500">
                                    <span class="flex items-center">
                                        <i data-lucide="map-pin" class="w-4 h-4 mr-1"></i>
                                        <?php echo $rspatient['city']; ?>
                                    </span>
                                    <span class="flex items-center">
                                        <i data-lucide="phone" class="w-4 h-4 mr-1"></i>
                                        <?php echo $rspatient['mobileno']; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Latest Appointment -->
                <div class="card-modern">
                    <div class="card-header">
                        <h3 class="text-h3 text-slate-900 flex items-center">
                            <i data-lucide="calendar" class="w-5 h-5 mr-2 text-primary"></i>
                            Latest Appointment
                        </h3>
                        <p class="text-slate-600">Your most recent appointment details</p>
                    </div>
                    <div class="card-body">
                        <?php if(mysqli_num_rows($qsqlpatientappointment) == 0): ?>
                            <div class="text-center py-8">
                                <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                    <i data-lucide="calendar-x" class="w-8 h-8 text-slate-400"></i>
                                </div>
                                <h4 class="font-semibold text-slate-900 mb-2">No Appointments Yet</h4>
                                <p class="text-slate-600 mb-6">You haven't booked any appointments with us yet.</p>
                                <a href="patientappointment.php" class="btn-modern btn-primary">
                                    <i data-lucide="calendar-plus" class="w-4 h-4"></i>
                                    Book Your First Appointment
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="flex items-start space-x-4">
                                <div class="w-12 h-12 bg-success/10 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="calendar-check" class="w-6 h-6 text-success"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-slate-900 mb-2">
                                        Appointment on <?php echo date('F j, Y', strtotime($rspatientappointment['appointmentdate'])); ?>
                                    </h4>
                                    <p class="text-slate-600 mb-3">
                                        Scheduled for <?php echo date('g:i A', strtotime($rspatientappointment['appointmenttime'])); ?>
                                    </p>
                                    <div class="flex items-center justify-between">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                            <?php 
                                            if($rspatientappointment['status'] == 'Approved') echo 'bg-success/10 text-success';
                                            elseif($rspatientappointment['status'] == 'Pending') echo 'bg-warning/10 text-warning';
                                            else echo 'bg-slate/10 text-slate-600';
                                            ?>">
                                            <?php echo $rspatientappointment['status']; ?>
                                        </span>
                                        <a href="viewappointment.php" class="text-primary hover:text-primary-dark text-sm font-medium">
                                            View Details →
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="card-modern">
                    <div class="card-header">
                        <h3 class="text-h3 text-slate-900 flex items-center">
                            <i data-lucide="zap" class="w-5 h-5 mr-2 text-primary"></i>
                            Quick Actions
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="space-y-3">
                            <a href="patientappointment.php" class="flex items-center p-3 rounded-xl hover:bg-slate-50 transition-colors group">
                                <div class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center mr-3 group-hover:bg-primary/20">
                                    <i data-lucide="calendar-plus" class="w-5 h-5 text-primary"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-slate-900">Book Appointment</p>
                                    <p class="text-xs text-slate-500">Schedule a new visit</p>
                                </div>
                            </a>
                            
                            <a href="patientprofile.php" class="flex items-center p-3 rounded-xl hover:bg-slate-50 transition-colors group">
                                <div class="w-10 h-10 bg-secondary/10 rounded-xl flex items-center justify-center mr-3 group-hover:bg-secondary/20">
                                    <i data-lucide="user-cog" class="w-5 h-5 text-secondary"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-slate-900">Update Profile</p>
                                    <p class="text-xs text-slate-500">Manage your information</p>
                                </div>
                            </a>
                            
                            <a href="viewappointment.php" class="flex items-center p-3 rounded-xl hover:bg-slate-50 transition-colors group">
                                <div class="w-10 h-10 bg-success/10 rounded-xl flex items-center justify-center mr-3 group-hover:bg-success/20">
                                    <i data-lucide="calendar" class="w-5 h-5 text-success"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-slate-900">View Appointments</p>
                                    <p class="text-xs text-slate-500">Check appointment history</p>
                                </div>
                            </a>
                            
                            <a href="patientchangepassword.php" class="flex items-center p-3 rounded-xl hover:bg-slate-50 transition-colors group">
                                <div class="w-10 h-10 bg-warning/10 rounded-xl flex items-center justify-center mr-3 group-hover:bg-warning/20">
                                    <i data-lucide="key" class="w-5 h-5 text-warning"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-slate-900">Change Password</p>
                                    <p class="text-xs text-slate-500">Update security</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Patient Info Card -->
                <div class="card-modern">
                    <div class="card-header">
                        <h3 class="text-h3 text-slate-900 flex items-center">
                            <i data-lucide="user" class="w-5 h-5 mr-2 text-primary"></i>
                            Your Information
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-slate-600">Blood Group:</span>
                                <span class="font-medium text-slate-900"><?php echo $rspatient['bloodgroup'] ?: 'Not specified'; ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600">Gender:</span>
                                <span class="font-medium text-slate-900"><?php echo $rspatient['gender']; ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600">Date of Birth:</span>
                                <span class="font-medium text-slate-900">
                                    <?php echo $rspatient['dob'] ? date('M j, Y', strtotime($rspatient['dob'])) : 'Not specified'; ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600">Status:</span>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-success/10 text-success">
                                    <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i>
                                    <?php echo $rspatient['status']; ?>
                                </span>
                            </div>
                        </div>
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
    
    // Add welcome animation
    const welcomeCard = document.querySelector('.bg-gradient-to-r');
    if (welcomeCard) {
        welcomeCard.style.opacity = '0';
        welcomeCard.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            welcomeCard.style.transition = 'all 0.6s ease-out';
            welcomeCard.style.opacity = '1';
            welcomeCard.style.transform = 'translateY(0)';
        }, 100);
    }
});
</script>

</body>
</html>