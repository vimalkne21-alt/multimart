<?php
/**
 * MultiMart - Master Catalog & Product Management (Admin View)
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

require_role('admin');
$pageTitle = "Catalog Moderation – Admin";

// Handle status toggle / deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $prodId = (int)($_POST['product_id'] ?? 0);
        $action = $_POST['action'] ?? '';

        if ($action === 'toggle_status') {
            $p = db_fetch_one("SELECT status FROM products WHERE id = ?", [$prodId]);
            $newStatus = ($p['status'] === 'active') ? 'inactive' : 'active';
            db_update('products', ['status' => $newStatus], 'id = ?', [$prodId]);
            log_activity('PRODUCT_STATUS_CHANGED', 'PRODUCT', $prodId, "Status updated to: $newStatus");
            set_flash('success', "Product status updated to: " . strtoupper($newStatus));
        } elseif ($action === 'delete') {
            db_delete('products', 'id = ?', [$prodId]);
            log_activity('PRODUCT_DELETED', 'PRODUCT', $prodId, "Product removed by administrator.");
            set_flash('info', "Product removed from marketplace catalog.");
        }
    }
}

$products = db_fetch_all("
    SELECT p.*, c.name as category_name, u.full_name as seller_name,
           (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
    FROM products p
    JOIN categories c ON p.category_id = c.id
    JOIN users u ON p.seller_id = u.id
    ORDER BY p.id DESC
");

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <span class="badge bg-danger text-uppercase fw-bold mb-1">Catalog Oversight</span>
      <h3 class="fw-bold mb-0">Platform Products & Listings (<?= count($products) ?>)</h3>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= base_url('seller/products_add.php') ?>" class="btn btn-primary btn-sm rounded-pill px-3">
        <i class="bi bi-plus-circle me-1"></i> Add Product
      </a>
      <a href="<?= base_url('admin/dashboard.php') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        Dashboard
      </a>
    </div>
  </div>

  <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <div class="table-responsive">
      <table class="table table-hover align-middle small mb-0">
        <thead class="table-light">
          <tr>
            <th>Product</th>
            <th>Model</th>
            <th>Seller</th>
            <th>Category</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $p): ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <img src="<?= base_url($p['primary_image'] ?? 'assets/images/products/headphones-1.jpg') ?>" class="rounded-3 border" width="40" height="40" style="object-fit: cover;" alt="Prod">
                  <div>
                    <div class="fw-bold text-truncate" style="max-width: 220px;"><?= e($p['title']) ?></div>
                    <code style="font-size: 0.7rem;"><?= e($p['sku']) ?></code>
                  </div>
                </div>
              </td>
              <td>
                <?php if ($p['is_b2b']): ?><span class="badge badge-b2b">B2B</span>
                <?php elseif ($p['is_c2c']): ?><span class="badge badge-c2c">C2C</span>
                <?php else: ?><span class="badge badge-b2c">B2C</span><?php endif; ?>
              </td>
              <td><?= e($p['seller_name']) ?></td>
              <td><?= e($p['category_name']) ?></td>
              <td class="fw-bold"><?= format_currency($p['discount_price'] ?: $p['price']) ?></td>
              <td><?= $p['stock_quantity'] ?></td>
              <td>
                <span class="badge <?= $p['status'] === 'active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?> text-uppercase">
                  <?= e($p['status']) ?>
                </span>
              </td>
              <td class="text-end">
                <form method="POST" action="" class="d-inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="toggle_status">
                  <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                  <button type="submit" class="btn btn-sm rounded-pill px-2 <?= $p['status'] === 'active' ? 'btn-outline-warning text-dark' : 'btn-outline-success' ?>" title="Toggle status">
                    <?= $p['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                  </button>
                </form>
                <form method="POST" action="" class="d-inline" onsubmit="return confirm('Delete this listing permanently?');">
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
