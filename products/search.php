<?php
/**
 * MultiMart - Comprehensive Multi-Model Search System
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$query = trim(sanitize_input($_GET['q'] ?? ''));
$filterModel = $_GET['model'] ?? 'ALL';
$pageTitle = "Search results for \"" . e($query) . "\" – MultiMart";

$term = "%$query%";
$products = [];
$services = [];

if (!empty($query)) {
    // Search Products
    $prodWhere = ["(p.title LIKE ? OR p.description LIKE ? OR p.sku LIKE ?)", "p.status = 'active'"];
    $prodParams = [$term, $term, $term];

    if ($filterModel === 'B2C') $prodWhere[] = "p.is_b2c = 1";
    elseif ($filterModel === 'B2B') $prodWhere[] = "p.is_b2b = 1";
    elseif ($filterModel === 'C2C') $prodWhere[] = "p.is_c2c = 1";

    $prodSql = "
        SELECT p.*, c.name as category_name,
               (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE " . implode(' AND ', $prodWhere) . "
        ORDER BY p.id DESC
    ";
    $products = db_fetch_all($prodSql, $prodParams);

    // Search Services if model is ALL or C2B
    if ($filterModel === 'ALL' || $filterModel === 'C2B') {
        $services = db_fetch_all("
            SELECT s.*, c.name as category_name, u.full_name as freelancer_name, u.avatar as freelancer_avatar
            FROM services s
            JOIN categories c ON s.category_id = c.id
            JOIN users u ON s.freelancer_id = u.id
            WHERE (s.title LIKE ? OR s.description LIKE ? OR s.skills_required LIKE ?) AND s.status = 'active'
        ", [$term, $term, $term]);
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
  <div class="mb-4">
    <h3 class="fw-bold">Search Results for <span class="text-primary">"<?= e($query) ?>"</span></h3>
    <p class="text-muted small">Found <strong><?= count($products) + count($services) ?></strong> matching entries across MultiMart's ecosystem.</p>
  </div>

  <!-- Model Filter Pills -->
  <div class="d-flex flex-wrap gap-2 mb-4">
    <a href="<?= base_url('products/search.php?q=' . urlencode($query) . '&model=ALL') ?>" class="btn btn-sm rounded-pill <?= $filterModel === 'ALL' ? 'btn-dark' : 'btn-outline-secondary' ?>">
      All Models (<?= count($products) + count($services) ?>)
    </a>
    <a href="<?= base_url('products/search.php?q=' . urlencode($query) . '&model=B2C') ?>" class="btn btn-sm rounded-pill <?= $filterModel === 'B2C' ? 'btn-primary' : 'btn-outline-primary' ?>">
      B2C Retail
    </a>
    <a href="<?= base_url('products/search.php?q=' . urlencode($query) . '&model=B2B') ?>" class="btn btn-sm rounded-pill <?= $filterModel === 'B2B' ? 'btn-purple text-white' : 'btn-outline-purple' ?>" style="<?= $filterModel === 'B2B' ? 'background-color:#7c3aed;' : 'border-color:#7c3aed;color:#7c3aed;' ?>">
      B2B Wholesale
    </a>
    <a href="<?= base_url('products/search.php?q=' . urlencode($query) . '&model=C2C') ?>" class="btn btn-sm rounded-pill <?= $filterModel === 'C2C' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark' ?>">
      C2C Pre-Owned
    </a>
    <a href="<?= base_url('products/search.php?q=' . urlencode($query) . '&model=C2B') ?>" class="btn btn-sm rounded-pill <?= $filterModel === 'C2B' ? 'btn-success' : 'btn-outline-success' ?>">
      C2B Freelance Services
    </a>
  </div>

  <?php if (empty($products) && empty($services)): ?>
    <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
      <i class="bi bi-search fs-1 text-muted mb-2"></i>
      <h5 class="fw-bold">No results found for "<?= e($query) ?>"</h5>
      <p class="text-muted small">Check spelling or try more general search keywords like "Headphones", "Relay", "Desk", or "Python".</p>
      <div>
        <a href="<?= base_url('index.php') ?>" class="btn btn-primary btn-sm rounded-pill px-4">Browse Marketplace Portals</a>
      </div>
    </div>
  <?php else: ?>
    <!-- Products Results -->
    <?php if (!empty($products)): ?>
      <div class="row g-4 mb-5">
        <?php foreach ($products as $prod): ?>
          <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="product-card">
              <div class="product-img-box">
                <img src="<?= base_url($prod['primary_image'] ?? 'assets/images/products/headphones-1.jpg') ?>" alt="<?= e($prod['title']) ?>">
              </div>
              <div class="product-card-body">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="text-muted small"><?= e($prod['category_name']) ?></span>
                  <?php if ($prod['is_b2b']): ?>
                    <span class="badge badge-b2b" style="font-size: 0.65rem;">B2B</span>
                  <?php elseif ($prod['is_c2c']): ?>
                    <span class="badge badge-c2c" style="font-size: 0.65rem;">C2C</span>
                  <?php else: ?>
                    <span class="badge badge-b2c" style="font-size: 0.65rem;">B2C</span>
                  <?php endif; ?>
                </div>

                <h6 class="fw-bold mb-2 text-truncate">
                  <a href="<?= $prod['is_c2c'] ? base_url('c2c/detail.php?id=' . $prod['id']) : base_url('products/detail.php?id=' . $prod['id']) ?>" class="text-dark text-decoration-none">
                    <?= e($prod['title']) ?>
                  </a>
                </h6>

                <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-center">
                  <span class="product-price-current"><?= format_currency($prod['discount_price'] ?: $prod['price']) ?></span>
                  <a href="<?= $prod['is_c2c'] ? base_url('c2c/detail.php?id=' . $prod['id']) : base_url('products/detail.php?id=' . $prod['id']) ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    View
                  </a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Services Results -->
    <?php if (!empty($services)): ?>
      <h5 class="fw-bold mb-3"><i class="bi bi-laptop text-success me-2"></i> Professional Services (C2B)</h5>
      <div class="row g-4">
        <?php foreach ($services as $srv): ?>
          <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-4 bg-white">
              <div class="d-flex align-items-center gap-3 mb-2">
                <img src="<?= base_url($srv['freelancer_avatar']) ?>" alt="Avatar" class="rounded-circle border" width="40" height="40">
                <div>
                  <h6 class="fw-bold mb-0"><?= e($srv['freelancer_name']) ?></h6>
                  <span class="text-muted small"><?= e($srv['category_name']) ?></span>
                </div>
              </div>
              <h6 class="fw-bold mb-2"><?= e($srv['title']) ?></h6>
              <p class="text-muted small mb-3"><?= e($srv['description']) ?></p>
              <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-center">
                <span class="fw-bold text-success">from <?= format_currency($srv['basic_price']) ?></span>
                <a href="<?= base_url('c2b/detail.php?id=' . $srv['id']) ?>" class="btn btn-success btn-sm rounded-pill px-3">View Service</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
