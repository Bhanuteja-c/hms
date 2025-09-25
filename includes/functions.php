<?php
// includes/functions.php

/**
 * Sanitize output (XSS safe)
 */
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * CSRF Token Helpers
 */
function csrf() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf($token) {
    return hash_equals($_SESSION['csrf_token'] ?? '', $token ?? '');
}

/**
 * Validate phone number (basic)
 */
function valid_phone($phone) {
    return preg_match('/^[0-9\-\+\s\(\)]+$/', $phone);
}

/**
 * Format datetime for display
 */
function format_datetime($dt) {
    return date('d M Y, H:i', strtotime($dt));
}

/**
 * Generate a secure random token (hex)
 */
function generate_token($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Create password reset token for a user
 */
function create_password_reset(PDO $pdo, string $email, int $hours_valid = 1): string {
    $token = generate_token(24); // 48 hex chars
    $expires = (new DateTime())->modify("+{$hours_valid} hours")->format('Y-m-d H:i:s');

    // Delete old tokens for this email
    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = :email");
    $stmt->execute([':email' => $email]);

    $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) 
                           VALUES (:email, :token, :expires)");
    $stmt->execute([
        ':email'   => $email,
        ':token'   => $token,
        ':expires' => $expires
    ]);

    return $token;
}

/**
 * Validate a reset token
 */
function get_password_reset(PDO $pdo, string $token) {
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = :token LIMIT 1");
    $stmt->execute([':token' => $token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) return false;

    // Check expiry
    if (new DateTime($row['expires_at']) < new DateTime()) {
        $del = $pdo->prepare("DELETE FROM password_resets WHERE id = :id");
        $del->execute([':id' => $row['id']]);
        return false;
    }

    return $row;
}

/**
 * Consume (delete) a reset token
 */
function consume_password_reset(PDO $pdo, int $id) {
    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE id = :id");
    $stmt->execute([':id' => $id]);
}

/**
 * Utility: send simulated email (for dev)
 */
function send_simulated_email($to, $subject, $body) {
    error_log("Simulated email to: {$to}\nSubject: {$subject}\n\n{$body}\n");
    return true;
}

/**
 * Audit log helper
 */
function audit_log(PDO $pdo, int $user_id, string $action, $details = null) {
    $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, created_at) 
                           VALUES (:uid, :action, :details, NOW())");
    $stmt->execute([
        ':uid'     => $user_id,
        ':action'  => $action,
        ':details' => $details
    ]);
}
