<?php
// includes/config.php

// Start session securely
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        // 'cookie_secure' => true, // enable if using HTTPS
    ]);
}

// Database connection constants
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'healsync');
define('DB_USER', 'root');
define('DB_PASS', ''); // XAMPP default

// Base URL
define('BASE_URL', '/healsync');
