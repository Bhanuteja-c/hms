<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$message = "";

// Handle add treatment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_treatment'])) {
    $name = trim($_POST['treatment_name']);
    $cost = floatval($_POST['cost']);

    if ($name && $cost >= 0) {
        $stmt = $pdo->prepare("INSERT INTO treatments (treatment_name, cost) VALUES (:n, :c)");
        $stmt->execute([':n'=>$name, ':c'=>$cost]);
        $message = "<p class='text-green-600'>Treatment added successfully.</p>";
    } else {
        $message = "<p class='text-red-600'>Please provide valid details.</p>";
    }
}

// Handle delete treatment
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM treatments WHERE id=:id")->execute([':id'=>$id]);
    $message = "<p class='text-green-600'>Treatment deleted.</p>";
}

// Fetch treatments
$stmt = $pdo->query("SELECT * FROM treatments ORDER BY treatment_name ASC");
$treatments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Manage Treatments - Healsync</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-100">
  <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300">
    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
      <i data-lucide="stethoscope" class="w-6 h-6 text-indigo-600"></i>
      Manage Treatments
    </h2>

    <?= $message ?>

    <!-- Add Treatment Form -->
    <div class="bg-white p-6 rounded-xl shadow mb-8">
      <h3 class="text-lg font-semibold mb-4">Add New Treatment</h3>
      <form method="post" class="grid md:grid-cols-2 gap-4">
        <input type="hidden" name="add_treatment" value="1">

        <div>
          <label class="block mb-1 font-medium">Treatment Name *</label>
          <input type="text" name="treatment_name" class="w-full border rounded p-2" required>
        </div>
        <div>
          <label class="block mb-1 font-medium">Cost ($) *</label>
          <input type="number" name="cost" step="0.01" class="w-full border rounded p-2" required>
        </div>

        <div class="md:col-span-2">
          <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded flex items-center gap-2 hover:bg-indigo-700">
            <i data-lucide="plus-circle" class="w-5 h-5"></i> Add Treatment
          </button>
        </div>
      </form>
    </div>

    <!-- Treatment List -->
    <div class="bg-white p-6 rounded-xl shadow">
      <h3 class="text-lg font-semibold mb-4">Treatment List</h3>
      <?php if ($treatments): ?>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-100">
              <tr>
                <th class="p-3 text-left">Treatment</th>
                <th class="p-3 text-left">Cost</th>
                <th class="p-3 text-left">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($treatments as $t): ?>
                <tr class="border-t hover:bg-gray-50">
                  <td class="p-3 font-medium"><?=e($t['treatment_name'])?></td>
                  <td class="p-3">$<?=number_format($t['cost'], 2)?></td>
                  <td class="p-3">
                    <a href="?delete=<?=e($t['id'])?>" onclick="return confirm('Delete this treatment?')"
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
        <p class="text-gray-500">No treatments found.</p>
      <?php endif; ?>
    </div>
  </main>

  <script> lucide.createIcons(); </script>
</body>
</html>
