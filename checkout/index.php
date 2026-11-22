<?php
/**
 * MultiMart - Secure Checkout & Simulated Payment Method Selection
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

require_login();
$pageTitle = "Secure Checkout – MultiMart";

// Fetch user's cart
$cart = db_fetch_one("SELECT id FROM carts WHERE user_id = ?", [$_SESSION['user_id']]);
$cartItems = [];
$subtotal = 0.00;

if ($cart) {
    $cartItems = db_fetch_all("
        SELECT ci.*, p.title, p.price, p.discount_price, p.seller_id, p.business_id, p.stock_quantity,
               (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.cart_id = ?
    ", [$cart['id']]);
}

if (empty($cartItems)) {
    set_flash('info', "Your shopping cart is currently empty.");
    header("Location: " . base_url('cart/index.php'));
    exit;
}

// Calculate amounts
foreach ($cartItems as $item) {
    $p = $item['discount_price'] ?: $item['price'];
    $subtotal += ($p * $item['quantity']);
}

$appliedCoupon = $_SESSION['applied_coupon'] ?? null;
$discount = 0.00;
if ($appliedCoupon) {
    if ($appliedCoupon['discount_type'] === 'percentage') {
        $discount = min((float)$appliedCoupon['max_discount'], $subtotal * ($appliedCoupon['discount_value'] / 100));
    } else {
        $discount = (float)$appliedCoupon['discount_value'];
    }
}

$shipping = $subtotal > 1500 ? 0.00 : 99.00;
$tax = round(($subtotal - $discount) * 0.05, 2);
$finalTotal = max(0, $subtotal - $discount + $shipping + $tax);

// Fetch saved addresses
$addresses = db_fetch_all("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC", [$_SESSION['user_id']]);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb small">
      <li class="breadcrumb-item"><a href="<?= base_url('index.php') ?>" class="text-decoration-none">Home</a></li>
      <li class="breadcrumb-item"><a href="<?= base_url('cart/index.php') ?>" class="text-decoration-none">Cart</a></li>
      <li class="breadcrumb-item active" aria-current="page">Checkout</li>
    </ol>
  </nav>

  <h3 class="fw-bold mb-4"><i class="bi bi-shield-check text-success me-2"></i> Secure Platform Checkout</h3>

  <form method="POST" action="<?= base_url('checkout/process_order.php') ?>" id="checkoutForm">
    <?= csrf_field() ?>

    <div class="row g-4">
      <!-- Left Column: Shipping Address & Payment Selection -->
      <div class="col-lg-8">
        <!-- 1. Delivery Address Section -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-geo-alt-fill text-primary me-2"></i> 1. Select Delivery Address</h5>
            <button class="btn btn-outline-primary btn-sm rounded-pill" type="button" data-bs-toggle="collapse" data-bs-target="#newAddrForm">
              <i class="bi bi-plus-circle me-1"></i> Add New
            </button>
          </div>

          <?php if (empty($addresses)): ?>
            <div class="alert alert-warning small mb-3">
              <i class="bi bi-info-circle me-1"></i> Please provide your shipping address to complete checkout.
            </div>
          <?php endif; ?>

          <div class="row g-3 mb-3">
            <?php foreach ($addresses as $idx => $addr): ?>
              <div class="col-md-6">
                <label class="card h-100 p-3 border rounded-3 cursor-pointer position-relative <?= $idx === 0 ? 'border-primary bg-primary bg-opacity-10' : '' ?>" style="cursor: pointer;">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="address_id" value="<?= $addr['id'] ?>" <?= $idx === 0 ? 'checked' : '' ?> required>
                    <span class="fw-bold small d-block"><?= e($addr['recipient_name']) ?> <span class="badge bg-secondary-subtle text-secondary small text-uppercase"><?= e($addr['address_type']) ?></span></span>
                    <span class="text-secondary small d-block mt-1"><?= e($addr['street_address']) ?></span>
                    <span class="text-secondary small d-block"><?= e($addr['city']) ?>, <?= e($addr['state']) ?> - <?= e($addr['postal_code']) ?></span>
                    <span class="text-muted small d-block mt-1"><i class="bi bi-telephone me-1"></i> <?= e($addr['phone']) ?></span>
                  </div>
                </label>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Quick Add Address Form Collapsible -->
          <div class="collapse <?= empty($addresses) ? 'show' : '' ?>" id="newAddrForm">
            <div class="card card-body bg-light border-0 rounded-3 p-3 mt-2">
              <h6 class="fw-bold text-primary small mb-2">New Address Information</h6>
              <div class="row g-2">
                <div class="col-md-6">
                  <input type="text" name="new_recipient_name" class="form-control form-control-sm" placeholder="Full Recipient Name" <?= empty($addresses) ? 'required' : '' ?>>
                </div>
                <div class="col-md-6">
                  <input type="tel" name="new_phone" class="form-control form-control-sm" placeholder="Phone Number" <?= empty($addresses) ? 'required' : '' ?>>
                </div>
                <div class="col-12">
                  <input type="text" name="new_street_address" class="form-control form-control-sm" placeholder="House/Flat No, Street, Landmark" <?= empty($addresses) ? 'required' : '' ?>>
                </div>
                <div class="col-md-4">
                  <input type="text" name="new_city" class="form-control form-control-sm" placeholder="City" <?= empty($addresses) ? 'required' : '' ?>>
                </div>
                <div class="col-md-4">
                  <input type="text" name="new_state" class="form-control form-control-sm" placeholder="State" <?= empty($addresses) ? 'required' : '' ?>>
                </div>
                <div class="col-md-4">
                  <input type="text" name="new_postal_code" class="form-control form-control-sm" placeholder="PIN Code" <?= empty($addresses) ? 'required' : '' ?>>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 2. Simulated Payment Gateway Selection -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-credit-card-2-front text-primary me-2"></i> 2. Simulated Payment Method</h5>
            <span class="simulated-badge-pill"><i class="bi bi-shield-lock me-1"></i> Academic Demo Mode</span>
          </div>

          <div class="alert alert-secondary small mb-3">
            <i class="bi bi-info-circle-fill text-primary me-1"></i>
            <strong>Safe Simulation Architecture:</strong> No actual financial credentials, real bank cards, UPI PINs or OTPs will be requested. Transactions generate mock authorization codes and ledger entries.
          </div>

          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <label class="card p-3 border rounded-3 cursor-pointer text-center h-100" style="cursor: pointer;">
                <input class="form-check-input mx-auto mb-2" type="radio" name="payment_method_choice" value="UPI" data-type="UPI" checked>
                <div class="fs-3 text-primary mb-1"><i class="bi bi-qr-code-scan"></i></div>
                <div class="fw-bold small">Instant UPI</div>
                <div class="text-muted" style="font-size: 0.7rem;">GPay, PhonePe, BHIM</div>
              </label>
            </div>

            <div class="col-md-4">
              <label class="card p-3 border rounded-3 cursor-pointer text-center h-100" style="cursor: pointer;">
                <input class="form-check-input mx-auto mb-2" type="radio" name="payment_method_choice" value="Credit Card" data-type="Card">
                <div class="fs-3 text-info mb-1"><i class="bi bi-credit-card"></i></div>
                <div class="fw-bold small">Credit / Debit Card</div>
                <div class="text-muted" style="font-size: 0.7rem;">Visa, Mastercard, RuPay</div>
              </label>
            </div>

            <div class="col-md-4">
              <label class="card p-3 border rounded-3 cursor-pointer text-center h-100" style="cursor: pointer;">
                <input class="form-check-input mx-auto mb-2" type="radio" name="payment_method_choice" value="Cash on Delivery" data-type="COD">
                <div class="fs-3 text-success mb-1"><i class="bi bi-cash-stack"></i></div>
                <div class="fw-bold small">Cash on Delivery</div>
                <div class="text-muted" style="font-size: 0.7rem;">Pay upon arrival</div>
              </label>
            </div>
          </div>

          <!-- Dynamic Simulated Form Area -->
          <div id="paymentMethodDynamicFields" class="p-3 bg-light rounded-3 border">
            <div class="mb-3">
              <label class="form-label small fw-semibold">Virtual Payment Address (VPA / UPI ID)</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-qr-code-scan"></i></span>
                <input type="text" class="form-control" name="sim_upi_id" placeholder="yourname@okhdfcbank" value="student.buyer@okaxis">
              </div>
              <div class="form-text text-muted small"><i class="bi bi-info-circle me-1"></i> Demo Simulator: Pre-filled test identifier for academic evaluation.</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Final Order Summary & Confirmation CTA -->
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 80px;">
          <h5 class="fw-bold mb-3">Review Items (<?= count($cartItems) ?>)</h5>

          <div class="d-flex flex-column gap-2 mb-3 overflow-y-auto" style="max-height: 200px;">
            <?php foreach ($cartItems as $ci): 
              $p = $ci['discount_price'] ?: $ci['price'];
            ?>
              <div class="d-flex justify-content-between align-items-center small">
                <div class="text-truncate me-2" style="max-width: 180px;">
                  <span class="fw-semibold"><?= e($ci['title']) ?></span>
                  <div class="text-muted" style="font-size: 0.7rem;">Qty: <?= $ci['quantity'] ?> × <?= format_currency($p) ?></div>
                </div>
                <span class="fw-bold"><?= format_currency($p * $ci['quantity']) ?></span>
              </div>
            <?php endforeach; ?>
          </div>

          <hr class="my-2">

          <div class="d-flex justify-content-between py-1 small">
            <span class="text-muted">Subtotal:</span>
            <span class="fw-semibold"><?= format_currency($subtotal) ?></span>
          </div>

          <?php if ($appliedCoupon): ?>
            <div class="d-flex justify-content-between py-1 small text-success">
              <span>Coupon Discount (<?= e($appliedCoupon['code']) ?>):</span>
              <span class="fw-bold">- <?= format_currency($discount) ?></span>
            </div>
          <?php endif; ?>

          <div class="d-flex justify-content-between py-1 small">
            <span class="text-muted">Shipping:</span>
            <span class="fw-semibold"><?= $shipping > 0 ? format_currency($shipping) : '<span class="text-success fw-bold">FREE</span>' ?></span>
          </div>

          <div class="d-flex justify-content-between py-1 small">
            <span class="text-muted">Simulated Tax (5%):</span>
            <span class="fw-semibold"><?= format_currency($tax) ?></span>
          </div>

          <hr class="my-2">

          <div class="d-flex justify-content-between align-items-baseline mb-4">
            <span class="fw-bold fs-5">Final Total:</span>
            <span class="fs-3 fw-extrabold text-primary"><?= format_currency($finalTotal) ?></span>
          </div>

          <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow">
            <i class="bi bi-lock-fill me-1"></i> Confirm & Process Order
          </button>

          <div class="text-center text-muted small mt-3" style="font-size: 0.75rem;">
            <i class="bi bi-shield-check text-success me-1"></i> Guaranteed 256-Bit SSL Mock Security
          </div>
        </div>
      </div>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
