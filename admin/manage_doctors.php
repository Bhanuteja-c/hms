<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$message = "";

// Handle add doctor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_doctor'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $specialty = trim($_POST['specialty']);
    $phone = trim($_POST['phone']);

    if ($name && $email && $password && $specialty) {
        $stmt = $pdo->prepare("INSERT INTO users (role, name, email, password, phone) VALUES ('doctor', :n, :e, :p, :ph)");
        $stmt->execute([':n'=>$name, ':e'=>$email, ':p'=>$password, ':ph'=>$phone]);
        $doctor_id = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO doctors (id, specialty) VALUES (:id, :sp)");
        $stmt->execute([':id'=>$doctor_id, ':sp'=>$specialty]);

        $message = "<p class='text-green-600'>Doctor added successfully.</p>";
    } else {
        $message = "<p class='text-red-600'>All fields are required.</p>";
    }
}

// Handle delete doctor
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM users WHERE id=:id AND role='doctor'")->execute([':id'=>$id]);
    $message = "<p class='text-green-600'>Doctor deleted.</p>";
}

// Fetch all doctors
$stmt = $pdo->query("
  SELECT u.id, u.name, u.email, u.phone, d.specialty
  FROM users u
  JOIN doctors d ON u.id=d.id
  WHERE u.role='doctor'
  ORDER BY u.name ASC
");
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
      <i data-lucide="user-plus" class="w-6 h-6 text-indigo-600"></i>
      Manage Doctors
    </h2>

    <?= $message ?>

    <!-- Add Doctor Form -->
    <div class="bg-white p-6 rounded-xl shadow mb-8">
      <h3 class="text-lg font-semibold mb-4">Add New Doctor</h3>
      <form method="post" class="grid md:grid-cols-2 gap-4">
        <input type="hidden" name="add_doctor" value="1">

        <div>
          <label class="block mb-1 font-medium">Name *</label>
          <input type="text" name="name" class="w-full border rounded p-2" required>
        </div>
        <div>
          <label class="block mb-1 font-medium">Email *</label>
          <input type="email" name="email" class="w-full border rounded p-2" required>
        </div>
        <div>
          <label class="block mb-1 font-medium">Password *</label>
          <input type="password" name="password" class="w-full border rounded p-2" required>
        </div>
        <div>
          <label class="block mb-1 font-medium">Specialty *</label>
          <input type="text" name="specialty" class="w-full border rounded p-2" required>
        </div>
        <div class="md:col-span-2">
          <label class="block mb-1 font-medium">Phone</label>
          <input type="text" name="phone" class="w-full border rounded p-2">
        </div>

        <div class="md:col-span-2">
          <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded flex items-center gap-2 hover:bg-indigo-700">
            <i data-lucide="plus-circle" class="w-5 h-5"></i> Add Doctor
          </button>
        </div>
      </form>
    </div>

    <!-- Doctors List -->
    <div class="bg-white p-6 rounded-xl shadow">
      <h3 class="text-lg font-semibold mb-4">Doctor List</h3>
      <?php if ($doctors): ?>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-100">
              <tr>
                <th class="p-3 text-left">Name</th>
                <th class="p-3 text-left">Email</th>
                <th class="p-3 text-left">Phone</th>
                <th class="p-3 text-left">Specialty</th>
                <th class="p-3 text-left">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($doctors as $d): ?>
                <tr class="border-t hover:bg-gray-50">
                  <td class="p-3 font-medium"><?=e($d['name'])?></td>
                  <td class="p-3"><?=e($d['email'])?></td>
                  <td class="p-3"><?=e($d['phone'])?></td>
                  <td class="p-3"><?=e($d['specialty'])?></td>
                  <td class="p-3">
                    <a href="?delete=<?=e($d['id'])?>" onclick="return confirm('Delete this doctor?')"
                       class="px-3 py-1 bg-red-100 text-red-600 rounded text-xs flex items-center gap-1">
                      <i data-lucide="trash-2" class="w-4 h-4"></i> Delete
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p class="text-gray-500">No doctors found.</p>
      <?php endif; ?>
    </div>
  </main>

  <script> lucide.createIcons(); </script>
</body>
</html>
