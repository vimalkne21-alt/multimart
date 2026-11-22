<?php
/**
 * MultiMart - Customer Wishlist Management
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

require_login();
$pageTitle = "My Wishlist – MultiMart";

$wishlistItems = db_fetch_all("
    SELECT wi.id as item_id, wi.created_at as added_on, p.*, c.name as category_name,
           (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
    FROM wishlist_items wi
    JOIN wishlists w ON wi.wishlist_id = w.id
    JOIN products p ON wi.product_id = p.id
    JOIN categories c ON p.category_id = c.id
    WHERE w.user_id = ?
    ORDER BY wi.id DESC
", [$_SESSION['user_id']]);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb small">
      <li class="breadcrumb-item"><a href="<?= base_url('index.php') ?>" class="text-decoration-none">Home</a></li>
      <li class="breadcrumb-item"><a href="<?= base_url('customer/dashboard.php') ?>" class="text-decoration-none">Dashboard</a></li>
      <li class="breadcrumb-item active" aria-current="page">My Wishlist</li>
    </ol>
  </nav>

  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h3 class="fw-bold mb-1"><i class="bi bi-heart-fill text-danger me-2"></i> Saved Wishlist</h3>
      <p class="text-muted small mb-0">Items saved for future procurement and price alerts.</p>
    </div>
    <span class="badge bg-secondary-subtle text-secondary small"><?= count($wishlistItems) ?> items saved</span>
  </div>

  <?php if (empty($wishlistItems)): ?>
    <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
      <i class="bi bi-heartbreak fs-1 text-muted mb-2"></i>
      <h5 class="fw-bold">Your Wishlist is Empty</h5>
      <p class="text-muted small">Explore products across retail, wholesale, and second-hand portals and tap the heart icon to save.</p>
      <div>
        <a href="<?= base_url('b2c/index.php') ?>" class="btn btn-primary rounded-pill px-4 fw-semibold">Browse Products</a>
      </div>
    </div>
  <?php else: ?>
    <div class="row g-4">
      <?php foreach ($wishlistItems as $item): ?>
        <div class="col-sm-6 col-md-4 col-lg-3">
          <div class="product-card">
            <div class="product-img-box">
              <img src="<?= base_url($item['primary_image'] ?? 'assets/images/products/headphones-1.jpg') ?>" alt="<?= e($item['title']) ?>">
            </div>
            <div class="product-card-body">
              <span class="text-muted small mb-1"><?= e($item['category_name']) ?></span>
              <h6 class="fw-bold mb-2 text-truncate">
                <a href="<?= base_url('products/detail.php?id=' . $item['id']) ?>" class="text-dark text-decoration-none">
                  <?= e($item['title']) ?>
                </a>
              </h6>

              <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-center">
                <span class="product-price-current"><?= format_currency($item['discount_price'] ?: $item['price']) ?></span>
                <div class="d-flex gap-1">
                  <button class="btn btn-primary btn-sm rounded-pill btn-add-to-cart px-3" data-product-id="<?= $item['id'] ?>" title="Move to Cart">
                    <i class="bi bi-cart-plus"></i>
                  </button>
                  <form method="POST" action="<?= base_url('customer/wishlist_action.php') ?>" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle p-2" title="Remove from Wishlist">
                      <i class="bi bi-x-lg"></i>
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
