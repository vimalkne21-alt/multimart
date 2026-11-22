<?php
/**
 * MultiMart - B2B Request for Quotation (RFQ) Submission
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

require_login();

$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$b2bProducts = db_fetch_all("SELECT p.id, p.title, p.price, p.min_order_qty, p.seller_id, b.company_name FROM products p JOIN businesses b ON p.business_id = b.id WHERE p.is_b2b = 1 AND p.status = 'active'");

$selectedProduct = null;
if ($productId > 0) {
    $selectedProduct = db_fetch_one("SELECT p.*, b.company_name FROM products p JOIN businesses b ON p.business_id = b.id WHERE p.id = ?", [$productId]);
}

$pageTitle = "Submit Request for Quotation (RFQ) – MultiMart";
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Security validation error. Please retry.";
    } else {
        $targetProdId = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);
        $targetPrice = (float)($_POST['target_price'] ?? 0);
        $message = trim(sanitize_input($_POST['message'] ?? ''));

        $prod = db_fetch_one("SELECT * FROM products WHERE id = ?", [$targetProdId]);

        if (!$prod) {
            $error = "Please select a valid wholesale product.";
        } elseif ($quantity < $prod['min_order_qty']) {
            $error = "Requested quantity must be at least the Minimum Order Quantity ({$prod['min_order_qty']} units).";
        } elseif (empty($message)) {
            $error = "Please provide procurement requirements and notes.";
        } else {
            $quoteNumber = 'RFQ-2026-' . rand(1000, 9999);
            $quoteId = db_insert('business_quotes', [
                'quote_number' => $quoteNumber,
                'buyer_id' => $_SESSION['user_id'],
                'seller_id' => $prod['seller_id'],
                'product_id' => $targetProdId,
                'quantity' => $quantity,
                'target_price' => $targetPrice,
                'message' => $message,
                'status' => 'requested'
            ]);

            db_insert('quote_items', [
                'quote_id' => $quoteId,
                'product_id' => $targetProdId,
                'requested_qty' => $quantity,
                'offered_unit_price' => $targetPrice > 0 ? ($targetPrice / $quantity) : ($prod['price'] / $prod['min_order_qty']),
                'line_total' => $targetPrice > 0 ? $targetPrice : $prod['price'],
                'notes' => 'Buyer requested bulk bid'
            ]);

            // Notify seller
            send_notification($prod['seller_id'], 'New B2B Quotation Request Received', "RFQ $quoteNumber was submitted for {$prod['title']} (Qty: $quantity).", 'seller/rfqs.php', 'quote');

            log_activity('RFQ_SUBMITTED', 'QUOTE', $quoteId, "Submitted RFQ $quoteNumber for Product #$targetProdId");
            set_flash('success', "Quotation Request $quoteNumber submitted successfully! The supplier has been notified.");
            header("Location: " . base_url('b2b/rfq_list.php'));
            exit;
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5" style="max-width: 750px;">
  <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
    <div class="text-center mb-4">
      <span class="badge bg-purple text-white px-3 py-1 rounded-pill fw-bold text-uppercase mb-2" style="background-color: #7c3aed;">
        <i class="bi bi-file-earmark-text me-1"></i> B2B Procurement
      </span>
      <h3 class="fw-bold">Request for Quotation (RFQ)</h3>
      <p class="text-muted small">Submit your bulk specifications to receive a custom commercial proposal.</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger py-2 small shadow-sm"><i class="bi bi-exclamation-triangle-fill me-1"></i> <?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <?= csrf_field() ?>

      <!-- Product Selection -->
      <div class="mb-3">
        <label class="form-label small fw-semibold">Wholesale Product <span class="text-danger">*</span></label>
        <select name="product_id" id="rfqProduct" class="form-select" required onchange="updateMoqHint(this)">
          <option value="">-- Choose Wholesale Product --</option>
          <?php foreach ($b2bProducts as $p): ?>
            <option value="<?= $p['id'] ?>" data-moq="<?= $p['min_order_qty'] ?>" <?= ($selectedProduct && $selectedProduct['id'] == $p['id']) ? 'selected' : '' ?>>
              <?= e($p['title']) ?> (Supplier: <?= e($p['company_name']) ?> | MOQ: <?= $p['min_order_qty'] ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label small fw-semibold">Required Quantity (Units) <span class="text-danger">*</span></label>
          <input type="number" name="quantity" id="rfqQuantity" class="form-control" value="<?= $selectedProduct ? $selectedProduct['min_order_qty'] : 100 ?>" required min="1">
          <div id="moqHelper" class="form-text small text-muted">
            Minimum Order Quantity: <strong id="moqValue"><?= $selectedProduct ? $selectedProduct['min_order_qty'] : 50 ?></strong> units
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label small fw-semibold">Target Budget / Unit Price (₹)</label>
          <input type="number" name="target_price" class="form-control" placeholder="e.g. 18500" step="0.01">
          <div class="form-text small text-muted">Optional: Target budget you are looking to spend.</div>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label small fw-semibold">Procurement Specifications & Delivery Requirements <span class="text-danger">*</span></label>
        <textarea name="message" class="form-control" rows="4" placeholder="Detail your exact requirements (e.g. GST invoice requirements, specific carton ratios, required dispatch date, lab testing certifications)..." required></textarea>
      </div>

      <button type="submit" class="btn btn-purple text-white w-100 py-2 rounded-pill fw-bold shadow-sm mb-3" style="background-color: #7c3aed;">
        <i class="bi bi-send-fill me-1"></i> Send Official Quotation Request
      </button>

      <div class="text-center small">
        <a href="<?= base_url('b2b/index.php') ?>" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i> Return to Wholesale Catalog</a>
      </div>
    </form>
  </div>
</div>

<script>
function updateMoqHint(selectEl) {
  const opt = selectEl.options[selectEl.selectedIndex];
  const moq = opt.getAttribute('data-moq') || 1;
  document.getElementById('moqValue').innerText = moq;
  document.getElementById('rfqQuantity').value = moq;
  document.getElementById('rfqQuantity').min = moq;
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
