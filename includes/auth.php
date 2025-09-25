<?php
// includes/auth.php
require_once __DIR__ . '/db.php';

function is_logged_in() {
    return isset($_SESSION['user']);
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: /healsync/auth/login.php");
        exit;
    }
}

function current_user_id() {
    return $_SESSION['user']['id'] ?? null;
}

function current_user_role() {
    return $_SESSION['user']['role'] ?? null;
}

function current_user_name() {
    return $_SESSION['user']['name'] ?? 'Guest';
}

function require_role($role) {
    require_login();
    if (current_user_role() !== $role) {
        http_response_code(403);
        echo "Forbidden - insufficient privileges";
        exit;
    }
}
