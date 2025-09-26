<?php
// auth/logout.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (isset($_SESSION['user'])) {
    try {
        // Attempt to write logout event to audit log
        audit_log(
            $pdo,
            $_SESSION['user']['id'],
            'logout',
            json_encode(['ip' => $_SERVER['REMOTE_ADDR']])
        );
    } catch (Exception $e) {
        // Fail-safe: log to PHP error log instead of breaking logout
        error_log("Audit log failed on logout: " . $e->getMessage());
    }
}

// Destroy session
session_unset();
session_destroy();

// Redirect to home
header("Location: /healsync/index.php");
exit;
