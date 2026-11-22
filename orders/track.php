<?php
/**
 * MultiMart - Real-Time Order Lifecycle Tracking System
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$orderNumber = trim(sanitize_input($_GET['order'] ?? ''));
$order = null;
$orderItems = [];

// Handle Order Cancellation by Customer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_order') {
    require_login();
    if (validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $cancelOrderNum = trim(sanitize_input($_POST['order_number'] ?? ''));
        $targetOrder = db_fetch_one("SELECT * FROM orders WHERE order_number = ? AND user_id = ?", [$cancelOrderNum, $_SESSION['user_id']]);

        if ($targetOrder && in_array($targetOrder['order_status'], ['Pending', 'Confirmed'])) {
            db_update('orders', ['order_status' => 'Cancelled'], 'id = ?', [$targetOrder['id']]);
            log_activity('ORDER_CANCELLED', 'ORDER', $targetOrder['id'], "Order #$cancelOrderNum cancelled by buyer.");
            send_notification($targetOrder['user_id'], "Order #$cancelOrderNum Cancelled", "Your cancellation request was completed successfully.", "orders/track.php?order=$cancelOrderNum", 'order');
            set_flash('info', "Order #$cancelOrderNum has been cancelled.");
            header("Location: " . base_url('orders/track.php?order=' . urlencode($cancelOrderNum)));
            exit;
        } else {
            set_flash('error', "This order cannot be cancelled as it is already being processed or shipped.");
        }
    }
}

if (!empty($orderNumber)) {
    $order = db_fetch_one("SELECT * FROM orders WHERE order_number = ?", [$orderNumber]);
    if ($order) {
        $orderItems = db_fetch_all("SELECT * FROM order_items WHERE order_id = ?", [$order['id']]);
    }
}

$pageTitle = "Track Order Lifecycle – MultiMart";

// Progress Stages Mapping
$stages = ['Pending', 'Confirmed', 'Processing', 'Shipped', 'Out for Delivery', 'Delivered'];
$currentStatus = $order['order_status'] ?? 'Pending';
$currentIdx = array_search($currentStatus, $stages);
if ($currentIdx === false) $currentIdx = 0;

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5" style="max-width: 850px;">
  <!-- Order Search Header -->
  <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
    <h4 class="fw-bold mb-3"><i class="bi bi-geo-alt-fill text-primary me-2"></i> Track Your MultiMart Consignment</h4>
    <form method="GET" action="" class="row g-2">
      <div class="col-md-9">
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-box-seam"></i></span>
          <input type="text" name="order" class="form-control" placeholder="Enter Order # (e.g. MM-ORD-2026-88101)" value="<?= e($orderNumber) ?>" required>
        </div>
      </div>
      <div class="col-md-3">
        <button type="submit" class="btn btn-primary w-100 rounded-pill fw-semibold">
          Track Status
        </button>
      </div>
    </form>
  </div>

  <?php if (!empty($orderNumber) && !$order): ?>
    <div class="alert alert-warning rounded-4 shadow-sm">
      <i class="bi bi-exclamation-circle me-1"></i> No matching order found for <code><?= e($orderNumber) ?></code>. Please verify the order number.
    </div>
  <?php elseif ($order): ?>
    <!-- Visual Progress Tracker Card -->
    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom pb-3 mb-4 gap-2">
        <div>
          <span class="badge bg-secondary-subtle text-secondary small text-uppercase fw-bold">Order #<?= e($order['order_number']) ?></span>
          <div class="fw-bold fs-5 text-dark mt-1">Status: <span class="<?= $order['order_status'] === 'Cancelled' ? 'text-danger' : 'text-primary' ?>"><?= e($order['order_status']) ?></span></div>
          <div class="text-muted small">Placed on: <?= format_date($order['created_at']) ?></div>
        </div>
        <div class="text-md-end">
          <div class="text-muted small">Courier Airway Bill:</div>
          <div class="fw-bold text-success font-monospace"><?= e($order['tracking_number'] ?: 'STANDARD-SURFACE-LOGISTICS') ?></div>
        </div>
      </div>

      <!-- Step Indicator -->
      <?php if ($order['order_status'] === 'Cancelled'): ?>
        <div class="alert alert-danger rounded-3 py-3 text-center">
          <i class="bi bi-x-circle-fill fs-3 me-2"></i> This order was cancelled and any simulated payment has been refunded to your ledger.
        </div>
      <?php else: ?>
        <div class="position-relative my-4">
          <div class="progress" style="height: 6px;">
            <div class="progress-bar bg-success" role="progressbar" style="width: <?= (($currentIdx + 1) / count($stages)) * 100 ?>%;"></div>
          </div>
          <div class="d-flex justify-content-between mt-3 text-center">
            <?php foreach ($stages as $idx => $st): 
              $isPassed = $idx <= $currentIdx;
              $isCurrent = $idx === $currentIdx;
            ?>
              <div style="width: 16%;">
                <div class="rounded-circle mx-auto mb-1 d-flex align-items-center justify-content-center" 
                     style="width: 32px; height: 32px; font-size: 0.85rem; background-color: <?= $isPassed ? '#10b981' : '#e2e8f0' ?>; color: <?= $isPassed ? 'white' : '#64748b' ?>;">
                  <?php if ($isPassed): ?><i class="bi bi-check-lg"></i><?php else: ?><?= $idx + 1 ?><?php endif; ?>
                </div>
                <div class="small <?= $isCurrent ? 'fw-bold text-primary' : ($isPassed ? 'text-dark' : 'text-muted') ?>" style="font-size: 0.72rem;">
                  <?= e($st) ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Destination & Payment Details -->
      <div class="row g-3 mt-4 pt-3 border-top small">
        <div class="col-md-6">
          <div class="fw-bold mb-1"><i class="bi bi-truck me-1 text-primary"></i> Shipping Destination</div>
          <div class="text-secondary"><?= e($order['shipping_address']) ?></div>
        </div>
        <div class="col-md-6 text-md-end">
          <div class="fw-bold mb-1"><i class="bi bi-receipt me-1 text-success"></i> Settlement Summary</div>
          <div>Total: <strong><?= format_currency($order['final_amount']) ?></strong> (<?= e($order['payment_status']) ?>)</div>
          <div>Order Type: <span class="badge bg-light text-dark border"><?= e($order['order_type']) ?></span></div>
        </div>
      </div>

      <!-- Action Footer -->
      <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
        <a href="<?= base_url('orders/invoice.php?order=' . urlencode($order['order_number'])) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
          <i class="bi bi-printer me-1"></i> Print Invoice
        </a>

        <?php if (is_logged_in() && $order['user_id'] == $_SESSION['user_id'] && in_array($order['order_status'], ['Pending', 'Confirmed'])): ?>
          <form method="POST" action="" onsubmit="return confirm('Are you sure you wish to cancel this order?');">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="cancel_order">
            <input type="hidden" name="order_number" value="<?= e($order['order_number']) ?>">
            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">
              <i class="bi bi-x-circle me-1"></i> Cancel Order
            </button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
