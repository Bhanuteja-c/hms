<?php
// patient/appointments.php
require_once __DIR__ . '/../includes/auth.php';
require_role('patient');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$pid = current_user_id();
$statusMsg = $_GET['status'] ?? "";
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>My Appointments - Healsync</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    .glass-effect {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    @keyframes fade-in { 
      from { opacity:0; transform:translateY(-6px);} 
      to {opacity:1; transform:none;} 
    }
    .animate-fade-in { 
      animation: fade-in .28s ease-out; 
    }
  </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 min-h-screen">
  <?php include __DIR__ . '/../includes/sidebar_patient.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <!-- Toast container -->
  <div id="toast-container" class="fixed top-5 right-5 space-y-3 z-50"></div>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300">
    <!-- Header Section -->
    <div class="mb-8">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
          <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
            My Appointments
          </h1>
          <p class="text-gray-600 mt-1">View and manage your medical appointments</p>
        </div>
        <div class="flex items-center gap-3">
          <a href="book_appointment.php" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-medium shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            Book New Appointment
          </a>
        </div>
      </div>
    </div>

    <?php if ($statusMsg === 'cancelled'): ?>
      <div class="animate-fade-in bg-green-50 border border-green-200 text-green-700 p-4 rounded-xl mb-6 flex items-center gap-2">
        <i data-lucide="check-circle" class="w-5 h-5"></i>
        Appointment cancelled successfully.
      </div>
    <?php endif; ?>

    <?php
    $stmt = $pdo->prepare("
        SELECT a.*, u.name AS doctor_name, d.specialty
        FROM appointments a
        JOIN users u ON a.doctor_id=u.id
        JOIN doctors d ON u.id=d.id
        WHERE a.patient_id=:pid
        ORDER BY a.date_time DESC
    ");
    $stmt->execute([':pid'=>$pid]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <!-- Appointments List -->
    <div class="glass-effect rounded-2xl border border-white/20 overflow-hidden">
      <div class="p-6 border-b border-gray-200/50">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center">
              <i data-lucide="calendar" class="w-5 h-5 text-white"></i>
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-900">Your Appointments</h3>
              <p class="text-gray-600 text-sm">Track your medical appointments and history</p>
            </div>
          </div>
          <div class="text-right">
            <p class="text-2xl font-bold text-gray-900"><?= count($appointments) ?></p>
            <p class="text-gray-600 text-sm">Total appointments</p>
          </div>
        </div>
      </div>

      <?php if ($appointments): ?>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date & Time</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Doctor</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Specialty</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Reason</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <?php foreach ($appointments as $a): ?>
                <tr class="hover:bg-gray-50/50 transition-colors duration-200">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="w-2 h-2 bg-indigo-500 rounded-full mr-3"></div>
                      <div>
                        <div class="text-sm font-medium text-gray-900"><?= e(date('d M Y', strtotime($a['date_time']))) ?></div>
                        <div class="text-sm text-gray-500"><?= e(date('H:i', strtotime($a['date_time']))) ?></div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-emerald-500 rounded-full flex items-center justify-center mr-3">
                        <i data-lucide="stethoscope" class="w-4 h-4 text-white"></i>
                      </div>
                      <span class="text-sm font-medium text-gray-900"><?= e($a['doctor_name']) ?></span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                      <?= e($a['specialty'] ?: 'General') ?>
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-gray-900 max-w-xs truncate" title="<?= e($a['reason']) ?>">
                      <?= e($a['reason'] ?: 'No reason provided') ?>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <?php
                    $statusColors = [
                      'pending' => 'bg-yellow-100 text-yellow-800',
                      'approved' => 'bg-green-100 text-green-800', 
                      'rejected' => 'bg-red-100 text-red-800',
                      'completed' => 'bg-blue-100 text-blue-800',
                      'cancelled' => 'bg-gray-100 text-gray-800'
                    ];
                    $statusIcons = [
                      'pending' => 'clock',
                      'approved' => 'check-circle',
                      'rejected' => 'x-circle', 
                      'completed' => 'check',
                      'cancelled' => 'ban'
                    ];
                    $colorClass = $statusColors[$a['status']] ?? 'bg-gray-100 text-gray-800';
                    $iconClass = $statusIcons[$a['status']] ?? 'help-circle';
                    ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $colorClass ?>">
                      <i data-lucide="<?= $iconClass ?>" class="w-3 h-3 mr-1"></i>
                      <?= e(ucfirst($a['status'])) ?>
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <?php if (in_array($a['status'], ['pending','approved']) && strtotime($a['date_time']) > time()): ?>
                      <form method="post" action="cancel_appointment.php" onsubmit="return confirm('Cancel this appointment?');" class="inline">
                        <input type="hidden" name="csrf" value="<?= csrf() ?>">
                        <input type="hidden" name="appointment_id" value="<?= $a['id'] ?>">
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-red-600 to-pink-600 text-white text-xs font-medium rounded-lg hover:from-red-700 hover:to-pink-700 transition-all duration-200 hover:scale-105 shadow-sm">
                          <i data-lucide="x-circle" class="w-3 h-3 mr-1"></i>
                          Cancel
                        </button>
                      </form>
                    <?php else: ?>
                      <span class="text-gray-400 text-xs">No actions</span>
                    <?php endif; ?>
                  </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        </div>
      <?php else: ?>
        <div class="p-12 text-center">
          <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i data-lucide="calendar-x" class="w-8 h-8 text-gray-400"></i>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No appointments yet</h3>
          <p class="text-gray-600 mb-4">You haven't booked any appointments. Start by scheduling your first appointment.</p>
          <a href="book_appointment.php" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-medium hover:from-indigo-700 hover:to-purple-700 transition-all duration-300">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            Book Your First Appointment
          </a>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <script>lucide.createIcons();</script>
</body>
</html>
