<?php
/**
 * MultiMart - C2B Service Details & Tiered Packages View
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$service = db_fetch_one("
    SELECT s.*, c.name as category_name, u.full_name as freelancer_name, u.email as freelancer_email, u.avatar as freelancer_avatar, u.created_at as joined_date
    FROM services s
    JOIN categories c ON s.category_id = c.id
    JOIN users u ON s.freelancer_id = u.id
    WHERE s.id = ?
", [$id]);

if (!$service) {
    set_flash('error', "Service listing not found.");
    header("Location: " . base_url('c2b/index.php'));
    exit;
}

$pageTitle = e($service['title']) . " – MultiMart Freelance Desk";

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb small">
      <li class="breadcrumb-item"><a href="<?= base_url('index.php') ?>" class="text-decoration-none">Home</a></li>
      <li class="breadcrumb-item"><a href="<?= base_url('c2b/index.php') ?>" class="text-decoration-none">C2B Talent</a></li>
      <li class="breadcrumb-item active" aria-current="page"><?= e($service['title']) ?></li>
    </ol>
  </nav>

  <div class="row g-5">
    <div class="col-lg-8">
      <div class="d-flex align-items-center gap-2 mb-2">
        <span class="badge badge-c2b text-uppercase">C2B Freelance Offering</span>
        <span class="text-muted small">Domain: <strong><?= e($service['category_name']) ?></strong></span>
      </div>

      <h2 class="fw-bold mb-3"><?= e($service['title']) ?></h2>

      <!-- Freelancer Card -->
      <div class="card border rounded-4 p-3 bg-white mb-4 shadow-sm">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-3">
            <img src="<?= base_url($service['freelancer_avatar']) ?>" alt="Freelancer" class="rounded-circle border" width="56" height="56">
            <div>
              <h6 class="fw-bold mb-0"><?= e($service['freelancer_name']) ?></h6>
              <div class="text-muted small">Specialist Engineer • Member since <?= format_date($service['joined_date'], 'M Y') ?></div>
              <?php if ($service['portfolio_url']): ?>
                <a href="<?= e($service['portfolio_url']) ?>" target="_blank" class="small text-decoration-none text-primary">
                  <i class="bi bi-github me-1"></i> Public GitHub Portfolio
                </a>
              <?php endif; ?>
            </div>
          </div>
          <a href="<?= base_url('messages/chat.php?receiver=' . $service['freelancer_id'] . '&service=' . $service['id']) ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-chat-dots me-1"></i> Contact
          </a>
        </div>
      </div>

      <!-- Overview & Deliverables -->
      <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
        <h5 class="fw-bold mb-3">Service Scope & Description</h5>
        <div class="text-secondary small mb-4" style="line-height: 1.7;">
          <?= nl2br(e($service['description'])) ?>
        </div>

        <h6 class="fw-bold small text-uppercase text-muted mb-2">Technology & Tools Utilized:</h6>
        <div class="d-flex flex-wrap gap-1">
          <?php foreach (explode(',', $service['skills_required']) as $sk): ?>
            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill small"><?= e(trim($sk)) ?></span>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Pricing Packages (3 Tiers) -->
    <div class="col-lg-4">
      <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white sticky-top" style="top: 80px;">
        <div class="card-header bg-dark text-white p-3 text-center">
          <span class="badge bg-success-subtle text-success text-uppercase fw-bold mb-1">Contract Packages</span>
          <h5 class="fw-bold mb-0">Choose Engagement Tier</h5>
        </div>

        <div class="card-body p-4">
          <ul class="nav nav-pills nav-fill mb-3" id="packageTab" role="tablist">
            <li class="nav-item">
              <button class="nav-link active small fw-bold" id="basic-tab" data-bs-toggle="pill" data-bs-target="#basicTier" type="button">Basic</button>
            </li>
            <li class="nav-item">
              <button class="nav-link small fw-bold" id="std-tab" data-bs-toggle="pill" data-bs-target="#stdTier" type="button">Standard</button>
            </li>
            <li class="nav-item">
              <button class="nav-link small fw-bold" id="prem-tab" data-bs-toggle="pill" data-bs-target="#premTier" type="button">Premium</button>
            </li>
          </ul>

          <div class="tab-content" id="packageTabContent">
            <!-- Basic Tier -->
            <div class="tab-pane fade show active" id="basicTier">
              <div class="d-flex justify-content-between align-items-baseline mb-2">
                <span class="text-muted small">Proof-of-Concept Tier</span>
                <span class="fs-3 fw-bold text-success"><?= format_currency($service['basic_price']) ?></span>
              </div>
              <p class="small text-secondary mb-3">Core prototype model script with initial data cleaning and basic exploratory visualizations.</p>
              <div class="small text-muted mb-3"><i class="bi bi-clock me-1"></i> Delivery in <strong><?= $service['delivery_days'] ?> Days</strong></div>
              <a href="<?= base_url('c2b/hire.php?service_id=' . $service['id'] . '&tier=basic') ?>" class="btn btn-success w-100 rounded-pill fw-semibold py-2">
                Select Basic Contract
              </a>
            </div>

            <!-- Standard Tier -->
            <div class="tab-pane fade" id="stdTier">
              <div class="d-flex justify-content-between align-items-baseline mb-2">
                <span class="text-muted small">Production Ready Tier</span>
                <span class="fs-3 fw-bold text-success"><?= format_currency($service['standard_price']) ?></span>
              </div>
              <p class="small text-secondary mb-3">Complete pipeline orchestration, hyperparameter tuning, model persistence, and modular API endpoints.</p>
              <div class="small text-muted mb-3"><i class="bi bi-clock me-1"></i> Delivery in <strong><?= $service['delivery_days'] + 3 ?> Days</strong></div>
              <a href="<?= base_url('c2b/hire.php?service_id=' . $service['id'] . '&tier=standard') ?>" class="btn btn-success w-100 rounded-pill fw-semibold py-2">
                Select Standard Contract
              </a>
            </div>

            <!-- Premium Tier -->
            <div class="tab-pane fade" id="premTier">
              <div class="d-flex justify-content-between align-items-baseline mb-2">
                <span class="text-muted small">Enterprise Deployment</span>
                <span class="fs-3 fw-bold text-success"><?= format_currency($service['premium_price']) ?></span>
              </div>
              <p class="small text-secondary mb-3">Full end-to-end integration, Docker containerization, Streamlit analytical dashboard, and unit testing suites.</p>
              <div class="small text-muted mb-3"><i class="bi bi-clock me-1"></i> Delivery in <strong><?= $service['delivery_days'] + 7 ?> Days</strong></div>
              <a href="<?= base_url('c2b/hire.php?service_id=' . $service['id'] . '&tier=premium') ?>" class="btn btn-success w-100 rounded-pill fw-semibold py-2">
                Select Premium Contract
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
