<?php
// auth/logout.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
if (isset($_SESSION['user'])) {
    audit_log($pdo, $_SESSION['user']['id'], 'logout', json_encode(['ip'=>$_SERVER['REMOTE_ADDR']]));
}
session_unset();
session_destroy();
header("Location: /healsync/index.php");
exit;
