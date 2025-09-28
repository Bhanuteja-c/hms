<?php
// reception/mark_offline_paid.php
require_once __DIR__ . '/../includes/auth.php';
require_role('receptionist');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: offline_payments.php?error=Invalid+request");
    exit;
}

if (!verify_csrf($_POST['csrf'] ?? '')) {
    header("Location: offline_payments.php?error=Invalid+CSRF+token");
    exit;
}

$billId = intval($_POST['bill_id'] ?? 0);
$method = $_POST['method'] ?? 'cash';

try {
    // Fetch bill to confirm
    $stmt = $pdo->prepare("SELECT * FROM bills WHERE id=:id AND status='offline_pending'");
    $stmt->execute([':id' => $billId]);
    $bill = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$bill) {
        header("Location: offline_payments.php?error=Bill+not+found+or+already+paid");
        exit;
    }

    $pdo->beginTransaction();

    // Update bill
    $pdo->prepare("UPDATE bills SET status='paid', paid_at=NOW() WHERE id=:id")
        ->execute([':id' => $billId]);

    // Insert payment record
    $txId = 'OFFLINE-' . strtoupper($method) . '-' . time();
    $pdo->prepare("INSERT INTO payments (bill_id, amount, method, transaction_id, created_at) 
                   VALUES (:bid, :amt, :method, :tx, NOW())")
        ->execute([
            ':bid'    => $billId,
            ':amt'    => $bill['total_amount'],
            ':method' => $method,
            ':tx'     => $txId
        ]);

    // Notify patient
    $pdo->prepare("INSERT INTO notifications (user_id, message, link) VALUES (:uid, :msg, :link)")
        ->execute([
            ':uid'  => $bill['patient_id'],
            ':msg'  => "Your offline payment of ₹" . number_format($bill['total_amount'], 2) . " was received at the reception.",
            ':link' => "/healsync/patient/bills.php"
        ]);

    // Audit log
    audit_log($pdo, current_user_id(), 'offline_payment_received', json_encode([
        'bill_id'    => $billId,
        'method'     => $method,
        'amount'     => $bill['total_amount'],
        'transaction'=> $txId
    ]));

    $pdo->commit();

    header("Location: offline_payments.php?success=Bill+{$billId}+marked+paid");
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("Offline payment error: " . $e->getMessage());
    header("Location: offline_payments.php?error=Payment+failed");
    exit;
}
