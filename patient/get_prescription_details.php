<?php
// patient/get_prescription_details.php
require_once __DIR__ . '/../includes/auth.php';
require_role('patient');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$appointment_id = intval($_GET['appointment_id'] ?? 0);
$patient_id = current_user_id();

if (!$appointment_id) {
    json_error('Invalid appointment ID');
}

// Verify appointment belongs to patient
$stmt = $pdo->prepare("
    SELECT a.date_time, d.name AS doctor_name, d.specialty
    FROM appointments a
    JOIN users d ON a.doctor_id = d.id
    WHERE a.id = :appointment_id AND a.patient_id = :patient_id
");
$stmt->execute([':appointment_id' => $appointment_id, ':patient_id' => $patient_id]);
$appointment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$appointment) {
    json_error('Appointment not found');
}

// Get all prescriptions for this appointment
$stmt = $pdo->prepare("
    SELECT medicine, dosage, duration, instructions
    FROM prescriptions
    WHERE appointment_id = :appointment_id
    ORDER BY id
");
$stmt->execute([':appointment_id' => $appointment_id]);
$medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$medicines) {
    json_error('No prescriptions found for this appointment');
}

// Generate HTML content
ob_start();
?>
<div class="space-y-6">
    <!-- Appointment Info -->
    <div class="bg-gray-50 rounded-lg p-4">
        <h4 class="font-semibold text-gray-900 mb-2">Appointment Details</h4>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-500">Date:</span>
                <span class="font-medium"><?= e(format_datetime($appointment['date_time'])) ?></span>
            </div>
            <div>
                <span class="text-gray-500">Doctor:</span>
                <span class="font-medium"><?= e($appointment['doctor_name']) ?></span>
            </div>
            <?php if ($appointment['specialty']): ?>
            <div class="col-span-2">
                <span class="text-gray-500">Specialty:</span>
                <span class="font-medium"><?= e($appointment['specialty']) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Medicines -->
    <div>
        <h4 class="font-semibold text-gray-900 mb-3">Prescribed Medicines</h4>
        <div class="space-y-3">
            <?php foreach ($medicines as $index => $medicine): ?>
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h5 class="font-medium text-gray-900"><?= e($medicine['medicine']) ?></h5>
                        <div class="mt-2 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <?php if ($medicine['dosage']): ?>
                            <div>
                                <span class="text-gray-500">Dosage:</span>
                                <span class="font-medium"><?= e($medicine['dosage']) ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if ($medicine['duration']): ?>
                            <div>
                                <span class="text-gray-500">Duration:</span>
                                <span class="font-medium"><?= e($medicine['duration']) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php if ($medicine['instructions']): ?>
                        <div class="mt-2">
                            <span class="text-gray-500 text-sm">Instructions:</span>
                            <p class="text-sm text-gray-700 mt-1"><?= e($medicine['instructions']) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                    <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded">
                        #<?= $index + 1 ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex justify-end gap-3 pt-4 border-t">
        <a href="prescription_pdf.php?id=<?= e($appointment_id) ?>" target="_blank"
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            <i data-lucide="download" class="w-4 h-4"></i>
            Download PDF
        </a>
        <button onclick="closePrescriptionModal()"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
            Close
        </button>
    </div>
</div>
<?php
$html = ob_get_clean();

json_success('Prescription details loaded', ['html' => $html]);
