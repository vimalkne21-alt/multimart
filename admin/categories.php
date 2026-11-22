<?php
/**
 * MultiMart - Category Hierarchy & Module Classification (Admin)
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

require_role('admin');
$pageTitle = "Category Management – Admin";

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $name = trim(sanitize_input($_POST['name'] ?? ''));
        $module = sanitize_input($_POST['module_type'] ?? 'B2C');
        $desc = trim(sanitize_input($_POST['description'] ?? ''));
        $icon = trim(sanitize_input($_POST['icon'] ?? 'bi-tag'));

        if (!empty($name)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name))) . '-' . rand(10, 99);
            db_insert('categories', [
                'name' => $name,
                'slug' => $slug,
                'description' => $desc,
                'icon' => $icon,
                'module_type' => $module,
                'status' => 'active'
            ]);
            log_activity('CATEGORY_CREATED', 'CATEGORY', null, "Admin created category: $name ($module)");
            set_flash('success', "New category \"$name\" created for $module portal.");
            header("Location: " . base_url('admin/categories.php'));
            exit;
        }
    }
}

$categories = db_fetch_all("SELECT * FROM categories ORDER BY module_type ASC, name ASC");

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <span class="badge bg-danger text-uppercase fw-bold mb-1">Architecture</span>
      <h3 class="fw-bold mb-0">Marketplace Taxonomy & Categories</h3>
    </div>
    <a href="<?= base_url('admin/dashboard.php') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
      Admin Dashboard
    </a>
  </div>

  <div class="row g-4">
    <!-- Add Category Form -->
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
        <h5 class="fw-bold mb-3"><i class="bi bi-plus-circle text-primary me-2"></i> Add New Category</h5>
        <form method="POST" action="">
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Category Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Robotics & Automation" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Target E-Commerce Model <span class="text-danger">*</span></label>
            <select name="module_type" class="form-select form-select-sm" required>
              <option value="B2C">B2C Retail Shopping</option>
              <option value="B2B">B2B Wholesale & RFQ</option>
              <option value="C2C">C2C Pre-Owned Market</option>
              <option value="C2B">C2B Freelance Talent</option>
              <option value="ALL">Universal (All Portals)</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Bootstrap Icon Class</label>
            <input type="text" name="icon" class="form-control form-control-sm" placeholder="bi-cpu" value="bi-tag">
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Description</label>
            <textarea name="description" class="form-control form-control-sm" rows="3" placeholder="Category purpose..."></textarea>
          </div>
          <button type="submit" class="btn btn-primary btn-sm w-100 rounded-pill fw-semibold">
            Create Category
          </button>
        </form>
      </div>
    </div>

    <!-- Category List Table -->
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
        <h5 class="fw-bold mb-3">All Active Categories (<?= count($categories) ?>)</h5>
        <div class="table-responsive">
          <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
              <tr>
                <th>Icon</th>
                <th>Category Name</th>
                <th>Target Model</th>
                <th>Slug</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($categories as $cat): ?>
                <tr>
                  <td><i class="bi <?= e($cat['icon']) ?> fs-5 text-primary"></i></td>
                  <td class="fw-bold"><?= e($cat['name']) ?></td>
                  <td>
                    <?php if ($cat['module_type'] === 'B2B'): ?><span class="badge badge-b2b">B2B Wholesale</span>
                    <?php elseif ($cat['module_type'] === 'C2C'): ?><span class="badge badge-c2c">C2C Used</span>
                    <?php elseif ($cat['module_type'] === 'C2B'): ?><span class="badge badge-c2b">C2B Services</span>
                    <?php else: ?><span class="badge badge-b2c">B2C Retail</span><?php endif; ?>
                  </td>
                  <td><code><?= e($cat['slug']) ?></code></td>
                  <td><span class="badge bg-success-subtle text-success text-uppercase"><?= e($cat['status']) ?></span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
