<?php
/**
 * MultiMart - Homepage
 * Unified Multi-Model E-Commerce Marketplace
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

$pageTitle = "MultiMart – All-in-One Multi-Model E-Commerce Marketplace";

// Fetch Featured B2C Products
$b2cProducts = db_fetch_all("
    SELECT p.*, c.name as category_name, 
           (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
           (SELECT AVG(rating) FROM reviews WHERE product_id = p.id AND status = 'approved') as avg_rating,
           (SELECT COUNT(*) FROM reviews WHERE product_id = p.id AND status = 'approved') as review_count
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.is_b2c = 1 AND p.status = 'active'
    ORDER BY p.id ASC
    LIMIT 4
");

// Fetch B2B Wholesale Products
$b2bProducts = db_fetch_all("
    SELECT p.*, c.name as category_name, b.company_name,
           (SELECT image_url FROM product_images WHERE product_id = p.id LIMIT 1) as primary_image
    FROM products p
    JOIN categories c ON p.category_id = c.id
    LEFT JOIN businesses b ON p.business_id = b.id
    WHERE p.is_b2b = 1 AND p.status = 'active'
    ORDER BY p.id ASC
    LIMIT 3
");

// Fetch C2C Used Items
$c2cProducts = db_fetch_all("
    SELECT p.*, c.name as category_name, u.full_name as seller_name,
           (SELECT image_url FROM product_images WHERE product_id = p.id LIMIT 1) as primary_image
    FROM products p
    JOIN categories c ON p.category_id = c.id
    JOIN users u ON p.seller_id = u.id
    WHERE p.is_c2c = 1 AND p.status = 'active'
    ORDER BY p.id ASC
    LIMIT 3
");

// Fetch C2B Services
$c2bServices = db_fetch_all("
    SELECT s.*, c.name as category_name, u.full_name as freelancer_name, u.avatar as freelancer_avatar
    FROM services s
    JOIN categories c ON s.category_id = c.id
    JOIN users u ON s.freelancer_id = u.id
    WHERE s.status = 'active'
    ORDER BY s.id ASC
    LIMIT 2
");

// Fetch active coupons
$coupons = db_fetch_all("SELECT * FROM coupons WHERE status = 'active' LIMIT 2");

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Banner Section -->
<section class="container mt-4">
  <div class="hero-banner shadow-lg">
    <div class="row align-items-center">
      <div class="col-lg-7">
        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold text-uppercase mb-3">
          <i class="bi bi-stars me-1"></i> Next-Gen E-Commerce Architecture
        </span>
        <h1 class="display-5 fw-extrabold text-white mb-3">
          The All-in-One Multi-Model Marketplace
        </h1>
        <p class="lead text-light mb-4" style="font-size: 1.15rem; opacity: 0.9;">
          Seamlessly uniting <strong>B2B Wholesale</strong>, <strong>B2C Retail</strong>, <strong>C2C Pre-Owned Trading</strong>, and <strong>C2B Professional Services</strong> on a single secure, role-governed platform.
        </p>
        <div class="d-flex flex-wrap gap-3">
          <a href="<?= base_url('b2c/index.php') ?>" class="btn btn-primary btn-lg px-4 rounded-pill shadow-sm">
            <i class="bi bi-cart3 me-2"></i> Shop Retail (B2C)
          </a>
          <a href="<?= base_url('b2b/index.php') ?>" class="btn btn-outline-light btn-lg px-4 rounded-pill">
            <i class="bi bi-building me-2"></i> Wholesale & RFQ (B2B)
          </a>
          <a href="<?= base_url('c2b/index.php') ?>" class="btn btn-outline-warning text-white btn-lg px-4 rounded-pill">
            <i class="bi bi-briefcase me-2"></i> Hire Freelancers (C2B)
          </a>
        </div>
      </div>
      <div class="col-lg-5 d-none d-lg-block text-center position-relative">
        <div class="p-4 bg-white bg-opacity-10 rounded-4 border border-white border-opacity-20 backdrop-blur shadow">
          <div class="text-uppercase tracking-wider text-warning fw-bold small mb-2">Architectural Highlights</div>
          <div class="row g-2 text-start small text-white">
            <div class="col-6"><i class="bi bi-check-circle-fill text-success me-1"></i> 30 Normalized Tables</div>
            <div class="col-6"><i class="bi bi-check-circle-fill text-success me-1"></i> Role-Based Dashboards</div>
            <div class="col-6"><i class="bi bi-check-circle-fill text-success me-1"></i> B2B RFQ Quotations</div>
            <div class="col-6"><i class="bi bi-check-circle-fill text-success me-1"></i> Simulated Safe Payments</div>
            <div class="col-6"><i class="bi bi-check-circle-fill text-success me-1"></i> Internal Messaging</div>
            <div class="col-6"><i class="bi bi-check-circle-fill text-success me-1"></i> PDO Prepared SQL</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Four Business Models Showcase Hub -->
<section class="container mt-5">
  <div class="text-center mb-4">
    <h2 class="fw-bold">Explore Our Four E-Commerce Ecosystems</h2>
    <p class="text-muted">MultiMart segregates workflows and permissions while sharing authentication and order intelligence.</p>
  </div>

  <div class="row g-4">
    <!-- Model 1: B2C -->
    <div class="col-md-6 col-lg-3">
      <a href="<?= base_url('b2c/index.php') ?>" class="hub-card">
        <div class="hub-icon-wrapper" style="background-color: #eff6ff; color: #2563eb;">
          <i class="bi bi-bag-heart-fill"></i>
        </div>
        <div class="badge badge-b2c mb-2">Model 1</div>
        <h5 class="fw-bold">B2C Retail Shopping</h5>
        <p class="text-muted small mb-0">Browse brand-new consumer electronics, fashion, and home lifestyle products with instant simulated checkout.</p>
      </a>
    </div>

    <!-- Model 2: B2B -->
    <div class="col-md-6 col-lg-3">
      <a href="<?= base_url('b2b/index.php') ?>" class="hub-card">
        <div class="hub-icon-wrapper" style="background-color: #f5f3ff; color: #7c3aed;">
          <i class="bi bi-building"></i>
        </div>
        <div class="badge badge-b2b mb-2">Model 2</div>
        <h5 class="fw-bold">B2B Wholesale & RFQ</h5>
        <p class="text-muted small mb-0">Procure bulk industrial supplies, custom garments, packaging lots with minimum order quantities & formal RFQ bidding.</p>
      </a>
    </div>

    <!-- Model 3: C2C -->
    <div class="col-md-6 col-lg-3">
      <a href="<?= base_url('c2c/index.php') ?>" class="hub-card">
        <div class="hub-icon-wrapper" style="background-color: #fffbeb; color: #d97706;">
          <i class="bi bi-arrow-repeat"></i>
        </div>
        <div class="badge badge-c2c mb-2">Model 3</div>
        <h5 class="fw-bold">C2C Pre-Owned Market</h5>
        <p class="text-muted small mb-0">Peer-to-peer consumer marketplace for college textbooks, vintage electronics, study desks with direct seller chat.</p>
      </a>
    </div>

    <!-- Model 4: C2B -->
    <div class="col-md-6 col-lg-3">
      <a href="<?= base_url('c2b/index.php') ?>" class="hub-card">
        <div class="hub-icon-wrapper" style="background-color: #ecfdf5; color: #059669;">
          <i class="bi bi-laptop"></i>
        </div>
        <div class="badge badge-c2b mb-2">Model 4</div>
        <h5 class="fw-bold">C2B Freelance Services</h5>
        <p class="text-muted small mb-0">Individuals and professionals offer skills in Data Science, Full-Stack Dev, and Design directly to business clients.</p>
      </a>
    </div>
  </div>
</section>

<!-- Active Promotional Coupons -->
<?php if (!empty($coupons)): ?>
<section class="container mt-5">
  <div class="row g-3">
    <?php foreach ($coupons as $cpn): ?>
      <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 p-3 d-flex flex-row align-items-center justify-content-between" style="background: linear-gradient(135deg, #f8fafc, #edf2f7); border-left: 5px solid var(--primary) !important;">
          <div>
            <span class="badge bg-primary text-uppercase mb-1"><?= e($cpn['code']) ?></span>
            <h6 class="fw-bold mb-1"><?= e($cpn['description']) ?></h6>
            <div class="text-muted small">Min Spend: <?= format_currency($cpn['min_spend']) ?> | Max Discount: <?= format_currency($cpn['max_discount']) ?></div>
          </div>
          <button class="btn btn-outline-primary btn-sm rounded-pill fw-semibold" onclick="navigator.clipboard.writeText('<?= e($cpn['code']) ?>'); showToast('Copied', 'Coupon code <?= e($cpn['code']) ?> copied to clipboard!', 'info');">
            <i class="bi bi-clipboard me-1"></i> Copy
          </button>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- Section: B2C Featured Retail Products -->
<section class="container mt-5">
  <div class="d-flex justify-content-between align-items-end mb-4">
    <div>
      <span class="badge badge-b2c text-uppercase mb-1">Business to Consumer</span>
      <h3 class="fw-bold mb-0">Trending Consumer Products</h3>
    </div>
    <a href="<?= base_url('b2c/index.php') ?>" class="btn btn-link text-decoration-none fw-semibold">View All Retail <i class="bi bi-arrow-right"></i></a>
  </div>

  <div class="row g-4">
    <?php foreach ($b2cProducts as $prod): ?>
      <div class="col-sm-6 col-lg-3">
        <div class="product-card">
          <button class="wishlist-btn-corner btn-toggle-wishlist" data-product-id="<?= $prod['id'] ?>" title="Add to Wishlist">
            <i class="bi bi-heart"></i>
          </button>
          <div class="product-img-box">
            <img src="<?= base_url($prod['primary_image'] ?? 'assets/images/products/headphones-1.jpg') ?>" alt="<?= e($prod['title']) ?>">
          </div>
          <div class="product-card-body">
            <span class="text-muted small mb-1"><?= e($prod['category_name']) ?></span>
            <h6 class="fw-bold mb-2 text-truncate" title="<?= e($prod['title']) ?>">
              <a href="<?= base_url('products/detail.php?id=' . $prod['id']) ?>" class="text-dark text-decoration-none">
                <?= e($prod['title']) ?>
              </a>
            </h6>
            
            <div class="d-flex align-items-center mb-2 small text-warning">
              <?php 
                $r = round($prod['avg_rating'] ?: 4.5);
                for ($i = 1; $i <= 5; $i++) {
                  echo $i <= $r ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>';
                }
              ?>
              <span class="text-muted ms-1">(<?= $prod['review_count'] ?: 2 ?>)</span>
            </div>

            <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-center">
              <div>
                <span class="product-price-current"><?= format_currency($prod['discount_price'] ?: $prod['price']) ?></span>
                <?php if ($prod['discount_price']): ?>
                  <span class="product-price-original"><?= format_currency($prod['price']) ?></span>
                <?php endif; ?>
              </div>
              <button class="btn btn-primary btn-sm rounded-pill btn-add-to-cart px-3" data-product-id="<?= $prod['id'] ?>">
                <i class="bi bi-cart-plus me-1"></i> Add
              </button>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- Section: B2B Wholesale & RFQ Spotlight -->
<section class="container mt-5">
  <div class="p-4 p-lg-5 rounded-4 shadow-sm" style="background-color: #f5f3ff; border: 1px solid #ddd6fe;">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
      <div>
        <span class="badge badge-b2b text-uppercase mb-2">Business to Business</span>
        <h3 class="fw-bold text-dark mb-1">Wholesale Supplies & RFQ Procurement</h3>
        <p class="text-muted mb-0">High-volume master cartons, tiered volume discounts, and verified supplier contracts.</p>
      </div>
      <a href="<?= base_url('b2b/index.php') ?>" class="btn btn-outline-primary rounded-pill mt-3 mt-md-0 fw-semibold">
        Explore B2B Catalog <i class="bi bi-arrow-right ms-1"></i>
      </a>
    </div>

    <div class="row g-4">
      <?php foreach ($b2bProducts as $prod): ?>
        <div class="col-md-4">
          <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="product-img-box" style="padding-top: 60%;">
              <img src="<?= base_url($prod['primary_image'] ?? 'assets/images/products/relay-module-1.jpg') ?>" alt="<?= e($prod['title']) ?>">
            </div>
            <div class="card-body p-4 d-flex flex-column">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-secondary-subtle text-secondary small fw-bold">MOQ: <?= $prod['min_order_qty'] ?> Units</span>
                <span class="text-muted small"><i class="bi bi-shield-check text-success me-1"></i> Verified Supplier</span>
              </div>
              <h5 class="fw-bold mb-2"><?= e($prod['title']) ?></h5>
              <p class="text-muted small line-clamp-2"><?= e($prod['short_description']) ?></p>
              
              <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                <div>
                  <div class="text-muted small">Lot Price:</div>
                  <div class="fw-bold fs-5 text-dark"><?= format_currency($prod['discount_price'] ?: $prod['price']) ?></div>
                </div>
                <a href="<?= base_url('b2b/rfq_create.php?product_id=' . $prod['id']) ?>" class="btn btn-purple btn-sm text-white rounded-pill px-3 py-2 fw-semibold" style="background-color: #7c3aed;">
                  <i class="bi bi-file-earmark-text me-1"></i> Request RFQ
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Section: C2C Pre-Owned Marketplace -->
<section class="container mt-5">
  <div class="d-flex justify-content-between align-items-end mb-4">
    <div>
      <span class="badge badge-c2c text-uppercase mb-1">Consumer to Consumer</span>
      <h3 class="fw-bold mb-0">Pre-Owned & Student Market</h3>
      <p class="text-muted mb-0">Affordable second-hand engineering textbooks, camera gear, and campus essentials.</p>
    </div>
    <a href="<?= base_url('c2c/index.php') ?>" class="btn btn-link text-decoration-none fw-semibold">View All Used Items <i class="bi bi-arrow-right"></i></a>
  </div>

  <div class="row g-4">
    <?php foreach ($c2cProducts as $prod): ?>
      <div class="col-md-4">
        <div class="card h-100 border rounded-4 shadow-sm overflow-hidden">
          <div class="product-img-box" style="padding-top: 60%;">
            <img src="<?= base_url($prod['primary_image'] ?? 'assets/images/products/cs-textbooks-1.jpg') ?>" alt="<?= e($prod['title']) ?>">
          </div>
          <div class="card-body p-3 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="badge bg-warning-subtle text-warning-emphasis text-uppercase" style="font-size: 0.7rem;">Condition: <?= str_replace('_', ' ', e($prod['condition_type'])) ?></span>
              <span class="text-muted small"><i class="bi bi-geo-alt me-1"></i> <?= e($prod['location']) ?></span>
            </div>
            <h6 class="fw-bold text-truncate mb-2"><?= e($prod['title']) ?></h6>
            <div class="text-muted small mb-3">Listed by: <strong><?= e($prod['seller_name']) ?></strong></div>
            
            <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-center">
              <span class="fw-bold fs-5 text-dark"><?= format_currency($prod['discount_price'] ?: $prod['price']) ?></span>
              <a href="<?= base_url('c2c/detail.php?id=' . $prod['id']) ?>" class="btn btn-outline-warning text-dark btn-sm rounded-pill px-3 fw-semibold">
                <i class="bi bi-chat-dots me-1"></i> Contact Seller
              </a>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- Section: C2B Professional Freelancing & Skills -->
<section class="container mt-5 mb-5">
  <div class="p-4 p-lg-5 rounded-4 shadow-sm" style="background-color: #ecfdf5; border: 1px solid #a7f3d0;">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
      <div>
        <span class="badge badge-c2b text-uppercase mb-2">Consumer to Business</span>
        <h3 class="fw-bold text-dark mb-1">Hire Talented Freelancers & Domain Specialists</h3>
        <p class="text-muted mb-0">Empower your enterprise with certified technical freelancers in Data Science, Full-Stack and AI.</p>
      </div>
      <a href="<?= base_url('c2b/index.php') ?>" class="btn btn-outline-success rounded-pill mt-3 mt-md-0 fw-semibold">
        Explore Services <i class="bi bi-arrow-right ms-1"></i>
      </a>
    </div>

    <div class="row g-4">
      <?php foreach ($c2bServices as $srv): ?>
        <div class="col-md-6">
          <div class="card h-100 border-0 shadow-sm rounded-4 p-4">
            <div class="d-flex align-items-center gap-3 mb-3">
              <img src="<?= base_url($srv['freelancer_avatar']) ?>" alt="Freelancer" class="freelancer-badge-avatar border">
              <div>
                <h6 class="fw-bold mb-0"><?= e($srv['freelancer_name']) ?></h6>
                <span class="text-muted small"><?= e($srv['category_name']) ?></span>
              </div>
            </div>
            <h5 class="fw-bold mb-2"><?= e($srv['title']) ?></h5>
            <p class="text-muted small mb-3"><?= e($srv['description']) ?></p>
            <div class="mb-3">
              <span class="text-muted small fw-semibold me-2">Skills:</span>
              <?php foreach (explode(',', $srv['skills_required']) as $skill): ?>
                <span class="badge bg-light text-dark border me-1 small"><?= e(trim($skill)) ?></span>
              <?php endforeach; ?>
            </div>
            <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
              <div>
                <span class="text-muted small">Starting from:</span>
                <span class="fw-bold fs-5 text-success ms-1"><?= format_currency($srv['basic_price']) ?></span>
              </div>
              <a href="<?= base_url('c2b/detail.php?id=' . $srv['id']) ?>" class="btn btn-success btn-sm rounded-pill px-4 fw-semibold">
                View Gig & Hire
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Trust & Security Signals -->
<section class="container mt-4 mb-4">
  <div class="row g-4 text-center">
    <div class="col-md-3">
      <div class="p-3 bg-white rounded-4 border shadow-sm">
        <i class="bi bi-shield-check text-primary fs-2 mb-2 d-block"></i>
        <h6 class="fw-bold mb-1">Simulated Secure Gateway</h6>
        <p class="text-muted small mb-0">Safe testing environment for UPI, Debit/Credit Card and Net Banking.</p>
      </div>
    </div>
    <div class="col-md-3">
      <div class="p-3 bg-white rounded-4 border shadow-sm">
        <i class="bi bi-award text-purple fs-2 mb-2 d-block" style="color: #7c3aed;"></i>
        <h6 class="fw-bold mb-1">Corporate B2B RFQs</h6>
        <p class="text-muted small mb-0">Direct vendor quotation workflows and quantity-based discounts.</p>
      </div>
    </div>
    <div class="col-md-3">
      <div class="p-3 bg-white rounded-4 border shadow-sm">
        <i class="bi bi-chat-quote text-warning fs-2 mb-2 d-block"></i>
        <h6 class="fw-bold mb-1">Internal Messaging</h6>
        <p class="text-muted small mb-0">Direct negotiation between buyers, sellers and freelancers.</p>
      </div>
    </div>
    <div class="col-md-3">
      <div class="p-3 bg-white rounded-4 border shadow-sm">
        <i class="bi bi-truck text-success fs-2 mb-2 d-block"></i>
        <h6 class="fw-bold mb-1">End-to-End Tracking</h6>
        <p class="text-muted small mb-0">Complete lifecycle order updates, invoice printing and returns.</p>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
