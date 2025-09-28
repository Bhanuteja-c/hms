<?php
// doctor/get_prescription_details_doctor.php
require_once __DIR__ . '/../includes/auth.php';
require_role('doctor');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$prescription_id = intval($_GET['id'] ?? 0);
$doctor_id = current_user_id();

if (!$prescription_id) {
    json_error('Invalid prescription ID');
}

// Verify prescription belongs to doctor
$stmt = $pdo->prepare("
    SELECT p.*, a.date_time, u.name AS patient_name, u.email AS patient_email, u.phone AS patient_phone
    FROM prescriptions p
    JOIN appointments a ON p.appointment_id = a.id
    JOIN users u ON a.patient_id = u.id
    WHERE p.id = :prescription_id AND a.doctor_id = :doctor_id
");
$stmt->execute([':prescription_id' => $prescription_id, ':doctor_id' => $doctor_id]);
$prescription = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prescription) {
    json_error('Prescription not found');
}

// Get all medicines for this appointment
$stmt = $pdo->prepare("
    SELECT medicine, dosage, duration, instructions
    FROM prescriptions
    WHERE appointment_id = :appointment_id
    ORDER BY id
");
$stmt->execute([':appointment_id' => $prescription['appointment_id']]);
$medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$medicines) {
    json_error('No medicines found for this prescription');
}

// Generate HTML content
ob_start();
?>
<div class="space-y-6">
    <!-- Patient Info -->
    <div class="bg-indigo-50 rounded-lg p-4">
        <h4 class="font-semibold text-indigo-900 mb-3 flex items-center gap-2">
            <i data-lucide="user" class="w-5 h-5"></i>
            Patient Information
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <span class="text-indigo-600 font-medium">Name:</span>
                <span class="text-indigo-800"><?= e($prescription['patient_name']) ?></span>
            </div>
            <div>
                <span class="text-indigo-600 font-medium">Email:</span>
                <span class="text-indigo-800"><?= e($prescription['patient_email']) ?></span>
            </div>
            <?php if ($prescription['patient_phone']): ?>
            <div>
                <span class="text-indigo-600 font-medium">Phone:</span>
                <span class="text-indigo-800"><?= e($prescription['patient_phone']) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Appointment Info -->
    <div class="bg-gray-50 rounded-lg p-4">
        <h4 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
            <i data-lucide="calendar" class="w-5 h-5"></i>
            Appointment Details
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-600 font-medium">Date:</span>
                <span class="text-gray-800"><?= e(format_datetime($prescription['date_time'])) ?></span>
            </div>
            <div>
                <span class="text-gray-600 font-medium">Prescription Date:</span>
                <span class="text-gray-800"><?= e(format_datetime($prescription['created_at'])) ?></span>
            </div>
        </div>
    </div>

    <!-- Medicines -->
    <div>
        <h4 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <i data-lucide="pill" class="w-5 h-5"></i>
            Prescribed Medicines (<?= count($medicines) ?>)
        </h4>
        <div class="space-y-3">
            <?php foreach ($medicines as $index => $medicine): ?>
            <div class="border border-gray-200 rounded-lg p-4 bg-white">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h5 class="font-medium text-gray-900 text-lg"><?= e($medicine['medicine']) ?></h5>
                        <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <?php if ($medicine['dosage']): ?>
                            <div class="bg-blue-50 rounded-lg p-3">
                                <span class="text-blue-600 font-medium">Dosage:</span>
                                <p class="text-blue-800 mt-1"><?= e($medicine['dosage']) ?></p>
                            </div>
                            <?php endif; ?>
                            <?php if ($medicine['duration']): ?>
                            <div class="bg-green-50 rounded-lg p-3">
                                <span class="text-green-600 font-medium">Duration:</span>
                                <p class="text-green-800 mt-1"><?= e($medicine['duration']) ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php if ($medicine['instructions']): ?>
                        <div class="mt-3 bg-yellow-50 rounded-lg p-3">
                            <span class="text-yellow-600 font-medium text-sm">Instructions:</span>
                            <p class="text-yellow-800 mt-1"><?= e($medicine['instructions']) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                    <span class="text-xs text-gray-400 bg-gray-100 px-3 py-1 rounded-full font-medium">
                        #<?= $index + 1 ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex justify-end gap-3 pt-4 border-t">
        <a href="prescription_pdf.php?id=<?= e($prescription_id) ?>" target="_blank"
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
