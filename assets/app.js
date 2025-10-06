// assets/app.js — UI logic using fetch() and small helpers
const $ = (sel, root = document) => root.querySelector(sel);
const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

const API = {
  list: () => fetch('app/api.php?action=list').then(r => r.json()),
  create: (cvr) => fetch('app/api.php?action=create', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ cvr_number: cvr })
  }).then(r => r.json()),
  del: (id) => fetch('app/api.php?action=delete&id=' + encodeURIComponent(id), { method: 'DELETE' }).then(r => r.json()),
  sync: (id) => fetch('app/api.php?action=sync', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id })
  }).then(r => r.json()),
  syncAll: () => fetch('app/api.php?action=sync_all', { method: 'POST' }).then(r => r.json()),
};

function rowFromTemplate() {
  return document.importNode($('#row-template').content, true).firstElementChild;
}

function renderTable(items) {
  const tbody = $('#companies tbody');
  tbody.innerHTML = '';
  for (const item of items) {
    const tr = rowFromTemplate();
    tr.dataset.id = item.id;
    tr.querySelector('.id').textContent = item.id;
    tr.querySelector('.cvr').textContent = item.cvr_number;
    tr.querySelector('.name').textContent = item.name || '';
    tr.querySelector('.phone').textContent = item.phone || '';
    tr.querySelector('.email').textContent = item.email || '';
    tr.querySelector('.address').textContent = item.address || '';
    tbody.appendChild(tr);
  }
}

async function load() {
  const data = await API.list();
  renderTable(data.items || []);
}

$('#add-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const input = $('#cvr');
  const cvr = (input.value || '').trim();
  if (!/^\d{8}$/.test(cvr)) {
    alert('CVR must be 8 digits');
    return;
  }
  const res = await API.create(cvr);
  if (res.error) {
    alert(res.error);
    return;
  }
  input.value = '';
  await load();
});

$('#sync-all').addEventListener('click', async () => {
  const btn = $('#sync-all');
  btn.disabled = true; btn.textContent = 'Synchronizing…';
  try {
    await API.syncAll();
    await load();
  } finally {
    btn.disabled = false; btn.textContent = 'Synchronize all';
  }
});

$('#companies').addEventListener('click', async (e) => {
  const btn = e.target.closest('button');
  if (!btn) return;
  const tr = e.target.closest('tr');
  const id = Number(tr?.dataset?.id);
  if (!id) return;

  const action = btn.dataset.action;
  if (action === 'delete') {
    if (!confirm('Delete company #' + id + '?')) return;
    await API.del(id);
    await load();
  }
  if (action === 'sync') {
    btn.disabled = true; btn.textContent = 'Syncing…';
    try {
      const res = await API.sync(id);
      if (res.item) {
        tr.querySelector('.name').textContent = res.item.name || '';
        tr.querySelector('.phone').textContent = res.item.phone || '';
        tr.querySelector('.email').textContent = res.item.email || '';
        tr.querySelector('.address').textContent = res.item.address || '';
      }
    } finally {
      btn.disabled = false; btn.textContent = 'Sync';
    }
  }
});

window.addEventListener('DOMContentLoaded', load);
