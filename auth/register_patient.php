<?php
// auth/register_patient.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $errors[] = "Invalid CSRF token.";
    } else {
        $name     = trim($_POST['name'] ?? '');
        $email    = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
        $phone    = trim($_POST['phone'] ?? '');
        $address  = trim($_POST['address'] ?? '');
        $dob      = $_POST['dob'] ?? null;
        $gender   = $_POST['gender'] ?? 'other';

        if (!$name) $errors[] = "Name required.";
        if (!$email) $errors[] = "Valid email required.";
        if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
        if ($phone && !valid_phone($phone)) $errors[] = "Invalid phone.";

        // Ensure unique email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email=:email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) $errors[] = "Email already in use.";

        if (empty($errors)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO users (role, name, email, password, phone, address, dob, gender) 
                VALUES ('patient', :name, :email, :pw, :phone, :address, :dob, :gender)
            ");
            $stmt->execute([
                ':name'    => $name,
                ':email'   => $email,
                ':pw'      => $hash,
                ':phone'   => $phone,
                ':address' => $address,
                ':dob'     => $dob,
                ':gender'  => $gender
            ]);
            $uid = $pdo->lastInsertId();

            // Create patient record
            $pdo->prepare("INSERT INTO patients (id, medical_history) VALUES (:id,'')")
                ->execute([':id' => $uid]);

            audit_log($pdo, $uid, 'patient_registered', json_encode(['email' => $email]));
            header("Location: login.php");
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Register - Patient - Healsync</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-indigo-50 via-white to-blue-50">
  <div class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-lg bg-white rounded-2xl shadow-xl p-8">
      
      <!-- Heading -->
      <div class="text-center mb-6">
        <img src="/healsync/assets/img/logo.png" class="h-12 w-12 mx-auto mb-3" alt="Healsync"/>
        <h2 class="text-2xl font-bold text-gray-900">Create a Patient Account</h2>
        <p class="text-gray-500 text-sm">Join Healsync to manage appointments and records</p>
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
      <form method="post" class="space-y-4">
        <input type="hidden" name="csrf" value="<?= csrf() ?>">

        <div>
          <label class="block text-sm font-medium mb-1">Full Name</label>
          <input name="name" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"/>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Email</label>
          <input name="email" type="email" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"/>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Password</label>
          <input name="password" type="password" required minlength="6" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"/>
          <p class="text-xs text-gray-500 mt-1">Must be at least 6 characters long</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Phone</label>
            <input name="phone" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"/>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Date of Birth</label>
            <input name="dob" type="date" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"/>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Address</label>
          <textarea name="address" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"></textarea>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Gender</label>
          <select name="gender" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
            <option value="other">Prefer not to say</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
          </select>
        </div>

        <button class="w-full mt-2 px-4 py-3 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 shadow-md transition transform hover:scale-[1.02]">
          Create Account
        </button>
      </form>

      <!-- Footer -->
      <p class="text-center text-sm text-gray-600 mt-6">
        Already registered?
        <a href="login.php" class="text-indigo-600 hover:underline">Login here</a>
      </p>
    </div>
  </div>
</body>
</html>
