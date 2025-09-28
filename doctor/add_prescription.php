<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('doctor');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$did = current_user_id();
$message = "";

// Fetch approved appointments
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
        $medicines      = $_POST['medicine'] ?? [];
        $dosages        = $_POST['dosage'] ?? [];
        $durations      = $_POST['duration'] ?? [];
        $instructions   = $_POST['instructions'] ?? [];

        // Ensure at least one medicine filled
        $validEntries = 0;
        foreach ($medicines as $m) {
            if (trim($m)) $validEntries++;
        }

        if ($appointment_id && $validEntries > 0) {
            // Verify appointment belongs to doctor
            $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id=:id AND doctor_id=:did AND status='approved'");
            $stmt->execute([':id'=>$appointment_id, ':did'=>$did]);
            $appt = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($appt) {
                $pid = $appt['patient_id'];

                // Insert medicines directly into prescriptions table
                $stmt = $pdo->prepare("
                    INSERT INTO prescriptions (appointment_id, medicine, dosage, duration, instructions)
                    VALUES (:aid, :med, :dos, :dur, :ins)
                ");

                for ($i=0; $i<count($medicines); $i++) {
                    $med = trim($medicines[$i]);
                    if (!$med) continue; // skip empty rows
                    $stmt->execute([
                        ':aid' => $appointment_id,
                        ':med' => $med,
                        ':dos' => trim($dosages[$i] ?? ''),
                        ':dur' => trim($durations[$i] ?? ''),
                        ':ins' => trim($instructions[$i] ?? '')
                    ]);
                }

                // Notify patient
                $msg = "A prescription has been added for your appointment on " .
                        date('d M Y', strtotime($appt['date_time'])) . ".";
                $link = "/healsync/patient/prescriptions.php";
                $pdo->prepare("INSERT INTO notifications (user_id,message,link) VALUES (:uid,:msg,:link)")
                    ->execute([':uid' => $pid, ':msg' => $msg, ':link' => $link]);

                // Audit log
                audit_log($pdo, $did, 'add_prescription', json_encode([
                    'appointment_id' => $appointment_id,
                    'medicines_count' => $validEntries
                ]));

                $message = "<div class='bg-green-50 border border-green-200 text-green-700 p-3 rounded mb-4'>
                              Prescription saved with $validEntries medicine(s).
                            </div>";
            } else {
                $message = "<div class='bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-4'>
                              Invalid appointment.
                            </div>";
            }
        } else {
            $message = "<div class='bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-4'>
                          Appointment and at least one medicine are required.
                        </div>";
        }
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

  <main class="pt-20 p-6 md:ml-64">
    <div class="max-w-6xl mx-auto">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold flex items-center gap-2">
          <i data-lucide="pill" class="w-6 h-6 text-indigo-600"></i>
          Add Prescription
        </h2>
        <a href="view_prescriptions.php" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
          <i data-lucide="arrow-left" class="w-4 h-4"></i>
          Back to Prescriptions
        </a>
      </div>

      <?= $message ?>

      <form method="post" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6" id="prescriptionForm">
        <input type="hidden" name="csrf" value="<?=csrf()?>">

        <!-- Appointment Selection -->
        <div class="mb-6">
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

        <!-- Medicines Section -->
        <div class="mb-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
              <i data-lucide="pill" class="w-5 h-5 text-indigo-600"></i>
              Prescribed Medicines
            </h3>
            <button type="button" onclick="addMedicineRow()" 
                    class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">
              <i data-lucide="plus" class="w-4 h-4"></i>
              Add Medicine
            </button>
          </div>

          <!-- Medicine Table Header -->
          <div class="bg-gray-50 rounded-lg p-4 mb-4">
            <div class="grid grid-cols-12 gap-4 text-sm font-medium text-gray-700">
              <div class="col-span-4">Medicine Name *</div>
              <div class="col-span-2">Dosage</div>
              <div class="col-span-2">Duration</div>
              <div class="col-span-3">Instructions</div>
              <div class="col-span-1 text-center">Action</div>
            </div>
          </div>

          <!-- Medicine Rows -->
          <div id="medicines-area" class="space-y-3">
            <div class="medicine-row grid grid-cols-12 gap-4 items-center p-4 border border-gray-200 rounded-lg bg-white">
              <div class="col-span-4">
                <input type="text" name="medicine[]" placeholder="e.g. Paracetamol 500mg" 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
                       required>
              </div>
              <div class="col-span-2">
                <input type="text" name="dosage[]" placeholder="e.g. 1 tablet" 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
              </div>
              <div class="col-span-2">
                <input type="text" name="duration[]" placeholder="e.g. 7 days" 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
              </div>
              <div class="col-span-3">
                <input type="text" name="instructions[]" placeholder="e.g. After meals" 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
              </div>
              <div class="col-span-1 text-center">
                <button type="button" onclick="removeMedicineRow(this)" 
                        class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors" 
                        title="Remove medicine">
                  <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
              </div>
            </div>
          </div>

          <div id="medicineError" class="text-red-600 text-sm mt-2 hidden"></div>
        </div>

        <!-- Preview Section -->
        <div class="mb-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <i data-lucide="eye" class="w-5 h-5 text-indigo-600"></i>
            Prescription Preview
          </h3>
          <div id="prescriptionPreview" class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <div class="text-gray-500 text-center py-4">
              <i data-lucide="info" class="w-8 h-8 text-gray-300 mx-auto mb-2"></i>
              <p>Select an appointment and add medicines to see preview</p>
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end gap-3 pt-4 border-t">
          <button type="button" onclick="resetForm()" 
                  class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
            <i data-lucide="refresh-cw" class="w-4 h-4 inline mr-1"></i>
            Reset
          </button>
          <button type="submit" id="submitBtn" 
                  class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4"></i>
            Save Prescription
          </button>
        </div>
      </form>
    </div>
  </main>

  <script>
    lucide.createIcons();
    
    // Form elements
    const form = document.getElementById('prescriptionForm');
    const appointmentSelect = document.getElementById('appointmentSelect');
    const medicinesArea = document.getElementById('medicines-area');
    const previewContent = document.getElementById('prescriptionPreview');
    const submitBtn = document.getElementById('submitBtn');

    // Error elements
    const appointmentError = document.getElementById('appointmentError');
    const medicineError = document.getElementById('medicineError');

    // Add medicine row
    function addMedicineRow() {
      const row = document.createElement('div');
      row.className = 'medicine-row grid grid-cols-12 gap-4 items-center p-4 border border-gray-200 rounded-lg bg-white';
      row.innerHTML = `
        <div class="col-span-4">
          <input type="text" name="medicine[]" placeholder="e.g. Paracetamol 500mg" 
                 class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
                 required>
        </div>
        <div class="col-span-2">
          <input type="text" name="dosage[]" placeholder="e.g. 1 tablet" 
                 class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div class="col-span-2">
          <input type="text" name="duration[]" placeholder="e.g. 7 days" 
                 class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div class="col-span-3">
          <input type="text" name="instructions[]" placeholder="e.g. After meals" 
                 class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div class="col-span-1 text-center">
          <button type="button" onclick="removeMedicineRow(this)" 
                  class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors" 
                  title="Remove medicine">
            <i data-lucide="trash-2" class="w-4 h-4"></i>
          </button>
        </div>
      `;
      medicinesArea.appendChild(row);
      lucide.createIcons();
      updatePreview();
    }

    // Remove medicine row
    function removeMedicineRow(btn) {
      const rows = medicinesArea.querySelectorAll('.medicine-row');
      if (rows.length > 1) {
        btn.closest('.medicine-row').remove();
        updatePreview();
      } else {
        alert("At least one medicine is required.");
      }
    }

    // Form validation
    function validateForm() {
      let isValid = true;
      
      // Clear previous errors
      [appointmentError, medicineError].forEach(el => {
        el.classList.add('hidden');
        el.textContent = '';
      });

      // Validate appointment
      if (!appointmentSelect.value) {
        appointmentError.textContent = 'Please select an appointment';
        appointmentError.classList.remove('hidden');
        isValid = false;
      }

      // Validate medicines
      const medicineInputs = medicinesArea.querySelectorAll('input[name="medicine[]"]');
      let validMedicines = 0;
      
      medicineInputs.forEach(input => {
        if (input.value.trim()) {
          validMedicines++;
        }
      });

      if (validMedicines === 0) {
        medicineError.textContent = 'At least one medicine is required';
        medicineError.classList.remove('hidden');
        isValid = false;
      }

      return isValid;
    }

    // Update preview
    function updatePreview() {
      const appointment = appointmentSelect.options[appointmentSelect.selectedIndex];
      const patient = appointment ? appointment.dataset.patient : '';
      const appointmentDate = appointment ? appointment.dataset.date : '';
      
      const medicineInputs = medicinesArea.querySelectorAll('input[name="medicine[]"]');
      const medicines = [];
      
      medicineInputs.forEach((input, index) => {
        if (input.value.trim()) {
          const row = input.closest('.medicine-row');
          const dosage = row.querySelector('input[name="dosage[]"]').value.trim();
          const duration = row.querySelector('input[name="duration[]"]').value.trim();
          const instructions = row.querySelector('input[name="instructions[]"]').value.trim();
          
          medicines.push({
            name: input.value.trim(),
            dosage: dosage,
            duration: duration,
            instructions: instructions
          });
        }
      });

      if (!appointmentSelect.value || medicines.length === 0) {
        previewContent.innerHTML = `
          <div class="text-gray-500 text-center py-4">
            <i data-lucide="info" class="w-8 h-8 text-gray-300 mx-auto mb-2"></i>
            <p>Select an appointment and add medicines to see preview</p>
          </div>
        `;
        return;
      }

      let medicinesHtml = medicines.map((med, index) => `
        <div class="bg-white rounded-lg p-3 border border-gray-200">
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <h4 class="font-medium text-gray-900">${med.name}</h4>
              <div class="mt-2 grid grid-cols-1 md:grid-cols-3 gap-2 text-sm">
                ${med.dosage ? `<div><span class="text-gray-500">Dosage:</span> <span class="font-medium">${med.dosage}</span></div>` : ''}
                ${med.duration ? `<div><span class="text-gray-500">Duration:</span> <span class="font-medium">${med.duration}</span></div>` : ''}
              </div>
              ${med.instructions ? `<div class="mt-2"><span class="text-gray-500 text-sm">Instructions:</span> <span class="text-sm text-gray-700">${med.instructions}</span></div>` : ''}
            </div>
            <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded">#${index + 1}</span>
          </div>
        </div>
      `).join('');

      previewContent.innerHTML = `
        <div class="space-y-4">
          <div class="bg-indigo-50 rounded-lg p-4">
            <h4 class="font-medium text-indigo-900 mb-2">Patient Details</h4>
            <p class="text-sm text-indigo-800">${patient}</p>
            <p class="text-sm text-indigo-600">${appointmentDate ? new Date(appointmentDate).toLocaleDateString() : ''}</p>
          </div>
          
          <div>
            <h4 class="font-medium text-gray-900 mb-3">Prescribed Medicines (${medicines.length})</h4>
            <div class="space-y-3">
              ${medicinesHtml}
            </div>
          </div>
        </div>
      `;
      
      lucide.createIcons();
    }

    // Event listeners
    appointmentSelect.addEventListener('change', updatePreview);
    
    // Listen for medicine input changes
    medicinesArea.addEventListener('input', function(e) {
      if (e.target.name === 'medicine[]' || e.target.name === 'dosage[]' || 
          e.target.name === 'duration[]' || e.target.name === 'instructions[]') {
        updatePreview();
      }
    });

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
      // Keep only the first medicine row
      const rows = medicinesArea.querySelectorAll('.medicine-row');
      for (let i = 1; i < rows.length; i++) {
        rows[i].remove();
      }
      updatePreview();
      [appointmentError, medicineError].forEach(el => {
        el.classList.add('hidden');
      });
    }

    // Initialize preview
    updatePreview();
  </script>

</body>
</html>
