<?php
/**
 * MultiMart - Create New Freelance Service Gig (C2B)
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

require_role(['freelancer', 'admin']);
$pageTitle = "Create New Service Offering – MultiMart";

$categories = db_fetch_all("SELECT * FROM categories WHERE module_type IN ('C2B', 'ALL') AND status = 'active' ORDER BY name ASC");
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Security validation error.";
    } else {
        $title = trim(sanitize_input($_POST['title'] ?? ''));
        $catId = (int)($_POST['category_id'] ?? 0);
        $skills = trim(sanitize_input($_POST['skills_required'] ?? ''));
        $basicPrice = (float)($_POST['basic_price'] ?? 0);
        $stdPrice = (float)($_POST['standard_price'] ?? 0);
        $premPrice = (float)($_POST['premium_price'] ?? 0);
        $deliveryDays = (int)($_POST['delivery_days'] ?? 5);
        $portfolioUrl = trim(sanitize_input($_POST['portfolio_url'] ?? 'https://github.com/freelancer-portfolio'));
        $description = trim(sanitize_input($_POST['description'] ?? ''));

        if (empty($title) || $catId <= 0 || $basicPrice <= 0 || empty($description)) {
            $error = "Please fill in all mandatory fields with valid rates.";
        } else {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title))) . '-' . rand(100, 999);

            $srvId = db_insert('services', [
                'freelancer_id' => $_SESSION['user_id'],
                'category_id' => $catId,
                'title' => $title,
                'slug' => $slug,
                'description' => $description,
                'skills_required' => $skills,
                'basic_price' => $basicPrice,
                'standard_price' => $stdPrice ?: ($basicPrice * 2.2),
                'premium_price' => $premPrice ?: ($basicPrice * 4.5),
                'delivery_days' => $deliveryDays,
                'portfolio_url' => $portfolioUrl,
                'status' => 'active'
            ]);

            log_activity('SERVICE_CREATED', 'SERVICE', $srvId, "Published new service: $title");
            set_flash('success', "Service gig \"$title\" published successfully!");
            header("Location: " . base_url('c2b/detail.php?id=' . $srvId));
            exit;
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5" style="max-width: 750px;">
  <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
    <div class="text-center mb-4">
      <span class="badge bg-success text-white px-3 py-1 rounded-pill fw-bold text-uppercase mb-2">
        <i class="bi bi-briefcase me-1"></i> Offer Services
      </span>
      <h3 class="fw-bold">Publish a Freelance Service Offering</h3>
      <p class="text-muted small">Showcase your specialized skills to businesses and clients.</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger py-2 small shadow-sm"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <?= csrf_field() ?>

      <div class="mb-3">
        <label class="form-label small fw-semibold">Service Gig Title <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control" placeholder="e.g. End-to-End Enterprise Data Science Pipeline & Interactive ML Dashboard" required value="<?= e($_POST['title'] ?? '') ?>">
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label small fw-semibold">Technical Domain / Category <span class="text-danger">*</span></label>
          <select name="category_id" class="form-select" required>
            <option value="">-- Choose Domain --</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-semibold">Core Skills (Comma separated)</label>
          <input type="text" name="skills_required" class="form-control" placeholder="Python, Scikit-Learn, Streamlit, SQL" value="<?= e($_POST['skills_required'] ?? '') ?>">
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Basic Tier Price (₹) <span class="text-danger">*</span></label>
          <input type="number" name="basic_price" class="form-control" placeholder="4999" step="0.01" required value="<?= e($_POST['basic_price'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Standard Tier Price (₹)</label>
          <input type="number" name="standard_price" class="form-control" placeholder="12999" step="0.01" value="<?= e($_POST['standard_price'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Premium Tier Price (₹)</label>
          <input type="number" name="premium_price" class="form-control" placeholder="24999" step="0.01" value="<?= e($_POST['premium_price'] ?? '') ?>">
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label small fw-semibold">Base Delivery Days</label>
          <input type="number" name="delivery_days" class="form-control" value="5" min="1">
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-semibold">Portfolio GitHub URL</label>
          <input type="url" name="portfolio_url" class="form-control" placeholder="https://github.com/your-username" value="https://github.com/freelancer-portfolio">
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label small fw-semibold">Scope of Service & Deliverables <span class="text-danger">*</span></label>
        <textarea name="description" class="form-control" rows="4" placeholder="Detail your experience, algorithm designs, deliverables, and revision policies..." required><?= e($_POST['description'] ?? '') ?></textarea>
      </div>

      <button type="submit" class="btn btn-success w-100 py-2 rounded-pill fw-bold shadow-sm mb-3">
        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Publish Service Offering
      </button>

      <div class="text-center small">
        <a href="<?= base_url('services/dashboard.php') ?>" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Freelancer Workspace</a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
