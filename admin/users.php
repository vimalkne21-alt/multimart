<?php
/**
 * MultiMart - Administrator Users & Roles Management
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

require_role('admin');
$pageTitle = "User & Roles Management – Admin";

// Handle Status & Role Modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $targetUserId = (int)($_POST['target_user_id'] ?? 0);
        $action = $_POST['action'] ?? '';

        if ($targetUserId !== (int)$_SESSION['user_id']) { // Do not lock self out
            if ($action === 'toggle_status') {
                $current = db_fetch_one("SELECT status FROM users WHERE id = ?", [$targetUserId]);
                $newStatus = ($current['status'] === 'active') ? 'suspended' : 'active';
                db_update('users', ['status' => $newStatus], 'id = ?', [$targetUserId]);
                log_activity('USER_STATUS_TOGGLED', 'USER', $targetUserId, "Admin changed user status to: $newStatus");
                set_flash('success', "User account status changed to: " . strtoupper($newStatus));
            } elseif ($action === 'update_role') {
                $newRoleId = (int)($_POST['new_role_id'] ?? 4);
                db_update('users', ['role_id' => $newRoleId], 'id = ?', [$targetUserId]);
                log_activity('USER_ROLE_MODIFIED', 'USER', $targetUserId, "Admin changed user role to ID: $newRoleId");
                set_flash('success', "User role updated successfully.");
            }
        } else {
            set_flash('error', "Cannot modify status of the currently authenticated administrator.");
        }
    }
}

$users = db_fetch_all("
    SELECT u.*, r.display_name as role_display, r.name as role_name
    FROM users u
    JOIN roles r ON u.role_id = r.id
    ORDER BY u.id ASC
");
$roles = db_fetch_all("SELECT * FROM roles ORDER BY id ASC");

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <span class="badge bg-danger text-uppercase fw-bold mb-1">Access Control</span>
      <h3 class="fw-bold mb-0">Registered Users & Role Allocations</h3>
    </div>
    <a href="<?= base_url('admin/dashboard.php') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
      <i class="bi bi-arrow-left me-1"></i> Admin Dashboard
    </a>
  </div>

  <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <div class="table-responsive">
      <table class="table table-hover align-middle small mb-0">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>User</th>
            <th>Role</th>
            <th>Phone</th>
            <th>Account Status</th>
            <th>Registered On</th>
            <th class="text-end">Administrative Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><code>#<?= $u['id'] ?></code></td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <img src="<?= base_url($u['avatar']) ?>" class="rounded-circle border" width="36" height="36" alt="Avatar">
                  <div>
                    <div class="fw-bold"><?= e($u['full_name']) ?></div>
                    <div class="text-muted" style="font-size: 0.72rem;"><?= e($u['email']) ?></div>
                  </div>
                </div>
              </td>
              <td>
                <form method="POST" action="" class="d-inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="update_role">
                  <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                  <select name="new_role_id" class="form-select form-select-sm" style="width: 140px;" onchange="this.form.submit()">
                    <?php foreach ($roles as $r): ?>
                      <option value="<?= $r['id'] ?>" <?= $u['role_id'] == $r['id'] ? 'selected' : '' ?>><?= e($r['display_name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </form>
              </td>
              <td><?= e($u['phone'] ?: 'N/A') ?></td>
              <td>
                <span class="badge <?= $u['status'] === 'active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?> text-uppercase">
                  <?= e($u['status']) ?>
                </span>
              </td>
              <td><?= format_date($u['created_at'], 'd M Y') ?></td>
              <td class="text-end">
                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                  <form method="POST" action="" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="toggle_status">
                    <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                    <button type="submit" class="btn btn-sm rounded-pill px-3 <?= $u['status'] === 'active' ? 'btn-outline-danger' : 'btn-outline-success' ?>">
                      <?= $u['status'] === 'active' ? 'Suspend' : 'Activate' ?>
                    </button>
                  </form>
                <?php else: ?>
                  <span class="badge bg-light text-muted border">Active Self</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
