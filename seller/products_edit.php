<?php
/**
 * MultiMart - Edit Product Listing (Seller Portal)
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

require_role(['business_seller', 'admin']);

$prodId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$sellerId = (int)$_SESSION['user_id'];
$isAdmin = ($_SESSION['user_role_name'] === 'admin');

$where = $isAdmin ? "id = ?" : "id = ? AND seller_id = $sellerId";
$product = db_fetch_one("SELECT * FROM products WHERE $where", [$prodId]);

if (!$product) {
    set_flash('error', "Product listing not found or access restricted.");
    header("Location: " . base_url('seller/products.php'));
    exit;
}

$pageTitle = "Edit Listing: " . e($product['title']) . " – MultiMart";
$categories = db_fetch_all("SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC");
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Security token mismatch.";
    } else {
        $title = trim(sanitize_input($_POST['title'] ?? ''));
        $categoryId = (int)($_POST['category_id'] ?? $product['category_id']);
        $price = (float)($_POST['price'] ?? 0);
        $discountPrice = !empty($_POST['discount_price']) ? (float)$_POST['discount_price'] : null;
        $stock = max(0, (int)($_POST['stock_quantity'] ?? 0));
        $moq = max(1, (int)($_POST['min_order_qty'] ?? 1));
        $shortDesc = trim(sanitize_input($_POST['short_description'] ?? ''));
        $description = trim(sanitize_input($_POST['description'] ?? ''));

        if (empty($title) || $price <= 0 || empty($description)) {
            $error = "Please fill in all mandatory fields with valid numbers.";
        } else {
            db_update('products', [
                'title' => $title,
                'category_id' => $categoryId,
                'price' => $price,
                'discount_price' => $discountPrice,
                'stock_quantity' => $stock,
                'min_order_qty' => $moq,
                'short_description' => $shortDesc,
                'description' => $description
            ], 'id = ?', [$prodId]);

            log_activity('PRODUCT_UPDATED', 'PRODUCT', $prodId, "Updated listing details for $title");
            set_flash('success', "Product \"$title\" updated successfully!");
            header("Location: " . base_url('seller/products.php'));
            exit;
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5" style="max-width: 800px;">
  <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <span class="badge bg-primary text-uppercase fw-bold mb-1">Catalog Update</span>
        <h3 class="fw-bold mb-0">Edit Product: <?= e($product['title']) ?></h3>
      </div>
      <a href="<?= base_url('seller/products.php') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        Back to Products
      </a>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger py-2 small shadow-sm"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <?= csrf_field() ?>

      <div class="mb-3">
        <label class="form-label small fw-semibold">Product Title <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control" value="<?= e($product['title']) ?>" required>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label small fw-semibold">Category <span class="text-danger">*</span></label>
          <select name="category_id" class="form-select" required>
            <?php foreach ($categories as $c): ?>
              <option value="<?= $c['id'] ?>" <?= $c['id'] == $product['category_id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-semibold">SKU Code</label>
          <input type="text" class="form-control bg-light" value="<?= e($product['sku']) ?>" readonly>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Base Price (₹) <span class="text-danger">*</span></label>
          <input type="number" name="price" class="form-control" value="<?= $product['price'] ?>" step="0.01" required>
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Discount Price (₹)</label>
          <input type="number" name="discount_price" class="form-control" value="<?= $product['discount_price'] ?>" step="0.01">
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Warehouse Stock Quantity <span class="text-danger">*</span></label>
          <input type="number" name="stock_quantity" class="form-control" value="<?= $product['stock_quantity'] ?>" min="0" required>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label small fw-semibold">Minimum Order Quantity (MOQ)</label>
          <input type="number" name="min_order_qty" class="form-control" value="<?= $product['min_order_qty'] ?>" min="1">
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-semibold">Short Summary</label>
          <input type="text" name="short_description" class="form-control" value="<?= e($product['short_description']) ?>">
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label small fw-semibold">Full Description <span class="text-danger">*</span></label>
        <textarea name="description" class="form-control" rows="4" required><?= e($product['description']) ?></textarea>
      </div>

      <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill fw-bold shadow-sm">
        Save Product Changes
      </button>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
