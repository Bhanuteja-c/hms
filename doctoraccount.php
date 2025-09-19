
<?php
session_start();
include('dbconnection.php');

// Check if doctor is logged in
if(!isset($_SESSION['doctorid']))
{
	echo "<script>window.location='doctorlogin.php';</script>";
	exit();
}

// Get doctor details
$sql = "SELECT * FROM doctor WHERE doctorid='$_SESSION[doctorid]'";
$doctortable = mysqli_query($con,$sql);
$doc = mysqli_fetch_array($doctortable);

// Get department details
$sqldept = "SELECT * FROM department WHERE departmentid='$doc[departmentid]'";
$qsqldept = mysqli_query($con,$sqldept);
$rsdept = mysqli_fetch_array($qsqldept);

// Get today's appointments
$sqltoday = "SELECT COUNT(*) as today FROM appointment WHERE doctorid='$_SESSION[doctorid]' AND appointmentdate='".date("Y-m-d")."'";
$qsqltoday = mysqli_query($con,$sqltoday);
$rstoday = mysqli_fetch_array($qsqltoday);

// Get total patients
$sqlpatients = "SELECT COUNT(DISTINCT patientid) as total FROM appointment WHERE doctorid='$_SESSION[doctorid]'";
$qsqlpatients = mysqli_query($con,$sqlpatients);
$rspatients = mysqli_fetch_array($qsqlpatients);

// Get pending appointments
$sqlpending = "SELECT COUNT(*) as pending FROM appointment WHERE doctorid='$_SESSION[doctorid]' AND status='Pending'";
$qsqlpending = mysqli_query($con,$sqlpending);
$rspending = mysqli_fetch_array($qsqlpending);

// Get approved appointments
$sqlapproved = "SELECT COUNT(*) as approved FROM appointment WHERE doctorid='$_SESSION[doctorid]' AND status='Approved'";
$qsqlapproved = mysqli_query($con,$sqlapproved);
$rsapproved = mysqli_fetch_array($qsqlapproved);
?>
<!DOCTYPE html>
<html class="no-js" lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Doctor Dashboard - HealSync</title>

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

<!-- Modern Doctor Navigation -->
<nav class="bg-white shadow-sm border-b border-slate-200 sticky top-0 z-50">
    <div class="container-modern">
        <div class="flex items-center justify-between py-4">
            <!-- Brand -->
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-cyan-500 rounded-xl flex items-center justify-center">
                    <i data-lucide="stethoscope" class="w-6 h-6 text-white"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900">HealSync Doctor</h1>
                    <p class="text-xs text-slate-500">Medical Portal</p>
                </div>
            </div>

            <!-- Doctor Info & Actions -->
            <div class="flex items-center space-x-4">
                <div class="hidden md:block text-right">
                    <p class="text-sm font-medium text-slate-900">Dr. <?php echo $doc['doctorname']; ?></p>
                    <p class="text-xs text-slate-500"><?php echo $rsdept['departmentname'] ?? 'Department'; ?></p>
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
            <div class="bg-gradient-to-r from-emerald-500 to-cyan-500 rounded-2xl p-8 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold mb-2">Welcome, Dr. <?php echo $doc['doctorname']; ?>!</h1>
                        <p class="text-emerald-100">
                            <?php echo $rsdept['departmentname'] ?? 'Medical'; ?> Department • 
                            <?php echo date('l, F j, Y'); ?>
                        </p>
                    </div>
                    <div class="hidden md:block">
                        <div class="w-20 h-20 bg-white/20 rounded-2xl flex items-center justify-center">
                            <i data-lucide="user-check" class="w-10 h-10 text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid-modern grid-cols-1 md:grid-cols-2 lg:grid-cols-4 mb-8">
            <!-- Today's Appointments -->
            <div class="card-modern hover-lift">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">Today's Appointments</p>
                            <p class="text-3xl font-bold text-slate-900"><?php echo $rstoday['today'] ?? 0; ?></p>
                            <p class="text-xs text-primary mt-1">
                                <i data-lucide="calendar" class="w-3 h-3 inline"></i>
                                <?php echo date('M j, Y'); ?>
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                            <i data-lucide="calendar-check" class="w-6 h-6 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Patients -->
            <div class="card-modern hover-lift">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">My Patients</p>
                            <p class="text-3xl font-bold text-slate-900"><?php echo $rspatients['total'] ?? 0; ?></p>
                            <p class="text-xs text-success mt-1">
                                <i data-lucide="users" class="w-3 h-3 inline"></i>
                                Total patients
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-success/10 rounded-xl flex items-center justify-center">
                            <i data-lucide="users" class="w-6 h-6 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Appointments -->
            <div class="card-modern hover-lift">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">Pending Approval</p>
                            <p class="text-3xl font-bold text-slate-900"><?php echo $rspending['pending'] ?? 0; ?></p>
                            <p class="text-xs text-warning mt-1">
                                <i data-lucide="clock" class="w-3 h-3 inline"></i>
                                Awaiting review
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-warning/10 rounded-xl flex items-center justify-center">
                            <i data-lucide="clock" class="w-6 h-6 text-warning"></i>
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
                            <p class="text-xs text-emerald-600 mt-1">
                                <i data-lucide="check-circle" class="w-3 h-3 inline"></i>
                                Confirmed visits
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                            <i data-lucide="check-circle" class="w-6 h-6 text-emerald-600"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid-modern grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Doctor Information -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Doctor Profile Card -->
                <div class="card-modern">
                    <div class="card-header">
                        <h3 class="text-h3 text-slate-900 flex items-center">
                            <i data-lucide="user-check" class="w-5 h-5 mr-2 text-emerald-500"></i>
                            Doctor Profile
                        </h3>
                        <p class="text-slate-600">Your professional information</p>
                    </div>
                    <div class="card-body">
                        <div class="grid-modern grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <label class="text-sm font-medium text-slate-600">Full Name</label>
                                    <p class="text-slate-900 font-semibold">Dr. <?php echo $doc['doctorname']; ?></p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-slate-600">Department</label>
                                    <p class="text-slate-900"><?php echo $rsdept['departmentname'] ?? 'Not assigned'; ?></p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-slate-600">Mobile Number</label>
                                    <p class="text-slate-900"><?php echo $doc['mobileno']; ?></p>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="text-sm font-medium text-slate-600">Education</label>
                                    <p class="text-slate-900"><?php echo $doc['education'] ?: 'Not specified'; ?></p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-slate-600">Experience</label>
                                    <p class="text-slate-900"><?php echo $doc['experience'] ? $doc['experience'] . ' years' : 'Not specified'; ?></p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-slate-600">Consultation Fee</label>
                                    <p class="text-slate-900">₹<?php echo number_format($doc['consultancy_charge'] ?? 0); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-6 pt-6 border-t border-slate-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-success/10 text-success">
                                        <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i>
                                        <?php echo $doc['status']; ?>
                                    </span>
                                </div>
                                <a href="doctorprofile.php" class="btn-modern btn-secondary">
                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                    Update Profile
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card-modern">
                    <div class="card-header">
                        <h3 class="text-h3 text-slate-900 flex items-center">
                            <i data-lucide="activity" class="w-5 h-5 mr-2 text-emerald-500"></i>
                            Today's Schedule
                        </h3>
                        <p class="text-slate-600">Your appointments for today</p>
                    </div>
                    <div class="card-body">
                        <?php
                        $sqltoday_appointments = "SELECT a.*, p.patientname FROM appointment a 
                                                 LEFT JOIN patient p ON a.patientid = p.patientid 
                                                 WHERE a.doctorid='$_SESSION[doctorid]' 
                                                 AND a.appointmentdate='".date("Y-m-d")."' 
                                                 ORDER BY a.appointmenttime ASC LIMIT 5";
                        $qsqltoday_appointments = mysqli_query($con,$sqltoday_appointments);
                        
                        if(mysqli_num_rows($qsqltoday_appointments) == 0): ?>
                            <div class="text-center py-8">
                                <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                    <i data-lucide="calendar-x" class="w-8 h-8 text-slate-400"></i>
                                </div>
                                <h4 class="font-semibold text-slate-900 mb-2">No Appointments Today</h4>
                                <p class="text-slate-600">You have a free day! Enjoy your time.</p>
                            </div>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php while($appointment = mysqli_fetch_array($qsqltoday_appointments)): ?>
                                <div class="flex items-center space-x-4 p-4 rounded-xl border border-slate-200 hover:border-emerald-200 transition-colors">
                                    <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                                        <i data-lucide="user" class="w-5 h-5 text-emerald-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-slate-900"><?php echo $appointment['patientname']; ?></h4>
                                        <p class="text-sm text-slate-600">
                                            <?php echo date('g:i A', strtotime($appointment['appointmenttime'])); ?>
                                        </p>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                        <?php 
                                        if($appointment['status'] == 'Approved') echo 'bg-success/10 text-success';
                                        elseif($appointment['status'] == 'Pending') echo 'bg-warning/10 text-warning';
                                        else echo 'bg-slate/10 text-slate-600';
                                        ?>">
                                        <?php echo $appointment['status']; ?>
                                    </span>
                                </div>
                                <?php endwhile; ?>
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
                            <i data-lucide="zap" class="w-5 h-5 mr-2 text-emerald-500"></i>
                            Quick Actions
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="space-y-3">
                            <a href="viewappointment.php" class="flex items-center p-3 rounded-xl hover:bg-slate-50 transition-colors group">
                                <div class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center mr-3 group-hover:bg-primary/20">
                                    <i data-lucide="calendar" class="w-5 h-5 text-primary"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-slate-900">View Appointments</p>
                                    <p class="text-xs text-slate-500">Manage patient visits</p>
                                </div>
                            </a>
                            
                            <a href="doctorprofile.php" class="flex items-center p-3 rounded-xl hover:bg-slate-50 transition-colors group">
                                <div class="w-10 h-10 bg-secondary/10 rounded-xl flex items-center justify-center mr-3 group-hover:bg-secondary/20">
                                    <i data-lucide="user-cog" class="w-5 h-5 text-secondary"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-slate-900">Update Profile</p>
                                    <p class="text-xs text-slate-500">Edit your information</p>
                                </div>
                            </a>
                            
                            <a href="doctortimings.php" class="flex items-center p-3 rounded-xl hover:bg-slate-50 transition-colors group">
                                <div class="w-10 h-10 bg-success/10 rounded-xl flex items-center justify-center mr-3 group-hover:bg-success/20">
                                    <i data-lucide="clock" class="w-5 h-5 text-success"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-slate-900">Set Timings</p>
                                    <p class="text-xs text-slate-500">Manage availability</p>
                                </div>
                            </a>
                            
                            <a href="doctorchangepassword.php" class="flex items-center p-3 rounded-xl hover:bg-slate-50 transition-colors group">
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

                <!-- Hospital Stats -->
                <div class="card-modern">
                    <div class="card-header">
                        <h3 class="text-h3 text-slate-900 flex items-center">
                            <i data-lucide="building" class="w-5 h-5 mr-2 text-emerald-500"></i>
                            Hospital Overview
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-600">Total Revenue</span>
                                <span class="font-semibold text-slate-900">
                                    ₹<?php 
                                    $sql = "SELECT sum(bill_amount) as total FROM billing_records";
                                    $qsql = mysqli_query($con,$sql);
                                    $row = mysqli_fetch_assoc($qsql);
                                    echo number_format($row['total'] ?? 0);
                                    ?>
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-600">Active Patients</span>
                                <span class="font-semibold text-slate-900">
                                    <?php
                                    $sql = "SELECT COUNT(*) as total FROM patient WHERE status='Active'";
                                    $qsql = mysqli_query($con,$sql);
                                    $row = mysqli_fetch_assoc($qsql);
                                    echo $row['total'];
                                    ?>
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-600">Your Department</span>
                                <span class="font-semibold text-slate-900"><?php echo $rsdept['departmentname'] ?? 'N/A'; ?></span>
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