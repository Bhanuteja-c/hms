<?php include 'modern-header.php'; ?>

<!-- Main Content -->
<main class="pt-24 min-h-screen">
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-healsync-50 to-cyan-50 py-16">
        <div class="container-modern">
            <div class="text-center max-w-3xl mx-auto">
                <div class="inline-flex items-center px-4 py-2 bg-white rounded-full shadow-md mb-6">
                    <i data-lucide="calendar-check" class="w-5 h-5 text-healsync-500 mr-2"></i>
                    <span class="text-sm font-medium text-slate-700">Book Your Appointment</span>
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
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="#appointment-form" class="btn-modern btn-primary">
                        <i data-lucide="calendar-plus" class="w-5 h-5"></i>
                        Book Now
                    </a>
                    <a href="#how-it-works" class="btn-modern btn-secondary">
                        <i data-lucide="help-circle" class="w-5 h-5"></i>
                        How It Works
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-16 bg-white">
        <div class="container-modern">
            <div class="text-center mb-12">
                <h2 class="text-h2 text-slate-900 mb-4">How It Works</h2>
                <p class="text-body text-slate-600 max-w-2xl mx-auto">
                    Simple steps to book your appointment and get the healthcare you need
                </p>
            </div>
            
            <div class="grid-modern grid-cols-1 md:grid-cols-3 max-w-4xl mx-auto">
                <div class="text-center p-6">
                    <div class="w-16 h-16 bg-healsync-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="user-plus" class="w-8 h-8 text-healsync-600"></i>
                    </div>
                    <h3 class="text-h3 text-slate-900 mb-3">1. Fill Details</h3>
                    <p class="text-body-sm text-slate-600">
                        Provide your personal information and medical history for better care
                    </p>
                </div>
                
                <div class="text-center p-6">
                    <div class="w-16 h-16 bg-cyan-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="stethoscope" class="w-8 h-8 text-cyan-600"></i>
                    </div>
                    <h3 class="text-h3 text-slate-900 mb-3">2. Choose Doctor</h3>
                    <p class="text-body-sm text-slate-600">
                        Select from our experienced healthcare professionals based on your needs
                    </p>
                </div>
                
                <div class="text-center p-6">
                    <div class="w-16 h-16 bg-emerald-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="calendar-check" class="w-8 h-8 text-emerald-600"></i>
                    </div>
                    <h3 class="text-h3 text-slate-900 mb-3">3. Get Confirmation</h3>
                    <p class="text-body-sm text-slate-600">
                        Receive instant confirmation and prepare for your healthcare visit
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Appointment Form Section -->
    <section id="appointment-form" class="py-16 bg-slate-50">
        <div class="container-modern">
            <div class="max-w-4xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-h2 text-slate-900 mb-4">Book Your Appointment</h2>
                    <p class="text-body text-slate-600">
                        Fill out the form below and we'll get back to you shortly
                    </p>
                </div>
                
                <div class="card-modern p-8">
                    <form class="form-modern" method="POST" action="appointment.php">
                        <div class="grid-modern grid-cols-1 md:grid-cols-2">
                            <!-- Personal Information -->
                            <div class="md:col-span-2 mb-6">
                                <h3 class="text-h3 text-slate-900 mb-4 flex items-center">
                                    <i data-lucide="user" class="w-5 h-5 mr-2 text-healsync-500"></i>
                                    Personal Information
                                </h3>
                            </div>
                            
                            <div class="form-group-modern">
                                <label class="form-label-modern">Full Name *</label>
                                <input type="text" name="fname" class="form-input-modern" placeholder="Enter your full name" required>
                            </div>
                            
                            <div class="form-group-modern">
                                <label class="form-label-modern">Email Address *</label>
                                <input type="email" name="email" class="form-input-modern" placeholder="Enter your email" required>
                            </div>
                            
                            <div class="form-group-modern">
                                <label class="form-label-modern">Phone Number *</label>
                                <input type="tel" name="mobileno" class="form-input-modern" placeholder="Enter your phone number" required>
                            </div>
                            
                            <div class="form-group-modern">
                                <label class="form-label-modern">Date of Birth</label>
                                <input type="date" name="dob" class="form-input-modern">
                            </div>
                            
                            <div class="form-group-modern">
                                <label class="form-label-modern">Gender</label>
                                <select name="gender" class="form-input-modern">
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            
                            <div class="form-group-modern">
                                <label class="form-label-modern">Blood Group</label>
                                <select name="bloodgroup" class="form-input-modern">
                                    <option value="">Select Blood Group</option>
                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                </select>
                            </div>
                            
                            <div class="md:col-span-2 form-group-modern">
                                <label class="form-label-modern">Address</label>
                                <textarea name="address" class="form-input-modern" rows="3" placeholder="Enter your complete address"></textarea>
                            </div>
                        </div>
                        
                        <!-- Appointment Details -->
                        <div class="grid-modern grid-cols-1 md:grid-cols-2 mt-8">
                            <div class="md:col-span-2 mb-6">
                                <h3 class="text-h3 text-slate-900 mb-4 flex items-center">
                                    <i data-lucide="calendar" class="w-5 h-5 mr-2 text-healsync-500"></i>
                                    Appointment Details
                                </h3>
                            </div>
                            
                            <div class="form-group-modern">
                                <label class="form-label-modern">Department *</label>
                                <select name="department" class="form-input-modern" required>
                                    <option value="">Select Department</option>
                                    <?php
                                    $ret = mysqli_query($con, "SELECT * FROM department");
                                    while($row = mysqli_fetch_array($ret)) {
                                        echo "<option value='".$row['department']."'>".$row['department']."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="form-group-modern">
                                <label class="form-label-modern">Doctor *</label>
                                <select name="doctor" class="form-input-modern" required>
                                    <option value="">Select Doctor</option>
                                    <?php
                                    $ret = mysqli_query($con, "SELECT * FROM doctor");
                                    while($row = mysqli_fetch_array($ret)) {
                                        echo "<option value='".$row['doctorname']."'>Dr. ".$row['doctorname']." - ".$row['department']."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="form-group-modern">
                                <label class="form-label-modern">Preferred Date *</label>
                                <input type="date" name="appointmentdate" class="form-input-modern" min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            
                            <div class="form-group-modern">
                                <label class="form-label-modern">Preferred Time *</label>
                                <select name="appointmenttime" class="form-input-modern" required>
                                    <option value="">Select Time</option>
                                    <option value="09:00">09:00 AM</option>
                                    <option value="10:00">10:00 AM</option>
                                    <option value="11:00">11:00 AM</option>
                                    <option value="12:00">12:00 PM</option>
                                    <option value="14:00">02:00 PM</option>
                                    <option value="15:00">03:00 PM</option>
                                    <option value="16:00">04:00 PM</option>
                                    <option value="17:00">05:00 PM</option>
                                </select>
                            </div>
                            
                            <div class="md:col-span-2 form-group-modern">
                                <label class="form-label-modern">Reason for Visit</label>
                                <textarea name="symptoms" class="form-input-modern" rows="4" placeholder="Please describe your symptoms or reason for the appointment"></textarea>
                            </div>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="flex justify-center mt-8">
                            <button type="submit" name="submit" class="btn-modern btn-primary px-8 py-3">
                                <i data-lucide="calendar-plus" class="w-5 h-5"></i>
                                Book Appointment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 bg-white">
        <div class="container-modern">
            <div class="text-center mb-12">
                <h2 class="text-h2 text-slate-900 mb-4">Why Choose HealSync?</h2>
                <p class="text-body text-slate-600 max-w-2xl mx-auto">
                    Experience modern healthcare management with our comprehensive features
                </p>
            </div>
            
            <div class="grid-modern grid-cols-1 md:grid-cols-2 lg:grid-cols-4">
                <div class="card-modern p-6 text-center">
                    <div class="w-12 h-12 bg-healsync-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="clock" class="w-6 h-6 text-healsync-600"></i>
                    </div>
                    <h3 class="text-h3 text-slate-900 mb-2">24/7 Support</h3>
                    <p class="text-body-sm text-slate-600">
                        Round-the-clock healthcare support for emergencies
                    </p>
                </div>
                
                <div class="card-modern p-6 text-center">
                    <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="shield-check" class="w-6 h-6 text-emerald-600"></i>
                    </div>
                    <h3 class="text-h3 text-slate-900 mb-2">Secure & Private</h3>
                    <p class="text-body-sm text-slate-600">
                        Your medical data is protected with advanced security
                    </p>
                </div>
                
                <div class="card-modern p-6 text-center">
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="users" class="w-6 h-6 text-purple-600"></i>
                    </div>
                    <h3 class="text-h3 text-slate-900 mb-2">Expert Doctors</h3>
                    <p class="text-body-sm text-slate-600">
                        Qualified healthcare professionals across specialties
                    </p>
                </div>
                
                <div class="card-modern p-6 text-center">
                    <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="smartphone" class="w-6 h-6 text-amber-600"></i>
                    </div>
                    <h3 class="text-h3 text-slate-900 mb-2">Easy Access</h3>
                    <p class="text-body-sm text-slate-600">
                        Access your health records anytime, anywhere
                    </p>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
// Handle form submission
if(isset($_POST['submit'])) {
    $fname = $_POST['fname'];
    $email = $_POST['email'];
    $mobileno = $_POST['mobileno'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $bloodgroup = $_POST['bloodgroup'];
    $address = $_POST['address'];
    $department = $_POST['department'];
    $doctor = $_POST['doctor'];
    $appointmentdate = $_POST['appointmentdate'];
    $appointmenttime = $_POST['appointmenttime'];
    $symptoms = $_POST['symptoms'];
    $status = 'Pending';
    $appointmentno = mt_rand(100000000, 999999999);
    
    $query = mysqli_query($con, "INSERT INTO appointment(appointmentno, fname, email, mobileno, dob, gender, bloodgroup, address, department, doctor, appointmentdate, appointmenttime, symptoms, status) VALUES('$appointmentno', '$fname', '$email', '$mobileno', '$dob', '$gender', '$bloodgroup', '$address', '$department', '$doctor', '$appointmentdate', '$appointmenttime', '$symptoms', '$status')");
    
    if($query) {
        echo "<script>
            Swal.fire({
                title: 'Success!',
                text: 'Your appointment has been booked successfully. Appointment No: $appointmentno',
                icon: 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: '#0ea5e9'
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                title: 'Error!',
                text: 'Something went wrong. Please try again.',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#ef4444'
            });
        </script>";
    }
}
?>

<!-- Modern Footer -->
<footer class="bg-slate-900 text-white py-12">
    <div class="container-modern">
        <div class="grid-modern grid-cols-1 md:grid-cols-4">
            <div class="md:col-span-2">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-healsync-500 to-healsync-600 rounded-xl flex items-center justify-center">
                        <i data-lucide="heart-pulse" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold">HealSync</h3>
                        <p class="text-sm text-slate-400">Healthcare Management System</p>
                    </div>
                </div>
                <p class="text-slate-400 mb-6 max-w-md">
                    Modern healthcare management system designed to provide efficient and secure healthcare services for patients and healthcare providers.
                </p>
                <div class="flex space-x-4">
                    <a href="#" class="w-10 h-10 bg-slate-800 rounded-lg flex items-center justify-center hover:bg-healsync-600 transition-colors">
                        <i data-lucide="facebook" class="w-5 h-5"></i>
                    </a>
                    <a href="#" class="w-10 h-10 bg-slate-800 rounded-lg flex items-center justify-center hover:bg-healsync-600 transition-colors">
                        <i data-lucide="twitter" class="w-5 h-5"></i>
                    </a>
                    <a href="#" class="w-10 h-10 bg-slate-800 rounded-lg flex items-center justify-center hover:bg-healsync-600 transition-colors">
                        <i data-lucide="instagram" class="w-5 h-5"></i>
                    </a>
                </div>
            </div>
            
            <div>
                <h4 class="font-semibold mb-4">Quick Links</h4>
                <ul class="space-y-2 text-slate-400">
                    <li><a href="index.php" class="hover:text-white transition-colors">Home</a></li>
                    <li><a href="about.php" class="hover:text-white transition-colors">About Us</a></li>
                    <li><a href="contact.php" class="hover:text-white transition-colors">Contact</a></li>
                    <li><a href="appointment.php" class="hover:text-white transition-colors">Book Appointment</a></li>
                </ul>
            </div>
            
            <div>
                <h4 class="font-semibold mb-4">Contact Info</h4>
                <ul class="space-y-2 text-slate-400">
                    <li class="flex items-center space-x-2">
                        <i data-lucide="phone" class="w-4 h-4"></i>
                        <span>+1 (555) 123-4567</span>
                    </li>
                    <li class="flex items-center space-x-2">
                        <i data-lucide="mail" class="w-4 h-4"></i>
                        <span>info@healsync.com</span>
                    </li>
                    <li class="flex items-center space-x-2">
                        <i data-lucide="map-pin" class="w-4 h-4"></i>
                        <span>123 Healthcare St, Medical City</span>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="border-t border-slate-800 mt-8 pt-8 text-center text-slate-400">
            <p>&copy; 2025 HealSync. All rights reserved. | Modern Healthcare Management System</p>
        </div>
    </div>
</footer>

<script>
    // Initialize Lucide icons
    lucide.createIcons();
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Form validation and enhancement
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('border-red-500');
                } else {
                    field.classList.remove('border-red-500');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                Swal.fire({
                    title: 'Missing Information',
                    text: 'Please fill in all required fields.',
                    icon: 'warning',
                    confirmButtonColor: '#0ea5e9'
                });
            }
        });
    }
</script>

</body>
</html>