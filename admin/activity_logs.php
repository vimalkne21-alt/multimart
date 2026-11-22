<?php
/**
 * MultiMart - Cybersecurity Activity Logs & Audit Trail (Admin)
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

require_role('admin');
$pageTitle = "Activity Audit Logs – Admin";

$logs = db_fetch_all("
    SELECT l.*, u.full_name as user_name, u.email as user_email
    FROM activity_logs l
    LEFT JOIN users u ON l.user_id = u.id
    ORDER BY l.id DESC
    LIMIT 50
");

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <span class="badge bg-danger text-uppercase fw-bold mb-1">Cybersecurity & Governance</span>
      <h3 class="fw-bold mb-0">Platform Audit Trail & Security Logs</h3>
    </div>
    <a href="<?= base_url('admin/dashboard.php') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
      Admin Dashboard
    </a>
  </div>

  <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <div class="table-responsive">
      <table class="table table-hover align-middle small mb-0">
        <thead class="table-light">
          <tr>
            <th>Timestamp</th>
            <th>Actor</th>
            <th>Action Code</th>
            <th>Entity</th>
            <th>IP Address</th>
            <th>Audit Details</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($logs as $l): ?>
            <tr>
              <td class="font-monospace text-muted" style="font-size: 0.75rem;"><?= format_date($l['created_at']) ?></td>
              <td>
                <?php if ($l['user_name']): ?>
                  <span class="fw-bold"><?= e($l['user_name']) ?></span>
                  <div class="text-muted" style="font-size: 0.7rem;"><?= e($l['user_email']) ?></div>
                <?php else: ?>
                  <span class="text-muted fst-italic">System / Anonymous</span>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge bg-secondary-subtle text-secondary font-monospace"><?= e($l['action']) ?></span>
              </td>
              <td>
                <?php if ($l['entity_type']): ?>
                  <span class="badge bg-light text-dark border"><?= e($l['entity_type']) ?> #<?= $l['entity_id'] ?></span>
                <?php else: ?>
                  <span class="text-muted">-</span>
                <?php endif; ?>
              </td>
              <td><code><?= e($l['ip_address']) ?></code></td>
              <td>
                <span class="text-secondary"><?= e($l['details']) ?></span>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
