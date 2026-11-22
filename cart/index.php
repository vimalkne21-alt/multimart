<?php
/**
 * MultiMart - Shopping Cart View
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$pageTitle = "Shopping Cart – MultiMart";

$cartItems = [];
$cartSubtotal = 0.00;

// Fetch Cart items for logged-in user or session
if (is_logged_in()) {
    $cart = db_fetch_one("SELECT id FROM carts WHERE user_id = ?", [$_SESSION['user_id']]);
    if ($cart) {
        $cartItems = db_fetch_all("
            SELECT ci.*, p.title, p.price, p.discount_price, p.stock_quantity, p.sku,
                   (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            WHERE ci.cart_id = ?
            ORDER BY ci.id ASC
        ", [$cart['id']]);
    }
}

foreach ($cartItems as $item) {
    $price = $item['discount_price'] ?: $item['price'];
    $cartSubtotal += ($price * $item['quantity']);
}

// Calculate Coupon Discount from Session
$appliedCoupon = $_SESSION['applied_coupon'] ?? null;
$discountAmount = 0.00;

if ($appliedCoupon && $cartSubtotal >= ($appliedCoupon['min_spend'] ?? 0)) {
    if ($appliedCoupon['discount_type'] === 'percentage') {
        $discountAmount = ($cartSubtotal * ($appliedCoupon['discount_value'] / 100));
        if ($discountAmount > $appliedCoupon['max_discount']) {
            $discountAmount = (float)$appliedCoupon['max_discount'];
        }
    } else {
        $discountAmount = (float)$appliedCoupon['discount_value'];
    }
} else {
    unset($_SESSION['applied_coupon']);
    $appliedCoupon = null;
}

// Shipping fee calculation: Free over Rs. 1500, else Rs. 99
$shippingFee = ($cartSubtotal > 1500 || $cartSubtotal == 0) ? 0.00 : 99.00;
// Simulated GST Tax (5%)
$taxAmount = round(($cartSubtotal - $discountAmount) * 0.05, 2);
$finalTotal = max(0, $cartSubtotal - $discountAmount + $shippingFee + $taxAmount);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb small">
      <li class="breadcrumb-item"><a href="<?= base_url('index.php') ?>" class="text-decoration-none">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">Shopping Cart</li>
    </ol>
  </nav>

  <h3 class="fw-bold mb-4"><i class="bi bi-cart3 text-primary me-2"></i> Your Shopping Cart</h3>

  <?php if (empty($cartItems)): ?>
    <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
      <i class="bi bi-cart-x fs-1 text-muted mb-2"></i>
      <h5 class="fw-bold">Your Cart is Currently Empty</h5>
      <p class="text-muted small">Discover our wide collection of retail products, bulk lots, and used items.</p>
      <div>
        <a href="<?= base_url('b2c/index.php') ?>" class="btn btn-primary rounded-pill px-4 fw-semibold">Start Shopping</a>
      </div>
    </div>
  <?php else: ?>
    <div class="row g-4">
      <!-- Items Table -->
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
          <div class="table-responsive">
            <table class="table align-middle">
              <thead class="table-light">
                <tr class="small text-muted text-uppercase">
                  <th>Product</th>
                  <th>Price</th>
                  <th style="width: 130px;">Quantity</th>
                  <th>Subtotal</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($cartItems as $item): 
                  $unitPrice = $item['discount_price'] ?: $item['price'];
                  $lineTotal = $unitPrice * $item['quantity'];
                ?>
                  <tr>
                    <td>
                      <div class="d-flex align-items-center gap-3">
                        <img src="<?= base_url($item['primary_image'] ?? 'assets/images/products/headphones-1.jpg') ?>" class="rounded-3 border" width="60" height="60" style="object-fit: cover;" alt="Prod">
                        <div>
                          <h6 class="fw-bold mb-0 small text-truncate" style="max-width: 250px;">
                            <a href="<?= base_url('products/detail.php?id=' . $item['product_id']) ?>" class="text-dark text-decoration-none">
                              <?= e($item['title']) ?>
                            </a>
                          </h6>
                          <span class="text-muted small" style="font-size: 0.72rem;">SKU: <?= e($item['sku']) ?></span>
                        </div>
                      </div>
                    </td>
                    <td class="fw-semibold small"><?= format_currency($unitPrice) ?></td>
                    <td>
                      <form method="POST" action="<?= base_url('cart/cart_action.php') ?>" class="d-flex align-items-center">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                        <div class="input-group input-group-sm">
                          <button class="btn btn-outline-secondary" type="submit" name="quantity" value="<?= max(1, $item['quantity'] - 1) ?>">-</button>
                          <input type="text" class="form-control text-center" value="<?= $item['quantity'] ?>" readonly style="max-width: 45px;">
                          <button class="btn btn-outline-secondary" type="submit" name="quantity" value="<?= $item['quantity'] + 1 ?>">+</button>
                        </div>
                      </form>
                    </td>
                    <td class="fw-bold text-dark small"><?= format_currency($lineTotal) ?></td>
                    <td class="text-end">
                      <form method="POST" action="<?= base_url('cart/cart_action.php') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="remove">
                        <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                        <button type="submit" class="btn btn-link text-danger p-0" title="Remove item">
                          <i class="bi bi-trash3"></i>
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <div class="d-flex justify-content-between align-items-center pt-3 border-top">
            <a href="<?= base_url('b2c/index.php') ?>" class="btn btn-link text-decoration-none text-muted small p-0">
              <i class="bi bi-arrow-left me-1"></i> Continue Shopping
            </a>
            <span class="small text-muted"><i class="bi bi-shield-lock text-success me-1"></i> Stock reserved for 30 minutes</span>
          </div>
        </div>
      </div>

      <!-- Order Summary Card -->
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-3">
          <h5 class="fw-bold mb-3">Order Summary</h5>

          <div class="d-flex justify-content-between py-1 small">
            <span class="text-muted">Cart Subtotal:</span>
            <span class="fw-semibold"><?= format_currency($cartSubtotal) ?></span>
          </div>

          <?php if ($appliedCoupon): ?>
            <div class="d-flex justify-content-between py-1 small text-success">
              <span>Coupon (<strong><?= e($appliedCoupon['code']) ?></strong>):</span>
              <span class="fw-bold">- <?= format_currency($discountAmount) ?></span>
            </div>
          <?php endif; ?>

          <div class="d-flex justify-content-between py-1 small">
            <span class="text-muted">Estimated Shipping:</span>
            <span class="fw-semibold"><?= $shippingFee > 0 ? format_currency($shippingFee) : '<span class="text-success fw-bold">FREE</span>' ?></span>
          </div>

          <div class="d-flex justify-content-between py-1 small">
            <span class="text-muted">Simulated GST (5%):</span>
            <span class="fw-semibold"><?= format_currency($taxAmount) ?></span>
          </div>

          <hr class="my-2">

          <div class="d-flex justify-content-between align-items-baseline mb-4">
            <span class="fw-bold">Total Amount:</span>
            <span class="fs-4 fw-extrabold text-primary"><?= format_currency($finalTotal) ?></span>
          </div>

          <!-- Coupon Code Input -->
          <form method="POST" action="<?= base_url('cart/cart_action.php') ?>" class="mb-4">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="apply_coupon">
            <label class="form-label small fw-semibold text-muted">Promo Voucher / Coupon</label>
            <div class="input-group">
              <input type="text" name="coupon_code" class="form-control form-control-sm text-uppercase" placeholder="e.g. WELCOME10" value="<?= e($appliedCoupon['code'] ?? '') ?>">
              <?php if ($appliedCoupon): ?>
                <button type="submit" name="remove_coupon" value="1" class="btn btn-outline-danger btn-sm">Remove</button>
              <?php else: ?>
                <button type="submit" class="btn btn-outline-primary btn-sm">Apply</button>
              <?php endif; ?>
            </div>
            <div class="form-text small" style="font-size: 0.72rem;">Try: <code>WELCOME10</code> or <code>MULTISAVE20</code></div>
          </form>

          <!-- Checkout CTA -->
          <a href="<?= base_url('checkout/index.php') ?>" class="btn btn-primary w-100 py-2 rounded-pill fw-bold shadow-sm">
            <i class="bi bi-shield-lock-fill me-1"></i> Proceed to Checkout
          </a>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
