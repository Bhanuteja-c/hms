<?php
session_start();
error_reporting(0);
include("dbconnection.php");
$dt = date("Y-m-d");
$tim = date("H:i:s");

include("dbconnection.php");
if(isset($_SESSION['doctorid']))
{
	echo "<script>window.location='doctoraccount.php';</script>";
}
$err='';
if(isset($_POST['submit']))
{
	$sql = "SELECT * FROM doctor WHERE loginid='$_POST[loginid]' AND password='$_POST[password]' AND status='Active'";
	$qsql = mysqli_query($con,$sql);
	if(mysqli_num_rows($qsql) == 1)
	{
		$rslogin = mysqli_fetch_array($qsql);
		$_SESSION['doctorid']= $rslogin['doctorid'] ;
		echo "<script>window.location='doctoraccount.php';</script>";
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
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Doctor Login - HealSync</title>

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

<body class="bg-gradient-to-br from-emerald-50 via-cyan-50 to-blue-50 min-h-screen">
<!-- Modern Doctor Login Container -->
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
  <div class="max-w-md w-full space-y-8">
    
    <!-- Header -->
    <div class="text-center">
      <div class="flex justify-center items-center space-x-3 mb-6">
        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-cyan-500 rounded-2xl flex items-center justify-center">
          <i data-lucide="stethoscope" class="w-6 h-6 text-white"></i>
        </div>
        <div>
          <h1 class="text-2xl font-bold bg-gradient-to-r from-emerald-600 to-cyan-600 bg-clip-text text-transparent">
            HealSync
          </h1>
        </div>
      </div>
      <h2 class="text-3xl font-bold text-slate-900 mb-2">Doctor Portal</h2>
      <p class="text-slate-600">Welcome back, Doctor! Please sign in to your account.</p>
    </div>

    <!-- Error Message -->
    <div id="err">
      <?php if($err): ?>
      <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 mb-6">
        <div class="flex items-center space-x-3">
          <i data-lucide="alert-circle" class="w-5 h-5 text-red-500"></i>
          <span class="text-red-700">Invalid credentials. Please check your username and password.</span>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- Login Form -->
    <div class="bg-white rounded-2xl shadow-xl border border-slate-200">
      <div class="p-8">
        <form method="post" action="" name="frmdoctlogin" id="sign_in" onSubmit="return validateform()" class="space-y-6">
          
          <!-- Username Field -->
          <div class="space-y-2">
            <label for="loginid" class="block text-sm font-medium text-slate-700">
              <i data-lucide="user" class="w-4 h-4 inline mr-2 text-emerald-500"></i>
              Doctor Username
            </label>
            <input 
              type="text" 
              name="loginid" 
              id="loginid" 
              class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200" 
              placeholder="Enter your username"
              required
            />
          </div>

          <!-- Password Field -->
          <div class="space-y-2">
            <label for="password" class="block text-sm font-medium text-slate-700">
              <i data-lucide="lock" class="w-4 h-4 inline mr-2 text-emerald-500"></i>
              Password
            </label>
            <div class="relative">
              <input 
                type="password" 
                name="password" 
                id="password" 
                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200 pr-12" 
                placeholder="Enter your password"
                required
              />
              <button 
                type="button" 
                id="toggle-password"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors"
              >
                <i data-lucide="eye" class="w-5 h-5"></i>
              </button>
            </div>
          </div>

          <!-- Doctor Info Notice -->
          <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
            <div class="flex items-start space-x-3">
              <i data-lucide="info" class="w-5 h-5 text-emerald-600 mt-0.5"></i>
              <div>
                <p class="text-sm text-emerald-800 font-medium">Doctor Portal Access</p>
                <p class="text-xs text-emerald-700 mt-1">Access patient records, appointments, and medical management tools.</p>
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <button 
            type="submit" 
            name="submit" 
            id="submit" 
            class="w-full bg-gradient-to-r from-emerald-500 to-cyan-500 hover:from-emerald-600 hover:to-cyan-600 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200 transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 flex items-center justify-center space-x-2"
          >
            <i data-lucide="log-in" class="w-5 h-5"></i>
            <span>Access Doctor Portal</span>
          </button>

        </form>
      </div>
    </div>

    <!-- Additional Links -->
    <div class="text-center space-y-4">
      <div class="flex items-center justify-center space-x-4 text-sm text-slate-500">
        <a href="patientlogin.php" class="hover:text-emerald-600 transition-colors">Patient Login</a>
        <span>•</span>
        <a href="adminlogin.php" class="hover:text-emerald-600 transition-colors">Admin Login</a>
        <span>•</span>
        <a href="index.php" class="hover:text-emerald-600 transition-colors">Back to Home</a>
      </div>
      
      <p class="text-xs text-slate-400">
        © 2025 HealSync. All rights reserved.
      </p>
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
  const loginid = document.frmdoctlogin.loginid.value.trim();
  const password = document.frmdoctlogin.password.value;
  const alphanumericExp = /^[0-9a-zA-Z]+$/;
  
  // Clear previous error states
  document.getElementById('loginid').classList.remove('border-red-500');
  document.getElementById('password').classList.remove('border-red-500');
  
  if (loginid === "") {
    showError('Login ID Required', 'Please enter your doctor username.');
    document.getElementById('loginid').classList.add('border-red-500');
    document.frmdoctlogin.loginid.focus();
    return false;
  }
  
  if (!loginid.match(alphanumericExp)) {
    showError('Invalid Username', 'Username should contain only letters and numbers.');
    document.getElementById('loginid').classList.add('border-red-500');
    document.frmdoctlogin.loginid.focus();
    return false;
  }
  
  if (password === "") {
    showError('Password Required', 'Please enter your password.');
    document.getElementById('password').classList.add('border-red-500');
    document.frmdoctlogin.password.focus();
    return false;
  }
  
  if (password.length < 8) {
    showError('Password Too Short', 'Password must be at least 8 characters long.');
    document.getElementById('password').classList.add('border-red-500');
    document.frmdoctlogin.password.focus();
    return false;
  }
  
  // Show loading state
  const submitBtn = document.getElementById('submit');
  const originalContent = submitBtn.innerHTML;
  submitBtn.innerHTML = '<div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div> Authenticating...';
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
document.querySelectorAll('input').forEach(input => {
  input.addEventListener('input', function() {
    this.classList.remove('border-red-500');
    if (this.value.trim()) {
      this.classList.add('border-emerald-500/50');
    } else {
      this.classList.remove('border-emerald-500/50');
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
</script>