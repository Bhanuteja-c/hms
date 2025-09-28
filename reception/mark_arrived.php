<?php
// reception/mark_arrived.php
require_once __DIR__ . '/../includes/auth.php';
require_role('receptionist');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// JSON helper
function jsonOut($arr, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($arr, JSON_UNESCAPED_SLASHES);
    exit;
}

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        jsonOut(['ok' => false, 'msg' => 'Invalid method'], 405);
    } else {
        header("Location: dashboard.php?error=Invalid+method");
        exit;
    }
}

// CSRF check
$csrf = $_POST['csrf'] ?? '';
if (!verify_csrf($csrf)) {
    if ($isAjax) {
        jsonOut(['ok' => false, 'msg' => 'Invalid CSRF token'], 403);
    } else {
        header("Location: dashboard.php?error=Invalid+CSRF+token");
        exit;
    }
}

$apptId = intval($_POST['appointment_id'] ?? 0);
if ($apptId <= 0) {
    if ($isAjax) {
        jsonOut(['ok' => false, 'msg' => 'Invalid appointment ID'], 400);
    } else {
        header("Location: dashboard.php?error=Invalid+appointment+ID");
        exit;
    }
}

try {
    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $apptId]);
    $appt = $stmt->fetch();

    if (!$appt) {
        $msg = "Appointment not found";
        if ($isAjax) jsonOut(['ok' => false, 'msg' => $msg], 404);
        header("Location: dashboard.php?error=" . urlencode($msg));
        exit;
    }

    if ($appt['arrived']) {
        $msg = "Already marked as arrived";
        if ($isAjax) jsonOut(['ok' => false, 'msg' => $msg], 409);
        header("Location: dashboard.php?error=" . urlencode($msg));
        exit;
    }

    // Update
    $pdo->prepare("UPDATE appointments SET arrived = 1, arrived_at = NOW() WHERE id = :id")
        ->execute([':id' => $apptId]);

    // Notify doctor
    if (!empty($appt['doctor_id'])) {
        $msgDoc = "Patient has arrived for appointment on " . date('d M Y H:i', strtotime($appt['date_time']));
        $pdo->prepare("INSERT INTO notifications (user_id, message, link) VALUES (:uid,:msg,:link)")
            ->execute([
                ':uid' => $appt['doctor_id'],
                ':msg' => $msgDoc,
                ':link' => "/healsync/doctor/approved_appointments.php"
            ]);
    }

    // Notify patient
    if (!empty($appt['patient_id'])) {
        $msgPat = "You have been marked as arrived for your appointment on " . date('d M Y H:i', strtotime($appt['date_time']));
        $pdo->prepare("INSERT INTO notifications (user_id, message, link) VALUES (:uid,:msg,:link)")
            ->execute([
                ':uid' => $appt['patient_id'],
                ':msg' => $msgPat,
                ':link' => "/healsync/patient/appointments.php"
            ]);
    }

    // Audit log
    audit_log($pdo, current_user_id(), 'mark_arrived', json_encode([
        'appointment_id' => $apptId,
        'status_before'  => $appt['status'],
        'receptionist'   => current_user_id()
    ]));

    if ($isAjax) {
        jsonOut(['ok' => true, 'msg' => 'Appointment marked as arrived']);
    } else {
        header("Location: dashboard.php?arrived=1");
        exit;
    }

} catch (Exception $ex) {
    error_log("mark_arrived error: " . $ex->getMessage());
    if ($isAjax) {
        jsonOut(['ok' => false, 'msg' => 'Server error'], 500);
    } else {
        header("Location: dashboard.php?error=Server+error");
        exit;
    }
}
