<?php
session_start();
include("dbconnection.php");
// Check if user is logged in (admin, doctor, or patient)
if(!isset($_SESSION['adminid']) && !isset($_SESSION['doctorid']) && !isset($_SESSION['patientid']))
{
	echo "<script>window.location='index.php';</script>";
	exit();
}
// Handle delete request
if(isset($_GET['delid']))
{
	$sql ="DELETE FROM appointment WHERE appointmentid='$_GET[delid]'";
	$qsql=mysqli_query($con,$sql);
	if(mysqli_affected_rows($con) == 1)
	{
		$success_message = "Appointment record deleted successfully.";
	}
	else
	{
		$error_message = "Failed to delete appointment record.";
	}
}
// Handle approve request
if(isset($_GET['approveid']))
{
	$sql ="UPDATE appointment SET status='Approved' WHERE appointmentid='$_GET[approveid]'";
	$qsql=mysqli_query($con,$sql);
	if(mysqli_affected_rows($con) == 1)
	{
		$success_message = "Appointment approved successfully.";
	}
	else
	{
		$error_message = "Failed to approve appointment.";
	}
}
// Determine user type
$user_type = '';
$user_name = '';
if(isset($_SESSION['adminid'])) {
	$user_type = 'admin';
	$user_name = 'Administrator';
} elseif(isset($_SESSION['doctorid'])) {
	$user_type = 'doctor';
	$user_name = 'Doctor';
} else {
	$user_type = 'patient';
	$user_name = 'Patient';
}
?>
<!DOCTYPE html>
<html class="no-js" lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Appointments - HealSync</title>
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
<!-- Modern Navigation -->
<nav class="bg-white shadow-sm border-b border-slate-200 sticky top-0 z-50">
    <div class="container-modern">
        <div class="flex items-center justify-between py-4">
            <!-- Brand -->
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-cyan-500 rounded-xl flex items-center justify-center">
                    <i data-lucide="calendar" class="w-6 h-6 text-white"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900">HealSync <?php echo $user_name; ?></h1>
                    <p class="text-xs text-slate-500">Appointment Management</p>
                </div>
            </div>
            <!-- Navigation Links -->
            <div class="flex items-center space-x-4">
                <?php if($user_type == 'admin'): ?>
                    <a href="admin.php" class="btn-modern btn-ghost">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        Back to Dashboard
                    </a>
                <?php elseif($user_type == 'doctor'): ?>
                    <a href="doctoraccount.php" class="btn-modern btn-ghost">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        Back to Dashboard
                    </a>
                <?php else: ?>
                    <a href="patientaccount.php" class="btn-modern btn-ghost">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        Back to Dashboard
                    </a>
                <?php endif; ?>
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
        <div class="text-center mb-8">
            <div class="inline-flex items-center px-4 py-2 bg-white rounded-full shadow-md mb-4">
                <i data-lucide="calendar-check" class="w-5 h-5 text-emerald-500 mr-2"></i>
                <span class="text-sm font-medium text-slate-700">Appointment Management</span>
            </div>
            <h1 class="text-h1 text-slate-900 mb-2">View Appointments</h1>
            <p class="text-slate-600">Manage and track all appointment records</p>
        </div>
        <!-- Success/Error Messages -->
        <?php if(isset($success_message)): ?>
            <div class="max-w-7xl mx-auto mb-6">
                <div class="bg-success/10 border border-success/20 rounded-xl p-4">
                    <div class="flex items-center space-x-3">
                        <i data-lucide="check-circle" class="w-5 h-5 text-success"></i>
                        <span class="text-success font-medium"><?php echo $success_message; ?></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if(isset($error_message)): ?>
            <div class="max-w-7xl mx-auto mb-6">
                <div class="bg-error/10 border border-error/20 rounded-xl p-4">
                    <div class="flex items-center space-x-3">
                        <i data-lucide="alert-circle" class="w-5 h-5 text-error"></i>
                        <span class="text-error font-medium"><?php echo $error_message; ?></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <!-- Appointments Table -->
        <div class="max-w-7xl mx-auto">
            <div class="card-modern">
                <div class="card-body p-0">
                    <!-- Table Header -->
                    <div class="p-6 border-b border-slate-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-h3 text-slate-900 flex items-center">
                                <i data-lucide="list" class="w-5 h-5 mr-2 text-emerald-500"></i>
                                Appointment Records
                            </h3>
                            <?php if($user_type == 'patient'): ?>
                                <a href="patientappointment.php" class="btn-modern btn-primary">
                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                    Book New Appointment
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <!-- Table Content -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i data-lucide="user" class="w-4 h-4"></i>
                                            <span>Patient Details</span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i data-lucide="calendar-clock" class="w-4 h-4"></i>
                                            <span>Date & Time</span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i data-lucide="building" class="w-4 h-4"></i>
                                            <span>Department</span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i data-lucide="stethoscope" class="w-4 h-4"></i>
                                            <span>Doctor</span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i data-lucide="file-text" class="w-4 h-4"></i>
                                            <span>Reason</span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        <div class="flex items-center space-x-2">
                                            <i data-lucide="activity" class="w-4 h-4"></i>
                                            <span>Status</span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        <div class="flex items-center justify-center space-x-2">
                                            <i data-lucide="settings" class="w-4 h-4"></i>
                                            <span>Actions</span>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                <?php
                                $sql ="SELECT * FROM appointment WHERE (status !='')";
                                if(isset($_SESSION['patientid']))
                                {
                                    $sql  = $sql . " AND patientid='$_SESSION[patientid]'";
                                }
                                $qsql = mysqli_query($con,$sql);
                                
                                if(mysqli_num_rows($qsql) > 0) {
                                    while($rs = mysqli_fetch_array($qsql))
                                    {
                                        $sqlpat = "SELECT * FROM patient WHERE patientid='$rs[patientid]'";
                                        $qsqlpat = mysqli_query($con,$sqlpat);
                                        $rspat = mysqli_fetch_array($qsqlpat);
                                        
                                        $sqldept = "SELECT * FROM department WHERE departmentid='$rs[departmentid]'";
                                        $qsqldept = mysqli_query($con,$sqldept);
                                        $rsdept = mysqli_fetch_array($qsqldept);
                                    
                                        $sqldoc= "SELECT * FROM doctor WHERE doctorid='$rs[doctorid]'";
                                        $qsqldoc = mysqli_query($con,$sqldoc);
                                        $rsdoc = mysqli_fetch_array($qsqldoc);
                                        
                                        // Status badge styling
                                        $status_class = '';
                                        switch(strtolower($rs['status'])) {
                                            case 'pending':
                                                $status_class = 'bg-warning/10 text-warning border-warning/20';
                                                break;
                                            case 'approved':
                                                $status_class = 'bg-success/10 text-success border-success/20';
                                                break;
                                            case 'cancelled':
                                                $status_class = 'bg-error/10 text-error border-error/20';
                                                break;
                                            default:
                                                $status_class = 'bg-slate-100 text-slate-600 border-slate-200';
                                        }
                                        
                                        echo "<tr class='hover:bg-slate-50 transition-colors duration-200'>
                                            <td class='px-6 py-4'>
                                                <div class='flex items-center space-x-3'>
                                                    <div class='w-10 h-10 bg-gradient-to-br from-emerald-500 to-cyan-500 rounded-full flex items-center justify-center'>
                                                        <i data-lucide='user' class='w-5 h-5 text-white'></i>
                                                    </div>
                                                    <div>
                                                        <div class='text-sm font-medium text-slate-900'>$rspat[patientname]</div>
                                                        <div class='text-sm text-slate-500'>$rspat[mobileno]</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class='px-6 py-4'>
                                                <div class='text-sm text-slate-900'>" . date("d M Y", strtotime($rs['appointmentdate'])) . "</div>
                                                <div class='text-sm text-slate-500'>" . date("h:i A", strtotime($rs['appointmenttime'])) . "</div>
                                            </td>
                                            <td class='px-6 py-4'>
                                                <div class='text-sm text-slate-900'>$rsdept[departmentname]</div>
                                            </td>
                                            <td class='px-6 py-4'>
                                                <div class='text-sm text-slate-900'>$rsdoc[doctorname]</div>
                                            </td>
                                            <td class='px-6 py-4'>
                                                <div class='text-sm text-slate-900 max-w-xs truncate' title='$rs[app_reason]'>$rs[app_reason]</div>
                                            </td>
                                            <td class='px-6 py-4'>
                                                <span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border $status_class'>
                                                    $rs[status]
                                                </span>
                                            </td>
                                            <td class='px-6 py-4 text-center'>
                                                <div class='flex items-center justify-center space-x-2'>";
                                        
                                        if($rs['status'] != "Approved")
                                        {
                                            if(!(isset($_SESSION['patientid'])))
                                            {
                                                echo "<a href='appointmentapproval.php?editid=$rs[appointmentid]' 
                                                         class='inline-flex items-center px-3 py-1.5 bg-success/10 text-success rounded-lg hover:bg-success/20 transition-colors duration-200'>
                                                        <i data-lucide='check' class='w-4 h-4 mr-1'></i>
                                                        Approve
                                                    </a>";
                                            }
                                            echo "<button onclick='confirmDelete($rs[appointmentid])' 
                                                         class='inline-flex items-center px-3 py-1.5 bg-error/10 text-error rounded-lg hover:bg-error/20 transition-colors duration-200'>
                                                        <i data-lucide='trash-2' class='w-4 h-4 mr-1'></i>
                                                        Delete
                                                    </button>";
                                        }
                                        else
                                        {
                                            echo "<a href='patientreport.php?patientid=$rs[patientid]&appointmentid=$rs[appointmentid]' 
                                                     class='inline-flex items-center px-3 py-1.5 bg-primary/10 text-primary rounded-lg hover:bg-primary/20 transition-colors duration-200'>
                                                    <i data-lucide='file-text' class='w-4 h-4 mr-1'></i>
                                                    View Report
                                                </a>";
                                        }
                                        
                                        echo "</div>
                                            </td>
                                        </tr>";
                                    }
                                } else {
                                    echo "<tr>
                                        <td colspan='7' class='px-6 py-12 text-center'>
                                            <div class='flex flex-col items-center space-y-4'>
                                                <div class='w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center'>
                                                    <i data-lucide='calendar-x' class='w-8 h-8 text-slate-400'></i>
                                                </div>
                                                <div>
                                                    <h3 class='text-lg font-medium text-slate-900 mb-1'>No Appointments Found</h3>
                                                    <p class='text-slate-500'>There are no appointment records to display.</p>
                                                </div>";
                                    if($user_type == 'patient') {
                                        echo "<a href='patientappointment.php' class='btn-modern btn-primary mt-4'>
                                                <i data-lucide='plus' class='w-4 h-4'></i>
                                                Book Your First Appointment
                                            </a>";
                                    }
                                    echo "</div>
                                        </td>
                                    </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
// Initialize Lucide icons
lucide.createIcons();
// Delete confirmation
function confirmDelete(appointmentId) {
    Swal.fire({
        title: 'Delete Appointment?',
        text: 'This action cannot be undone. The appointment record will be permanently deleted.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'viewappointment.php?delid=' + appointmentId;
        }
    });
}
// Success/Error message handling
<?php if(isset($success_message)): ?>
Swal.fire({
    icon: 'success',
    title: 'Success!',
    text: '<?php echo $success_message; ?>',
    confirmButtonColor: '#10b981'
});
<?php endif; ?>
<?php if(isset($error_message)): ?>
Swal.fire({
    icon: 'error',
    title: 'Error!',
    text: '<?php echo $error_message; ?>',
    confirmButtonColor: '#10b981'
});
<?php endif; ?>
</script>
</body>
</html>