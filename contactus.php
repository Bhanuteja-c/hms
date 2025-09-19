<?php
include("dbconnection.php");
require "vendor/autoload.php";
use PHPMailer\PHPMailer\PHPMailer;
// Handle form submission
if(isset($_POST['submit']))
{  
	$name = mysqli_real_escape_string($con, $_POST['name']);
	$email = mysqli_real_escape_string($con, $_POST['email']);
	$comment = mysqli_real_escape_string($con, $_POST['comment']);
	
	$message = "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
				<h2 style='color: #10b981;'>New Contact Form Submission</h2>
				<div style='background: #f8fafc; padding: 20px; border-radius: 8px; margin: 20px 0;'>
					<p><strong>Name:</strong> $name</p>
					<p><strong>Email:</strong> $email</p>
					<p><strong>Message:</strong></p>
					<div style='background: white; padding: 15px; border-radius: 4px; border-left: 4px solid #10b981;'>
						$comment
					</div>
				</div>
				<p style='color: #64748b; font-size: 12px;'>This message was sent from the HealSync contact form.</p>
			</div>";
	
	if(sendmail("danielchristopher315@gmail.com", "New Contact Form - HealSync", $message)) {
		$success_message = "Thank you for contacting us! We'll get back to you soon.";
	} else {
		$error_message = "Sorry, there was an error sending your message. Please try again.";
	}
}
?>
<!DOCTYPE html>
<html class="no-js" lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contact Us - HealSync</title>
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
<!-- Modern Header -->
<?php include("modern-header.php"); ?>
<!-- Main Content -->
<main class="py-16">
    <div class="container-modern">
        <!-- Header -->
        <div class="text-center mb-16">
            <div class="inline-flex items-center px-4 py-2 bg-white rounded-full shadow-md mb-4">
                <i data-lucide="mail" class="w-5 h-5 text-emerald-500 mr-2"></i>
                <span class="text-sm font-medium text-slate-700">Get In Touch</span>
            </div>
            <h1 class="text-h1 text-slate-900 mb-4">Contact Us</h1>
            <p class="text-xl text-slate-600 max-w-2xl mx-auto">
                Have questions or need assistance? We're here to help you with all your healthcare needs.
            </p>
        </div>
        <!-- Success/Error Messages -->
        <?php if(isset($success_message)): ?>
            <div class="max-w-4xl mx-auto mb-8">
                <div class="bg-success/10 border border-success/20 rounded-xl p-4">
                    <div class="flex items-center space-x-3">
                        <i data-lucide="check-circle" class="w-5 h-5 text-success"></i>
                        <span class="text-success font-medium"><?php echo $success_message; ?></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if(isset($error_message)): ?>
            <div class="max-w-4xl mx-auto mb-8">
                <div class="bg-error/10 border border-error/20 rounded-xl p-4">
                    <div class="flex items-center space-x-3">
                        <i data-lucide="alert-circle" class="w-5 h-5 text-error"></i>
                        <span class="text-error font-medium"><?php echo $error_message; ?></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <!-- Contact Section -->
        <div class="max-w-6xl mx-auto">
            <div class="grid-modern grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Contact Information -->
                <div class="space-y-8">
                    <!-- Hospital Info Card -->
                    <div class="card-modern">
                        <div class="card-body">
                            <h3 class="text-h3 text-slate-900 mb-6 flex items-center">
                                <i data-lucide="map-pin" class="w-6 h-6 mr-3 text-emerald-500"></i>
                                Our Location
                            </h3>
                            <div class="space-y-4">
                                <div class="flex items-start space-x-4">
                                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                                        <i data-lucide="building" class="w-5 h-5 text-emerald-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-slate-900">HealSync Medical Center</h4>
                                        <p class="text-slate-600">Online Hospital Management System<br>Bangalore, Karnataka</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-4">
                                    <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
                                        <i data-lucide="phone" class="w-5 h-5 text-primary-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-slate-900">Phone</h4>
                                        <p class="text-slate-600">080661 86611</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-4">
                                    <div class="w-10 h-10 bg-secondary-100 rounded-lg flex items-center justify-center">
                                        <i data-lucide="mail" class="w-5 h-5 text-secondary-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-slate-900">Email</h4>
                                        <p class="text-slate-600">danielchristopher315@gmail.com</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Operating Hours -->
                    <div class="card-modern">
                        <div class="card-body">
                            <h3 class="text-h3 text-slate-900 mb-6 flex items-center">
                                <i data-lucide="clock" class="w-6 h-6 mr-3 text-emerald-500"></i>
                                Operating Hours
                            </h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-2 border-b border-slate-100">
                                    <span class="text-slate-700">Monday - Friday</span>
                                    <span class="font-medium text-slate-900">8:00 AM - 8:00 PM</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-slate-100">
                                    <span class="text-slate-700">Saturday</span>
                                    <span class="font-medium text-slate-900">9:00 AM - 6:00 PM</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-slate-100">
                                    <span class="text-slate-700">Sunday</span>
                                    <span class="font-medium text-slate-900">10:00 AM - 4:00 PM</span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-slate-700">Emergency</span>
                                    <span class="font-medium text-emerald-600">24/7 Available</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Contact Form -->
                <div class="card-modern">
                    <div class="card-body">
                        <h3 class="text-h3 text-slate-900 mb-6 flex items-center">
                            <i data-lucide="send" class="w-6 h-6 mr-3 text-emerald-500"></i>
                            Send us a Message
                        </h3>
                        <form action="" method="post" name="contactForm" onSubmit="return validateContactForm()" class="form-modern">
                            <!-- Name Field -->
                            <div class="form-group-modern">
                                <label class="form-label-modern">Full Name *</label>
                                <input class="form-input-modern" type="text" name="name" id="name" 
                                    placeholder="Enter your full name" required />
                            </div>
                            <!-- Email Field -->
                            <div class="form-group-modern">
                                <label class="form-label-modern">Email Address *</label>
                                <input class="form-input-modern" type="email" name="email" id="email" 
                                    placeholder="Enter your email address" required />
                            </div>
                            <!-- Message Field -->
                            <div class="form-group-modern">
                                <label class="form-label-modern">Message *</label>
                                <textarea class="form-input-modern" name="comment" id="comment" rows="6" 
                                    placeholder="Tell us how we can help you..." required></textarea>
                            </div>
                            <!-- Submit Buttons -->
                            <div class="flex flex-col sm:flex-row gap-4 pt-6">
                                <button type="submit" name="submit" id="submit" class="btn-modern btn-primary flex-1">
                                    <i data-lucide="send" class="w-5 h-5"></i>
                                    Send Message
                                </button>
                                <button type="reset" name="reset" id="reset" class="btn-modern btn-ghost flex-1">
                                    <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                                    Reset Form
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Quick Actions -->
        <div class="max-w-4xl mx-auto mt-16">
            <div class="text-center mb-8">
                <h2 class="text-h2 text-slate-900 mb-4">Need Immediate Assistance?</h2>
                <p class="text-slate-600">Choose from our quick action options</p>
            </div>
            <div class="grid-modern grid-cols-1 md:grid-cols-3 gap-6">
                <a href="patientappointment.php" class="card-modern hover:shadow-lg transition-shadow duration-300">
                    <div class="card-body text-center">
                        <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="calendar-plus" class="w-6 h-6 text-emerald-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">Book Appointment</h3>
                        <p class="text-slate-600 text-sm">Schedule your visit with our specialists</p>
                    </div>
                </a>
                <a href="tel:08066186611" class="card-modern hover:shadow-lg transition-shadow duration-300">
                    <div class="card-body text-center">
                        <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="phone-call" class="w-6 h-6 text-primary-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">Call Us</h3>
                        <p class="text-slate-600 text-sm">Speak directly with our support team</p>
                    </div>
                </a>
                <a href="mailto:danielchristopher315@gmail.com" class="card-modern hover:shadow-lg transition-shadow duration-300">
                    <div class="card-body text-center">
                        <div class="w-12 h-12 bg-secondary-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="mail" class="w-6 h-6 text-secondary-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">Email Us</h3>
                        <p class="text-slate-600 text-sm">Send us an email for detailed inquiries</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</main>
<!-- Modern Footer -->
<?php include("modern-footer.php"); ?>
<script>
// Initialize Lucide icons
lucide.createIcons();
// Modern form validation
function validateContactForm() {
    const form = document.contactForm;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    // Clear previous error states
    document.querySelectorAll('.form-input-modern').forEach(input => {
        input.classList.remove('border-red-500');
    });
    // Name validation
    if (form.name.value.trim() === "") {
        showError("Name Required", "Please enter your full name.");
        form.name.classList.add('border-red-500');
        form.name.focus();
        return false;
    }
    if (form.name.value.trim().length < 2) {
        showError("Invalid Name", "Name must be at least 2 characters long.");
        form.name.classList.add('border-red-500');
        form.name.focus();
        return false;
    }
    // Email validation
    if (form.email.value.trim() === "") {
        showError("Email Required", "Please enter your email address.");
        form.email.classList.add('border-red-500');
        form.email.focus();
        return false;
    }
    if (!emailRegex.test(form.email.value)) {
        showError("Invalid Email", "Please enter a valid email address.");
        form.email.classList.add('border-red-500');
        form.email.focus();
        return false;
    }
    // Message validation
    if (form.comment.value.trim() === "") {
        showError("Message Required", "Please enter your message.");
        form.comment.classList.add('border-red-500');
        form.comment.focus();
        return false;
    }
    if (form.comment.value.trim().length < 10) {
        showError("Message Too Short", "Please provide a more detailed message (at least 10 characters).");
        form.comment.classList.add('border-red-500');
        form.comment.focus();
        return false;
    }
    // Show loading state
    const submitBtn = document.getElementById('submit');
    const originalContent = submitBtn.innerHTML;
    submitBtn.innerHTML = '<div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div> Sending Message...';
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
document.querySelectorAll('.form-input-modern').forEach(input => {
    input.addEventListener('input', function() {
        this.classList.remove('border-red-500');
        if (this.value.trim()) {
            this.classList.add('border-emerald-500/50');
        } else {
            this.classList.remove('border-emerald-500/50');
        }
    });
});
// Form submission success handling
<?php if(isset($success_message)): ?>
Swal.fire({
    icon: 'success',
    title: 'Message Sent!',
    text: '<?php echo $success_message; ?>',
    confirmButtonColor: '#10b981'
});
<?php endif; ?>
<?php if(isset($error_message)): ?>
Swal.fire({
    icon: 'error',
    title: 'Send Failed!',
    text: '<?php echo $error_message; ?>',
    confirmButtonColor: '#10b981'
});
<?php endif; ?>
</script>
</body>
</html>
<?php
function sendmail($toaddress,$subject,$message)
{
	try {
		$mail = new PHPMailer;
		$mail->isSMTP();
		$mail->Host = 'mail.dentaldiary.in';
		$mail->SMTPAuth = true;
		$mail->Username = 'sendmail@dentaldiary.in';
		$mail->Password = 'q1w2e3r4/';
		$mail->SMTPSecure = 'tls';
		$mail->Port = 587;
		
		$mail->setFrom('sendmail@dentaldiary.in', 'HealSync Contact Form');
		$mail->addAddress($toaddress);
		$mail->addReplyTo($_POST['email'], $_POST['name']);
		
		$mail->isHTML(true);
		$mail->Subject = $subject;
		$mail->Body = $message;
		$mail->AltBody = strip_tags($message);
		
		return $mail->send();
	} catch (Exception $e) {
		return false;
	}
}
?>