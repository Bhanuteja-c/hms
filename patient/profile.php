<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('patient');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$pid = current_user_id();
$errors = [];
$success_profile = null;
$success_password = null;

// Fetch user details
$stmt = $pdo->prepare("SELECT id, name, email, phone, address, dob, gender FROM users WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $pid]);
$user = $stmt->fetch();

if (!$user) {
    $errors[] = "User record not found in database. Please contact support.";
}

// Handle profile update
if ($user && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $errors[] = "Invalid CSRF token.";
    } else {
        $name    = trim($_POST['name']);
        $phone   = trim($_POST['phone']);
        $dob     = $_POST['dob'];
        $gender  = $_POST['gender'];
        $address = trim($_POST['address']);

        if ($name === '' || $phone === '' || $dob === '' || $gender === '' || $address === '') {
            $errors[] = "All fields are required.";
        }
        if (!valid_phone($phone)) {
            $errors[] = "Invalid phone number format.";
        }

        if (!$errors) {
            $stmt = $pdo->prepare("
                UPDATE users 
                SET name = :name, phone = :phone, dob = :dob, gender = :gender, address = :address 
                WHERE id = :id
            ");
            $stmt->execute([
                ':name'    => $name,
                ':phone'   => $phone,
                ':dob'     => $dob,
                ':gender'  => $gender,
                ':address' => $address,
                ':id'      => $pid
            ]);

            audit_log($pdo, $pid, 'update_profile', json_encode(['user_id' => $pid]));

            $success_profile = "Profile updated successfully!";
            $user = array_merge($user, compact('name','phone','dob','gender','address'));
        }
    }
}

// Handle password change
if ($user && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $errors[] = "Invalid CSRF token.";
    } else {
        $old_pw     = $_POST['old_password'] ?? '';
        $new_pw     = $_POST['new_password'] ?? '';
        $confirm_pw = $_POST['confirm_password'] ?? '';

        if ($new_pw !== $confirm_pw) {
            $errors[] = "New passwords do not match.";
        } elseif (strlen($new_pw) < 6) {
            $errors[] = "New password must be at least 6 characters.";
        } else {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :id");
            $stmt->execute([':id' => $pid]);
            $row = $stmt->fetch();

            if (!$row || !password_verify($old_pw, $row['password'])) {
                $errors[] = "Old password is incorrect.";
            } else {
                $hash = password_hash($new_pw, PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE users SET password = :pw WHERE id = :id")
                    ->execute([':pw' => $hash, ':id' => $pid]);

                audit_log($pdo, $pid, 'change_password', json_encode(['user_id' => $pid]));

                $success_password = "Password changed successfully!";
            }
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>My Profile - Healsync</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-100">
  <?php include __DIR__ . '/../includes/sidebar_patient.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300">
    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
      <i data-lucide="user" class="w-6 h-6 text-indigo-600"></i>
      My Profile
    </h2>

    <!-- Messages -->
    <?php if ($errors): ?>
      <div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-3">
        <?=implode('<br>', array_map('e', $errors))?>
      </div>
    <?php endif; ?>

    <?php if ($success_profile): ?>
      <div class="bg-green-50 border border-green-200 text-green-700 p-3 rounded mb-3">
        <?=e($success_profile)?>
      </div>
    <?php endif; ?>

    <?php if ($success_password): ?>
      <div class="bg-green-50 border border-green-200 text-green-700 p-3 rounded mb-3">
        <?=e($success_password)?>
      </div>
    <?php endif; ?>

    <?php if ($user): ?>
      <!-- Profile Update -->
      <div class="bg-white p-6 rounded shadow mb-6">
        <h3 class="text-lg font-semibold mb-4">Update Profile</h3>
        <form method="post" class="space-y-4">
          <input type="hidden" name="csrf" value="<?=csrf()?>">
          <input type="hidden" name="action" value="update_profile">

          <div>
            <label class="block text-sm font-medium">Full Name</label>
            <input type="text" name="name" value="<?=e($user['name'])?>" class="w-full border rounded px-3 py-2" required>
          </div>
          <div>
            <label class="block text-sm font-medium">Email (cannot change)</label>
            <input type="email" value="<?=e($user['email'])?>" disabled class="w-full border rounded px-3 py-2 bg-gray-100">
          </div>
          <div>
            <label class="block text-sm font-medium">Phone</label>
            <input type="text" name="phone" value="<?=e($user['phone'])?>" class="w-full border rounded px-3 py-2" required>
          </div>
          <div>
            <label class="block text-sm font-medium">Date of Birth</label>
            <input type="date" name="dob" value="<?=e($user['dob'])?>" class="w-full border rounded px-3 py-2" required>
          </div>
          <div>
            <label class="block text-sm font-medium">Gender</label>
            <select name="gender" class="w-full border rounded px-3 py-2" required>
              <option value="Male" <?=($user['gender']==='Male'?'selected':'')?>>Male</option>
              <option value="Female" <?=($user['gender']==='Female'?'selected':'')?>>Female</option>
              <option value="Other" <?=($user['gender']==='Other'?'selected':'')?>>Other</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium">Address</label>
            <textarea name="address" class="w-full border rounded px-3 py-2" required><?=e($user['address'])?></textarea>
          </div>
          <button class="px-4 py-2 bg-indigo-600 text-white rounded">Save Changes</button>
        </form>
      </div>

      <!-- Change Password -->
      <div class="bg-white p-6 rounded shadow">
        <h3 class="text-lg font-semibold mb-4">Change Password</h3>
        <form method="post" class="space-y-4">
          <input type="hidden" name="csrf" value="<?=csrf()?>">
          <input type="hidden" name="action" value="change_password">

          <div>
            <label class="block text-sm font-medium">Old Password</label>
            <input type="password" name="old_password" required class="w-full border rounded px-3 py-2">
          </div>
          <div>
            <label class="block text-sm font-medium">New Password</label>
            <input type="password" name="new_password" required minlength="6" class="w-full border rounded px-3 py-2">
          </div>
          <div>
            <label class="block text-sm font-medium">Confirm New Password</label>
            <input type="password" name="confirm_password" required minlength="6" class="w-full border rounded px-3 py-2">
          </div>
          <button class="px-4 py-2 bg-green-600 text-white rounded">Change Password</button>
        </form>
      </div>
    <?php endif; ?>
  </main>

  <script>lucide.createIcons();</script>
</body>
</html>
