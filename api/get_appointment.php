<?php
// api/get_appointment.php
require_once __DIR__ . '/../includes/auth.php';
require_role('doctor');
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

$id  = intval($_GET['id'] ?? 0);
$did = current_user_id();

// Fetch appointment
$stmt = $pdo->prepare("
  SELECT a.id, a.reason, u.name AS patient_name
  FROM appointments a
  JOIN users u ON a.patient_id = u.id
  WHERE a.id = :id AND a.doctor_id = :did AND a.status = 'approved'
");
$stmt->execute([':id' => $id, ':did' => $did]);
$app = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$app) {
    echo json_encode(['ok' => false, 'msg' => 'Appointment not found']);
    exit;
}

// Check prescription
$stmt = $pdo->prepare("SELECT COUNT(*) FROM prescriptions WHERE appointment_id = :id");
$stmt->execute([':id' => $id]);
$hasPrescription = $stmt->fetchColumn() > 0;

// Check treatment
$stmt = $pdo->prepare("SELECT COUNT(*) FROM treatments WHERE appointment_id = :id");
$stmt->execute([':id' => $id]);
$hasTreatment = $stmt->fetchColumn() > 0;

// Determine status for calendar color
$status = 'approved';
if ($hasPrescription && $hasTreatment) {
    $status = 'both';
} elseif ($hasPrescription) {
    $status = 'prescription';
} elseif ($hasTreatment) {
    $status = 'treatment';
}

echo json_encode([
    'ok' => true,
    'id' => $app['id'],
    'patient_name' => $app['patient_name'],
    'reason' => $app['reason'],
    'status' => $status
]);
