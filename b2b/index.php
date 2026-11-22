<?php
/**
 * MultiMart - B2B Wholesale & RFQ Procurement Portal
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$pageTitle = "B2B Wholesale & RFQ Procurement – MultiMart";

// Category filter
$catId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$categories = db_fetch_all("SELECT * FROM categories WHERE module_type IN ('B2B', 'ALL') AND status = 'active' ORDER BY name ASC");

$where = ["p.is_b2b = 1", "p.status = 'active'"];
$params = [];

if ($catId > 0) {
    $where[] = "p.category_id = ?";
    $params[] = $catId;
}

$whereClause = implode(' AND ', $where);
$products = db_fetch_all("
    SELECT p.*, c.name as category_name, b.company_name, b.tax_id_gst, b.verified_status,
           (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
    FROM products p
    JOIN categories c ON p.category_id = c.id
    LEFT JOIN businesses b ON p.business_id = b.id
    WHERE {$whereClause}
    ORDER BY p.id DESC
", $params);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
  <!-- Breadcrumbs -->
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb small">
      <li class="breadcrumb-item"><a href="<?= base_url('index.php') ?>" class="text-decoration-none">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">B2B Wholesale Hub</li>
    </ol>
  </nav>

  <!-- B2B Hero Banner -->
  <div class="p-4 p-md-5 rounded-4 mb-4 shadow-sm text-white" style="background: linear-gradient(135deg, #2e1065 0%, #4c1d95 50%, #6d28d9 100%);">
    <div class="row align-items-center">
      <div class="col-lg-8">
        <span class="badge bg-purple text-white border border-light border-opacity-25 px-3 py-1 rounded-pill mb-2 small fw-bold">
          <i class="bi bi-building me-1"></i> Business-to-Business Marketplace
        </span>
        <h1 class="display-6 fw-bold mb-2">Wholesale Procurement & Direct RFQs</h1>
        <p class="lead mb-3" style="font-size: 1.05rem; opacity: 0.9;">
          Source industrial components, bulk apparel lots, and packaging materials directly from verified manufacturers with volume tier discounts and negotiable quotes.
        </p>
        <div class="d-flex flex-wrap gap-2">
          <a href="<?= base_url('b2b/rfq_create.php') ?>" class="btn btn-warning rounded-pill px-4 fw-bold text-dark shadow-sm">
            <i class="bi bi-file-earmark-text me-1"></i> Post a Custom RFQ
          </a>
          <a href="<?= base_url('b2b/rfq_list.php') ?>" class="btn btn-outline-light rounded-pill px-4 fw-semibold">
            <i class="bi bi-clock-history me-1"></i> My RFQ Requests
          </a>
        </div>
      </div>
      <div class="col-lg-4 d-none d-lg-block text-end">
        <i class="bi bi-boxes fs-1" style="font-size: 6rem !important; opacity: 0.3;"></i>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <!-- Category sidebar -->
    <div class="col-lg-3">
      <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
        <h6 class="fw-bold mb-3"><i class="bi bi-grid me-2 text-purple" style="color:#7c3aed;"></i> B2B Sectors</h6>
        <div class="d-flex flex-column gap-1">
          <a href="<?= base_url('b2b/index.php') ?>" class="text-decoration-none small py-1 <?= $catId === 0 ? 'fw-bold text-purple' : 'text-secondary' ?>" style="<?= $catId === 0 ? 'color:#7c3aed;' : '' ?>">
            <i class="bi bi-chevron-right me-1 small"></i> All Wholesale Categories
          </a>
          <?php foreach ($categories as $cat): ?>
            <a href="<?= base_url('b2b/index.php?category=' . $cat['id']) ?>" class="text-decoration-none small py-1 <?= $catId === (int)$cat['id'] ? 'fw-bold text-purple' : 'text-secondary' ?>" style="<?= $catId === (int)$cat['id'] ? 'color:#7c3aed;' : '' ?>">
              <i class="bi bi-chevron-right me-1 small"></i> <?= e($cat['name']) ?>
            </a>
          <?php endforeach; ?>
        </div>

        <div class="border-top mt-4 pt-3">
          <div class="small fw-bold text-muted mb-2 text-uppercase" style="font-size: 0.72rem;">B2B Buyer Benefits</div>
          <ul class="list-unstyled small text-secondary d-flex flex-column gap-2 mb-0">
            <li><i class="bi bi-check-circle-fill text-success me-1"></i> Verified Corporate GST/CIN</li>
            <li><i class="bi bi-check-circle-fill text-success me-1"></i> Transparent MOQ Thresholds</li>
            <li><i class="bi bi-check-circle-fill text-success me-1"></i> Legally Binding Digital RFQs</li>
            <li><i class="bi bi-check-circle-fill text-success me-1"></i> Tiered Volume Pricing</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Products grid -->
    <div class="col-lg-9">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="text-muted small">Displaying <strong><?= count($products) ?></strong> wholesale catalog lots</span>
      </div>

      <div class="row g-4">
        <?php foreach ($products as $prod): ?>
          <div class="col-md-6 col-xl-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden bg-white">
              <div class="product-img-box" style="padding-top: 60%;">
                <img src="<?= base_url($prod['primary_image'] ?? 'assets/images/products/relay-module-1.jpg') ?>" alt="<?= e($prod['title']) ?>">
              </div>
              <div class="card-body p-4 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span class="badge bg-purple-subtle text-purple small fw-bold" style="background-color: #f3e8ff; color: #6b21a8;">
                    MOQ: <?= $prod['min_order_qty'] ?> units
                  </span>
                  <span class="text-success small fw-semibold"><i class="bi bi-shield-check me-1"></i> Verified</span>
                </div>
                
                <h6 class="fw-bold mb-1 text-truncate" title="<?= e($prod['title']) ?>">
                  <a href="<?= base_url('b2b/detail.php?id=' . $prod['id']) ?>" class="text-dark text-decoration-none">
                    <?= e($prod['title']) ?>
                  </a>
                </h6>
                <div class="text-muted small mb-2"><i class="bi bi-building me-1"></i> <?= e($prod['company_name'] ?: 'Apex Industrial Ltd') ?></div>
                <p class="text-secondary small line-clamp-2 mb-3"><?= e($prod['short_description']) ?></p>

                <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                  <div>
                    <div class="text-muted small" style="font-size: 0.72rem;">Wholesale Lot:</div>
                    <div class="fw-bold fs-5 text-dark"><?= format_currency($prod['discount_price'] ?: $prod['price']) ?></div>
                  </div>
                  <a href="<?= base_url('b2b/rfq_create.php?product_id=' . $prod['id']) ?>" class="btn btn-sm rounded-pill px-3 py-2 fw-semibold text-white" style="background-color: #7c3aed;">
                    Request Quote
                  </a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
