<?php
/**
 * MultiMart - Unified Global Header Template
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$currentUser = current_user();
$pageTitle = $pageTitle ?? APP_NAME . ' – ' . APP_TAGLINE;

// Fetch Cart & Wishlist counts for logged-in user
$cartCount = 0;
$wishlistCount = 0;
$unreadNotifications = 0;

if ($currentUser) {
    try {
        $cartRow = db_fetch_one("SELECT COUNT(*) as cnt FROM cart_items ci JOIN carts c ON ci.cart_id = c.id WHERE c.user_id = ?", [$currentUser['id']]);
        $cartCount = (int)($cartRow['cnt'] ?? 0);

        $wlRow = db_fetch_one("SELECT COUNT(*) as cnt FROM wishlist_items wi JOIN wishlists w ON wi.wishlist_id = w.id WHERE w.user_id = ?", [$currentUser['id']]);
        $wishlistCount = (int)($wlRow['cnt'] ?? 0);

        $notifRow = db_fetch_one("SELECT COUNT(*) as cnt FROM notifications WHERE user_id = ? AND is_read = 0", [$currentUser['id']]);
        $unreadNotifications = (int)($notifRow['cnt'] ?? 0);
    } catch (Exception $e) {
        // Fallback gracefully
    }
} else {
    // Session-based cart fallback for guests
    $cartCount = isset($_SESSION['guest_cart']) ? count($_SESSION['guest_cart']) : 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="MultiMart: Academic Major Multi-Model E-Commerce Platform supporting B2B, B2C, C2C and C2B.">
  
  <!-- Favicon -->
  <link rel="icon" type="image/svg+xml" href="<?= base_url('assets/images/default-avatar.png') ?>">

  <!-- Google Fonts: Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Bootstrap 5.3 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!-- Custom Stylesheet -->
  <link rel="stylesheet" href="<?= base_url('public/css/style.css') ?>">
</head>
<body data-base-url="<?= base_url() ?>">

  <!-- Top Announcement Bar -->
  <div class="top-bar d-none d-md-block">
    <div class="container d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center gap-3">
        <span><i class="bi bi-shield-check text-success me-1"></i> B.Tech CSE / Data Science Academic Major Project</span>
        <span class="text-secondary">|</span>
        <span><i class="bi bi-layers me-1"></i> 4 Business Models: B2B • B2C • C2C • C2B</span>
      </div>
      <div class="d-flex align-items-center gap-3">
        <a href="<?= base_url('b2b/index.php') ?>"><i class="bi bi-building me-1"></i> Wholesale Portal</a>
        <a href="<?= base_url('c2b/index.php') ?>"><i class="bi bi-briefcase me-1"></i> Hire Freelancers</a>
        <a href="<?= base_url('help.php') ?>"><i class="bi bi-question-circle me-1"></i> Help & FAQ</a>
      </div>
    </div>
  </div>

  <!-- Main Navigation Bar -->
  <nav class="navbar navbar-expand-lg main-navbar py-2">
    <div class="container">
      <!-- Brand Logo -->
      <a class="brand-logo me-4" href="<?= base_url('index.php') ?>">
        <i class="bi bi-shop-window text-primary fs-3"></i>
        <span>MultiMart</span>
        <span class="brand-badge">All-in-One</span>
      </a>

      <!-- Mobile Toggle -->
      <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbarContent">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="mainNavbarContent">
        <!-- Search Bar with Suggestions -->
        <div class="search-wrapper mx-lg-4 my-2 my-lg-0 flex-grow-1" style="max-width: 600px;">
          <form action="<?= base_url('products/search.php') ?>" method="GET" class="d-flex position-relative">
            <input 
              type="text" 
              name="q" 
              id="mainSearchInput" 
              class="form-control search-input" 
              placeholder="Search products, wholesale lots, used items or services..." 
              value="<?= e($_GET['q'] ?? '') ?>"
              autocomplete="off"
              required>
            <button class="btn btn-primary search-btn" type="submit">
              <i class="bi bi-search"></i>
            </button>
            <div id="searchSuggestionsBox" class="search-suggestions-box"></div>
          </form>
        </div>

        <!-- Right Side Nav Actions -->
        <ul class="navbar-nav ms-auto align-items-center gap-1 gap-lg-3">
          <!-- Wishlist -->
          <li class="nav-item">
            <a class="nav-link position-relative text-dark px-2" href="<?= base_url('customer/wishlist.php') ?>" title="My Wishlist">
              <i class="bi bi-heart fs-5"></i>
              <span id="wishlistCounterBadge" class="position-absolute top-1 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; <?= $wishlistCount > 0 ? '' : 'display:none;' ?>">
                <?= $wishlistCount ?>
              </span>
            </a>
          </li>

          <!-- Cart -->
          <li class="nav-item">
            <a class="nav-link position-relative text-dark px-2" href="<?= base_url('cart/index.php') ?>" title="Shopping Cart">
              <i class="bi bi-cart3 fs-5"></i>
              <span id="cartCounterBadge" class="position-absolute top-1 start-100 translate-middle badge rounded-pill bg-primary" style="font-size: 0.65rem; <?= $cartCount > 0 ? '' : 'display:none;' ?>">
                <?= $cartCount ?>
              </span>
            </a>
          </li>

          <!-- User Authentication Dropdown -->
          <?php if ($currentUser): ?>
            <!-- Notifications -->
            <li class="nav-item dropdown">
              <a class="nav-link position-relative text-dark px-2" href="#" data-bs-toggle="dropdown">
                <i class="bi bi-bell fs-5"></i>
                <?php if ($unreadNotifications > 0): ?>
                  <span class="position-absolute top-1 start-100 translate-middle badge rounded-pill bg-warning text-dark" style="font-size: 0.65rem;">
                    <?= $unreadNotifications ?>
                  </span>
                <?php endif; ?>
              </a>
              <div class="dropdown-menu dropdown-menu-end shadow-sm p-2" style="width: 300px;">
                <div class="d-flex justify-content-between align-items-center px-2 py-1 border-bottom mb-2">
                  <span class="fw-bold small">Notifications</span>
                  <a href="<?= base_url('customer/notifications.php') ?>" class="text-primary small text-decoration-none">View All</a>
                </div>
                <div class="small text-muted px-2 py-2">
                  <i class="bi bi-bell-fill text-primary me-1"></i> You have <?= $unreadNotifications ?> unread notifications.
                </div>
              </div>
            </li>

            <!-- Messages -->
            <li class="nav-item">
              <a class="nav-link text-dark px-2" href="<?= base_url('messages/inbox.php') ?>" title="Internal Messages">
                <i class="bi bi-chat-dots fs-5"></i>
              </a>
            </li>

            <!-- User Menu -->
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
                <img src="<?= base_url($currentUser['avatar']) ?>" alt="Avatar" class="rounded-circle border" width="34" height="34">
                <span class="d-none d-lg-inline fw-semibold small"><?= e(explode(' ', $currentUser['full_name'])[0]) ?></span>
              </a>
              <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li class="px-3 py-2 border-bottom">
                  <div class="fw-bold text-truncate"><?= e($currentUser['full_name']) ?></div>
                  <div class="text-muted small text-truncate"><?= e($currentUser['email']) ?></div>
                  <span class="badge bg-secondary mt-1 text-uppercase" style="font-size: 0.68rem;"><?= e($currentUser['role_name']) ?></span>
                </li>

                <!-- Role-Based Dashboard Shortcuts -->
                <?php if ($currentUser['role_name'] === 'admin'): ?>
                  <li><a class="dropdown-item text-danger fw-semibold" href="<?= base_url('admin/dashboard.php') ?>"><i class="bi bi-speedometer2 me-2"></i>Admin Dashboard</a></li>
                <?php endif; ?>

                <?php if ($currentUser['role_name'] === 'business_seller' || $currentUser['role_name'] === 'admin'): ?>
                  <li><a class="dropdown-item fw-semibold text-primary" href="<?= base_url('seller/dashboard.php') ?>"><i class="bi bi-shop me-2"></i>Seller Dashboard</a></li>
                <?php endif; ?>

                <?php if ($currentUser['role_name'] === 'freelancer' || $currentUser['role_name'] === 'admin'): ?>
                  <li><a class="dropdown-item fw-semibold text-success" href="<?= base_url('services/dashboard.php') ?>"><i class="bi bi-laptop me-2"></i>Freelancer Desk</a></li>
                <?php endif; ?>

                <li><a class="dropdown-item" href="<?= base_url('customer/dashboard.php') ?>"><i class="bi bi-person-circle me-2"></i>My Customer Hub</a></li>
                <li><a class="dropdown-item" href="<?= base_url('customer/orders.php') ?>"><i class="bi bi-box-seam me-2"></i>My Orders</a></li>
                <li><a class="dropdown-item" href="<?= base_url('auth/profile.php') ?>"><i class="bi bi-gear me-2"></i>Account Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="<?= base_url('auth/logout.php') ?>"><i class="bi bi-box-arrow-right me-2"></i>Sign Out</a></li>
              </ul>
            </li>

          <?php else: ?>
            <li class="nav-item">
              <a class="btn btn-outline-primary btn-sm px-3 rounded-pill" href="<?= base_url('auth/login.php') ?>">Sign In</a>
            </li>
            <li class="nav-item">
              <a class="btn btn-primary btn-sm px-3 rounded-pill" href="<?= base_url('auth/register.php') ?>">Register</a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Multi-Model Category Sub-Navigation -->
  <nav class="category-nav d-none d-lg-block">
    <div class="container d-flex align-items-center justify-content-between overflow-x-auto py-1">
      <div class="d-flex gap-2">
        <a class="nav-link <?= empty($_GET['model']) && basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>" href="<?= base_url('index.php') ?>">
          <i class="bi bi-grid-fill me-1 text-primary"></i> All Portals
        </a>
        <a class="nav-link text-primary fw-bold" href="<?= base_url('b2c/index.php') ?>">
          <span class="badge badge-b2c me-1">B2C</span> Retail Shopping
        </a>
        <a class="nav-link text-purple fw-bold" href="<?= base_url('b2b/index.php') ?>" style="color: #7c3aed;">
          <span class="badge badge-b2b me-1">B2B</span> Wholesale & RFQ
        </a>
        <a class="nav-link text-warning-emphasis fw-bold" href="<?= base_url('c2c/index.php') ?>">
          <span class="badge badge-c2c me-1">C2C</span> Used & Pre-Owned
        </a>
        <a class="nav-link text-success fw-bold" href="<?= base_url('c2b/index.php') ?>">
          <span class="badge badge-c2b me-1">C2B</span> Freelance Services
        </a>
      </div>
      <div class="d-flex align-items-center gap-3">
        <a href="<?= base_url('c2c/sell.php') ?>" class="btn btn-sm btn-outline-warning text-dark fw-semibold rounded-pill px-3 py-1">
          <i class="bi bi-tag-fill me-1 text-warning"></i> Sell Used Item
        </a>
        <a href="<?= base_url('b2b/rfq_create.php') ?>" class="btn btn-sm btn-outline-primary fw-semibold rounded-pill px-3 py-1">
          <i class="bi bi-file-earmark-text me-1"></i> Request Quote (RFQ)
        </a>
      </div>
    </div>
  </nav>

  <!-- Global Flash Alert Notification -->
  <div class="container mt-3">
    <?php if ($msg = get_flash('success')): ?>
      <div class="alert alert-success alert-dismissible fade show alert-auto-dismiss shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> <?= e($msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>
    <?php if ($msg = get_flash('error')): ?>
      <div class="alert alert-danger alert-dismissible fade show alert-auto-dismiss shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= e($msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>
    <?php if ($msg = get_flash('info')): ?>
      <div class="alert alert-info alert-dismissible fade show alert-auto-dismiss shadow-sm" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i> <?= e($msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>
  </div>

  <main>
