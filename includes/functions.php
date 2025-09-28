<?php
// includes/functions.php
// Safe helper functions used across the app.

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        // 'cookie_secure' => true, // enable on HTTPS
    ]);
}

/**
 * Escape output (XSS-safe) - standardized helper
 */
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Escape output for HTML attributes
 */
function e_attr($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * CSRF helpers (keeps the token name compatible with existing code)
 */
function csrf() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf($token) {
    return !empty($_SESSION['csrf_token']) && !empty($token) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * JSON response helper - standardized API responses
 */
function json_response($data, int $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Success JSON response
 */
function json_success($message, $data = null) {
    $response = ['ok' => true, 'msg' => $message];
    if ($data !== null) $response['data'] = $data;
    json_response($response);
}

/**
 * Error JSON response
 */
function json_error($message, $status = 400) {
    json_response(['ok' => false, 'msg' => $message], $status);
}

/**
 * Basic validators and formatters
 */
function valid_phone($phone) {
    return preg_match('/^[0-9\-\+\s\(\)]+$/', $phone);
}

/**
 * Format datetime consistently across the app
 */
function format_datetime($dt) {
    return $dt ? date('d M Y, H:i', strtotime($dt)) : '';
}

/**
 * Format date only
 */
function format_date($dt) {
    return $dt ? date('d M Y', strtotime($dt)) : '';
}

/**
 * Format time only
 */
function format_time($dt) {
    return $dt ? date('H:i', strtotime($dt)) : '';
}

/**
 * Format money consistently
 */
function money($amount, $currency = '₹') {
    return $currency . number_format($amount, 2);
}

/**
 * Safe date formatting for PDFs
 */
function safe_date($dt) {
    return $dt ? date('d M Y', strtotime($dt)) : 'N/A';
}

/**
 * Token / password reset helpers (DB-aware)
 */
function generate_token(int $bytes = 24) {
    return bin2hex(random_bytes($bytes));
}

function create_password_reset(PDO $pdo, string $email, int $hours_valid = 1): string {
    $token = generate_token(24);
    $expires = (new DateTime())->modify("+{$hours_valid} hours")->format('Y-m-d H:i:s');

    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = :email");
    $stmt->execute([':email' => $email]);

    $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires)");
    $stmt->execute([':email' => $email, ':token' => $token, ':expires' => $expires]);

    return $token;
}

function get_password_reset(PDO $pdo, string $token) {
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = :token LIMIT 1");
    $stmt->execute([':token' => $token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) return false;
    if (new DateTime($row['expires_at']) < new DateTime()) {
        $del = $pdo->prepare("DELETE FROM password_resets WHERE id = :id");
        $del->execute([':id' => $row['id']]);
        return false;
    }
    return $row;
}

function consume_password_reset(PDO $pdo, int $id) {
    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE id = :id");
    $stmt->execute([':id' => $id]);
}

/**
 * Simulated email helper for development
 */
function send_simulated_email($to, $subject, $body) {
    error_log("Simulated email to: {$to}\nSubject: {$subject}\n\n{$body}\n");
    return true;
}

/**
 * Audit logging helper (non-blocking) - standardized logging
 */
function audit_log(PDO $pdo, int $user_id, string $action, $details = null) {
    try {
        $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, created_at) VALUES (:uid, :action, :details, NOW())");
        $stmt->execute([
            ':uid'     => $user_id,
            ':action'  => $action,
            ':details' => is_string($details) ? $details : json_encode($details)
        ]);
    } catch (Exception $e) {
        // swallow and log - don't break UX
        error_log("audit_log fail: " . $e->getMessage());
    }
}

/**
 * Log sensitive actions with IP tracking
 */
function audit_sensitive(PDO $pdo, int $user_id, string $action, $details = null) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $log_details = [
        'ip' => $ip,
        'user_agent' => $user_agent,
        'timestamp' => date('Y-m-d H:i:s'),
        'details' => $details
    ];
    
    audit_log($pdo, $user_id, $action, $log_details);
}

/**
 * Flash helpers (store short session messages)
 */
function flash_set($key, $message) {
    $_SESSION['flash'][$key] = $message;
}
function flash_get($key) {
    $v = $_SESSION['flash'][$key] ?? null;
    if (isset($_SESSION['flash'][$key])) unset($_SESSION['flash'][$key]);
    return $v;
}
