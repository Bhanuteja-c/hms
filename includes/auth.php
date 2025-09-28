<?php
// includes/auth.php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

/**
 * Check if user is logged in with session validation
 */
function is_logged_in(): bool {
    if (empty($_SESSION['user']) || empty($_SESSION['user']['id'])) {
        return false;
    }
    
    // Check session timeout (2 hours)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 7200)) {
        session_destroy();
        return false;
    }
    
    // Update last activity
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Require user to be logged in, redirect if not
 */
function require_login() {
    if (!is_logged_in()) {
        // Store intended URL for redirect after login
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
        }
        header("Location: " . BASE_URL . "/auth/login.php");
        exit;
    }
}

/**
 * Get current user ID
 */
function current_user_id() {
    return $_SESSION['user']['id'] ?? null;
}

/**
 * Get current user role
 */
function current_user_role() {
    return $_SESSION['user']['role'] ?? null;
}

/**
 * Get current user name
 */
function current_user_name() {
    return $_SESSION['user']['name'] ?? 'Guest';
}

/**
 * Get current user email
 */
function current_user_email() {
    return $_SESSION['user']['email'] ?? null;
}

/**
 * Require specific role(s) - accepts string role or array of roles
 */
function require_role($role) {
    require_login();
    $roleNow = current_user_role();
    
    if (is_array($role)) {
        if (!in_array($roleNow, $role, true)) {
            show_access_denied();
        }
    } else {
        if ($roleNow !== $role) {
            show_access_denied();
        }
    }
}

/**
 * Show access denied page with proper styling
 */
function show_access_denied() {
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Access Denied - Healsync</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://unpkg.com/lucide@latest"></script>
    </head>
    <body class="bg-gray-100 min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-lg text-center max-w-md">
            <i data-lucide="shield-x" class="w-16 h-16 text-red-500 mx-auto mb-4"></i>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Access Denied</h1>
            <p class="text-gray-600 mb-6">You don't have permission to access this page.</p>
            <div class="space-y-2">
                <a href="<?= BASE_URL ?>/auth/login.php" class="block px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                    Go to Login
                </a>
                <a href="javascript:history.back()" class="block px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                    Go Back
                </a>
            </div>
        </div>
        <script>lucide.createIcons();</script>
    </body>
    </html>
    <?php
    exit;
}

/**
 * Regenerate session ID for security
 */
function regenerate_session() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

/**
 * Check if user has specific role
 */
function has_role($role): bool {
    if (!is_logged_in()) return false;
    return current_user_role() === $role;
}

/**
 * Check if user has any of the specified roles
 */
function has_any_role(array $roles): bool {
    if (!is_logged_in()) return false;
    return in_array(current_user_role(), $roles, true);
}
