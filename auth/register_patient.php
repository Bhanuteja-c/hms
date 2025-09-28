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
        $confirm  = $_POST['confirm_password'] ?? '';
        $phone    = trim($_POST['phone'] ?? '');
        $address  = trim($_POST['address'] ?? '');
        $dob      = $_POST['dob'] ?? null;
        $gender   = $_POST['gender'] ?? 'other';

        if (!$name) $errors[] = "Name required.";
        if (!$email) $errors[] = "Valid email required.";
        if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
        if ($password !== $confirm) $errors[] = "Passwords do not match.";
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
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    /* Tooltip styling */
    .tooltip {
      position: relative;
      display: inline-block;
      cursor: pointer;
    }
    .tooltip .tooltip-text {
      visibility: hidden;
      width: 220px;
      background-color: #1f2937;
      color: #fff;
      text-align: left;
      border-radius: 6px;
      padding: 8px;
      position: absolute;
      z-index: 1;
      bottom: 125%; 
      left: 50%;
      margin-left: -110px;
      opacity: 0;
      transition: opacity 0.3s;
      font-size: 0.75rem;
    }
    .tooltip .tooltip-text::after {
      content: "";
      position: absolute;
      top: 100%;
      left: 50%;
      margin-left: -5px;
      border-width: 5px;
      border-style: solid;
      border-color: #1f2937 transparent transparent transparent;
    }
    .tooltip:hover .tooltip-text {
      visibility: visible;
      opacity: 1;
    }
  </style>
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
      <form method="post" class="space-y-4" novalidate>
        <input type="hidden" name="csrf" value="<?= csrf() ?>">

        <div>
          <label class="block text-sm font-medium mb-1">Full Name</label>
          <input name="name" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"/>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Email</label>
          <input name="email" type="email" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"/>
        </div>

        <!-- Password -->
        <div>
          <label class="block text-sm font-medium mb-1 flex items-center gap-2">
            Password
            <span class="tooltip text-indigo-600 text-xs">
              <i data-lucide="info"></i>
              <span class="tooltip-text">
                🔒 Password Requirements:<br>
                • At least 6 characters<br>
                • 1 uppercase letter<br>
                • 1 number<br>
                • 1 special character
              </span>
            </span>
          </label>
          <div class="relative">
            <input id="password" name="password" type="password" required minlength="6"
                   class="w-full border rounded-lg px-3 py-2 pr-10 focus:ring-2 focus:ring-indigo-500"/>
            <button type="button" id="togglePassword"
                    class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-indigo-600">
              <i data-lucide="eye"></i>
            </button>
          </div>
          <div class="w-full h-2 bg-gray-200 rounded mt-2">
            <div id="strengthBar" class="h-2 rounded transition-all duration-300"></div>
          </div>
          <p id="strengthMessage" class="mt-1 text-xs font-medium"></p>
        </div>

        <!-- Confirm Password -->
        <div>
          <label class="block text-sm font-medium mb-1">Confirm Password</label>
          <div class="relative">
            <input id="confirm_password" name="confirm_password" type="password" required minlength="6"
                   class="w-full border rounded-lg px-3 py-2 pr-10 focus:ring-2 focus:ring-indigo-500"/>
            <button type="button" id="toggleConfirmPassword"
                    class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-indigo-600">
              <i data-lucide="eye"></i>
            </button>
          </div>
          <p id="matchMessage" class="mt-1 text-xs font-medium"></p>
        </div>

        <!-- Phone & DOB -->
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

        <!-- Address -->
        <div>
          <label class="block text-sm font-medium mb-1">Address</label>
          <textarea name="address" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"></textarea>
        </div>

        <!-- Gender -->
        <div>
          <label class="block text-sm font-medium mb-1">Gender</label>
          <select name="gender" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
            <option value="other">Prefer not to say</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
          </select>
        </div>

        <!-- Submit -->
        <button id="submitBtn" disabled
                class="w-full mt-2 px-4 py-3 bg-indigo-600 text-white rounded-lg font-medium opacity-50 cursor-not-allowed transition">
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

  <!-- JS -->
  <script>
    lucide.createIcons();

    function setupToggle(toggleId, inputId) {
      const toggleBtn = document.getElementById(toggleId);
      const input = document.getElementById(inputId);

      toggleBtn.addEventListener("click", () => {
        if (input.type === "password") {
          input.type = "text";
          toggleBtn.innerHTML = '<i data-lucide="eye-off"></i>';
        } else {
          input.type = "password";
          toggleBtn.innerHTML = '<i data-lucide="eye"></i>';
        }
        lucide.createIcons();
      });
    }

    setupToggle("togglePassword", "password");
    setupToggle("toggleConfirmPassword", "confirm_password");

    const passwordInput = document.getElementById("password");
    const confirmInput = document.getElementById("confirm_password");
    const strengthBar = document.getElementById("strengthBar");
    const strengthMsg = document.getElementById("strengthMessage");
    const matchMsg = document.getElementById("matchMessage");
    const submitBtn = document.getElementById("submitBtn");

    let strengthLevel = 0;

    function checkStrength() {
      const val = passwordInput.value;
      let strength = 0;

      if (val.length >= 6) strength++;
      if (/[A-Z]/.test(val)) strength++;
      if (/[0-9]/.test(val)) strength++;
      if (/[^A-Za-z0-9]/.test(val)) strength++;

      strengthLevel = strength;

      strengthBar.className = "h-2 rounded transition-all duration-300";

      if (strength === 0) {
        strengthBar.style.width = "0%";
        strengthMsg.textContent = "";
      } else if (strength <= 2) {
        strengthBar.style.width = "40%";
        strengthBar.classList.add("bg-red-500");
        strengthMsg.textContent = "Weak password";
        strengthMsg.className = "mt-1 text-xs font-medium text-red-600";
      } else if (strength === 3) {
        strengthBar.style.width = "70%";
        strengthBar.classList.add("bg-yellow-500");
        strengthMsg.textContent = "Medium strength password";
        strengthMsg.className = "mt-1 text-xs font-medium text-yellow-600";
      } else {
        strengthBar.style.width = "100%";
        strengthBar.classList.add("bg-green-500");
        strengthMsg.textContent = "Strong password";
        strengthMsg.className = "mt-1 text-xs font-medium text-green-600";
      }

      checkFormReady();
    }

    function checkMatch() {
      if (!confirmInput.value) {
        matchMsg.textContent = "";
        return;
      }
      if (passwordInput.value === confirmInput.value) {
        matchMsg.textContent = "✅ Passwords match";
        matchMsg.className = "mt-1 text-xs font-medium text-green-600";
      } else {
        matchMsg.textContent = "❌ Passwords do not match";
        matchMsg.className = "mt-1 text-xs font-medium text-red-600";
      }
      checkFormReady();
    }

    function checkFormReady() {
      if (strengthLevel >= 3 && passwordInput.value === confirmInput.value && confirmInput.value !== "") {
        submitBtn.disabled = false;
        submitBtn.classList.remove("opacity-50", "cursor-not-allowed");
      } else {
        submitBtn.disabled = true;
        submitBtn.classList.add("opacity-50", "cursor-not-allowed");
      }
    }

    passwordInput.addEventListener("input", () => {
      checkStrength();
      checkMatch();
    });

    confirmInput.addEventListener("input", checkMatch);
  </script>
</body>
</html>
