
  <?php include 'modern-header.php';?>

  <!-- Main Content -->
  <main class="pt-24 min-h-screen">
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-healsync-50 to-cyan-50 py-16">
        <div class="container-modern">
            <div class="text-center max-w-3xl mx-auto">
                <div class="inline-flex items-center px-4 py-2 bg-white rounded-full shadow-md mb-6">
                    <i data-lucide="phone" class="w-5 h-5 text-healsync-500 mr-2"></i>
                    <span class="text-sm font-medium text-slate-700">Contact Us</span>
                </div>
                
                <h1 class="text-display text-slate-900 mb-6">
                    Get In 
                    <span class="text-healsync-600">Touch</span>
                </h1>
                
                <p class="text-body-lg text-slate-600 mb-8">
                    Have questions about our services? Need support? We're here to help you with all your healthcare needs.
                </p>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-20 bg-white">
        <div class="container-modern">
            <div class="grid-modern grid-cols-1 lg:grid-cols-2 gap-16">
                
                <!-- Contact Information -->
                <div class="space-y-8">
                    <div>
                        <h2 class="text-h2 text-slate-900 mb-6">Contact Information</h2>
                        <p class="text-body text-slate-600 mb-8">
                            Reach out to us through any of the following channels. Our team is available 24/7 for emergency situations.
                        </p>
                    </div>
                    
                    <!-- Contact Cards -->
                    <div class="space-y-6">
                        <!-- Phone -->
                        <div class="card-modern">
                            <div class="card-body">
                                <div class="flex items-start space-x-4">
                                    <div class="w-12 h-12 bg-healsync-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <i data-lucide="phone" class="w-6 h-6 text-healsync-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-slate-900 mb-2">Phone</h4>
                                        <p class="text-slate-600 mb-2">24/7 Emergency Hotline</p>
                                        <p class="text-healsync-600 font-semibold">+1 (555) 123-4567</p>
                                        <p class="text-slate-500 text-sm">General Inquiries: +1 (555) 123-4568</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Email -->
                        <div class="card-modern">
                            <div class="card-body">
                                <div class="flex items-start space-x-4">
                                    <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <i data-lucide="mail" class="w-6 h-6 text-emerald-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-slate-900 mb-2">Email</h4>
                                        <p class="text-slate-600 mb-2">Send us your inquiries</p>
                                        <p class="text-emerald-600 font-semibold">info@healsync.com</p>
                                        <p class="text-slate-500 text-sm">Support: support@healsync.com</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Address -->
                        <div class="card-modern">
                            <div class="card-body">
                                <div class="flex items-start space-x-4">
                                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <i data-lucide="map-pin" class="w-6 h-6 text-purple-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-slate-900 mb-2">Address</h4>
                                        <p class="text-slate-600 mb-2">Visit our main facility</p>
                                        <p class="text-purple-600 font-semibold">123 Healthcare Street</p>
                                        <p class="text-slate-500 text-sm">Medical City, MC 12345</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hours -->
                        <div class="card-modern">
                            <div class="card-body">
                                <div class="flex items-start space-x-4">
                                    <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <i data-lucide="clock" class="w-6 h-6 text-amber-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-slate-900 mb-2">Operating Hours</h4>
                                        <div class="space-y-1 text-sm">
                                            <p class="text-slate-600">Monday - Friday: 8:00 AM - 8:00 PM</p>
                                            <p class="text-slate-600">Saturday: 9:00 AM - 6:00 PM</p>
                                            <p class="text-slate-600">Sunday: 10:00 AM - 4:00 PM</p>
                                            <p class="text-red-600 font-semibold">Emergency: 24/7</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Form -->
                <div class="card-modern">
                    <div class="card-header">
                        <h3 class="text-h3 text-slate-900">Send us a Message</h3>
                        <p class="text-slate-600">Fill out the form below and we'll get back to you within 24 hours.</p>
                    </div>
                    <div class="card-body">
                        <form role="form" id="contact_form" class="form-modern" method="post" onSubmit="return validateContactForm()">
                            <div class="grid-modern grid-cols-1 md:grid-cols-2">
                                <!-- Name -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Full Name *</label>
                                    <input type="text" class="form-input-modern" name="name" id="name" placeholder="Enter your full name" required>
                                </div>
                                
                                <!-- Email -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Email Address *</label>
                                    <input type="email" class="form-input-modern" name="email" id="email" placeholder="Enter your email" required>
                                </div>
                                
                                <!-- Phone -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Phone Number</label>
                                    <input type="tel" class="form-input-modern" name="phone" id="phone" placeholder="Enter your phone number">
                                </div>
                                
                                <!-- Department -->
                                <div class="form-group-modern">
                                    <label class="form-label-modern">Department</label>
                                    <select class="form-input-modern" name="department" id="department">
                                        <option value="">Select Department</option>
                                        <option value="General Inquiry">General Inquiry</option>
                                        <option value="Appointments">Appointments</option>
                                        <option value="Emergency">Emergency</option>
                                        <option value="Billing">Billing</option>
                                        <option value="Technical Support">Technical Support</option>
                                    </select>
                                </div>
                                
                                <!-- Subject -->
                                <div class="md:col-span-2 form-group-modern">
                                    <label class="form-label-modern">Subject</label>
                                    <input type="text" class="form-input-modern" name="subject" id="subject" placeholder="Brief subject of your message">
                                </div>
                                
                                <!-- Message -->
                                <div class="md:col-span-2 form-group-modern">
                                    <label class="form-label-modern">Message *</label>
                                    <textarea class="form-input-modern" name="message" id="message" rows="5" placeholder="Enter your message here..." required></textarea>
                                </div>
                            </div>
                            
                            <!-- Submit Button -->
                            <div class="flex justify-center mt-6">
                                <button type="submit" name="submit" class="btn-modern btn-primary px-8">
                                    <i data-lucide="send" class="w-5 h-5"></i>
                                    Send Message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-20 bg-slate-50">
        <div class="container-modern">
            <div class="text-center mb-16">
                <h2 class="text-h2 text-slate-900 mb-4">Frequently Asked Questions</h2>
                <p class="text-body text-slate-600 max-w-2xl mx-auto">
                    Quick answers to common questions about our services and processes
                </p>
            </div>
            
            <div class="max-w-3xl mx-auto space-y-4">
                <!-- FAQ Item 1 -->
                <div class="card-modern">
                    <div class="card-body">
                        <button class="faq-toggle w-full text-left flex items-center justify-between" onclick="toggleFAQ(1)">
                            <h4 class="font-semibold text-slate-900">How do I book an appointment?</h4>
                            <i data-lucide="chevron-down" class="w-5 h-5 text-slate-500 transition-transform" id="faq-icon-1"></i>
                        </button>
                        <div class="faq-content hidden mt-4" id="faq-content-1">
                            <p class="text-slate-600">You can book an appointment through our online booking system, by calling our hotline, or by visiting our facility in person. Our online system is available 24/7 for your convenience.</p>
                        </div>
                    </div>
                </div>
                
                <!-- FAQ Item 2 -->
                <div class="card-modern">
                    <div class="card-body">
                        <button class="faq-toggle w-full text-left flex items-center justify-between" onclick="toggleFAQ(2)">
                            <h4 class="font-semibold text-slate-900">What should I bring to my appointment?</h4>
                            <i data-lucide="chevron-down" class="w-5 h-5 text-slate-500 transition-transform" id="faq-icon-2"></i>
                        </button>
                        <div class="faq-content hidden mt-4" id="faq-content-2">
                            <p class="text-slate-600">Please bring a valid ID, insurance card, list of current medications, and any relevant medical records or test results. Arrive 15 minutes early for check-in.</p>
                        </div>
                    </div>
                </div>
                
                <!-- FAQ Item 3 -->
                <div class="card-modern">
                    <div class="card-body">
                        <button class="faq-toggle w-full text-left flex items-center justify-between" onclick="toggleFAQ(3)">
                            <h4 class="font-semibold text-slate-900">Do you accept insurance?</h4>
                            <i data-lucide="chevron-down" class="w-5 h-5 text-slate-500 transition-transform" id="faq-icon-3"></i>
                        </button>
                        <div class="faq-content hidden mt-4" id="faq-content-3">
                            <p class="text-slate-600">Yes, we accept most major insurance plans. Please contact our billing department or check with your insurance provider to verify coverage for specific services.</p>
                        </div>
                    </div>
                </div>
                
                <!-- FAQ Item 4 -->
                <div class="card-modern">
                    <div class="card-body">
                        <button class="faq-toggle w-full text-left flex items-center justify-between" onclick="toggleFAQ(4)">
                            <h4 class="font-semibold text-slate-900">How can I access my medical records?</h4>
                            <i data-lucide="chevron-down" class="w-5 h-5 text-slate-500 transition-transform" id="faq-icon-4"></i>
                        </button>
                        <div class="faq-content hidden mt-4" id="faq-content-4">
                            <p class="text-slate-600">You can access your medical records through our patient portal after logging in with your credentials. You can also request copies by contacting our medical records department.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
  </main>
<?php include 'modern-footer.php';?>

<script>
// Initialize Lucide icons
lucide.createIcons();

// Contact form validation
function validateContactForm() {
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const message = document.getElementById('message').value.trim();
    
    if (name === '') {
        Swal.fire({
            icon: 'error',
            title: 'Name Required',
            text: 'Please enter your full name.',
            confirmButtonColor: '#0ea5e9'
        });
        return false;
    }
    
    if (email === '') {
        Swal.fire({
            icon: 'error',
            title: 'Email Required',
            text: 'Please enter your email address.',
            confirmButtonColor: '#0ea5e9'
        });
        return false;
    }
    
    if (message === '') {
        Swal.fire({
            icon: 'error',
            title: 'Message Required',
            text: 'Please enter your message.',
            confirmButtonColor: '#0ea5e9'
        });
        return false;
    }
    
    // Show success message
    Swal.fire({
        icon: 'success',
        title: 'Message Sent!',
        text: 'Thank you for contacting us. We will get back to you within 24 hours.',
        confirmButtonColor: '#0ea5e9'
    });
    
    return true;
}

// FAQ Toggle functionality
function toggleFAQ(index) {
    const content = document.getElementById(`faq-content-${index}`);
    const icon = document.getElementById(`faq-icon-${index}`);
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.classList.add('hidden');
        icon.style.transform = 'rotate(0deg)';
    }
}

// Enhanced form interactions
document.querySelectorAll('.form-input-modern').forEach(input => {
    input.addEventListener('focus', function() {
        this.parentElement.classList.add('focused');
    });
    
    input.addEventListener('blur', function() {
        this.parentElement.classList.remove('focused');
    });
    
    input.addEventListener('input', function() {
        if (this.value.trim()) {
            this.classList.add('border-healsync-500/50');
        } else {
            this.classList.remove('border-healsync-500/50');
        }
    });
});

// Smooth scrolling for internal links
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
</script>