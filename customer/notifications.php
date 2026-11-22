<?php
/**
 * MultiMart - Notification Center
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

require_login();
$pageTitle = "Notifications – MultiMart";
$userId = (int)$_SESSION['user_id'];

// Handle Mark All Read
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (validate_csrf_token($_POST['csrf_token'] ?? '')) {
        db_update('notifications', ['is_read' => 1], 'user_id = ?', [$userId]);
        set_flash('success', "All notifications marked as read.");
        header("Location: " . base_url('customer/notifications.php'));
        exit;
    }
}

$notifications = db_fetch_all("
    SELECT * FROM notifications 
    WHERE user_id = ? 
    ORDER BY id DESC
", [$userId]);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4" style="max-width: 800px;">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h3 class="fw-bold mb-0"><i class="bi bi-bell-fill text-warning me-2"></i> Notification Center</h3>
      <p class="text-muted small mb-0">System updates, order transitions, RFQ bids and message alerts.</p>
    </div>
    <?php if (!empty($notifications)): ?>
      <form method="POST" action="">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-outline-primary btn-sm rounded-pill px-3">
          <i class="bi bi-check2-all me-1"></i> Mark All as Read
        </button>
      </form>
    <?php endif; ?>
  </div>

  <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <?php if (empty($notifications)): ?>
      <div class="text-center py-4 text-muted small">
        <i class="bi bi-bell-slash fs-2 mb-2 d-block"></i> You have no notifications at this time.
      </div>
    <?php else: ?>
      <div class="d-flex flex-column gap-3">
        <?php foreach ($notifications as $n): ?>
          <div class="p-3 rounded-3 border d-flex align-items-start gap-3 <?= $n['is_read'] ? 'bg-light' : 'bg-primary bg-opacity-10 border-primary' ?>">
            <div class="fs-4 <?= $n['is_read'] ? 'text-secondary' : 'text-primary' ?>">
              <?php
                if ($n['type'] === 'order') echo '<i class="bi bi-box-seam"></i>';
                elseif ($n['type'] === 'quote') echo '<i class="bi bi-file-earmark-text"></i>';
                elseif ($n['type'] === 'service') echo '<i class="bi bi-laptop"></i>';
                elseif ($n['type'] === 'message') echo '<i class="bi bi-chat-dots"></i>';
                else echo '<i class="bi bi-info-circle"></i>';
              ?>
            </div>
            <div class="flex-grow-1">
              <div class="d-flex justify-content-between align-items-baseline">
                <h6 class="fw-bold mb-1 small"><?= e($n['title']) ?></h6>
                <span class="text-muted" style="font-size: 0.7rem;"><?= format_date($n['created_at']) ?></span>
              </div>
              <p class="text-secondary small mb-2"><?= e($n['message']) ?></p>
              <?php if ($n['link']): ?>
                <a href="<?= base_url($n['link']) ?>" class="btn btn-link text-primary p-0 small text-decoration-none fw-semibold">
                  View Details <i class="bi bi-arrow-right"></i>
                </a>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
