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
<html>
<head>
  <meta charset="utf-8">
  <title>Book Appointment - Healsync</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>
<body class="bg-gray-100">
  <?php include __DIR__ . '/../includes/sidebar_patient.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 max-w-2xl mx-auto">
    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
      <i data-lucide="calendar-plus" class="w-6 h-6 text-indigo-600"></i>
      Book Appointment
    </h2>

    <?= $message ?>

    <form method="post" class="bg-white p-6 rounded-xl shadow space-y-4">
      <input type="hidden" name="csrf" value="<?= csrf() ?>">

      <!-- Doctor -->
      <div>
        <label class="block mb-1 font-medium">Doctor</label>
        <select name="doctor_id" class="w-full border rounded-lg p-2 focus:ring-indigo-500" required>
          <option value="">-- Select Doctor --</option>
          <?php foreach ($doctors as $d): ?>
            <option value="<?= e($d['id']) ?>">
              <?= e($d['name']) ?> (<?= e($d['specialty']) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Date & Time -->
      <div>
        <label class="block mb-1 font-medium">Date & Time</label>
        <input type="text" name="date_time" id="date_time"
               class="w-full border rounded-lg p-2 focus:ring-indigo-500"
               placeholder="Select date and time" required>
      </div>

      <!-- Reason -->
      <div>
        <label class="block mb-1 font-medium">Reason</label>
        <textarea name="reason" rows="3"
                  class="w-full border rounded-lg p-2 focus:ring-indigo-500"
                  placeholder="Enter the reason for visit" required></textarea>
      </div>

      <button type="submit"
        class="px-4 py-2 bg-indigo-600 text-white rounded-lg flex items-center gap-2 hover:bg-indigo-700">
        <i data-lucide="check-circle" class="w-5 h-5"></i>
        Submit
      </button>
    </form>
  </main>

  <script>
    lucide.createIcons();
    flatpickr("#date_time", {
      enableTime: true,
      dateFormat: "Y-m-d H:i",
      minDate: "today"
    });
  </script>
</body>
</html>
