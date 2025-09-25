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
        $name = trim($_POST['name'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $dob = $_POST['dob'] ?? null;
        $gender = $_POST['gender'] ?? 'other';

        if (!$name) $errors[] = "Name required.";
        if (!$email) $errors[] = "Valid email required.";
        if (strlen($password) < 6) $errors[] = "Password must be at least 6 chars.";
        if ($phone && !valid_phone($phone)) $errors[] = "Invalid phone.";

        // ensure unique email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email=:email");
        $stmt->execute([':email'=>$email]);
        if ($stmt->fetch()) $errors[] = "Email already in use.";

        if (empty($errors)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (role,name,email,password,phone,address,dob,gender) VALUES ('patient',:name,:email,:pw,:phone,:address,:dob,:gender)");
            $stmt->execute([
                ':name'=>$name,':email'=>$email,':pw'=>$hash,':phone'=>$phone,':address'=>$address,':dob'=>$dob,':gender'=>$gender
            ]);
            $uid = $pdo->lastInsertId();
            // create patient record
            $pdo->prepare("INSERT INTO patients (id, medical_history) VALUES (:id,'')")->execute([':id'=>$uid]);
            audit_log($pdo, $uid, 'patient_registered', json_encode(['email'=>$email]));
            echo "<script>location.href='login.php';</script>";
            exit;
        }
    }
}

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <title>Register - Patient - Healsync</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
  <div class="min-h-screen flex items-center justify-center">
    <div class="max-w-lg w-full bg-white p-6 rounded shadow">
      <h2 class="text-2xl font-bold mb-4">Patient Registration</h2>
      <?php if (!empty($errors)): ?>
        <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
          <?php foreach($errors as $e) echo '<div>'.e($e).'</div>'; ?>
        </div>
      <?php endif; ?>
      <form method="post">
        <input type="hidden" name="csrf" value="<?=csrf()?>">
        <label class="block mb-2">
          <span class="text-sm">Full name</span>
          <input name="name" required class="mt-1 block w-full border rounded p-2" />
        </label>
        <label class="block mb-2">
          <span class="text-sm">Email</span>
          <input name="email" type="email" required class="mt-1 block w-full border rounded p-2" />
        </label>
        <label class="block mb-2">
          <span class="text-sm">Password</span>
          <input name="password" type="password" required class="mt-1 block w-full border rounded p-2" />
        </label>
        <div class="grid grid-cols-2 gap-3">
          <label class="block mb-2">
            <span class="text-sm">Phone</span>
            <input name="phone" class="mt-1 block w-full border rounded p-2" />
          </label>
          <label class="block mb-2">
            <span class="text-sm">DOB</span>
            <input name="dob" type="date" class="mt-1 block w-full border rounded p-2" />
          </label>
        </div>
        <label class="block mb-2">
          <span class="text-sm">Address</span>
          <textarea name="address" class="mt-1 block w-full border rounded p-2"></textarea>
        </label>
        <label class="block mb-4">
          <span class="text-sm">Gender</span>
          <select name="gender" class="mt-1 block w-full border rounded p-2">
            <option value="other">Prefer not to say</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
          </select>
        </label>
        <div class="flex justify-between items-center">
          <button class="px-4 py-2 bg-green-600 text-white rounded">Create account</button>
          <a href="login.php" class="text-sm text-blue-600">Already have an account?</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
