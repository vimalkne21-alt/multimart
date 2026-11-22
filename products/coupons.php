<?php
/**
 * MultiMart - Discount Coupons & Special Deals Showcase
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$pageTitle = "Discount Coupons & Promo Offers – MultiMart";
$coupons = db_fetch_all("SELECT * FROM coupons WHERE status = 'active' ORDER BY id ASC");

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5" style="max-width: 900px;">
  <div class="text-center mb-5">
    <span class="badge bg-danger-subtle text-danger fw-bold text-uppercase mb-2"><i class="bi bi-tag-fill me-1"></i> Mega Savings</span>
    <h1 class="display-6 fw-bold">Active Coupons & Promotional Codes</h1>
    <p class="text-muted">Apply these promo vouchers at simulated checkout to save on retail and bulk purchases.</p>
  </div>

  <div class="row g-4">
    <?php foreach ($coupons as $cpn): ?>
      <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bg-white" style="border-left: 6px solid var(--primary) !important;">
          <div class="card-body p-4 d-flex flex-column justify-content-between">
            <div>
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fs-4 fw-extrabold text-primary"><?= e($cpn['code']) ?></span>
                <span class="badge bg-success-subtle text-success fw-bold">Active</span>
              </div>
              <h6 class="fw-bold mb-2"><?= e($cpn['description']) ?></h6>
              <ul class="list-unstyled small text-muted mb-4 d-flex flex-column gap-1">
                <li><i class="bi bi-check2 text-success me-1"></i> Discount: <strong><?= $cpn['discount_type'] === 'percentage' ? $cpn['discount_value'] . '%' : format_currency($cpn['discount_value']) ?></strong></li>
                <li><i class="bi bi-check2 text-success me-1"></i> Minimum Cart Spend: <strong><?= format_currency($cpn['min_spend']) ?></strong></li>
                <li><i class="bi bi-check2 text-success me-1"></i> Maximum Discount Cap: <strong><?= format_currency($cpn['max_discount']) ?></strong></li>
                <li><i class="bi bi-clock me-1"></i> Valid through: <?= format_date($cpn['expiry_date'], 'd M Y') ?></li>
              </ul>
            </div>
            <button class="btn btn-outline-primary rounded-pill w-100 fw-semibold" onclick="navigator.clipboard.writeText('<?= e($cpn['code']) ?>'); showToast('Coupon Copied', '<?= e($cpn['code']) ?> is ready to paste at checkout!', 'success');">
              <i class="bi bi-clipboard me-1"></i> Copy Code
            </button>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
