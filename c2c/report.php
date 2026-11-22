<?php
/**
 * MultiMart - C2C Listing Moderation Report Form
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

require_login();

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = db_fetch_one("SELECT * FROM products WHERE id = ?", [$productId]);

if (!$product) {
    header("Location: " . base_url('c2c/index.php'));
    exit;
}

$pageTitle = "Report Listing – MultiMart";
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Security token mismatch.";
    } else {
        $reason = sanitize_input($_POST['reason'] ?? 'Misleading Information');
        $details = trim(sanitize_input($_POST['details'] ?? ''));

        if (empty($details)) {
            $error = "Please provide specific details explaining the report.";
        } else {
            db_insert('reports', [
                'reporter_id' => $_SESSION['user_id'],
                'reported_type' => 'product',
                'reported_id' => $productId,
                'reason' => $reason,
                'details' => $details,
                'status' => 'pending'
            ]);

            log_activity('LISTING_REPORTED', 'PRODUCT', $productId, "Listing reported for: $reason");
            set_flash('success', "Thank you. Your report has been submitted to platform moderators.");
            header("Location: " . base_url('c2c/detail.php?id=' . $productId));
            exit;
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5" style="max-width: 600px;">
  <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
    <div class="text-center mb-4">
      <i class="bi bi-flag-fill text-danger fs-1"></i>
      <h4 class="fw-bold mt-2">Report Marketplace Listing</h4>
      <p class="text-muted small">Reporting: <strong><?= e($product['title']) ?></strong></p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger py-2 small"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <?= csrf_field() ?>

      <div class="mb-3">
        <label class="form-label small fw-semibold">Reason for Report <span class="text-danger">*</span></label>
        <select name="reason" class="form-select" required>
          <option value="Misleading Condition or Fake Photos">Misleading Condition or Fake Photos</option>
          <option value="Suspected Counterfeit or Pirated Book">Suspected Counterfeit or Pirated Book</option>
          <option value="Prohibited or Inappropriate Item">Prohibited or Inappropriate Item</option>
          <option value="Unresponsive or Fraudulent Seller">Unresponsive or Fraudulent Seller</option>
          <option value="Other Policy Violation">Other Policy Violation</option>
        </select>
      </div>

      <div class="mb-4">
        <label class="form-label small fw-semibold">Detailed Description <span class="text-danger">*</span></label>
        <textarea name="details" class="form-control" rows="4" placeholder="Explain the specific issue with this listing..." required></textarea>
      </div>

      <button type="submit" class="btn btn-danger w-100 py-2 rounded-pill fw-bold shadow-sm mb-3">
        Submit Report for Review
      </button>

      <div class="text-center small">
        <a href="<?= base_url('c2c/detail.php?id=' . $productId) ?>" class="text-decoration-none text-muted">Cancel and Go Back</a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
