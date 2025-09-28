<?php
// reception/walkin_submit.php
require_once __DIR__ . '/../includes/auth.php';
require_role('receptionist');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// JSON response helper
function jsonOut($arr, $status=200){
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($arr, JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonOut(['ok'=>false,'msg'=>'Invalid method'], 405);
}

$csrf = $_POST['csrf'] ?? '';
if (!verify_csrf($csrf)) jsonOut(['ok'=>false,'msg'=>'Invalid CSRF token'], 403);

$doctor_id = intval($_POST['doctor_id'] ?? 0);
$date_time = trim($_POST['date_time'] ?? '');
$reason    = trim($_POST['reason'] ?? '');

if (!$doctor_id || !$date_time || !$reason) jsonOut(['ok'=>false,'msg'=>'Missing required fields'], 400);

// patient: existing or create new
$patient_id = intval($_POST['patient_id'] ?? 0);
$patient_name = trim($_POST['new_name'] ?? '');
$patient_email = filter_var(trim($_POST['new_email'] ?? ''), FILTER_VALIDATE_EMAIL) ?: null;
$patient_phone = trim($_POST['new_phone'] ?? '');

try {
    $pdo->beginTransaction();

    if ($patient_id <= 0) {
        // create new patient user
        if (!$patient_name) {
            $pdo->rollBack();
            jsonOut(['ok'=>false,'msg'=>'Provide patient or create new patient name'], 400);
        }
        // ensure email uniqueness if provided
        if ($patient_email) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
            $stmt->execute([':email'=>$patient_email]);
            if ($stmt->fetch()) {
                $pdo->rollBack();
                jsonOut(['ok'=>false,'msg'=>'Email already in use. Choose existing patient.'], 409);
            }
        }
        $randPw = bin2hex(random_bytes(6));
        $hash = password_hash($randPw, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (role,name,email,password,phone,created_at) VALUES ('patient',:name,:email,:pw,:phone,NOW())");
        $stmt->execute([':name'=>$patient_name, ':email'=>$patient_email, ':pw'=>$hash, ':phone'=>$patient_phone]);
        $patient_id = $pdo->lastInsertId();
        // create patients table record
        $pdo->prepare("INSERT INTO patients (id, medical_history) VALUES (:id,'')")->execute([':id'=>$patient_id]);
        // (Optionally) you may notify or log the generated password for reception to hand to patient.
    } else {
        // ensure the patient exists & is a patient role
        $stmt = $pdo->prepare("SELECT id, name FROM users WHERE id = :id AND role = 'patient' LIMIT 1");
        $stmt->execute([':id'=>$patient_id]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$u) { $pdo->rollBack(); jsonOut(['ok'=>false,'msg'=>'Selected patient not found'],404); }
        $patient_name = $u['name'];
    }

    // Insert appointment — mark approved and arrived for a walk-in
    $stmt = $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, date_time, reason, status, arrived, arrived_at, created_at) VALUES (:pid,:did,:dt,:reason,'approved',1,NOW(),NOW())");
    $stmt->execute([':pid'=>$patient_id, ':did'=>$doctor_id, ':dt'=>$date_time, ':reason'=>$reason]);

    $apptId = $pdo->lastInsertId();

    // Audit
    audit_log($pdo, current_user_id(), 'walkin_created', json_encode(['appointment_id'=>$apptId, 'patient_id'=>$patient_id]));

    // Notify doctor
    $docStmt = $pdo->prepare("SELECT name FROM users WHERE id=:id LIMIT 1");
    $docStmt->execute([':id'=>$doctor_id]);
    $doc = $docStmt->fetch(PDO::FETCH_ASSOC);
    $doctor_name = $doc['name'] ?? 'Doctor';

    $msg = "Walk-in: {$patient_name} has arrived for appointment on " . date('d M Y H:i', strtotime($date_time));
    $pdo->prepare("INSERT INTO notifications (user_id,message,link) VALUES (:uid,:msg,:link)")->execute([
        ':uid'=>$doctor_id, ':msg'=>$msg, ':link'=>"/healsync/doctor/approved_appointments.php"
    ]);

    $pdo->commit();

    // return JSON with the appointment details
    jsonOut([
        'ok'=>true,
        'msg'=>'Walk-in appointment added and marked arrived.',
        'appointment'=>[
            'id'=>$apptId,
            'patient_id'=>$patient_id,
            'patient_name'=>$patient_name,
            'doctor_id'=>$doctor_id,
            'doctor_name'=>$doctor_name,
            'date_time'=>$date_time,
            'status'=>'approved',
            'arrived'=>1
        ]
    ]);
} catch (Exception $ex) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("walkin_submit error: ".$ex->getMessage());
    jsonOut(['ok'=>false,'msg'=>'Server error'], 500);
}
