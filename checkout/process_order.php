<?php
/**
 * MultiMart - Order Processing & Safe Simulated Payment Engine
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validate_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash('error', "Security validation error. Please restart checkout.");
    header("Location: " . base_url('cart/index.php'));
    exit;
}

$userId = (int)$_SESSION['user_id'];

// 1. Fetch Cart Items
$cart = db_fetch_one("SELECT id FROM carts WHERE user_id = ?", [$userId]);
if (!$cart) {
    set_flash('error', "No active shopping cart found.");
    header("Location: " . base_url('cart/index.php'));
    exit;
}

$cartItems = db_fetch_all("
    SELECT ci.*, p.title, p.price, p.discount_price, p.seller_id, p.business_id, p.is_b2b, p.is_c2c, p.stock_quantity
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    WHERE ci.cart_id = ?
", [$cart['id']]);

if (empty($cartItems)) {
    set_flash('error', "Your cart is empty.");
    header("Location: " . base_url('cart/index.php'));
    exit;
}

// 2. Resolve Shipping Address
$shippingAddressText = '';
$addrId = (int)($_POST['address_id'] ?? 0);

if ($addrId > 0) {
    $addr = db_fetch_one("SELECT * FROM user_addresses WHERE id = ? AND user_id = ?", [$addrId, $userId]);
    if ($addr) {
        $shippingAddressText = "{$addr['recipient_name']}, {$addr['street_address']}, {$addr['city']}, {$addr['state']} - {$addr['postal_code']}, Phone: {$addr['phone']}";
    }
}

// If new address entered in checkout form
if (empty($shippingAddressText) && !empty($_POST['new_recipient_name'])) {
    $newRecipient = trim(sanitize_input($_POST['new_recipient_name']));
    $newPhone = trim(sanitize_input($_POST['new_phone']));
    $newStreet = trim(sanitize_input($_POST['new_street_address']));
    $newCity = trim(sanitize_input($_POST['new_city']));
    $newState = trim(sanitize_input($_POST['new_state']));
    $newPostal = trim(sanitize_input($_POST['new_postal_code']));

    $newAddrId = db_insert('user_addresses', [
        'user_id' => $userId,
        'address_type' => 'home',
        'recipient_name' => $newRecipient,
        'phone' => $newPhone,
        'street_address' => $newStreet,
        'city' => $newCity,
        'state' => $newState,
        'postal_code' => $newPostal,
        'country' => 'India',
        'is_default' => 0
    ]);
    $shippingAddressText = "{$newRecipient}, {$newStreet}, {$newCity}, {$newState} - {$newPostal}, Phone: {$newPhone}";
}

if (empty($shippingAddressText)) {
    $shippingAddressText = "{$_SESSION['user_name']}, MultiMart Standard Delivery Hub, Bengaluru, Karnataka - 560100";
}

// 3. Compute Financial Totals
$subtotal = 0.00;
$orderType = 'B2C';

foreach ($cartItems as $item) {
    $p = $item['discount_price'] ?: $item['price'];
    $subtotal += ($p * $item['quantity']);
    if ($item['is_b2b']) $orderType = 'B2B';
    elseif ($item['is_c2c']) $orderType = 'C2C';
}

$appliedCoupon = $_SESSION['applied_coupon'] ?? null;
$discount = 0.00;
$couponCode = null;

if ($appliedCoupon) {
    $couponCode = $appliedCoupon['code'];
    if ($appliedCoupon['discount_type'] === 'percentage') {
        $discount = min((float)$appliedCoupon['max_discount'], $subtotal * ($appliedCoupon['discount_value'] / 100));
    } else {
        $discount = (float)$appliedCoupon['discount_value'];
    }
}

$shipping = $subtotal > 1500 ? 0.00 : 99.00;
$tax = round(($subtotal - $discount) * 0.05, 2);
$finalTotal = max(0, $subtotal - $discount + $shipping + $tax);

$paymentMethod = sanitize_input($_POST['payment_method_choice'] ?? 'UPI');
$isCod = ($paymentMethod === 'Cash on Delivery');

// 4. Create Order Record
$orderNumber = 'MM-ORD-' . date('Y') . '-' . rand(10000, 99999);
$trackingNumber = 'EXP-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8)) . '-IN';

$orderId = db_insert('orders', [
    'order_number' => $orderNumber,
    'user_id' => $userId,
    'business_id' => $cartItems[0]['business_id'] ?? null,
    'order_type' => $orderType,
    'total_amount' => $subtotal,
    'discount_amount' => $discount,
    'shipping_fee' => $shipping,
    'tax_amount' => $tax,
    'final_amount' => $finalTotal,
    'coupon_code' => $couponCode,
    'shipping_address' => $shippingAddressText,
    'billing_address' => $shippingAddressText,
    'order_status' => 'Confirmed',
    'payment_status' => $isCod ? 'Pending' : 'Paid',
    'tracking_number' => $trackingNumber,
    'notes' => 'Simulated checkout completed via MultiMart platform gateway.'
]);

// 5. Insert Order Items & Adjust Stock Inventory
foreach ($cartItems as $item) {
    $unitPrice = $item['discount_price'] ?: $item['price'];
    $lineTotal = $unitPrice * $item['quantity'];

    db_insert('order_items', [
        'order_id' => $orderId,
        'product_id' => $item['product_id'],
        'seller_id' => $item['seller_id'],
        'product_title' => $item['title'],
        'unit_price' => $unitPrice,
        'quantity' => $item['quantity'],
        'subtotal' => $lineTotal,
        'status' => 'confirmed'
    ]);

    // Inventory reduction
    $newStock = max(0, $item['stock_quantity'] - $item['quantity']);
    db_update('products', ['stock_quantity' => $newStock], 'id = ?', [$item['product_id']]);

    db_insert('product_inventory', [
        'product_id' => $item['product_id'],
        'change_type' => 'sale',
        'quantity' => -$item['quantity'],
        'remaining_stock' => $newStock,
        'notes' => "Order #$orderNumber dispatch deduction"
    ]);

    // Notify seller
    send_notification($item['seller_id'], 'New Order Received!', "Order #$orderNumber contains {$item['quantity']}x {$item['title']}.", 'seller/orders.php', 'order');
}

// 6. Safe Simulated Payment Record & Ledger Transaction
$txRef = 'TXN_SIM_' . strtoupper(substr(bin2hex(random_bytes(6)), 0, 12));
$simResponse = json_encode([
    'status' => 'SUCCESS',
    'simulated_auth_code' => 'SIM-AUTH-' . rand(100000, 999999),
    'gateway_provider' => $paymentMethod,
    'timestamp' => date('c'),
    'academic_demo_flag' => true
]);

$paymentId = db_insert('payments', [
    'order_id' => $orderId,
    'user_id' => $userId,
    'payment_method' => $paymentMethod,
    'transaction_reference' => $txRef,
    'amount' => $finalTotal,
    'payment_status' => $isCod ? 'Pending' : 'Success',
    'gateway_response' => $simResponse,
    'is_demo' => 1
]);

db_insert('transactions', [
    'payment_id' => $paymentId,
    'user_id' => $userId,
    'type' => 'debit',
    'amount' => $finalTotal,
    'reference_code' => 'LEDGER-' . rand(10000, 99999),
    'description' => "Simulated payment settlement for Order #$orderNumber via $paymentMethod",
    'status' => 'completed'
]);

// 7. Clear Shopping Cart & Session Coupon
db_delete('cart_items', 'cart_id = ?', [$cart['id']]);
unset($_SESSION['applied_coupon']);

// 8. Notifications & Audit Logs
send_notification($userId, "Order Placed Successfully (#$orderNumber)", "Your order has been confirmed with simulated transaction reference $txRef.", "orders/track.php?order=$orderNumber", 'order');
log_activity('CHECKOUT_COMPLETED', 'ORDER', $orderId, "Order $orderNumber completed for " . format_currency($finalTotal));

// 9. Redirect to Confirmation Screen
set_flash('success', "Order #$orderNumber placed successfully!");
header("Location: " . base_url('orders/confirmation.php?order=' . urlencode($orderNumber)));
exit;
