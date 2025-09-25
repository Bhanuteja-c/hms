<?php
// auth/reset_password.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$errors = [];
$success = null;
$show_form = false;
$token = $_GET['token'] ?? ($_POST['token'] ?? '');

if ($token) {
    // Verify token exists and not expired
    $row = get_password_reset($pdo, $token);
    if (!$row) {
        $errors[] = "Invalid or expired reset token.";
    } else {
        $show_form = true;
    }
} else {
    $errors[] = "Missing token.";
}

// Handle POST: actually reset password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['token'])) {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $errors[] = "Invalid CSRF token.";
    } else {
        $token = $_POST['token'] ?? '';
        $row = get_password_reset($pdo, $token);
        if (!$row) {
            $errors[] = "Invalid or expired token.";
            $show_form = false;
        } else {
            $pw = $_POST['password'] ?? '';
            $pw2 = $_POST['password_confirm'] ?? '';
            if (strlen($pw) < 6) {
                $errors[] = "Password must be at least 6 characters.";
                $show_form = true;
            } elseif ($pw !== $pw2) {
                $errors[] = "Passwords do not match.";
                $show_form = true;
            } else {
                // Update user password (if user still exists)
                $email = $row['email'];
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
                $stmt->execute([':email' => $email]);
                $u = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$u) {
                    $errors[] = "User account not found for this reset request.";
                } else {
                    $hash = password_hash($pw, PASSWORD_DEFAULT);
                    $pdo->prepare("UPDATE users SET password = :pw WHERE id = :id")
                        ->execute([':pw' => $hash, ':id' => $u['id']]);

                    // consume token
                    consume_password_reset($pdo, (int)$row['id']);

                    // optional audit log
                    if (function_exists('audit_log')) {
                        audit_log($pdo, $u['id'], 'password_reset', json_encode(['method'=>'forgot']));
                    }

                    $success = "Password updated. You may now log in.";
                    $show_form = false;
                }
            }
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Reset Password - Healsync</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
  <div class="w-full max-w-md bg-white rounded shadow p-6">
    <h1 class="text-xl font-semibold mb-4">Reset Password</h1>

    <?php if ($errors): ?>
      <div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-3">
        <?=implode('<br>', array_map('htmlspecialchars', $errors))?>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="bg-green-50 border border-green-200 text-green-700 p-3 rounded mb-3">
        <?=htmlspecialchars($success)?>
        <div class="mt-3">
          <a href="/healsync/auth/login.php" class="text-blue-600">Go to login</a>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($show_form && $row): ?>
      <form method="post" class="space-y-3">
        <input type="hidden" name="csrf" value="<?=csrf()?>">
        <input type="hidden" name="token" value="<?=htmlspecialchars($token)?>">
        <label class="block text-sm">
          <span class="text-gray-700">New password</span>
          <input type="password" name="password" required class="mt-1 block w-full border rounded px-3 py-2" minlength="6">
        </label>
        <label class="block text-sm">
          <span class="text-gray-700">Confirm password</span>
          <input type="password" name="password_confirm" required class="mt-1 block w-full border rounded px-3 py-2" minlength="6">
        </label>

        <div class="flex justify-between items-center">
          <a href="/healsync/auth/login.php" class="text-sm text-gray-600">Back to login</a>
          <button class="px-4 py-2 bg-indigo-600 text-white rounded">Set New Password</button>
        </div>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>
