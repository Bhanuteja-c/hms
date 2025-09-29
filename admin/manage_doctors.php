<?php
// admin/manage_doctors.php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$message = '';
$action = $_GET['action'] ?? 'list';
$id = intval($_GET['id'] ?? 0);
$search = trim($_GET['search'] ?? '');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $message = "<div class='bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-4'>Invalid CSRF token.</div>";
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $dob = $_POST['dob'] ?? '';
        $gender = $_POST['gender'] ?? 'other';
        $specialty = trim($_POST['specialty'] ?? '');

        if ($action === 'add') {
            if (!$name || !$email || !$password || !$specialty) {
                $message = "<div class='bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-4'>Name, email, password, and specialty are required.</div>";
            } else {
                // Check if email exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
                $stmt->execute([':email' => $email]);
                if ($stmt->fetch()) {
                    $message = "<div class='bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-4'>Email already exists.</div>";
                } else {
                    try {
                        $pdo->beginTransaction();
                        
                        // Create doctor user
                        $hash = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("
                            INSERT INTO users (role, name, email, password, phone, address, dob, gender, created_at) 
                            VALUES ('doctor', :name, :email, :password, :phone, :address, :dob, :gender, NOW())
                        ");
                        $stmt->execute([
                            ':name' => $name,
                            ':email' => $email,
                            ':password' => $hash,
                            ':phone' => $phone,
                            ':address' => $address,
                            ':dob' => $dob ?: null,
                            ':gender' => $gender
                        ]);
                        $doctor_id = $pdo->lastInsertId();

                        // Create doctor profile
                        $stmt = $pdo->prepare("INSERT INTO doctors (id, specialty, availability, created_at) VALUES (:id, :specialty, :availability, NOW())");
                        $stmt->execute([
                            ':id' => $doctor_id,
                            ':specialty' => $specialty,
                            ':availability' => json_encode([
                                'monday' => ['start' => '09:00', 'end' => '17:00'],
                                'tuesday' => ['start' => '09:00', 'end' => '17:00'],
                                'wednesday' => ['start' => '09:00', 'end' => '17:00'],
                                'thursday' => ['start' => '09:00', 'end' => '17:00'],
                                'friday' => ['start' => '09:00', 'end' => '15:00']
                            ])
                        ]);

                        $pdo->commit();

                        audit_sensitive($pdo, current_user_id(), 'doctor_created', [
                            'doctor_email' => $email,
                            'doctor_name' => $name,
                            'specialty' => $specialty
                        ]);

                        $message = "<div class='bg-green-50 border border-green-200 text-green-700 p-3 rounded mb-4'>Doctor created successfully.</div>";
                        $action = 'list';
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $message = "<div class='bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-4'>Error creating doctor: " . e($e->getMessage()) . "</div>";
                    }
                }
            }
        } elseif ($action === 'edit' && $id) {
            if (!$name || !$email || !$specialty) {
                $message = "<div class='bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-4'>Name, email, and specialty are required.</div>";
            } else {
                // Check if email exists for other users
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
                $stmt->execute([':email' => $email, ':id' => $id]);
                if ($stmt->fetch()) {
                    $message = "<div class='bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-4'>Email already exists.</div>";
                } else {
                    try {
                        $pdo->beginTransaction();
                        
                        $updateFields = "name = :name, email = :email, phone = :phone, address = :address, dob = :dob, gender = :gender";
                        $params = [
                            ':name' => $name,
                            ':email' => $email,
                            ':phone' => $phone,
                            ':address' => $address,
                            ':dob' => $dob ?: null,
                            ':gender' => $gender,
                            ':id' => $id
                        ];

                        // Update password if provided
                        if ($password) {
                            $updateFields .= ", password = :password";
                            $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
                        }

                        $stmt = $pdo->prepare("UPDATE users SET $updateFields WHERE id = :id AND role = 'doctor'");
                        $stmt->execute($params);

                        // Update doctor specialty
                        $stmt = $pdo->prepare("UPDATE doctors SET specialty = :specialty WHERE id = :id");
                        $stmt->execute([':specialty' => $specialty, ':id' => $id]);

                        $pdo->commit();

                        audit_sensitive($pdo, current_user_id(), 'doctor_updated', [
                            'doctor_id' => $id,
                            'doctor_email' => $email,
                            'doctor_name' => $name,
                            'specialty' => $specialty
                        ]);

                        $message = "<div class='bg-green-50 border border-green-200 text-green-700 p-3 rounded mb-4'>Doctor updated successfully.</div>";
                        $action = 'list';
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $message = "<div class='bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-4'>Error updating doctor: " . e($e->getMessage()) . "</div>";
                    }
                }
            }
        }
    }
}

// Handle delete
if ($action === 'delete' && $id) {
    $stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = :id AND role = 'doctor'");
    $stmt->execute([':id' => $id]);
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($doctor) {
        try {
            $pdo->beginTransaction();
            
            // Check for existing appointments
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = :id");
            $stmt->execute([':id' => $id]);
            $appointmentCount = $stmt->fetchColumn();
            
            if ($appointmentCount > 0) {
                $message = "<div class='bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-4'>Cannot delete doctor with existing appointments. Please reassign or cancel appointments first.</div>";
            } else {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id AND role = 'doctor'");
                $stmt->execute([':id' => $id]);
                
                audit_sensitive($pdo, current_user_id(), 'doctor_deleted', [
                    'doctor_id' => $id,
                    'doctor_name' => $doctor['name'],
                    'doctor_email' => $doctor['email']
                ]);
                
                $message = "<div class='bg-green-50 border border-green-200 text-green-700 p-3 rounded mb-4'>Doctor deleted successfully.</div>";
            }
            
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "<div class='bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-4'>Error deleting doctor: " . e($e->getMessage()) . "</div>";
        }
    }
    $action = 'list';
}

// Fetch doctors for listing
if ($action === 'list') {
    $sql = "
        SELECT u.id, u.name, u.email, u.phone, u.address, u.dob, u.gender, u.created_at, d.specialty
        FROM users u
        JOIN doctors d ON u.id = d.id
        WHERE u.role = 'doctor'
    ";
    $params = [];
    
    if ($search !== '') {
        $sql .= " AND (u.name LIKE :search OR u.email LIKE :search OR u.phone LIKE :search OR d.specialty LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    $sql .= " ORDER BY u.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch doctor for editing
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("
        SELECT u.*, d.specialty 
        FROM users u 
        JOIN doctors d ON u.id = d.id 
        WHERE u.id = :id AND u.role = 'doctor'
    ");
    $stmt->execute([':id' => $id]);
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$doctor) {
        $message = "<div class='bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-4'>Doctor not found.</div>";
        $action = 'list';
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Manage Doctors - Healsync</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-100">
  <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300">
    <div class="max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold flex items-center gap-2">
          <i data-lucide="user-plus" class="w-6 h-6 text-indigo-600"></i>
          Manage Doctors
        </h2>
        <a href="?action=add" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
          <i data-lucide="plus" class="w-4 h-4"></i>
          Add Doctor
        </a>
      </div>

      <?= $message ?>

      <?php if ($action === 'list'): ?>
        <!-- Search -->
        <form method="get" class="flex gap-2 mb-6 max-w-lg">
          <input type="hidden" name="action" value="list">
          <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search by name, email, phone, or specialty"
                 class="flex-grow border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
          <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg flex items-center gap-2 hover:bg-indigo-700">
            <i data-lucide="search" class="w-4 h-4"></i> Search
          </button>
        </form>

        <!-- Doctors List -->
        <?php if (empty($doctors)): ?>
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
            <i data-lucide="user-plus" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Doctors Found</h3>
            <p class="text-gray-500 mb-4">No doctors have been added yet or match your search criteria.</p>
            <a href="?action=add" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
              <i data-lucide="plus" class="w-4 h-4"></i>
              Add First Doctor
            </a>
          </div>
        <?php else: ?>
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">Doctors (<?= count($doctors) ?>)</h3>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Specialty</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gender</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <?php foreach ($doctors as $d): ?>
                    <tr class="hover:bg-gray-50">
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900"><?= e($d['name']) ?></div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900"><?= e($d['email']) ?></div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900"><?= e($d['phone'] ?: 'N/A') ?></div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                          <?= e($d['specialty']) ?>
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900"><?= ucfirst(e($d['gender'])) ?></div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900"><?= e(format_date($d['created_at'])) ?></div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex items-center gap-2">
                          <a href="?action=edit&id=<?= e($d['id']) ?>" 
                             class="inline-flex items-center gap-1 px-3 py-1 bg-yellow-100 text-yellow-700 rounded text-sm hover:bg-yellow-200 transition-colors">
                            <i data-lucide="edit" class="w-4 h-4"></i>
                            Edit
                          </a>
                          <a href="?action=delete&id=<?= e($d['id']) ?>" 
                             onclick="return confirm('Are you sure you want to delete this doctor?')"
                             class="inline-flex items-center gap-1 px-3 py-1 bg-red-100 text-red-700 rounded text-sm hover:bg-red-200 transition-colors">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                            Delete
                          </a>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php endif; ?>

      <?php elseif ($action === 'add' || $action === 'edit'): ?>
        <!-- Add/Edit Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-6">
            <?= $action === 'add' ? 'Add New Doctor' : 'Edit Doctor' ?>
          </h3>

          <form method="post" class="space-y-6">
            <input type="hidden" name="csrf" value="<?= csrf() ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                <input type="text" name="name" value="<?= e($doctor['name'] ?? '') ?>" 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                <input type="email" name="email" value="<?= e($doctor['email'] ?? '') ?>" 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                <input type="tel" name="phone" value="<?= e($doctor['phone'] ?? '') ?>" 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                <input type="date" name="dob" value="<?= e($doctor['dob'] ?? '') ?>" 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                <select name="gender" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                  <option value="male" <?= ($doctor['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                  <option value="female" <?= ($doctor['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                  <option value="other" <?= ($doctor['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Specialty *</label>
                <select name="specialty" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                  <option value="">Select Specialty</option>
                  <option value="Internal Medicine" <?= ($doctor['specialty'] ?? '') === 'Internal Medicine' ? 'selected' : '' ?>>Internal Medicine</option>
                  <option value="Cardiology" <?= ($doctor['specialty'] ?? '') === 'Cardiology' ? 'selected' : '' ?>>Cardiology</option>
                  <option value="Neurology" <?= ($doctor['specialty'] ?? '') === 'Neurology' ? 'selected' : '' ?>>Neurology</option>
                  <option value="Pediatrics" <?= ($doctor['specialty'] ?? '') === 'Pediatrics' ? 'selected' : '' ?>>Pediatrics</option>
                  <option value="Dermatology" <?= ($doctor['specialty'] ?? '') === 'Dermatology' ? 'selected' : '' ?>>Dermatology</option>
                  <option value="Orthopedics" <?= ($doctor['specialty'] ?? '') === 'Orthopedics' ? 'selected' : '' ?>>Orthopedics</option>
                  <option value="Psychiatry" <?= ($doctor['specialty'] ?? '') === 'Psychiatry' ? 'selected' : '' ?>>Psychiatry</option>
                  <option value="Radiology" <?= ($doctor['specialty'] ?? '') === 'Radiology' ? 'selected' : '' ?>>Radiology</option>
                  <option value="Surgery" <?= ($doctor['specialty'] ?? '') === 'Surgery' ? 'selected' : '' ?>>Surgery</option>
                  <option value="Emergency Medicine" <?= ($doctor['specialty'] ?? '') === 'Emergency Medicine' ? 'selected' : '' ?>>Emergency Medicine</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
              <textarea name="address" rows="3" 
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"><?= e($doctor['address'] ?? '') ?></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Password <?= $action === 'edit' ? '(leave blank to keep current)' : '*' ?></label>
              <input type="password" name="password" <?= $action === 'add' ? 'required' : '' ?>
                     class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
              <a href="manage_doctors.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                Cancel
              </a>
              <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                <?= $action === 'add' ? 'Create Doctor' : 'Update Doctor' ?>
              </button>
            </div>
          </form>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <script>lucide.createIcons();</script>
</body>
</html>
