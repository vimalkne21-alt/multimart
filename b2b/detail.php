<?php
/**
 * MultiMart - B2B Wholesale Product Detail & Tier Calculator
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = db_fetch_one("
    SELECT p.*, c.name as category_name, b.company_name, b.tax_id_gst, b.business_type, b.address as biz_address,
           (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
    FROM products p
    JOIN categories c ON p.category_id = c.id
    LEFT JOIN businesses b ON p.business_id = b.id
    WHERE p.id = ? AND p.is_b2b = 1
", [$id]);

if (!$product) {
    set_flash('error', "Wholesale listing not found.");
    header("Location: " . base_url('b2b/index.php'));
    exit;
}

$pageTitle = e($product['title']) . " (B2B Wholesale) – MultiMart";
$unitPrice = $product['bulk_price'] ?: ($product['price'] / $product['min_order_qty']);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb small">
      <li class="breadcrumb-item"><a href="<?= base_url('index.php') ?>" class="text-decoration-none">Home</a></li>
      <li class="breadcrumb-item"><a href="<?= base_url('b2b/index.php') ?>" class="text-decoration-none">B2B Wholesale</a></li>
      <li class="breadcrumb-item active" aria-current="page"><?= e($product['title']) ?></li>
    </ol>
  </nav>

  <div class="row g-5">
    <!-- Image -->
    <div class="col-lg-5">
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white p-3">
        <div class="product-img-box rounded-3" style="padding-top: 75%;">
          <img src="<?= base_url($product['primary_image'] ?? 'assets/images/products/relay-module-1.jpg') ?>" alt="<?= e($product['title']) ?>">
        </div>
      </div>
    </div>

    <!-- Details -->
    <div class="col-lg-7">
      <div class="d-flex align-items-center gap-2 mb-2">
        <span class="badge badge-b2b text-uppercase">B2B Bulk Lot</span>
        <span class="badge bg-secondary-subtle text-secondary">MOQ: <?= $product['min_order_qty'] ?> Units</span>
        <span class="text-muted small">SKU: <code><?= e($product['sku']) ?></code></span>
      </div>

      <h2 class="fw-bold mb-2"><?= e($product['title']) ?></h2>
      <div class="text-muted small mb-3">Supplied by: <strong class="text-dark"><?= e($product['company_name'] ?: 'Apex Industrial Ltd') ?></strong> (GST: <code><?= e($product['tax_id_gst'] ?: '29AABCU9603R1ZM') ?></code>)</div>

      <!-- Bulk Price Highlight -->
      <div class="p-4 rounded-4 bg-light mb-4 border">
        <div class="row align-items-center">
          <div class="col-md-7">
            <div class="text-muted small">Standard Master Carton Lot Price:</div>
            <div class="fs-2 fw-extrabold text-dark"><?= format_currency($product['discount_price'] ?: $product['price']) ?></div>
            <div class="text-muted small mt-1">Approx. <strong><?= format_currency($unitPrice) ?></strong> per individual unit</div>
          </div>
          <div class="col-md-5 text-md-end mt-3 mt-md-0">
            <a href="<?= base_url('b2b/rfq_create.php?product_id=' . $product['id']) ?>" class="btn btn-warning rounded-pill px-4 py-2 fw-bold text-dark shadow-sm">
              <i class="bi bi-file-earmark-text me-1"></i> Request RFQ
            </a>
          </div>
        </div>
      </div>

      <!-- Tiered Volume Pricing Table -->
      <h6 class="fw-bold text-uppercase small text-muted mb-2">Volume Tier Discounts</h6>
      <div class="table-responsive mb-4">
        <table class="table table-bordered table-sm small">
          <thead class="table-light">
            <tr>
              <th>Order Quantity</th>
              <th>Unit Rate</th>
              <th>Volume Incentive</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><?= $product['min_order_qty'] ?> – <?= $product['min_order_qty'] * 2 ?> units</td>
              <td class="fw-bold"><?= format_currency($unitPrice) ?></td>
              <td>Base Wholesale Rate</td>
            </tr>
            <tr>
              <td><?= $product['min_order_qty'] * 2 + 1 ?> – <?= $product['min_order_qty'] * 5 ?> units</td>
              <td class="fw-bold text-success"><?= format_currency($unitPrice * 0.92) ?></td>
              <td><span class="badge bg-success-subtle text-success">8% Volume Rebate</span></td>
            </tr>
            <tr>
              <td><?= $product['min_order_qty'] * 5 + 1 ?>+ units</td>
              <td class="fw-bold text-success"><?= format_currency($unitPrice * 0.85) ?></td>
              <td><span class="badge bg-success-subtle text-success">15% Enterprise Tier</span></td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Description -->
      <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
        <h5 class="fw-bold mb-3">Wholesale Lot Specifications</h5>
        <div class="text-secondary small" style="line-height: 1.7;">
          <?= nl2br(e($product['description'])) ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
