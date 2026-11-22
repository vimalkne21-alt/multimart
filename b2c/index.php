<?php
/**
 * MultiMart - B2C Retail Shopping Catalog
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$pageTitle = "B2C Retail Shopping – MultiMart";

// Filters & Query Parameters
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sortBy = $_GET['sort'] ?? 'newest';
$minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 100000;
$search = trim(sanitize_input($_GET['q'] ?? ''));

// Fetch B2C Categories
$categories = db_fetch_all("SELECT * FROM categories WHERE module_type IN ('B2C', 'ALL') AND status = 'active' ORDER BY name ASC");

// Construct SQL query safely with parameter binding
$where = ["p.is_b2c = 1", "p.status = 'active'"];
$params = [];

if ($categoryId > 0) {
    $where[] = "p.category_id = ?";
    $params[] = $categoryId;
}

if (!empty($search)) {
    $where[] = "(p.title LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($minPrice > 0) {
    $where[] = "COALESCE(p.discount_price, p.price) >= ?";
    $params[] = $minPrice;
}

if ($maxPrice < 100000) {
    $where[] = "COALESCE(p.discount_price, p.price) <= ?";
    $params[] = $maxPrice;
}

$orderBy = "p.id DESC";
if ($sortBy === 'price_asc') {
    $orderBy = "COALESCE(p.discount_price, p.price) ASC";
} elseif ($sortBy === 'price_desc') {
    $orderBy = "COALESCE(p.discount_price, p.price) DESC";
} elseif ($sortBy === 'title') {
    $orderBy = "p.title ASC";
}

$whereClause = implode(' AND ', $where);
$sql = "
    SELECT p.*, c.name as category_name,
           (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
           (SELECT AVG(rating) FROM reviews WHERE product_id = p.id AND status = 'approved') as avg_rating,
           (SELECT COUNT(*) FROM reviews WHERE product_id = p.id AND status = 'approved') as review_count
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE {$whereClause}
    ORDER BY {$orderBy}
";
$products = db_fetch_all($sql, $params);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb small">
      <li class="breadcrumb-item"><a href="<?= base_url('index.php') ?>" class="text-decoration-none">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">B2C Retail Shopping</li>
    </ol>
  </nav>

  <!-- Banner Header -->
  <div class="p-4 rounded-4 mb-4 shadow-sm text-white" style="background: linear-gradient(135deg, #1e3a8a, #2563eb);">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <span class="badge bg-white text-primary text-uppercase fw-bold mb-2">Consumer Marketplace</span>
        <h2 class="fw-bold mb-1">Direct-to-Consumer Retail Hub</h2>
        <p class="mb-0 text-white-50 small">Top consumer tech, lifestyle accessories, and home gadgets with door-step delivery.</p>
      </div>
      <div class="fs-1 d-none d-md-block opacity-50"><i class="bi bi-bag-check-fill"></i></div>
    </div>
  </div>

  <div class="row g-4">
    <!-- Filter Sidebar -->
    <div class="col-lg-3">
      <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="fw-bold mb-0"><i class="bi bi-funnel me-1 text-primary"></i> Filters</h6>
          <a href="<?= base_url('b2c/index.php') ?>" class="small text-decoration-none text-muted">Reset</a>
        </div>

        <form method="GET" action="">
          <!-- Category Filter -->
          <div class="mb-4">
            <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Category</label>
            <div class="d-flex flex-column gap-1">
              <a href="<?= base_url('b2c/index.php') ?>" class="text-decoration-none small py-1 <?= $categoryId === 0 ? 'fw-bold text-primary' : 'text-secondary' ?>">
                <i class="bi bi-chevron-right me-1 small"></i> All Categories
              </a>
              <?php foreach ($categories as $cat): ?>
                <a href="<?= base_url('b2c/index.php?category=' . $cat['id']) ?>" class="text-decoration-none small py-1 <?= $categoryId === (int)$cat['id'] ? 'fw-bold text-primary' : 'text-secondary' ?>">
                  <i class="bi bi-chevron-right me-1 small"></i> <?= e($cat['name']) ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Price Range Filter -->
          <div class="mb-4">
            <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Price Range (₹)</label>
            <div class="row g-2 mb-2">
              <div class="col-6">
                <input type="number" name="min_price" class="form-control form-control-sm" placeholder="Min" value="<?= $minPrice > 0 ? $minPrice : '' ?>">
              </div>
              <div class="col-6">
                <input type="number" name="max_price" class="form-control form-control-sm" placeholder="Max" value="<?= $maxPrice < 100000 ? $maxPrice : '' ?>">
              </div>
            </div>
          </div>

          <!-- Sorting -->
          <div class="mb-4">
            <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Sort Results</label>
            <select name="sort" class="form-select form-select-sm">
              <option value="newest" <?= $sortBy === 'newest' ? 'selected' : '' ?>>Newest Arrivals</option>
              <option value="price_asc" <?= $sortBy === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
              <option value="price_desc" <?= $sortBy === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
              <option value="title" <?= $sortBy === 'title' ? 'selected' : '' ?>>Alphabetical</option>
            </select>
          </div>

          <button type="submit" class="btn btn-primary btn-sm w-100 rounded-pill fw-semibold">
            Apply Filters
          </button>
        </form>
      </div>
    </div>

    <!-- Product Grid -->
    <div class="col-lg-9">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="text-muted small">Showing <strong><?= count($products) ?></strong> consumer products</span>
      </div>

      <?php if (empty($products)): ?>
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
          <i class="bi bi-inbox fs-1 text-muted mb-2"></i>
          <h5 class="fw-bold">No Products Matched Your Criteria</h5>
          <p class="text-muted small">Try broadening your price filters or search keywords.</p>
          <div>
            <a href="<?= base_url('b2c/index.php') ?>" class="btn btn-outline-primary btn-sm rounded-pill px-4">Clear All Filters</a>
          </div>
        </div>
      <?php else: ?>
        <div class="row g-4">
          <?php foreach ($products as $prod): ?>
            <div class="col-sm-6 col-md-4">
              <div class="product-card">
                <button class="wishlist-btn-corner btn-toggle-wishlist" data-product-id="<?= $prod['id'] ?>" title="Add to Wishlist">
                  <i class="bi bi-heart"></i>
                </button>
                <div class="product-img-box">
                  <img src="<?= base_url($prod['primary_image'] ?? 'assets/images/products/headphones-1.jpg') ?>" alt="<?= e($prod['title']) ?>">
                </div>
                <div class="product-card-body">
                  <span class="text-muted small mb-1"><?= e($prod['category_name']) ?></span>
                  <h6 class="fw-bold mb-2 text-truncate" title="<?= e($prod['title']) ?>">
                    <a href="<?= base_url('products/detail.php?id=' . $prod['id']) ?>" class="text-dark text-decoration-none">
                      <?= e($prod['title']) ?>
                    </a>
                  </h6>

                  <div class="d-flex align-items-center mb-2 small text-warning">
                    <?php 
                      $r = round($prod['avg_rating'] ?: 4.5);
                      for ($i = 1; $i <= 5; $i++) {
                        echo $i <= $r ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>';
                      }
                    ?>
                    <span class="text-muted ms-1">(<?= $prod['review_count'] ?: 2 ?>)</span>
                  </div>

                  <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-center">
                    <div>
                      <span class="product-price-current"><?= format_currency($prod['discount_price'] ?: $prod['price']) ?></span>
                      <?php if ($prod['discount_price']): ?>
                        <span class="product-price-original"><?= format_currency($prod['price']) ?></span>
                      <?php endif; ?>
                    </div>
                    <button class="btn btn-primary btn-sm rounded-pill btn-add-to-cart px-3" data-product-id="<?= $prod['id'] ?>">
                      <i class="bi bi-cart-plus me-1"></i> Add
                    </button>
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
