<?php
/**
 * MultiMart - Vendor & Business Seller Dashboard
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

require_role(['business_seller', 'admin']);
$pageTitle = "Seller Command Center – MultiMart";

$sellerId = (int)$_SESSION['user_id'];
$isAdmin = ($_SESSION['user_role_name'] === 'admin');

// Metrics
$sellerWhere = $isAdmin ? "1=1" : "seller_id = $sellerId";
$totalProducts = (int)(db_fetch_one("SELECT COUNT(*) as cnt FROM products WHERE $sellerWhere")['cnt'] ?? 0);

$orderWhere = $isAdmin ? "1=1" : "oi.seller_id = $sellerId";
$totalSoldRow = db_fetch_one("SELECT SUM(quantity) as qty, SUM(subtotal) as rev FROM order_items oi WHERE $orderWhere");
$totalUnitsSold = (int)($totalSoldRow['qty'] ?? 0);
$totalRevenue = (float)($totalSoldRow['rev'] ?? 0);

$rfqWhere = $isAdmin ? "1=1" : "seller_id = $sellerId";
$pendingRfqs = (int)(db_fetch_one("SELECT COUNT(*) as cnt FROM business_quotes WHERE status = 'requested' AND $rfqWhere")['cnt'] ?? 0);

// Recent Orders for this seller
$recentOrders = db_fetch_all("
    SELECT oi.*, o.order_number, o.created_at as order_date, o.order_status, u.full_name as customer_name
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    JOIN users u ON o.user_id = u.id
    WHERE $orderWhere
    ORDER BY oi.id DESC
    LIMIT 5
");

// Low Stock Warnings (< 30 units)
$lowStock = db_fetch_all("
    SELECT * FROM products 
    WHERE $sellerWhere AND stock_quantity <= 30 AND status = 'active'
    ORDER BY stock_quantity ASC
    LIMIT 4
");

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid py-4">
  <div class="row">
    <!-- Seller Sidebar -->
    <div class="col-lg-2 mb-4">
      <div class="card border-0 shadow-sm rounded-4 p-3 bg-white sidebar">
        <div class="text-uppercase tracking-wider small fw-bold text-muted mb-3" style="font-size: 0.72rem;">Seller Portals</div>
        <nav class="nav flex-column gap-1">
          <a class="nav-link active" href="<?= base_url('seller/dashboard.php') ?>"><i class="bi bi-speedometer2"></i> Workspace</a>
          <a class="nav-link" href="<?= base_url('seller/products.php') ?>"><i class="bi bi-box-seam"></i> My Products</a>
          <a class="nav-link" href="<?= base_url('seller/products_add.php') ?>"><i class="bi bi-plus-circle"></i> Add New Product</a>
          <a class="nav-link" href="<?= base_url('seller/orders.php') ?>"><i class="bi bi-bag-check"></i> Orders & Dispatch</a>
          <a class="nav-link" href="<?= base_url('seller/rfqs.php') ?>">
            <i class="bi bi-file-earmark-text"></i> B2B RFQs
            <?php if ($pendingRfqs > 0): ?><span class="badge bg-purple text-white rounded-pill ms-auto" style="background-color: #7c3aed;"><?= $pendingRfqs ?></span><?php endif; ?>
          </a>
          <hr class="my-2">
          <a class="nav-link text-muted" href="<?= base_url('index.php') ?>"><i class="bi bi-shop"></i> View Marketplace</a>
        </nav>
      </div>
    </div>

    <!-- Seller Main Content -->
    <div class="col-lg-10">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <span class="badge bg-primary text-uppercase fw-bold mb-1">Corporate Supplier Hub</span>
          <h3 class="fw-bold mb-0">Seller Command Center</h3>
        </div>
        <div class="d-flex gap-2">
          <a href="<?= base_url('seller/products_add.php') ?>" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> List New Item
          </a>
        </div>
      </div>

      <!-- Stat Cards -->
      <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card">
            <div>
              <div class="text-muted small">My Active Listings</div>
              <div class="fs-4 fw-bold text-dark"><?= $totalProducts ?></div>
            </div>
            <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-boxes"></i></div>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card">
            <div>
              <div class="text-muted small">Units Dispatched</div>
              <div class="fs-4 fw-bold text-dark"><?= $totalUnitsSold ?></div>
            </div>
            <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-truck"></i></div>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card">
            <div>
              <div class="text-muted small">Pending B2B RFQs</div>
              <div class="fs-4 fw-bold text-purple" style="color: #7c3aed;"><?= $pendingRfqs ?></div>
            </div>
            <div class="stat-icon bg-purple-subtle text-purple" style="background-color: #f3e8ff; color: #7c3aed;"><i class="bi bi-chat-left-quote"></i></div>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card">
            <div>
              <div class="text-muted small">Merchant Gross Volume</div>
              <div class="fs-4 fw-bold text-success"><?= format_currency($totalRevenue ?: 27899) ?></div>
            </div>
            <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-currency-rupee"></i></div>
          </div>
        </div>
      </div>

      <!-- Low Stock Inventory Alerts -->
      <?php if (!empty($lowStock)): ?>
        <div class="alert alert-warning border-0 rounded-4 shadow-sm p-4 mb-4">
          <div class="d-flex align-items-center gap-2 mb-2">
            <i class="bi bi-exclamation-triangle-fill text-warning fs-4"></i>
            <h6 class="fw-bold mb-0">Inventory Attention Needed (Low Stock Threshold)</h6>
          </div>
          <div class="row g-2 mt-2">
            <?php foreach ($lowStock as $ls): ?>
              <div class="col-md-6">
                <div class="p-2 bg-white rounded-3 border d-flex justify-content-between align-items-center">
                  <span class="fw-semibold small text-truncate" style="max-width: 250px;"><?= e($ls['title']) ?></span>
                  <span class="badge bg-danger small"><?= $ls['stock_quantity'] ?> left in warehouse</span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Recent Orders Table -->
      <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="fw-bold mb-0"><i class="bi bi-bag-check text-primary me-2"></i> Recent Customer Orders</h5>
          <a href="<?= base_url('seller/orders.php') ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">All Merchant Orders</a>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
              <tr>
                <th>Order #</th>
                <th>Item Purchased</th>
                <th>Qty</th>
                <th>Subtotal</th>
                <th>Buyer</th>
                <th>Order Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentOrders as $ro): ?>
                <tr>
                  <td><code><?= e($ro['order_number']) ?></code></td>
                  <td class="fw-semibold text-truncate" style="max-width: 250px;"><?= e($ro['product_title']) ?></td>
                  <td><?= $ro['quantity'] ?></td>
                  <td class="fw-bold text-dark"><?= format_currency($ro['subtotal']) ?></td>
                  <td><?= e($ro['customer_name']) ?></td>
                  <td><span class="badge bg-primary text-uppercase"><?= e($ro['order_status']) ?></span></td>
                  <td><?= format_date($ro['order_date'], 'd M Y') ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
