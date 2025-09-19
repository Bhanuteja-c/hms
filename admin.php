<?php
session_start();
include("dbconnection.php");

// Check if admin is logged in
if(!isset($_SESSION['adminid'])){
    echo "<script>window.location='adminlogin.php';</script>";
    exit();
}
if(isset($_POST['submit']))
{
	if(isset($_GET['editid']))
	{
		$sql ="UPDATE admin SET adminname='$_POST[adminname]',loginid='$_POST[loginid]',password='$_POST[password]',status='$_POST[select]' WHERE adminid='$_GET[editid]'";
		if($qsql = mysqli_query($con,$sql))
		{
			echo "<div class='alert alert-success'>
			Admin Record updated successfully
			</div>";
		}
		else
		{
			echo mysqli_error($con);
		}	
	}
	else
	{
		$sql ="INSERT INTO admin(adminname,loginid,password,status) values('$_POST[adminname]','$_POST[loginid]','$_POST[password]','$_POST[select]')";
		if($qsql = mysqli_query($con,$sql))
		{
			echo "<div class='alert alert-success'>
			Admin Record Inserted successfully
			</div>";
		}
		else
		{
			echo mysqli_error($con);
		}
	}
}
if(isset($_GET['editid']))
{
	$sql="SELECT * FROM admin WHERE adminid='$_GET[editid]' ";
	$qsql = mysqli_query($con,$sql);
	$rsedit = mysqli_fetch_array($qsql);
	
}
?>

<!DOCTYPE html>
<html class="no-js" lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Management - HealSync</title>

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

<!-- Modern Admin Navigation -->
<nav class="bg-white shadow-sm border-b border-slate-200 sticky top-0 z-50">
    <div class="container-modern">
        <div class="flex items-center justify-between py-4">
            <!-- Brand -->
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                    <i data-lucide="shield-check" class="w-6 h-6 text-white"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900">HealSync Admin</h1>
                    <p class="text-xs text-slate-500">Administrator Management</p>
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="flex items-center space-x-4">
                <a href="adminaccount.php" class="btn-modern btn-ghost">
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
                <i data-lucide="shield-plus" class="w-5 h-5 text-purple-500 mr-2"></i>
                <span class="text-sm font-medium text-slate-700">Administrator Management</span>
            </div>
            <h1 class="text-h1 text-slate-900 mb-2">
                <?php echo isset($_GET['editid']) ? 'Edit Administrator' : 'Add New Administrator'; ?>
            </h1>
            <p class="text-slate-600">
                <?php echo isset($_GET['editid']) ? 'Update administrator credentials and permissions' : 'Create a new administrator account for system management'; ?>
            </p>
        </div>

        <!-- Success/Error Messages -->
        <?php if(isset($_POST['submit'])): ?>
            <div class="max-w-2xl mx-auto mb-6">
                <?php if(!mysqli_error($con)): ?>
                    <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                        <div class="flex items-center space-x-3">
                            <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                            <span class="text-green-800 font-medium">
                                <?php echo isset($_GET['editid']) ? 'Administrator updated successfully!' : 'Administrator created successfully!'; ?>
                            </span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                        <div class="flex items-center space-x-3">
                            <i data-lucide="alert-circle" class="w-5 h-5 text-red-600"></i>
                            <span class="text-red-800 font-medium">Error occurred. Please try again.</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Admin Form -->
        <div class="max-w-2xl mx-auto">
            <div class="card-modern">
                <div class="card-body">
                    <form method="post" action="" name="frmadminprofile" onSubmit="return validateform()" class="form-modern">
                        
                        <!-- Administrator Information Section -->
                        <div class="mb-8">
                            <h3 class="text-h3 text-slate-900 mb-6 flex items-center">
                                <i data-lucide="user" class="w-5 h-5 mr-2 text-purple-500"></i>
                                Administrator Information
                            </h3>
                            
                            <!-- Admin Name -->
                            <div class="form-group-modern">
                                <label class="form-label-modern">Administrator Name *</label>
                                <input type="text" class="form-input-modern" name="adminname" id="adminname" 
                                    value="<?php echo $rsedit['adminname']; ?>" 
                                    placeholder="Enter administrator's full name" required />
                            </div>

                            <!-- Login ID -->
                            <div class="form-group-modern">
                                <label class="form-label-modern">Login ID *</label>
                                <input type="text" class="form-input-modern" name="loginid" id="loginid" 
                                    value="<?php echo $rsedit['loginid']; ?>" 
                                    placeholder="Enter unique login ID" required />
                                <p class="text-xs text-slate-500 mt-1">This will be used for admin portal access</p>
                            </div>

                            <!-- Status -->
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
                                <p class="text-xs text-slate-500 mt-1">Active administrators can access the admin panel</p>
                            </div>
                        </div>

                        <!-- Security Credentials Section -->
                        <div class="mb-8">
                            <h3 class="text-h3 text-slate-900 mb-6 flex items-center">
                                <i data-lucide="key" class="w-5 h-5 mr-2 text-purple-500"></i>
                                Security Credentials
                            </h3>
                            
                            <div class="grid-modern grid-cols-1 md:grid-cols-2">
                                <!-- Password -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Password *</label>
                                    <div class="relative">
                                        <input type="password" class="form-input-modern pr-12" name="password" id="password" 
                                            value="<?php echo $rsedit['password']; ?>" 
                                            placeholder="Enter secure password" required />
                                        <button type="button" id="toggle-password" 
                                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                            <i data-lucide="eye" class="w-5 h-5"></i>
                                        </button>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-1">Minimum 8 characters required</p>
                                </div>

                                <!-- Confirm Password -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Confirm Password *</label>
                                    <div class="relative">
                                        <input type="password" class="form-input-modern pr-12" name="cnfirmpassword" id="cnfirmpassword" 
                                            value="<?php echo $rsedit['password']; ?>" 
                                            placeholder="Confirm password" required />
                                        <button type="button" id="toggle-confirm-password" 
                                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                            <i data-lucide="eye" class="w-5 h-5"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-center pt-6">
                            <button type="submit" name="submit" id="submit" class="btn-modern btn-primary px-8 py-3">
                                <i data-lucide="save" class="w-5 h-5"></i>
                                <?php echo isset($_GET['editid']) ? 'Update Administrator' : 'Create Administrator'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Security Guidelines Card -->
            <div class="card-modern mt-8">
                <div class="card-body">
                    <h3 class="text-h3 text-slate-900 mb-4 flex items-center">
                        <i data-lucide="shield-alert" class="w-5 h-5 mr-2 text-amber-500"></i>
                        Security Guidelines
                    </h3>
                    <div class="space-y-3 text-sm text-slate-600">
                        <div class="flex items-start space-x-3">
                            <i data-lucide="check-circle" class="w-4 h-4 text-green-500 mt-0.5"></i>
                            <p>Use strong passwords with at least 8 characters</p>
                        </div>
                        <div class="flex items-start space-x-3">
                            <i data-lucide="check-circle" class="w-4 h-4 text-green-500 mt-0.5"></i>
                            <p>Login IDs should be unique across the system</p>
                        </div>
                        <div class="flex items-start space-x-3">
                            <i data-lucide="check-circle" class="w-4 h-4 text-green-500 mt-0.5"></i>
                            <p>Only active administrators can access the admin panel</p>
                        </div>
                        <div class="flex items-start space-x-3">
                            <i data-lucide="check-circle" class="w-4 h-4 text-green-500 mt-0.5"></i>
                            <p>Administrator actions are logged for security</p>
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

document.getElementById('toggle-confirm-password').addEventListener('click', function() {
    const passwordInput = document.getElementById('cnfirmpassword');
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
    const form = document.frmadminprofile;
    const alphaspaceExp = /^[a-zA-Z\s]+$/;
    const alphanumericExp = /^[0-9a-zA-Z]+$/;
    
    // Clear previous error states
    document.querySelectorAll('.form-input-modern').forEach(input => {
        input.classList.remove('border-red-500');
    });
    
    // Admin name validation
    if (form.adminname.value.trim() === "") {
        showError("Administrator Name Required", "Please enter the administrator's full name.");
        form.adminname.classList.add('border-red-500');
        form.adminname.focus();
        return false;
    }
    
    if (!form.adminname.value.match(alphaspaceExp)) {
        showError("Invalid Administrator Name", "Administrator name should contain only letters and spaces.");
        form.adminname.classList.add('border-red-500');
        form.adminname.focus();
        return false;
    }
    
    // Login ID validation
    if (form.loginid.value.trim() === "") {
        showError("Login ID Required", "Please enter a unique login ID.");
        form.loginid.classList.add('border-red-500');
        form.loginid.focus();
        return false;
    }
    
    if (!form.loginid.value.match(alphanumericExp)) {
        showError("Invalid Login ID", "Login ID should contain only letters and numbers.");
        form.loginid.classList.add('border-red-500');
        form.loginid.focus();
        return false;
    }
    
    // Password validation
    if (form.password.value === "") {
        showError("Password Required", "Please enter a secure password.");
        form.password.classList.add('border-red-500');
        form.password.focus();
        return false;
    }
    
    if (form.password.value.length < 8) {
        showError("Password Too Short", "Password must be at least 8 characters long.");
        form.password.classList.add('border-red-500');
        form.password.focus();
        return false;
    }
    
    // Confirm password validation
    if (form.password.value !== form.cnfirmpassword.value) {
        showError("Passwords Don't Match", "Password and confirm password must be the same.");
        form.cnfirmpassword.classList.add('border-red-500');
        form.cnfirmpassword.focus();
        return false;
    }
    
    // Status validation
    if (form.select.value === "") {
        showError("Status Required", "Please select the administrator status.");
        form.select.classList.add('border-red-500');
        form.select.focus();
        return false;
    }
    
    // Show loading state
    const submitBtn = document.getElementById('submit');
    const originalContent = submitBtn.innerHTML;
    submitBtn.innerHTML = '<div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div> Processing...';
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
        background: '#ffffff',
        color: '#1e293b'
    });
}

// Enhanced input validation
document.querySelectorAll('.form-input-modern').forEach(input => {
    input.addEventListener('input', function() {
        this.classList.remove('border-red-500');
        if (this.value.trim()) {
            this.classList.add('border-purple-500/50');
        } else {
            this.classList.remove('border-purple-500/50');
        }
    });
});

// Auto-generate login ID based on admin name
document.getElementById('adminname').addEventListener('input', function() {
    const loginIdField = document.getElementById('loginid');
    if (!loginIdField.value && this.value) {
        const name = this.value.toLowerCase().replace(/\s+/g, '');
        const randomNum = Math.floor(Math.random() * 1000);
        loginIdField.value = 'admin' + name.substring(0, 4) + randomNum;
    }
});

// Real-time password strength indicator
document.getElementById('password').addEventListener('input', function() {
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

// Status change handler
document.getElementById('select').addEventListener('change', function() {
    const status = this.value;
    const helpText = this.parentElement.querySelector('.text-xs');
    
    if (status === 'Active') {
        helpText.textContent = 'Active administrators can access the admin panel and manage the system';
        helpText.className = 'text-xs text-green-600 mt-1';
    } else if (status === 'Inactive') {
        helpText.textContent = 'Inactive administrators cannot access the admin panel';
        helpText.className = 'text-xs text-red-600 mt-1';
    } else {
        helpText.textContent = 'Active administrators can access the admin panel';
        helpText.className = 'text-xs text-slate-500 mt-1';
    }
});

// Form submission success handling
<?php if(isset($_POST['submit']) && !mysqli_error($con)): ?>
Swal.fire({
    icon: 'success',
    title: 'Success!',
    text: '<?php echo isset($_GET["editid"]) ? "Administrator updated successfully!" : "Administrator created successfully!"; ?>',
    confirmButtonColor: '#8b5cf6'
}).then((result) => {
    if (result.isConfirmed) {
        <?php if(!isset($_GET['editid'])): ?>
        // Clear form for new entry
        document.frmadminprofile.reset();
        <?php endif; ?>
    }
});
<?php endif; ?>
</script>

</body>
</html>
</script>