<?php
/**
 * MultiMart - Seller Inventory & Products Management
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

require_role(['business_seller', 'admin']);
$pageTitle = "My Products – Seller Portal";

$sellerId = (int)$_SESSION['user_id'];
$isAdmin = ($_SESSION['user_role_name'] === 'admin');
$where = $isAdmin ? "1=1" : "p.seller_id = $sellerId";

// Handle Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $delId = (int)($_POST['product_id'] ?? 0);
        db_delete('products', "id = ? AND ($where)", [$delId]);
        set_flash('info', "Product removed from your catalog.");
    }
}

$products = db_fetch_all("
    SELECT p.*, c.name as category_name,
           (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE {$where}
    ORDER BY p.id DESC
");

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <span class="badge bg-primary text-uppercase fw-bold mb-1">Catalog Management</span>
      <h3 class="fw-bold mb-0">My Products & Inventory (<?= count($products) ?>)</h3>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= base_url('seller/products_add.php') ?>" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
        <i class="bi bi-plus-circle me-1"></i> Add Product
      </a>
      <a href="<?= base_url('seller/dashboard.php') ?>" class="btn btn-outline-secondary rounded-pill px-3">
        Workspace
      </a>
    </div>
  </div>

  <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <div class="table-responsive">
      <table class="table table-hover align-middle small mb-0">
        <thead class="table-light">
          <tr>
            <th>Product</th>
            <th>Type</th>
            <th>Category</th>
            <th>Retail / Lot Price</th>
            <th>Stock</th>
            <th>MOQ</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $p): ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <img src="<?= base_url($p['primary_image'] ?? 'assets/images/products/headphones-1.jpg') ?>" class="rounded-3 border" width="45" height="45" style="object-fit: cover;" alt="Prod">
                  <div>
                    <div class="fw-bold text-truncate" style="max-width: 200px;"><?= e($p['title']) ?></div>
                    <code style="font-size: 0.7rem;"><?= e($p['sku']) ?></code>
                  </div>
                </div>
              </td>
              <td>
                <?php if ($p['is_b2b']): ?><span class="badge badge-b2b">B2B Wholesale</span>
                <?php else: ?><span class="badge badge-b2c">B2C Retail</span><?php endif; ?>
              </td>
              <td><?= e($p['category_name']) ?></td>
              <td class="fw-bold"><?= format_currency($p['discount_price'] ?: $p['price']) ?></td>
              <td>
                <span class="<?= $p['stock_quantity'] <= 30 ? 'text-danger fw-bold' : '' ?>">
                  <?= $p['stock_quantity'] ?> units
                </span>
              </td>
              <td><?= $p['min_order_qty'] ?></td>
              <td><span class="badge bg-success-subtle text-success text-uppercase"><?= e($p['status']) ?></span></td>
              <td class="text-end">
                <a href="<?= base_url('seller/products_edit.php?id=' . $p['id']) ?>" class="btn btn-outline-primary btn-sm rounded-circle p-1" title="Edit">
                  <i class="bi bi-pencil"></i>
                </a>
                <form method="POST" action="" class="d-inline" onsubmit="return confirm('Delete this product?');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                  <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle p-1" title="Delete">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
