<?php
/**
 * MultiMart - Comprehensive Automated Backend Test Suite
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

echo "=====================================================================\n";
echo " MULTIMART AUTOMATED SYSTEM & INTEGRATION TEST SUITE\n";
echo " Academic Major Project: B.Tech CSE / Data Science\n";
echo " Timestamp: " . date('Y-m-d H:i:s') . "\n";
echo "=====================================================================\n\n";

$passCount = 0;
$failCount = 0;

function assert_test($name, $condition, $details = '') {
    global $passCount, $failCount;
    if ($condition) {
        $passCount++;
        echo " [PASS] $name\n";
    } else {
        $failCount++;
        echo " [FAIL] $name - $details\n";
    }
}

// -------------------------------------------------------------
// Test 1: Database Connectivity & Driver
// -------------------------------------------------------------
try {
    $db = get_db();
    $driver = Database::getDriver();
    assert_test("Database Connection Established", $db instanceof PDO, "Could not acquire PDO instance");
    echo "        Active Database Engine: " . strtoupper($driver) . "\n";
} catch (Exception $e) {
    assert_test("Database Connection Established", false, $e->getMessage());
}

// -------------------------------------------------------------
// Test 2: Verify All 30 Normalized Tables
// -------------------------------------------------------------
$expectedTables = [
    'users', 'roles', 'user_addresses', 'businesses', 'business_verifications',
    'categories', 'products', 'product_images', 'product_inventory', 'carts',
    'cart_items', 'wishlists', 'wishlist_items', 'orders', 'order_items',
    'payments', 'transactions', 'coupons', 'reviews', 'notifications',
    'business_quotes', 'quote_items', 'services', 'service_requests', 'proposals',
    'conversations', 'messages', 'reports', 'returns', 'activity_logs'
];

$existingTables = [];
if (Database::getDriver() === 'sqlite') {
    $rows = db_fetch_all("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
    $existingTables = array_column($rows, 'name');
} else {
    $rows = db_fetch_all("SHOW TABLES");
    $existingTables = array_map('current', $rows);
}

$missingTables = array_diff($expectedTables, $existingTables);
assert_test("30 Normalized Architecture Tables Present", count($missingTables) === 0, "Missing: " . implode(', ', $missingTables));

// -------------------------------------------------------------
// Test 3: User Roles & Authentication Credentials
// -------------------------------------------------------------
$roles = db_fetch_all("SELECT * FROM roles ORDER BY id ASC");
assert_test("5 Major User Roles Configured", count($roles) >= 5);

$admin = db_fetch_one("SELECT * FROM users WHERE email = 'admin@multimart.com'");
assert_test("Admin User Account Seeded", $admin !== null && $admin['role_id'] == 1);
assert_test("Password Hash Verification (Admin@123)", verify_password('Admin@123', $admin['password_hash']));

// -------------------------------------------------------------
// Test 4: B2C Module Validation
// -------------------------------------------------------------
$b2cCount = (int)(db_fetch_one("SELECT COUNT(*) as cnt FROM products WHERE is_b2c = 1")['cnt'] ?? 0);
assert_test("B2C Retail Products Catalog Populated", $b2cCount >= 4);

$prod1 = db_fetch_one("SELECT * FROM products WHERE id = 1");
assert_test("B2C Product Detail Integrity", !empty($prod1['title']) && $prod1['price'] > 0);

// -------------------------------------------------------------
// Test 5: B2B Wholesale & RFQ System
// -------------------------------------------------------------
$b2bCount = (int)(db_fetch_one("SELECT COUNT(*) as cnt FROM products WHERE is_b2b = 1")['cnt'] ?? 0);
assert_test("B2B Wholesale Catalog with MOQ Present", $b2bCount >= 3);

$rfqs = db_fetch_all("SELECT * FROM business_quotes");
assert_test("B2B RFQ Quotation Pipeline Active", count($rfqs) >= 1);

// -------------------------------------------------------------
// Test 6: C2C Pre-Owned Market Validation
// -------------------------------------------------------------
$c2cCount = (int)(db_fetch_one("SELECT COUNT(*) as cnt FROM products WHERE is_c2c = 1")['cnt'] ?? 0);
assert_test("C2C Pre-Owned Listings Active", $c2cCount >= 3);

$c2cItem = db_fetch_one("SELECT * FROM products WHERE is_c2c = 1 LIMIT 1");
assert_test("C2C Condition Rating Set", in_array($c2cItem['condition_type'], ['brand_new', 'like_new', 'good', 'fair']));

// -------------------------------------------------------------
// Test 7: C2B Freelance Talent & Service Packages
// -------------------------------------------------------------
$services = db_fetch_all("SELECT * FROM services");
assert_test("C2B Freelance Services Catalog Active", count($services) >= 2);

$contracts = db_fetch_all("SELECT * FROM service_requests");
assert_test("C2B Project Contracts Workflow Active", count($contracts) >= 1);

// -------------------------------------------------------------
// Test 8: Cart Calculation & Coupon Discount Logic
// -------------------------------------------------------------
$coupon = db_fetch_one("SELECT * FROM coupons WHERE code = 'WELCOME10'");
assert_test("Promotional Coupon WELCOME10 Available", $coupon !== null && $coupon['status'] === 'active');

$subtotal = 3999.00;
$discount = min((float)$coupon['max_discount'], $subtotal * ($coupon['discount_value'] / 100));
$expectedDiscount = 399.90;
assert_test("Percentage Coupon Computation Accuracy", abs($discount - $expectedDiscount) < 0.01);

// -------------------------------------------------------------
// Test 9: Simulated Payment Gateway Safety
// -------------------------------------------------------------
$payments = db_fetch_all("SELECT * FROM payments WHERE is_demo = 1");
assert_test("Simulated Payments Tagged as Demo Only", count($payments) >= 2);

$firstPay = $payments[0] ?? [];
assert_test("Simulated Transaction Reference Structure", str_starts_with($firstPay['transaction_reference'] ?? '', 'TXN_SIM_'));

// -------------------------------------------------------------
// Test 10: Security Controls (CSRF, XSS, Escaping)
// -------------------------------------------------------------
$testHtml = "<script>alert('xss');</script>";
$escaped = e($testHtml);
assert_test("XSS Sanitization Helper (e()) Defense", strpos($escaped, '<script>') === false && strpos($escaped, '&lt;script&gt;') !== false);

$token = csrf_token();
assert_test("CSRF Cryptographic Token Generation", strlen($token) === 64 && validate_csrf_token($token));

// -------------------------------------------------------------
// Test 11: Audit Logs & Notifications
// -------------------------------------------------------------
$logs = db_fetch_all("SELECT * FROM activity_logs");
assert_test("Cybersecurity Audit Logs Tracking", count($logs) >= 3);

$notifs = db_fetch_all("SELECT * FROM notifications");
assert_test("Internal Notifications System Active", count($notifs) >= 3);

echo "\n=====================================================================\n";
echo " TEST SUMMARY: Total Tests: " . ($passCount + $failCount) . " | Passed: $passCount | Failed: $failCount\n";
echo " Success Rate: " . round(($passCount / ($passCount + $failCount)) * 100, 1) . "%\n";
echo "=====================================================================\n";
