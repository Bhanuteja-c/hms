<?php
session_start();
include("dbconnection.php");
// Check if user is logged in (admin, doctor, or patient)
if(!isset($_SESSION['adminid']) && !isset($_SESSION['doctorid']) && !isset($_SESSION['patientid']))
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
	$paiddate = $_POST['date'];
	$paidtime = $_POST['time'];
	$paidamount = $_POST['paidamount'];
	$paymentmethod = $_POST['paymentmethod'];
	$transactionid = $_POST['transactionid'];
	
	$sql ="INSERT INTO payment(patientid,appointmentid,paiddate,paidtime,paidamount,paymentmethod,transactionid,status) 
		   values('$patientid','$appointmentid','$paiddate','$paidtime','$paidamount','$paymentmethod','$transactionid','Completed')";
	
	if($qsql = mysqli_query($con,$sql))
	{
		$success_message = "Payment recorded successfully!";
		// Update billing status if needed
		$updatebilling = "UPDATE billing SET status='Paid' WHERE appointmentid='$appointmentid'";
		mysqli_query($con, $updatebilling);
	}
	else
	{
		$error_message = "Error recording payment: " . mysqli_error($con);
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
// Get total billing amount
$totalbill = 0;
if($appointmentid) {
	$sqlbilling = "SELECT SUM(amount) as total FROM billing WHERE appointmentid='$appointmentid'";
	$qsqlbilling = mysqli_query($con, $sqlbilling);
	$rsbilling = mysqli_fetch_array($qsqlbilling);
	$totalbill = $rsbilling['total'] ? $rsbilling['total'] : 0;
}
// Get total paid amount
$totalpaid = 0;
if($appointmentid) {
	$sqlpaid = "SELECT SUM(paidamount) as total FROM payment WHERE appointmentid='$appointmentid'";
	$qsqlpaid = mysqli_query($con, $sqlpaid);
	$rspaid = mysqli_fetch_array($qsqlpaid);
	$totalpaid = $rspaid['total'] ? $rspaid['total'] : 0;
}
$balance = $totalbill - $totalpaid;
?>

<!DOCTYPE html>
<html class="no-js" lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment Processing - HealSync</title>
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
                    <h1 class="text-xl font-bold text-slate-900">HealSync Payment</h1>
                    <p class="text-xs text-slate-500">Payment Processing</p>
                </div>
            </div>
            <!-- Navigation Links -->
            <div class="flex items-center space-x-4">
                <?php if(isset($_SESSION['adminid'])): ?>
                    <a href="admin.php" class="btn-modern btn-ghost">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        Back to Dashboard
                    </a>
                <?php elseif(isset($_SESSION['doctorid'])): ?>
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
                <i data-lucide="wallet" class="w-5 h-5 text-emerald-500 mr-2"></i>
                <span class="text-sm font-medium text-slate-700">Payment Processing</span>
            </div>
            <h1 class="text-h1 text-slate-900 mb-2">Process Payment</h1>
            <p class="text-slate-600">Record and manage patient payments securely</p>
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
        <!-- Patient & Billing Summary -->
        <?php if($patientid && isset($rspatient)): ?>
        <div class="max-w-4xl mx-auto mb-8">
            <div class="grid-modern grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Patient Information -->
                <div class="card-modern">
                    <div class="card-body">
                        <h3 class="text-h3 text-slate-900 mb-4 flex items-center">
                            <i data-lucide="user" class="w-5 h-5 mr-2 text-emerald-500"></i>
                            Patient Information
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-slate-600">Name:</span>
                                <span class="font-semibold text-slate-900"><?php echo $rspatient['patientname']; ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600">Patient ID:</span>
                                <span class="font-semibold text-slate-900">#<?php echo $rspatient['patientid']; ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600">Mobile:</span>
                                <span class="font-semibold text-slate-900"><?php echo $rspatient['mobileno']; ?></span>
                            </div>
                            <?php if($appointmentid && isset($rsappointment)): ?>
                            <div class="flex justify-between">
                                <span class="text-slate-600">Appointment ID:</span>
                                <span class="font-semibold text-slate-900">#<?php echo $rsappointment['appointmentid']; ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- Billing Summary -->
                <div class="card-modern">
                    <div class="card-body">
                        <h3 class="text-h3 text-slate-900 mb-4 flex items-center">
                            <i data-lucide="receipt" class="w-5 h-5 mr-2 text-emerald-500"></i>
                            Billing Summary
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-slate-600">Total Bill Amount:</span>
                                <span class="font-semibold text-slate-900">₹<?php echo number_format($totalbill, 2); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600">Total Paid:</span>
                                <span class="font-semibold text-success">₹<?php echo number_format($totalpaid, 2); ?></span>
                            </div>
                            <div class="border-t border-slate-200 pt-3">
                                <div class="flex justify-between">
                                    <span class="text-slate-900 font-medium">Outstanding Balance:</span>
                                    <span class="font-bold text-lg <?php echo $balance > 0 ? 'text-warning' : 'text-success'; ?>">
                                        ₹<?php echo number_format($balance, 2); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <!-- Payment Form -->
        <div class="max-w-2xl mx-auto">
            <div class="card-modern">
                <div class="card-body">
                    <form method="post" action="" name="frmpatprfl" onSubmit="return validateform()" class="form-modern">
                        <!-- Payment Details -->
                        <div class="mb-8">
                            <h3 class="text-h3 text-slate-900 mb-6 flex items-center">
                                <i data-lucide="credit-card" class="w-5 h-5 mr-2 text-emerald-500"></i>
                                Payment Details
                            </h3>
                            <div class="grid-modern grid-cols-1 md:grid-cols-2">
                                <!-- Payment Date -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Payment Date *</label>
                                    <input class="form-input-modern" type="date" name="date" id="date" 
                                        value="<?php echo date("Y-m-d"); ?>" required />
                                </div>
                                <!-- Payment Time -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Payment Time *</label>
                                    <input class="form-input-modern" type="time" name="time" id="time" 
                                        value="<?php echo date("H:i"); ?>" required />
                                </div>
                            </div>
                            <!-- Payment Amount -->
                            <div class="form-group-modern">
                                <label class="form-label-modern">Payment Amount (₹) *</label>
                                <input class="form-input-modern" type="number" name="paidamount" id="paidamount" 
                                    placeholder="0.00" min="0" step="0.01" 
                                    max="<?php echo $balance; ?>" value="<?php echo $balance; ?>" required />
                                <p class="text-xs text-slate-500 mt-1">Maximum payable amount: ₹<?php echo number_format($balance, 2); ?></p>
                            </div>
                            <!-- Payment Method -->
                            <div class="form-group-modern">
                                <label class="form-label-modern">Payment Method *</label>
                                <select class="form-input-modern" name="paymentmethod" id="paymentmethod" required>
                                    <option value="">Select Payment Method</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Credit Card">Credit Card</option>
                                    <option value="Debit Card">Debit Card</option>
                                    <option value="UPI">UPI</option>
                                    <option value="Net Banking">Net Banking</option>
                                    <option value="Cheque">Cheque</option>
                                </select>
                            </div>
                            <!-- Transaction ID -->
                            <div class="form-group-modern">
                                <label class="form-label-modern">Transaction ID / Reference Number</label>
                                <input class="form-input-modern" type="text" name="transactionid" id="transactionid" 
                                    placeholder="Enter transaction reference (optional)" />
                                <p class="text-xs text-slate-500 mt-1">For digital payments, enter the transaction ID</p>
                            </div>
                        </div>
                        <!-- Submit Button -->
                        <div class="flex justify-center pt-6">
                            <button type="submit" name="submit" id="submit" class="btn-modern btn-primary px-8 py-3">
                                <i data-lucide="check-circle" class="w-5 h-5"></i>
                                Process Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Payment History -->
        <?php if($appointmentid): ?>
        <div class="max-w-4xl mx-auto mt-8">
            <div class="card-modern">
                <div class="card-body p-0">
                    <div class="p-6 border-b border-slate-200">
                        <h3 class="text-h3 text-slate-900 flex items-center">
                            <i data-lucide="history" class="w-5 h-5 mr-2 text-emerald-500"></i>
                            Payment History
                        </h3>
                    </div>
                    <div class="p-6">
                        <?php
                        $sqlpayments = "SELECT * FROM payment WHERE appointmentid='$appointmentid' ORDER BY paiddate DESC, paidtime DESC";
                        $qsqlpayments = mysqli_query($con, $sqlpayments);
                        if(mysqli_num_rows($qsqlpayments) > 0) {
                            echo "<div class='space-y-4'>";
                            while($rspayment = mysqli_fetch_array($qsqlpayments)) {
                                echo "<div class='flex justify-between items-center p-4 bg-slate-50 rounded-lg'>
                                        <div>
                                            <p class='font-medium text-slate-900'>Payment #$rspayment[paymentid]</p>
                                            <p class='text-sm text-slate-500'>
                                                " . date('d M Y', strtotime($rspayment['paiddate'])) . " at " . date('h:i A', strtotime($rspayment['paidtime'])) . "
                                            </p>
                                            <p class='text-sm text-slate-600'>Method: $rspayment[paymentmethod]</p>";
                                if($rspayment['transactionid']) {
                                    echo "<p class='text-xs text-slate-500'>Ref: $rspayment[transactionid]</p>";
                                }
                                echo "</div>
                                        <div class='text-right'>
                                            <p class='font-semibold text-slate-900'>₹" . number_format($rspayment['paidamount'], 2) . "</p>
                                            <span class='inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-success/10 text-success'>
                                                $rspayment[status]
                                            </span>
                                        </div>
                                    </div>";
                            }
                            echo "</div>";
                        } else {
                            echo "<div class='text-center py-8'>
                                    <div class='w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4'>
                                        <i data-lucide='credit-card' class='w-8 h-8 text-slate-400'></i>
                                    </div>
                                    <p class='text-slate-500'>No payment records found for this appointment.</p>
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
                <a href="viewpaymentreport.php" class="btn-modern btn-ghost">
                    <i data-lucide="eye" class="w-5 h-5"></i>
                    View All Payments
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
    const form = document.frmpatprfl;
    const numericExpression = /^[0-9]+(\.[0-9]{1,2})?$/;
    // Clear previous error states
    document.querySelectorAll('.form-input-modern').forEach(input => {
        input.classList.remove('border-red-500');
    });
    // Date validation
    if (form.date.value === "") {
        showError("Date Required", "Please select the payment date.");
        form.date.classList.add('border-red-500');
        form.date.focus();
        return false;
    }
    // Time validation
    if (form.time.value === "") {
        showError("Time Required", "Please select the payment time.");
        form.time.classList.add('border-red-500');
        form.time.focus();
        return false;
    }
    // Amount validation
    if (form.paidamount.value === "" || form.paidamount.value === "0") {
        showError("Amount Required", "Please enter the payment amount.");
        form.paidamount.classList.add('border-red-500');
        form.paidamount.focus();
        return false;
    }
    if (!numericExpression.test(form.paidamount.value) || parseFloat(form.paidamount.value) <= 0) {
        showError("Invalid Amount", "Please enter a valid payment amount greater than 0.");
        form.paidamount.classList.add('border-red-500');
        form.paidamount.focus();
        return false;
    }
    const maxAmount = <?php echo $balance; ?>;
    if (parseFloat(form.paidamount.value) > maxAmount) {
        showError("Amount Exceeds Balance", "Payment amount cannot exceed the outstanding balance of ₹" + maxAmount.toFixed(2));
        form.paidamount.classList.add('border-red-500');
        form.paidamount.focus();
        return false;
    }
    // Payment method validation
    if (form.paymentmethod.value === "") {
        showError("Payment Method Required", "Please select a payment method.");
        form.paymentmethod.classList.add('border-red-500');
        form.paymentmethod.focus();
        return false;
    }
    // Show loading state
    const submitBtn = document.getElementById('submit');
    const originalContent = submitBtn.innerHTML;
    submitBtn.innerHTML = '<div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div> Processing Payment...';
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
// Payment method change handler
document.getElementById('paymentmethod').addEventListener('change', function() {
    const transactionField = document.getElementById('transactionid');
    const transactionLabel = transactionField.previousElementSibling;
    
    if (this.value === 'Cash' || this.value === 'Cheque') {
        transactionField.required = false;
        transactionLabel.textContent = 'Reference Number (Optional)';
    } else {
        transactionField.required = true;
        transactionLabel.textContent = 'Transaction ID / Reference Number *';
    }
});
// Form submission success handling
<?php if(isset($success_message)): ?>
Swal.fire({
    icon: 'success',
    title: 'Payment Processed!',
    text: '<?php echo $success_message; ?>',
    confirmButtonColor: '#10b981'
});
<?php endif; ?>
<?php if(isset($error_message)): ?>
Swal.fire({
    icon: 'error',
    title: 'Payment Failed!',
    text: '<?php echo $error_message; ?>',
    confirmButtonColor: '#10b981'
});
<?php endif; ?>
</script>
</body>
</html>
