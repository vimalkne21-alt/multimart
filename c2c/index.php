<?php
/**
 * MultiMart - C2C Pre-Owned & Peer-to-Peer Consumer Marketplace
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$pageTitle = "C2C Pre-Owned Marketplace – MultiMart";

$catId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$condition = $_GET['condition'] ?? '';
$search = trim(sanitize_input($_GET['q'] ?? ''));

// Fetch C2C Categories
$categories = db_fetch_all("SELECT * FROM categories WHERE module_type IN ('C2C', 'ALL') AND status = 'active' ORDER BY name ASC");

$where = ["p.is_c2c = 1", "p.status = 'active'"];
$params = [];

if ($catId > 0) {
    $where[] = "p.category_id = ?";
    $params[] = $catId;
}

if (!empty($condition)) {
    $where[] = "p.condition_type = ?";
    $params[] = $condition;
}

if (!empty($search)) {
    $where[] = "(p.title LIKE ? OR p.description LIKE ? OR p.location LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = implode(' AND ', $where);
$products = db_fetch_all("
    SELECT p.*, c.name as category_name, u.full_name as seller_name, u.avatar as seller_avatar,
           (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
    FROM products p
    JOIN categories c ON p.category_id = c.id
    JOIN users u ON p.seller_id = u.id
    WHERE {$whereClause}
    ORDER BY p.id DESC
", $params);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb small">
      <li class="breadcrumb-item"><a href="<?= base_url('index.php') ?>" class="text-decoration-none">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">C2C Pre-Owned Marketplace</li>
    </ol>
  </nav>

  <!-- Banner -->
  <div class="p-4 p-md-5 rounded-4 mb-4 shadow-sm text-dark" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 1px solid #fcd34d;">
    <div class="row align-items-center">
      <div class="col-lg-8">
        <span class="badge bg-warning text-dark px-3 py-1 rounded-pill mb-2 small fw-bold">
          <i class="bi bi-arrow-repeat me-1"></i> Consumer to Consumer
        </span>
        <h1 class="display-6 fw-bold mb-2">Peer-to-Peer Second Hand Market</h1>
        <p class="lead mb-3 text-secondary" style="font-size: 1.05rem;">
          Buy and sell pre-owned textbooks, vintage electronics, study desks, and bicycles safely with direct in-app messaging and localized pickups.
        </p>
        <div class="d-flex flex-wrap gap-2">
          <a href="<?= base_url('c2c/sell.php') ?>" class="btn btn-dark rounded-pill px-4 fw-bold shadow-sm">
            <i class="bi bi-plus-circle-fill text-warning me-1"></i> List Your Pre-Owned Item
          </a>
        </div>
      </div>
      <div class="col-lg-4 d-none d-lg-block text-end">
        <i class="bi bi-bag-heart fs-1" style="font-size: 5.5rem !important; opacity: 0.25;"></i>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <!-- Filter Sidebar -->
    <div class="col-lg-3">
      <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
        <h6 class="fw-bold mb-3"><i class="bi bi-funnel me-1 text-warning"></i> Categories</h6>
        <div class="d-flex flex-column gap-1 mb-4">
          <a href="<?= base_url('c2c/index.php') ?>" class="text-decoration-none small py-1 <?= $catId === 0 ? 'fw-bold text-warning-emphasis' : 'text-secondary' ?>">
            <i class="bi bi-chevron-right me-1 small"></i> All Categories
          </a>
          <?php foreach ($categories as $cat): ?>
            <a href="<?= base_url('c2c/index.php?category=' . $cat['id']) ?>" class="text-decoration-none small py-1 <?= $catId === (int)$cat['id'] ? 'fw-bold text-warning-emphasis' : 'text-secondary' ?>">
              <i class="bi bi-chevron-right me-1 small"></i> <?= e($cat['name']) ?>
            </a>
          <?php endforeach; ?>
        </div>

        <h6 class="fw-bold mb-2 small text-uppercase text-muted" style="font-size: 0.72rem;">Condition Filter</h6>
        <div class="d-flex flex-column gap-1 small">
          <a href="<?= base_url('c2c/index.php?category=' . $catId) ?>" class="text-decoration-none py-1 <?= empty($condition) ? 'fw-bold' : 'text-muted' ?>">Any Condition</a>
          <a href="<?= base_url('c2c/index.php?category=' . $catId . '&condition=like_new') ?>" class="text-decoration-none py-1 <?= $condition === 'like_new' ? 'fw-bold text-success' : 'text-muted' ?>">Like New</a>
          <a href="<?= base_url('c2c/index.php?category=' . $catId . '&condition=good') ?>" class="text-decoration-none py-1 <?= $condition === 'good' ? 'fw-bold text-primary' : 'text-muted' ?>">Good Condition</a>
          <a href="<?= base_url('c2c/index.php?category=' . $catId . '&condition=fair') ?>" class="text-decoration-none py-1 <?= $condition === 'fair' ? 'fw-bold text-warning' : 'text-muted' ?>">Fair Condition</a>
        </div>
      </div>
    </div>

    <!-- Products grid -->
    <div class="col-lg-9">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="text-muted small">Showing <strong><?= count($products) ?></strong> pre-owned items</span>
      </div>

      <?php if (empty($products)): ?>
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
          <i class="bi bi-inbox fs-1 text-muted mb-2"></i>
          <h5 class="fw-bold">No used items found in this section</h5>
          <p class="text-muted small">Be the first to list an item in this category!</p>
          <div>
            <a href="<?= base_url('c2c/sell.php') ?>" class="btn btn-warning rounded-pill px-4 fw-bold">Post an Item for Sale</a>
          </div>
        </div>
      <?php else: ?>
        <div class="row g-4">
          <?php foreach ($products as $prod): ?>
            <div class="col-md-6 col-xl-4">
              <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                <div class="product-img-box" style="padding-top: 65%;">
                  <img src="<?= base_url($prod['primary_image'] ?? 'assets/images/products/cs-textbooks-1.jpg') ?>" alt="<?= e($prod['title']) ?>">
                </div>
                <div class="card-body p-4 d-flex flex-column">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-warning-subtle text-warning-emphasis text-uppercase" style="font-size: 0.68rem;">
                      <?= str_replace('_', ' ', e($prod['condition_type'])) ?>
                    </span>
                    <span class="text-muted small" style="font-size: 0.72rem;"><i class="bi bi-geo-alt me-1"></i> <?= e($prod['location']) ?></span>
                  </div>

                  <h6 class="fw-bold mb-1 text-truncate" title="<?= e($prod['title']) ?>">
                    <a href="<?= base_url('c2c/detail.php?id=' . $prod['id']) ?>" class="text-dark text-decoration-none">
                      <?= e($prod['title']) ?>
                    </a>
                  </h6>
                  <div class="text-muted small mb-2">Seller: <strong><?= e($prod['seller_name']) ?></strong></div>
                  <p class="text-secondary small line-clamp-2 mb-3"><?= e($prod['short_description']) ?></p>

                  <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                    <div>
                      <div class="text-muted small" style="font-size: 0.72rem;">Asking Price:</div>
                      <div class="fw-bold fs-5 text-dark"><?= format_currency($prod['discount_price'] ?: $prod['price']) ?></div>
                    </div>
                    <a href="<?= base_url('c2c/detail.php?id=' . $prod['id']) ?>" class="btn btn-outline-warning text-dark btn-sm rounded-pill px-3 py-2 fw-semibold">
                      <i class="bi bi-chat-dots me-1"></i> Contact
                    </a>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
