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
                unset($user['password']);
                $_SESSION['user'] = $user;

                audit_log($pdo, $user['id'], 'login', json_encode(['ip'=>$_SERVER['REMOTE_ADDR']]));

                if ($user['role'] === 'admin') {
                    header("Location: /healsync/admin/dashboard.php");
                } elseif ($user['role'] === 'doctor') {
                    header("Location: /healsync/doctor/dashboard.php");
                } elseif ($user['role'] === 'receptionist') {
                    header("Location: /healsync/reception/dashboard.php");
                } else {
                    header("Location: /healsync/patient/dashboard.php");
                }
                exit;
            } else {
                $errors[] = "Invalid credentials.";
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Login - Healsync</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-indigo-50 via-white to-blue-50">
  <div class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8">
      
      <!-- Logo -->
      <div class="text-center mb-6">
        <img src="/healsync/assets/img/logo.png" class="h-12 w-12 mx-auto mb-3" alt="Healsync"/>
        <h2 class="text-2xl font-bold text-gray-900">Welcome Back</h2>
        <p class="text-gray-500 text-sm">Sign in to access your account</p>
      </div>

      <!-- Errors -->
      <?php if (!empty($errors)): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-5 text-sm">
          <?php foreach($errors as $e): ?>
            <p>• <?= e($e) ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Form -->
      <form method="post" class="space-y-5" novalidate>
        <input type="hidden" name="csrf" value="<?= csrf() ?>">

        <!-- Email -->
        <div>
          <label class="block text-sm font-medium mb-1">Email</label>
          <input name="email" type="email" required 
                 class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"/>
        </div>

        <!-- Password with toggle -->
        <div>
          <label class="block text-sm font-medium mb-1">Password</label>
          <div class="relative">
            <input id="password" name="password" type="password" required
                  class="w-full border rounded-lg px-3 py-2 pr-10 focus:ring-2 focus:ring-indigo-500"/>
            <button type="button" id="togglePassword"
                    class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-indigo-600">
              <i data-lucide="eye"></i>
            </button>
          </div>
        </div>





        <!-- Forgot password -->
        <div class="flex items-center justify-between text-sm">
          <a href="forgot_password.php" class="text-indigo-600 hover:underline">Forgot password?</a>
        </div>

        <!-- Submit -->
        <button class="w-full mt-2 px-4 py-3 bg-indigo-600 text-white rounded-lg font-medium 
                       hover:bg-indigo-700 shadow-md transition transform hover:scale-[1.02]">
          Sign In
        </button>
      </form>

      <!-- Footer -->
      <p class="text-center text-sm text-gray-600 mt-6">
        Don’t have an account? 
        <a href="register_patient.php" class="text-indigo-600 hover:underline">Register</a>
      </p>
    </div>
  </div>

  <!-- Password toggle script -->
<script src="https://unpkg.com/lucide@latest"></script>
<script>
  lucide.createIcons();

  const toggleBtn = document.getElementById("togglePassword");
  const passwordInput = document.getElementById("password");

  toggleBtn.addEventListener("click", () => {
    if (passwordInput.type === "password") {
      passwordInput.type = "text";
      toggleBtn.innerHTML = '<i data-lucide="eye-off"></i>'; // 👁‍🗨 Eye-off
    } else {
      passwordInput.type = "password";
      toggleBtn.innerHTML = '<i data-lucide="eye"></i>'; // 👁 Eye
    }
    lucide.createIcons(); // re-render icon
  });
</script>



</body>
</html>
