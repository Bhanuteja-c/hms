<?php
// api/ajax_approve.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); exit;
}
if (!verify_csrf($_POST['csrf'] ?? '')) {
    echo "Invalid CSRF"; exit;
}
$aid = intval($_POST['appointment_id'] ?? 0);
$action = $_POST['action'] ?? null;
if (!$aid || !in_array($action,['approve','reject'])) {
    echo "Invalid data"; exit;
}

// load appointment
$stmt = $pdo->prepare("SELECT * FROM appointments WHERE id=:id LIMIT 1");
$stmt->execute([':id'=>$aid]);
$app = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$app) {
    echo "Appointment not found"; exit;
}
if ($app['doctor_id'] != current_user_id()) {
    echo "Not authorized"; exit;
}

$status = $action === 'approve' ? 'approved' : 'rejected';
$pdo->prepare("UPDATE appointments SET status=:st WHERE id=:id")->execute([':st'=>$status,':id'=>$aid]);

// notify patient by audit log (demo)
audit_log($pdo, $app['patient_id'], 'appointment_'.$status, json_encode(['appointment_id'=>$aid]));

// redirect back
header("Location: /healsync/doctor/pending_appointments.php");
exit;
