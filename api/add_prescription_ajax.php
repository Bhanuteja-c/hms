<?php
// api/add_prescription_ajax.php
require_once __DIR__ . '/../includes/auth.php';
require_role('doctor');
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok'=>false,'msg'=>'Invalid request']); exit;
}

$aid = intval($_POST['appointment_id'] ?? 0);
$med = trim($_POST['medicine'] ?? '');
$dos = trim($_POST['dosage'] ?? '');
$dur = trim($_POST['duration'] ?? '');
$ins = trim($_POST['instructions'] ?? '');

if (!$aid || !$med) {
    echo json_encode(['ok'=>false,'msg'=>'Missing fields']); exit;
}

$stmt = $pdo->prepare("INSERT INTO prescriptions (appointment_id,medicine,dosage,duration,instructions) VALUES (:aid,:m,:d,:dur,:i)");
$stmt->execute([':aid'=>$aid,':m'=>$med,':d'=>$dos,':dur'=>$dur,':i'=>$ins]);

echo json_encode([
  'ok'=>true,
  'msg'=>'Prescription saved.',
  'status'=>'prescription'
]);
