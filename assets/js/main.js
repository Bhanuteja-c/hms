// assets/js/main.js
// minimal fetch wrapper with CSRF token included from server side in a meta tag
async function postJSON(url, data) {
  const csrf = document.querySelector('meta[name="csrf"]').getAttribute('content');
  data.csrf = csrf;
  const res = await fetch(url, {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify(data),
  });
  return await res.json();
}

// show a simple overlay loader
function showLoader() {
  let l = document.getElementById('global-loader');
  if (!l) {
    l = document.createElement('div');
    l.id = 'global-loader';
    l.innerHTML = '<div class="fixed inset-0 bg-black/20 flex items-center justify-center"><div class="p-6 bg-white rounded shadow">Loading...</div></div>';
    document.body.appendChild(l);
  }
  l.style.display = 'block';
}
function hideLoader() {
  const l = document.getElementById('global-loader');
  if (l) l.style.display = 'none';
}
