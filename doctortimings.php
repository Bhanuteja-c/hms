<?php
session_start();
include("dbconnection.php");
// Check if user is logged in (admin or doctor)
if(!isset($_SESSION['adminid']) && !isset($_SESSION['doctorid']))
{
	echo "<script>window.location='index.php';</script>";
	exit();
}
// Handle form submission
if(isset($_POST['submit']))
{
	if(isset($_GET['editid']))
	{
		$sql ="UPDATE doctor_timings SET doctorid='$_POST[select2]',start_time='$_POST[ftime]',end_time='$_POST[ttime]',status='$_POST[select]' WHERE doctor_timings_id='$_GET[editid]'";
		if($qsql = mysqli_query($con,$sql))
		{
			$success_message = "Doctor timings updated successfully!";
		}
		else
		{
			$error_message = "Error updating doctor timings: " . mysqli_error($con);
		}	
	}
	else
	{
		$sql ="INSERT INTO doctor_timings(doctorid,start_time,end_time,status) values('$_POST[select2]','$_POST[ftime]','$_POST[ttime]','$_POST[select]')";
		if($qsql = mysqli_query($con,$sql))
		{
			$success_message = "Doctor timings added successfully!";
		}
		else
		{
			$error_message = "Error adding doctor timings: " . mysqli_error($con);
		}
	}
}
// Get edit data if editing
if(isset($_GET['editid']))
{
	$sql="SELECT * FROM doctor_timings WHERE doctor_timings_id='$_GET[editid]'";
	$qsql = mysqli_query($con,$sql);
	$rsedit = mysqli_fetch_array($qsql);
}
// Determine user type
$user_type = isset($_SESSION['adminid']) ? 'admin' : 'doctor';
$page_title = isset($_GET['editid']) ? 'Edit Doctor Timings' : 'Add Doctor Timings';
?>


<!DOCTYPE html>
<html class="no-js" lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $page_title; ?> - HealSync</title>
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
                    <i data-lucide="clock" class="w-6 h-6 text-white"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900">HealSync <?php echo $user_type == 'admin' ? 'Admin' : 'Doctor'; ?></h1>
                    <p class="text-xs text-slate-500">Schedule Management</p>
                </div>
            </div>
            <!-- Navigation Links -->
            <div class="flex items-center space-x-4">
                <?php if($user_type == 'admin'): ?>
                    <a href="admin.php" class="btn-modern btn-ghost">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        Back to Dashboard
                    </a>
                <?php else: ?>
                    <a href="doctoraccount.php" class="btn-modern btn-ghost">
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
                <i data-lucide="calendar-clock" class="w-5 h-5 text-emerald-500 mr-2"></i>
                <span class="text-sm font-medium text-slate-700">Schedule Management</span>
            </div>
            <h1 class="text-h1 text-slate-900 mb-2"><?php echo $page_title; ?></h1>
            <p class="text-slate-600">Set up doctor availability schedules for appointments</p>
        </div>
        <!-- Success/Error Messages -->
        <?php if(isset($success_message)): ?>
            <div class="max-w-2xl mx-auto mb-6">
                <div class="bg-success/10 border border-success/20 rounded-xl p-4">
                    <div class="flex items-center space-x-3">
                        <i data-lucide="check-circle" class="w-5 h-5 text-success"></i>
                        <span class="text-success font-medium"><?php echo $success_message; ?></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if(isset($error_message)): ?>
            <div class="max-w-2xl mx-auto mb-6">
                <div class="bg-error/10 border border-error/20 rounded-xl p-4">
                    <div class="flex items-center space-x-3">
                        <i data-lucide="alert-circle" class="w-5 h-5 text-error"></i>
                        <span class="text-error font-medium"><?php echo $error_message; ?></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <!-- Doctor Timings Form -->
        <div class="max-w-2xl mx-auto">
            <div class="card-modern">
                <div class="card-body">
                    <form method="post" action="" name="frmdocttimings" onSubmit="return validateform()" class="form-modern">
                        <!-- Doctor Selection (Admin only) -->
                        <?php if(isset($_SESSION['doctorid'])): ?>
                            <input type="hidden" name="select2" value="<?php echo $_SESSION['doctorid']; ?>" />
                        <?php else: ?>
                            <div class="form-group-modern">
                                <label class="form-label-modern">Select Doctor *</label>
                                <select class="form-input-modern" name="select2" id="select2" required>
                                    <option value="">Choose a doctor</option>
                                    <?php
                                    $sqldoctor= "SELECT * FROM doctor WHERE status='Active'";
                                    $qsqldoctor = mysqli_query($con,$sqldoctor);
                                    while($rsdoctor = mysqli_fetch_array($qsqldoctor))
                                    {
                                        if($rsdoctor['doctorid'] == $rsedit['doctorid'])
                                        {
                                            echo "<option value='$rsdoctor[doctorid]' selected>Dr. $rsdoctor[doctorname] (ID: $rsdoctor[doctorid])</option>";
                                        }
                                        else
                                        {
                                            echo "<option value='$rsdoctor[doctorid]'>Dr. $rsdoctor[doctorname] (ID: $rsdoctor[doctorid])</option>";				
                                        }
                                    }
                                    ?>
                                </select>
                                <p class="text-xs text-slate-500 mt-1">Select the doctor for this schedule</p>
                            </div>
                        <?php endif; ?>
                        <!-- Time Settings -->
                        <div class="mb-8">
                            <h3 class="text-h3 text-slate-900 mb-6 flex items-center">
                                <i data-lucide="clock" class="w-5 h-5 mr-2 text-emerald-500"></i>
                                Schedule Settings
                            </h3>
                            <div class="grid-modern grid-cols-1 md:grid-cols-2">
                                <!-- Start Time -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Start Time *</label>
                                    <input class="form-input-modern" type="time" name="ftime" id="ftime" 
                                        value="<?php echo $rsedit['start_time']; ?>" required />
                                    <p class="text-xs text-slate-500 mt-1">When does the doctor start seeing patients?</p>
                                </div>
                                <!-- End Time -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">End Time *</label>
                                    <input class="form-input-modern" type="time" name="ttime" id="ttime" 
                                        value="<?php echo $rsedit['end_time']; ?>" required />
                                    <p class="text-xs text-slate-500 mt-1">When does the doctor stop seeing patients?</p>
                                </div>
                            </div>
                        </div>
                        <!-- Status Settings -->
                        <div class="mb-8">
                            <h3 class="text-h3 text-slate-900 mb-6 flex items-center">
                                <i data-lucide="toggle-left" class="w-5 h-5 mr-2 text-emerald-500"></i>
                                Availability Status
                            </h3>
                            <div class="form-group-modern">
                                <label class="form-label-modern">Status *</label>
                                <select class="form-input-modern" name="select" id="select" required>
                                    <option value="">Select Status</option>
                                    <?php
                                    $arr = array("Active","Inactive");
                                    foreach($arr as $val)
                                    {
                                        if($val == $rsedit['status'])
                                        {
                                            echo "<option value='$val' selected>$val</option>";
                                        }
                                        else
                                        {
                                            echo "<option value='$val'>$val</option>";			  
                                        }
                                    }
                                    ?>
                                </select>
                                <p class="text-xs text-slate-500 mt-1">Active schedules will be available for appointments</p>
                            </div>
                        </div>
                        <!-- Submit Button -->
                        <div class="flex justify-center pt-6">
                            <button type="submit" name="submit" id="submit" class="btn-modern btn-primary px-8 py-3">
                                <i data-lucide="save" class="w-5 h-5"></i>
                                <?php echo isset($_GET['editid']) ? 'Update Schedule' : 'Add Schedule'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Schedule Guidelines Card -->
            <div class="card-modern mt-8">
                <div class="card-body">
                    <h3 class="text-h3 text-slate-900 mb-4 flex items-center">
                        <i data-lucide="info" class="w-5 h-5 mr-2 text-emerald-500"></i>
                        Schedule Guidelines
                    </h3>
                    <div class="grid-modern grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-3 text-sm text-slate-600">
                            <div class="flex items-start space-x-3">
                                <i data-lucide="check-circle" class="w-4 h-4 text-success mt-0.5"></i>
                                <p>Set realistic time slots for patient consultations</p>
                            </div>
                            <div class="flex items-start space-x-3">
                                <i data-lucide="check-circle" class="w-4 h-4 text-success mt-0.5"></i>
                                <p>Consider break times between appointments</p>
                            </div>
                            <div class="flex items-start space-x-3">
                                <i data-lucide="check-circle" class="w-4 h-4 text-success mt-0.5"></i>
                                <p>Active schedules appear in appointment booking</p>
                            </div>
                        </div>
                        <div class="space-y-3 text-sm text-slate-600">
                            <div class="flex items-start space-x-3">
                                <i data-lucide="check-circle" class="w-4 h-4 text-success mt-0.5"></i>
                                <p>Inactive schedules won't accept new appointments</p>
                            </div>
                            <div class="flex items-start space-x-3">
                                <i data-lucide="check-circle" class="w-4 h-4 text-success mt-0.5"></i>
                                <p>Update schedules regularly for holidays</p>
                            </div>
                            <div class="flex items-start space-x-3">
                                <i data-lucide="check-circle" class="w-4 h-4 text-success mt-0.5"></i>
                                <p>Ensure end time is after start time</p>
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
// Modern form validation
function validateform() {
    const form = document.frmdocttimings;
    // Clear previous error states
    document.querySelectorAll('.form-input-modern').forEach(input => {
        input.classList.remove('border-red-500');
    });
    // Doctor selection validation (only for admin)
    <?php if(!isset($_SESSION['doctorid'])): ?>
    if (form.select2.value === "") {
        showError("Doctor Required", "Please select a doctor for this schedule.");
        form.select2.classList.add('border-red-500');
        form.select2.focus();
        return false;
    }
    <?php endif; ?>
    // Start time validation
    if (form.ftime.value === "") {
        showError("Start Time Required", "Please select the start time for appointments.");
        form.ftime.classList.add('border-red-500');
        form.ftime.focus();
        return false;
    }
    // End time validation
    if (form.ttime.value === "") {
        showError("End Time Required", "Please select the end time for appointments.");
        form.ttime.classList.add('border-red-500');
        form.ttime.focus();
        return false;
    }
    // Time logic validation
    if (form.ftime.value && form.ttime.value) {
        const startTime = new Date('1970-01-01T' + form.ftime.value + ':00');
        const endTime = new Date('1970-01-01T' + form.ttime.value + ':00');
        if (endTime <= startTime) {
            showError("Invalid Time Range", "End time must be after start time.");
            form.ttime.classList.add('border-red-500');
            form.ttime.focus();
            return false;
        }
    }
    // Status validation
    if (form.select.value === "") {
        showError("Status Required", "Please select the availability status.");
        form.select.classList.add('border-red-500');
        form.select.focus();
        return false;
    }
    // Show loading state
    const submitBtn = document.getElementById('submit');
    const originalContent = submitBtn.innerHTML;
    submitBtn.innerHTML = '<div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div> Saving Schedule...';
    submitBtn.disabled = true;
    return true;
}
// Modern error display
function showError(title, message) {
    Swal.fire({
        icon: 'error',
        title: title,
        text: message,
        confirmButtonColor: '#10b981',
        background: '#ffffff',
        color: '#1e293b'
    });
}
// Enhanced input validation
document.querySelectorAll('.form-input-modern').forEach(input => {
    input.addEventListener('input', function() {
        this.classList.remove('border-red-500');
        if (this.value.trim()) {
            this.classList.add('border-emerald-500/50');
        } else {
            this.classList.remove('border-emerald-500/50');
        }
    });
});
// Real-time time validation
document.getElementById('ttime').addEventListener('change', function() {
    const startTime = document.getElementById('ftime').value;
    const endTime = this.value;
    if (startTime && endTime) {
        const start = new Date('1970-01-01T' + startTime + ':00');
        const end = new Date('1970-01-01T' + endTime + ':00');
        if (end <= start) {
            this.classList.add('border-red-500');
            showError("Invalid Time Range", "End time must be after start time.");
        } else {
            this.classList.remove('border-red-500');
            this.classList.add('border-emerald-500/50');
        }
    }
});
// Form submission success handling
<?php if(isset($success_message)): ?>
Swal.fire({
    icon: 'success',
    title: 'Schedule Updated!',
    text: '<?php echo $success_message; ?>',
    confirmButtonColor: '#10b981'
});
<?php endif; ?>
<?php if(isset($error_message)): ?>
Swal.fire({
    icon: 'error',
    title: 'Update Failed!',
    text: '<?php echo $error_message; ?>',
    confirmButtonColor: '#10b981'
});
<?php endif; ?>
</script>
</body>
</html>
</script>