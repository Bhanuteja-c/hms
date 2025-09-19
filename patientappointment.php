<?php
include("modern-header.php");
include("dbconnection.php");
session_start();
if(isset($_POST['submit']))
{  
	if(isset($_SESSION['patientid']))
	{
		$lastinsid =$_SESSION['patientid'];
	}
	else
	{
		$dt = date("Y-m-d");
		$tim = date("H:i:s");
		$sql ="INSERT INTO patient(patientname,admissiondate,admissiontime,address,city,mobileno,loginid,password,gender,dob,status) values('$_POST[patiente]','$dt','$tim','$_POST[textarea]','$_POST[city]','$_POST[mobileno]','$_POST[loginid]','$_POST[password]','$_POST[select6]','$_POST[dob]','Active')";
		if($qsql = mysqli_query($con,$sql))
		{
			 echo "<script>alert('patient record inserted successfully...');</script>"; 
		}
		else
		{
			echo mysqli_error($con);
		}
		$lastinsid = mysqli_insert_id($con);
	}
	
	$sqlappointment="SELECT * FROM appointment WHERE appointmentdate='$_POST[appointmentdate]' AND appointmenttime='$_POST[appointmenttime]' AND doctorid='$_POST[doct]' AND status='Approved'";
	$qsqlappointment = mysqli_query($con,$sqlappointment);
	if(mysqli_num_rows($qsqlappointment) >= 1)
	{
		echo "<script>alert('Appointment already scheduled for this time..');</script>";
	}
	else
	{
		$sql ="INSERT INTO appointment(appointmenttype,patientid,appointmentdate,appointmenttime,app_reason,status,departmentid,doctorid) values('ONLINE','$lastinsid','$_POST[appointmentdate]','$_POST[appointmenttime]','$_POST[app_reason]','Pending','$_POST[department]','$_POST[doct]')";
		if($qsql = mysqli_query($con,$sql))
		{
			echo "<script>alert('Appointment record inserted successfully...');</script>";
		}
		else
		{
			echo mysqli_error($con);
		}
	}
}
if(isset($_GET['editid']))
{
	$sql="SELECT * FROM appointment WHERE appointmentid='$_GET[editid]' ";
	$qsql = mysqli_query($con,$sql);
	$rsedit = mysqli_fetch_array($qsql);
	
}
if(isset($_SESSION['patientid']))
{
    $sqlpatient = "SELECT * FROM patient WHERE patientid=18 ";
    $qsqlpatient = mysqli_query($con,$sqlpatient);
    $rspatient = mysqli_fetch_array($qsqlpatient);
    $readonly = " readonly";
}
?>
<!-- Main Content -->
<main class="pt-24 min-h-screen">
    <?php
    if(isset($_POST['submit']))
    {
        if(mysqli_num_rows($qsqlappointment) >= 1)
        {
            ?>
            <!-- Appointment Conflict Section -->
            <section class="py-20 bg-red-50">
                <div class="container-modern text-center">
                    <div class="max-w-2xl mx-auto">
                        <div class="w-16 h-16 bg-red-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                            <i data-lucide="calendar-x" class="w-8 h-8 text-red-600"></i>
                        </div>
                        <h2 class="text-h2 text-slate-900 mb-4">Appointment Conflict</h2>
                        <p class="text-body text-slate-600 mb-8">
                            An appointment is already scheduled for <?php echo date("d M Y", strtotime($_POST['appointmentdate'])); ?> 
                            at <?php echo date("h:i A", strtotime($_POST['appointmenttime'])); ?>. 
                            Please select a different time slot.
                        </p>
                        <a href="patientappointment.php" class="btn-modern btn-primary">
                            <i data-lucide="calendar-plus" class="w-5 h-5"></i>
                            Book Another Appointment
                        </a>
                    </div>
                </div>
            </section>
            <?php
        }
        else
        {
            ?>
            <!-- Success Section -->
            <section class="py-20 bg-green-50">
                <div class="container-modern text-center">
                    <div class="max-w-2xl mx-auto">
                        <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                            <i data-lucide="calendar-check" class="w-8 h-8 text-green-600"></i>
                        </div>
                        <h2 class="text-h2 text-slate-900 mb-4">Appointment Booked Successfully!</h2>
                        <p class="text-body text-slate-600 mb-8">
                            Your appointment has been submitted and is currently pending approval. 
                            <?php if(isset($_SESSION['patientid'])): ?>
                                You can check the status in your patient dashboard.
                            <?php else: ?>
                                Please wait for confirmation message or login to check status.
                            <?php endif; ?>
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <?php if(isset($_SESSION['patientid'])): ?>
                                <a href="viewappointment.php" class="btn-modern btn-primary">
                                    <i data-lucide="eye" class="w-5 h-5"></i>
                                    View Appointment Status
                                </a>
                            <?php else: ?>
                                <a href="patientlogin.php" class="btn-modern btn-primary">
                                    <i data-lucide="log-in" class="w-5 h-5"></i>
                                    Login to Check Status
                                </a>
                            <?php endif; ?>
                            <a href="patientappointment.php" class="btn-modern btn-secondary">
                                <i data-lucide="calendar-plus" class="w-5 h-5"></i>
                                Book Another Appointment
                            </a>
                        </div>
                    </div>
                </div>
            </section>
            <?php
        }
    }
    else
    {
        ?>
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-healsync-50 to-cyan-50 py-16">
        <div class="container-modern">
            <div class="text-center max-w-3xl mx-auto">
                <div class="inline-flex items-center px-4 py-2 bg-white rounded-full shadow-md mb-6">
                    <i data-lucide="calendar-plus" class="w-5 h-5 text-healsync-500 mr-2"></i>
                    <span class="text-sm font-medium text-slate-700">Book Appointment</span>
                </div>
                
                <h1 class="text-display text-slate-900 mb-6">
                    Schedule Your 
                    <span class="text-healsync-600">Healthcare</span> 
                    Visit
                </h1>
                
                <p class="text-body-lg text-slate-600 mb-8">
                    Book an appointment with our experienced healthcare professionals. 
                    Choose your preferred doctor, date, and time for a convenient consultation.
                </p>
            </div>
        </div>
    </section>

    <!-- Appointment Form Section -->
    <section class="py-20 bg-white">
        <div class="container-modern">
            <div class="max-w-4xl mx-auto">
                <div class="card-modern">
                    <div class="card-header">
                        <h2 class="text-h2 text-slate-900 mb-2">Book Your Appointment</h2>
                        <p class="text-slate-600">Fill out the form below to schedule your healthcare visit</p>
                    </div>
                    <div class="card-body">
                        <form method="post" action="" name="frmpatapp" onSubmit="return validateform()" class="form-modern">
                            
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
                                        <input type="text" class="form-input-modern" name="patiente" id="patiente"
                                            value="<?php echo $rspatient['patientname']; ?>" 
                                            placeholder="Enter patient's full name" 
                                            <?php echo $readonly; ?> required>
                                    </div>

                                    <!-- Contact Number -->
                                    <div class="form-group-modern">
                                        <label class="form-label-modern">Contact Number *</label>
                                        <input type="tel" class="form-input-modern" name="mobileno" id="mobileno"
                                            value="<?php echo $rspatient['mobileno']; ?>" 
                                            placeholder="Enter contact number" 
                                            <?php echo $readonly; ?> required>
                                    </div>

                                    <!-- Address -->
                                    <div class="form-group-modern">
                                        <label class="form-label-modern">Address *</label>
                                        <input type="text" class="form-input-modern" name="textarea" id="textarea"
                                            value="<?php echo $rspatient['address']; ?>" 
                                            placeholder="Enter complete address" 
                                            <?php echo $readonly; ?> required>
                                    </div>

                                    <!-- City -->
                                    <div class="form-group-modern">
                                        <label class="form-label-modern">City *</label>
                                        <input type="text" class="form-input-modern" name="city" id="city" 
                                            value="<?php echo $rspatient['city']; ?>" 
                                            placeholder="Enter city" 
                                            <?php echo $readonly; ?> required>
                                    </div>

                                    <?php if(!isset($_SESSION['patientid'])): ?>
                                    <!-- Login ID -->
                                    <div class="form-group-modern">
                                        <label class="form-label-modern">Email/Login ID *</label>
                                        <input type="email" class="form-input-modern" name="loginid" id="loginid"
                                            value="<?php echo $rspatient['loginid']; ?>" 
                                            placeholder="Enter email address" required>
                                    </div>

                                    <!-- Password -->
                                    <div class="form-group-modern">
                                        <label class="form-label-modern">Password *</label>
                                        <div class="relative">
                                            <input type="password" class="form-input-modern pr-12" name="password" id="password"
                                                value="<?php echo $rspatient['password']; ?>" 
                                                placeholder="Enter password" required>
                                            <button type="button" id="toggle-password" 
                                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                                <i data-lucide="eye" class="w-5 h-5"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <!-- Gender -->
                                    <div class="form-group-modern">
                                        <label class="form-label-modern">Gender *</label>
                                        <?php if(isset($_SESSION['patientid'])): ?>
                                            <input type="text" class="form-input-modern bg-slate-100" 
                                                value="<?php echo $rspatient['gender']; ?>" readonly>
                                        <?php else: ?>
                                            <select name="select6" id="select6" class="form-input-modern" required>
                                                <option value="">Select Gender</option>
                                                <?php
                                                $arr = array("Male","Female");
                                                foreach($arr as $val) {
                                                    echo "<option value='$val'>$val</option>";
                                                }
                                                ?>
                                            </select>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Date of Birth -->
                                    <div class="form-group-modern">
                                        <label class="form-label-modern">Date of Birth *</label>
                                        <input type="date" class="form-input-modern" name="dob" id="dob"
                                            value="<?php echo $rspatient['dob']; ?>" 
                                            max="<?php echo date("Y-m-d"); ?>" 
                                            <?php echo $readonly; ?> required>
                                    </div>
                                </div>
                            </div>

                            <!-- Appointment Details Section -->
                            <div class="mb-8">
                                <h3 class="text-h3 text-slate-900 mb-6 flex items-center">
                                    <i data-lucide="calendar" class="w-5 h-5 mr-2 text-healsync-500"></i>
                                    Appointment Details
                                </h3>
                                
                                <div class="grid-modern grid-cols-1 md:grid-cols-2">
                                    <!-- Appointment Date -->
                                    <div class="form-group-modern">
                                        <label class="form-label-modern">Appointment Date *</label>
                                        <input type="date" class="form-input-modern" name="appointmentdate" id="appointmentdate"
                                            min="<?php echo date("Y-m-d"); ?>" required>
                                    </div>

                                    <!-- Appointment Time -->
                                    <div class="form-group-modern">
                                        <label class="form-label-modern">Appointment Time *</label>
                                        <input type="time" class="form-input-modern" name="appointmenttime" id="appointmenttime" required>
                                    </div>

                                    <!-- Department -->
                                    <div class="form-group-modern">
                                        <label class="form-label-modern">Department *</label>
                                        <select name="department" class="form-input-modern" id="department" required>
                                            <option value="">Select Department</option>
                                            <?php
                                            $sqldept = "SELECT * FROM department WHERE status='Active'";
                                            $qsqldept = mysqli_query($con,$sqldept);
                                            while($rsdept = mysqli_fetch_array($qsqldept)) {
                                                echo "<option value='$rsdept[departmentid]'>$rsdept[departmentname]</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <!-- Doctor -->
                                    <div class="form-group-modern">
                                        <label class="form-label-modern">Doctor *</label>
                                        <select name="doct" class="form-input-modern" id="doctor" required>
                                            <option value="">Select Doctor</option>
                                            <?php
                                            $sqldoct = "SELECT * FROM doctor WHERE status='Active'";
                                            $qsqldoct = mysqli_query($con,$sqldoct);
                                            while($rsdoct = mysqli_fetch_array($qsqldoct)) {
                                                echo "<option value='$rsdoct[doctorid]'>Dr. $rsdoct[doctorname]";
                                                $sqldeptname = "SELECT * FROM department WHERE departmentid='$rsdoct[departmentid]'";
                                                $qsqldeptname = mysqli_query($con,$sqldeptname);
                                                $rsdeptname = mysqli_fetch_array($qsqldeptname);
                                                echo " - " . $rsdeptname['departmentname'];
                                                echo "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <!-- Appointment Reason -->
                                    <div class="md:col-span-2 form-group-modern">
                                        <label class="form-label-modern">Reason for Appointment</label>
                                        <textarea class="form-input-modern" name="app_reason" rows="4" 
                                            placeholder="Please describe your symptoms or reason for the appointment"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-center">
                                <button type="submit" name="submit" id="submit" class="btn-modern btn-primary px-8 py-3">
                                    <i data-lucide="calendar-plus" class="w-5 h-5"></i>
                                    Book Appointment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
        <?php
    }
    ?>



</main>

<?php include 'modern-footer.php';?>

<script>
// Initialize Lucide icons
lucide.createIcons();

// Password toggle functionality (if not logged in)
const togglePassword = document.getElementById('toggle-password');
if (togglePassword) {
    togglePassword.addEventListener('click', function() {
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
}

// Modern form validation
function validateform() {
    const form = document.frmpatapp;
    const alphaspaceExp = /^[a-zA-Z\s]+$/;
    const numericExpression = /^[0-9]+$/;
    const emailExp = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
    
    // Clear previous error states
    document.querySelectorAll('.form-input-modern').forEach(input => {
        input.classList.remove('border-red-500');
    });
    
    // Patient name validation
    if (form.patiente.value.trim() === "") {
        showError("Patient Name Required", "Please enter the patient's full name.");
        form.patiente.classList.add('border-red-500');
        form.patiente.focus();
        return false;
    }
    
    if (!form.patiente.value.match(alphaspaceExp)) {
        showError("Invalid Patient Name", "Patient name should contain only letters and spaces.");
        form.patiente.classList.add('border-red-500');
        form.patiente.focus();
        return false;
    }
    
    // Address validation
    if (form.textarea.value.trim() === "") {
        showError("Address Required", "Please enter the complete address.");
        form.textarea.classList.add('border-red-500');
        form.textarea.focus();
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
    
    // Mobile number validation
    if (form.mobileno.value.trim() === "") {
        showError("Mobile Number Required", "Please enter the mobile number.");
        form.mobileno.classList.add('border-red-500');
        form.mobileno.focus();
        return false;
    }
    
    if (!form.mobileno.value.match(numericExpression) || form.mobileno.value.length !== 10) {
        showError("Invalid Mobile Number", "Please enter a valid 10-digit mobile number.");
        form.mobileno.classList.add('border-red-500');
        form.mobileno.focus();
        return false;
    }
    
    // Login ID validation (if not logged in)
    if (form.loginid && form.loginid.value.trim() === "") {
        showError("Email Required", "Please enter your email address.");
        form.loginid.classList.add('border-red-500');
        form.loginid.focus();
        return false;
    }
    
    if (form.loginid && !form.loginid.value.match(emailExp)) {
        showError("Invalid Email", "Please enter a valid email address.");
        form.loginid.classList.add('border-red-500');
        form.loginid.focus();
        return false;
    }
    
    // Password validation (if not logged in)
    if (form.password && form.password.value === "") {
        showError("Password Required", "Please enter a password.");
        form.password.classList.add('border-red-500');
        form.password.focus();
        return false;
    }
    
    if (form.password && form.password.value.length < 8) {
        showError("Password Too Short", "Password must be at least 8 characters long.");
        form.password.classList.add('border-red-500');
        form.password.focus();
        return false;
    }
    
    // Gender validation (if not logged in)
    if (form.select6 && form.select6.value === "") {
        showError("Gender Required", "Please select gender.");
        form.select6.classList.add('border-red-500');
        form.select6.focus();
        return false;
    }
    
    // Date of birth validation
    if (form.dob.value === "") {
        showError("Date of Birth Required", "Please select date of birth.");
        form.dob.classList.add('border-red-500');
        form.dob.focus();
        return false;
    }
    
    // Appointment date validation
    if (form.appointmentdate.value === "") {
        showError("Appointment Date Required", "Please select appointment date.");
        form.appointmentdate.classList.add('border-red-500');
        form.appointmentdate.focus();
        return false;
    }
    
    // Check if appointment date is not in the past
    const selectedDate = new Date(form.appointmentdate.value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (selectedDate < today) {
        showError("Invalid Date", "Appointment date cannot be in the past.");
        form.appointmentdate.classList.add('border-red-500');
        form.appointmentdate.focus();
        return false;
    }
    
    // Appointment time validation
    if (form.appointmenttime.value === "") {
        showError("Appointment Time Required", "Please select appointment time.");
        form.appointmenttime.classList.add('border-red-500');
        form.appointmenttime.focus();
        return false;
    }
    
    // Department validation
    if (form.department.value === "") {
        showError("Department Required", "Please select a department.");
        form.department.classList.add('border-red-500');
        form.department.focus();
        return false;
    }
    
    // Doctor validation
    if (form.doct.value === "") {
        showError("Doctor Required", "Please select a doctor.");
        form.doct.classList.add('border-red-500');
        form.doct.focus();
        return false;
    }
    
    // Show loading state
    const submitBtn = document.getElementById('submit');
    const originalContent = submitBtn.innerHTML;
    submitBtn.innerHTML = '<div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div> Booking Appointment...';
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

// Department change handler to filter doctors
document.getElementById('department').addEventListener('change', function() {
    const deptId = this.value;
    const doctorSelect = document.getElementById('doctor');
    
    if (deptId) {
        // Clear current doctor options
        doctorSelect.innerHTML = '<option value="">Loading doctors...</option>';
        
        // Fetch doctors for selected department
        fetch(`departmentDoctor.php?deptid=${deptId}`)
            .then(response => response.text())
            .then(data => {
                doctorSelect.innerHTML = data;
            })
            .catch(error => {
                console.error('Error loading doctors:', error);
                doctorSelect.innerHTML = '<option value="">Error loading doctors</option>';
            });
    } else {
        doctorSelect.innerHTML = '<option value="">Select Doctor</option>';
    }
});

// Auto-generate login ID based on patient name (if not logged in)
const patientNameField = document.getElementById('patiente');
const loginIdField = document.getElementById('loginid');

if (patientNameField && loginIdField && !loginIdField.readOnly) {
    patientNameField.addEventListener('input', function() {
        if (!loginIdField.value && this.value) {
            const name = this.value.toLowerCase().replace(/\s+/g, '');
            const randomNum = Math.floor(Math.random() * 1000);
            loginIdField.value = name.substring(0, 6) + randomNum + '@healsync.com';
        }
    });
}

// Time slot suggestions
document.getElementById('appointmenttime').addEventListener('focus', function() {
    // You can add time slot suggestions here
    const commonTimes = ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00'];
    // This could be enhanced to show available time slots
});

// Date picker enhancements
document.getElementById('appointmentdate').addEventListener('change', function() {
    const selectedDate = new Date(this.value);
    const dayOfWeek = selectedDate.getDay();
    
    // Check if it's a weekend (optional warning)
    if (dayOfWeek === 0 || dayOfWeek === 6) {
        Swal.fire({
            icon: 'info',
            title: 'Weekend Appointment',
            text: 'Please note that weekend appointments may have limited availability.',
            confirmButtonColor: '#0ea5e9'
        });
    }
});
</script>
</script>