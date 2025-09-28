<?php
// patient/cancel_appointment.php
require_once __DIR__ . '/../includes/auth.php';
require_role('patient');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        die("Invalid CSRF token.");
    }

    $aid = intval($_POST['appointment_id'] ?? 0);
    $pid = current_user_id();

    if ($aid > 0) {
        // Ensure appointment belongs to patient and is cancellable
        $stmt = $pdo->prepare("
            SELECT * 
            FROM appointments 
            WHERE id = :id 
              AND patient_id = :pid 
              AND status IN ('pending','approved') 
              AND date_time > NOW()
        ");
        $stmt->execute([':id' => $aid, ':pid' => $pid]);
        $appt = $stmt->fetch();

        if ($appt) {
            $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = :id")
                ->execute([':id' => $aid]);

            audit_log($pdo, $pid, 'cancel_appointment', json_encode(['appointment_id' => $aid]));

            // Notify doctor
            $doctorId = $appt['doctor_id'];
            $msg  = "Appointment on " . date('d M Y H:i', strtotime($appt['date_time'])) . 
                    " was cancelled by the patient.";
            $link = "/healsync/doctor/appointments.php";

            $pdo->prepare("INSERT INTO notifications (user_id, message, link) 
                           VALUES (:uid, :msg, :link)")
                ->execute([':uid' => $doctorId, ':msg' => $msg, ':link' => $link]);
        }
    }
}

header("Location: appointments.php?status=cancelled");
exit;
