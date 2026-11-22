<?php
/**
 * MultiMart - Add Product Listing Form (Seller Portal)
 * Supports B2C Retail & B2B Wholesale Lots
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

require_role(['business_seller', 'admin']);
$pageTitle = "Add New Product – Seller Portal";

$categories = db_fetch_all("SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC");
$business = db_fetch_one("SELECT id FROM businesses WHERE user_id = ?", [$_SESSION['user_id']]);
$businessId = $business['id'] ?? null;

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Security validation error.";
    } else {
        $title = trim(sanitize_input($_POST['title'] ?? ''));
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $modelType = sanitize_input($_POST['model_type'] ?? 'B2C');
        $sku = trim(sanitize_input($_POST['sku'] ?? ''));
        $price = (float)($_POST['price'] ?? 0);
        $discountPrice = !empty($_POST['discount_price']) ? (float)$_POST['discount_price'] : null;
        $stock = max(1, (int)($_POST['stock_quantity'] ?? 10));
        $moq = max(1, (int)($_POST['min_order_qty'] ?? 1));
        $bulkPrice = !empty($_POST['bulk_price']) ? (float)$_POST['bulk_price'] : null;
        $shortDesc = trim(sanitize_input($_POST['short_description'] ?? ''));
        $description = trim(sanitize_input($_POST['description'] ?? ''));
        $imageChoice = sanitize_input($_POST['image_choice'] ?? 'assets/images/products/headphones-1.jpg');

        if (empty($title) || $categoryId <= 0 || $price <= 0 || empty($description)) {
            $error = "Please fill in all mandatory fields with valid rates.";
        } else {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title))) . '-' . rand(100, 999);
            if (empty($sku)) {
                $sku = ($modelType === 'B2B' ? 'B2B-' : 'B2C-') . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
            }

            $isB2B = ($modelType === 'B2B') ? 1 : 0;
            $isB2C = ($modelType === 'B2C') ? 1 : 0;

            $prodId = db_insert('products', [
                'seller_id' => $_SESSION['user_id'],
                'business_id' => $businessId,
                'category_id' => $categoryId,
                'title' => $title,
                'slug' => $slug,
                'sku' => $sku,
                'short_description' => $shortDesc ?: substr($description, 0, 150),
                'description' => $description,
                'price' => $price,
                'discount_price' => $discountPrice,
                'stock_quantity' => $stock,
                'min_order_qty' => $moq,
                'bulk_price' => $bulkPrice,
                'is_b2b' => $isB2B,
                'is_b2c' => $isB2C,
                'is_c2c' => 0,
                'condition_type' => 'brand_new',
                'status' => 'active'
            ]);

            db_insert('product_images', [
                'product_id' => $prodId,
                'image_url' => $imageChoice,
                'is_primary' => 1,
                'display_order' => 1
            ]);

            db_insert('product_inventory', [
                'product_id' => $prodId,
                'change_type' => 'initial',
                'quantity' => $stock,
                'remaining_stock' => $stock,
                'notes' => 'Initial vendor catalog stock staging'
            ]);

            log_activity('PRODUCT_ADDED', 'PRODUCT', $prodId, "Vendor created listing: $title ($modelType)");
            set_flash('success', "Product listing \"$title\" created successfully!");
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
        <span class="badge bg-primary text-uppercase fw-bold mb-1">Catalog Entry</span>
        <h3 class="fw-bold mb-0">Add New Product to MultiMart</h3>
      </div>
      <a href="<?= base_url('seller/products.php') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        Cancel
      </a>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger py-2 small shadow-sm"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <?= csrf_field() ?>

      <div class="row g-3 mb-3">
        <div class="col-md-8">
          <label class="form-label small fw-semibold">Product Title <span class="text-danger">*</span></label>
          <input type="text" name="title" class="form-control" placeholder="e.g. Industrial Automation Micro-Controllers (Master Carton)" required value="<?= e($_POST['title'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold">E-Commerce Model <span class="text-danger">*</span></label>
          <select name="model_type" class="form-select" id="modelTypeSelector" onchange="toggleB2BFields(this.value)">
            <option value="B2C" selected>B2C Retail Product</option>
            <option value="B2B">B2B Wholesale Lot (MOQ/Bulk)</option>
          </select>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label small fw-semibold">Product Category <span class="text-danger">*</span></label>
          <select name="category_id" class="form-select" required>
            <option value="">-- Choose Category --</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= $c['id'] ?>"><?= e($c['name']) ?> (<?= e($c['module_type']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-semibold">SKU Code (Stock Keeping Unit)</label>
          <input type="text" name="sku" class="form-control" placeholder="Leave blank to auto-generate">
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Base Price (₹) <span class="text-danger">*</span></label>
          <input type="number" name="price" class="form-control" placeholder="4999.00" step="0.01" required value="<?= e($_POST['price'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Promotional Discount Price (₹)</label>
          <input type="number" name="discount_price" class="form-control" placeholder="3999.00" step="0.01">
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Opening Stock Quantity <span class="text-danger">*</span></label>
          <input type="number" name="stock_quantity" class="form-control" value="50" min="1" required>
        </div>
      </div>

      <!-- B2B Wholesale Fields -->
      <div id="b2bWholesaleFields" class="p-3 bg-light rounded-3 border mb-3" style="display: none;">
        <h6 class="fw-bold text-purple small mb-2" style="color: #7c3aed;"><i class="bi bi-boxes me-1"></i> B2B Wholesale Parameters</h6>
        <div class="row g-2">
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Minimum Order Quantity (MOQ)</label>
            <input type="number" name="min_order_qty" class="form-control form-control-sm" value="50" min="1">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Unit Bulk Price on Enterprise Tier (₹)</label>
            <input type="number" name="bulk_price" class="form-control form-control-sm" placeholder="e.g. 195.00" step="0.01">
          </div>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label small fw-semibold">Select Display Artwork / Mock Image</label>
        <select name="image_choice" class="form-select">
          <option value="assets/images/products/headphones-1.jpg">Quantum Pro Audio Graphic</option>
          <option value="assets/images/products/laptop-1.jpg">Workstation Laptop Graphic</option>
          <option value="assets/images/products/backpack-1.jpg">Commuter Backpack Graphic</option>
          <option value="assets/images/products/relay-module-1.jpg">Industrial Relays & CNC Graphic</option>
          <option value="assets/images/products/tshirt-blanks-1.jpg">Textile Apparel Blanks Graphic</option>
          <option value="assets/images/products/corrugated-boxes-1.jpg">Packaging & Mailers Graphic</option>
          <option value="assets/images/products/diffuser-1.jpg">Home & Lifestyle Graphic</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label small fw-semibold">Short Summary</label>
        <input type="text" name="short_description" class="form-control" placeholder="Brief one-line highlighted specifications">
      </div>

      <div class="mb-4">
        <label class="form-label small fw-semibold">Full Product Description <span class="text-danger">*</span></label>
        <textarea name="description" class="form-control" rows="4" placeholder="Detail comprehensive product specifications, warranty info, and packaging ratios..." required><?= e($_POST['description'] ?? '') ?></textarea>
      </div>

      <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill fw-bold shadow-sm">
        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Publish Catalog Listing
      </button>
    </form>
  </div>
</div>

<script>
function toggleB2BFields(val) {
  const box = document.getElementById('b2bWholesaleFields');
  box.style.display = (val === 'B2B') ? 'block' : 'none';
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
