<?php
/**
 * MultiMart - Customer Order History & Cancellation Center
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

require_login();
$pageTitle = "My Orders – MultiMart";
$userId = (int)$_SESSION['user_id'];

$statusFilter = $_GET['status'] ?? 'ALL';
$where = ["user_id = ?"];
$params = [$userId];

if ($statusFilter !== 'ALL' && !empty($statusFilter)) {
    $where[] = "order_status = ?";
    $params[] = $statusFilter;
}

$whereClause = implode(' AND ', $where);
$orders = db_fetch_all("
    SELECT * FROM orders 
    WHERE {$whereClause}
    ORDER BY id DESC
", $params);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb small">
      <li class="breadcrumb-item"><a href="<?= base_url('index.php') ?>" class="text-decoration-none">Home</a></li>
      <li class="breadcrumb-item"><a href="<?= base_url('customer/dashboard.php') ?>" class="text-decoration-none">Dashboard</a></li>
      <li class="breadcrumb-item active" aria-current="page">My Orders</li>
    </ol>
  </nav>

  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
      <h3 class="fw-bold mb-0"><i class="bi bi-box-seam text-primary me-2"></i> My Order History</h3>
      <p class="text-muted small mb-0">Review shipment tracking, print official invoices and track delivery.</p>
    </div>
    <!-- Status Filter Pills -->
    <div class="d-flex flex-wrap gap-1">
      <a href="<?= base_url('customer/orders.php?status=ALL') ?>" class="btn btn-sm rounded-pill <?= $statusFilter === 'ALL' ? 'btn-dark' : 'btn-outline-secondary' ?>">All</a>
      <a href="<?= base_url('customer/orders.php?status=Confirmed') ?>" class="btn btn-sm rounded-pill <?= $statusFilter === 'Confirmed' ? 'btn-primary' : 'btn-outline-primary' ?>">Confirmed</a>
      <a href="<?= base_url('customer/orders.php?status=Shipped') ?>" class="btn btn-sm rounded-pill <?= $statusFilter === 'Shipped' ? 'btn-info text-dark' : 'btn-outline-info text-dark' ?>">Shipped</a>
      <a href="<?= base_url('customer/orders.php?status=Delivered') ?>" class="btn btn-sm rounded-pill <?= $statusFilter === 'Delivered' ? 'btn-success' : 'btn-outline-success' ?>">Delivered</a>
      <a href="<?= base_url('customer/orders.php?status=Cancelled') ?>" class="btn btn-sm rounded-pill <?= $statusFilter === 'Cancelled' ? 'btn-danger' : 'btn-outline-danger' ?>">Cancelled</a>
    </div>
  </div>

  <?php if (empty($orders)): ?>
    <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
      <i class="bi bi-inbox fs-1 text-muted mb-2"></i>
      <h5 class="fw-bold">No Orders Found</h5>
      <p class="text-muted small">You haven't placed any orders matching the selected filter.</p>
      <div>
        <a href="<?= base_url('b2c/index.php') ?>" class="btn btn-primary rounded-pill px-4 fw-semibold">Start Shopping</a>
      </div>
    </div>
  <?php else: ?>
    <div class="d-flex flex-column gap-3">
      <?php foreach ($orders as $o): 
        $items = db_fetch_all("SELECT * FROM order_items WHERE order_id = ?", [$o['id']]);
      ?>
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom pb-3 mb-3 gap-2">
            <div>
              <span class="badge bg-secondary-subtle text-secondary fw-bold me-2"><?= e($o['order_number']) ?></span>
              <span class="text-muted small">Placed: <?= format_date($o['created_at']) ?></span>
            </div>
            <div>
              <?php
                $sc = 'bg-primary';
                if ($o['order_status'] === 'Delivered') $sc = 'bg-success';
                elseif ($o['order_status'] === 'Cancelled') $sc = 'bg-danger';
                elseif ($o['order_status'] === 'Shipped') $sc = 'bg-info text-dark';
              ?>
              <span class="badge <?= $sc ?> text-uppercase px-3 py-1"><?= e($o['order_status']) ?></span>
            </div>
          </div>

          <div class="row g-3 align-items-center">
            <div class="col-md-6">
              <div class="text-muted small mb-1">Purchased Merchandise:</div>
              <ul class="list-unstyled mb-0 small">
                <?php foreach ($items as $it): ?>
                  <li class="text-truncate">
                    <i class="bi bi-box me-1 text-primary"></i> <strong><?= $it['quantity'] ?>x</strong> <?= e($it['product_title']) ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>

            <div class="col-md-3">
              <div class="text-muted small">Invoice Amount:</div>
              <div class="fs-5 fw-bold text-dark"><?= format_currency($o['final_amount']) ?></div>
              <span class="badge bg-success-subtle text-success small" style="font-size: 0.68rem;"><?= e($o['payment_status']) ?> (Simulated)</span>
            </div>

            <div class="col-md-3 text-md-end">
              <div class="d-flex flex-column gap-1">
                <a href="<?= base_url('orders/track.php?order=' . urlencode($o['order_number'])) ?>" class="btn btn-primary btn-sm rounded-pill fw-semibold">
                  <i class="bi bi-geo-alt-fill me-1"></i> Track Consignment
                </a>
                <a href="<?= base_url('orders/invoice.php?order=' . urlencode($o['order_number'])) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill">
                  <i class="bi bi-printer me-1"></i> Tax Invoice
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
