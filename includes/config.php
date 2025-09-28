<?php
// includes/config.php

// Start session (if not started by functions.php)
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        // 'cookie_secure' => true, // enable on HTTPS
    ]);
}

// DB constants — change to match your environment
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'healsync');
define('DB_USER', 'root');
define('DB_PASS', '');

// Base URL for links - adjust for your virtualhost or subfolder
define('BASE_URL', '/healsync');
