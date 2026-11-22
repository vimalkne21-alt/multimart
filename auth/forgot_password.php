<?php
/**
 * MultiMart - Forgot Password Interface & Reset Token Generator
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$pageTitle = "Forgot Password – MultiMart";
$success = '';
$error = '';
$simulatedResetLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Security validation failed. Please refresh.";
    } else {
        $email = trim(sanitize_input($_POST['email'] ?? ''));
        if (empty($email)) {
            $error = "Please enter your registered email address.";
        } else {
            $user = db_fetch_one("SELECT * FROM users WHERE email = ?", [$email]);
            if ($user) {
                // Generate a cryptographically secure token
                $token = bin2hex(random_bytes(24));
                $_SESSION['pwd_reset_token'] = $token;
                $_SESSION['pwd_reset_email'] = $email;
                $_SESSION['pwd_reset_expiry'] = time() + 1800; // 30 mins

                $simulatedResetLink = base_url('auth/reset_password.php?token=' . $token);
                $success = "Password reset instructions have been generated for $email.";
                log_activity('PASSWORD_RESET_REQ', 'USER', $user['id'], "Password reset requested.");
            } else {
                // Do not reveal whether user exists for security, or show helpful message for demo
                $error = "No active account found with that email address.";
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5" style="max-width: 500px;">
  <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
    <div class="text-center mb-4">
      <div class="brand-logo justify-content-center mb-2">
        <i class="bi bi-key-fill text-warning fs-2"></i>
      </div>
      <h4 class="fw-bold">Password Recovery</h4>
      <p class="text-muted small">Enter your email to receive simulated reset instructions</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-triangle-fill me-1"></i> <?= e($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
      <div class="alert alert-success py-2 small"><i class="bi bi-check-circle-fill me-1"></i> <?= e($success) ?></div>
      <div class="p-3 bg-light rounded-3 border mb-3 small">
        <div class="fw-bold text-primary mb-1"><i class="bi bi-info-circle me-1"></i> Simulated Email Delivery (Demo Sandbox):</div>
        <p class="mb-2 text-muted">In an offline academic setting, email dispatch is safely simulated. Click the generated token link below:</p>
        <a href="<?= $simulatedResetLink ?>" class="btn btn-warning btn-sm w-100 fw-bold">Proceed to Reset Password</a>
      </div>
    <?php endif; ?>

    <form method="POST" action="">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label small fw-semibold">Registered Email Address</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-envelope"></i></span>
          <input type="email" name="email" class="form-control" placeholder="priya.sharma@multimart.com" required value="<?= e($_POST['email'] ?? '') ?>">
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill fw-semibold shadow-sm mb-3">
        Send Reset Link
      </button>

      <div class="text-center small">
        <a href="<?= base_url('auth/login.php') ?>" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Sign In</a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
