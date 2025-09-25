<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('doctor');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$did = current_user_id();
$message = "";

// Fetch approved appointments for this doctor
$stmt = $pdo->prepare("
    SELECT a.id, a.date_time, u.name AS patient_name, a.patient_id
    FROM appointments a
    JOIN users u ON a.patient_id = u.id
    WHERE a.doctor_id = :did AND a.status='approved'
    ORDER BY a.date_time DESC
");
$stmt->execute([':did' => $did]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_id = intval($_POST['appointment_id'] ?? 0);
    $treatment_name = trim($_POST['treatment_name'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $cost = floatval($_POST['cost'] ?? 0);

    if ($appointment_id && $treatment_name && $date && $cost > 0) {
        // Verify appointment belongs to doctor
        $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id=:id AND doctor_id=:did AND status='approved'");
        $stmt->execute([':id' => $appointment_id, ':did' => $did]);
        $appt = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($appt) {
            $pid = $appt['patient_id'];

            // Insert treatment
            $stmt = $pdo->prepare("INSERT INTO treatments (appointment_id, treatment_name, date, notes, cost)
                                   VALUES (:aid, :tname, :tdate, :notes, :cost)");
            $stmt->execute([
                ':aid'   => $appointment_id,
                ':tname' => $treatment_name,
                ':tdate' => $date,
                ':notes' => $notes,
                ':cost'  => $cost
            ]);

            // Update / Create bill
            $stmt = $pdo->prepare("SELECT * FROM bills WHERE patient_id=:pid AND status='unpaid'");
            $stmt->execute([':pid' => $pid]);
            $bill = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($bill) {
                $pdo->prepare("UPDATE bills SET total_amount=total_amount+:c WHERE id=:id")
                    ->execute([':c' => $cost, ':id' => $bill['id']]);
            } else {
                $pdo->prepare("INSERT INTO bills (patient_id,total_amount,status,created_at)
                               VALUES (:pid,:c,'unpaid',NOW())")
                    ->execute([':pid' => $pid, ':c' => $cost]);
            }

            // ✅ Notify patient
            $msg = "A new bill has been generated for your treatment on " . date('d M Y');
            $link = "/healsync/patient/bills.php";
            $pdo->prepare("INSERT INTO notifications (user_id,message,link) VALUES (:uid,:msg,:link)")
                ->execute([':uid' => $pid, ':msg' => $msg, ':link' => $link]);

            $message = "<p class='text-green-600'>Treatment added and bill updated successfully.</p>";
        } else {
            $message = "<p class='text-red-600'>Invalid appointment.</p>";
        }
    } else {
        $message = "<p class='text-red-600'>All required fields must be filled, and cost must be greater than 0.</p>";
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Add Treatment - Healsync</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>
<body class="bg-gray-100">
  <?php include __DIR__ . '/../includes/sidebar_doctor.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 max-w-2xl mx-auto">
    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
      <i data-lucide="activity" class="w-6 h-6 text-indigo-600"></i>
      Add Treatment
    </h2>

    <?= $message ?>

    <form method="post" class="bg-white p-6 rounded shadow space-y-4">
      <div>
        <label class="block mb-1 font-medium">Appointment *</label>
        <select name="appointment_id" class="w-full border rounded p-2" required>
          <option value="">-- Select Appointment --</option>
          <?php foreach ($appointments as $a): ?>
            <option value="<?= e($a['id']) ?>">
              <?= date('d M Y H:i', strtotime($a['date_time'])) ?> - <?= e($a['patient_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="block mb-1 font-medium">Treatment Name *</label>
        <input type="text" name="treatment_name" class="w-full border rounded p-2" required>
      </div>

      <div>
        <label class="block mb-1 font-medium">Date *</label>
        <input type="text" name="date" id="treat_date" class="w-full border rounded p-2" required>
      </div>

      <div>
        <label class="block mb-1 font-medium">Notes</label>
        <textarea name="notes" class="w-full border rounded p-2" rows="3"></textarea>
      </div>

      <div>
        <label class="block mb-1 font-medium">Cost ($) *</label>
        <input type="number" step="0.01" name="cost" class="w-full border rounded p-2" required>
      </div>

      <button type="submit"
        class="px-4 py-2 bg-indigo-600 text-white rounded flex items-center gap-2 hover:bg-indigo-700">
        <i data-lucide="check-circle" class="w-5 h-5"></i> Save Treatment
      </button>
    </form>
  </main>

  <script>
    lucide.createIcons();
    flatpickr("#treat_date", { dateFormat: "Y-m-d" });
  </script>
</body>
</html>
