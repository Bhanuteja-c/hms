<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$message = "";
$search = trim($_GET['search'] ?? '');

// Handle delete patient
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM users WHERE id=:id AND role='patient'")->execute([':id'=>$id]);
    $message = "<p class='text-green-600'>Patient deleted.</p>";
}

// Fetch patients
$sql = "SELECT id, name, email, phone, dob, gender, address, created_at FROM users WHERE role='patient'";
$params = [];

if ($search !== '') {
    $sql .= " AND (name LIKE :s OR email LIKE :s OR phone LIKE :s)";
    $params[':s'] = "%$search%";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Manage Patients - Healsync</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-100">
  <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300">
    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
      <i data-lucide="users" class="w-6 h-6 text-indigo-600"></i>
      Manage Patients
    </h2>

    <?= $message ?>

    <!-- Search -->
    <form method="get" class="flex gap-2 mb-6 max-w-lg">
      <input type="text" name="search" value="<?=e($search)?>" placeholder="Search by name, email, or phone"
             class="flex-grow border rounded px-3 py-2">
      <button class="px-4 py-2 bg-indigo-600 text-white rounded flex items-center gap-2">
        <i data-lucide="search" class="w-4 h-4"></i> Search
      </button>
    </form>

    <!-- Patient List -->
    <div class="bg-white p-6 rounded-xl shadow">
      <h3 class="text-lg font-semibold mb-4">Patient List</h3>
      <?php if ($patients): ?>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-100">
              <tr>
                <th class="p-3 text-left">Name</th>
                <th class="p-3 text-left">Email</th>
                <th class="p-3 text-left">Phone</th>
                <th class="p-3 text-left">DOB</th>
                <th class="p-3 text-left">Gender</th>
                <th class="p-3 text-left">Address</th>
                <th class="p-3 text-left">Registered</th>
                <th class="p-3 text-left">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($patients as $p): ?>
                <tr class="border-t hover:bg-gray-50">
                  <td class="p-3 font-medium"><?=e($p['name'])?></td>
                  <td class="p-3"><?=e($p['email'])?></td>
                  <td class="p-3"><?=e($p['phone'])?></td>
                  <td class="p-3"><?=e($p['dob'])?></td>
                  <td class="p-3"><?=e($p['gender'])?></td>
                  <td class="p-3"><?=e($p['address'])?></td>
                  <td class="p-3"><?=date('d M Y', strtotime($p['created_at']))?></td>
                  <td class="p-3">
                    <a href="?delete=<?=e($p['id'])?>" onclick="return confirm('Delete this patient?')"
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
        <p class="text-gray-500">No patients found.</p>
      <?php endif; ?>
    </div>
  </main>

  <script> lucide.createIcons(); </script>
</body>
</html>
