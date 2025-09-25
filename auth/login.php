<?php
// auth/login.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/config.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $errors[] = "Invalid CSRF token.";
    } else {
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (!$email) $errors[] = "Invalid email.";
        if (!$password) $errors[] = "Password required.";

        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->execute([':email'=>$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user && password_verify($password, $user['password'])) {
                // login success
                unset($user['password']);
                $_SESSION['user'] = $user;
                audit_log($pdo, $user['id'], 'login', json_encode(['ip'=>$_SERVER['REMOTE_ADDR']]));
                // redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: /healsync/admin/dashboard.php");
                    exit;
                } elseif ($user['role'] === 'doctor') {
                    header("Location: /healsync/doctor/dashboard.php");
                    exit;
                } else {
                    header("Location: /healsync/patient/dashboard.php");
                    exit;
                }
            } else {
                $errors[] = "Invalid credentials.";
            }
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <title>Login - Healsync</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
  <div class="min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white p-6 rounded shadow">
      <h2 class="text-2xl font-bold mb-4">Login</h2>
      <?php if (!empty($errors)): ?>
        <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
          <?php foreach($errors as $e) echo '<div>'.e($e).'</div>'; ?>
        </div>
      <?php endif; ?>
      <form method="post" novalidate>
        <input type="hidden" name="csrf" value="<?=csrf()?>">
        <label class="block mb-2">
          <span class="text-sm">Email</span>
          <input name="email" type="email" required class="mt-1 block w-full border rounded p-2" />
        </label>
        <label class="block mb-4">
          <span class="text-sm">Password</span>
          <input name="password" type="password" required class="mt-1 block w-full border rounded p-2" />
        </label>
        <div class="flex items-center justify-between">
          <button class="px-4 py-2 bg-blue-600 text-white rounded">Sign in</button>
          <a href="forgot_password.php" class="text-sm text-blue-600">Forgot password?</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
