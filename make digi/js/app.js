/* ===== DigiLocker Clone — Application Logic (PHP Backend) ===== */

// ───────── API Base Paths ─────────
const API_AUTH = 'api/auth.php';
const API_DOCS = 'api/documents.php';
const API_PROFILE = 'api/profile.php';

// ───────── Cached session user (set once per page load) ─────────
let _currentUser = undefined; // undefined = not yet fetched, null = logged out

// ───────── Fetch Wrappers ─────────
async function apiGet(url) {
  const res = await fetch(url, { credentials: 'same-origin' });
  return res.json();
}

async function apiPost(url, data) {
  const res = await fetch(url, {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data),
  });
  return res.json();
}

// ───────── Auth Helpers ─────────
async function getCurrentUser() {
  if (_currentUser !== undefined) return _currentUser;
  const data = await apiGet(`${API_AUTH}?action=me`);
  _currentUser = data.ok ? data.user : null;
  return _currentUser;
}

async function signup(userData) {
  const data = await apiPost(`${API_AUTH}?action=signup`, userData);
  if (data.ok) _currentUser = data.user;
  return data;
}

async function login(username, password) {
  const data = await apiPost(`${API_AUTH}?action=login`, { username, password });
  if (data.ok) _currentUser = data.user;
  return data;
}

async function logout() {
  await apiPost(`${API_AUTH}?action=logout`, {});
  _currentUser = null;
  window.location.href = 'index.php';
}

async function updateUser(updates) {
  const data = await apiPost(`${API_PROFILE}?action=update`, updates);
  if (data.ok) _currentUser = data.user;
  return data;
}

async function requireAuth() {
  const user = await getCurrentUser();
  if (!user) window.location.href = 'index.php';
  return user;
}

// ───────── Document Helpers ─────────
async function getDocuments() {
  const data = await apiGet(`${API_DOCS}?action=list`);
  return data.ok ? data.docs : [];
}

async function addDocument(doc) {
  const data = await apiPost(`${API_DOCS}?action=add`, doc);
  return data.ok ? data.doc : null;
}

async function deleteDocument(id) {
  return apiPost(`${API_DOCS}?action=delete`, { id });
}

async function getDocumentById(id) {
  const data = await apiGet(`${API_DOCS}?action=get&id=${encodeURIComponent(id)}`);
  return data.ok ? data.doc : null;
}

// ───────── View / Download Document ─────────
async function viewDocument(id) {
  const doc = await getDocumentById(id);
  if (!doc || !doc.data) { showToast('Cannot preview this document', 'error'); return; }
  const win = window.open();
  if (doc.fileType === 'application/pdf') {
    win.document.write(`<html><head><title>${doc.name}</title></head>
      <body style="margin:0;"><iframe src="${doc.data}" style="width:100%;height:100vh;border:none;"></iframe></body></html>`);
  } else {
    win.document.write(`<html><head><title>${doc.name}</title>
      <style>body{margin:0;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#f1f5f9;}
      img{max-width:95vw;max-height:95vh;border-radius:8px;box-shadow:0 8px 32px rgba(0,0,0,.15);}</style></head>
      <body><img src="${doc.data}" alt="${doc.name}"></body></html>`);
  }
}

async function downloadDocument(id) {
  const doc = await getDocumentById(id);
  if (!doc || !doc.data) { showToast('Cannot download this document', 'error'); return; }
  const a = document.createElement('a');
  a.href = doc.data;
  a.download = doc.fileName || doc.name;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  showToast('Download started', 'success');
}

// ───────── Category Helpers ─────────
function getCategoryIcon(cat) {
  const icons = {
    identity: '<i class="fas fa-id-card"></i>',
    education: '<i class="fas fa-graduation-cap"></i>',
    vehicle: '<i class="fas fa-car"></i>',
    insurance: '<i class="fas fa-shield-halved"></i>',
    other: '<i class="fas fa-file-lines"></i>'
  };
  return icons[cat] || icons.other;
}

function getCategoryBg(cat) {
  const bgs = {
    identity: 'rgba(26,115,232,.1)',
    education: 'rgba(124,77,255,.1)',
    vehicle: 'rgba(255,112,67,.1)',
    insurance: 'rgba(0,191,165,.1)',
    other: 'rgba(100,116,139,.1)'
  };
  return bgs[cat] || bgs.other;
}

function getCategoryColor(cat) {
  const colors = {
    identity: '#1a73e8',
    education: '#7c4dff',
    vehicle: '#ff7043',
    insurance: '#00bfa5',
    other: '#64748b'
  };
  return colors[cat] || colors.other;
}

function capitalize(str) {
  return str.charAt(0).toUpperCase() + str.slice(1);
}

// ───────── File Size Formatter ─────────
function formatFileSize(bytes) {
  if (bytes < 1024) return bytes + ' B';
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

// ───────── Toast Notifications ─────────
function showToast(message, type = 'info') {
  const container = document.getElementById('toastContainer');
  if (!container) return;
  const icons = { success: 'fa-circle-check', error: 'fa-circle-xmark', info: 'fa-circle-info' };
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `
    <span class="toast-icon"><i class="fas ${icons[type] || icons.info}"></i></span>
    <span class="toast-msg">${message}</span>
    <span class="toast-close" onclick="this.parentElement.classList.add('hiding'); setTimeout(()=>this.parentElement.remove(),300);">
      <i class="fas fa-xmark"></i>
    </span>
  `;
  container.appendChild(toast);
  setTimeout(() => {
    toast.classList.add('hiding');
    setTimeout(() => toast.remove(), 300);
  }, 3500);
}

// ───────── Toggle Password Visibility ─────────
function togglePassword(inputId, toggleEl) {
  const input = document.getElementById(inputId);
  const icon = toggleEl.querySelector('i');
  if (input.type === 'password') {
    input.type = 'text';
    icon.classList.replace('fa-eye', 'fa-eye-slash');
  } else {
    input.type = 'password';
    icon.classList.replace('fa-eye-slash', 'fa-eye');
  }
}

// ───────── Sidebar Toggle (mobile) ─────────
function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebarOverlay');
  if (sidebar) sidebar.classList.toggle('open');
  if (overlay) overlay.classList.toggle('show');
}

// ───────── Populate Sidebar User Info ─────────
async function populateSidebar() {
  const user = await getCurrentUser();
  if (!user) return;
  const nameEl   = document.getElementById('sidebarName');
  const emailEl  = document.getElementById('sidebarEmail');
  const avatarEl = document.getElementById('sidebarAvatar');
  if (nameEl)   nameEl.textContent   = user.name  || 'User';
  if (emailEl)  emailEl.textContent  = user.email || '';
  if (avatarEl) avatarEl.textContent = user.name  ? user.name[0].toUpperCase() : 'U';
}

// ───────── Dashboard Renderers ─────────
async function renderDashboardStats() {
  const docs = await getDocuments();
  const el = id => document.getElementById(id);
  if (el('statTotal'))    el('statTotal').textContent    = docs.length;
  if (el('statVerified')) el('statVerified').textContent = docs.filter(d => d.verified).length;
  if (el('statPending'))  el('statPending').textContent  = docs.filter(d => !d.verified).length;
  if (el('statShared'))   el('statShared').textContent   = docs.filter(d => d.shared).length;
}

async function renderRecentDocs() {
  const container = document.getElementById('recentDocsContainer');
  if (!container) return;
  const docs = (await getDocuments()).slice(0, 5);

  if (!docs.length) {
    container.innerHTML = `
      <div class="empty-state">
        <div class="empty-icon">📄</div>
        <h3>No documents yet</h3>
        <p>Upload your first document to see it here</p>
        <a href="upload.php" class="btn btn-primary btn-sm"><i class="fas fa-cloud-arrow-up"></i> Upload Now</a>
      </div>`;
    return;
  }

  container.innerHTML = `
    <table class="docs-table">
      <thead>
        <tr>
          <th>Document</th>
          <th>Category</th>
          <th>Date</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        ${docs.map(doc => `
          <tr>
            <td>
              <div class="doc-name-cell">
                <div class="doc-icon-wrap ${doc.category}">${getCategoryIcon(doc.category)}</div>
                <span>${doc.name}</span>
              </div>
            </td>
            <td>${capitalize(doc.category)}</td>
            <td>${doc.date}</td>
            <td>
              <span class="status-badge ${doc.verified ? 'verified' : 'pending'}">
                <i class="fas fa-${doc.verified ? 'circle-check' : 'clock'}"></i>
                ${doc.verified ? 'Verified' : 'Pending'}
              </span>
            </td>
            <td>
              <span class="action-dots" onclick="viewDocument('${doc.id}')" title="View">
                <i class="fas fa-eye"></i>
              </span>
              <span class="action-dots" onclick="downloadDocument('${doc.id}')" title="Download">
                <i class="fas fa-download"></i>
              </span>
            </td>
          </tr>
        `).join('')}
      </tbody>
    </table>`;
}
