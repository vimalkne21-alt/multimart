<?php
/**
 * MultiMart - Shopping Cart Controller & Coupon Processor (Supports AJAX & Form POST)
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') 
          || (isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/x-www-form-urlencoded') !== false && !isset($_POST['quantity']));

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action'];

// Get or initialize cart ID for logged-in user
function get_or_create_cart_id(): int {
    if (is_logged_in()) {
        $cart = db_fetch_one("SELECT id FROM carts WHERE user_id = ?", [$_SESSION['user_id']]);
        if ($cart) {
            return (int)$cart['id'];
        }
        return db_insert('carts', [
            'user_id' => $_SESSION['user_id'],
            'session_id' => session_id()
        ]);
    }
    return 0;
}

function get_cart_count(int $cartId): int {
    if ($cartId > 0) {
        $r = db_fetch_one("SELECT COUNT(*) as cnt FROM cart_items WHERE cart_id = ?", [$cartId]);
        return (int)($r['cnt'] ?? 0);
    }
    return isset($_SESSION['guest_cart']) ? count($_SESSION['guest_cart']) : 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. ADD TO CART
    if ($action === 'add') {
        $productId = (int)($_POST['product_id'] ?? 0);
        $qty = max(1, (int)($_POST['quantity'] ?? 1));

        $product = db_fetch_one("SELECT * FROM products WHERE id = ? AND status = 'active'", [$productId]);

        if (!$product) {
            $response = ['success' => false, 'message' => 'Product unavailable or out of stock.'];
        } else {
            $unitPrice = $product['discount_price'] ?: $product['price'];

            if (is_logged_in()) {
                $cartId = get_or_create_cart_id();
                $existing = db_fetch_one("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?", [$cartId, $productId]);

                if ($existing) {
                    $newQty = $existing['quantity'] + $qty;
                    db_update('cart_items', ['quantity' => $newQty], 'id = ?', [$existing['id']]);
                } else {
                    db_insert('cart_items', [
                        'cart_id' => $cartId,
                        'product_id' => $productId,
                        'quantity' => $qty,
                        'unit_price' => $unitPrice
                    ]);
                }
                $count = get_cart_count($cartId);
            } else {
                if (!isset($_SESSION['guest_cart'])) $_SESSION['guest_cart'] = [];
                if (isset($_SESSION['guest_cart'][$productId])) {
                    $_SESSION['guest_cart'][$productId] += $qty;
                } else {
                    $_SESSION['guest_cart'][$productId] = $qty;
                }
                $count = count($_SESSION['guest_cart']);
            }

            $response = [
                'success' => true,
                'message' => "Added {$product['title']} to your shopping cart!",
                'cart_count' => $count
            ];
        }
    }

    // 2. UPDATE QUANTITY
    elseif ($action === 'update') {
        $productId = (int)($_POST['product_id'] ?? 0);
        $qty = max(1, (int)($_POST['quantity'] ?? 1));

        if (is_logged_in()) {
            $cartId = get_or_create_cart_id();
            db_update('cart_items', ['quantity' => $qty], 'cart_id = ? AND product_id = ?', [$cartId, $productId]);
        } else {
            if (isset($_SESSION['guest_cart'][$productId])) {
                $_SESSION['guest_cart'][$productId] = $qty;
            }
        }
        set_flash('success', "Cart quantity updated.");
        header("Location: " . base_url('cart/index.php'));
        exit;
    }

    // 3. REMOVE ITEM
    elseif ($action === 'remove') {
        $productId = (int)($_POST['product_id'] ?? 0);
        if (is_logged_in()) {
            $cartId = get_or_create_cart_id();
            db_delete('cart_items', 'cart_id = ? AND product_id = ?', [$cartId, $productId]);
        } else {
            unset($_SESSION['guest_cart'][$productId]);
        }
        set_flash('info', "Item removed from your cart.");
        header("Location: " . base_url('cart/index.php'));
        exit;
    }

    // 4. APPLY COUPON
    elseif ($action === 'apply_coupon') {
        if (!empty($_POST['remove_coupon'])) {
            unset($_SESSION['applied_coupon']);
            set_flash('info', "Coupon code removed.");
        } else {
            $code = strtoupper(trim(sanitize_input($_POST['coupon_code'] ?? '')));
            $coupon = db_fetch_one("SELECT * FROM coupons WHERE code = ? AND status = 'active' AND expiry_date >= CURRENT_DATE", [$code]);

            if ($coupon) {
                $_SESSION['applied_coupon'] = $coupon;
                set_flash('success', "Promo code \"$code\" applied successfully!");
            } else {
                set_flash('error', "Invalid or expired coupon code. Please verify and try again.");
            }
        }
        header("Location: " . base_url('cart/index.php'));
        exit;
    }
}

// Return JSON for AJAX requests
if ($isAjax || isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response);
    exit;
}

header("Location: " . base_url('cart/index.php'));
exit;
