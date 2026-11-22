<?php
/**
 * MultiMart - User Profile & Account Settings
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

require_login();
$user = db_fetch_one("SELECT u.*, r.display_name as role_display, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?", [$_SESSION['user_id']]);

$pageTitle = "My Profile – MultiMart";
$addresses = db_fetch_all("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC", [$user['id']]);

// Check if user has associated business
$business = null;
if ($user['role_name'] === 'business_seller' || $user['role_name'] === 'admin') {
    $business = db_fetch_one("SELECT * FROM businesses WHERE user_id = ?", [$user['id']]);
}

$error = '';
$success = '';

// Handle Profile Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Security token mismatch.";
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'update_profile') {
            $fullName = trim(sanitize_input($_POST['full_name'] ?? ''));
            $phone = trim(sanitize_input($_POST['phone'] ?? ''));
            $avatar = sanitize_input($_POST['avatar'] ?? $user['avatar']);

            if (empty($fullName)) {
                $error = "Full Name cannot be empty.";
            } else {
                db_update('users', [
                    'full_name' => $fullName,
                    'phone' => $phone,
                    'avatar' => $avatar
                ], 'id = ?', [$user['id']]);

                $_SESSION['user_name'] = $fullName;
                $_SESSION['user_avatar'] = $avatar;
                $success = "Profile details updated successfully!";
                $user = db_fetch_one("SELECT u.*, r.display_name as role_display, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?", [$user['id']]);
            }
        } elseif ($action === 'change_password') {
            $currentPwd = $_POST['current_password'] ?? '';
            $newPwd = $_POST['new_password'] ?? '';
            $confirmPwd = $_POST['confirm_password'] ?? '';

            if (!verify_password($currentPwd, $user['password_hash'])) {
                $error = "Current password is incorrect.";
            } elseif (strlen($newPwd) < 6) {
                $error = "New password must be at least 6 characters long.";
            } elseif ($newPwd !== $confirmPwd) {
                $error = "New passwords do not match.";
            } else {
                $newHash = hash_password($newPwd);
                db_update('users', ['password_hash' => $newHash], 'id = ?', [$user['id']]);
                log_activity('PASSWORD_CHANGED', 'USER', $user['id'], "User changed account password.");
                $success = "Your password has been changed securely.";
            }
        } elseif ($action === 'add_address') {
            $recipient = trim(sanitize_input($_POST['recipient_name'] ?? ''));
            $addrPhone = trim(sanitize_input($_POST['phone'] ?? ''));
            $street = trim(sanitize_input($_POST['street_address'] ?? ''));
            $city = trim(sanitize_input($_POST['city'] ?? ''));
            $state = trim(sanitize_input($_POST['state'] ?? ''));
            $postal = trim(sanitize_input($_POST['postal_code'] ?? ''));
            $type = sanitize_input($_POST['address_type'] ?? 'home');

            if (empty($recipient) || empty($street) || empty($city) || empty($postal)) {
                $error = "Please fill in all address fields.";
            } else {
                db_insert('user_addresses', [
                    'user_id' => $user['id'],
                    'address_type' => $type,
                    'recipient_name' => $recipient,
                    'phone' => $addrPhone,
                    'street_address' => $street,
                    'city' => $city,
                    'state' => $state,
                    'postal_code' => $postal,
                    'country' => 'India',
                    'is_default' => empty($addresses) ? 1 : 0
                ]);
                $success = "New address saved to your profile.";
                $addresses = db_fetch_all("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC", [$user['id']]);
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
  <div class="row g-4">
    <!-- Left Sidebar Profile Card -->
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-white">
        <img src="<?= base_url($user['avatar']) ?>" alt="Avatar" class="rounded-circle border mx-auto mb-3" width="100" height="100">
        <h5 class="fw-bold mb-1"><?= e($user['full_name']) ?></h5>
        <p class="text-muted small mb-2"><?= e($user['email']) ?></p>
        <span class="badge bg-primary text-uppercase px-3 py-1 rounded-pill small mb-3"><?= e($user['role_display']) ?></span>

        <div class="border-top pt-3 text-start small">
          <div class="d-flex justify-content-between py-1">
            <span class="text-muted">Account ID:</span>
            <span class="fw-semibold">#USR-<?= str_pad($user['id'], 5, '0', STR_PAD_LEFT) ?></span>
          </div>
          <div class="d-flex justify-content-between py-1">
            <span class="text-muted">Status:</span>
            <span class="badge bg-success-subtle text-success text-uppercase"><?= e($user['status']) ?></span>
          </div>
          <div class="d-flex justify-content-between py-1">
            <span class="text-muted">Joined On:</span>
            <span class="fw-semibold"><?= format_date($user['created_at'], 'd M Y') ?></span>
          </div>
          <?php if ($business): ?>
            <div class="d-flex justify-content-between py-1">
              <span class="text-muted">Company:</span>
              <span class="fw-semibold text-truncate ms-2" style="max-width: 150px;"><?= e($business['company_name']) ?></span>
            </div>
            <div class="d-flex justify-content-between py-1">
              <span class="text-muted">GST / Tax ID:</span>
              <span class="fw-semibold"><?= e($business['tax_id_gst']) ?></span>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Right Profile Tabs Content -->
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger py-2 small shadow-sm"><i class="bi bi-exclamation-triangle-fill me-1"></i> <?= e($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
          <div class="alert alert-success py-2 small shadow-sm"><i class="bi bi-check-circle-fill me-1"></i> <?= e($success) ?></div>
        <?php endif; ?>

        <ul class="nav nav-tabs border-bottom mb-4" id="profileTab" role="tablist">
          <li class="nav-item">
            <button class="nav-link active fw-semibold" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button">Personal Information</button>
          </li>
          <li class="nav-item">
            <button class="nav-link fw-semibold" id="addresses-tab" data-bs-toggle="tab" data-bs-target="#addresses" type="button">Saved Addresses (<?= count($addresses) ?>)</button>
          </li>
          <li class="nav-item">
            <button class="nav-link fw-semibold" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button">Security & Password</button>
          </li>
        </ul>

        <div class="tab-content" id="profileTabContent">
          <!-- Personal Info Tab -->
          <div class="tab-pane fade show active" id="personal" role="tabpanel">
            <form method="POST" action="">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="update_profile">

              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label small fw-semibold">Full Name</label>
                  <input type="text" name="full_name" class="form-control" value="<?= e($user['full_name']) ?>" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-semibold">Contact Phone</label>
                  <input type="tel" name="phone" class="form-control" value="<?= e($user['phone']) ?>">
                </div>
                <div class="col-md-12">
                  <label class="form-label small fw-semibold">Email Address (Read-only)</label>
                  <input type="email" class="form-control bg-light" value="<?= e($user['email']) ?>" readonly>
                </div>
                <div class="col-md-12">
                  <label class="form-label small fw-semibold">Profile Avatar Choice</label>
                  <select name="avatar" class="form-select">
                    <option value="assets/images/user-priya.png" <?= $user['avatar'] === 'assets/images/user-priya.png' ? 'selected' : '' ?>>Priya (Shopper Avatar)</option>
                    <option value="assets/images/user-john.png" <?= $user['avatar'] === 'assets/images/user-john.png' ? 'selected' : '' ?>>John (Seller Avatar)</option>
                    <option value="assets/images/vendor-apex.png" <?= $user['avatar'] === 'assets/images/vendor-apex.png' ? 'selected' : '' ?>>Corporate Business Avatar</option>
                    <option value="assets/images/freelancer-rahul.png" <?= $user['avatar'] === 'assets/images/freelancer-rahul.png' ? 'selected' : '' ?>>Freelancer Tech Avatar</option>
                    <option value="assets/images/admin-avatar.png" <?= $user['avatar'] === 'assets/images/admin-avatar.png' ? 'selected' : '' ?>>System Administrator Shield</option>
                    <option value="assets/images/default-avatar.png" <?= $user['avatar'] === 'assets/images/default-avatar.png' ? 'selected' : '' ?>>Default Clean Avatar</option>
                  </select>
                </div>
              </div>

              <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                Save Profile Changes
              </button>
            </form>
          </div>

          <!-- Saved Addresses Tab -->
          <div class="tab-pane fade" id="addresses" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="fw-bold mb-0">Delivery & Billing Addresses</h6>
              <button class="btn btn-outline-primary btn-sm rounded-pill" type="button" data-bs-toggle="collapse" data-bs-target="#newAddrForm">
                <i class="bi bi-plus-circle me-1"></i> Add New Address
              </button>
            </div>

            <!-- Add Address Collapsible Form -->
            <div class="collapse mb-4" id="newAddrForm">
              <div class="card card-body bg-light border-0 rounded-3 p-3">
                <h6 class="fw-bold small mb-2 text-primary">New Address Details</h6>
                <form method="POST" action="">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="add_address">
                  <div class="row g-2 mb-2">
                    <div class="col-md-6">
                      <input type="text" name="recipient_name" class="form-control form-control-sm" placeholder="Recipient Name" required>
                    </div>
                    <div class="col-md-6">
                      <input type="tel" name="phone" class="form-control form-control-sm" placeholder="Phone Number" required>
                    </div>
                    <div class="col-12">
                      <input type="text" name="street_address" class="form-control form-control-sm" placeholder="Street Address / Flat / Building" required>
                    </div>
                    <div class="col-md-4">
                      <input type="text" name="city" class="form-control form-control-sm" placeholder="City" required>
                    </div>
                    <div class="col-md-4">
                      <input type="text" name="state" class="form-control form-control-sm" placeholder="State" required>
                    </div>
                    <div class="col-md-4">
                      <input type="text" name="postal_code" class="form-control form-control-sm" placeholder="PIN / Postal Code" required>
                    </div>
                  </div>
                  <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">Save Address</button>
                </form>
              </div>
            </div>

            <div class="row g-3">
              <?php foreach ($addresses as $addr): ?>
                <div class="col-md-6">
                  <div class="border rounded-3 p-3 position-relative h-100 bg-white">
                    <?php if ($addr['is_default']): ?>
                      <span class="badge bg-success-subtle text-success position-absolute top-2 end-2 small">Default</span>
                    <?php endif; ?>
                    <div class="fw-bold"><?= e($addr['recipient_name']) ?> <span class="badge bg-secondary-subtle text-secondary small text-uppercase"><?= e($addr['address_type']) ?></span></div>
                    <div class="text-secondary small mt-1"><?= e($addr['street_address']) ?></div>
                    <div class="text-secondary small"><?= e($addr['city']) ?>, <?= e($addr['state']) ?> - <?= e($addr['postal_code']) ?></div>
                    <div class="text-muted small mt-1"><i class="bi bi-telephone me-1"></i> <?= e($addr['phone']) ?></div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Security Tab -->
          <div class="tab-pane fade" id="security" role="tabpanel">
            <form method="POST" action="">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="change_password">

              <div class="mb-3">
                <label class="form-label small fw-semibold">Current Password</label>
                <input type="password" name="current_password" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label small fw-semibold">New Password (Min. 6 characters)</label>
                <input type="password" name="new_password" class="form-control" required>
              </div>
              <div class="mb-4">
                <label class="form-label small fw-semibold">Confirm New Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
              </div>

              <button type="submit" class="btn btn-warning rounded-pill px-4 fw-semibold shadow-sm">
                Update Account Password
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
