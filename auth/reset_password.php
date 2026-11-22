<?php
/**
 * MultiMart - Password Reset Processor
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$pageTitle = "Reset Password – MultiMart";
$token = $_GET['token'] ?? '';
$error = '';
$success = false;

// Validate token in session
$validSessionToken = $_SESSION['pwd_reset_token'] ?? '';
$resetEmail = $_SESSION['pwd_reset_email'] ?? '';
$expiry = $_SESSION['pwd_reset_expiry'] ?? 0;

$isValid = !empty($token) && hash_equals($validSessionToken, $token) && time() <= $expiry;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Security validation failed. Please retry.";
    } elseif (!$isValid) {
        $error = "This password reset token has expired or is invalid. Please request a new link.";
    } else {
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (strlen($newPassword) < 6) {
            $error = "New password must be at least 6 characters long.";
        } elseif ($newPassword !== $confirmPassword) {
            $error = "Passwords do not match.";
        } else {
            $hashed = hash_password($newPassword);
            db_update('users', ['password_hash' => $hashed], 'email = ?', [$resetEmail]);
            log_activity('PASSWORD_RESET_SUCCESS', 'USER', null, "Password updated successfully for: $resetEmail");

            // Invalidate token
            unset($_SESSION['pwd_reset_token'], $_SESSION['pwd_reset_email'], $_SESSION['pwd_reset_expiry']);
            $success = true;
            set_flash('success', "Your password has been reset successfully. Please sign in with your new credentials.");
            header("Location: " . base_url('auth/login.php'));
            exit;
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5" style="max-width: 500px;">
  <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
    <div class="text-center mb-4">
      <h4 class="fw-bold">Create New Password</h4>
      <p class="text-muted small">Choose a secure password for your MultiMart account</p>
    </div>

    <?php if (!$isValid): ?>
      <div class="alert alert-warning small">
        <i class="bi bi-exclamation-triangle me-1"></i> Invalid or expired reset link. Please <a href="<?= base_url('auth/forgot_password.php') ?>" class="fw-bold">request a new link</a>.
      </div>
    <?php else: ?>
      <?php if (!empty($error)): ?>
        <div class="alert alert-danger py-2 small"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="">
        <?= csrf_field() ?>
        <div class="mb-3">
          <label class="form-label small fw-semibold">New Password</label>
          <input type="password" name="new_password" class="form-control" placeholder="Min. 6 characters" required>
        </div>

        <div class="mb-4">
          <label class="form-label small fw-semibold">Confirm New Password</label>
          <input type="password" name="confirm_password" class="form-control" placeholder="Repeat new password" required>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill fw-semibold shadow-sm">
          Update Password
        </button>
      </form>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
