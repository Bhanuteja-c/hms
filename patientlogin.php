
<?php
session_start();
error_reporting(0);
include("dbconnection.php");
$dt = date("Y-m-d");
$tim = date("H:i:s");

include("dbconnection.php");
if(isset($_SESSION['patientid']))
{
	echo "<script>window.location='patientaccount.php';</script>";
}
$err='';
if(isset($_POST['submit']))
{
	$sql = "SELECT * FROM patient WHERE loginid='$_POST[loginid]' AND password='$_POST[password]' AND status='Active'";
	$qsql = mysqli_query($con,$sql);
	if(mysqli_num_rows($qsql) >= 1)
	{
		$rslogin = mysqli_fetch_array($qsql);
		$_SESSION['patientid']= $rslogin['patientid'] ;
		echo "<script>window.location='patientaccount.php';</script>";
	}
	else
	{
		$err = "<div class='alert alert-danger'>
		<strong>Oh !</strong> Change a few things up and try submitting again.
	</div>";
	}
}
?>
<!DOCTYPE html>
<html class="no-js" lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>Patient Login - HealSync</title>

<!-- Favicon -->
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
<link rel="icon" href="images/favicon.ico" type="image/x-icon">

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

<script src="sweetalert2.min.js"></script>
<link rel="stylesheet" href="sweetalert2.min.css">
</head>

<body class="bg-gradient-to-br from-slate-50 to-slate-100 min-h-screen">

<!-- Modern Login Container -->
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
  <div class="max-w-md w-full space-y-8">
    
    <!-- Header -->
    <div class="text-center">
      <div class="flex justify-center items-center space-x-3 mb-6">
        <div class="w-12 h-12 bg-primary/10 rounded-2xl flex items-center justify-center">
          <i data-lucide="user-check" class="w-6 h-6 text-primary"></i>
        </div>
        <div>
          <h1 class="text-2xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
            HealSync
          </h1>
        </div>
      </div>
      <h2 class="text-3xl font-bold text-slate-900 mb-2">Patient Login</h2>
      <p class="text-slate-600">Welcome back! Please sign in to your account.</p>
    </div>

    <!-- Error Message -->
    <?php if($err): ?>
    <div class="alert-modern alert-error">
      <i data-lucide="alert-circle" class="w-5 h-5"></i>
      <span>Invalid credentials. Please check your username and password.</span>
    </div>
    <?php endif; ?>

    <!-- Login Form -->
    <div class="card-modern">
      <div class="card-body">
        <form method="post" action="" name="frmpatlogin" id="sign_in" onSubmit="return validateform()" class="form-modern">
          
          <!-- Username Field -->
          <div class="form-group">
            <label for="loginid" class="form-label">
              <i data-lucide="user" class="w-4 h-4 inline mr-2"></i>
              Username
            </label>
            <input 
              type="text" 
              name="loginid" 
              id="loginid" 
              class="form-input focus-ring" 
              placeholder="Enter your username"
              required
            />
          </div>

          <!-- Password Field -->
          <div class="form-group">
            <label for="password" class="form-label">
              <i data-lucide="lock" class="w-4 h-4 inline mr-2"></i>
              Password
            </label>
            <div class="relative">
              <input 
                type="password" 
                name="password" 
                id="password" 
                class="form-input focus-ring pr-12" 
                placeholder="Enter your password"
                required
              />
              <button 
                type="button" 
                id="toggle-password"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-400 hover:text-slate-600"
              >
                <i data-lucide="eye" class="w-5 h-5"></i>
              </button>
            </div>
          </div>

          <!-- Remember Me & Forgot Password -->
          <div class="flex items-center justify-between mb-6">
            <label class="flex items-center space-x-2">
              <input type="checkbox" class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary">
              <span class="text-sm text-slate-600">Remember me</span>
            </label>
            <a href="patientforgotpassword.php" class="text-sm text-primary hover:text-primary-dark transition-colors">
              Forgot password?
            </a>
          </div>

          <!-- Submit Button -->
          <button 
            type="submit" 
            name="submit" 
            id="submit" 
            class="btn-modern btn-primary w-full justify-center py-3 text-base font-semibold"
          >
            <i data-lucide="log-in" class="w-5 h-5"></i>
            Sign In
          </button>

        </form>
      </div>
    </div>

    <!-- Additional Links -->
    <div class="text-center space-y-4">
      <p class="text-slate-600">
        Don't have an account? 
        <a href="patient.php" class="text-primary hover:text-primary-dark font-medium transition-colors">
          Register here
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

// Password toggle functionality
document.getElementById('toggle-password').addEventListener('click', function() {
  const passwordInput = document.getElementById('password');
  const eyeIcon = this.querySelector('[data-lucide]');
  
  if (passwordInput.type === 'password') {
    passwordInput.type = 'text';
    eyeIcon.setAttribute('data-lucide', 'eye-off');
  } else {
    passwordInput.type = 'password';
    eyeIcon.setAttribute('data-lucide', 'eye');
  }
  lucide.createIcons();
});

// Modern form validation
function validateform() {
  const loginid = document.frmpatlogin.loginid.value.trim();
  const password = document.frmpatlogin.password.value;
  
  // Clear previous error states
  document.getElementById('loginid').classList.remove('border-error');
  document.getElementById('password').classList.remove('border-error');
  
  if (loginid === "") {
    Swal.fire({
      icon: 'error',
      title: 'Login ID Required',
      text: 'Please enter your login ID.',
      confirmButtonColor: '#0ea5e9'
    });
    document.getElementById('loginid').classList.add('border-error');
    document.frmpatlogin.loginid.focus();
    return false;
  }
  
  if (password === "") {
    Swal.fire({
      icon: 'error',
      title: 'Password Required',
      text: 'Please enter your password.',
      confirmButtonColor: '#0ea5e9'
    });
    document.getElementById('password').classList.add('border-error');
    document.frmpatlogin.password.focus();
    return false;
  }
  
  if (password.length < 8) {
    Swal.fire({
      icon: 'error',
      title: 'Password Too Short',
      text: 'Password must be at least 8 characters long.',
      confirmButtonColor: '#0ea5e9'
    });
    document.getElementById('password').classList.add('border-error');
    document.frmpatlogin.password.focus();
    return false;
  }
  
  // Show loading state
  const submitBtn = document.getElementById('submit');
  const originalContent = submitBtn.innerHTML;
  submitBtn.innerHTML = '<div class="loading-spinner"></div> Signing In...';
  submitBtn.disabled = true;
  
  return true;
}

// Enhanced input validation
document.querySelectorAll('.form-input').forEach(input => {
  input.addEventListener('input', function() {
    this.classList.remove('border-error');
    if (this.value.trim()) {
      this.classList.add('border-success');
    } else {
      this.classList.remove('border-success');
    }
  });
});

// Auto-focus first input
document.addEventListener('DOMContentLoaded', function() {
  document.getElementById('loginid').focus();
});
</script>

</body>
</html>
	