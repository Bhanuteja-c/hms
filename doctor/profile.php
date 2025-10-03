<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('doctor');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$did = current_user_id();
$errors = [];
$success = null;

// Fetch user and doctor details
$stmt = $pdo->prepare("
    SELECT u.id, u.name, u.email, u.phone, u.address, u.dob, u.gender, 
           d.specialty
    FROM users u 
    LEFT JOIN doctors d ON u.id = d.id 
    WHERE u.id = :id LIMIT 1
");
$stmt->execute(['id' => $did]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

// Handle profile update
if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $errors[] = "Invalid CSRF token.";
    } else {
        $name = trim($_POST['name']);
        $phone = trim($_POST['phone']);
        $dob = $_POST['dob'];
        $gender = $_POST['gender'];
        $address = trim($_POST['address']);
        $specialty = trim($_POST['specialty']);

        if ($name === '' || $phone === '' || $dob === '' || $gender === '' || $address === '' || $specialty === '') {
            $errors[] = "All required fields must be filled.";
        }

        if (!$errors) {
            try {
                $pdo->beginTransaction();
                
                // Update users table
                $stmt = $pdo->prepare("UPDATE users SET name=:name, phone=:phone, dob=:dob, gender=:gender, address=:address WHERE id=:id");
                $stmt->execute([
                    'name' => $name,
                    'phone' => $phone,
                    'dob' => $dob,
                    'gender' => $gender,
                    'address' => $address,
                    'id' => $did
                ]);

                // Update or insert doctors table
                $stmt = $pdo->prepare("
                    INSERT INTO doctors (id, specialty) 
                    VALUES (:id, :specialty)
                    ON DUPLICATE KEY UPDATE 
                    specialty=:specialty
                ");
                $stmt->execute([
                    'id' => $did,
                    'specialty' => $specialty
                ]);

                $pdo->commit();
                $success = "Profile updated successfully!";
                
                // Refresh user data
                $stmt = $pdo->prepare("
                    SELECT u.id, u.name, u.email, u.phone, u.address, u.dob, u.gender, 
                           d.specialty
                    FROM users u 
                    LEFT JOIN doctors d ON u.id = d.id 
                    WHERE u.id = :id LIMIT 1
                ");
                $stmt->execute(['id' => $did]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = "Error updating profile: " . $e->getMessage();
            }
        }
    }
}

// Handle password change
if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $errors[] = "Invalid CSRF token.";
    } else {
        $old_pw = $_POST['old_password'];
        $new_pw = $_POST['new_password'];
        $confirm_pw = $_POST['confirm_password'];

        if ($old_pw === '' || $new_pw === '' || $confirm_pw === '') {
            $errors[] = "All password fields are required.";
        } elseif ($new_pw !== $confirm_pw) {
            $errors[] = "New passwords do not match.";
        } elseif (strlen($new_pw) < 6) {
            $errors[] = "New password must be at least 6 characters.";
        }

        if (!$errors) {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id=:id");
            $stmt->execute(['id' => $did]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row || !password_verify($old_pw, $row['password'])) {
                $errors[] = "Old password is incorrect.";
            } else {
                $hash = password_hash($new_pw, PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE users SET password=:pw WHERE id=:id")
                    ->execute(['pw' => $hash, 'id' => $did]);

                $success = "Password changed successfully!";
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>My Profile - Healsync</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    .glass-effect {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    .form-card {
      transition: all 0.3s ease;
    }
    .form-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 min-h-screen">
  <?php include __DIR__ . '/../includes/sidebar_doctor.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300">
    <!-- Header Section -->
    <div class="mb-8">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
            My Profile
          </h1>
          <p class="text-gray-600 mt-1">Manage your professional information and account settings</p>
        </div>
        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-500 rounded-xl flex items-center justify-center">
          <i data-lucide="stethoscope" class="w-6 h-6 text-white"></i>
        </div>
      </div>
    </div>

    <!-- Messages -->
    <?php if ($success): ?>
      <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-center gap-2">
        <i data-lucide="check-circle" class="w-5 h-5"></i>
        <?= e($success) ?>
      </div>
    <?php endif; ?>

    <?php if ($errors): ?>
      <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl">
        <div class="flex items-center gap-2 mb-2">
          <i data-lucide="alert-circle" class="w-5 h-5"></i>
          <span class="font-medium">Please fix the following errors:</span>
        </div>
        <ul class="list-disc list-inside space-y-1">
          <?php foreach ($errors as $error): ?>
            <li><?= e($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div class="grid lg:grid-cols-2 gap-8">
      <!-- Profile Information -->
      <div class="form-card glass-effect rounded-2xl border border-white/20 overflow-hidden">
        <div class="p-6 border-b border-gray-200/50">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-xl flex items-center justify-center">
              <i data-lucide="user" class="w-5 h-5 text-white"></i>
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-900">Profile Information</h3>
              <p class="text-gray-600 text-sm">Update your personal and professional details</p>
            </div>
          </div>
        </div>

        <form method="post" class="p-6 space-y-6">
          <input type="hidden" name="csrf" value="<?= csrf() ?>">
          <input type="hidden" name="action" value="update_profile">

          <!-- Personal Information -->
          <div class="space-y-4">
            <h4 class="font-semibold text-gray-900 flex items-center gap-2">
              <i data-lucide="user" class="w-4 h-4 text-indigo-600"></i>
              Personal Information
            </h4>
            
            <div class="grid md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                <input type="text" name="name" value="<?= e($user['name']) ?>" required
                       class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" value="<?= e($user['email']) ?>" disabled
                       class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 text-gray-500">
                <p class="text-xs text-gray-500 mt-1">Email cannot be changed</p>
              </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Phone *</label>
                <input type="tel" name="phone" value="<?= e($user['phone']) ?>" required
                       class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth *</label>
                <input type="date" name="dob" value="<?= e($user['dob']) ?>" required
                       class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors">
              </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Gender *</label>
                <select name="gender" required
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors">
                  <option value="">Select Gender</option>
                  <option value="male" <?= $user['gender'] === 'male' ? 'selected' : '' ?>>Male</option>
                  <option value="female" <?= $user['gender'] === 'female' ? 'selected' : '' ?>>Female</option>
                  <option value="other" <?= $user['gender'] === 'other' ? 'selected' : '' ?>>Other</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Address *</label>
              <textarea name="address" required rows="3"
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"><?= e($user['address']) ?></textarea>
            </div>
          </div>

          <!-- Professional Information -->
          <div class="space-y-4 pt-6 border-t border-gray-200">
            <h4 class="font-semibold text-gray-900 flex items-center gap-2">
              <i data-lucide="stethoscope" class="w-4 h-4 text-green-600"></i>
              Professional Information
            </h4>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Specialty *</label>
              <input type="text" name="specialty" value="<?= e($user['specialty'] ?? '') ?>" required
                     placeholder="e.g., Cardiology, Pediatrics"
                     class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors">
            </div>
          </div>

          <button type="submit" 
                  class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-medium hover:from-indigo-700 hover:to-purple-700 transition-all duration-300 hover:scale-105 shadow-lg">
            <i data-lucide="save" class="w-4 h-4"></i>
            Update Profile
          </button>
        </form>
      </div>

      <!-- Change Password -->
      <div class="form-card glass-effect rounded-2xl border border-white/20 overflow-hidden">
        <div class="p-6 border-b border-gray-200/50">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-pink-500 rounded-xl flex items-center justify-center">
              <i data-lucide="lock" class="w-5 h-5 text-white"></i>
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-900">Change Password</h3>
              <p class="text-gray-600 text-sm">Update your account password</p>
            </div>
          </div>
        </div>

        <form method="post" class="p-6 space-y-6">
          <input type="hidden" name="csrf" value="<?= csrf() ?>">
          <input type="hidden" name="action" value="change_password">

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
            <input type="password" name="old_password" required
                   class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-colors">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
            <input type="password" name="new_password" required minlength="6"
                   class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-colors">
            <p class="text-xs text-gray-500 mt-1">Minimum 6 characters</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
            <input type="password" name="confirm_password" required minlength="6"
                   class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-colors">
          </div>

          <button type="submit" 
                  class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-red-600 to-pink-600 text-white rounded-lg font-medium hover:from-red-700 hover:to-pink-700 transition-all duration-300 hover:scale-105 shadow-lg">
            <i data-lucide="key" class="w-4 h-4"></i>
            Change Password
          </button>
        </form>
      </div>
    </div>
  </main>

  <script>lucide.createIcons();</script>
</body>
</html>
