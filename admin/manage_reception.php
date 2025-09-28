<?php
// admin/manage_reception.php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$message = '';
$action = $_GET['action'] ?? 'list';
$id = intval($_GET['id'] ?? 0);

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

        if ($action === 'add') {
            if (!$name || !$email || !$password) {
                $message = "<div class='bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-4'>Name, email, and password are required.</div>";
            } else {
                // Check if email exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
                $stmt->execute([':email' => $email]);
                if ($stmt->fetch()) {
                    $message = "<div class='bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-4'>Email already exists.</div>";
                } else {
                    // Create receptionist
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("
                        INSERT INTO users (role, name, email, password, phone, address, created_at) 
                        VALUES ('receptionist', :name, :email, :password, :phone, :address, NOW())
                    ");
                    $stmt->execute([
                        ':name' => $name,
                        ':email' => $email,
                        ':password' => $hash,
                        ':phone' => $phone,
                        ':address' => $address
                    ]);

                    audit_sensitive($pdo, current_user_id(), 'receptionist_created', [
                        'receptionist_email' => $email,
                        'receptionist_name' => $name
                    ]);

                    $message = "<div class='bg-green-50 border border-green-200 text-green-700 p-3 rounded mb-4'>Receptionist created successfully.</div>";
                    $action = 'list';
                }
            }
        } elseif ($action === 'edit' && $id) {
            if (!$name || !$email) {
                $message = "<div class='bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-4'>Name and email are required.</div>";
            } else {
                // Check if email exists for other users
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
                $stmt->execute([':email' => $email, ':id' => $id]);
                if ($stmt->fetch()) {
                    $message = "<div class='bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-4'>Email already exists.</div>";
                } else {
                    $updateFields = "name = :name, email = :email, phone = :phone, address = :address";
                    $params = [
                        ':name' => $name,
                        ':email' => $email,
                        ':phone' => $phone,
                        ':address' => $address,
                        ':id' => $id
                    ];

                    // Update password if provided
                    if ($password) {
                        $updateFields .= ", password = :password";
                        $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
                    }

                    $stmt = $pdo->prepare("UPDATE users SET $updateFields WHERE id = :id AND role = 'receptionist'");
                    $stmt->execute($params);

                    audit_sensitive($pdo, current_user_id(), 'receptionist_updated', [
                        'receptionist_id' => $id,
                        'receptionist_email' => $email,
                        'receptionist_name' => $name
                    ]);

                    $message = "<div class='bg-green-50 border border-green-200 text-green-700 p-3 rounded mb-4'>Receptionist updated successfully.</div>";
                    $action = 'list';
                }
            }
        }
    }
}

// Handle delete
if ($action === 'delete' && $id) {
    $stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = :id AND role = 'receptionist'");
    $stmt->execute([':id' => $id]);
    $receptionist = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($receptionist) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id AND role = 'receptionist'");
        $stmt->execute([':id' => $id]);
        
        audit_sensitive($pdo, current_user_id(), 'receptionist_deleted', [
            'receptionist_id' => $id,
            'receptionist_name' => $receptionist['name'],
            'receptionist_email' => $receptionist['email']
        ]);
        
        $message = "<div class='bg-green-50 border border-green-200 text-green-700 p-3 rounded mb-4'>Receptionist deleted successfully.</div>";
    }
    $action = 'list';
}

// Fetch receptionists for listing
if ($action === 'list') {
    $stmt = $pdo->prepare("
        SELECT id, name, email, phone, address, created_at
        FROM users 
        WHERE role = 'receptionist' 
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $receptionists = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch receptionist for editing
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id AND role = 'receptionist'");
    $stmt->execute([':id' => $id]);
    $receptionist = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$receptionist) {
        $message = "<div class='bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-4'>Receptionist not found.</div>";
        $action = 'list';
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Manage Receptionists - Healsync</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-100">
  <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64">
    <div class="max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold flex items-center gap-2">
          <i data-lucide="users" class="w-6 h-6 text-indigo-600"></i>
          Manage Receptionists
        </h2>
        <a href="?action=add" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
          <i data-lucide="plus" class="w-4 h-4"></i>
          Add Receptionist
        </a>
      </div>

      <?= $message ?>

      <?php if ($action === 'list'): ?>
        <!-- Receptionists List -->
        <?php if (empty($receptionists)): ?>
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
            <i data-lucide="users" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Receptionists</h3>
            <p class="text-gray-500 mb-4">No receptionists have been added yet.</p>
            <a href="?action=add" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
              <i data-lucide="plus" class="w-4 h-4"></i>
              Add First Receptionist
            </a>
          </div>
        <?php else: ?>
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">Receptionists (<?= count($receptionists) ?>)</h3>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <?php foreach ($receptionists as $r): ?>
                    <tr class="hover:bg-gray-50">
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900"><?= e($r['name']) ?></div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900"><?= e($r['email']) ?></div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900"><?= e($r['phone'] ?: 'N/A') ?></div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900"><?= e(format_date($r['created_at'])) ?></div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex items-center gap-2">
                          <a href="?action=edit&id=<?= e($r['id']) ?>" 
                             class="inline-flex items-center gap-1 px-3 py-1 bg-yellow-100 text-yellow-700 rounded text-sm hover:bg-yellow-200 transition-colors">
                            <i data-lucide="edit" class="w-4 h-4"></i>
                            Edit
                          </a>
                          <a href="?action=delete&id=<?= e($r['id']) ?>" 
                             onclick="return confirm('Are you sure you want to delete this receptionist?')"
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
            <?= $action === 'add' ? 'Add New Receptionist' : 'Edit Receptionist' ?>
          </h3>

          <form method="post" class="space-y-6">
            <input type="hidden" name="csrf" value="<?= csrf() ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                <input type="text" name="name" value="<?= e($receptionist['name'] ?? '') ?>" 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                <input type="email" name="email" value="<?= e($receptionist['email'] ?? '') ?>" 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                <input type="tel" name="phone" value="<?= e($receptionist['phone'] ?? '') ?>" 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Password <?= $action === 'edit' ? '(leave blank to keep current)' : '*' ?></label>
                <input type="password" name="password" <?= $action === 'add' ? 'required' : '' ?>
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
              <textarea name="address" rows="3" 
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"><?= e($receptionist['address'] ?? '') ?></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
              <a href="manage_reception.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                Cancel
              </a>
              <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                <?= $action === 'add' ? 'Create Receptionist' : 'Update Receptionist' ?>
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
