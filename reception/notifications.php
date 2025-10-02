<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('receptionist');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$rid = current_user_id();

// Filter
$filter = $_GET['filter'] ?? 'all';
$where = "user_id=:uid";
if ($filter === 'unread') $where .= " AND is_read=0";
if ($filter === 'read') $where .= " AND is_read=1";

// Fetch notifications
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE $where ORDER BY created_at DESC");
$stmt->execute([':uid'=>$rid]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Notifications - Reception</title>
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
    /* Timeline */
    .timeline { position: relative; margin-left: 1.5rem; padding-left: 1.5rem; border-left: 2px solid #e5e7eb; }
    .timeline-item { position: relative; margin-bottom: 1.5rem; }
    .timeline-icon { position: absolute; left: -2.1rem; top: 0; background: white; border: 2px solid #6366f1; border-radius: 9999px; padding: 0.3rem; }
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
            My Notifications
          </h1>
          <p class="text-gray-600 mt-1">Stay informed about appointments and patient activities</p>
        </div>
        <div class="w-12 h-12 bg-gradient-to-br from-teal-500 to-cyan-500 rounded-xl flex items-center justify-center">
          <i data-lucide="bell" class="w-6 h-6 text-white"></i>
        </div>
      </div>
    </div>

    <!-- Controls -->
    <div class="glass-effect rounded-2xl premium-shadow p-6 mb-8">
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <!-- Filters -->
        <div class="flex gap-2 text-sm">
          <a href="?filter=all" 
             class="px-4 py-2 rounded-lg transition-all duration-200 <?= $filter==='all' ? 'bg-teal-600 text-white' : 'bg-white text-gray-600 hover:bg-teal-50 hover:text-teal-600' ?>">
             All
          </a>
          <a href="?filter=unread" 
             class="px-4 py-2 rounded-lg transition-all duration-200 <?= $filter==='unread' ? 'bg-teal-600 text-white' : 'bg-white text-gray-600 hover:bg-teal-50 hover:text-teal-600' ?>">
             Unread
          </a>
          <a href="?filter=read" 
             class="px-4 py-2 rounded-lg transition-all duration-200 <?= $filter==='read' ? 'bg-teal-600 text-white' : 'bg-white text-gray-600 hover:bg-teal-50 hover:text-teal-600' ?>">
             Read
          </a>
        </div>

        <!-- Mark All Read Button -->
        <?php if ($notes): ?>
          <button onclick="markAllRead()" 
                  class="px-4 py-2 bg-gradient-to-r from-teal-600 to-cyan-600 text-white rounded-lg text-sm hover:from-teal-700 hover:to-cyan-700 transition-all duration-200 flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4"></i>
            Mark All as Read
          </button>
        <?php endif; ?>
      </div>
    </div>

    <!-- Notifications List -->
    <div class="glass-effect rounded-2xl premium-shadow p-8">
      <?php if ($notes): ?>
        <div class="timeline">
          <?php foreach ($notes as $n): ?>
            <div class="timeline-item">
              <div class="timeline-icon">
                <i data-lucide="<?= $n['is_read'] ? 'check-circle' : 'bell' ?>" 
                   class="w-4 h-4 <?= $n['is_read'] ? 'text-green-600' : 'text-teal-600' ?>"></i>
              </div>
              <div class="ml-6 bg-white/50 rounded-xl p-4 hover:bg-white/70 transition-all duration-200">
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <p class="text-gray-800 font-medium"><?= e($n['message']) ?></p>
                    <p class="text-sm text-gray-500 mt-1 flex items-center gap-2">
                      <i data-lucide="clock" class="w-3 h-3"></i>
                      <?= date('d M Y H:i', strtotime($n['created_at'])) ?>
                    </p>
                  </div>
                  <?php if (!$n['is_read']): ?>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-teal-100 text-teal-800">
                      New
                    </span>
                  <?php endif; ?>
                </div>
                <div class="flex gap-3 mt-3">
                  <?php if ($n['link']): ?>
                    <a href="<?= e($n['link']) ?>" 
                       class="inline-flex items-center gap-1 text-teal-600 text-sm hover:text-teal-700 hover:underline">
                      <i data-lucide="external-link" class="w-3 h-3"></i>
                      View Details
                    </a>
                  <?php endif; ?>
                  <?php if (!$n['is_read']): ?>
                    <button onclick="markRead(<?= $n['id'] ?>)" 
                            class="inline-flex items-center gap-1 text-green-600 text-sm hover:text-green-700 hover:underline">
                      <i data-lucide="check" class="w-3 h-3"></i>
                      Mark as Read
                    </button>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="text-center py-12">
          <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i data-lucide="bell-off" class="w-8 h-8 text-gray-400"></i>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No notifications found</h3>
          <p class="text-gray-500">You're all caught up! New notifications will appear here.</p>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <script>
    lucide.createIcons();

    function markAllRead() {
      fetch('<?= e(BASE_URL . '/api/mark_all_read.php') ?>', { method: 'POST' })
        .then(() => location.reload())
        .catch(err => console.error('Error:', err));
    }

    function markRead(id) {
      fetch('<?= e(BASE_URL . '/api/mark_notification_read.php') ?>', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'id='+id
      }).then(() => location.reload())
        .catch(err => console.error('Error:', err));
    }
  </script>
</body>
</html>
