<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('doctor');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$did = current_user_id();
$message = "";

// Fetch approved appointments for this doctor
$stmt = $pdo->prepare("
    SELECT a.id, a.date_time, u.name AS patient_name
    FROM appointments a
    JOIN users u ON a.patient_id = u.id
    WHERE a.doctor_id = :did AND a.status='approved'
    ORDER BY a.date_time DESC
");
$stmt->execute([':did'=>$did]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_id = intval($_POST['appointment_id'] ?? 0);
    $medicine = trim($_POST['medicine'] ?? '');
    $dosage = trim($_POST['dosage'] ?? '');
    $duration = trim($_POST['duration'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');

    if ($appointment_id && $medicine && $dosage && $duration) {
        // Verify appointment belongs to doctor
        $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id=:id AND doctor_id=:did AND status='approved'");
        $stmt->execute([':id'=>$appointment_id, ':did'=>$did]);
        $appt = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($appt) {
            // Insert prescription
            $stmt = $pdo->prepare("INSERT INTO prescriptions (appointment_id, medicine, dosage, duration, instructions)
                                   VALUES (:aid, :med, :dos, :dur, :ins)");
            $stmt->execute([
                ':aid'=>$appointment_id,
                ':med'=>$medicine,
                ':dos'=>$dosage,
                ':dur'=>$duration,
                ':ins'=>$instructions
            ]);

            $message = "<p class='text-green-600'>Prescription added successfully.</p>";
        } else {
            $message = "<p class='text-red-600'>Invalid appointment.</p>";
        }
    } else {
        $message = "<p class='text-red-600'>All required fields must be filled.</p>";
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Add Prescription - Healsync</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-100">
  <?php include __DIR__ . '/../includes/sidebar_doctor.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300 max-w-2xl mx-auto">
    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
      <i data-lucide="pill" class="w-6 h-6 text-indigo-600"></i>
      Add Prescription
    </h2>

    <?= $message ?>

    <form method="post" class="bg-white p-6 rounded shadow space-y-4">
      <div>
        <label class="block mb-1 font-medium">Appointment *</label>
        <select name="appointment_id" class="w-full border rounded p-2" required>
          <option value="">-- Select Appointment --</option>
          <?php foreach ($appointments as $a): ?>
            <option value="<?=e($a['id'])?>">
              <?=date('d M Y H:i', strtotime($a['date_time']))?> - <?=e($a['patient_name'])?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="block mb-1 font-medium">Medicine *</label>
        <input type="text" name="medicine" class="w-full border rounded p-2" required>
      </div>

      <div>
        <label class="block mb-1 font-medium">Dosage *</label>
        <input type="text" name="dosage" placeholder="e.g. 500mg twice daily" class="w-full border rounded p-2" required>
      </div>

      <div>
        <label class="block mb-1 font-medium">Duration *</label>
        <input type="text" name="duration" placeholder="e.g. 5 days" class="w-full border rounded p-2" required>
      </div>

      <div>
        <label class="block mb-1 font-medium">Instructions</label>
        <textarea name="instructions" class="w-full border rounded p-2" rows="3" placeholder="e.g. Take after meals"></textarea>
      </div>

      <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded flex items-center gap-2 hover:bg-indigo-700">
        <i data-lucide="check-circle" class="w-5 h-5"></i> Save Prescription
      </button>
    </form>
  </main>

  <script> lucide.createIcons(); </script>
</body>
</html>
