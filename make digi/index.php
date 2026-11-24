<?php
// Redirect already-logged-in users to dashboard
session_start();
if (isset($_SESSION['username'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description"
    content="DigiLocker — Your digital document wallet. Store, access, and share your documents securely.">
  <title>DigiLocker — Sign In</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
  <div class="auth-page">
    <!-- Hero Section -->
    <div class="auth-hero">
      <div class="auth-hero-content">
        <div class="logo-large">CHODU<span>Locker</span></div>
        <p class="tagline">Jada bckchodi na kare</p>
        <ul class="features-list">
          <li><span class="icon"><i class="fas fa-shield-halved"></i></span> chut chut </li>
          <li><span class="icon"><i class="fas fa-cloud-arrow-up"></i></span> bhare bajar me </li>
          <li><span class="icon"><i class="fas fa-share-nodes"></i></span> Share verified documents digitally</li>
          <li><span class="icon"><i class="fas fa-fingerprint"></i></span> Aadhaar-linked authentication</li>
          <li><span class="icon"><i class="fas fa-mobile-screen"></i></span> Access from any device, anywhere</li>
        </ul>
      </div>
    </div>

    <!-- Login Form -->
    <div class="auth-form-section">
      <div class="auth-form-wrapper">
        <h1 class="form-title">HUM AMAR HAI BSDK </h1>
        <p class="form-subtitle">BHAK BSDK</p>

        <form id="loginForm" autocomplete="off">
          <div class="form-group">
            <label for="loginUsername">nam dal M.C</label>
            <div class="input-wrapper">
              <input type="text" id="loginUsername" placeholder="Enter username or Aadhaar" required>
              <i class="fas fa-user input-icon"></i>
            </div>
          </div>

          <div class="form-group">
            <label for="loginPassword">krupaya password sahi se dale varna ma chod denge </label>
            <div class="input-wrapper">
              <input type="password" id="loginPassword" placeholder="Enter your password" required>
              <i class="fas fa-lock input-icon"></i>
              <span class="toggle-password" onclick="togglePassword('loginPassword', this)">
                <i class="fas fa-eye"></i>
              </span>
            </div>
          </div>

          <div class="form-options">
            <label class="checkbox-wrapper">
              <input type="checkbox" checked> Jyada Na Hilai
            </label>
            <a href="#">Marli Mutthi?</a>
          </div>

          <button type="submit" class="btn btn-primary"
            style="width:100%; justify-content:center; font-size:1rem; padding:15px;">
            <i class="fas fa-right-to-bracket"></i> Sign In
          </button>
        </form>

        <div class="divider">or continue with</div>

        <button class="btn btn-secondary" style="width:100%; justify-content:center;"
          onclick="showToast('OTP login coming soon!', 'info')">
          <i class="fas fa-mobile-screen"></i> Sign In with OTP
        </button>

        <p class="form-footer">
          Don't have an account? <a href="signup.php">Create Account</a>
        </p>
      </div>
    </div>
  </div>

  <div class="toast-container" id="toastContainer"></div>
  <script src="js/app.js"></script>
  <script>
    document.getElementById('loginForm').addEventListener('submit', async function (e) {
      e.preventDefault();
      const username = document.getElementById('loginUsername').value.trim();
      const password = document.getElementById('loginPassword').value;
      const result = await login(username, password);
      if (result.ok) {
        showToast('Login successful! Redirecting...', 'success');
        setTimeout(() => window.location.href = 'dashboard.php', 800);
      } else {
        showToast(result.message || 'Invalid username or password', 'error');
      }
    });
  </script>
</body>

</html>
