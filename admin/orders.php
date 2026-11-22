<?php
/**
 * MultiMart - Master Order Management & Logistics Control (Admin)
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

require_role('admin');
$pageTitle = "Global Order Management – Admin";

// Handle Status Transitions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $orderId = (int)($_POST['order_id'] ?? 0);
        $newStatus = sanitize_input($_POST['order_status'] ?? '');
        $trackingNum = trim(sanitize_input($_POST['tracking_number'] ?? ''));

        if (!empty($newStatus)) {
            $updateData = ['order_status' => $newStatus];
            if (!empty($trackingNum)) {
                $updateData['tracking_number'] = $trackingNum;
            }
            db_update('orders', $updateData, 'id = ?', [$orderId]);

            $ord = db_fetch_one("SELECT * FROM orders WHERE id = ?", [$orderId]);
            send_notification($ord['user_id'], "Order Update: #{$ord['order_number']}", "Your order is now: " . strtoupper($newStatus), "orders/track.php?order={$ord['order_number']}", 'order');
            log_activity('ORDER_STATUS_ADMIN_UPDATED', 'ORDER', $orderId, "Status updated to $newStatus");
            set_flash('success', "Order #{$ord['order_number']} status updated to: " . strtoupper($newStatus));
        }
    }
}

$orders = db_fetch_all("
    SELECT o.*, u.full_name as customer_name, u.email as customer_email,
           p.payment_method, p.transaction_reference
    FROM orders o
    JOIN users u ON o.user_id = u.id
    LEFT JOIN payments p ON o.id = p.order_id
    ORDER BY o.id DESC
");

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <span class="badge bg-danger text-uppercase fw-bold mb-1">Fulfillment & Logistics</span>
      <h3 class="fw-bold mb-0">Global Order Registry (<?= count($orders) ?>)</h3>
    </div>
    <a href="<?= base_url('admin/dashboard.php') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
      Admin Dashboard
    </a>
  </div>

  <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <div class="table-responsive">
      <table class="table table-hover align-middle small mb-0">
        <thead class="table-light">
          <tr>
            <th>Order #</th>
            <th>Customer</th>
            <th>Model</th>
            <th>Total</th>
            <th>Payment</th>
            <th>Status Transition</th>
            <th>Airway Tracking #</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
            <tr>
              <td><code><?= e($o['order_number']) ?></code></td>
              <td>
                <div class="fw-semibold"><?= e($o['customer_name']) ?></div>
                <div class="text-muted" style="font-size: 0.72rem;"><?= e($o['customer_email']) ?></div>
              </td>
              <td><span class="badge bg-light text-dark border"><?= e($o['order_type']) ?></span></td>
              <td class="fw-bold"><?= format_currency($o['final_amount']) ?></td>
              <td>
                <span class="badge bg-success-subtle text-success"><?= e($o['payment_method']) ?></span>
                <div class="text-muted" style="font-size: 0.68rem;"><?= e($o['payment_status']) ?></div>
              </td>
              <td>
                <form method="POST" action="" class="d-flex align-items-center gap-1">
                  <?= csrf_field() ?>
                  <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                  <select name="order_status" class="form-select form-select-sm" style="width: 140px;" onchange="this.form.submit()">
                    <option value="Confirmed" <?= $o['order_status'] === 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                    <option value="Processing" <?= $o['order_status'] === 'Processing' ? 'selected' : '' ?>>Processing</option>
                    <option value="Shipped" <?= $o['order_status'] === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                    <option value="Out for Delivery" <?= $o['order_status'] === 'Out for Delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                    <option value="Delivered" <?= $o['order_status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                    <option value="Cancelled" <?= $o['order_status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                  </select>
                </form>
              </td>
              <td>
                <span class="font-monospace text-muted small"><?= e($o['tracking_number'] ?: 'PENDING') ?></span>
              </td>
              <td class="text-end">
                <a href="<?= base_url('orders/track.php?order=' . urlencode($o['order_number'])) ?>" class="btn btn-outline-primary btn-sm rounded-pill px-2">
                  <i class="bi bi-geo-alt"></i>
                </a>
                <a href="<?= base_url('orders/invoice.php?order=' . urlencode($o['order_number'])) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill px-2">
                  <i class="bi bi-printer"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
