<?php
include("modern-header.php");
include("dbconnection.php");
if(isset($_POST['submit']))
{
	if(isset($_GET['editid']))
	{
		$sql ="UPDATE patient SET patientname='$_POST[patientname]',admissiondate='$_POST[admissiondate]',admissiontime='$_POST[admissiontme]',address='$_POST[address]',mobileno='$_POST[mobilenumber]',city='$_POST[city]',pincode='$_POST[pincode]',loginid='$_POST[loginid]',password='$_POST[password]',bloodgroup='$_POST[select2]',gender='$_POST[select3]',dob='$_POST[dateofbirth]',status='$_POST[select]' WHERE patientid='$_GET[editid]'";
		if($qsql = mysqli_query($con,$sql))
		{
			echo "<script>alert('patient record updated successfully...');</script>";
		}
		else
		{
			echo mysqli_error($con);
		}	
	}
	else
	{
		$sql ="INSERT INTO patient(patientname,admissiondate,admissiontime,address,mobileno,city,pincode,loginid,password,bloodgroup,gender,dob,status) values('$_POST[patientname]','$dt','$tim','$_POST[address]','$_POST[mobilenumber]','$_POST[city]','$_POST[pincode]','$_POST[loginid]','$_POST[password]','$_POST[select2]','$_POST[select3]','$_POST[dateofbirth]','Active')";
		if($qsql = mysqli_query($con,$sql))
		{
			echo "<script>alert('patients record inserted successfully...');</script>";
			$insid= mysqli_insert_id($con);
			if(isset($_SESSION['adminid']))
			{
				echo "<script>window.location='appointment.php?patid=$insid';</script>";	
			}
			else
			{
				echo "<script>window.location='patientlogin.php';</script>";	
			}		
		}
		else
		{
			echo mysqli_error($con);
		}
	}
}
if(isset($_GET['editid']))
{
	$sql="SELECT * FROM patient WHERE patientid='$_GET[editid]' ";
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
                <i data-lucide="user-plus" class="w-5 h-5 text-healsync-500 mr-2"></i>
                <span class="text-sm font-medium text-slate-700">Patient Management</span>
            </div>
            <h1 class="text-h1 text-slate-900 mb-2">
                <?php echo isset($_GET['editid']) ? 'Edit Patient Record' : 'Patient Registration'; ?>
            </h1>
            <p class="text-slate-600">
                <?php echo isset($_GET['editid']) ? 'Update patient information and medical details' : 'Register a new patient in the HealSync system'; ?>
            </p>
        </div>

        <!-- Registration Form -->
        <div class="max-w-4xl mx-auto">
            <div class="card-modern">
                <div class="card-body">
                    <form method="post" action="" name="frmpatient" onSubmit="return validateform()" class="form-modern">
                        
                        <!-- Personal Information Section -->
                        <div class="mb-8">
                            <h3 class="text-h3 text-slate-900 mb-6 flex items-center">
                                <i data-lucide="user" class="w-5 h-5 mr-2 text-healsync-500"></i>
                                Personal Information
                            </h3>
                            
                            <div class="grid-modern grid-cols-1 md:grid-cols-2">
                                <!-- Patient Name -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Patient Name *</label>
                                    <input class="form-input-modern" type="text" name="patientname" id="patientname"
                                        value="<?php echo $rsedit['patientname']; ?>" placeholder="Enter full name" required />
                                </div>

                                <!-- Date of Birth -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Date of Birth *</label>
                                    <input class="form-input-modern" type="date" name="dateofbirth" max="<?php echo date("Y-m-d"); ?>"
                                        id="dateofbirth" value="<?php echo $rsedit['dob']; ?>" required />
                                </div>

                                <!-- Gender -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Gender *</label>
                                    <select class="form-input-modern" name="select3" id="select3" required>
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

                                <!-- Blood Group -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Blood Group *</label>
                                    <select class="form-input-modern" name="select2" id="select2" required>
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

                                <!-- Mobile Number -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Mobile Number *</label>
                                    <input class="form-input-modern" type="tel" name="mobilenumber" id="mobilenumber"
                                        value="<?php echo $rsedit['mobileno']; ?>" placeholder="Enter mobile number" required />
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
                                        value="<?php echo $rsedit['city']; ?>" placeholder="Enter city" required />
                                </div>

                                <!-- PIN Code -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">PIN Code *</label>
                                    <input class="form-input-modern" type="text" name="pincode" id="pincode"
                                        value="<?php echo $rsedit['pincode']; ?>" placeholder="Enter PIN code" required />
                                </div>
                            </div>
                        </div>

                        <?php if(isset($_GET['editid'])): ?>
                        <!-- Admission Information (Edit Mode Only) -->
                        <div class="mb-8">
                            <h3 class="text-h3 text-slate-900 mb-6 flex items-center">
                                <i data-lucide="calendar" class="w-5 h-5 mr-2 text-healsync-500"></i>
                                Admission Information
                            </h3>
                            
                            <div class="grid-modern grid-cols-1 md:grid-cols-2">
                                <!-- Admission Date -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Admission Date</label>
                                    <input class="form-input-modern bg-slate-100" type="date" name="admissiondate" id="admissiondate"
                                        value="<?php echo $rsedit['admissiondate']; ?>" readonly />
                                </div>

                                <!-- Admission Time -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Admission Time</label>
                                    <input class="form-input-modern bg-slate-100" type="time" name="admissiontme" id="admissiontme"
                                        value="<?php echo $rsedit['admissiontime']; ?>" readonly />
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

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
                                        value="<?php echo $rsedit['loginid']; ?>" placeholder="Enter unique login ID" required />
                                    <p class="text-xs text-slate-500 mt-1">This will be used for patient portal access</p>
                                </div>

                                <div></div> <!-- Empty space for layout -->

                                <!-- Password -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Password *</label>
                                    <div class="relative">
                                        <input class="form-input-modern pr-12" type="password" name="password" id="password"
                                            value="<?php echo $rsedit['password']; ?>" placeholder="Enter password" required />
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
                                        <input class="form-input-modern pr-12" type="password" name="confirmpassword" id="confirmpassword"
                                            value="<?php echo $rsedit['confirmpassword']; ?>" placeholder="Confirm password" required />
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
                                <?php echo isset($_GET['editid']) ? 'Update Patient' : 'Register Patient'; ?>
                            </button>
                        </div>
                    </form>
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
    const passwordInput = document.getElementById('confirmpassword');
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
    const form = document.frmpatient;
    const alphaspaceExp = /^[a-zA-Z\s]+$/;
    const numericExpression = /^[0-9]+$/;
    const alphanumericExp = /^[0-9a-zA-Z]+$/;
    
    // Clear previous error states
    document.querySelectorAll('.form-input-modern').forEach(input => {
        input.classList.remove('border-red-500');
    });
    
    // Patient name validation
    if (form.patientname.value.trim() === "") {
        showError("Patient Name Required", "Please enter the patient's full name.");
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
        showError("Address Required", "Please enter the complete address.");
        form.address.classList.add('border-red-500');
        form.address.focus();
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
    
    // City validation
    if (form.city.value.trim() === "") {
        showError("City Required", "Please enter the city name.");
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
        showError("PIN Code Required", "Please enter the PIN code.");
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
    if (form.password.value !== form.confirmpassword.value) {
        showError("Passwords Don't Match", "Password and confirm password must be the same.");
        form.confirmpassword.classList.add('border-red-500');
        form.confirmpassword.focus();
        return false;
    }
    
    // Blood group validation
    if (form.select2.value === "") {
        showError("Blood Group Required", "Please select a blood group.");
        form.select2.classList.add('border-red-500');
        form.select2.focus();
        return false;
    }
    
    // Gender validation
    if (form.select3.value === "") {
        showError("Gender Required", "Please select gender.");
        form.select3.classList.add('border-red-500');
        form.select3.focus();
        return false;
    }
    
    // Date of birth validation
    if (form.dateofbirth.value === "") {
        showError("Date of Birth Required", "Please select date of birth.");
        form.dateofbirth.classList.add('border-red-500');
        form.dateofbirth.focus();
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

// Auto-generate login ID based on patient name
document.getElementById('patientname').addEventListener('input', function() {
    const loginIdField = document.getElementById('loginid');
    if (!loginIdField.value && this.value) {
        const name = this.value.toLowerCase().replace(/\s+/g, '');
        const randomNum = Math.floor(Math.random() * 1000);
        loginIdField.value = name.substring(0, 6) + randomNum;
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
</script>
</script>