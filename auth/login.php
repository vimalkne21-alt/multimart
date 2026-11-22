<?php
/**
 * MultiMart - User Login Portal with Quick Demo Autofill
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

// If already logged in, redirect to respective dashboard
if (is_logged_in()) {
    $role = $_SESSION['user_role_name'] ?? 'customer';
    if ($role === 'admin') header("Location: " . base_url('admin/dashboard.php'));
    elseif ($role === 'business_seller') header("Location: " . base_url('seller/dashboard.php'));
    elseif ($role === 'freelancer') header("Location: " . base_url('services/dashboard.php'));
    else header("Location: " . base_url('customer/dashboard.php'));
    exit;
}

$pageTitle = "Sign In – MultiMart";
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Security token mismatch. Please try again.";
    } else {
        $email = trim(sanitize_input($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = "Please enter both email and password.";
        } else {
            $user = db_fetch_one("
                SELECT u.*, r.name as role_name, r.display_name as role_display 
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                WHERE u.email = ? AND u.status = 'active'
            ", [$email]);

            if ($user && verify_password($password, $user['password_hash'])) {
                // Populate session
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['user_role_id'] = (int)$user['role_id'];
                $_SESSION['user_role_name'] = $user['role_name'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_avatar'] = $user['avatar'] ?: 'assets/images/default-avatar.png';

                log_activity('LOGIN_SUCCESS', 'USER', $user['id'], "User signed in with role {$user['role_name']}");
                set_flash('success', "Welcome back, " . htmlspecialchars($user['full_name']) . "!");

                // Role-based routing
                $target = $_SESSION['redirect_after_login'] ?? null;
                unset($_SESSION['redirect_after_login']);

                if ($target) {
                    header("Location: " . $target);
                } elseif ($user['role_name'] === 'admin') {
                    header("Location: " . base_url('admin/dashboard.php'));
                } elseif ($user['role_name'] === 'business_seller') {
                    header("Location: " . base_url('seller/dashboard.php'));
                } elseif ($user['role_name'] === 'freelancer') {
                    header("Location: " . base_url('services/dashboard.php'));
                } else {
                    header("Location: " . base_url('customer/dashboard.php'));
                }
                exit;
            } else {
                $error = "Invalid email credentials or inactive account status.";
                log_activity('LOGIN_FAILED', 'USER', null, "Failed login attempt for: $email");
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5" style="max-width: 520px;">
  <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
    <div class="text-center mb-4">
      <div class="brand-logo justify-content-center mb-2">
        <i class="bi bi-shop-window text-primary fs-2"></i>
        <span class="fs-3">MultiMart</span>
      </div>
      <h4 class="fw-bold">Sign In to Your Account</h4>
      <p class="text-muted small">Access your multi-model marketplace portal</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger py-2 small shadow-sm"><i class="bi bi-exclamation-triangle-fill me-1"></i> <?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <?= csrf_field() ?>

      <div class="mb-3">
        <label class="form-label small fw-semibold">Email Address</label>
        <div class="input-group">
          <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope"></i></span>
          <input type="email" name="email" id="loginEmail" class="form-control" placeholder="name@domain.com" required value="<?= e($_POST['email'] ?? '') ?>">
        </div>
      </div>

      <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center">
          <label class="form-label small fw-semibold mb-0">Password</label>
          <a href="<?= base_url('auth/forgot_password.php') ?>" class="small text-decoration-none text-primary">Forgot Password?</a>
        </div>
        <div class="input-group mt-1">
          <span class="input-group-text bg-light text-muted"><i class="bi bi-lock"></i></span>
          <input type="password" name="password" id="loginPassword" class="form-control" placeholder="••••••••" required>
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill fw-semibold shadow-sm mb-3">
        Sign In
      </button>

      <div class="text-center small text-muted">
        Don't have an account yet? <a href="<?= base_url('auth/register.php') ?>" class="text-primary fw-semibold text-decoration-none">Create Account</a>
      </div>
    </form>

    <!-- Quick Demo Accounts Autofill for Academic Evaluation -->
    <div class="mt-4 pt-3 border-top">
      <div class="text-uppercase fw-bold text-muted small text-center mb-2" style="font-size: 0.72rem;">
        ⚡ Instant Demo Role Login (1-Click Fill)
      </div>
      <div class="d-flex flex-wrap gap-1 justify-content-center">
        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill" onclick="fillLogin('admin@multimart.com', 'Admin@123')">
          <i class="bi bi-shield-lock me-1"></i> Admin
        </button>
        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" onclick="fillLogin('apex.supplies@multimart.com', 'Seller@123')">
          <i class="bi bi-building me-1"></i> B2B Seller
        </button>
        <button type="button" class="btn btn-outline-warning text-dark btn-sm rounded-pill" onclick="fillLogin('john.doe@multimart.com', 'User@123')">
          <i class="bi bi-person me-1"></i> C2C Seller
        </button>
        <button type="button" class="btn btn-outline-info text-dark btn-sm rounded-pill" onclick="fillLogin('priya.sharma@multimart.com', 'Customer@123')">
          <i class="bi bi-cart me-1"></i> Customer
        </button>
        <button type="button" class="btn btn-outline-success btn-sm rounded-pill" onclick="fillLogin('rahul.tech@multimart.com', 'Freelance@123')">
          <i class="bi bi-laptop me-1"></i> Freelancer
        </button>
      </div>
    </div>
  </div>
</div>

<script>
function fillLogin(email, pass) {
  document.getElementById('loginEmail').value = email;
  document.getElementById('loginPassword').value = pass;
  showToast('Demo Account Selected', 'Credentials filled for ' + email + '. Click Sign In!', 'info');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
