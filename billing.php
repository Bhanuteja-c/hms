<?php
session_start();
include("dbconnection.php");
// Check if user is logged in (admin or doctor)
if(!isset($_SESSION['adminid']) && !isset($_SESSION['doctorid']))
{
	echo "<script>window.location='index.php';</script>";
	exit();
}
// Get appointment and patient details
$appointmentid = isset($_GET['appointmentid']) ? $_GET['appointmentid'] : '';
$patientid = isset($_GET['patientid']) ? $_GET['patientid'] : '';
// Handle form submission
if(isset($_POST['submit']))
{
	$date = $_POST['date'];
	$amount = $_POST['amount'];
	$description = $_POST['description'];
	$billtype = "Service Charge";
	
	$sql = "INSERT INTO billing (appointmentid, patientid, date, amount, description, billtype, status) 
			VALUES ('$appointmentid', '$patientid', '$date', '$amount', '$description', '$billtype', 'Pending')";
	
	if(mysqli_query($con, $sql))
	{
		$success_message = "Service charge added successfully!";
	}
	else
	{
		$error_message = "Error adding service charge: " . mysqli_error($con);
	}
}
// Get patient details
if($patientid) {
	$sqlpatient = "SELECT * FROM patient WHERE patientid='$patientid'";
	$qsqlpatient = mysqli_query($con, $sqlpatient);
	$rspatient = mysqli_fetch_array($qsqlpatient);
}
// Get appointment details
if($appointmentid) {
	$sqlappointment = "SELECT * FROM appointment WHERE appointmentid='$appointmentid'";
	$qsqlappointment = mysqli_query($con, $sqlappointment);
	$rsappointment = mysqli_fetch_array($qsqlappointment);
}
?>
<!DOCTYPE html>
<html class="no-js" lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Billing Management - HealSync</title>
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
                    <i data-lucide="credit-card" class="w-6 h-6 text-white"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900">HealSync Billing</h1>
                    <p class="text-xs text-slate-500">Financial Management</p>
                </div>
            </div>
            <!-- Navigation Links -->
            <div class="flex items-center space-x-4">
                <?php if(isset($_SESSION['adminid'])): ?>
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
                <i data-lucide="receipt" class="w-5 h-5 text-emerald-500 mr-2"></i>
                <span class="text-sm font-medium text-slate-700">Billing Management</span>
            </div>
            <h1 class="text-h1 text-slate-900 mb-2">Add Service Charge</h1>
            <p class="text-slate-600">Manage billing and service charges for patient appointments</p>
        </div>
        <!-- Success/Error Messages -->
        <?php if(isset($success_message)): ?>
            <div class="max-w-4xl mx-auto mb-6">
                <div class="bg-success/10 border border-success/20 rounded-xl p-4">
                    <div class="flex items-center space-x-3">
                        <i data-lucide="check-circle" class="w-5 h-5 text-success"></i>
                        <span class="text-success font-medium"><?php echo $success_message; ?></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if(isset($error_message)): ?>
            <div class="max-w-4xl mx-auto mb-6">
                <div class="bg-error/10 border border-error/20 rounded-xl p-4">
                    <div class="flex items-center space-x-3">
                        <i data-lucide="alert-circle" class="w-5 h-5 text-error"></i>
                        <span class="text-error font-medium"><?php echo $error_message; ?></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <!-- Patient & Appointment Info -->
        <?php if($patientid && isset($rspatient)): ?>
        <div class="max-w-4xl mx-auto mb-8">
            <div class="card-modern">
                <div class="card-body">
                    <h3 class="text-h3 text-slate-900 mb-4 flex items-center">
                        <i data-lucide="user" class="w-5 h-5 mr-2 text-emerald-500"></i>
                        Patient Information
                    </h3>
                    <div class="grid-modern grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-slate-500">Patient Name</p>
                            <p class="font-semibold text-slate-900"><?php echo $rspatient['patientname']; ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500">Patient ID</p>
                            <p class="font-semibold text-slate-900">#<?php echo $rspatient['patientid']; ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500">Mobile Number</p>
                            <p class="font-semibold text-slate-900"><?php echo $rspatient['mobileno']; ?></p>
                        </div>
                        <?php if($appointmentid && isset($rsappointment)): ?>
                        <div>
                            <p class="text-sm text-slate-500">Appointment ID</p>
                            <p class="font-semibold text-slate-900">#<?php echo $rsappointment['appointmentid']; ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <!-- Billing Form -->
        <div class="max-w-2xl mx-auto">
            <div class="card-modern">
                <div class="card-body">
                    <form method="post" action="" name="frmbill" onSubmit="return validateform()" class="form-modern">
                        <!-- Billing Information -->
                        <div class="mb-8">
                            <h3 class="text-h3 text-slate-900 mb-6 flex items-center">
                                <i data-lucide="calendar" class="w-5 h-5 mr-2 text-emerald-500"></i>
                                Service Details
                            </h3>
                            <!-- Date -->
                            <div class="form-group-modern">
                                <label class="form-label-modern">Service Date *</label>
                                <input class="form-input-modern" type="date" name="date" id="date" 
                                    min="<?php echo date("Y-m-d"); ?>" 
                                    value="<?php echo date("Y-m-d"); ?>" required />
                                <p class="text-xs text-slate-500 mt-1">Date when the service was provided</p>
                            </div>
                            <!-- Service Description -->
                            <div class="form-group-modern">
                                <label class="form-label-modern">Service Description *</label>
                                <textarea class="form-input-modern" name="description" id="description" rows="3" 
                                    placeholder="Describe the service provided..." required></textarea>
                                <p class="text-xs text-slate-500 mt-1">Brief description of the service or treatment</p>
                            </div>
                            <!-- Amount -->
                            <div class="form-group-modern">
                                <label class="form-label-modern">Service Amount (₹) *</label>
                                <input class="form-input-modern" type="number" name="amount" id="amount" 
                                    placeholder="0.00" min="0" step="0.01" required />
                                <p class="text-xs text-slate-500 mt-1">Enter the service charge amount</p>
                            </div>
                        </div>
                        <!-- Submit Button -->
                        <div class="flex justify-center pt-6">
                            <button type="submit" name="submit" id="submit" class="btn-modern btn-primary px-8 py-3">
                                <i data-lucide="plus" class="w-5 h-5"></i>
                                Add Service Charge
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Existing Billing Records -->
        <?php if($appointmentid): ?>
        <div class="max-w-4xl mx-auto mt-8">
            <div class="card-modern">
                <div class="card-body p-0">
                    <div class="p-6 border-b border-slate-200">
                        <h3 class="text-h3 text-slate-900 flex items-center">
                            <i data-lucide="list" class="w-5 h-5 mr-2 text-emerald-500"></i>
                            Existing Billing Records
                        </h3>
                    </div>
                    <div class="p-6">
                        <?php
                        $sqlbilling = "SELECT * FROM billing WHERE appointmentid='$appointmentid' ORDER BY date DESC";
                        $qsqlbilling = mysqli_query($con, $sqlbilling);
                        if(mysqli_num_rows($qsqlbilling) > 0) {
                            echo "<div class='space-y-4'>";
                            $total = 0;
                            while($rsbilling = mysqli_fetch_array($qsqlbilling)) {
                                $total += $rsbilling['amount'];
                                echo "<div class='flex justify-between items-center p-4 bg-slate-50 rounded-lg'>
                                        <div>
                                            <p class='font-medium text-slate-900'>$rsbilling[description]</p>
                                            <p class='text-sm text-slate-500'>Date: " . date('d M Y', strtotime($rsbilling['date'])) . "</p>
                                        </div>
                                        <div class='text-right'>
                                            <p class='font-semibold text-slate-900'>₹" . number_format($rsbilling['amount'], 2) . "</p>
                                            <span class='inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-warning/10 text-warning'>
                                                $rsbilling[status]
                                            </span>
                                        </div>
                                    </div>";
                            }
                            echo "<div class='border-t border-slate-200 pt-4 mt-4'>
                                    <div class='flex justify-between items-center'>
                                        <p class='text-lg font-semibold text-slate-900'>Total Amount:</p>
                                        <p class='text-xl font-bold text-emerald-600'>₹" . number_format($total, 2) . "</p>
                                    </div>
                                </div>";
                            echo "</div>";
                        } else {
                            echo "<div class='text-center py-8'>
                                    <div class='w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4'>
                                        <i data-lucide='receipt' class='w-8 h-8 text-slate-400'></i>
                                    </div>
                                    <p class='text-slate-500'>No billing records found for this appointment.</p>
                                </div>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <!-- Action Buttons -->
        <div class="max-w-2xl mx-auto mt-8">
            <div class="flex justify-center space-x-4">
                <?php if($patientid): ?>
                <a href="patientreport.php?patientid=<?php echo $patientid; ?>" class="btn-modern btn-secondary">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                    View Patient Report
                </a>
                <?php endif; ?>
                <a href="viewbilling.php" class="btn-modern btn-ghost">
                    <i data-lucide="eye" class="w-5 h-5"></i>
                    View All Billing
                </a>
            </div>
        </div>
    </div>
</main>
<script>
// Initialize Lucide icons
lucide.createIcons();
// Modern form validation
function validateform() {
    const form = document.frmbill;
    const numericExpression = /^[0-9]+(\.[0-9]{1,2})?$/;
    // Clear previous error states
    document.querySelectorAll('.form-input-modern').forEach(input => {
        input.classList.remove('border-red-500');
    });
    // Date validation
    if (form.date.value === "") {
        showError("Date Required", "Please select the service date.");
        form.date.classList.add('border-red-500');
        form.date.focus();
        return false;
    }
    // Description validation
    if (form.description.value.trim() === "") {
        showError("Description Required", "Please provide a service description.");
        form.description.classList.add('border-red-500');
        form.description.focus();
        return false;
    }
    if (form.description.value.trim().length < 5) {
        showError("Description Too Short", "Please provide a more detailed description (at least 5 characters).");
        form.description.classList.add('border-red-500');
        form.description.focus();
        return false;
    }
    // Amount validation
    if (form.amount.value === "") {
        showError("Amount Required", "Please enter the service amount.");
        form.amount.classList.add('border-red-500');
        form.amount.focus();
        return false;
    }
    if (!numericExpression.test(form.amount.value) || parseFloat(form.amount.value) <= 0) {
        showError("Invalid Amount", "Please enter a valid amount greater than 0.");
        form.amount.classList.add('border-red-500');
        form.amount.focus();
        return false;
    }
    // Show loading state
    const submitBtn = document.getElementById('submit');
    const originalContent = submitBtn.innerHTML;
    submitBtn.innerHTML = '<div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div> Adding Charge...';
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
// Form submission success handling
<?php if(isset($success_message)): ?>
Swal.fire({
    icon: 'success',
    title: 'Charge Added!',
    text: '<?php echo $success_message; ?>',
    confirmButtonColor: '#10b981'
});
<?php endif; ?>
<?php if(isset($error_message)): ?>
Swal.fire({
    icon: 'error',
    title: 'Addition Failed!',
    text: '<?php echo $error_message; ?>',
    confirmButtonColor: '#10b981'
});
<?php endif; ?>
</script>
</body>
</html>
</script>