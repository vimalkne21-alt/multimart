<?php
/**
 * MultiMart - User Registration System
 * Role-Based Onboarding for Customers, B2B Businesses, C2C Sellers, and Freelancers
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

if (is_logged_in()) {
    header("Location: " . base_url('customer/dashboard.php'));
    exit;
}

$pageTitle = "Create an Account – MultiMart";
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Security token validation failed. Please try again.";
    } else {
        $fullName = trim(sanitize_input($_POST['full_name'] ?? ''));
        $email = trim(sanitize_input($_POST['email'] ?? ''));
        $phone = trim(sanitize_input($_POST['phone'] ?? ''));
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $roleId = (int)($_POST['role_id'] ?? 4);

        // Validation
        if (empty($fullName) || empty($email) || empty($password)) {
            $error = "Full Name, Email, and Password are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please provide a valid email address.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters in length.";
        } elseif ($password !== $confirmPassword) {
            $error = "Password confirmation does not match.";
        } else {
            // Check if email already registered
            $exists = db_fetch_one("SELECT id FROM users WHERE email = ?", [$email]);
            if ($exists) {
                $error = "This email address is already registered. Please sign in instead.";
            } else {
                // Determine role name
                $roleRow = db_fetch_one("SELECT name FROM roles WHERE id = ?", [$roleId]);
                $roleName = $roleRow['name'] ?? 'customer';

                $passwordHash = hash_password($password);

                // Insert into users
                $userId = db_insert('users', [
                    'role_id' => $roleId,
                    'full_name' => $fullName,
                    'email' => $email,
                    'password_hash' => $passwordHash,
                    'phone' => $phone,
                    'avatar' => 'assets/images/default-avatar.png',
                    'status' => 'active',
                    'email_verified' => 1
                ]);

                // Additional setup if Business Seller
                if ($roleName === 'business_seller') {
                    $companyName = trim(sanitize_input($_POST['company_name'] ?? $fullName . ' Enterprise'));
                    $taxId = trim(sanitize_input($_POST['tax_id_gst'] ?? 'GST-PENDING-' . rand(1000, 9999)));
                    $bizType = sanitize_input($_POST['business_type'] ?? 'Wholesaler');

                    $bizId = db_insert('businesses', [
                        'user_id' => $userId,
                        'company_name' => $companyName,
                        'business_type' => $bizType,
                        'registration_number' => 'REG-' . rand(100000, 999999),
                        'tax_id_gst' => $taxId,
                        'official_email' => $email,
                        'phone' => $phone,
                        'address' => 'Corporate Office Headquarters',
                        'verified_status' => 'verified'
                    ]);

                    db_insert('business_verifications', [
                        'business_id' => $bizId,
                        'document_type' => 'GST Registration Certificate',
                        'document_file' => 'uploads/documents/default_gst.pdf',
                        'verification_status' => 'approved',
                        'reviewed_by' => 1,
                        'review_notes' => 'Self-registered and auto-verified in academic sandbox mode.',
                        'reviewed_at' => date('Y-m-d H:i:s')
                    ]);
                }

                // Log Activity
                log_activity('USER_REGISTERED', 'USER', $userId, "New user registered with role: $roleName");

                // Establish Session
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_role_id'] = $roleId;
                $_SESSION['user_role_name'] = $roleName;
                $_SESSION['user_name'] = $fullName;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_avatar'] = 'assets/images/default-avatar.png';

                // Send Welcome Notification
                send_notification($userId, 'Welcome to MultiMart!', 'Your account has been created. Explore our multi-model e-commerce marketplace.', 'customer/dashboard.php', 'system');

                set_flash('success', "Registration successful! Welcome to MultiMart, $fullName.");

                // Redirect based on role
                if ($roleName === 'business_seller') {
                    header("Location: " . base_url('seller/dashboard.php'));
                } elseif ($roleName === 'freelancer') {
                    header("Location: " . base_url('services/dashboard.php'));
                } else {
                    header("Location: " . base_url('customer/dashboard.php'));
                }
                exit;
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5" style="max-width: 620px;">
  <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
    <div class="text-center mb-4">
      <div class="brand-logo justify-content-center mb-2">
        <i class="bi bi-shop-window text-primary fs-2"></i>
        <span class="fs-3">MultiMart</span>
      </div>
      <h4 class="fw-bold">Create Your Free Account</h4>
      <p class="text-muted small">Choose your marketplace role to begin</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger py-2 small shadow-sm"><i class="bi bi-exclamation-triangle-fill me-1"></i> <?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <?= csrf_field() ?>

      <!-- Role Selection -->
      <div class="mb-3">
        <label class="form-label small fw-semibold">Select Account Type / Role <span class="text-danger">*</span></label>
        <select name="role_id" id="roleSelector" class="form-select" onchange="toggleRoleFields(this.value)">
          <option value="4" selected>Customer (Browse & Purchase Products / Hire Services)</option>
          <option value="2">Business Seller (Wholesale B2B & Retail B2C Vendor)</option>
          <option value="3">Individual Seller (Sell Pre-Owned C2C Goods)</option>
          <option value="5">Freelancer (Offer Skills & Services to Businesses)</option>
        </select>
      </div>

      <!-- Common Fields -->
      <div class="row g-2 mb-3">
        <div class="col-md-6">
          <label class="form-label small fw-semibold">Full Name <span class="text-danger">*</span></label>
          <input type="text" name="full_name" class="form-control" placeholder="Priya Sharma" required value="<?= e($_POST['full_name'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-semibold">Phone Number</label>
          <input type="tel" name="phone" class="form-control" placeholder="+91 98765 43210" value="<?= e($_POST['phone'] ?? '') ?>">
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label small fw-semibold">Email Address <span class="text-danger">*</span></label>
        <input type="email" name="email" class="form-control" placeholder="priya@example.com" required value="<?= e($_POST['email'] ?? '') ?>">
      </div>

      <!-- Dynamic Business Fields -->
      <div id="businessFields" class="p-3 mb-3 bg-light rounded-3 border" style="display: none;">
        <h6 class="fw-bold text-primary mb-2 small"><i class="bi bi-building me-1"></i> Business Details (B2B)</h6>
        <div class="mb-2">
          <label class="form-label small fw-semibold">Company Name</label>
          <input type="text" name="company_name" class="form-control form-control-sm" placeholder="e.g. Acme Tech Solutions Pvt Ltd">
        </div>
        <div class="row g-2">
          <div class="col-6">
            <label class="form-label small fw-semibold">GST / Tax Identification</label>
            <input type="text" name="tax_id_gst" class="form-control form-control-sm" placeholder="29ABCDE1234F1Z5">
          </div>
          <div class="col-6">
            <label class="form-label small fw-semibold">Business Model</label>
            <select name="business_type" class="form-select form-select-sm">
              <option value="Wholesaler">Wholesaler</option>
              <option value="Manufacturer">Manufacturer</option>
              <option value="Distributor">Distributor</option>
            </select>
          </div>
        </div>
      </div>

      <div class="row g-2 mb-3">
        <div class="col-md-6">
          <label class="form-label small fw-semibold">Password <span class="text-danger">*</span></label>
          <input type="password" name="password" class="form-control" placeholder="Min. 6 characters" required>
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-semibold">Confirm Password <span class="text-danger">*</span></label>
          <input type="password" name="confirm_password" class="form-control" placeholder="Repeat password" required>
        </div>
      </div>

      <div class="form-check mb-4 small">
        <input class="form-check-input" type="checkbox" id="termsCheck" required checked>
        <label class="form-check-label text-muted" for="termsCheck">
          I agree to the <a href="<?= base_url('terms.php') ?>" class="text-primary text-decoration-none" target="_blank">Terms & Conditions</a> and <a href="<?= base_url('privacy.php') ?>" class="text-primary text-decoration-none" target="_blank">Privacy Policy</a>.
        </label>
      </div>

      <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill fw-semibold shadow-sm mb-3">
        Create Account
      </button>

      <div class="text-center small text-muted">
        Already have an account? <a href="<?= base_url('auth/login.php') ?>" class="text-primary fw-semibold text-decoration-none">Sign In</a>
      </div>
    </form>
  </div>
</div>

<script>
function toggleRoleFields(roleId) {
  const bizBox = document.getElementById('businessFields');
  if (roleId === '2') {
    bizBox.style.display = 'block';
  } else {
    bizBox.style.display = 'none';
  }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
