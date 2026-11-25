<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Manage your DigiLocker profile and account settings.">
  <title>DigiLocker — My Profile</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
  <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <div class="sidebar-logo">Digi<span>Locker</span></div>
    </div>
    <nav class="sidebar-nav">
      <div class="nav-section-title">Main Menu</div>
      <a href="dashboard.php"><span class="nav-icon"><i class="fas fa-th-large"></i></span> Dashboard</a>
      <a href="documents.php"><span class="nav-icon"><i class="fas fa-folder-open"></i></span> My Documents</a>
      <a href="upload.php"><span class="nav-icon"><i class="fas fa-cloud-arrow-up"></i></span> Upload Document</a>
      <div class="nav-section-title">Account</div>
      <a href="profile.php" class="active"><span class="nav-icon"><i class="fas fa-user-circle"></i></span> My Profile</a>
      <a href="#" onclick="logout()"><span class="nav-icon"><i class="fas fa-right-from-bracket"></i></span> Logout</a>
    </nav>
    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="user-avatar" id="sidebarAvatar">U</div>
        <div class="user-info">
          <div class="user-name" id="sidebarName">User</div>
          <div class="user-email" id="sidebarEmail">user@email.com</div>
        </div>
      </div>
    </div>
  </aside>

  <div class="app-layout">
    <div class="main-content">
      <header class="topbar">
        <div class="topbar-left">
          <div class="hamburger" onclick="toggleSidebar()"><i class="fas fa-bars"></i></div>
          <h1 class="page-title">My Profile</h1>
        </div>
        <div class="topbar-right"></div>
      </header>

      <div class="page-content">
        <div class="profile-grid">
          <!-- Profile Sidebar -->
          <div class="profile-sidebar">
            <div class="profile-avatar" id="profileAvatar">U</div>
            <h3 id="profileName">User Name</h3>
            <p class="profile-email" id="profileEmail">user@email.com</p>
            <p class="profile-aadhaar" id="profileAadhaar">XXXX XXXX XXXX</p>
            <div class="profile-stats">
              <div class="profile-stat">
                <div class="num" id="profileDocCount">0</div>
                <div class="lbl">Documents</div>
              </div>
              <div class="profile-stat">
                <div class="num" id="profileVerified">0</div>
                <div class="lbl">Verified</div>
              </div>
              <div class="profile-stat">
                <div class="num" id="profileStorage">0 KB</div>
                <div class="lbl">Storage Used</div>
              </div>
              <div class="profile-stat">
                <div class="num" id="profileJoined">—</div>
                <div class="lbl">Member Since</div>
              </div>
            </div>
          </div>

          <!-- Profile Details -->
          <div class="profile-details">
            <div class="section-header">
              <h2><i class="fas fa-user-pen" style="color:var(--blue-500); margin-right:8px;"></i>Edit Profile</h2>
              <button class="btn btn-primary btn-sm" id="saveProfileBtn" onclick="saveProfile()">
                <i class="fas fa-save"></i> Save
              </button>
            </div>

            <form id="profileForm">
              <div class="detail-row">
                <div class="form-group">
                  <label for="editName">Full Name</label>
                  <div class="input-wrapper">
                    <input type="text" id="editName" placeholder="Your full name">
                    <i class="fas fa-user input-icon"></i>
                  </div>
                </div>
                <div class="form-group">
                  <label for="editUsername">Username</label>
                  <div class="input-wrapper">
                    <input type="text" id="editUsername" disabled style="opacity:.6; cursor:not-allowed;">
                    <i class="fas fa-at input-icon"></i>
                  </div>
                </div>
              </div>

              <div class="detail-row">
                <div class="form-group">
                  <label for="editEmail">Email Address</label>
                  <div class="input-wrapper">
                    <input type="email" id="editEmail" placeholder="your@email.com">
                    <i class="fas fa-envelope input-icon"></i>
                  </div>
                </div>
                <div class="form-group">
                  <label for="editPhone">Phone Number</label>
                  <div class="input-wrapper">
                    <input type="tel" id="editPhone" placeholder="+91 XXXXX XXXXX">
                    <i class="fas fa-phone input-icon"></i>
                  </div>
                </div>
              </div>

              <div class="detail-row">
                <div class="form-group">
                  <label for="editAadhaar">Aadhaar Number</label>
                  <div class="input-wrapper">
                    <input type="text" id="editAadhaar" disabled style="opacity:.6; cursor:not-allowed;">
                    <i class="fas fa-fingerprint input-icon"></i>
                  </div>
                </div>
                <div class="form-group">
                  <label for="editDob">Date of Birth</label>
                  <div class="input-wrapper">
                    <input type="date" id="editDob">
                    <i class="fas fa-cake-candles input-icon"></i>
                  </div>
                </div>
              </div>

              <div class="detail-row">
                <div class="form-group" style="grid-column:1/-1;">
                  <label for="editAddress">Address</label>
                  <textarea id="editAddress" placeholder="Enter your address"></textarea>
                </div>
              </div>
            </form>

            <!-- Change Password -->
            <div style="margin-top:32px; padding-top:24px; border-top:1px solid var(--gray-200);">
              <h2 style="font-size:1.1rem; margin-bottom:16px;"><i class="fas fa-shield-halved"
                  style="color:var(--blue-500); margin-right:8px;"></i>Security</h2>
              <div class="detail-row">
                <div class="form-group">
                  <label for="currentPass">Current Password</label>
                  <div class="input-wrapper">
                    <input type="password" id="currentPass" placeholder="Enter current password">
                    <i class="fas fa-lock input-icon"></i>
                  </div>
                </div>
                <div class="form-group">
                  <label for="newPass">New Password</label>
                  <div class="input-wrapper">
                    <input type="password" id="newPass" placeholder="Enter new password">
                    <i class="fas fa-key input-icon"></i>
                  </div>
                </div>
              </div>
              <button class="btn btn-secondary btn-sm" onclick="changePassword()">
                <i class="fas fa-arrow-rotate-right"></i> Update Password
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="toast-container" id="toastContainer"></div>
  <script src="js/app.js"></script>
  <script>
    async function init() {
      await populateSidebar();
      const user = await getCurrentUser();
      if (!user) return;

      document.getElementById('profileAvatar').textContent = user.name ? user.name[0].toUpperCase() : 'U';
      document.getElementById('profileName').textContent   = user.name  || 'User';
      document.getElementById('profileEmail').textContent  = user.email || '';
      const maskedAadhaar = user.aadhaar ? 'XXXX XXXX ' + user.aadhaar.slice(-4) : 'Not linked';
      document.getElementById('profileAadhaar').textContent = maskedAadhaar;

      // Edit fields
      document.getElementById('editName').value     = user.name     || '';
      document.getElementById('editUsername').value = user.username || '';
      document.getElementById('editEmail').value    = user.email    || '';
      document.getElementById('editPhone').value    = user.phone    || '';
      document.getElementById('editAadhaar').value  = user.aadhaar
        ? user.aadhaar.replace(/(\d{4})(?=\d)/g, '$1 ') : '';
      document.getElementById('editDob').value     = user.dob     || '';
      document.getElementById('editAddress').value = user.address || '';

      // Stats
      const docs = await getDocuments();
      document.getElementById('profileDocCount').textContent = docs.length;
      document.getElementById('profileVerified').textContent = docs.filter(d => d.verified).length;
      document.getElementById('profileJoined').textContent   = user.joined || 'Today';

      // Estimate storage
      let totalBytes = 0;
      docs.forEach(d => { if (d.data) totalBytes += d.data.length; });
      document.getElementById('profileStorage').textContent = formatFileSize(totalBytes);
    }

    async function saveProfile() {
      const updated = {
        name   : document.getElementById('editName').value.trim(),
        email  : document.getElementById('editEmail').value.trim(),
        phone  : document.getElementById('editPhone').value.trim(),
        dob    : document.getElementById('editDob').value,
        address: document.getElementById('editAddress').value.trim()
      };
      const result = await updateUser(updated);
      if (result.ok) {
        showToast('Profile updated successfully!', 'success');
        populateSidebar();
        document.getElementById('profileAvatar').textContent = updated.name ? updated.name[0].toUpperCase() : 'U';
        document.getElementById('profileName').textContent   = updated.name;
        document.getElementById('profileEmail').textContent  = updated.email;
      } else {
        showToast(result.message || 'Update failed', 'error');
      }
    }

    async function changePassword() {
      const current = document.getElementById('currentPass').value;
      const newP    = document.getElementById('newPass').value;
      if (!current || !newP) { showToast('Fill in both password fields', 'error'); return; }
      if (newP.length < 6)   { showToast('New password must be at least 6 characters', 'error'); return; }

      const result = await apiPost('api/profile.php?action=change_password', {
        current_password: current,
        new_password: newP
      });
      if (result.ok) {
        showToast('Password updated!', 'success');
        document.getElementById('currentPass').value = '';
        document.getElementById('newPass').value     = '';
      } else {
        showToast(result.message || 'Password change failed', 'error');
      }
    }

    init();
  </script>
</body>

</html>
