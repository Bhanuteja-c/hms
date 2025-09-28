<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('doctor');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$did = current_user_id();
$message = "";

// Fetch approved appointments for dropdown
$stmt = $pdo->prepare("
    SELECT a.id, a.date_time, u.name AS patient_name, a.patient_id
    FROM appointments a
    JOIN users u ON a.patient_id = u.id
    WHERE a.doctor_id = :did AND a.status = 'approved'
    ORDER BY a.date_time DESC
");
$stmt->execute([':did' => $did]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $message = "<div class='bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-4'>
                      Invalid CSRF token.
                    </div>";
    } else {
        $appointment_id = intval($_POST['appointment_id'] ?? 0);
        $treatment_name = trim($_POST['treatment_name'] ?? '');
        $date           = trim($_POST['date'] ?? '');
        $notes          = trim($_POST['notes'] ?? '');
        $cost           = floatval($_POST['cost'] ?? 0);

        if ($appointment_id && $treatment_name && $date && $cost > 0) {
            // Verify appointment belongs to doctor
            $stmt = $pdo->prepare("
                SELECT * FROM appointments 
                WHERE id=:id AND doctor_id=:did AND status='approved'
            ");
            $stmt->execute([':id' => $appointment_id, ':did' => $did]);
            $appt = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($appt) {
                $pid = $appt['patient_id'];

                // Insert treatment
                $stmt = $pdo->prepare("
                    INSERT INTO treatments (appointment_id, treatment_name, date, notes, cost)
                    VALUES (:aid, :tname, :tdate, :notes, :cost)
                ");
                $stmt->execute([
                    ':aid'   => $appointment_id,
                    ':tname' => $treatment_name,
                    ':tdate' => $date,
                    ':notes' => $notes,
                    ':cost'  => $cost
                ]);

                // Ensure one bill per appointment
                $stmt = $pdo->prepare("SELECT id FROM bills WHERE appointment_id=:aid LIMIT 1");
                $stmt->execute([':aid' => $appointment_id]);
                $bill = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($bill) {
                    // Update bill total
                    $pdo->prepare("
                        UPDATE bills SET total_amount = total_amount + :c WHERE id=:id
                    ")->execute([':c' => $cost, ':id' => $bill['id']]);
                    $billId = $bill['id'];
                } else {
                    // Create bill
                    $stmt = $pdo->prepare("
                        INSERT INTO bills (patient_id, doctor_id, appointment_id, total_amount, status, created_at)
                        VALUES (:pid,:did,:aid,:c,'unpaid',NOW())
                    ");
                    $stmt->execute([
                        ':pid' => $pid,
                        ':did' => $did,
                        ':aid' => $appointment_id,
                        ':c'   => $cost
                    ]);
                    $billId = $pdo->lastInsertId();
                }

                // Add bill item
                $pdo->prepare("
                    INSERT INTO bill_items (bill_id, description, amount)
                    VALUES (:bid, :desc, :amt)
                ")->execute([
                    ':bid'  => $billId,
                    ':desc' => $treatment_name,
                    ':amt'  => $cost
                ]);

                // Notify patient
                $msg = "A new treatment ($treatment_name) was added on " . date('d M Y', strtotime($date)) .
                       ". A bill has been generated.";
                $link = "/healsync/patient/bills.php";
                $pdo->prepare("INSERT INTO notifications (user_id,message,link) VALUES (:uid,:msg,:link)")
                    ->execute([':uid' => $pid, ':msg' => $msg, ':link' => $link]);

                // Audit log
                audit_log($pdo, $did, 'add_treatment', json_encode([
                    'appointment_id' => $appointment_id,
                    'treatment'      => $treatment_name,
                    'cost'           => $cost
                ]));

                $message = "<div class='bg-green-50 border border-green-200 text-green-700 p-3 rounded mb-4'>
                              ✅ Treatment saved and bill updated successfully.
                            </div>";
            } else {
                $message = "<div class='bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-4'>
                              Invalid appointment.
                            </div>";
            }
        } else {
            $message = "<div class='bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-4'>
                          All fields are required and cost must be greater than 0.
                        </div>";
        }
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

  <main class="pt-20 p-6 md:ml-64">
    <div class="max-w-4xl mx-auto">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold flex items-center gap-2">
          <i data-lucide="activity" class="w-6 h-6 text-indigo-600"></i>
          Add Treatment
        </h2>
        <a href="view_treatments.php" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
          <i data-lucide="arrow-left" class="w-4 h-4"></i>
          Back to Treatments
        </a>
      </div>

      <?= $message ?>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form -->
        <div class="lg:col-span-2">
          <form method="post" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-6" id="treatmentForm">
            <input type="hidden" name="csrf" value="<?=csrf()?>">

            <!-- Appointment Selection -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                <i data-lucide="calendar" class="w-4 h-4 inline mr-1"></i>
                Select Appointment *
              </label>
              <select name="appointment_id" id="appointmentSelect" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                <option value="">-- Choose an appointment --</option>
                <?php foreach ($appointments as $a): ?>
                  <option value="<?= e($a['id']) ?>" data-patient="<?= e($a['patient_name']) ?>" data-date="<?= e($a['date_time']) ?>">
                    <?= e(format_datetime($a['date_time'])) ?> - <?= e($a['patient_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div id="appointmentError" class="text-red-600 text-sm mt-1 hidden"></div>
            </div>

            <!-- Treatment Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  <i data-lucide="stethoscope" class="w-4 h-4 inline mr-1"></i>
                  Treatment Name *
                </label>
                <input type="text" name="treatment_name" id="treatmentName" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required placeholder="e.g. Physiotherapy, X-Ray, Blood Test">
                <div id="treatmentError" class="text-red-600 text-sm mt-1 hidden"></div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  <i data-lucide="calendar" class="w-4 h-4 inline mr-1"></i>
                  Treatment Date *
                </label>
                <input type="text" name="date" id="treat_date" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                <div id="dateError" class="text-red-600 text-sm mt-1 hidden"></div>
              </div>
            </div>

            <!-- Cost -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                <i data-lucide="credit-card" class="w-4 h-4 inline mr-1"></i>
                Cost (₹) *
              </label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">₹</span>
                <input type="number" step="0.01" name="cost" id="costInput" class="w-full border border-gray-300 rounded-lg pl-8 pr-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required placeholder="0.00" min="0">
              </div>
              <div id="costError" class="text-red-600 text-sm mt-1 hidden"></div>
            </div>

            <!-- Notes -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                <i data-lucide="file-text" class="w-4 h-4 inline mr-1"></i>
                Treatment Notes
              </label>
              <textarea name="notes" id="notesInput" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" rows="4" placeholder="Additional details, instructions, or observations..."></textarea>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end gap-3 pt-4 border-t">
              <button type="button" onclick="resetForm()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                <i data-lucide="refresh-cw" class="w-4 h-4 inline mr-1"></i>
                Reset
              </button>
              <button type="submit" id="submitBtn" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
                Save Treatment
              </button>
            </div>
          </form>
        </div>

        <!-- Preview Panel -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
              <i data-lucide="eye" class="w-5 h-5 text-indigo-600"></i>
              Treatment Preview
            </h3>
            <div id="previewContent" class="space-y-3 text-sm">
              <div class="text-gray-500 text-center py-4">
                <i data-lucide="info" class="w-8 h-8 text-gray-300 mx-auto mb-2"></i>
                <p>Fill the form to see preview</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script>
    lucide.createIcons();
    
    // Initialize Flatpickr
    flatpickr("#treat_date", { 
      dateFormat: "Y-m-d",
      minDate: "today",
      maxDate: new Date().fp_incr(365) // 1 year from now
    });

    // Form elements
    const form = document.getElementById('treatmentForm');
    const appointmentSelect = document.getElementById('appointmentSelect');
    const treatmentName = document.getElementById('treatmentName');
    const costInput = document.getElementById('costInput');
    const notesInput = document.getElementById('notesInput');
    const previewContent = document.getElementById('previewContent');
    const submitBtn = document.getElementById('submitBtn');

    // Error elements
    const appointmentError = document.getElementById('appointmentError');
    const treatmentError = document.getElementById('treatmentError');
    const costError = document.getElementById('costError');

    // Real-time validation
    function validateForm() {
      let isValid = true;
      
      // Clear previous errors
      [appointmentError, treatmentError, costError].forEach(el => {
        el.classList.add('hidden');
        el.textContent = '';
      });

      // Validate appointment
      if (!appointmentSelect.value) {
        appointmentError.textContent = 'Please select an appointment';
        appointmentError.classList.remove('hidden');
        isValid = false;
      }

      // Validate treatment name
      if (!treatmentName.value.trim()) {
        treatmentError.textContent = 'Treatment name is required';
        treatmentError.classList.remove('hidden');
        isValid = false;
      } else if (treatmentName.value.trim().length < 3) {
        treatmentError.textContent = 'Treatment name must be at least 3 characters';
        treatmentError.classList.remove('hidden');
        isValid = false;
      }

      // Validate cost
      const cost = parseFloat(costInput.value);
      if (!costInput.value || isNaN(cost) || cost <= 0) {
        costError.textContent = 'Cost must be greater than 0';
        costError.classList.remove('hidden');
        isValid = false;
      }

      return isValid;
    }

    // Update preview
    function updatePreview() {
      const appointment = appointmentSelect.options[appointmentSelect.selectedIndex];
      const patient = appointment ? appointment.dataset.patient : '';
      const appointmentDate = appointment ? appointment.dataset.date : '';
      
      if (!appointmentSelect.value || !treatmentName.value || !costInput.value) {
        previewContent.innerHTML = `
          <div class="text-gray-500 text-center py-4">
            <i data-lucide="info" class="w-8 h-8 text-gray-300 mx-auto mb-2"></i>
            <p>Fill the form to see preview</p>
          </div>
        `;
        return;
      }

      const cost = parseFloat(costInput.value) || 0;
      const notes = notesInput.value.trim();
      
      previewContent.innerHTML = `
        <div class="space-y-3">
          <div class="bg-gray-50 rounded-lg p-3">
            <h4 class="font-medium text-gray-900 mb-2">Patient Details</h4>
            <p class="text-sm text-gray-600">${patient}</p>
            <p class="text-sm text-gray-500">${appointmentDate ? new Date(appointmentDate).toLocaleDateString() : ''}</p>
          </div>
          
          <div class="bg-indigo-50 rounded-lg p-3">
            <h4 class="font-medium text-indigo-900 mb-2">Treatment Details</h4>
            <p class="text-sm text-indigo-800 font-medium">${treatmentName.value}</p>
            <p class="text-sm text-indigo-600">Cost: ₹${cost.toFixed(2)}</p>
            ${notes ? `<p class="text-sm text-indigo-700 mt-2">${notes}</p>` : ''}
          </div>
          
          <div class="bg-green-50 rounded-lg p-3">
            <h4 class="font-medium text-green-900 mb-1">Bill Impact</h4>
            <p class="text-sm text-green-800">This treatment will add ₹${cost.toFixed(2)} to the patient's bill</p>
          </div>
        </div>
      `;
      
      lucide.createIcons();
    }

    // Event listeners
    appointmentSelect.addEventListener('change', updatePreview);
    treatmentName.addEventListener('input', updatePreview);
    costInput.addEventListener('input', updatePreview);
    notesInput.addEventListener('input', updatePreview);

    // Form submission
    form.addEventListener('submit', function(e) {
      if (!validateForm()) {
        e.preventDefault();
        return false;
      }
      
      // Show loading state
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin mr-2"></i>Saving...';
    });

    // Reset form
    function resetForm() {
      form.reset();
      updatePreview();
      [appointmentError, treatmentError, costError].forEach(el => {
        el.classList.add('hidden');
      });
    }

    // Auto-format cost input
    costInput.addEventListener('blur', function() {
      const value = parseFloat(this.value);
      if (!isNaN(value)) {
        this.value = value.toFixed(2);
        updatePreview();
      }
    });

    // Initialize preview
    updatePreview();
  </script>
</body>
</html>
