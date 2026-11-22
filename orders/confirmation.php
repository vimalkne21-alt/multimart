<?php
/**
 * MultiMart - Order Confirmation Screen
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

require_login();

$orderNumber = trim(sanitize_input($_GET['order'] ?? ''));
$order = db_fetch_one("
    SELECT o.*, p.payment_method, p.transaction_reference, p.is_demo
    FROM orders o
    LEFT JOIN payments p ON o.id = p.order_id
    WHERE o.order_number = ? AND (o.user_id = ? OR ? = 'admin')
", [$orderNumber, $_SESSION['user_id'], $_SESSION['user_role_name']]);

if (!$order) {
    set_flash('error', "Order record not found.");
    header("Location: " . base_url('customer/orders.php'));
    exit;
}

$orderItems = db_fetch_all("SELECT * FROM order_items WHERE order_id = ?", [$order['id']]);
$pageTitle = "Order Confirmed #{$order['order_number']} – MultiMart";

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5" style="max-width: 800px;">
  <!-- Success Splash Card -->
  <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white text-center mb-4">
    <div class="mx-auto mb-3" style="width: 80px; height: 80px; border-radius: 50%; background-color: #ecfdf5; display: flex; align-items: center; justify-content: center;">
      <i class="bi bi-check-lg text-success fs-1"></i>
    </div>
    <span class="badge bg-success-subtle text-success text-uppercase fw-bold mb-2">Simulated Payment Verified</span>
    <h2 class="fw-extrabold mb-1">Thank You for Your Order!</h2>
    <p class="text-muted small mb-3">Order Confirmation: <strong class="text-dark">#<?= e($order['order_number']) ?></strong></p>

    <div class="p-3 bg-light rounded-4 border mb-4 text-start small">
      <div class="row g-2">
        <div class="col-sm-6">
          <span class="text-muted">Simulated Transaction Ref:</span>
          <div class="fw-bold font-monospace"><?= e($order['transaction_reference'] ?: 'TXN_SIM_2026') ?></div>
        </div>
        <div class="col-sm-6">
          <span class="text-muted">Payment Method:</span>
          <div class="fw-bold"><?= e($order['payment_method']) ?> (Demo Mode)</div>
        </div>
        <div class="col-sm-6">
          <span class="text-muted">Tracking Waybill:</span>
          <div class="fw-bold text-primary"><?= e($order['tracking_number']) ?></div>
        </div>
        <div class="col-sm-6">
          <span class="text-muted">Delivery Destination:</span>
          <div class="fw-semibold text-truncate"><?= e($order['shipping_address']) ?></div>
        </div>
      </div>
    </div>

    <!-- Order Items List -->
    <div class="card border rounded-3 p-3 mb-4 text-start">
      <h6 class="fw-bold text-uppercase small text-muted mb-3">Purchased Items Breakdown</h6>
      <div class="d-flex flex-column gap-2">
        <?php foreach ($orderItems as $item): ?>
          <div class="d-flex justify-content-between align-items-center small border-bottom pb-2">
            <div>
              <span class="fw-semibold"><?= e($item['product_title']) ?></span>
              <div class="text-muted" style="font-size: 0.72rem;">Qty: <?= $item['quantity'] ?> × <?= format_currency($item['unit_price']) ?></div>
            </div>
            <span class="fw-bold"><?= format_currency($item['subtotal']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="mt-3 text-end small">
        <div class="text-muted">Final Settled Total:</div>
        <div class="fs-4 fw-extrabold text-success"><?= format_currency($order['final_amount']) ?></div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex flex-wrap gap-2 justify-content-center">
      <a href="<?= base_url('orders/track.php?order=' . urlencode($order['order_number'])) ?>" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
        <i class="bi bi-geo-alt-fill me-1"></i> Track Order Lifecycle
      </a>
      <a href="<?= base_url('orders/invoice.php?order=' . urlencode($order['order_number'])) ?>" target="_blank" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold">
        <i class="bi bi-printer me-1"></i> View / Print Tax Invoice
      </a>
      <a href="<?= base_url('index.php') ?>" class="btn btn-link text-decoration-none text-muted small">
        Return to MultiMart
      </a>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
