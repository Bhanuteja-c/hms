<?php
// api/get_appointment.php
require_once __DIR__ . '/../includes/auth.php';
require_role('doctor');
require_once __DIR__ . '/../includes/db.php';

$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("
  SELECT a.id, a.reason, u.name AS patient_name
  FROM appointments a
  JOIN users u ON a.patient_id = u.id
  WHERE a.id = :id AND a.doctor_id = :did AND a.status = 'approved'
");
$stmt->execute([':id'=>$id, ':did'=>current_user_id()]);
$app = $stmt->fetch(PDO::FETCH_ASSOC);

if ($app) {
  echo json_encode(['ok'=>true] + $app);
} else {
  echo json_encode(['ok'=>false]);
}
