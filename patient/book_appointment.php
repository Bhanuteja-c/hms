<?php
// patient/book_appointment.php
require_once __DIR__ . '/../includes/auth.php';
require_role('patient');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$pid = current_user_id();
$message = "";

// Fetch doctors
$stmt = $pdo->query("
  SELECT u.id, u.name, d.specialty
  FROM users u
  JOIN doctors d ON u.id = d.id
  ORDER BY u.name
");
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $message = "<div class='bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-4'>Invalid CSRF token.</div>";
    } else {
        $doctor_id = intval($_POST['doctor_id'] ?? 0);
        $date_time = trim($_POST['date_time'] ?? '');
        $reason    = trim($_POST['reason'] ?? '');

        if ($doctor_id && $date_time && $reason) {
            // Check if slot already taken
            $check = $pdo->prepare("
              SELECT COUNT(*) 
              FROM appointments 
              WHERE doctor_id = :did AND date_time = :dt 
                AND status IN ('pending','approved')
            ");
            $check->execute([':did'=>$doctor_id, ':dt'=>$date_time]);

            if ($check->fetchColumn() > 0) {
                $message = "<div class='bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-4'>
                              This slot is already booked. Please choose another.
                            </div>";
            } else {
                // Insert appointment
                $stmt = $pdo->prepare("
                  INSERT INTO appointments 
                  (patient_id, doctor_id, date_time, reason, status, created_at)
                  VALUES (:pid, :did, :dt, :reason, 'pending', NOW())
                ");
                $stmt->execute([
                    ':pid' => $pid,
                    ':did' => $doctor_id,
                    ':dt'  => $date_time,
                    ':reason' => $reason
                ]);

                // Log action
                audit_log($pdo, $pid, 'book_appointment', json_encode([
                    'doctor_id' => $doctor_id, 
                    'date_time' => $date_time
                ]));

                // Notify doctor
                $msg  = "New appointment request from " . e($_SESSION['user']['name']) .
                        " on " . date('d M Y H:i', strtotime($date_time));
                $link = "/healsync/doctor/appointments.php";

                $pdo->prepare("INSERT INTO notifications (user_id,message,link) 
                               VALUES (:uid,:msg,:link)")
                    ->execute([':uid'=>$doctor_id, ':msg'=>$msg, ':link'=>$link]);

                $message = "<div class='bg-green-50 border border-green-200 text-green-700 p-3 rounded mb-4'>
                              Appointment request submitted! Pending doctor approval.
                            </div>";
            }
        } else {
            $message = "<div class='bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-4'>
                          All fields are required.
                        </div>";
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Book Appointment - Healsync</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <style>
    .glass-effect {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    .gradient-bg {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .premium-shadow {
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    .floating-label {
      transition: all 0.2s ease-in-out;
    }
    .floating-label.active {
      transform: translateY(-1.5rem) scale(0.85);
      color: #6366f1;
    }
    .flatpickr-calendar {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 12px;
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    }
  </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 min-h-screen">
  <?php include __DIR__ . '/../includes/sidebar_patient.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300">
    <!-- Header Section -->
    <div class="mb-8">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
            Book Appointment
          </h1>
          <p class="text-gray-600 mt-1">Schedule your visit with our healthcare professionals</p>
        </div>
        <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
          <i data-lucide="calendar-plus" class="w-6 h-6 text-white"></i>
        </div>
      </div>
    </div>

    <?= $message ?>

    <!-- Appointment Form -->
    <div class="glass-effect rounded-2xl premium-shadow p-8 max-w-2xl mx-auto">
      <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-2">Appointment Details</h2>
        <p class="text-gray-600 text-sm">Please fill in all the required information to book your appointment</p>
      </div>

      <form method="post" class="space-y-6">
        <input type="hidden" name="csrf" value="<?= csrf() ?>">

        <!-- Doctor Selection -->
        <div class="relative">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            <i data-lucide="user-check" class="w-4 h-4 inline mr-1"></i>
            Select Doctor
          </label>
          <select name="doctor_id" 
                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 bg-white/80 backdrop-blur-sm" 
                  required>
            <option value="">Choose your preferred doctor</option>
            <?php foreach ($doctors as $d): ?>
              <option value="<?= e($d['id']) ?>" class="py-2">
                Dr. <?= e($d['name']) ?> - <?= e($d['specialty']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Date & Time Selection -->
        <div class="relative">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            <i data-lucide="calendar-clock" class="w-4 h-4 inline mr-1"></i>
            Preferred Date & Time
          </label>
          <input type="text" 
                 name="date_time" 
                 id="date_time"
                 class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 bg-white/80 backdrop-blur-sm"
                 placeholder="Click to select date and time" 
                 required>
        </div>

        <!-- Reason for Visit -->
        <div class="relative">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            <i data-lucide="file-text" class="w-4 h-4 inline mr-1"></i>
            Reason for Visit
          </label>
          <textarea name="reason" 
                    rows="4"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 bg-white/80 backdrop-blur-sm resize-none"
                    placeholder="Please describe your symptoms or reason for the appointment..."
                    required></textarea>
        </div>

        <!-- Submit Button -->
        <div class="pt-4">
          <button type="submit"
                  class="w-full px-6 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-medium flex items-center justify-center gap-3 hover:from-indigo-700 hover:to-purple-700 transform hover:scale-[1.02] transition-all duration-200 premium-shadow">
            <i data-lucide="calendar-check" class="w-5 h-5"></i>
            Book Appointment
          </button>
        </div>
      </form>
    </div>
  </main>

  <script>
    lucide.createIcons();
    
    // Enhanced Flatpickr configuration
    flatpickr("#date_time", {
      enableTime: true,
      dateFormat: "Y-m-d H:i",
      minDate: "today",
      maxDate: new Date().fp_incr(30), // 30 days from today
      minuteIncrement: 15,
      time_24hr: false,
      theme: "light",
      disableMobile: false,
      locale: {
        firstDayOfWeek: 1 // Monday
      },
      disable: [
        function(date) {
          // Disable Sundays
          return date.getDay() === 0;
        }
      ],
      onReady: function(selectedDates, dateStr, instance) {
        instance.calendarContainer.classList.add('premium-calendar');
      }
    });

    // Form validation and UX enhancements
    const form = document.querySelector('form');
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;

    form.addEventListener('submit', function(e) {
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i data-lucide="loader-2" class="w-5 h-5 animate-spin"></i> Booking...';
      lucide.createIcons();
    });

    // Reset button state if form submission fails
    window.addEventListener('pageshow', function() {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalBtnText;
      lucide.createIcons();
    });
  </script>
</body>
</html>
