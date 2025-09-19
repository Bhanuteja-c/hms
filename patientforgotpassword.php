<?php
session_start();
include("dbconnection.php");

// Redirect if already logged in
if(isset($_SESSION['patientid']))
{
	echo "<script>window.location='patientaccount.php';</script>";
	exit();
}

// Handle form submission
if(isset($_POST['submit']))
{
	$sql = "SELECT * FROM patient WHERE loginid='$_POST[loginid]' AND status='Active'";
	$qsql = mysqli_query($con,$sql);
	if(mysqli_num_rows($qsql) >= 1)
	{
		$rslogin = mysqli_fetch_array($qsql);
		$msg = "Kindly enter Login ID: $rslogin[loginid] and Password is : $rslogin[password] to login HealSync..";
		
		// SMS Gateway (keeping original functionality)
		?>
<iframe style="visibility:hidden" src="http://login.smsgatewayhub.com/api/mt/SendSMS?APIKey=qyQgcDu3EEiw1VfItgv1tA&senderid=WEBSMS&channel=1&DCS=0&flashsms=0&number=<?php echo $rslogin['mobileno']; ?>&text=<?php echo $msg; ?>&route=1"></iframe>	
<?php	
		$success_message = "Login details sent to your registered mobile number.";
		$redirect_to_login = true;
	}
	else
	{
		$error_message = "Invalid login ID entered. Please check and try again.";
	}
}
?>
<!DOCTYPE html>
<html class="no-js" lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password - HealSync</title>

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

<body class="bg-gradient-to-br from-slate-50 to-slate-100 min-h-screen">

<!-- Modern Forgot Password Container -->
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
  <div class="max-w-md w-full space-y-8">
    
    <!-- Header -->
    <div class="text-center">
      <div class="flex justify-center items-center space-x-3 mb-6">
        <div class="w-12 h-12 bg-primary/10 rounded-2xl flex items-center justify-center">
          <i data-lucide="key" class="w-6 h-6 text-primary"></i>
        </div>
        <div>
          <h1 class="text-2xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
            HealSync
          </h1>
        </div>
      </div>
      <h2 class="text-3xl font-bold text-slate-900 mb-2">Forgot Password?</h2>
      <p class="text-slate-600">Enter your login ID to recover your password</p>
    </div>

    <!-- Success/Error Messages -->
    <?php if(isset($success_message)): ?>
    <div class="bg-success/10 border border-success/20 rounded-xl p-4">
      <div class="flex items-center space-x-3">
        <i data-lucide="check-circle" class="w-5 h-5 text-success"></i>
        <span class="text-success font-medium"><?php echo $success_message; ?></span>
      </div>
    </div>
    <?php endif; ?>

    <?php if(isset($error_message)): ?>
    <div class="bg-error/10 border border-error/20 rounded-xl p-4">
      <div class="flex items-center space-x-3">
        <i data-lucide="alert-circle" class="w-5 h-5 text-error"></i>
        <span class="text-error font-medium"><?php echo $error_message; ?></span>
      </div>
    </div>
    <?php endif; ?>

    <!-- Recovery Form -->
    <div class="card-modern">
      <div class="card-body">
        <form method="post" action="" name="frmpatlogin" onSubmit="return validateform()" class="form-modern">
          
          <!-- Login ID Field -->
          <div class="form-group-modern">
            <label for="loginid" class="form-label-modern">
              <i data-lucide="mail" class="w-4 h-4 inline mr-2"></i>
              Email/Login ID
            </label>
            <input 
              type="email" 
              name="loginid" 
              id="loginid" 
              class="form-input-modern" 
              placeholder="Enter your registered email address"
              required
            />
            <p class="text-xs text-slate-500 mt-1">We'll send your login details to your registered mobile number</p>
          </div>

          <!-- Submit Button -->
          <button 
            type="submit" 
            name="submit" 
            id="submit" 
            class="btn-modern btn-primary w-full justify-center py-3 text-base font-semibold"
          >
            <i data-lucide="send" class="w-5 h-5"></i>
            Send Recovery Details
          </button>

        </form>
      </div>
    </div>

    <!-- Additional Information -->
    <div class="card-modern">
      <div class="card-body">
        <h3 class="text-h3 text-slate-900 mb-4 flex items-center">
          <i data-lucide="info" class="w-5 h-5 mr-2 text-primary"></i>
          How Password Recovery Works
        </h3>
        <div class="space-y-3 text-sm text-slate-600">
          <div class="flex items-start space-x-3">
            <i data-lucide="check-circle" class="w-4 h-4 text-success mt-0.5"></i>
            <p>Enter your registered email address</p>
          </div>
          <div class="flex items-start space-x-3">
            <i data-lucide="check-circle" class="w-4 h-4 text-success mt-0.5"></i>
            <p>We'll send your login details to your registered mobile number</p>
          </div>
          <div class="flex items-start space-x-3">
            <i data-lucide="check-circle" class="w-4 h-4 text-success mt-0.5"></i>
            <p>Use the received credentials to log in</p>
          </div>
          <div class="flex items-start space-x-3">
            <i data-lucide="check-circle" class="w-4 h-4 text-success mt-0.5"></i>
            <p>Change your password after logging in for security</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Additional Links -->
    <div class="text-center space-y-4">
      <p class="text-slate-600">
        Remember your password? 
        <a href="patientlogin.php" class="text-primary hover:text-primary-dark font-medium transition-colors">
          Sign in here
        </a>
      </p>
      
      <div class="flex items-center justify-center space-x-4 text-sm text-slate-500">
        <a href="adminlogin.php" class="hover:text-primary transition-colors">Admin Login</a>
        <span>•</span>
        <a href="doctorlogin.php" class="hover:text-primary transition-colors">Doctor Login</a>
        <span>•</span>
        <a href="index.php" class="hover:text-primary transition-colors">Back to Home</a>
      </div>
    </div>

  </div>
</div>

<script>
// Initialize Lucide icons
lucide.createIcons();

// Modern form validation
function validateform() {
  const loginid = document.frmpatlogin.loginid.value.trim();
  const emailExp = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
  
  // Clear previous error states
  document.getElementById('loginid').classList.remove('border-red-500');
  
  if (loginid === "") {
    Swal.fire({
      icon: 'error',
      title: 'Email Required',
      text: 'Please enter your registered email address.',
      confirmButtonColor: '#0ea5e9'
    });
    document.getElementById('loginid').classList.add('border-red-500');
    document.frmpatlogin.loginid.focus();
    return false;
  }
  
  if (!loginid.match(emailExp)) {
    Swal.fire({
      icon: 'error',
      title: 'Invalid Email',
      text: 'Please enter a valid email address.',
      confirmButtonColor: '#0ea5e9'
    });
    document.getElementById('loginid').classList.add('border-red-500');
    document.frmpatlogin.loginid.focus();
    return false;
  }
  
  // Show loading state
  const submitBtn = document.getElementById('submit');
  const originalContent = submitBtn.innerHTML;
  submitBtn.innerHTML = '<div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div> Sending...';
  submitBtn.disabled = true;
  
  return true;
}

// Enhanced input validation
document.getElementById('loginid').addEventListener('input', function() {
  this.classList.remove('border-red-500');
  if (this.value.trim()) {
    this.classList.add('border-primary/50');
  } else {
    this.classList.remove('border-primary/50');
  }
});

// Auto-focus input
document.addEventListener('DOMContentLoaded', function() {
  document.getElementById('loginid').focus();
});

// Handle success/error messages
<?php if(isset($success_message)): ?>
Swal.fire({
  icon: 'success',
  title: 'Recovery Details Sent!',
  text: '<?php echo $success_message; ?>',
  confirmButtonColor: '#0ea5e9'
}).then((result) => {
  if (result.isConfirmed) {
    window.location = 'patientlogin.php';
  }
});
<?php endif; ?>

<?php if(isset($error_message)): ?>
Swal.fire({
  icon: 'error',
  title: 'Recovery Failed!',
  text: '<?php echo $error_message; ?>',
  confirmButtonColor: '#0ea5e9'
});
<?php endif; ?>
</script>

</body>
</html>