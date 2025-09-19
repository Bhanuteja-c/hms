<?php
include("modern-header.php");
include("dbconnection.php");
if(isset($_POST['submit']))
{
	if(isset($_GET['editid']))
	{
			$sql ="UPDATE doctor SET doctorname='$_POST[doctorname]',mobileno='$_POST[mobilenumber]',departmentid='$_POST[select3]',loginid='$_POST[loginid]',password='$_POST[password]',status='$_POST[select]',education='$_POST[education]',experience='$_POST[experience]',consultancy_charge='$_POST[consultancy_charge]' WHERE doctorid='$_GET[editid]'";
		if($qsql = mysqli_query($con,$sql))
		{
			echo "<script>alert('doctor record updated successfully...');</script>";
		}
		else
		{
			echo mysqli_error($con);
		}	
	}
	else
	{
	$sql ="INSERT INTO doctor(doctorname,mobileno,departmentid,loginid,password,status,education,experience,consultancy_charge) values('$_POST[doctorname]','$_POST[mobilenumber]','$_POST[select3]','$_POST[loginid]','$_POST[password]','Active','$_POST[education]','$_POST[experience]','$_POST[consultancy_charge]')";
	if($qsql = mysqli_query($con,$sql))
	{
		echo "<script>alert('Doctor record inserted successfully...');</script>";
	}
	else
	{
		echo mysqli_error($con);
	}
}
}
if(isset($_GET['editid']))
{
	$sql="SELECT * FROM doctor WHERE doctorid='$_GET[editid]' ";
	$qsql = mysqli_query($con,$sql);
	$rsedit = mysqli_fetch_array($qsql);
	
}
?>

<!-- Main Content -->
<main class="pt-24 min-h-screen bg-slate-50">
    <div class="container-modern py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center px-4 py-2 bg-white rounded-full shadow-md mb-4">
                <i data-lucide="stethoscope" class="w-5 h-5 text-healsync-500 mr-2"></i>
                <span class="text-sm font-medium text-slate-700">Doctor Management</span>
            </div>
            <h1 class="text-h1 text-slate-900 mb-2">
                <?php echo isset($_GET['editid']) ? 'Edit Doctor Profile' : 'Add New Doctor'; ?>
            </h1>
            <p class="text-slate-600">
                <?php echo isset($_GET['editid']) ? 'Update doctor information and credentials' : 'Register a new doctor in the HealSync system'; ?>
            </p>
        </div>

        <!-- Doctor Form -->
        <div class="max-w-4xl mx-auto">
            <div class="card-modern">
                <div class="card-body">
                    <form method="post" action="" name="frmdoct" onSubmit="return validateform()" class="form-modern">
                        
                        <!-- Personal Information Section -->
                        <div class="mb-8">
                            <h3 class="text-h3 text-slate-900 mb-6 flex items-center">
                                <i data-lucide="user" class="w-5 h-5 mr-2 text-healsync-500"></i>
                                Personal Information
                            </h3>
                            
                            <div class="grid-modern grid-cols-1 md:grid-cols-2">
                                <!-- Doctor Name -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Doctor Name *</label>
                                    <input class="form-input-modern" type="text" name="doctorname" id="doctorname" 
                                        value="<?php echo $rsedit['doctorname']; ?>" 
                                        placeholder="Enter doctor's full name" required />
                                </div>

                                <!-- Mobile Number -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Mobile Number *</label>
                                    <input class="form-input-modern" type="tel" name="mobilenumber" id="mobilenumber" 
                                        value="<?php echo $rsedit['mobileno']; ?>" 
                                        placeholder="Enter mobile number" required />
                                </div>

                                <!-- Department -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Department *</label>
                                    <select name="select3" id="select3" class="form-input-modern" required>
                                        <option value="">Select Department</option>
                                        <?php
                                        $sqldepartment= "SELECT * FROM department WHERE status='Active'";
                                        $qsqldepartment = mysqli_query($con,$sqldepartment);
                                        while($rsdepartment=mysqli_fetch_array($qsqldepartment))
                                        {
                                            if($rsdepartment['departmentid'] == $rsedit['departmentid'])
                                            {
                                                echo "<option value='$rsdepartment[departmentid]' selected>$rsdepartment[departmentname]</option>";
                                            }
                                            else
                                            {
                                                echo "<option value='$rsdepartment[departmentid]'>$rsdepartment[departmentname]</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>

                                <!-- Status -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Status *</label>
                                    <select class="form-input-modern" name="select" id="select" required>
                                        <option value="">Select Status</option>
                                        <?php
                                        $arr= array("Active","Inactive");
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
                                </div>
                            </div>
                        </div>

                        <!-- Professional Information Section -->
                        <div class="mb-8">
                            <h3 class="text-h3 text-slate-900 mb-6 flex items-center">
                                <i data-lucide="graduation-cap" class="w-5 h-5 mr-2 text-healsync-500"></i>
                                Professional Information
                            </h3>
                            
                            <div class="grid-modern grid-cols-1 md:grid-cols-2">
                                <!-- Education -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Highest Education *</label>
                                    <input class="form-input-modern" type="text" name="education" id="education" 
                                        value="<?php echo $rsedit['education']; ?>" 
                                        placeholder="e.g., MBBS, MD, MS" required />
                                </div>

                                <!-- Experience -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Experience (Years) *</label>
                                    <input class="form-input-modern" type="number" name="experience" id="experience" 
                                        value="<?php echo $rsedit['experience']; ?>" 
                                        placeholder="Enter years of experience" min="0" max="50" required />
                                </div>

                                <!-- Consultancy Charge -->
                                <div class="md:col-span-2 form-group-modern">
                                    <label class="form-label-modern">Consultancy Charge (₹) *</label>
                                    <input class="form-input-modern" type="number" name="consultancy_charge" id="consultancy_charge" 
                                        value="<?php echo $rsedit['consultancy_charge']; ?>" 
                                        placeholder="Enter consultation fee" min="0" step="50" required />
                                    <p class="text-xs text-slate-500 mt-1">Consultation fee per appointment</p>
                                </div>
                            </div>
                        </div>

                        <!-- Login Credentials Section -->
                        <div class="mb-8">
                            <h3 class="text-h3 text-slate-900 mb-6 flex items-center">
                                <i data-lucide="key" class="w-5 h-5 mr-2 text-healsync-500"></i>
                                Login Credentials
                            </h3>
                            
                            <div class="grid-modern grid-cols-1 md:grid-cols-2">
                                <!-- Login ID -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Login ID *</label>
                                    <input class="form-input-modern" type="text" name="loginid" id="loginid" 
                                        value="<?php echo $rsedit['loginid']; ?>" 
                                        placeholder="Enter unique login ID" required />
                                    <p class="text-xs text-slate-500 mt-1">This will be used for doctor portal access</p>
                                </div>

                                <div></div> <!-- Empty space for layout -->

                                <!-- Password -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Password *</label>
                                    <div class="relative">
                                        <input class="form-input-modern pr-12" type="password" name="password" id="password" 
                                            value="<?php echo $rsedit['password']; ?>" 
                                            placeholder="Enter password" required />
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
                                        <input class="form-input-modern pr-12" type="password" name="cnfirmpassword" id="cnfirmpassword" 
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
                                <?php echo isset($_GET['editid']) ? 'Update Doctor' : 'Add Doctor'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Guidelines Card -->
            <div class="card-modern mt-8">
                <div class="card-body">
                    <h3 class="text-h3 text-slate-900 mb-4 flex items-center">
                        <i data-lucide="info" class="w-5 h-5 mr-2 text-healsync-500"></i>
                        Doctor Registration Guidelines
                    </h3>
                    <div class="grid-modern grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-3 text-sm text-slate-600">
                            <div class="flex items-start space-x-3">
                                <i data-lucide="check-circle" class="w-4 h-4 text-green-500 mt-0.5"></i>
                                <p>Ensure all medical credentials are verified</p>
                            </div>
                            <div class="flex items-start space-x-3">
                                <i data-lucide="check-circle" class="w-4 h-4 text-green-500 mt-0.5"></i>
                                <p>Login ID should be unique across the system</p>
                            </div>
                            <div class="flex items-start space-x-3">
                                <i data-lucide="check-circle" class="w-4 h-4 text-green-500 mt-0.5"></i>
                                <p>Active doctors can receive appointments</p>
                            </div>
                        </div>
                        <div class="space-y-3 text-sm text-slate-600">
                            <div class="flex items-start space-x-3">
                                <i data-lucide="check-circle" class="w-4 h-4 text-green-500 mt-0.5"></i>
                                <p>Consultancy charges are displayed to patients</p>
                            </div>
                            <div class="flex items-start space-x-3">
                                <i data-lucide="check-circle" class="w-4 h-4 text-green-500 mt-0.5"></i>
                                <p>Experience should be in years</p>
                            </div>
                            <div class="flex items-start space-x-3">
                                <i data-lucide="check-circle" class="w-4 h-4 text-green-500 mt-0.5"></i>
                                <p>Department assignment affects appointment routing</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include 'modern-footer.php';?>

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
    const form = document.frmdoct;
    const alphaspaceExp = /^[a-zA-Z\s]+$/;
    const numericExpression = /^[0-9]+$/;
    const alphanumericExp = /^[0-9a-zA-Z]+$/;
    const alphaExp = /^[a-zA-Z\s,.-]+$/; // Allow common education formats
    
    // Clear previous error states
    document.querySelectorAll('.form-input-modern').forEach(input => {
        input.classList.remove('border-red-500');
    });
    
    // Doctor name validation
    if (form.doctorname.value.trim() === "") {
        showError("Doctor Name Required", "Please enter the doctor's full name.");
        form.doctorname.classList.add('border-red-500');
        form.doctorname.focus();
        return false;
    }
    
    if (!form.doctorname.value.match(alphaspaceExp)) {
        showError("Invalid Doctor Name", "Doctor name should contain only letters and spaces.");
        form.doctorname.classList.add('border-red-500');
        form.doctorname.focus();
        return false;
    }
    
    // Mobile number validation
    if (form.mobilenumber.value.trim() === "") {
        showError("Mobile Number Required", "Please enter the mobile number.");
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
    
    // Department validation
    if (form.select3.value === "") {
        showError("Department Required", "Please select a department.");
        form.select3.classList.add('border-red-500');
        form.select3.focus();
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
        showError("Password Required", "Please enter a password.");
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
    
    // Education validation
    if (form.education.value.trim() === "") {
        showError("Education Required", "Please enter the highest education qualification.");
        form.education.classList.add('border-red-500');
        form.education.focus();
        return false;
    }
    
    if (!form.education.value.match(alphaExp)) {
        showError("Invalid Education Format", "Please enter a valid education qualification (e.g., MBBS, MD, MS).");
        form.education.classList.add('border-red-500');
        form.education.focus();
        return false;
    }
    
    // Experience validation
    if (form.experience.value.trim() === "") {
        showError("Experience Required", "Please enter years of experience.");
        form.experience.classList.add('border-red-500');
        form.experience.focus();
        return false;
    }
    
    if (!form.experience.value.match(numericExpression) || parseInt(form.experience.value) < 0 || parseInt(form.experience.value) > 50) {
        showError("Invalid Experience", "Please enter a valid number of years (0-50).");
        form.experience.classList.add('border-red-500');
        form.experience.focus();
        return false;
    }
    
    // Consultancy charge validation
    if (form.consultancy_charge.value.trim() === "") {
        showError("Consultancy Charge Required", "Please enter the consultation fee.");
        form.consultancy_charge.classList.add('border-red-500');
        form.consultancy_charge.focus();
        return false;
    }
    
    if (parseInt(form.consultancy_charge.value) < 0) {
        showError("Invalid Consultancy Charge", "Consultation fee cannot be negative.");
        form.consultancy_charge.classList.add('border-red-500');
        form.consultancy_charge.focus();
        return false;
    }
    
    // Status validation
    if (form.select.value === "") {
        showError("Status Required", "Please select the doctor status.");
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
            this.classList.add('border-healsync-500/50');
        } else {
            this.classList.remove('border-healsync-500/50');
        }
    });
});

// Auto-generate login ID based on doctor name
document.getElementById('doctorname').addEventListener('input', function() {
    const loginIdField = document.getElementById('loginid');
    if (!loginIdField.value && this.value) {
        const name = this.value.toLowerCase().replace(/\s+/g, '');
        const randomNum = Math.floor(Math.random() * 1000);
        loginIdField.value = 'dr' + name.substring(0, 6) + randomNum;
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
    
    if (!helpText) {
        const newHelpText = document.createElement('p');
        newHelpText.className = 'text-xs text-slate-500 mt-1';
        this.parentElement.appendChild(newHelpText);
    }
    
    const helpElement = this.parentElement.querySelector('.text-xs');
    
    if (status === 'Active') {
        helpElement.textContent = 'Active doctors can receive appointments and access the portal';
        helpElement.className = 'text-xs text-green-600 mt-1';
    } else if (status === 'Inactive') {
        helpElement.textContent = 'Inactive doctors cannot receive new appointments';
        helpElement.className = 'text-xs text-red-600 mt-1';
    }
});

// Form submission success handling
<?php if(isset($_POST['submit']) && !mysqli_error($con)): ?>
Swal.fire({
    icon: 'success',
    title: 'Success!',
    text: '<?php echo isset($_GET["editid"]) ? "Doctor profile updated successfully!" : "Doctor registered successfully!"; ?>',
    confirmButtonColor: '#0ea5e9'
}).then((result) => {
    if (result.isConfirmed) {
        <?php if(!isset($_GET['editid'])): ?>
        // Clear form for new entry
        document.frmdoct.reset();
        <?php endif; ?>
    }
});
<?php endif; ?>
</script>
</script>