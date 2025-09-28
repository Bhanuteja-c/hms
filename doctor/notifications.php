<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('doctor');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$did = current_user_id();

// Filter
$filter = $_GET['filter'] ?? 'all';
$where  = "user_id = :uid";
if ($filter === 'unread') $where .= " AND is_read = 0";
if ($filter === 'read')   $where .= " AND is_read = 1";

// Fetch notifications
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE $where ORDER BY created_at DESC");
$stmt->execute([':uid' => $did]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>My Notifications - Doctor</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    /* Timeline style */
    .timeline { position: relative; margin-left: 1.5rem; padding-left: 1.5rem; border-left: 2px solid #e5e7eb; }
    .timeline-item { position: relative; margin-bottom: 1.5rem; }
    .timeline-icon { position: absolute; left: -2.1rem; top: 0; background: white; border: 2px solid #6366f1; border-radius: 9999px; padding: 0.3rem; }
  </style>
</head>
<body class="bg-gray-100">
  <?php include __DIR__ . '/../includes/sidebar_doctor.php'; ?>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <main class="pt-20 p-6 md:ml-64 transition-all duration-300 max-w-3xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold flex items-center gap-2">
        <i data-lucide="bell" class="w-6 h-6 text-indigo-600"></i>
        Notifications
      </h2>
      <?php if ($notes): ?>
        <button onclick="markAllRead()" 
                class="px-3 py-1 bg-indigo-600 text-white rounded text-sm hover:bg-indigo-700">
          Mark All as Read
        </button>
      <?php endif; ?>
    </div>

    <!-- Filters -->
    <div class="flex gap-4 mb-6 border-b pb-2 text-sm">
      <a href="?filter=all" 
         class="<?= $filter==='all' ? 'text-indigo-600 font-semibold' : 'text-gray-600 hover:text-indigo-600' ?>">
         All
      </a>
      <a href="?filter=unread" 
         class="<?= $filter==='unread' ? 'text-indigo-600 font-semibold' : 'text-gray-600 hover:text-indigo-600' ?>">
         Unread
      </a>
      <a href="?filter=read" 
         class="<?= $filter==='read' ? 'text-indigo-600 font-semibold' : 'text-gray-600 hover:text-indigo-600' ?>">
         Read
      </a>
    </div>

    <!-- Notifications List -->
    <?php if ($notes): ?>
      <div class="timeline">
        <?php foreach ($notes as $n): ?>
          <div class="timeline-item">
            <div class="timeline-icon">
              <i data-lucide="<?= $n['is_read'] ? 'check-circle' : 'bell' ?>" 
                 class="w-4 h-4 <?= $n['is_read'] ? 'text-green-600' : 'text-indigo-600' ?>"></i>
            </div>
            <div class="ml-6">
              <p class="text-gray-700"><?= e($n['message']) ?></p>
              <p class="text-xs text-gray-400"><?= date('d M Y H:i', strtotime($n['created_at'])) ?></p>
              <div class="flex gap-3 mt-1">
                <?php if (!empty($n['link'])): ?>
                  <a href="<?= e($n['link']) ?>" class="text-indigo-600 text-sm hover:underline">View Details</a>
                <?php endif; ?>
                <?php if (!$n['is_read']): ?>
                  <button onclick="markRead(<?= $n['id'] ?>)" class="text-green-600 text-sm hover:underline">Mark as Read</button>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="text-gray-500">No notifications found.</p>
    <?php endif; ?>
  </main>

  <script>
    lucide.createIcons();

    // Mark all as read
    function markAllRead() {
      fetch('/healsync/api/mark_all_read.php', { method: 'POST' })
        .then(() => location.reload());
    }

    // Mark a single notification as read
    function markRead(id) {
      fetch('/healsync/api/mark_notification_read.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'id=' + id
      }).then(() => location.reload());
    }
  </script>
</body>
</html>
