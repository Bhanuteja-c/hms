<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('receptionist');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$rid = current_user_id();
$errors = [];
$success = null;

// Fetch user details
$stmt = $pdo->prepare("SELECT id, name, email, phone, address, dob, gender FROM users WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $rid]);
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

        if ($name === '' || $phone === '' || $dob === '' || $gender === '' || $address === '') {
            $errors[] = "All fields are required.";
        }

        if (!$errors) {
            $stmt = $pdo->prepare("UPDATE users SET name=:name, phone=:phone, dob=:dob, gender=:gender, address=:address WHERE id=:id");
            $stmt->execute([
                ':name' => $name,
                ':phone' => $phone,
                ':dob' => $dob,
                ':gender' => $gender,
                ':address' => $address,
                ':id' => $rid
            ]);

            $success = "Profile updated successfully!";
            $user = array_merge($user, compact('name','phone','dob','gender','address'));
        }
    }
}

// Handle password change
if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $errors[] = "Invalid CSRF token.";
    } else {
        $old_pw = $_POST['old_password'] ?? '';
        $new_pw = $_POST['new_password'] ?? '';
        $confirm_pw = $_POST['confirm_password'] ?? '';

        if ($new_pw !== $confirm_pw) {
            $errors[] = "New passwords do not match.";
        } elseif (strlen($new_pw) < 6) {
            $errors[] = "New password must be at least 6 characters.";
        } else {
            // Fetch current hash
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id=:id");
            $stmt->execute([':id' => $rid]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row || !password_verify($old_pw, $row['password'])) {
                $errors[] = "Old password is incorrect.";
            } else {
                $hash = password_hash($new_pw, PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE users SET password=:pw WHERE id=:id")
                    ->execute([':pw' => $hash, ':id' => $rid]);

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
  <title>My Profile - Reception</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    .glass-effect {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    .premium-shadow {
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
  </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 min-h-screen">
  <?php include __DIR__ . '/../includes/sidebar_reception.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300">
    <!-- Header Section -->
    <div class="mb-8">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
            My Profile
          </h1>
          <p class="text-gray-600 mt-1">Manage your reception account settings</p>
        </div>
        <div class="w-12 h-12 bg-gradient-to-br from-teal-500 to-cyan-500 rounded-xl flex items-center justify-center">
          <i data-lucide="user" class="w-6 h-6 text-white"></i>
        </div>
      </div>
    </div>

    <?php if ($errors): ?>
      <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-6">
        <div class="flex items-center gap-2 mb-2">
          <i data-lucide="alert-circle" class="w-5 h-5"></i>
          <span class="font-medium">Error</span>
        </div>
        <?=implode('<br>', array_map('htmlspecialchars', $errors))?>
      </div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="bg-green-50 border border-green-200 text-green-700 p-4 rounded-xl mb-6">
        <div class="flex items-center gap-2">
          <i data-lucide="check-circle" class="w-5 h-5"></i>
          <span><?=htmlspecialchars($success)?></span>
        </div>
      </div>
    <?php endif; ?>

    <!-- Profile Update -->
    <div class="glass-effect rounded-2xl premium-shadow p-8 mb-8">
      <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 bg-gradient-to-br from-teal-500 to-cyan-600 rounded-xl flex items-center justify-center">
          <i data-lucide="edit-3" class="w-5 h-5 text-white"></i>
        </div>
        <div>
          <h3 class="text-xl font-semibold text-gray-800">Update Profile</h3>
          <p class="text-gray-600 text-sm">Keep your information up to date</p>
        </div>
      </div>
      <form method="post" class="space-y-6">
        <input type="hidden" name="csrf" value="<?=csrf()?>">
        <input type="hidden" name="action" value="update_profile">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="relative">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              <i data-lucide="user" class="w-4 h-4 inline mr-1"></i>
              Full Name
            </label>
            <input type="text" 
                   name="name" 
                   value="<?=e($user['name'])?>" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all duration-200 bg-white/80 backdrop-blur-sm"
                   required>
          </div>
          
          <div class="relative">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              <i data-lucide="mail" class="w-4 h-4 inline mr-1"></i>
              Email (cannot change)
            </label>
            <input type="email" 
                   value="<?=e($user['email'])?>" 
                   disabled 
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-100 text-gray-500 cursor-not-allowed">
          </div>
          
          <div class="relative">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              <i data-lucide="phone" class="w-4 h-4 inline mr-1"></i>
              Phone
            </label>
            <input type="tel" 
                   name="phone" 
                   value="<?=e($user['phone'])?>" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all duration-200 bg-white/80 backdrop-blur-sm"
                   required>
          </div>
          
          <div class="relative">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              <i data-lucide="calendar" class="w-4 h-4 inline mr-1"></i>
              Date of Birth
            </label>
            <input type="date" 
                   name="dob" 
                   value="<?=e($user['dob'])?>" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all duration-200 bg-white/80 backdrop-blur-sm"
                   required>
          </div>
          
          <div class="relative">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              <i data-lucide="users" class="w-4 h-4 inline mr-1"></i>
              Gender
            </label>
            <select name="gender" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all duration-200 bg-white/80 backdrop-blur-sm"
                    required>
              <option value="male" <?=($user['gender']==='male'?'selected':'')?>>Male</option>
              <option value="female" <?=($user['gender']==='female'?'selected':'')?>>Female</option>
              <option value="other" <?=($user['gender']==='other'?'selected':'')?>>Other</option>
            </select>
          </div>
        </div>
        
        <div class="relative">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            <i data-lucide="map-pin" class="w-4 h-4 inline mr-1"></i>
            Address
          </label>
          <textarea name="address" 
                    rows="3"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all duration-200 bg-white/80 backdrop-blur-sm resize-none"
                    placeholder="Enter your full address..."
                    required><?=e($user['address'])?></textarea>
        </div>
        
        <div class="pt-4">
          <button type="submit"
                  class="w-full md:w-auto px-8 py-3 bg-gradient-to-r from-teal-600 to-cyan-600 text-white rounded-xl font-medium flex items-center justify-center gap-2 hover:from-teal-700 hover:to-cyan-700 transform hover:scale-[1.02] transition-all duration-200 premium-shadow">
            <i data-lucide="save" class="w-5 h-5"></i>
            Save Changes
          </button>
        </div>
      </form>
    </div>

    <!-- Change Password -->
    <div class="glass-effect rounded-2xl premium-shadow p-8">
      <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-pink-600 rounded-xl flex items-center justify-center">
          <i data-lucide="shield-check" class="w-5 h-5 text-white"></i>
        </div>
        <div>
          <h3 class="text-xl font-semibold text-gray-800">Change Password</h3>
          <p class="text-gray-600 text-sm">Update your account security</p>
        </div>
      </div>
      <form method="post" class="space-y-6">
        <input type="hidden" name="csrf" value="<?=csrf()?>">
        <input type="hidden" name="action" value="change_password">

        <div class="relative">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            <i data-lucide="lock" class="w-4 h-4 inline mr-1"></i>
            Current Password
          </label>
          <input type="password" 
                 name="old_password" 
                 required 
                 class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200 bg-white/80 backdrop-blur-sm"
                 placeholder="Enter your current password">
        </div>
        
        <div class="relative">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            <i data-lucide="key" class="w-4 h-4 inline mr-1"></i>
            New Password
          </label>
          <input type="password" 
                 name="new_password" 
                 required 
                 minlength="6" 
                 class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200 bg-white/80 backdrop-blur-sm"
                 placeholder="Enter new password (min. 6 characters)">
        </div>
        
        <div class="relative">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            <i data-lucide="shield" class="w-4 h-4 inline mr-1"></i>
            Confirm New Password
          </label>
          <input type="password" 
                 name="confirm_password" 
                 required 
                 minlength="6" 
                 class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200 bg-white/80 backdrop-blur-sm"
                 placeholder="Confirm your new password">
        </div>
        
        <div class="pt-4">
          <button type="submit"
                  class="w-full md:w-auto px-8 py-3 bg-gradient-to-r from-red-600 to-pink-600 text-white rounded-xl font-medium flex items-center justify-center gap-2 hover:from-red-700 hover:to-pink-700 transform hover:scale-[1.02] transition-all duration-200 premium-shadow">
            <i data-lucide="shield-check" class="w-5 h-5"></i>
            Update Password
          </button>
        </div>
      </form>
    </div>
  </main>

  <script>
    lucide.createIcons();
  </script>
</body>
</html>