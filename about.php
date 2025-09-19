<?php include ('modern-header.php');
include('dbconnection.php');
?>

<!-- Main Content -->
<main class="pt-24 min-h-screen">
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-healsync-50 to-cyan-50 py-16">
        <div class="container-modern">
            <div class="text-center max-w-3xl mx-auto">
                <div class="inline-flex items-center px-4 py-2 bg-white rounded-full shadow-md mb-6">
                    <i data-lucide="info" class="w-5 h-5 text-healsync-500 mr-2"></i>
                    <span class="text-sm font-medium text-slate-700">About HealSync</span>
                </div>
                
                <h1 class="text-display text-slate-900 mb-6">
                    Modern Healthcare 
                    <span class="text-healsync-600">Management</span> 
                    System
                </h1>
                
                <p class="text-body-lg text-slate-600 mb-8">
                    HealSync is a comprehensive healthcare management platform designed to streamline hospital operations, 
                    enhance patient care, and provide seamless digital healthcare experiences.
                </p>
            </div>
        </div>
    </section>

    <!-- About Content Section -->
    <section class="py-20 bg-white">
        <div class="container-modern">
            <div class="grid-modern grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                
                <!-- Content -->
                <div class="space-y-8">
                    <div>
                        <h2 class="text-h2 text-slate-900 mb-6">
                            Comprehensive Healthcare Solutions
                        </h2>
                        <p class="text-body-lg text-slate-600 leading-relaxed mb-8">
                            HealSync provides cutting-edge healthcare management with advanced features designed 
                            for modern medical facilities, ensuring efficient operations and exceptional patient care.
                        </p>
                    </div>
                    
                    <!-- Features Grid -->
                    <div class="grid-modern grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <i data-lucide="zap" class="w-6 h-6 text-red-600"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-slate-900 mb-2">Emergency Care</h4>
                                <p class="text-slate-600 text-sm">24/7 emergency response system with instant alerts and rapid deployment for critical situations.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 bg-healsync-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <i data-lucide="user-check" class="w-6 h-6 text-healsync-600"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-slate-900 mb-2">Qualified Doctors</h4>
                                <p class="text-slate-600 text-sm">Board-certified specialists with extensive experience and modern training in their respective fields.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <i data-lucide="calendar-check" class="w-6 h-6 text-emerald-600"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-slate-900 mb-2">Online Appointments</h4>
                                <p class="text-slate-600 text-sm">Easy online booking system with real-time availability, confirmations, and appointment management.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <i data-lucide="smartphone" class="w-6 h-6 text-purple-600"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-slate-900 mb-2">Digital Services</h4>
                                <p class="text-slate-600 text-sm">Complete digital healthcare ecosystem accessible from any device, anywhere, anytime.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- CTA Button -->
                    <div class="pt-6">
                        <a href="modern-appointment.php" class="btn-modern btn-primary">
                            <i data-lucide="calendar-plus" class="w-5 h-5"></i>
                            Book Appointment Now
                        </a>
                    </div>
                </div>
                
                <!-- Image -->
                <div class="relative">
                    <div class="card-modern overflow-hidden">
                        <img src="images/intro-img.jpg" alt="HealSync Healthcare" class="w-full h-auto object-cover">
                    </div>
                    <!-- Floating Stats -->
                    <div class="absolute -bottom-6 -left-6 bg-white rounded-2xl shadow-xl p-6 border border-slate-200">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-healsync-100 rounded-xl flex items-center justify-center">
                                <i data-lucide="users" class="w-6 h-6 text-healsync-600"></i>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900">10,000+</p>
                                <p class="text-sm text-slate-600">Patients Served</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="absolute -top-6 -right-6 bg-white rounded-2xl shadow-xl p-6 border border-slate-200">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                                <i data-lucide="stethoscope" class="w-6 h-6 text-emerald-600"></i>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900">50+</p>
                                <p class="text-sm text-slate-600">Expert Doctors</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Vision Section -->
    <section class="py-20 bg-slate-50">
        <div class="container-modern">
            <div class="text-center mb-16">
                <h2 class="text-h2 text-slate-900 mb-4">Our Mission & Vision</h2>
                <p class="text-body text-slate-600 max-w-2xl mx-auto">
                    Committed to transforming healthcare through technology and compassionate care
                </p>
            </div>
            
            <div class="grid-modern grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Mission -->
                <div class="card-modern text-center">
                    <div class="card-body">
                        <div class="w-16 h-16 bg-healsync-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                            <i data-lucide="target" class="w-8 h-8 text-healsync-600"></i>
                        </div>
                        <h3 class="text-h3 text-slate-900 mb-4">Our Mission</h3>
                        <p class="text-slate-600 leading-relaxed">
                            To provide accessible, efficient, and compassionate healthcare services through innovative 
                            technology solutions that connect patients, healthcare providers, and administrators in a 
                            seamless digital ecosystem.
                        </p>
                    </div>
                </div>
                
                <!-- Vision -->
                <div class="card-modern text-center">
                    <div class="card-body">
                        <div class="w-16 h-16 bg-purple-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                            <i data-lucide="eye" class="w-8 h-8 text-purple-600"></i>
                        </div>
                        <h3 class="text-h3 text-slate-900 mb-4">Our Vision</h3>
                        <p class="text-slate-600 leading-relaxed">
                            To be the leading healthcare management platform that revolutionizes patient care delivery, 
                            making quality healthcare accessible to everyone while empowering healthcare professionals 
                            with cutting-edge tools.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="py-20 bg-white">
        <div class="container-modern">
            <div class="text-center mb-16">
                <h2 class="text-h2 text-slate-900 mb-4">Our Core Values</h2>
                <p class="text-body text-slate-600 max-w-2xl mx-auto">
                    The principles that guide everything we do at HealSync
                </p>
            </div>
            
            <div class="grid-modern grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Excellence -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-amber-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <i data-lucide="award" class="w-8 h-8 text-amber-600"></i>
                    </div>
                    <h3 class="text-h3 text-slate-900 mb-4">Excellence</h3>
                    <p class="text-slate-600">
                        We strive for excellence in every aspect of healthcare delivery, from patient care to technological innovation.
                    </p>
                </div>
                
                <!-- Compassion -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-pink-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <i data-lucide="heart" class="w-8 h-8 text-pink-600"></i>
                    </div>
                    <h3 class="text-h3 text-slate-900 mb-4">Compassion</h3>
                    <p class="text-slate-600">
                        Every interaction is guided by empathy and understanding, ensuring patients feel cared for and supported.
                    </p>
                </div>
                
                <!-- Innovation -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-cyan-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <i data-lucide="lightbulb" class="w-8 h-8 text-cyan-600"></i>
                    </div>
                    <h3 class="text-h3 text-slate-900 mb-4">Innovation</h3>
                    <p class="text-slate-600">
                        We continuously embrace new technologies and methodologies to improve healthcare outcomes and experiences.
                    </p>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'modern-footer.php';?>

<script>
// Initialize Lucide icons
lucide.createIcons();
</script>