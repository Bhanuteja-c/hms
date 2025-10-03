<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('doctor');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$did = current_user_id();
$tab = $_GET['tab'] ?? 'all';

// Handle appointment actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['appointment_id'])) {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $message = "<p class='text-red-600 mb-4'>Invalid CSRF token.</p>";
    } else {
        $appt_id = intval($_POST['appointment_id']);
        $action = $_POST['action'];

        if (in_array($action, ['approve','reject','complete'])) {
            $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id=:id AND doctor_id=:did");
            $stmt->execute(['id'=>$appt_id, 'did'=>$did]);
            $appt = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($appt) {
                $newStatus = $action === 'approve' ? 'approved' : ($action === 'reject' ? 'rejected' : 'completed');
                
                // Only allow status changes based on current status
                if (($appt['status'] === 'pending' && in_array($action, ['approve','reject'])) ||
                    ($appt['status'] === 'approved' && $action === 'complete')) {
                    
                    $pdo->prepare("UPDATE appointments SET status=:status WHERE id=:id")
                        ->execute(['status'=>$newStatus, 'id'=>$appt_id]);

                    // Notify patient
                    $msg = "Your appointment on " . date('d M Y H:i', strtotime($appt['date_time'])) .
                           " with Dr. " . e(current_user_name()) . " has been $newStatus.";
                    $link = "/healsync/patient/appointments.php";
                    $pdo->prepare("INSERT INTO notifications (user_id, message, link) VALUES (:uid,:msg,:link)")
                        ->execute(['uid'=>$appt['patient_id'], 'msg'=>$msg, 'link'=>$link]);

                    $message = "<p class='text-green-600 mb-4'>Appointment " . ucfirst($newStatus) . " successfully.</p>";
                } else {
                    $message = "<p class='text-red-600 mb-4'>Invalid status change.</p>";
                }
            } else {
                $message = "<p class='text-red-600 mb-4'>Invalid request.</p>";
            }
        }
    }
}

// Fetch appointments based on tab
$whereClause = "";
$params = ['did' => $did];

switch ($tab) {
    case 'pending':
        $whereClause = "AND a.status='pending'";
        break;
    case 'approved':
        $whereClause = "AND a.status='approved'";
        break;
    case 'completed':
        $whereClause = "AND a.status='completed'";
        break;
    case 'rejected':
        $whereClause = "AND a.status='rejected'";
        break;
    default:
        $whereClause = ""; // Show all
}

$stmt = $pdo->prepare("
    SELECT a.*, u.name AS patient_name, u.email AS patient_email, u.phone AS patient_phone,
           (SELECT COUNT(*) FROM prescriptions WHERE appointment_id = a.id) as prescription_count,
           (SELECT COUNT(*) FROM treatments WHERE appointment_id = a.id) as treatment_count
    FROM appointments a
    JOIN users u ON a.patient_id=u.id
    WHERE a.doctor_id=:did $whereClause
    ORDER BY a.date_time DESC
");
$stmt->execute($params);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>My Appointments - Healsync</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-100">
  <?php include __DIR__ . '/../includes/sidebar_doctor.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300">
    <div class="max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold flex items-center gap-2">
          <i data-lucide="calendar" class="w-6 h-6 text-indigo-600"></i>
          My Appointments
        </h2>
        <a href="calendar.php" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
          <i data-lucide="calendar-days" class="w-4 h-4"></i>
          Calendar View
        </a>
      </div>

      <?= $message ?? '' ?>

      <!-- Tabs -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="border-b border-gray-200">
          <nav class="-mb-px flex space-x-8 px-6">
            <a href="?tab=all" class="<?= $tab === 'all' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
              All Appointments
            </a>
            <a href="?tab=pending" class="<?= $tab === 'pending' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
              Pending
            </a>
            <a href="?tab=approved" class="<?= $tab === 'approved' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
              Approved
            </a>
            <a href="?tab=completed" class="<?= $tab === 'completed' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
              Completed
            </a>
            <a href="?tab=rejected" class="<?= $tab === 'rejected' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
              Rejected
            </a>
          </nav>
        </div>

        <!-- Appointments List -->
        <div class="p-6">
          <?php if (empty($appointments)): ?>
            <div class="text-center py-12">
              <i data-lucide="calendar-x" class="w-12 h-12 text-gray-300 mx-auto mb-4"></i>
              <h3 class="text-lg font-medium text-gray-900 mb-2">No appointments found</h3>
              <p class="text-gray-500">No appointments match the current filter.</p>
            </div>
          <?php else: ?>
            <div class="space-y-4">
              <?php foreach ($appointments as $appt): ?>
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                  <div class="flex items-start justify-between">
                    <div class="flex-1">
                      <div class="flex items-center gap-3 mb-2">
                        <h3 class="text-lg font-semibold text-gray-900"><?= e($appt['patient_name']) ?></h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                          <?= $appt['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                              ($appt['status'] === 'approved' ? 'bg-green-100 text-green-800' : 
                              ($appt['status'] === 'completed' ? 'bg-blue-100 text-blue-800' : 
                              'bg-red-100 text-red-800')) ?>">
                          <?= ucfirst($appt['status']) ?>
                        </span>
                      </div>
                      
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                        <div>
                          <p><strong>Date & Time:</strong> <?= e(format_datetime($appt['date_time'])) ?></p>
                          <p><strong>Email:</strong> <?= e($appt['patient_email']) ?></p>
                          <p><strong>Phone:</strong> <?= e($appt['patient_phone']) ?></p>
                        </div>
                        <div>
                          <p><strong>Reason:</strong> <?= e($appt['reason']) ?></p>
                          <div class="flex gap-4 mt-2">
                            <?php if ($appt['prescription_count'] > 0): ?>
                              <span class="inline-flex items-center gap-1 text-green-600">
                                <i data-lucide="pill" class="w-4 h-4"></i>
                                <?= $appt['prescription_count'] ?> Prescription(s)
                              </span>
                            <?php endif; ?>
                            <?php if ($appt['treatment_count'] > 0): ?>
                              <span class="inline-flex items-center gap-1 text-blue-600">
                                <i data-lucide="stethoscope" class="w-4 h-4"></i>
                                <?= $appt['treatment_count'] ?> Treatment(s)
                              </span>
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="flex flex-col gap-2 ml-4">
                      <?php if ($appt['status'] === 'pending'): ?>
                        <form method="post" class="inline">
                          <input type="hidden" name="csrf" value="<?= csrf() ?>">
                          <input type="hidden" name="appointment_id" value="<?= $appt['id'] ?>">
                          <input type="hidden" name="action" value="approve">
                          <button type="submit" class="inline-flex items-center gap-1 px-3 py-1 bg-green-600 text-white rounded text-sm hover:bg-green-700">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            Approve
                          </button>
                        </form>
                        <form method="post" class="inline">
                          <input type="hidden" name="csrf" value="<?= csrf() ?>">
                          <input type="hidden" name="appointment_id" value="<?= $appt['id'] ?>">
                          <input type="hidden" name="action" value="reject">
                          <button type="submit" class="inline-flex items-center gap-1 px-3 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700">
                            <i data-lucide="x" class="w-4 h-4"></i>
                            Reject
                          </button>
                        </form>
                      <?php elseif ($appt['status'] === 'approved'): ?>
                        <form method="post" class="inline">
                          <input type="hidden" name="csrf" value="<?= csrf() ?>">
                          <input type="hidden" name="appointment_id" value="<?= $appt['id'] ?>">
                          <input type="hidden" name="action" value="complete">
                          <button type="submit" class="inline-flex items-center gap-1 px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            Mark Complete
                          </button>
                        </form>
                      <?php endif; ?>
                      
                      <div class="flex gap-1">
                        <a href="add_prescription.php?appointment_id=<?= $appt['id'] ?>" 
                           class="inline-flex items-center gap-1 px-3 py-1 bg-indigo-600 text-white rounded text-sm hover:bg-indigo-700">
                          <i data-lucide="pill" class="w-4 h-4"></i>
                          Prescription
                        </a>
                        <a href="add_treatment.php?appointment_id=<?= $appt['id'] ?>" 
                           class="inline-flex items-center gap-1 px-3 py-1 bg-purple-600 text-white rounded text-sm hover:bg-purple-700">
                          <i data-lucide="stethoscope" class="w-4 h-4"></i>
                          Treatment
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>

  <script>lucide.createIcons();</script>
</body>
</html>
