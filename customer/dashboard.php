<?php
/**
 * MultiMart - Customer Hub & Personal Dashboard
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

require_login();
$pageTitle = "My Customer Hub – MultiMart";
$userId = (int)$_SESSION['user_id'];

// Metrics
$totalOrders = (int)(db_fetch_one("SELECT COUNT(*) as cnt FROM orders WHERE user_id = ?", [$userId])['cnt'] ?? 0);

$wlRow = db_fetch_one("SELECT COUNT(*) as cnt FROM wishlist_items wi JOIN wishlists w ON wi.wishlist_id = w.id WHERE w.user_id = ?", [$userId]);
$totalWishlist = (int)($wlRow['cnt'] ?? 0);

$totalRfqs = (int)(db_fetch_one("SELECT COUNT(*) as cnt FROM business_quotes WHERE buyer_id = ?", [$userId])['cnt'] ?? 0);

$notifRow = db_fetch_one("SELECT COUNT(*) as cnt FROM notifications WHERE user_id = ? AND is_read = 0", [$userId]);
$unreadNotifs = (int)($notifRow['cnt'] ?? 0);

// Recent Orders
$recentOrders = db_fetch_all("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC LIMIT 5", [$userId]);

// Active C2B Service Requests
$serviceContracts = db_fetch_all("
    SELECT sr.*, s.title as service_title, u.full_name as freelancer_name
    FROM service_requests sr
    JOIN services s ON sr.service_id = s.id
    JOIN users u ON sr.freelancer_id = u.id
    WHERE sr.client_id = ?
    ORDER BY sr.id DESC
", [$userId]);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
  <!-- Header Banner -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
      <span class="badge bg-primary text-uppercase fw-bold mb-1">Customer Command Center</span>
      <h3 class="fw-bold mb-0">Welcome back, <?= e($_SESSION['user_name']) ?>!</h3>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= base_url('b2c/index.php') ?>" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
        <i class="bi bi-cart3 me-1"></i> Shop Marketplace
      </a>
      <a href="<?= base_url('auth/profile.php') ?>" class="btn btn-outline-secondary rounded-pill px-3">
        <i class="bi bi-gear me-1"></i> Settings
      </a>
    </div>
  </div>

  <!-- Metric Stat Cards -->
  <div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
      <div class="stat-card">
        <div>
          <div class="text-muted small">My Orders Placed</div>
          <div class="fs-4 fw-bold text-dark"><?= $totalOrders ?></div>
        </div>
        <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-box-seam"></i></div>
      </div>
    </div>

    <div class="col-sm-6 col-lg-3">
      <div class="stat-card">
        <div>
          <div class="text-muted small">Saved in Wishlist</div>
          <div class="fs-4 fw-bold text-danger"><?= $totalWishlist ?></div>
        </div>
        <div class="stat-icon bg-danger-subtle text-danger"><i class="bi bi-heart"></i></div>
      </div>
    </div>

    <div class="col-sm-6 col-lg-3">
      <div class="stat-card">
        <div>
          <div class="text-muted small">B2B Quotations (RFQ)</div>
          <div class="fs-4 fw-bold text-purple" style="color: #7c3aed;"><?= $totalRfqs ?></div>
        </div>
        <div class="stat-icon bg-purple-subtle text-purple" style="background-color: #f3e8ff; color: #7c3aed;"><i class="bi bi-file-earmark-text"></i></div>
      </div>
    </div>

    <div class="col-sm-6 col-lg-3">
      <div class="stat-card">
        <div>
          <div class="text-muted small">Unread Alerts</div>
          <div class="fs-4 fw-bold text-warning"><?= $unreadNotifs ?></div>
        </div>
        <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-bell"></i></div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <!-- Recent Orders -->
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="fw-bold mb-0"><i class="bi bi-clock-history text-primary me-2"></i> My Recent Orders</h5>
          <a href="<?= base_url('customer/orders.php') ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">View Full History</a>
        </div>

        <?php if (empty($recentOrders)): ?>
          <div class="text-center py-4 text-muted small">
            <i class="bi bi-cart-x fs-2 mb-2 d-block"></i> No purchase orders placed yet.
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle small mb-0">
              <thead class="table-light">
                <tr>
                  <th>Order #</th>
                  <th>Amount</th>
                  <th>Payment</th>
                  <th>Status</th>
                  <th>Date</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentOrders as $ro): ?>
                  <tr>
                    <td><code><?= e($ro['order_number']) ?></code></td>
                    <td class="fw-bold text-dark"><?= format_currency($ro['final_amount']) ?></td>
                    <td><span class="badge bg-success-subtle text-success"><?= e($ro['payment_status']) ?></span></td>
                    <td>
                      <span class="badge <?= $ro['order_status'] === 'Delivered' ? 'bg-success' : 'bg-primary' ?> text-uppercase">
                        <?= e($ro['order_status']) ?>
                      </span>
                    </td>
                    <td><?= format_date($ro['created_at'], 'd M Y') ?></td>
                    <td class="text-end">
                      <a href="<?= base_url('orders/track.php?order=' . urlencode($ro['order_number'])) ?>" class="btn btn-outline-primary btn-sm rounded-pill px-2">
                        <i class="bi bi-geo-alt"></i> Track
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <!-- Active C2B Freelance Contracts -->
      <?php if (!empty($serviceContracts)): ?>
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
          <h5 class="fw-bold mb-3"><i class="bi bi-briefcase text-success me-2"></i> Hired Freelance Service Contracts</h5>
          <div class="d-flex flex-column gap-3">
            <?php foreach ($serviceContracts as $sc): ?>
              <div class="border rounded-3 p-3 d-flex justify-content-between align-items-center bg-light">
                <div>
                  <div class="fw-bold"><?= e($sc['title']) ?></div>
                  <div class="text-muted small">Specialist: <strong><?= e($sc['freelancer_name']) ?></strong> | Tier: <span class="badge bg-light text-dark border text-uppercase"><?= e($sc['selected_tier']) ?></span></div>
                  <div class="text-muted small">Deadline: <?= format_date($sc['deadline'], 'd M Y') ?></div>
                </div>
                <div class="text-end">
                  <div class="fw-bold text-success fs-5"><?= format_currency($sc['budget']) ?></div>
                  <span class="badge bg-primary text-uppercase"><?= str_replace('_', ' ', e($sc['status'])) ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- Right Quick Links & Notifications -->
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
        <h5 class="fw-bold mb-3">Quick Navigation</h5>
        <div class="d-flex flex-column gap-2 small">
          <a href="<?= base_url('customer/orders.php') ?>" class="p-2 rounded-3 bg-light text-dark text-decoration-none d-flex justify-content-between align-items-center">
            <span><i class="bi bi-box-seam me-2 text-primary"></i> All Order History</span>
            <i class="bi bi-chevron-right text-muted"></i>
          </a>
          <a href="<?= base_url('customer/wishlist.php') ?>" class="p-2 rounded-3 bg-light text-dark text-decoration-none d-flex justify-content-between align-items-center">
            <span><i class="bi bi-heart me-2 text-danger"></i> My Wishlist</span>
            <i class="bi bi-chevron-right text-muted"></i>
          </a>
          <a href="<?= base_url('b2b/rfq_list.php') ?>" class="p-2 rounded-3 bg-light text-dark text-decoration-none d-flex justify-content-between align-items-center">
            <span><i class="bi bi-file-earmark-text me-2 text-purple" style="color:#7c3aed;"></i> B2B Quotations</span>
            <i class="bi bi-chevron-right text-muted"></i>
          </a>
          <a href="<?= base_url('messages/inbox.php') ?>" class="p-2 rounded-3 bg-light text-dark text-decoration-none d-flex justify-content-between align-items-center">
            <span><i class="bi bi-chat-dots me-2 text-warning"></i> Internal Messages</span>
            <i class="bi bi-chevron-right text-muted"></i>
          </a>
          <a href="<?= base_url('auth/profile.php') ?>" class="p-2 rounded-3 bg-light text-dark text-decoration-none d-flex justify-content-between align-items-center">
            <span><i class="bi bi-geo-alt me-2 text-success"></i> Manage Addresses</span>
            <i class="bi bi-chevron-right text-muted"></i>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
