<?php
/**
 * MultiMart - Seller Customer Orders & Dispatch Management
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

require_role(['business_seller', 'admin']);
$pageTitle = "Orders & Dispatch – Seller Portal";

$sellerId = (int)$_SESSION['user_id'];
$isAdmin = ($_SESSION['user_role_name'] === 'admin');
$where = $isAdmin ? "1=1" : "oi.seller_id = $sellerId";

// Handle Order Item Status Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    if (validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $orderId = (int)($_POST['order_id'] ?? 0);
        $newStatus = sanitize_input($_POST['order_status'] ?? '');

        if (!empty($newStatus)) {
            db_update('orders', ['order_status' => $newStatus], 'id = ?', [$orderId]);
            $ord = db_fetch_one("SELECT * FROM orders WHERE id = ?", [$orderId]);
            send_notification($ord['user_id'], "Order #{$ord['order_number']} Updated", "Your consignment status is now: " . strtoupper($newStatus), "orders/track.php?order={$ord['order_number']}", 'order');
            set_flash('success', "Order #{$ord['order_number']} status updated to: " . strtoupper($newStatus));
        }
    }
}

$orders = db_fetch_all("
    SELECT oi.*, o.order_number, o.order_status, o.shipping_address, o.created_at as order_date,
           u.full_name as customer_name, u.email as customer_email, u.phone as customer_phone
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    JOIN users u ON o.user_id = u.id
    WHERE {$where}
    ORDER BY oi.id DESC
");

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <span class="badge bg-primary text-uppercase fw-bold mb-1">Dispatch & Fulfillment</span>
      <h3 class="fw-bold mb-0">Merchant Orders & Dispatch (<?= count($orders) ?>)</h3>
    </div>
    <a href="<?= base_url('seller/dashboard.php') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
      Workspace
    </a>
  </div>

  <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <?php if (empty($orders)): ?>
      <div class="text-center py-4 text-muted small">
        <i class="bi bi-inbox fs-2 mb-2 d-block"></i> No customer orders received yet.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
          <thead class="table-light">
            <tr>
              <th>Order #</th>
              <th>Product</th>
              <th>Qty</th>
              <th>Subtotal</th>
              <th>Customer</th>
              <th>Delivery Address</th>
              <th>Status</th>
              <th class="text-end">Update Dispatch</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $o): ?>
              <tr>
                <td><code><?= e($o['order_number']) ?></code></td>
                <td>
                  <div class="fw-bold text-truncate" style="max-width: 180px;"><?= e($o['product_title']) ?></div>
                </td>
                <td><?= $o['quantity'] ?></td>
                <td class="fw-bold text-dark"><?= format_currency($o['subtotal']) ?></td>
                <td>
                  <div class="fw-semibold"><?= e($o['customer_name']) ?></div>
                  <div class="text-muted" style="font-size: 0.7rem;"><?= e($o['customer_phone']) ?></div>
                </td>
                <td>
                  <div class="text-truncate text-secondary" style="max-width: 180px;" title="<?= e($o['shipping_address']) ?>">
                    <?= e($o['shipping_address']) ?>
                  </div>
                </td>
                <td>
                  <span class="badge bg-primary text-uppercase"><?= e($o['order_status']) ?></span>
                </td>
                <td class="text-end">
                  <form method="POST" action="" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
                    <select name="order_status" class="form-select form-select-sm" style="width: 130px;" onchange="this.form.submit()">
                      <option value="Confirmed" <?= $o['order_status'] === 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                      <option value="Processing" <?= $o['order_status'] === 'Processing' ? 'selected' : '' ?>>Processing</option>
                      <option value="Shipped" <?= $o['order_status'] === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                      <option value="Out for Delivery" <?= $o['order_status'] === 'Out for Delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                      <option value="Delivered" <?= $o['order_status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                    </select>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
