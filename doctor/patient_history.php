<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('doctor');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$errors = [];
$patients = [];
$patient = null;
$history = [];

if (isset($_GET['query'])) {
    $query = trim($_GET['query']);
    if ($query !== '') {
        // Fetch all matching patients
        $stmt = $pdo->prepare("SELECT id, name, email, phone, dob, gender, address 
                               FROM users 
                               WHERE role='patient' AND (name LIKE :q OR email LIKE :q)");
        $stmt->execute(['q' => "%$query%"]);
        $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // If doctor selected a patient_id from dropdown
        if (isset($_GET['patient_id'])) {
            $pid = intval($_GET['patient_id']);
            $patient = array_values(array_filter($patients, fn($p) => $p['id'] == $pid))[0] ?? null;

            if ($patient) {
                // Appointments
                $stmt = $pdo->prepare("SELECT id, date_time, reason, status FROM appointments WHERE patient_id=:pid");
                $stmt->execute(['pid' => $pid]);
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $a) {
                    $history[] = [
                        'type' => 'appointment',
                        'date' => $a['date_time'],
                        'title' => "Appointment",
                        'details' => "Reason: {$a['reason']}<br>Status: ".ucfirst($a['status'])
                    ];
                }

                // Prescriptions
                $stmt = $pdo->prepare("SELECT p.*, a.date_time 
                                       FROM prescriptions p 
                                       JOIN appointments a ON p.appointment_id=a.id 
                                       WHERE a.patient_id=:pid");
                $stmt->execute(['pid' => $pid]);
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $p) {
                    $history[] = [
                        'type' => 'prescription',
                        'date' => $p['date_time'],
                        'title' => "Prescription: {$p['medicine']}",
                        'details' => "{$p['dosage']} - {$p['duration']}<br>Instructions: {$p['instructions']}"
                    ];
                }

                // Treatments
                $stmt = $pdo->prepare("SELECT t.*, a.date_time 
                                       FROM treatments t 
                                       JOIN appointments a ON t.appointment_id=a.id 
                                       WHERE a.patient_id=:pid");
                $stmt->execute(['pid' => $pid]);
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $t) {
                    $history[] = [
                        'type' => 'treatment',
                        'date' => $t['date'],
                        'title' => "Treatment: {$t['treatment_name']}",
                        'details' => "Cost: $" . number_format($t['cost'],2) . "<br>Notes: {$t['notes']}"
                    ];
                }

                // Sort by newest first
                usort($history, fn($a,$b) => strtotime($b['date']) <=> strtotime($a['date']));
            }
        }
    } else {
        $errors[] = "Enter a patient name or email.";
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Patient History - Doctor</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    .timeline { position: relative; margin-left: 1.5rem; padding-left: 1.5rem; border-left: 2px solid #e5e7eb; }
    .timeline-item { position: relative; margin-bottom: 2rem; }
    .timeline-icon { position: absolute; left: -2.1rem; top: 0; background: white; border: 2px solid #6366f1; border-radius: 9999px; padding: 0.4rem; }
  </style>
</head>
<body class="bg-gray-100">
  <?php include __DIR__ . '/../includes/sidebar_doctor.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300">
    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
      <i data-lucide="activity" class="w-6 h-6 text-indigo-600"></i>
      Patient History Viewer
    </h2>

    <!-- Search -->
    <form method="get" class="flex gap-2 mb-6">
      <input type="text" name="query" placeholder="Enter patient name or email" value="<?=e($_GET['query'] ?? '')?>" class="flex-grow border rounded px-3 py-2">
      <button class="px-4 py-2 bg-indigo-600 text-white rounded flex items-center gap-2">
        <i data-lucide="search" class="w-4 h-4"></i> Search
      </button>
    </form>

    <?php if ($errors): ?>
      <div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-3">
        <?=implode('<br>', array_map('htmlspecialchars',$errors))?>
      </div>
    <?php endif; ?>

    <?php if ($patients && !$patient): ?>
      <!-- Multiple patients found -->
      <div class="bg-white p-6 rounded shadow mb-6">
        <h3 class="text-lg font-semibold mb-3">Select a Patient</h3>
        <form method="get" class="space-y-3">
          <input type="hidden" name="query" value="<?=e($_GET['query'])?>">
          <select name="patient_id" class="w-full border rounded p-2">
            <?php foreach ($patients as $p): ?>
              <option value="<?=e($p['id'])?>"><?=e($p['name'])?> (<?=e($p['email'])?>)</option>
            <?php endforeach; ?>
          </select>
          <button class="px-4 py-2 bg-indigo-600 text-white rounded">View History</button>
        </form>
      </div>
    <?php endif; ?>

    <?php if ($patient): ?>
      <!-- Patient Info -->
      <div class="bg-white p-6 rounded shadow mb-6">
        <h3 class="text-lg font-semibold mb-3 flex items-center gap-2">
          <i data-lucide="user" class="w-5 h-5 text-indigo-600"></i>
          <?=e($patient['name'])?> (<?=e($patient['email'])?>)
        </h3>
        <p><strong>Phone:</strong> <?=e($patient['phone'])?></p>
        <p><strong>DOB:</strong> <?=e($patient['dob'])?></p>
        <p><strong>Gender:</strong> <?=e($patient['gender'])?></p>
        <p><strong>Address:</strong> <?=e($patient['address'])?></p>
      </div>

      <!-- Timeline -->
      <div class="bg-white p-6 rounded shadow">
        <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
          <i data-lucide="clock" class="w-5 h-5 text-indigo-600"></i>
          Medical History Timeline
        </h3>
        <?php if ($history): ?>
          <div class="timeline">
            <?php foreach ($history as $item): ?>
              <div class="timeline-item">
                <div class="timeline-icon">
                  <?php if ($item['type']==='appointment'): ?>
                    <i data-lucide="calendar" class="w-4 h-4 text-indigo-600"></i>
                  <?php elseif ($item['type']==='prescription'): ?>
                    <i data-lucide="pill" class="w-4 h-4 text-green-600"></i>
                  <?php elseif ($item['type']==='treatment'): ?>
                    <i data-lucide="stethoscope" class="w-4 h-4 text-red-600"></i>
                  <?php endif; ?>
                </div>
                <div class="ml-6">
                  <p class="text-sm text-gray-500"><?=date('d M Y, H:i', strtotime($item['date']))?></p>
                  <h4 class="font-semibold"><?=$item['title']?></h4>
                  <p class="text-gray-700 text-sm"><?=$item['details']?></p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="text-gray-500">No medical history found.</p>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </main>

  <script> lucide.createIcons(); </script>
</body>
</html>
