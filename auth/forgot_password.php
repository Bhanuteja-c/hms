<?php
// auth/forgot_password.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $errors[] = "Invalid CSRF token.";
    } else {
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        if (!$email) {
            $errors[] = "Enter a valid email.";
        } else {
            // Check if user exists
            $u = $pdo->prepare("SELECT id, email FROM users WHERE email = :email LIMIT 1");
            $u->execute([':email' => $email]);
            $user = $u->fetch(PDO::FETCH_ASSOC);
            if (!$user) {
                // For security, do not reveal whether email exists.
                // But for dev/demo we can still show a generic message.
                $success = "If that email exists, a reset link has been sent.";
            } else {
                // Create token
                $token = create_password_reset($pdo, $email, 1); // 1 hour validity

                // Create reset URL
                // Adjust base path if your app is in a subfolder
                // Build reset URL correctly (no duplicate "auth")
                $resetUrl = sprintf(
                    "%s://%s%s/reset_password.php?token=%s",
                    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http',
                    $_SERVER['HTTP_HOST'],
                    dirname($_SERVER['SCRIPT_NAME']),
                    $token
                );

                // Simulated email content
                $subject = "Healsync password reset";
                $body = "Hello,\n\nA password reset was requested for your Healsync account.\n\n";
                $body .= "Click the link below to reset your password (valid 1 hour):\n\n";
                $body .= $resetUrl . "\n\nIf you did not request this, ignore this email.\n\nThanks,\nHealsync Team";

                // Send (simulated)
                send_simulated_email($email, $subject, $body);

                // For demo, show success and the link (remove in production)
                $success = "If that email exists, a reset link has been sent. (In dev the link is shown below.)";
                $success_link = $resetUrl;
            }
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Forgot Password - Healsync</title>
  <script src="/healsync/assets/js/tailwind-cdn.js"></script>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
  <div class="w-full max-w-md bg-white rounded shadow p-6">
    <h1 class="text-xl font-semibold mb-4">Forgot Password</h1>

    <?php if ($errors): ?>
      <div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-3">
        <?=implode('<br>', array_map('htmlspecialchars', $errors))?>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="bg-green-50 border border-green-200 text-green-700 p-3 rounded mb-3">
        <?=htmlspecialchars($success)?>
        <?php if (!empty($success_link)): ?>
          <div class="mt-2 text-sm">
            <strong>Debug/reset link (development):</strong><br>
            <a class="text-blue-600 break-all" href="<?=htmlspecialchars($success_link)?>"><?=htmlspecialchars($success_link)?></a>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <form method="post" class="space-y-3">
      <input type="hidden" name="csrf" value="<?=csrf()?>">
      <label class="block text-sm">
        <span class="text-gray-700">Email</span>
        <input type="email" name="email" required class="mt-1 block w-full border rounded px-3 py-2" placeholder="your@email.com">
      </label>

      <div class="flex justify-between items-center">
        <a href="/healsync/auth/login.php" class="text-sm text-gray-600">Back to login</a>
        <button class="px-4 py-2 bg-indigo-600 text-white rounded">Send Reset Link</button>
      </div>
    </form>
  </div>
</body>
</html>
