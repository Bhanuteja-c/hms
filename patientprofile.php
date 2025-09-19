<?php
session_start();
include("dbconnection.php");

// Check if patient is logged in
if(!isset($_SESSION['patientid']))
{
	echo "<script>window.location='patientlogin.php';</script>";
	exit();
}

// Handle form submission
if(isset($_POST['submit']))
{
	$sql ="UPDATE patient SET patientname='$_POST[patientname]',admissiondate='$_POST[admissiondate]',admissiontime='$_POST[admissiontme]',address='$_POST[address]',mobileno='$_POST[mobilenumber]',city='$_POST[city]',pincode='$_POST[pincode]',loginid='$_POST[loginid]',bloodgroup='$_POST[select2]',gender='$_POST[select3]',dob='$_POST[dateofbirth]' WHERE patientid='$_SESSION[patientid]'";
	if($qsql = mysqli_query($con,$sql))
	{
		$success_message = "Profile updated successfully!";
	}
	else
	{
		$error_message = "Error updating profile: " . mysqli_error($con);
	}
}

// Get patient details
$sql="SELECT * FROM patient WHERE patientid='$_SESSION[patientid]'";
$qsql = mysqli_query($con,$sql);
$rsedit = mysqli_fetch_array($qsql);
?>



<!DOCTYPE html>
<html class="no-js" lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Patient Profile - HealSync</title>

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
                    <p class="text-xs text-slate-500">Profile Management</p>
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="flex items-center space-x-4">
                <a href="patientaccount.php" class="btn-modern btn-ghost">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Back to Dashboard
                </a>
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
                <i data-lucide="user-cog" class="w-5 h-5 text-primary mr-2"></i>
                <span class="text-sm font-medium text-slate-700">Profile Management</span>
            </div>
            <h1 class="text-h1 text-slate-900 mb-2">Update Your Profile</h1>
            <p class="text-slate-600">Keep your information up to date for better healthcare service</p>
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

        <!-- Profile Form -->
        <div class="max-w-4xl mx-auto">
            <div class="card-modern">
                <div class="card-body">
                    <form method="post" action="" name="frmpatprfl" onSubmit="return validateform()" class="form-modern">
                        
                        <!-- Personal Information Section -->
                        <div class="mb-8">
                            <h3 class="text-h3 text-slate-900 mb-6 flex items-center">
                                <i data-lucide="user" class="w-5 h-5 mr-2 text-primary"></i>
                                Personal Information
                            </h3>
                            
                            <div class="grid-modern grid-cols-1 md:grid-cols-2">
                                <!-- Patient Name -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Patient Name *</label>
                                    <input class="form-input-modern" type="text" name="patientname" id="patientname" 
                                        value="<?php echo $rsedit['patientname']; ?>" 
                                        placeholder="Enter your full name" required />
                                </div>

                                <!-- Mobile Number -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Mobile Number *</label>
                                    <input class="form-input-modern" type="tel" name="mobilenumber" id="mobilenumber" 
                                        value="<?php echo $rsedit['mobileno']; ?>" 
                                        placeholder="Enter mobile number" required />
                                </div>

                                <!-- Address -->
                                <div class="md:col-span-2 form-group-modern">
                                    <label class="form-label-modern">Address *</label>
                                    <textarea class="form-input-modern" name="address" id="address" rows="3" 
                                        placeholder="Enter complete address" required><?php echo $rsedit['address']; ?></textarea>
                                </div>

                                <!-- City -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">City *</label>
                                    <input class="form-input-modern" type="text" name="city" id="city" 
                                        value="<?php echo $rsedit['city']; ?>" 
                                        placeholder="Enter city" required />
                                </div>

                                <!-- PIN Code -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">PIN Code *</label>
                                    <input class="form-input-modern" type="text" name="pincode" id="pincode" 
                                        value="<?php echo $rsedit['pincode']; ?>" 
                                        placeholder="Enter PIN code" required />
                                </div>

                                <!-- Login ID -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Email/Login ID *</label>
                                    <input class="form-input-modern" type="email" name="loginid" id="loginid" 
                                        value="<?php echo $rsedit['loginid']; ?>" 
                                        placeholder="Enter email address" required />
                                </div>

                                <!-- Date of Birth -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Date of Birth *</label>
                                    <input class="form-input-modern" type="date" name="dateofbirth" id="dateofbirth" 
                                        value="<?php echo $rsedit['dob']; ?>" 
                                        max="<?php echo date('Y-m-d'); ?>" required />
                                </div>

                                <!-- Blood Group -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Blood Group *</label>
                                    <select name="select2" id="select2" class="form-input-modern" required>
                                        <option value="">Select Blood Group</option>
                                        <?php
                                        $arr = array("A+","A-","B+","B-","O+","O-","AB+","AB-");
                                        foreach($arr as $val)
                                        {
                                            if($val == $rsedit['bloodgroup'])
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
                                </div>

                                <!-- Gender -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Gender *</label>
                                    <select name="select3" id="select3" class="form-input-modern" required>
                                        <option value="">Select Gender</option>
                                        <?php
                                        $arr = array("MALE","FEMALE");
                                        foreach($arr as $val)
                                        {
                                            if($val == $rsedit['gender'])
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
                                </div>
                            </div>
                        </div>

                        <!-- Registration Information Section -->
                        <div class="mb-8">
                            <h3 class="text-h3 text-slate-900 mb-6 flex items-center">
                                <i data-lucide="calendar" class="w-5 h-5 mr-2 text-primary"></i>
                                Registration Information
                            </h3>
                            
                            <div class="grid-modern grid-cols-1 md:grid-cols-2">
                                <!-- Admission Date -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Admission Date</label>
                                    <input class="form-input-modern bg-slate-100" type="date" name="admissiondate" id="admissiondate" 
                                        value="<?php echo $rsedit['admissiondate']; ?>" readonly />
                                    <p class="text-xs text-slate-500 mt-1">Date when you registered with HealSync</p>
                                </div>

                                <!-- Admission Time -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Admission Time</label>
                                    <input class="form-input-modern bg-slate-100" type="time" name="admissiontme" id="admissiontme" 
                                        value="<?php echo $rsedit['admissiontime']; ?>" readonly />
                                    <p class="text-xs text-slate-500 mt-1">Time when you registered with HealSync</p>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-center pt-6">
                            <button type="submit" name="submit" id="submit" class="btn-modern btn-primary px-8 py-3">
                                <i data-lucide="save" class="w-5 h-5"></i>
                                Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Profile Guidelines Card -->
            <div class="card-modern mt-8">
                <div class="card-body">
                    <h3 class="text-h3 text-slate-900 mb-4 flex items-center">
                        <i data-lucide="info" class="w-5 h-5 mr-2 text-primary"></i>
                        Profile Guidelines
                    </h3>
                    <div class="grid-modern grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-3 text-sm text-slate-600">
                            <div class="flex items-start space-x-3">
                                <i data-lucide="check-circle" class="w-4 h-4 text-success mt-0.5"></i>
                                <p>Keep your contact information updated for appointment notifications</p>
                            </div>
                            <div class="flex items-start space-x-3">
                                <i data-lucide="check-circle" class="w-4 h-4 text-success mt-0.5"></i>
                                <p>Accurate medical information helps doctors provide better care</p>
                            </div>
                            <div class="flex items-start space-x-3">
                                <i data-lucide="check-circle" class="w-4 h-4 text-success mt-0.5"></i>
                                <p>Your email is used for login and important communications</p>
                            </div>
                        </div>
                        <div class="space-y-3 text-sm text-slate-600">
                            <div class="flex items-start space-x-3">
                                <i data-lucide="check-circle" class="w-4 h-4 text-success mt-0.5"></i>
                                <p>Blood group information is crucial for emergency situations</p>
                            </div>
                            <div class="flex items-start space-x-3">
                                <i data-lucide="check-circle" class="w-4 h-4 text-success mt-0.5"></i>
                                <p>Registration date and time cannot be modified</p>
                            </div>
                            <div class="flex items-start space-x-3">
                                <i data-lucide="check-circle" class="w-4 h-4 text-success mt-0.5"></i>
                                <p>All information is kept secure and confidential</p>
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
    const form = document.frmpatprfl;
    const alphaspaceExp = /^[a-zA-Z\s]+$/;
    const numericExpression = /^[0-9]+$/;
    const emailExp = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
    
    // Clear previous error states
    document.querySelectorAll('.form-input-modern').forEach(input => {
        input.classList.remove('border-red-500');
    });
    
    // Patient name validation
    if (form.patientname.value.trim() === "") {
        showError("Patient Name Required", "Please enter your full name.");
        form.patientname.classList.add('border-red-500');
        form.patientname.focus();
        return false;
    }
    
    if (!form.patientname.value.match(alphaspaceExp)) {
        showError("Invalid Patient Name", "Patient name should contain only letters and spaces.");
        form.patientname.classList.add('border-red-500');
        form.patientname.focus();
        return false;
    }
    
    // Address validation
    if (form.address.value.trim() === "") {
        showError("Address Required", "Please enter your complete address.");
        form.address.classList.add('border-red-500');
        form.address.focus();
        return false;
    }
    
    // Mobile number validation
    if (form.mobilenumber.value.trim() === "") {
        showError("Mobile Number Required", "Please enter your mobile number.");
        form.mobilenumber.classList.add('border-red-500');
        form.mobilenumber.focus();
        return false;
    }
    
    if (!form.mobilenumber.value.match(numericExpression) || form.mobilenumber.value.length !== 10) {
        showError("Invalid Mobile Number", "Please enter a valid 10-digit mobile number.");
        form.mobilenumber.classList.add('border-red-500');
        form.mobilenumber.focus();
        return false;
    }
    
    // City validation
    if (form.city.value.trim() === "") {
        showError("City Required", "Please enter your city.");
        form.city.classList.add('border-red-500');
        form.city.focus();
        return false;
    }
    
    if (!form.city.value.match(alphaspaceExp)) {
        showError("Invalid City", "City name should contain only letters and spaces.");
        form.city.classList.add('border-red-500');
        form.city.focus();
        return false;
    }
    
    // PIN code validation
    if (form.pincode.value.trim() === "") {
        showError("PIN Code Required", "Please enter your PIN code.");
        form.pincode.classList.add('border-red-500');
        form.pincode.focus();
        return false;
    }
    
    if (!form.pincode.value.match(numericExpression) || form.pincode.value.length !== 6) {
        showError("Invalid PIN Code", "Please enter a valid 6-digit PIN code.");
        form.pincode.classList.add('border-red-500');
        form.pincode.focus();
        return false;
    }
    
    // Login ID validation
    if (form.loginid.value.trim() === "") {
        showError("Email Required", "Please enter your email address.");
        form.loginid.classList.add('border-red-500');
        form.loginid.focus();
        return false;
    }
    
    if (!form.loginid.value.match(emailExp)) {
        showError("Invalid Email", "Please enter a valid email address.");
        form.loginid.classList.add('border-red-500');
        form.loginid.focus();
        return false;
    }
    
    // Blood group validation
    if (form.select2.value === "") {
        showError("Blood Group Required", "Please select your blood group.");
        form.select2.classList.add('border-red-500');
        form.select2.focus();
        return false;
    }
    
    // Gender validation
    if (form.select3.value === "") {
        showError("Gender Required", "Please select your gender.");
        form.select3.classList.add('border-red-500');
        form.select3.focus();
        return false;
    }
    
    // Date of birth validation
    if (form.dateofbirth.value === "") {
        showError("Date of Birth Required", "Please select your date of birth.");
        form.dateofbirth.classList.add('border-red-500');
        form.dateofbirth.focus();
        return false;
    }
    
    // Show loading state
    const submitBtn = document.getElementById('submit');
    const originalContent = submitBtn.innerHTML;
    submitBtn.innerHTML = '<div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div> Updating Profile...';
    submitBtn.disabled = true;
    
    return true;
}

// Modern error display
function showError(title, message) {
    Swal.fire({
        icon: 'error',
        title: title,
        text: message,
        confirmButtonColor: '#0ea5e9',
        background: '#ffffff',
        color: '#1e293b'
    });
}

// Enhanced input validation
document.querySelectorAll('.form-input-modern').forEach(input => {
    input.addEventListener('input', function() {
        this.classList.remove('border-red-500');
        if (this.value.trim()) {
            this.classList.add('border-primary/50');
        } else {
            this.classList.remove('border-primary/50');
        }
    });
});

// Form submission success handling
<?php if(isset($success_message)): ?>
Swal.fire({
    icon: 'success',
    title: 'Profile Updated!',
    text: '<?php echo $success_message; ?>',
    confirmButtonColor: '#0ea5e9'
});
<?php endif; ?>

<?php if(isset($error_message)): ?>
Swal.fire({
    icon: 'error',
    title: 'Update Failed!',
    text: '<?php echo $error_message; ?>',
    confirmButtonColor: '#0ea5e9'
});
<?php endif; ?>
</script>

</body>
</html>
</script>