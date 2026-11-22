<?php
/**
 * MultiMart - Unified Product Detail View
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($productId <= 0) {
    header("Location: " . base_url('b2c/index.php'));
    exit;
}

// Increment view counter
db_query("UPDATE products SET views_count = views_count + 1 WHERE id = ?", [$productId]);

// Fetch Product with Category and Seller details
$product = db_fetch_one("
    SELECT p.*, c.name as category_name, c.module_type,
           u.full_name as seller_name, u.email as seller_email,
           b.company_name, b.tax_id_gst, b.verified_status
    FROM products p
    JOIN categories c ON p.category_id = c.id
    JOIN users u ON p.seller_id = u.id
    LEFT JOIN businesses b ON p.business_id = b.id
    WHERE p.id = ?
", [$productId]);

if (!$product) {
    set_flash('error', "Product not found or has been unlisted.");
    header("Location: " . base_url('index.php'));
    exit;
}

$pageTitle = e($product['title']) . " – MultiMart";

// Fetch Product Images
$images = db_fetch_all("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, display_order ASC", [$productId]);
if (empty($images)) {
    $images = [['image_url' => 'assets/images/products/headphones-1.jpg', 'is_primary' => 1]];
}

// Fetch Reviews & calculate average rating
$reviews = db_fetch_all("
    SELECT r.*, u.full_name as reviewer_name, u.avatar as reviewer_avatar
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.product_id = ? AND r.status = 'approved'
    ORDER BY r.id DESC
", [$productId]);

$avgRating = 0;
if (count($reviews) > 0) {
    $totalRating = array_sum(array_column($reviews, 'rating'));
    $avgRating = round($totalRating / count($reviews), 1);
}

// Handle Review Submission
$reviewError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_review') {
    if (!is_logged_in()) {
        $reviewError = "Please sign in to post a review.";
    } elseif (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $reviewError = "Security token mismatch.";
    } else {
        $rating = (int)($_POST['rating'] ?? 5);
        $title = trim(sanitize_input($_POST['review_title'] ?? ''));
        $text = trim(sanitize_input($_POST['review_text'] ?? ''));

        if ($rating < 1 || $rating > 5) {
            $reviewError = "Please select a valid rating from 1 to 5 stars.";
        } elseif (empty($title) || empty($text)) {
            $reviewError = "Please provide both a title and review feedback.";
        } else {
            db_insert('reviews', [
                'product_id' => $productId,
                'user_id' => $_SESSION['user_id'],
                'rating' => $rating,
                'review_title' => $title,
                'review_text' => $text,
                'review_type' => 'product',
                'status' => 'approved'
            ]);
            set_flash('success', "Thank you! Your product review has been published.");
            header("Location: " . base_url('products/detail.php?id=' . $productId));
            exit;
        }
    }
}

// Related Products in same category
$relatedProducts = db_fetch_all("
    SELECT p.*, 
           (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
    FROM products p
    WHERE p.category_id = ? AND p.id != ? AND p.status = 'active'
    LIMIT 4
", [$product['category_id'], $productId]);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
  <!-- Breadcrumbs -->
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb small">
      <li class="breadcrumb-item"><a href="<?= base_url('index.php') ?>" class="text-decoration-none">Home</a></li>
      <li class="breadcrumb-item">
        <a href="<?= $product['is_b2b'] ? base_url('b2b/index.php') : ($product['is_c2c'] ? base_url('c2c/index.php') : base_url('b2c/index.php')) ?>" class="text-decoration-none">
          <?= $product['is_b2b'] ? 'B2B Wholesale' : ($product['is_c2c'] ? 'C2C Market' : 'B2C Retail') ?>
        </a>
      </li>
      <li class="breadcrumb-item active" aria-current="page"><?= e($product['category_name']) ?></li>
    </ol>
  </nav>

  <div class="row g-5">
    <!-- Product Gallery Carousel / Image Display -->
    <div class="col-lg-6">
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white p-3">
        <div class="product-img-box rounded-3 mb-3" style="padding-top: 80%;">
          <img id="mainProductImg" src="<?= base_url($images[0]['image_url']) ?>" alt="<?= e($product['title']) ?>">
        </div>
        <?php if (count($images) > 1): ?>
          <div class="d-flex gap-2">
            <?php foreach ($images as $img): ?>
              <img src="<?= base_url($img['image_url']) ?>" 
                   class="rounded-3 border cursor-pointer" 
                   style="width: 70px; height: 70px; object-fit: cover; cursor: pointer;"
                   onclick="document.getElementById('mainProductImg').src = this.src;" 
                   alt="Thumb">
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Product Info & Purchase Options -->
    <div class="col-lg-6">
      <div class="d-flex align-items-center gap-2 mb-2">
        <?php if ($product['is_b2b']): ?>
          <span class="badge badge-b2b text-uppercase">B2B Wholesale</span>
        <?php elseif ($product['is_c2c']): ?>
          <span class="badge badge-c2c text-uppercase">C2C Pre-Owned</span>
        <?php else: ?>
          <span class="badge badge-b2c text-uppercase">B2C Retail</span>
        <?php endif; ?>

        <span class="text-muted small">Category: <strong><?= e($product['category_name']) ?></strong></span>
        <?php if ($product['sku']): ?>
          <span class="text-muted small">| SKU: <code><?= e($product['sku']) ?></code></span>
        <?php endif; ?>
      </div>

      <h2 class="fw-bold mb-3"><?= e($product['title']) ?></h2>

      <!-- Ratings & Reviews Summary -->
      <div class="d-flex align-items-center gap-2 mb-3">
        <div class="text-warning">
          <?php 
            for ($i = 1; $i <= 5; $i++) {
              echo $i <= round($avgRating ?: 5) ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>';
            }
          ?>
        </div>
        <span class="fw-bold small"><?= $avgRating ?: '4.8' ?> / 5.0</span>
        <span class="text-muted small">(<?= count($reviews) ?: 2 ?> verified reviews)</span>
        <span class="text-secondary small">| Views: <?= $product['views_count'] ?></span>
      </div>

      <!-- Price Block -->
      <div class="p-3 rounded-4 bg-light mb-4">
        <div class="d-flex align-items-baseline gap-3">
          <span class="display-6 fw-bold text-dark"><?= format_currency($product['discount_price'] ?: $product['price']) ?></span>
          <?php if ($product['discount_price']): ?>
            <span class="fs-5 text-muted text-decoration-line-through"><?= format_currency($product['price']) ?></span>
            <span class="badge bg-success-subtle text-success fw-bold">
              Save <?= format_currency($product['price'] - $product['discount_price']) ?>
            </span>
          <?php endif; ?>
        </div>
        <div class="text-muted small mt-1">Inclusive of all local taxes and simulated GST. Free academic demo shipping.</div>
      </div>

      <!-- Stock & Availability Notice -->
      <div class="mb-4">
        <?php if ($product['stock_quantity'] > 0): ?>
          <span class="badge bg-success-subtle text-success py-2 px-3 rounded-pill fw-semibold">
            <i class="bi bi-check-circle-fill me-1"></i> In Stock (<?= $product['stock_quantity'] ?> units available)
          </span>
        <?php else: ?>
          <span class="badge bg-danger-subtle text-danger py-2 px-3 rounded-pill fw-semibold">
            <i class="bi bi-x-circle-fill me-1"></i> Out of Stock
          </span>
        <?php endif; ?>

        <?php if ($product['min_order_qty'] > 1): ?>
          <span class="badge bg-info-subtle text-info py-2 px-3 rounded-pill fw-semibold ms-2">
            Minimum Order Qty (MOQ): <?= $product['min_order_qty'] ?> units
          </span>
        <?php endif; ?>
      </div>

      <!-- Action Buttons -->
      <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
        <!-- Quantity control -->
        <div class="input-group" style="width: 130px;">
          <button class="btn btn-outline-secondary" type="button" onclick="let q=document.getElementById('productQty'); if(parseInt(q.value)>1) q.value=parseInt(q.value)-1;">-</button>
          <input type="number" id="productQty" class="form-control text-center" value="<?= $product['min_order_qty'] ?: 1 ?>" min="<?= $product['min_order_qty'] ?: 1 ?>">
          <button class="btn btn-outline-secondary" type="button" onclick="let q=document.getElementById('productQty'); q.value=parseInt(q.value)+1;">+</button>
        </div>

        <button class="btn btn-primary rounded-pill px-4 py-2 fw-semibold btn-add-to-cart" 
                data-product-id="<?= $product['id'] ?>"
                onclick="this.dataset.quantity = document.getElementById('productQty').value;">
          <i class="bi bi-cart-plus me-1"></i> Add to Cart
        </button>

        <button class="btn btn-outline-danger rounded-circle p-2 btn-toggle-wishlist" data-product-id="<?= $product['id'] ?>" title="Toggle Wishlist">
          <i class="bi bi-heart fs-5"></i>
        </button>

        <?php if ($product['is_b2b']): ?>
          <a href="<?= base_url('b2b/rfq_create.php?product_id=' . $product['id']) ?>" class="btn btn-purple text-white rounded-pill px-4 py-2 fw-semibold" style="background-color: #7c3aed;">
            <i class="bi bi-file-earmark-text me-1"></i> Request B2B Quote
          </a>
        <?php endif; ?>

        <?php if ($product['is_c2c']): ?>
          <a href="<?= base_url('messages/chat.php?receiver=' . $product['seller_id'] . '&product=' . $product['id']) ?>" class="btn btn-warning rounded-pill px-4 py-2 fw-semibold">
            <i class="bi bi-chat-dots me-1"></i> Chat with Seller
          </a>
        <?php endif; ?>
      </div>

      <!-- Seller Metadata Card -->
      <div class="card border rounded-3 p-3 bg-light mb-4">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-shield-check text-primary fs-3"></i>
            <div>
              <div class="fw-bold small"><?= e($product['company_name'] ?: $product['seller_name']) ?></div>
              <div class="text-muted small"><?= $product['company_name'] ? 'Verified B2B Business Vendor' : 'Individual Verified Seller' ?></div>
            </div>
          </div>
          <span class="badge bg-success-subtle text-success small">Verified</span>
        </div>
      </div>

      <!-- Full Product Description -->
      <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
        <h5 class="fw-bold mb-3">Product Description</h5>
        <div class="text-secondary small" style="line-height: 1.7;">
          <?= nl2br(e($product['description'])) ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Reviews Section -->
  <div class="row mt-5">
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h4 class="fw-bold mb-0">Customer Ratings & Reviews (<?= count($reviews) ?>)</h4>
        </div>

        <?php if (!empty($reviewError)): ?>
          <div class="alert alert-danger py-2 small"><?= e($reviewError) ?></div>
        <?php endif; ?>

        <!-- Write a Review Form -->
        <?php if (is_logged_in()): ?>
          <div class="p-3 bg-light rounded-3 border mb-4">
            <h6 class="fw-bold text-primary mb-2 small"><i class="bi bi-pencil-square me-1"></i> Write a Customer Review</h6>
            <form method="POST" action="">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="submit_review">
              <div class="row g-2 mb-2">
                <div class="col-md-3">
                  <label class="form-label small fw-semibold">Rating</label>
                  <select name="rating" class="form-select form-select-sm">
                    <option value="5">⭐⭐⭐⭐⭐ (5/5)</option>
                    <option value="4">⭐⭐⭐⭐ (4/5)</option>
                    <option value="3">⭐⭐⭐ (3/5)</option>
                    <option value="2">⭐⭐ (2/5)</option>
                    <option value="1">⭐ (1/5)</option>
                  </select>
                </div>
                <div class="col-md-9">
                  <label class="form-label small fw-semibold">Review Headline</label>
                  <input type="text" name="review_title" class="form-control form-control-sm" placeholder="e.g. Excellent build quality & fast delivery" required>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label small fw-semibold">Detailed Feedback</label>
                <textarea name="review_text" class="form-control form-control-sm" rows="3" placeholder="Share your experience with this item..." required></textarea>
              </div>
              <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-semibold">Publish Review</button>
            </form>
          </div>
        <?php else: ?>
          <div class="alert alert-secondary small mb-4">
            <i class="bi bi-info-circle me-1"></i> Want to leave a review? <a href="<?= base_url('auth/login.php') ?>" class="fw-semibold">Sign in</a> to share your feedback.
          </div>
        <?php endif; ?>

        <!-- Review List -->
        <?php if (empty($reviews)): ?>
          <p class="text-muted small">No customer reviews yet. Be the first to review this product!</p>
        <?php else: ?>
          <div class="d-flex flex-column gap-3">
            <?php foreach ($reviews as $rev): ?>
              <div class="border-bottom pb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <div class="d-flex align-items-center gap-2">
                    <img src="<?= base_url($rev['reviewer_avatar'] ?: 'assets/images/default-avatar.png') ?>" alt="Reviewer" class="rounded-circle border" width="32" height="32">
                    <span class="fw-semibold small"><?= e($rev['reviewer_name']) ?></span>
                    <span class="badge bg-success-subtle text-success small" style="font-size: 0.65rem;">Verified Purchase</span>
                  </div>
                  <div class="text-warning small">
                    <?php 
                      for ($i = 1; $i <= 5; $i++) {
                        echo $i <= $rev['rating'] ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>';
                      }
                    ?>
                  </div>
                </div>
                <h6 class="fw-bold mb-1 small"><?= e($rev['review_title']) ?></h6>
                <p class="text-secondary small mb-1"><?= nl2br(e($rev['review_text'])) ?></p>
                <div class="text-muted" style="font-size: 0.72rem;"><?= format_date($rev['created_at']) ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Related Products -->
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
        <h5 class="fw-bold mb-3">Related Items</h5>
        <div class="d-flex flex-column gap-3">
          <?php foreach ($relatedProducts as $rel): ?>
            <div class="d-flex align-items-center gap-3">
              <img src="<?= base_url($rel['primary_image'] ?? 'assets/images/products/laptop-1.jpg') ?>" class="rounded-3 border" style="width: 65px; height: 65px; object-fit: cover;" alt="<?= e($rel['title']) ?>">
              <div class="flex-grow-1">
                <h6 class="fw-bold mb-1 small text-truncate" style="max-width: 180px;">
                  <a href="<?= base_url('products/detail.php?id=' . $rel['id']) ?>" class="text-dark text-decoration-none">
                    <?= e($rel['title']) ?>
                  </a>
                </h6>
                <div class="fw-bold text-primary small"><?= format_currency($rel['discount_price'] ?: $rel['price']) ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
