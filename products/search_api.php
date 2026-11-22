<?php
/**
 * MultiMart - Live Autocomplete Search Suggestions API (JSON)
 * Academic Major Project: B.Tech CSE / Data Science
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$query = trim(sanitize_input($_GET['q'] ?? ''));
if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$results = [];
$term = "%$query%";

// 1. Search Products (B2C, B2B, C2C)
$products = db_fetch_all("
    SELECT p.id, p.title, p.price, p.discount_price, p.is_b2b, p.is_b2c, p.is_c2c, c.name as category_name
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE (p.title LIKE ? OR p.description LIKE ? OR p.sku LIKE ?) AND p.status = 'active'
    LIMIT 6
", [$term, $term, $term]);

foreach ($products as $p) {
    $type = 'B2C';
    $url = base_url('products/detail.php?id=' . $p['id']);
    $icon = 'bi-bag';

    if ($p['is_b2b']) {
        $type = 'B2B';
        $icon = 'bi-building';
    } elseif ($p['is_c2c']) {
        $type = 'C2C';
        $url = base_url('c2c/detail.php?id=' . $p['id']);
        $icon = 'bi-arrow-repeat';
    }

    $results[] = [
        'id' => $p['id'],
        'title' => $p['title'],
        'type' => $type,
        'category' => $p['category_name'],
        'price_formatted' => format_currency($p['discount_price'] ?: $p['price']),
        'url' => $url,
        'icon' => $icon
    ];
}

// 2. Search Services (C2B)
$services = db_fetch_all("
    SELECT s.id, s.title, s.basic_price, c.name as category_name
    FROM services s
    JOIN categories c ON s.category_id = c.id
    WHERE (s.title LIKE ? OR s.description LIKE ? OR s.skills_required LIKE ?) AND s.status = 'active'
    LIMIT 3
", [$term, $term, $term]);

foreach ($services as $s) {
    $results[] = [
        'id' => $s['id'],
        'title' => $s['title'],
        'type' => 'C2B',
        'category' => $s['category_name'],
        'price_formatted' => 'from ' . format_currency($s['basic_price']),
        'url' => base_url('c2b/detail.php?id=' . $s['id']),
        'icon' => 'bi-laptop'
    ];
}

echo json_encode($results);
