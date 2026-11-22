<?php
/**
 * MultiMart - Wishlist Action Handler (AJAX & Form POST)
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Please sign in to save items to your wishlist.']);
    exit;
}

$action = $_POST['action'] ?? '';
$productId = (int)($_POST['product_id'] ?? 0);

if ($productId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product identifier.']);
    exit;
}

// Ensure wishlist exists for user
$wl = db_fetch_one("SELECT id FROM wishlists WHERE user_id = ?", [$_SESSION['user_id']]);
$wlId = $wl ? (int)$wl['id'] : db_insert('wishlists', ['user_id' => $_SESSION['user_id']]);

if ($action === 'toggle') {
    $existing = db_fetch_one("SELECT id FROM wishlist_items WHERE wishlist_id = ? AND product_id = ?", [$wlId, $productId]);
    if ($existing) {
        db_delete('wishlist_items', 'id = ?', [$existing['id']]);
        $inWishlist = false;
        $message = "Removed item from your wishlist.";
    } else {
        db_insert('wishlist_items', ['wishlist_id' => $wlId, 'product_id' => $productId]);
        $inWishlist = true;
        $message = "Saved item to your wishlist!";
    }

    $cntRow = db_fetch_one("SELECT COUNT(*) as cnt FROM wishlist_items WHERE wishlist_id = ?", [$wlId]);
    echo json_encode([
        'success' => true,
        'in_wishlist' => $inWishlist,
        'wishlist_count' => (int)($cntRow['cnt'] ?? 0),
        'message' => $message
    ]);
    exit;
} elseif ($action === 'remove') {
    db_delete('wishlist_items', 'wishlist_id = ? AND product_id = ?', [$wlId, $productId]);
    set_flash('info', "Item removed from your wishlist.");
    header("Location: " . base_url('customer/wishlist.php'));
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unsupported action.']);
exit;
