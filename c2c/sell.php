<?php
/**
 * MultiMart - C2C Sell Pre-Owned Product Listing Form
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

require_login();
$pageTitle = "Sell Pre-Owned Item (C2C) – MultiMart";

$categories = db_fetch_all("SELECT * FROM categories WHERE module_type IN ('C2C', 'ALL') AND status = 'active' ORDER BY name ASC");
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Security token validation failed.";
    } else {
        $title = trim(sanitize_input($_POST['title'] ?? ''));
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $condition = sanitize_input($_POST['condition_type'] ?? 'good');
        $price = (float)($_POST['price'] ?? 0);
        $location = trim(sanitize_input($_POST['location'] ?? 'Bengaluru, India'));
        $shortDesc = trim(sanitize_input($_POST['short_description'] ?? ''));
        $description = trim(sanitize_input($_POST['description'] ?? ''));
        $imageChoice = sanitize_input($_POST['image_choice'] ?? 'assets/images/products/cs-textbooks-1.jpg');

        if (empty($title) || $categoryId <= 0 || $price <= 0 || empty($description)) {
            $error = "Please fill in all mandatory fields with valid pricing.";
        } else {
            // Generate clean slug
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title))) . '-' . rand(100, 999);
            $sku = 'C2C-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

            $prodId = db_insert('products', [
                'seller_id' => $_SESSION['user_id'],
                'category_id' => $categoryId,
                'title' => $title,
                'slug' => $slug,
                'sku' => $sku,
                'short_description' => $shortDesc ?: substr($description, 0, 150),
                'description' => $description,
                'price' => $price,
                'discount_price' => $price,
                'stock_quantity' => 1,
                'min_order_qty' => 1,
                'is_b2b' => 0,
                'is_b2c' => 0,
                'is_c2c' => 1,
                'condition_type' => $condition,
                'location' => $location,
                'status' => 'active'
            ]);

            db_insert('product_images', [
                'product_id' => $prodId,
                'image_url' => $imageChoice,
                'is_primary' => 1,
                'display_order' => 1
            ]);

            log_activity('C2C_PRODUCT_LISTED', 'PRODUCT', $prodId, "Listed pre-owned product: $title");
            set_flash('success', "Your item \"$title\" has been listed on the C2C Pre-Owned Marketplace!");
            header("Location: " . base_url('c2c/detail.php?id=' . $prodId));
            exit;
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5" style="max-width: 750px;">
  <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
    <div class="text-center mb-4">
      <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold text-uppercase mb-2">
        <i class="bi bi-tag-fill me-1"></i> Peer-to-Peer
      </span>
      <h3 class="fw-bold">List an Item for Sale</h3>
      <p class="text-muted small">Reach students and community buyers across your city.</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger py-2 small shadow-sm"><i class="bi bi-exclamation-triangle-fill me-1"></i> <?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <?= csrf_field() ?>

      <div class="mb-3">
        <label class="form-label small fw-semibold">Item Title <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control" placeholder="e.g. Silberschatz Operating Systems Textbook (10th Edition)" required value="<?= e($_POST['title'] ?? '') ?>">
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label small fw-semibold">Category <span class="text-danger">*</span></label>
          <select name="category_id" class="form-select" required>
            <option value="">-- Select Category --</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label small fw-semibold">Condition <span class="text-danger">*</span></label>
          <select name="condition_type" class="form-select" required>
            <option value="like_new">Like New (Barely used, zero blemishes)</option>
            <option value="good" selected>Good (Minor cosmetic wear, fully functional)</option>
            <option value="fair">Fair (Noticeable wear, functional)</option>
            <option value="brand_new">Brand New (Unopened box / sealed)</option>
          </select>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label small fw-semibold">Asking Price (₹) <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text">₹</span>
            <input type="number" name="price" class="form-control" placeholder="1200" step="0.01" required value="<?= e($_POST['price'] ?? '') ?>">
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label small fw-semibold">Location (City, State) <span class="text-danger">*</span></label>
          <input type="text" name="location" class="form-control" placeholder="e.g. Mumbai, MH" required value="<?= e($_POST['location'] ?? 'Mumbai, MH') ?>">
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label small fw-semibold">Select Display Image / Graphic</label>
        <select name="image_choice" class="form-select">
          <option value="assets/images/products/cs-textbooks-1.jpg">Academic Textbooks Graphic</option>
          <option value="assets/images/products/sony-camera-1.jpg">Camera & Electronics Graphic</option>
          <option value="assets/images/products/study-desk-1.jpg">Furniture & Study Desk Graphic</option>
          <option value="assets/images/products/backpack-1.jpg">Backpack & Gear Graphic</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label small fw-semibold">Short Summary</label>
        <input type="text" name="short_description" class="form-control" placeholder="Brief one-line summary of condition & accessories included">
      </div>

      <div class="mb-4">
        <label class="form-label small fw-semibold">Detailed Description <span class="text-danger">*</span></label>
        <textarea name="description" class="form-control" rows="4" placeholder="Detail any cosmetic flaws, purchase date, reason for selling, and pickup preferences..." required><?= e($_POST['description'] ?? '') ?></textarea>
      </div>

      <button type="submit" class="btn btn-warning text-dark w-100 py-2 rounded-pill fw-bold shadow-sm mb-3">
        <i class="bi bi-check2-circle me-1"></i> Publish Pre-Owned Listing
      </button>

      <div class="text-center small">
        <a href="<?= base_url('c2c/index.php') ?>" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Pre-Owned Market</a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
