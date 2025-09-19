
  <?php include 'modern-header.php';?>
  
  <!-- Modern Hero Section -->
  <section class="relative min-h-screen flex items-center justify-center overflow-hidden">
    <!-- Background with Overlay -->
    <div class="absolute inset-0 z-0">
      <img src="images/hmsab.jpg" alt="HealSync Hospital" class="w-full h-full object-cover">
      <div class="absolute inset-0 bg-gradient-to-r from-slate-900/80 via-slate-900/60 to-transparent"></div>
    </div>
    
    <!-- Hero Content -->
    <div class="relative z-10 container-modern text-center text-white px-6">
      <div class="max-w-4xl mx-auto">
        <!-- Main Heading -->
        <h1 class="text-5xl md:text-7xl font-bold mb-6 fade-in">
          <span class="bg-gradient-to-r from-white to-slate-300 bg-clip-text text-transparent">
            HealSync
          </span>
        </h1>
        
        <!-- Subtitle -->
        <h2 class="text-2xl md:text-4xl font-light mb-8 text-slate-200 slide-up">
          Modern Hospital Management System
        </h2>
        
        <!-- Description -->
        <p class="text-lg md:text-xl text-slate-300 mb-12 max-w-2xl mx-auto leading-relaxed slide-up">
          Experience the future of healthcare management with our comprehensive, user-friendly platform designed for patients, doctors, and administrators.
        </p>
        
        <!-- CTA Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center slide-up">
          <a href="patientappointment.php" class="btn-modern btn-primary px-8 py-4 text-lg hover-lift">
            <i data-lucide="calendar-plus" class="w-5 h-5"></i>
            Book Appointment
          </a>
          <a href="about.php" class="btn-modern btn-secondary px-8 py-4 text-lg hover-lift bg-white/10 backdrop-blur-sm border-white/20 text-white hover:bg-white/20">
            <i data-lucide="info" class="w-5 h-5"></i>
            Learn More
          </a>
        </div>
        
        <!-- Features Grid -->
         <a href="patientlogin.php">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-16 slide-up">
          <div class="glass rounded-2xl p-6 text-center hover-lift">
            <div class="w-16 h-16 bg-primary/20 rounded-full flex items-center justify-center mx-auto mb-4">
              <i data-lucide="user-check" class="w-8 h-8 text-primary"></i>
            </div>

            <h3 class="text-xl font-semibold mb-2">Patient Care</h3>
            <p class="text-slate-300">Comprehensive patient management and care coordination</p>
          </div>
          </a>
          
          <a href="doctorlogin.php">
          <div class="glass rounded-2xl p-6 text-center hover-lift">
            <div class="w-16 h-16 bg-secondary/20 rounded-full flex items-center justify-center mx-auto mb-4">
              <i data-lucide="stethoscope" class="w-8 h-8 text-secondary"></i>
            </div>
            <h3 class="text-xl font-semibold mb-2">Doctor Portal</h3>
            <p class="text-slate-300">Advanced tools for medical professionals and specialists</p>
          </div>
          </a>
          
          <a href="adminlogin.php">
          <div class="glass rounded-2xl p-6 text-center hover-lift">
            <div class="w-16 h-16 bg-accent/20 rounded-full flex items-center justify-center mx-auto mb-4">
              <i data-lucide="shield-check" class="w-8 h-8 text-accent"></i>
            </div>
            <h3 class="text-xl font-semibold mb-2">Admin Control</h3>
            <p class="text-slate-300">Complete hospital operations and management system</p>
          </div>
          </a>
        </div>
      </div>
    </div>
    
    <!-- Scroll Indicator -->
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 text-white animate-bounce">
      <i data-lucide="chevron-down" class="w-6 h-6"></i>
    </div>
  </section>

  <!-- Modern Features Section -->
  <section class="py-20 bg-white">
    <div class="container-modern">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
        <!-- Content -->
        <div class="space-y-8">
          <div>
            <h2 class="text-4xl font-bold text-slate-900 mb-6">
              Comprehensive Healthcare Solutions
            </h2>
            <p class="text-lg text-slate-600 leading-relaxed">
              HealSync provides cutting-edge healthcare management with advanced features designed for modern medical facilities and patient care excellence.
            </p>
          </div>
          
          <!-- Features List -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="flex items-start space-x-4">
              <div class="w-12 h-12 bg-error/10 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="zap" class="w-6 h-6 text-error"></i>
              </div>
              <div>
                <h4 class="font-semibold text-slate-900 mb-2">Emergency Care</h4>
                <p class="text-slate-600 text-sm">24/7 emergency response system with instant alerts and rapid deployment.</p>
              </div>
            </div>
            
            <div class="flex items-start space-x-4">
              <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="user-check" class="w-6 h-6 text-primary"></i>
              </div>
              <div>
                <h4 class="font-semibold text-slate-900 mb-2">Qualified Doctors</h4>
                <p class="text-slate-600 text-sm">Board-certified specialists with extensive experience and modern training.</p>
              </div>
            </div>
            
            <div class="flex items-start space-x-4">
              <div class="w-12 h-12 bg-success/10 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="calendar-check" class="w-6 h-6 text-success"></i>
              </div>
              <div>
                <h4 class="font-semibold text-slate-900 mb-2">Online Appointments</h4>
                <p class="text-slate-600 text-sm">Easy online booking system with real-time availability and confirmations.</p>
              </div>
            </div>
            
            <div class="flex items-start space-x-4">
              <div class="w-12 h-12 bg-secondary/10 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="smartphone" class="w-6 h-6 text-secondary"></i>
              </div>
              <div>
                <h4 class="font-semibold text-slate-900 mb-2">Digital Services</h4>
                <p class="text-slate-600 text-sm">Complete digital healthcare ecosystem accessible from any device.</p>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Hospital Hours Card -->
        <div class="card-modern">
          <div class="card-header">
            <div class="flex items-center space-x-3">
              <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                <i data-lucide="clock" class="w-6 h-6 text-primary"></i>
              </div>
              <div>
                <h3 class="text-xl font-semibold">Hospital Hours</h3>
                <p class="text-slate-600">We're here when you need us</p>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="space-y-4">
              <div class="flex justify-between items-center py-2 border-b border-slate-100">
                <span class="font-medium text-slate-700">Monday - Friday</span>
                <span class="text-slate-600">8:00 AM - 4:00 PM</span>
              </div>
              <div class="flex justify-between items-center py-2 border-b border-slate-100">
                <span class="font-medium text-slate-700">Saturday</span>
                <span class="text-slate-600">8:00 AM - 4:00 PM</span>
              </div>
              <div class="flex justify-between items-center py-2 border-b border-slate-100">
                <span class="font-medium text-slate-700">Sunday</span>
                <span class="text-slate-600">8:00 AM - 4:00 PM</span>
              </div>
              <div class="flex justify-between items-center py-2">
                <span class="font-medium text-error">Emergency</span>
                <span class="text-error font-semibold">24/7 Available</span>
              </div>
            </div>
          </div>
          <div class="card-footer">
            <a href="patientappointment.php" class="btn-modern btn-primary w-full justify-center">
              <i data-lucide="calendar-plus" class="w-4 h-4"></i>
              Book Appointment Now
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Modern Services Section -->
  <section class="py-20 bg-slate-50">
    <div class="container-modern">
      <!-- Section Header -->
      <div class="text-center mb-16">
        <h2 class="text-4xl font-bold text-slate-900 mb-4">Our Medical Services</h2>
        <p class="text-lg text-slate-600 max-w-2xl mx-auto">
          Comprehensive healthcare services delivered by experienced professionals using state-of-the-art technology and compassionate care.
        </p>
      </div>
      
      <!-- Services Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <!-- Eye Specialist -->
        <div class="card-modern hover-lift">
          <div class="card-body text-center">
            <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center mx-auto mb-6">
              <i data-lucide="eye" class="w-8 h-8 text-primary"></i>
            </div>
            <h3 class="text-xl font-semibold mb-3">Eye Specialist</h3>
            <p class="text-slate-600 mb-6">Comprehensive eye care with advanced diagnostic equipment and experienced ophthalmologists.</p>
            <a href="#" class="btn-modern btn-secondary">Learn More</a>
          </div>
        </div>
        
        <!-- Operation Theater -->
        <div class="card-modern hover-lift">
          <div class="card-body text-center">
            <div class="w-16 h-16 bg-error/10 rounded-2xl flex items-center justify-center mx-auto mb-6">
              <i data-lucide="activity" class="w-8 h-8 text-error"></i>
            </div>
            <h3 class="text-xl font-semibold mb-3">Operation Theater</h3>
            <p class="text-slate-600 mb-6">State-of-the-art surgical facilities with modern equipment and sterile environments.</p>
            <a href="#" class="btn-modern btn-secondary">Learn More</a>
          </div>
        </div>
        
        <!-- ICU Department -->
        <div class="card-modern hover-lift">
          <div class="card-body text-center">
            <div class="w-16 h-16 bg-warning/10 rounded-2xl flex items-center justify-center mx-auto mb-6">
              <i data-lucide="monitor" class="w-8 h-8 text-warning"></i>
            </div>
            <h3 class="text-xl font-semibold mb-3">ICU Department</h3>
            <p class="text-slate-600 mb-6">Advanced intensive care unit with 24/7 monitoring and specialized medical staff.</p>
            <a href="#" class="btn-modern btn-secondary">Learn More</a>
          </div>
        </div>
        
        <!-- Gastroenterology -->
        <div class="card-modern hover-lift">
          <div class="card-body text-center">
            <div class="w-16 h-16 bg-success/10 rounded-2xl flex items-center justify-center mx-auto mb-6">
              <i data-lucide="pill" class="w-8 h-8 text-success"></i>
            </div>
            <h3 class="text-xl font-semibold mb-3">Gastroenterology</h3>
            <p class="text-slate-600 mb-6">Specialized treatment for digestive system disorders with modern diagnostic tools.</p>
            <a href="#" class="btn-modern btn-secondary">Learn More</a>
          </div>
        </div>
        
        <!-- Qualified Doctors -->
        <div class="card-modern hover-lift">
          <div class="card-body text-center">
            <div class="w-16 h-16 bg-accent/10 rounded-2xl flex items-center justify-center mx-auto mb-6">
              <i data-lucide="stethoscope" class="w-8 h-8 text-accent"></i>
            </div>
            <h3 class="text-xl font-semibold mb-3">Expert Physicians</h3>
            <p class="text-slate-600 mb-6">Board-certified doctors with years of experience in their respective specializations.</p>
            <a href="#" class="btn-modern btn-secondary">Learn More</a>
          </div>
        </div>
        
        <!-- Cardiology -->
        <div class="card-modern hover-lift">
          <div class="card-body text-center">
            <div class="w-16 h-16 bg-error/10 rounded-2xl flex items-center justify-center mx-auto mb-6">
              <i data-lucide="heart-pulse" class="w-8 h-8 text-error"></i>
            </div>
            <h3 class="text-xl font-semibold mb-3">Cardiology</h3>
            <p class="text-slate-600 mb-6">Comprehensive heart care with advanced cardiac procedures and preventive treatments.</p>
            <a href="#" class="btn-modern btn-secondary">Learn More</a>
          </div>
        </div>
      </div>
    </div>
  </section>
   
    
    
  </div>
  
  <!-- Modern Footer -->
<?php include 'modern-footer.php';?>