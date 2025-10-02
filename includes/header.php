<?php
// includes/header.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php'; // header needs DB for notifications

// UI helpers for header
$userName = current_user_name();
$parts = preg_split('/\s+/', trim($userName));
$initials = strtoupper(substr($parts[0] ?? 'U', 0, 1) . substr($parts[1] ?? '', 0, 1));

// Build notification endpoints for JS
$BASE_API_NOTIFS = rtrim(BASE_URL, '/') . '/api/notifications_fetch.php';
$BASE_API_MARK    = rtrim(BASE_URL, '/') . '/api/mark_notification_read.php';
$BASE_API_MARK_ALL= rtrim(BASE_URL, '/') . '/api/mark_all_read.php';
?>
<header class="fixed top-0 left-0 right-0 md:left-64 z-40 bg-white/80 backdrop-blur border-b border-gray-200">
  <div class="max-w-full mx-auto px-4 py-3 flex items-center justify-between">
    <!-- Logo - Only show on mobile when sidebar is hidden -->
    <div class="flex items-center gap-3 md:hidden">
      <a href="<?= e(BASE_URL . '/index.php') ?>" class="flex items-center gap-3">
        <img src="<?= e(BASE_URL . '/assets/img/logo.png') ?>" class="h-8 w-8 rounded" alt="Healsync logo" />
        <span class="font-semibold text-gray-900">Healsync</span>
      </a>
    </div>
    
    <!-- Page Title for Desktop -->
    <div class="hidden md:flex items-center">
      <h1 class="text-lg font-semibold text-gray-800">
        <?php
        $currentPage = basename($_SERVER['PHP_SELF'], '.php');
        $pageTitle = ucfirst(str_replace('_', ' ', $currentPage));
        if ($pageTitle === 'Dashboard') echo 'Dashboard';
        elseif ($pageTitle === 'Book appointment') echo 'Book Appointment';
        elseif ($pageTitle === 'Profile') echo 'My Profile';
        elseif ($pageTitle === 'Notifications') echo 'Notifications';
        else echo $pageTitle;
        ?>
      </h1>
    </div>

    <div class="flex items-center gap-4 relative">
      <?php if (is_logged_in()): ?>
      <div id="notifWrapper" style="overflow:visible; position:relative;">
        <button id="notifBtn" aria-haspopup="true" aria-expanded="false"
                class="p-2 rounded-lg hover:bg-gray-100 focus:outline-none relative transition-colors" title="Notifications">
          <i data-lucide="bell" class="w-6 h-6 text-gray-700"></i>
          <span id="notifCount" class="absolute -top-1 -right-0.5 hidden bg-red-600 text-white text-xs rounded-full px-1.5 py-0.5">0</span>
        </button>

        <div id="notifDropdown" class="hidden bg-white rounded-lg shadow-lg z-50" role="menu"
             style="position:fixed; min-width:320px; max-width:420px; pointer-events:auto;">
          <div class="p-3 border-b flex items-center justify-between">
            <div class="text-sm font-medium">Notifications</div>
            <button id="markAllRead" class="text-xs text-gray-500 hover:underline">Mark all read</button>
          </div>
          <div id="notifList" class="max-h-72 overflow-auto">
            <div class="p-4 text-sm text-gray-500">Loading…</div>
          </div>
          <div class="p-3 border-t text-center text-sm">
            <a id="openAllPage" href="<?php 
              $role = current_user_role();
              if ($role === 'doctor') echo e(BASE_URL . '/doctor/notifications.php');
              elseif ($role === 'admin') echo e(BASE_URL . '/admin/notifications.php');
              elseif ($role === 'receptionist') echo e(BASE_URL . '/reception/notifications.php');
              else echo e(BASE_URL . '/patient/notifications.php');
            ?>" class="text-indigo-600 hover:underline">Open notifications page</a>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <nav class="flex items-center gap-3">
        <?php if (is_logged_in()): ?>
          <a href="<?= e(BASE_URL . (current_user_role()==='doctor' ? '/doctor/dashboard.php' : (current_user_role()==='admin' ? '/admin/dashboard.php' : '/patient/dashboard.php'))) ?>" class="text-sm text-gray-700 hover:text-indigo-600 transition-colors">Dashboard</a>
          <div class="flex items-center gap-2 pl-2">
            <div class="h-8 w-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-semibold">
              <?= e($initials) ?>
            </div>
            <span class="text-sm text-gray-700 font-medium hidden sm:inline"><?= e($userName) ?></span>
          </div>
          <a href="<?= e(BASE_URL . '/auth/logout.php') ?>" class="px-3 py-1.5 bg-red-600 text-white rounded-md text-sm hover:bg-red-700 transition-colors">Logout</a>
        <?php else: ?>
          <a href="<?= e(BASE_URL . '/auth/login.php') ?>" class="text-sm px-3 py-1.5 rounded-md bg-indigo-600 text-white hover:bg-indigo-700 transition-colors">Login</a>
          <a href="<?= e(BASE_URL . '/auth/register_patient.php') ?>" class="text-sm px-3 py-1.5 rounded-md bg-gray-900 text-white hover:bg-gray-800 transition-colors">Register</a>
        <?php endif; ?>
      </nav>
    </div>
  </div>
</header>

<style>
  #notifWrapper { overflow: visible !important; }
  #notifDropdown { display: block; } /* show/hide via .hidden class in JS */
  #notifDropdown.hidden { display: none !important; }
  #notifDropdown { border-radius: .75rem; box-shadow: 0 10px 30px rgba(16,24,40,0.12); }
</style>

<script>
(function(){
  const notifBtn = document.getElementById('notifBtn');
  if (!notifBtn) return;

  const dd = document.getElementById('notifDropdown');
  const list = document.getElementById('notifList');
  const countEl = document.getElementById('notifCount');
  const wrapper = document.getElementById('notifWrapper');
  const markAllBtn = document.getElementById('markAllRead');

  const FETCH_URL = <?= json_encode($BASE_API_NOTIFS) ?>;
  const MARK_URL  = <?= json_encode($BASE_API_MARK) ?>;
  const MARK_ALL_URL = <?= json_encode($BASE_API_MARK_ALL) ?>;

  function escapeHtml(s){ return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
  function timeAgo(iso) {
    try {
      const diff = Math.floor((Date.now() - new Date(iso).getTime())/1000);
      if (diff < 60) return diff + 's';
      if (diff < 3600) return Math.floor(diff/60) + 'm';
      if (diff < 86400) return Math.floor(diff/3600) + 'h';
      return Math.floor(diff/86400) + 'd';
    } catch(e){ return ''; }
  }

  function positionDropdown() {
    const rect = notifBtn.getBoundingClientRect();
    const minWidth = 320;
    const maxWidth = Math.min(420, window.innerWidth - 32);
    const ddWidth = Math.max(minWidth, maxWidth);
    dd.style.minWidth = ddWidth + 'px';
    dd.style.position = 'fixed';
    dd.style.zIndex = 99999;
    dd.style.pointerEvents = 'auto';

    let left = rect.right - ddWidth;
    if (left < 8) left = 8;
    if (left + ddWidth > window.innerWidth - 8) left = window.innerWidth - ddWidth - 8;
    dd.style.left = Math.round(left) + 'px';

    let top = rect.bottom + 8;
    const ddRectHeight = Math.min(window.innerHeight - 32, dd.getBoundingClientRect().height || 300);
    if (top + ddRectHeight > window.innerHeight - 8) {
      top = rect.top - ddRectHeight - 8;
      if (top < 8) top = 8;
    }
    dd.style.top = Math.round(top) + 'px';
  }

  function renderNotifs(data) {
    const notifs = (data && data.notifications) || [];
    const unread = (data && data.unread_count) || 0;

    if (unread > 0) {
      countEl.textContent = unread;
      countEl.classList.remove('hidden');
    } else {
      countEl.classList.add('hidden');
    }

    if (!notifs.length) {
      list.innerHTML = '<div class="p-4 text-sm text-gray-500">No notifications.</div>';
      return;
    }

    list.innerHTML = '';
    notifs.forEach(n => {
      const a = document.createElement('a');
      a.href = n.link || '#';
      a.className = 'block p-3 border-b hover:bg-gray-50 flex gap-3 items-start';
      if (!n.is_read) a.classList.add('bg-indigo-50');
      a.dataset.id = n.id;
      a.innerHTML = `
        <div class="flex-1">
          <div class="text-sm text-gray-800">${escapeHtml(n.message)}</div>
          <div class="text-xs text-gray-400 mt-1">${timeAgo(n.created_at)} ago</div>
        </div>`;
      a.addEventListener('click', function(ev){
        ev.preventDefault();
        markReadAndGoto(n.id, a.href);
      });
      list.appendChild(a);
    });
  }

  async function fetchNotifs() {
    if (list) list.innerHTML = '<div class="p-4 text-sm text-gray-500">Loading…</div>';
    try {
      const res = await fetch(FETCH_URL, { credentials:'same-origin' });
      if (!res.ok) throw new Error('Network error');
      const json = await res.json();
      renderNotifs(json);
    } catch (err) {
      if (list) list.innerHTML = '<div class="p-4 text-sm text-red-600">Could not load notifications.</div>';
      console.error('notifications fetch error', err);
    }
  }

  async function markReadAndGoto(id, href) {
    try {
      await fetch(MARK_URL, {
        method:'POST',
        credentials:'same-origin',
        headers:{ 'Content-Type':'application/x-www-form-urlencoded' },
        body: 'id=' + encodeURIComponent(id)
      });
    } catch(e){ console.warn('mark read failed', e); }
    window.location = href;
  }

  async function markAllRead() {
    try {
      await fetch(MARK_ALL_URL, { method:'POST', credentials:'same-origin' });
      await fetchNotifs();
    } catch(e){ console.warn('mark all failed', e); }
  }

  function toggleDropdown() {
    const open = !dd.classList.contains('hidden');
    if (!open) {
      positionDropdown();
      dd.classList.remove('hidden');
      notifBtn.setAttribute('aria-expanded','true');
      fetchNotifs();
    } else {
      dd.classList.add('hidden');
      notifBtn.setAttribute('aria-expanded','false');
    }
  }

  notifBtn.addEventListener('click', function(ev){
    ev.stopPropagation();
    toggleDropdown();
  });
  markAllBtn && markAllBtn.addEventListener('click', function(e){
    e.preventDefault();
    markAllRead();
  });

  document.addEventListener('click', function(e){
    if (!wrapper.contains(e.target) && !dd.contains(e.target)) {
      dd.classList.add('hidden');
      notifBtn.setAttribute('aria-expanded','false');
    }
  });
  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape') {
      dd.classList.add('hidden');
      notifBtn.setAttribute('aria-expanded','false');
    }
  });

  window.addEventListener('resize', function(){ if (!dd.classList.contains('hidden')) positionDropdown(); });
  window.addEventListener('scroll', function(){ if (!dd.classList.contains('hidden')) positionDropdown(); });

  (function initBadge(){
    fetch(FETCH_URL, { credentials:'same-origin' })
      .then(r => r.json())
      .then(json => {
        if (json && json.unread_count > 0) {
          countEl.textContent = json.unread_count;
          countEl.classList.remove('hidden');
        }
      }).catch(()=>{});
  })();
})();
</script>

<!-- lucide icons -->
<script src="https://unpkg.com/lucide@latest"></script>
<script>lucide.createIcons();</script>
