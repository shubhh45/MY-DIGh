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
  <meta name="description" content="Upload documents to your DigiLocker digital wallet.">
  <title>DigiLocker — Upload Document</title>
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
      <a href="upload.php" class="active"><span class="nav-icon"><i class="fas fa-cloud-arrow-up"></i></span> Upload Document</a>
      <div class="nav-section-title">Account</div>
      <a href="profile.php"><span class="nav-icon"><i class="fas fa-user-circle"></i></span> My Profile</a>
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
          <h1 class="page-title">Upload Document</h1>
        </div>
        <div class="topbar-right"></div>
      </header>

      <div class="page-content">
        <div class="section-card">
          <div class="section-header">
            <h2><i class="fas fa-cloud-arrow-up" style="color:var(--blue-500); margin-right:8px;"></i>Add New Document</h2>
          </div>

          <!-- Drop Zone -->
          <div class="upload-area" id="uploadArea" onclick="document.getElementById('fileInput').click()">
            <div class="upload-icon"><i class="fas fa-cloud-arrow-up"></i></div>
            <h3>Drag &amp; drop your file here</h3>
            <p>or click to browse from your device</p>
            <p class="file-types">Supports: PDF, JPG, PNG, JPEG (Max 10 MB)</p>
            <input type="file" id="fileInput" accept=".pdf,.jpg,.jpeg,.png" hidden>
          </div>

          <!-- File Preview -->
          <div class="file-preview" id="filePreview">
            <div class="file-icon"><i class="fas fa-file"></i></div>
            <div class="file-info">
              <div class="file-name" id="previewName">file.pdf</div>
              <div class="file-size" id="previewSize">0 KB</div>
            </div>
            <span class="remove-file" onclick="removeFile()"><i class="fas fa-xmark"></i></span>
          </div>

          <!-- Upload Progress -->
          <div class="progress-bar-wrapper" id="progressWrapper">
            <div class="progress-bar">
              <div class="progress-fill" id="progressFill"></div>
            </div>
            <div class="progress-text" id="progressText">0%</div>
          </div>

          <!-- Form Fields -->
          <form id="uploadForm" class="upload-form" style="margin-top:24px;">
            <div class="form-group">
              <label for="docName">Document Name *</label>
              <div class="input-wrapper">
                <input type="text" id="docName" placeholder="e.g. Aadhaar Card" required>
                <i class="fas fa-file-signature input-icon"></i>
              </div>
            </div>

            <div class="form-group">
              <label for="docCategory">Category *</label>
              <select id="docCategory" required>
                <option value="">Select category</option>
                <option value="identity">🪪 Identity</option>
                <option value="education">🎓 Education</option>
                <option value="vehicle">🚗 Vehicle</option>
                <option value="insurance">🛡️ Insurance</option>
                <option value="other">📄 Other</option>
              </select>
            </div>

            <div class="form-group">
              <label for="issuingAuth">Issuing Authority</label>
              <div class="input-wrapper">
                <input type="text" id="issuingAuth" placeholder="e.g. UIDAI, RTO, University">
                <i class="fas fa-building-columns input-icon"></i>
              </div>
            </div>

            <div class="form-group">
              <label for="docDate">Date of Issue</label>
              <div class="input-wrapper">
                <input type="date" id="docDate">
                <i class="fas fa-calendar input-icon"></i>
              </div>
            </div>

            <div class="form-group full-width">
              <label for="docNotes">Notes (Optional)</label>
              <textarea id="docNotes" placeholder="Any additional notes about this document..."></textarea>
            </div>

            <div class="full-width" style="display:flex; gap:12px; justify-content:flex-end;">
              <button type="button" class="btn btn-secondary" onclick="window.location.href='documents.php'">Cancel</button>
              <button type="submit" class="btn btn-primary" id="uploadBtn">
                <i class="fas fa-cloud-arrow-up"></i> Upload Document
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="toast-container" id="toastContainer"></div>
  <script src="js/app.js"></script>
  <script>
    populateSidebar();

    let selectedFile = null;
    const uploadArea = document.getElementById('uploadArea');
    const fileInput  = document.getElementById('fileInput');

    // Drag & drop
    ['dragenter','dragover'].forEach(ev => {
      uploadArea.addEventListener(ev, e => { e.preventDefault(); uploadArea.classList.add('drag-over'); });
    });
    ['dragleave','drop'].forEach(ev => {
      uploadArea.addEventListener(ev, e => { e.preventDefault(); uploadArea.classList.remove('drag-over'); });
    });
    uploadArea.addEventListener('drop', e => {
      const file = e.dataTransfer.files[0];
      if (file) handleFile(file);
    });
    fileInput.addEventListener('change', e => {
      if (e.target.files[0]) handleFile(e.target.files[0]);
    });

    function handleFile(file) {
      const allowed = ['application/pdf','image/jpeg','image/png','image/jpg'];
      if (!allowed.includes(file.type)) {
        showToast('Invalid file type. Use PDF, JPG, or PNG.', 'error');
        return;
      }
      if (file.size > 10 * 1024 * 1024) {
        showToast('File too large. Max 10 MB.', 'error');
        return;
      }
      selectedFile = file;
      document.getElementById('previewName').textContent = file.name;
      document.getElementById('previewSize').textContent = formatFileSize(file.size);
      document.getElementById('filePreview').classList.add('show');
      uploadArea.style.display = 'none';

      // auto-fill name from filename
      const nameInput = document.getElementById('docName');
      if (!nameInput.value) {
        nameInput.value = file.name.replace(/\.[^/.]+$/, '').replace(/[_-]/g, ' ');
      }
    }

    function removeFile() {
      selectedFile = null;
      fileInput.value = '';
      document.getElementById('filePreview').classList.remove('show');
      uploadArea.style.display = '';
    }

    document.getElementById('uploadForm').addEventListener('submit', function(e) {
      e.preventDefault();
      if (!selectedFile) { showToast('Please select a file first', 'error'); return; }
      const name      = document.getElementById('docName').value.trim();
      const category  = document.getElementById('docCategory').value;
      const authority = document.getElementById('issuingAuth').value.trim();
      const date      = document.getElementById('docDate').value || new Date().toISOString().split('T')[0];
      const notes     = document.getElementById('docNotes').value.trim();

      if (!name || !category) { showToast('Name and category are required', 'error'); return; }

      // Show progress animation
      const wrapper = document.getElementById('progressWrapper');
      const fill    = document.getElementById('progressFill');
      const text    = document.getElementById('progressText');
      wrapper.classList.add('show');
      document.getElementById('uploadBtn').disabled = true;

      let progress = 0;
      const interval = setInterval(() => {
        progress += Math.random() * 20 + 5;
        if (progress > 100) progress = 100;
        fill.style.width = progress + '%';
        text.textContent = Math.round(progress) + '%';
        if (progress >= 100) {
          clearInterval(interval);
          // Read file as data URL, then POST to PHP API
          const reader = new FileReader();
          reader.onload = async function(ev) {
            const result = await addDocument({
              name,
              category,
              authority,
              date,
              notes,
              fileName : selectedFile.name,
              fileType : selectedFile.type,
              size     : formatFileSize(selectedFile.size),
              data     : ev.target.result,
              verified : Math.random() > 0.5
            });
            if (result) {
              showToast('Document uploaded successfully!', 'success');
              setTimeout(() => window.location.href = 'documents.php', 1000);
            } else {
              showToast('Upload failed. Please try again.', 'error');
              document.getElementById('uploadBtn').disabled = false;
            }
          };
          reader.readAsDataURL(selectedFile);
        }
      }, 200);
    });
  </script>
</body>
</html>
