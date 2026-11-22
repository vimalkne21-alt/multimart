<?php
/**
 * MultiMart - Official Commercial Tax Invoice (Print / PDF Ready)
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$orderNumber = trim(sanitize_input($_GET['order'] ?? ''));
$order = db_fetch_one("
    SELECT o.*, u.full_name as customer_name, u.email as customer_email, u.phone as customer_phone,
           p.payment_method, p.transaction_reference
    FROM orders o
    JOIN users u ON o.user_id = u.id
    LEFT JOIN payments p ON o.id = p.order_id
    WHERE o.order_number = ?
", [$orderNumber]);

if (!$order) {
    die("Error: Invoice record could not be located.");
}

$items = db_fetch_all("SELECT * FROM order_items WHERE order_id = ?", [$order['id']]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Tax Invoice – <?= e($order['order_number']) ?> – MultiMart</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { font-family: 'Inter', system-ui, sans-serif; background: #fff; color: #1e293b; font-size: 0.9rem; }
    .invoice-card { max-width: 800px; margin: 30px auto; border: 1px solid #cbd5e1; border-radius: 12px; padding: 40px; }
    .invoice-header { border-bottom: 2px solid #0f172a; padding-bottom: 20px; margin-bottom: 25px; }
    .tax-badge { background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; padding: 4px 8px; font-size: 0.75rem; }
    @media print {
      .no-print { display: none !important; }
      .invoice-card { border: none !important; margin: 0 !important; padding: 0 !important; }
    }
  </style>
</head>
<body>

<div class="container">
  <div class="invoice-card shadow-sm">
    <div class="d-flex justify-content-end mb-3 no-print gap-2">
      <button class="btn btn-primary btn-sm rounded-pill px-3" onclick="window.print()">
        🖨️ Print / Save as PDF
      </button>
      <button class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="window.close()">
        Close
      </button>
    </div>

    <!-- Header -->
    <div class="invoice-header d-flex justify-content-between align-items-start">
      <div>
        <h3 class="fw-bold text-dark mb-0">MultiMart Marketplace</h3>
        <div class="text-muted small">All-in-One Multi-Model Commercial Platform</div>
        <div class="text-secondary small mt-1">GSTIN: <strong>29AABCU9603R1ZM</strong> | CIN: U72200KA2020PTC139882</div>
        <div class="text-secondary small">Academic Block IV, Tech Corridor, Bengaluru, KA 560100</div>
      </div>
      <div class="text-end">
        <span class="badge bg-primary text-uppercase px-3 py-1 mb-1">Commercial Tax Invoice</span>
        <div class="fw-bold fs-5 mt-1">#<?= e($order['order_number']) ?></div>
        <div class="text-muted small">Date: <?= format_date($order['created_at'], 'd M Y') ?></div>
      </div>
    </div>

    <!-- Bill To / Ship To -->
    <div class="row mb-4">
      <div class="col-6">
        <div class="fw-bold small text-muted text-uppercase mb-1">Billed & Delivered To:</div>
        <div class="fw-bold"><?= e($order['customer_name']) ?></div>
        <div class="text-secondary small"><?= nl2br(e($order['shipping_address'])) ?></div>
        <div class="text-muted small mt-1">Email: <?= e($order['customer_email']) ?></div>
      </div>
      <div class="col-6 text-end">
        <div class="fw-bold small text-muted text-uppercase mb-1">Transaction Details:</div>
        <div class="small">Payment Method: <strong><?= e($order['payment_method']) ?></strong></div>
        <div class="small">Simulated Ref: <code><?= e($order['transaction_reference'] ?: 'TXN_SIM_2026') ?></code></div>
        <div class="small">Payment Status: <span class="badge bg-success-subtle text-success"><?= e($order['payment_status']) ?></span></div>
        <div class="small">Waybill Tracking: <strong><?= e($order['tracking_number']) ?></strong></div>
      </div>
    </div>

    <!-- Items Table -->
    <table class="table table-bordered align-middle small mb-4">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Item Description</th>
          <th class="text-center">HSN/SAC</th>
          <th class="text-center">Qty</th>
          <th class="text-end">Unit Price</th>
          <th class="text-end">Total Amount</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $idx => $it): ?>
          <tr>
            <td><?= $idx + 1 ?></td>
            <td class="fw-semibold"><?= e($it['product_title']) ?></td>
            <td class="text-center font-monospace">85183000</td>
            <td class="text-center"><?= $it['quantity'] ?></td>
            <td class="text-end"><?= format_currency($it['unit_price']) ?></td>
            <td class="text-end fw-bold"><?= format_currency($it['subtotal']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="5" class="text-end fw-bold">Cart Subtotal:</td>
          <td class="text-end fw-bold"><?= format_currency($order['total_amount']) ?></td>
        </tr>
        <?php if ($order['discount_amount'] > 0): ?>
          <tr class="text-success">
            <td colspan="5" class="text-end">Promotional Discount (<?= e($order['coupon_code']) ?>):</td>
            <td class="text-end fw-bold">- <?= format_currency($order['discount_amount']) ?></td>
          </tr>
        <?php endif; ?>
        <tr>
          <td colspan="5" class="text-end">Shipping & Packaging Fee:</td>
          <td class="text-end"><?= format_currency($order['shipping_fee']) ?></td>
        </tr>
        <tr>
          <td colspan="5" class="text-end">Simulated GST (CGST 2.5% + SGST 2.5%):</td>
          <td class="text-end"><?= format_currency($order['tax_amount']) ?></td>
        </tr>
        <tr class="table-active">
          <td colspan="5" class="text-end fs-6 fw-bold">Grand Invoice Total:</td>
          <td class="text-end fs-6 fw-extrabold text-primary"><?= format_currency($order['final_amount']) ?></td>
        </tr>
      </tfoot>
    </table>

    <!-- Digital Authorization Seal -->
    <div class="row align-items-center pt-3 border-top">
      <div class="col-8">
        <div class="small text-muted">
          <em>This is a computer-generated tax invoice verified under the MultiMart Academic Demonstration System. No physical signature is required.</em>
        </div>
      </div>
      <div class="col-4 text-end">
        <div class="p-2 border rounded-3 d-inline-block text-center bg-light" style="width: 140px;">
          <div class="text-success fw-bold" style="font-size: 0.75rem;">✓ DIGITALLY SEALED</div>
          <div class="text-muted" style="font-size: 0.65rem;">MultiMart Core Node</div>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>
