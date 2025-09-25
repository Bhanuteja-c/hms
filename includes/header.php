<?php
// includes/header.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';

$unreadCount = 0;
$latestNotes = [];

if (is_logged_in()) {
    $uid = current_user_id();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=:uid AND is_read=0");
    $stmt->execute([':uid'=>$uid]);
    $unreadCount = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id=:uid ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([':uid'=>$uid]);
    $latestNotes = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<header class="bg-white shadow fixed top-0 left-0 right-0 md:left-64 z-30 flex items-center justify-between px-6 py-4 transition-all duration-300">
  <!-- Hamburger button (mobile only) -->
  <button onclick="toggleSidebar()" class="md:hidden text-gray-600 hover:text-gray-900">
    <i data-lucide="menu" class="w-6 h-6"></i>
  </button>

  <div class="flex items-center gap-3">
    <span class="font-semibold text-indigo-600">Healsync</span>
  </div>

  <nav class="flex items-center gap-6 relative">
    <?php if (is_logged_in()): ?>

      <!-- Notifications -->
      <div class="relative">
        <button onclick="toggleNotifications()" class="relative" id="notifBell">
          <i data-lucide="bell" class="w-6 h-6 text-gray-600"></i>
          <?php if ($unreadCount > 0): ?>
            <span id="notifBadge" class="absolute -top-1 -right-1 bg-red-600 text-white text-xs rounded-full px-1">
              <?=$unreadCount?>
            </span>
          <?php else: ?>
            <span id="notifBadge" class="hidden absolute -top-1 -right-1 bg-red-600 text-white text-xs rounded-full px-1">0</span>
          <?php endif; ?>
        </button>

        <!-- Dropdown -->
        <div id="notificationsDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded shadow-lg border z-40">
          <div class="p-3 border-b font-semibold">Notifications</div>
          <div id="notifList">
            <?php if ($latestNotes): ?>
              <ul class="max-h-60 overflow-y-auto divide-y">
                <?php foreach ($latestNotes as $n): ?>
                  <li class="p-3 text-sm">
                    <p class="text-gray-700"><?=e($n['message'])?></p>
                    <p class="text-xs text-gray-400"><?=date('d M Y H:i', strtotime($n['created_at']))?></p>
                    <?php if ($n['link']): ?>
                      <a href="<?=e($n['link'])?>" class="text-indigo-600 text-xs">View</a>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
              <div class="p-2 text-center border-t">
                <a href="/healsync/<?=(current_user_role()==='doctor'?'doctor':'patient')?>/notifications.php" class="text-indigo-600 text-sm">View All</a>
              </div>
            <?php else: ?>
              <div class="p-3 text-gray-500 text-sm">No notifications</div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Dashboard Link -->
      <a href="/healsync/<?=
        current_user_role() === 'admin' ? 'admin/dashboard.php' :
        (current_user_role() === 'doctor' ? 'doctor/dashboard.php' : 'patient/dashboard.php')
      ?>" class="text-sm">Dashboard</a>

      <span class="text-sm text-gray-500"><?=e($_SESSION['user']['name'])?></span>
      <a href="/healsync/auth/logout.php" class="px-3 py-1 bg-red-100 text-red-700 rounded text-sm">Logout</a>

    <?php else: ?>
      <a href="/healsync/auth/login.php" class="text-sm">Login</a>
      <a href="/healsync/auth/register_patient.php" class="text-sm">Register</a>
    <?php endif; ?>
  </nav>
</header>

<script>
function toggleNotifications() {
  document.getElementById('notificationsDropdown').classList.toggle('hidden');
}

// 🔄 Auto-refresh notifications every 30s
setInterval(loadNotifications, 30000);

function loadNotifications() {
  fetch('/healsync/api/get_notifications.php')
    .then(r => r.json())
    .then(data => {
      if (data.ok) {
        const badge = document.getElementById('notifBadge');
        badge.innerText = data.unread;
        if (data.unread > 0) {
          badge.classList.remove('hidden');
        } else {
          badge.classList.add('hidden');
        }

        // update dropdown list
        let list = "";
        if (data.latest.length > 0) {
          list += '<ul class="max-h-60 overflow-y-auto divide-y">';
          data.latest.forEach(n => {
            list += `<li class="p-3 text-sm cursor-pointer hover:bg-gray-50"
                        onclick="markReadAndGo(${n.id}, '${n.link}')">
              <p class="text-gray-700">${n.message}</p>
              <p class="text-xs text-gray-400">${n.created_at}</p>
              ${n.link ? `<span class="text-indigo-600 text-xs underline">View</span>` : ""}
            </li>`;
          });
          list += '</ul><div class="p-2 text-center border-t"><a href="/healsync/${data.role}/notifications.php" class="text-indigo-600 text-sm">View All</a></div>`;
        } else {
          list = '<div class="p-3 text-gray-500 text-sm">No notifications</div>';
        }
        document.getElementById('notifList').innerHTML = list;
      }
    });
}

function markReadAndGo(id, link) {
  fetch('/healsync/api/mark_notification_read.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'id=' + encodeURIComponent(id)
  }).then(() => {
    loadNotifications(); // refresh badge & list
    if (link) window.location.href = link;
  });
}
</script>
