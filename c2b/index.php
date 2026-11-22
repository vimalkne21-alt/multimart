<?php
/**
 * MultiMart - C2B Professional Services & Freelance Marketplace
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$pageTitle = "C2B Freelance Services & Technical Talent – MultiMart";

$catId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = trim(sanitize_input($_GET['q'] ?? ''));

$categories = db_fetch_all("SELECT * FROM categories WHERE module_type IN ('C2B', 'ALL') AND status = 'active' ORDER BY name ASC");

$where = ["s.status = 'active'"];
$params = [];

if ($catId > 0) {
    $where[] = "s.category_id = ?";
    $params[] = $catId;
}

if (!empty($search)) {
    $where[] = "(s.title LIKE ? OR s.description LIKE ? OR s.skills_required LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = implode(' AND ', $where);
$services = db_fetch_all("
    SELECT s.*, c.name as category_name, u.full_name as freelancer_name, u.avatar as freelancer_avatar,
           (SELECT AVG(rating) FROM reviews WHERE service_id = s.id AND status = 'approved') as avg_rating,
           (SELECT COUNT(*) FROM reviews WHERE service_id = s.id AND status = 'approved') as review_count
    FROM services s
    JOIN categories c ON s.category_id = c.id
    JOIN users u ON s.freelancer_id = u.id
    WHERE {$whereClause}
    ORDER BY s.id DESC
", $params);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb small">
      <li class="breadcrumb-item"><a href="<?= base_url('index.php') ?>" class="text-decoration-none">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">C2B Freelance Talent Desk</li>
    </ol>
  </nav>

  <!-- Banner -->
  <div class="p-4 p-md-5 rounded-4 mb-4 shadow-sm text-white" style="background: linear-gradient(135deg, #064e3b 0%, #047857 50%, #10b981 100%);">
    <div class="row align-items-center">
      <div class="col-lg-8">
        <span class="badge bg-white text-success px-3 py-1 rounded-pill mb-2 small fw-bold">
          <i class="bi bi-briefcase-fill me-1"></i> Consumer to Business (C2B)
        </span>
        <h1 class="display-6 fw-bold mb-2">Hire Expert Freelancers & Technical Specialists</h1>
        <p class="lead mb-3 text-white-50" style="font-size: 1.05rem;">
          Connect your business with certified independent talent in Data Science, Machine Learning, Full-Stack PHP & React development, and UI/UX design.
        </p>
        <div class="d-flex flex-wrap gap-2">
          <a href="<?= base_url('services/create.php') ?>" class="btn btn-warning rounded-pill px-4 fw-bold text-dark shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> Offer Your Services (Freelancer)
          </a>
          <a href="<?= base_url('services/dashboard.php') ?>" class="btn btn-outline-light rounded-pill px-4 fw-semibold">
            <i class="bi bi-speedometer2 me-1"></i> Freelancer Workspace
          </a>
        </div>
      </div>
      <div class="col-lg-4 d-none d-lg-block text-end">
        <i class="bi bi-laptop fs-1" style="font-size: 6rem !important; opacity: 0.25;"></i>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <!-- Category Sidebar -->
    <div class="col-lg-3">
      <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
        <h6 class="fw-bold mb-3"><i class="bi bi-code-slash text-success me-1"></i> Technical Domains</h6>
        <div class="d-flex flex-column gap-1 mb-4">
          <a href="<?= base_url('c2b/index.php') ?>" class="text-decoration-none small py-1 <?= $catId === 0 ? 'fw-bold text-success' : 'text-secondary' ?>">
            <i class="bi bi-chevron-right me-1 small"></i> All Domains
          </a>
          <?php foreach ($categories as $cat): ?>
            <a href="<?= base_url('c2b/index.php?category=' . $cat['id']) ?>" class="text-decoration-none small py-1 <?= $catId === (int)$cat['id'] ? 'fw-bold text-success' : 'text-secondary' ?>">
              <i class="bi bi-chevron-right me-1 small"></i> <?= e($cat['name']) ?>
            </a>
          <?php endforeach; ?>
        </div>

        <div class="border-top pt-3">
          <div class="small fw-bold text-muted mb-2 text-uppercase" style="font-size: 0.72rem;">Enterprise Assurances</div>
          <ul class="list-unstyled small text-secondary d-flex flex-column gap-2 mb-0">
            <li><i class="bi bi-shield-check text-success me-1"></i> Verified GitHub Portfolios</li>
            <li><i class="bi bi-shield-check text-success me-1"></i> Transparent Tier Deliverables</li>
            <li><i class="bi bi-shield-check text-success me-1"></i> Milestone-based Approvals</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Services Grid -->
    <div class="col-lg-9">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="text-muted small">Showing <strong><?= count($services) ?></strong> professional talent offerings</span>
      </div>

      <div class="row g-4">
        <?php foreach ($services as $srv): ?>
          <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-4 bg-white d-flex flex-column">
              <div class="d-flex align-items-center gap-3 mb-3">
                <img src="<?= base_url($srv['freelancer_avatar']) ?>" alt="Freelancer" class="rounded-circle border" width="48" height="48">
                <div>
                  <h6 class="fw-bold mb-0"><?= e($srv['freelancer_name']) ?></h6>
                  <span class="text-muted small"><?= e($srv['category_name']) ?></span>
                </div>
              </div>

              <h5 class="fw-bold mb-2">
                <a href="<?= base_url('c2b/detail.php?id=' . $srv['id']) ?>" class="text-dark text-decoration-none">
                  <?= e($srv['title']) ?>
                </a>
              </h5>
              <p class="text-secondary small line-clamp-2 mb-3"><?= e($srv['description']) ?></p>

              <div class="mb-3">
                <span class="text-muted small fw-semibold me-1">Core Tech:</span>
                <?php foreach (explode(',', $srv['skills_required']) as $sk): ?>
                  <span class="badge bg-light text-dark border me-1 small"><?= e(trim($sk)) ?></span>
                <?php endforeach; ?>
              </div>

              <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                <div>
                  <div class="text-muted small" style="font-size: 0.72rem;">Starting Rate:</div>
                  <div class="fw-bold fs-5 text-success"><?= format_currency($srv['basic_price']) ?></div>
                </div>
                <a href="<?= base_url('c2b/detail.php?id=' . $srv['id']) ?>" class="btn btn-success btn-sm rounded-pill px-4 fw-semibold">
                  View Packages & Hire
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
