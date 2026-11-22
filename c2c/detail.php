<?php
/**
 * MultiMart - C2C Pre-Owned Product Detail View & Inquiry System
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = db_fetch_one("
    SELECT p.*, c.name as category_name, u.full_name as seller_name, u.email as seller_email, u.avatar as seller_avatar, u.created_at as seller_joined,
           (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
    FROM products p
    JOIN categories c ON p.category_id = c.id
    JOIN users u ON p.seller_id = u.id
    WHERE p.id = ? AND p.is_c2c = 1
", [$id]);

if (!$product) {
    set_flash('error', "Pre-owned listing not found.");
    header("Location: " . base_url('c2c/index.php'));
    exit;
}

$pageTitle = e($product['title']) . " (Pre-Owned C2C) – MultiMart";

// Handle In-App Inquiry Message Submission
$msgError = '';
$msgSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_inquiry') {
    if (!is_logged_in()) {
        $msgError = "Please sign in to message this seller.";
    } elseif (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $msgError = "Security token mismatch.";
    } else {
        $messageText = trim(sanitize_input($_POST['message_text'] ?? ''));
        if (empty($messageText)) {
            $msgError = "Please write a message before sending.";
        } else {
            // Find or create conversation
            $conv = db_fetch_one("
                SELECT id FROM conversations 
                WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
                  AND related_type = 'product' AND related_id = ?
            ", [$_SESSION['user_id'], $product['seller_id'], $product['seller_id'], $_SESSION['user_id'], $product['id']]);

            $convId = $conv['id'] ?? 0;
            if (!$convId) {
                $convId = db_insert('conversations', [
                    'sender_id' => $_SESSION['user_id'],
                    'receiver_id' => $product['seller_id'],
                    'subject' => "Inquiry regarding " . $product['title'],
                    'related_type' => 'product',
                    'related_id' => $product['id']
                ]);
            }

            db_insert('messages', [
                'conversation_id' => $convId,
                'sender_id' => $_SESSION['user_id'],
                'message_text' => $messageText,
                'is_read' => 0
            ]);

            send_notification($product['seller_id'], "New Inquiry on {$product['title']}", "A buyer sent you a message: " . substr($messageText, 0, 80), 'messages/inbox.php', 'message');
            $msgSuccess = "Your message has been sent to {$product['seller_name']}. Check your inbox for replies!";
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb small">
      <li class="breadcrumb-item"><a href="<?= base_url('index.php') ?>" class="text-decoration-none">Home</a></li>
      <li class="breadcrumb-item"><a href="<?= base_url('c2c/index.php') ?>" class="text-decoration-none">C2C Marketplace</a></li>
      <li class="breadcrumb-item active" aria-current="page"><?= e($product['title']) ?></li>
    </ol>
  </nav>

  <div class="row g-5">
    <!-- Image -->
    <div class="col-lg-5">
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white p-3">
        <div class="product-img-box rounded-3" style="padding-top: 75%;">
          <img src="<?= base_url($product['primary_image'] ?? 'assets/images/products/cs-textbooks-1.jpg') ?>" alt="<?= e($product['title']) ?>">
        </div>
      </div>
    </div>

    <!-- Details -->
    <div class="col-lg-7">
      <div class="d-flex align-items-center gap-2 mb-2">
        <span class="badge badge-c2c text-uppercase">C2C Peer-to-Peer</span>
        <span class="badge bg-warning-subtle text-warning-emphasis text-uppercase fw-bold">
          Condition: <?= str_replace('_', ' ', e($product['condition_type'])) ?>
        </span>
        <span class="text-muted small"><i class="bi bi-geo-alt me-1"></i> <?= e($product['location']) ?></span>
      </div>

      <h2 class="fw-bold mb-2"><?= e($product['title']) ?></h2>

      <div class="p-3 rounded-4 bg-light mb-4 border">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="text-muted small">Seller's Asking Price:</div>
            <div class="fs-2 fw-extrabold text-dark"><?= format_currency($product['discount_price'] ?: $product['price']) ?></div>
          </div>
          <button class="btn btn-primary rounded-pill px-4 fw-semibold btn-add-to-cart" data-product-id="<?= $product['id'] ?>">
            <i class="bi bi-bag-check me-1"></i> Buy Through Platform
          </button>
        </div>
      </div>

      <!-- Seller Profile Card -->
      <div class="card border rounded-4 p-3 bg-white mb-4 shadow-sm">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-3">
            <img src="<?= base_url($product['seller_avatar']) ?>" alt="Seller" class="rounded-circle border" width="50" height="50">
            <div>
              <h6 class="fw-bold mb-0"><?= e($product['seller_name']) ?></h6>
              <div class="text-muted small">Member since: <?= format_date($product['seller_joined'], 'M Y') ?></div>
            </div>
          </div>
          <span class="badge bg-success-subtle text-success small">Verified Individual</span>
        </div>
      </div>

      <!-- In-App Messaging Box -->
      <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
        <h5 class="fw-bold mb-2"><i class="bi bi-chat-left-dots text-warning me-2"></i> Message Seller Directly</h5>
        <p class="text-muted small mb-3">Ask questions regarding inspection, price negotiations, or pickup time.</p>

        <?php if (!empty($msgError)): ?>
          <div class="alert alert-danger py-2 small"><?= e($msgError) ?></div>
        <?php endif; ?>
        <?php if (!empty($msgSuccess)): ?>
          <div class="alert alert-success py-2 small"><i class="bi bi-check-circle me-1"></i> <?= e($msgSuccess) ?></div>
        <?php endif; ?>

        <?php if (is_logged_in()): ?>
          <form method="POST" action="">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="send_inquiry">
            <div class="mb-3">
              <textarea name="message_text" class="form-control form-control-sm" rows="3" placeholder="Hi <?= e($product['seller_name']) ?>, is this item still available? Can we meet near..." required></textarea>
            </div>
            <div class="d-flex justify-content-between align-items-center">
              <button type="submit" class="btn btn-warning text-dark btn-sm rounded-pill px-4 fw-bold">
                <i class="bi bi-send-fill me-1"></i> Send Message
              </button>
              <a href="<?= base_url('c2c/report.php?id=' . $product['id']) ?>" class="text-danger small text-decoration-none">
                <i class="bi bi-flag me-1"></i> Report Inaccurate Listing
              </a>
            </div>
          </form>
        <?php else: ?>
          <div class="alert alert-secondary small mb-0">
            <i class="bi bi-lock me-1"></i> Please <a href="<?= base_url('auth/login.php') ?>" class="fw-bold">Sign In</a> to send internal messages to this seller.
          </div>
        <?php endif; ?>
      </div>

      <!-- Description -->
      <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
        <h5 class="fw-bold mb-3">Item Details & Condition Notes</h5>
        <div class="text-secondary small" style="line-height: 1.7;">
          <?= nl2br(e($product['description'])) ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
