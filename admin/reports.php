<?php
/**
 * MultiMart - Moderation & Community Safety Reports (Admin)
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

require_role('admin');
$pageTitle = "Moderation Reports – Admin";

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $reportId = (int)($_POST['report_id'] ?? 0);
        $action = $_POST['action'] ?? '';

        if ($action === 'dismiss') {
            db_update('reports', ['status' => 'dismissed'], 'id = ?', [$reportId]);
            set_flash('info', "Report marked as dismissed.");
        } elseif ($action === 'action_taken') {
            $rep = db_fetch_one("SELECT * FROM reports WHERE id = ?", [$reportId]);
            if ($rep && $rep['reported_type'] === 'product') {
                db_update('products', ['status' => 'inactive'], 'id = ?', [$rep['reported_id']]);
            }
            db_update('reports', ['status' => 'action_taken'], 'id = ?', [$reportId]);
            log_activity('MODERATION_ACTION_TAKEN', 'REPORT', $reportId, "Admin de-listed reported entity.");
            set_flash('success', "Action taken: Reported listing de-listed from catalog.");
        }
    }
}

$reports = db_fetch_all("
    SELECT r.*, u.full_name as reporter_name, u.email as reporter_email
    FROM reports r
    JOIN users u ON r.reporter_id = u.id
    ORDER BY r.id DESC
");

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <span class="badge bg-danger text-uppercase fw-bold mb-1">Content Moderation</span>
      <h3 class="fw-bold mb-0">Community Safety & Incident Reports</h3>
    </div>
    <a href="<?= base_url('admin/dashboard.php') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
      Admin Dashboard
    </a>
  </div>

  <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <?php if (empty($reports)): ?>
      <div class="text-center py-4 text-muted small">
        <i class="bi bi-shield-check fs-2 mb-2 d-block text-success"></i> No pending community moderation reports.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Reporter</th>
              <th>Target</th>
              <th>Flag Reason</th>
              <th>Details</th>
              <th>Status</th>
              <th>Reported On</th>
              <th class="text-end">Moderation</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($reports as $rep): ?>
              <tr>
                <td><code>#<?= $rep['id'] ?></code></td>
                <td>
                  <div class="fw-bold"><?= e($rep['reporter_name']) ?></div>
                  <div class="text-muted" style="font-size: 0.72rem;"><?= e($rep['reporter_email']) ?></div>
                </td>
                <td>
                  <span class="badge bg-light text-dark border text-uppercase"><?= e($rep['reported_type']) ?> #<?= $rep['reported_id'] ?></span>
                </td>
                <td class="fw-semibold text-danger"><?= e($rep['reason']) ?></td>
                <td>
                  <div class="text-truncate" style="max-width: 200px;" title="<?= e($rep['details']) ?>"><?= e($rep['details']) ?></div>
                </td>
                <td>
                  <?php
                    $sc = 'bg-warning text-dark';
                    if ($rep['status'] === 'action_taken') $sc = 'bg-danger text-white';
                    elseif ($rep['status'] === 'dismissed') $sc = 'bg-secondary text-white';
                  ?>
                  <span class="badge <?= $sc ?> text-uppercase"><?= str_replace('_', ' ', e($rep['status'])) ?></span>
                </td>
                <td><?= format_date($rep['created_at'], 'd M Y') ?></td>
                <td class="text-end">
                  <?php if ($rep['status'] === 'pending'): ?>
                    <form method="POST" action="" class="d-inline">
                      <?= csrf_field() ?>
                      <input type="hidden" name="report_id" value="<?= $rep['id'] ?>">
                      <input type="hidden" name="action" value="action_taken">
                      <button type="submit" class="btn btn-danger btn-sm rounded-pill px-2">De-List Item</button>
                    </form>
                    <form method="POST" action="" class="d-inline">
                      <?= csrf_field() ?>
                      <input type="hidden" name="report_id" value="<?= $rep['id'] ?>">
                      <input type="hidden" name="action" value="dismiss">
                      <button type="submit" class="btn btn-outline-secondary btn-sm rounded-pill px-2">Dismiss</button>
                    </form>
                  <?php else: ?>
                    <span class="text-muted small">Resolved</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
