<?php
// api/ajax_check_slot.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';

$doctor_id = intval($_GET['doctor_id'] ?? 0);
$dt = $_GET['datetime'] ?? '';
if (!$doctor_id || !$dt) {
    echo json_encode(['ok'=>false,'msg'=>'Invalid params']);
    exit;
}
$stmt = $pdo->prepare("SELECT COUNT(*) as c FROM appointments WHERE doctor_id=:did AND date_time = :dt AND status IN ('pending','approved')");
$stmt->execute([':did'=>$doctor_id,':dt'=>$dt]);
$c = (int)$stmt->fetchColumn();
echo json_encode(['ok'=>true,'available'=> $c === 0]);
