<?php
// api/add_treatment_ajax.php
require_once __DIR__ . '/../includes/auth.php';
require_role('doctor');
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok'=>false,'msg'=>'Invalid request']); exit;
}

$aid = intval($_POST['appointment_id'] ?? 0);
$tname = trim($_POST['treatment_name'] ?? '');
$date = $_POST['date'] ?? null;
$notes = trim($_POST['notes'] ?? '');
$cost = floatval($_POST['cost'] ?? 0);

if (!$aid || !$tname || !$date) {
    echo json_encode(['ok'=>false,'msg'=>'Missing fields']); exit;
}

// Insert treatment
$stmt = $pdo->prepare("INSERT INTO treatments (appointment_id,treatment_name,date,notes,cost) VALUES (:aid,:t,:d,:n,:c)");
$stmt->execute([':aid'=>$aid,':t'=>$tname,':d'=>$date,':n'=>$notes,':c'=>$cost]);

// Update or create bill
$stmt = $pdo->prepare("SELECT patient_id FROM appointments WHERE id=:aid");
$stmt->execute([':aid'=>$aid]);
$pid = $stmt->fetchColumn();
if ($pid) {
    $b = $pdo->prepare("SELECT id,total_amount FROM bills WHERE patient_id=:pid AND status='unpaid' ORDER BY created_at DESC LIMIT 1");
    $b->execute([':pid'=>$pid]);
    $bill = $b->fetch(PDO::FETCH_ASSOC);
    if ($bill) {
        $pdo->prepare("UPDATE bills SET total_amount=total_amount+:c WHERE id=:id")->execute([':c'=>$cost,':id'=>$bill['id']]);
    } else {
        $pdo->prepare("INSERT INTO bills (patient_id,total_amount,status) VALUES (:pid,:tot,'unpaid')")
            ->execute([':pid'=>$pid,':tot'=>$cost]);
    }
}

echo json_encode([
  'ok'=>true,
  'msg'=>'Treatment saved.',
  'status'=>'treatment'
]);
