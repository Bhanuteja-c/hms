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
	$sql = "UPDATE patient SET password='$_POST[newpassword]' WHERE password='$_POST[oldpassword]' AND patientid='$_SESSION[patientid]'";
	$qsql= mysqli_query($con,$sql);
	if(mysqli_affected_rows($con) == 1)
	{
		$success_message = "Password has been updated successfully!";
	}
	else
	{
		$error_message = "Password update failed. Please check your current password.";
	}
}
?>

<!DOCTYPE html>
<html class="no-js" lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Change Password - HealSync</title>

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
                    <i data-lucide="key" class="w-6 h-6 text-white"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900">HealSync Patient</h1>
                    <p class="text-xs text-slate-500">Security Settings</p>
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
                <i data-lucide="shield" class="w-5 h-5 text-primary mr-2"></i>
                <span class="text-sm font-medium text-slate-700">Security Settings</span>
            </div>
            <h1 class="text-h1 text-slate-900 mb-2">Change Password</h1>
            <p class="text-slate-600">Update your password to keep your account secure</p>
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

        <!-- Password Change Form -->
        <div class="max-w-2xl mx-auto">
            <div class="card-modern">
                <div class="card-body">
                    <form method="post" action="" name="frmpatchange" onSubmit="return validateform()" class="form-modern">
                        
                        <!-- Security Information -->
                        <div class="mb-8">
                            <h3 class="text-h3 text-slate-900 mb-6 flex items-center">
                                <i data-lucide="key" class="w-5 h-5 mr-2 text-primary"></i>
                                Password Security
                            </h3>
                            
                            <!-- Current Password -->
                            <div class="form-group-modern">
                                <label class="form-label-modern">Current Password *</label>
                                <div class="relative">
                                    <input class="form-input-modern pr-12" type="password" name="oldpassword" id="oldpassword" 
                                        placeholder="Enter your current password" required />
                                    <button type="button" id="toggle-old-password" 
                                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                        <i data-lucide="eye" class="w-5 h-5"></i>
                                    </button>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">Enter your current password to verify your identity</p>
                            </div>

                            <!-- New Password -->
                            <div class="form-group-modern">
                                <label class="form-label-modern">New Password *</label>
                                <div class="relative">
                                    <input class="form-input-modern pr-12" type="password" name="newpassword" id="newpassword" 
                                        placeholder="Enter your new password" required />
                                    <button type="button" id="toggle-new-password" 
                                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                        <i data-lucide="eye" class="w-5 h-5"></i>
                                    </button>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">Minimum 8 characters required</p>
                            </div>

                            <!-- Confirm Password -->
                            <div class="form-group-modern">
                                <label class="form-label-modern">Confirm New Password *</label>
                                <div class="relative">
                                    <input class="form-input-modern pr-12" type="password" name="password" id="password" 
                                        placeholder="Confirm your new password" required />
                                    <button type="button" id="toggle-confirm-password" 
                                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                        <i data-lucide="eye" class="w-5 h-5"></i>
                                    </button>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">Re-enter your new password to confirm</p>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-center pt-6">
                            <button type="submit" name="submit" id="submit" class="btn-modern btn-primary px-8 py-3">
                                <i data-lucide="shield-check" class="w-5 h-5"></i>
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Security Guidelines Card -->
            <div class="card-modern mt-8">
                <div class="card-body">
                    <h3 class="text-h3 text-slate-900 mb-4 flex items-center">
                        <i data-lucide="shield-alert" class="w-5 h-5 mr-2 text-warning"></i>
                        Password Security Guidelines
                    </h3>
                    <div class="grid-modern grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-3 text-sm text-slate-600">
                            <div class="flex items-start space-x-3">
                                <i data-lucide="check-circle" class="w-4 h-4 text-success mt-0.5"></i>
                                <p>Use at least 8 characters for better security</p>
                            </div>
                            <div class="flex items-start space-x-3">
                                <i data-lucide="check-circle" class="w-4 h-4 text-success mt-0.5"></i>
                                <p>Include uppercase and lowercase letters</p>
                            </div>
                            <div class="flex items-start space-x-3">
                                <i data-lucide="check-circle" class="w-4 h-4 text-success mt-0.5"></i>
                                <p>Add numbers and special characters</p>
                            </div>
                        </div>
                        <div class="space-y-3 text-sm text-slate-600">
                            <div class="flex items-start space-x-3">
                                <i data-lucide="check-circle" class="w-4 h-4 text-success mt-0.5"></i>
                                <p>Avoid using personal information</p>
                            </div>
                            <div class="flex items-start space-x-3">
                                <i data-lucide="check-circle" class="w-4 h-4 text-success mt-0.5"></i>
                                <p>Don't reuse passwords from other accounts</p>
                            </div>
                            <div class="flex items-start space-x-3">
                                <i data-lucide="check-circle" class="w-4 h-4 text-success mt-0.5"></i>
                                <p>Change your password regularly</p>
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

// Password toggle functionality
document.getElementById('toggle-old-password').addEventListener('click', function() {
    const passwordInput = document.getElementById('oldpassword');
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

document.getElementById('toggle-new-password').addEventListener('click', function() {
    const passwordInput = document.getElementById('newpassword');
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

document.getElementById('toggle-confirm-password').addEventListener('click', function() {
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
    const form = document.frmpatchange;
    
    // Clear previous error states
    document.querySelectorAll('.form-input-modern').forEach(input => {
        input.classList.remove('border-red-500');
    });
    
    // Old password validation
    if (form.oldpassword.value.trim() === "") {
        showError("Current Password Required", "Please enter your current password.");
        form.oldpassword.classList.add('border-red-500');
        form.oldpassword.focus();
        return false;
    }
    
    // New password validation
    if (form.newpassword.value === "") {
        showError("New Password Required", "Please enter a new password.");
        form.newpassword.classList.add('border-red-500');
        form.newpassword.focus();
        return false;
    }
    
    if (form.newpassword.value.length < 8) {
        showError("Password Too Short", "New password must be at least 8 characters long.");
        form.newpassword.classList.add('border-red-500');
        form.newpassword.focus();
        return false;
    }
    
    // Confirm password validation
    if (form.newpassword.value !== form.password.value) {
        showError("Passwords Don't Match", "New password and confirm password must be the same.");
        form.password.classList.add('border-red-500');
        form.password.focus();
        return false;
    }
    
    // Show loading state
    const submitBtn = document.getElementById('submit');
    const originalContent = submitBtn.innerHTML;
    submitBtn.innerHTML = '<div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div> Updating Password...';
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

// Real-time password strength indicator
document.getElementById('newpassword').addEventListener('input', function() {
    const password = this.value;
    const strengthIndicator = document.createElement('div');
    
    let strength = 0;
    if (password.length >= 8) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    // Remove existing indicator
    const existing = this.parentElement.querySelector('.password-strength');
    if (existing) existing.remove();
    
    if (password.length > 0) {
        strengthIndicator.className = 'password-strength text-xs mt-1';
        const colors = ['text-red-500', 'text-orange-500', 'text-yellow-500', 'text-green-500'];
        const labels = ['Weak', 'Fair', 'Good', 'Strong'];
        
        strengthIndicator.className += ' ' + colors[strength - 1];
        strengthIndicator.textContent = 'Password strength: ' + labels[strength - 1];
        
        this.parentElement.appendChild(strengthIndicator);
    }
});

// Form submission success handling
<?php if(isset($success_message)): ?>
Swal.fire({
    icon: 'success',
    title: 'Password Updated!',
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
