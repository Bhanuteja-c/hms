<?php
session_start();
error_reporting(0);
include("dbconnection.php");
$dt = date("Y-m-d");
$tim = date("H:i:s");
?>
<!DOCTYPE html>
<html class="no-js" lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - HealSync</title>

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

<body class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 min-h-screen">

<?php
// Always clear admin session when accessing login page directly
// This prevents the bypass issue completely
if(!isset($_POST['submit'])) {
    // Clear admin session when coming to login page
    session_unset();
    $_SESSION = array();
}

// Only redirect if admin is logged in AND this is after a successful login
if(isset($_SESSION['adminid']) && isset($_POST['submit']))
{
	echo "<script>window.location='adminaccount.php';</script>";
}
$err='';
if(isset($_POST["submit"]))

{	
	$sql = "SELECT * FROM admin WHERE loginid='$_POST[loginid]' AND password='$_POST[password]' AND status='Active'";
	$qsql = mysqli_query($con,$sql);
	if(mysqli_num_rows($qsql) == 1)
	{
		$rslogin = mysqli_fetch_array($qsql);
		$_SESSION['adminid']= $rslogin['adminid'] ;
		echo "<script>window.location='adminaccount.php';</script>";
	}
	else
	{
		$err = "<div class='alert alert-danger'>
		<strong>Oh !</strong> Change a few things up and try submitting again.
	</div>";
	}
}
		
?>


<!-- Modern Admin Login Container -->
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
  <div class="max-w-md w-full space-y-8">
    
    <!-- Header -->
    <div class="text-center">
      <div class="flex justify-center items-center space-x-3 mb-6">
        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center">
          <i data-lucide="shield-check" class="w-6 h-6 text-white"></i>
        </div>
        <div>
          <h1 class="text-2xl font-bold bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent">
            HealSync
          </h1>
        </div>
      </div>
      <h2 class="text-3xl font-bold text-white mb-2">Admin Portal</h2>
      <p class="text-slate-300">Secure access to hospital management system</p>
    </div>

    <!-- Error Message -->
    <div id="err">
      <?php if($err): ?>
      <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 mb-6">
        <div class="flex items-center space-x-3">
          <i data-lucide="alert-circle" class="w-5 h-5 text-red-400"></i>
          <span class="text-red-300">Invalid credentials. Please check your username and password.</span>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- Login Form -->
    <div class="bg-white/10 backdrop-blur-lg rounded-2xl border border-white/20 shadow-2xl">
      <div class="p-8">
        <form method="post" action="" name="frmadminlogin" id="sign_in" onSubmit="return validateform()" class="space-y-6">
          
          <!-- Username Field -->
          <div class="space-y-2">
            <label for="loginid" class="block text-sm font-medium text-slate-200">
              <i data-lucide="user" class="w-4 h-4 inline mr-2"></i>
              Admin Username
            </label>
            <input 
              type="text" 
              name="loginid" 
              id="loginid" 
              class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200" 
              placeholder="Enter admin username"
              required
            />
          </div>

          <!-- Password Field -->
          <div class="space-y-2">
            <label for="password" class="block text-sm font-medium text-slate-200">
              <i data-lucide="lock" class="w-4 h-4 inline mr-2"></i>
              Password
            </label>
            <div class="relative">
              <input 
                type="password" 
                name="password" 
                id="password" 
                class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 pr-12" 
                placeholder="Enter admin password"
                required
              />
              <button 
                type="button" 
                id="toggle-password"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-400 hover:text-white transition-colors"
              >
                <i data-lucide="eye" class="w-5 h-5"></i>
              </button>
            </div>
          </div>

          <!-- Security Notice -->
          <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl p-4">
            <div class="flex items-start space-x-3">
              <i data-lucide="shield-alert" class="w-5 h-5 text-amber-400 mt-0.5"></i>
              <div>
                <p class="text-sm text-amber-300 font-medium">Security Notice</p>
                <p class="text-xs text-amber-200/80 mt-1">This is a secure admin area. All activities are logged and monitored.</p>
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <button 
            type="submit" 
            name="submit" 
            id="submit" 
            class="w-full bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200 transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-slate-900 flex items-center justify-center space-x-2"
          >
            <i data-lucide="log-in" class="w-5 h-5"></i>
            <span>Access Admin Panel</span>
          </button>

        </form>
      </div>
    </div>

    <!-- Additional Links -->
    <div class="text-center space-y-4">
      <div class="flex items-center justify-center space-x-4 text-sm text-slate-400">
        <a href="patientlogin.php" class="hover:text-white transition-colors">Patient Login</a>
        <span>•</span>
        <a href="doctorlogin.php" class="hover:text-white transition-colors">Doctor Login</a>
        <span>•</span>
        <a href="index.php" class="hover:text-white transition-colors">Back to Home</a>
      </div>
      
      <p class="text-xs text-slate-500">
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
  const loginid = document.frmadminlogin.loginid.value.trim();
  const password = document.frmadminlogin.password.value;
  const alphanumericExp = /^[0-9a-zA-Z]+$/;
  
  // Clear previous error states
  document.getElementById('loginid').classList.remove('border-red-500');
  document.getElementById('password').classList.remove('border-red-500');
  
  if (loginid === "") {
    showError('Login ID Required', 'Please enter your admin username.');
    document.getElementById('loginid').classList.add('border-red-500');
    document.frmadminlogin.loginid.focus();
    return false;
  }
  
  if (!loginid.match(alphanumericExp)) {
    showError('Invalid Username', 'Username should contain only letters and numbers.');
    document.getElementById('loginid').classList.add('border-red-500');
    document.frmadminlogin.loginid.focus();
    return false;
  }
  
  if (password === "") {
    showError('Password Required', 'Please enter your admin password.');
    document.getElementById('password').classList.add('border-red-500');
    document.frmadminlogin.password.focus();
    return false;
  }
  
  if (password.length < 8) {
    showError('Password Too Short', 'Password must be at least 8 characters long.');
    document.getElementById('password').classList.add('border-red-500');
    document.frmadminlogin.password.focus();
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
    confirmButtonColor: '#8b5cf6',
    background: '#1e293b',
    color: '#f1f5f9'
  });
}

// Enhanced input validation
document.querySelectorAll('input').forEach(input => {
  input.addEventListener('input', function() {
    this.classList.remove('border-red-500');
    if (this.value.trim()) {
      this.classList.add('border-green-500/50');
    } else {
      this.classList.remove('border-green-500/50');
    }
  });
});

// Auto-focus first input
document.addEventListener('DOMContentLoaded', function() {
  document.getElementById('loginid').focus();
});

// Add floating animation to the form
const form = document.querySelector('.bg-white\\/10');
let mouseX = 0;
let mouseY = 0;

document.addEventListener('mousemove', (e) => {
  mouseX = e.clientX;
  mouseY = e.clientY;
});

setInterval(() => {
  const rect = form.getBoundingClientRect();
  const centerX = rect.left + rect.width / 2;
  const centerY = rect.top + rect.height / 2;
  
  const deltaX = (mouseX - centerX) * 0.01;
  const deltaY = (mouseY - centerY) * 0.01;
  
  form.style.transform = `translate(${deltaX}px, ${deltaY}px)`;
}, 100);
</script>

</body>
</html>
</script>