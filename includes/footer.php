  </main>

  <!-- Global Footer -->
  <footer>
    <div class="container">
      <div class="row g-4">
        <!-- Brand Info -->
        <div class="col-lg-4 col-md-6">
          <div class="d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-shop-window text-primary fs-3"></i>
            <span class="fs-4 fw-bold text-white">MultiMart</span>
            <span class="brand-badge">All-in-One</span>
          </div>
          <p class="text-secondary small mb-3">
            MultiMart is an enterprise-grade multi-vendor, multi-model e-commerce marketplace engineered for high performance, modularity and security. Built as a comprehensive B.Tech CSE / Data Science Academic Major Project.
          </p>
          <div class="alert alert-dark border-secondary p-2 mb-0" style="font-size: 0.75rem;">
            <i class="bi bi-shield-lock-fill text-warning me-1"></i>
            <strong>Simulated Academic Demonstration:</strong> All payments, UPI identifiers, card numbers, and banking transactions on this platform are simulated in Demo Mode.
          </div>
        </div>

        <!-- Four Business Models -->
        <div class="col-lg-2 col-md-6">
          <h6>Business Models</h6>
          <ul class="list-unstyled small d-flex flex-column gap-2">
            <li><a href="<?= base_url('b2c/index.php') ?>"><i class="bi bi-bag-check me-1 text-primary"></i> B2C Retail</a></li>
            <li><a href="<?= base_url('b2b/index.php') ?>"><i class="bi bi-building me-1 text-purple" style="color:#a78bfa;"></i> B2B Wholesale & RFQ</a></li>
            <li><a href="<?= base_url('c2c/index.php') ?>"><i class="bi bi-tag me-1 text-warning"></i> C2C Pre-Owned Market</a></li>
            <li><a href="<?= base_url('c2b/index.php') ?>"><i class="bi bi-briefcase me-1 text-success"></i> C2B Freelancing Desk</a></li>
            <li><a href="<?= base_url('products/coupons.php') ?>"><i class="bi bi-percent me-1 text-info"></i> Discount Coupons</a></li>
          </ul>
        </div>

        <!-- Portals & Dashboards -->
        <div class="col-lg-3 col-md-6">
          <h6>Role Portals</h6>
          <ul class="list-unstyled small d-flex flex-column gap-2">
            <li><a href="<?= base_url('admin/dashboard.php') ?>"><i class="bi bi-speedometer2 me-1 text-danger"></i> Admin Control Panel</a></li>
            <li><a href="<?= base_url('seller/dashboard.php') ?>"><i class="bi bi-shop me-1 text-primary"></i> Seller Portal</a></li>
            <li><a href="<?= base_url('customer/dashboard.php') ?>"><i class="bi bi-person me-1 text-info"></i> Customer Dashboard</a></li>
            <li><a href="<?= base_url('services/dashboard.php') ?>"><i class="bi bi-laptop me-1 text-success"></i> Freelancer Desk</a></li>
            <li><a href="<?= base_url('orders/track.php') ?>"><i class="bi bi-geo-alt me-1 text-warning"></i> Track Any Order</a></li>
          </ul>
        </div>

        <!-- Institutional & Legal -->
        <div class="col-lg-3 col-md-6">
          <h6>Information & Policies</h6>
          <ul class="list-unstyled small d-flex flex-column gap-2">
            <li><a href="<?= base_url('about.php') ?>"><i class="bi bi-info-circle me-1"></i> About MultiMart Project</a></li>
            <li><a href="<?= base_url('contact.php') ?>"><i class="bi bi-envelope me-1"></i> Contact & Support</a></li>
            <li><a href="<?= base_url('help.php') ?>"><i class="bi bi-question-circle me-1"></i> Help & FAQ Center</a></li>
            <li><a href="<?= base_url('privacy.php') ?>"><i class="bi bi-shield-check me-1"></i> Privacy Policy</a></li>
            <li><a href="<?= base_url('terms.php') ?>"><i class="bi bi-file-text me-1"></i> Terms & Conditions</a></li>
            <li><a href="<?= base_url('documentation/README.md') ?>"><i class="bi bi-journal-code me-1 text-warning"></i> Project Documentation</a></li>
          </ul>
        </div>
      </div>

      <!-- Bottom Bar -->
      <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
        <div class="text-secondary">
          &copy; <?= date('Y') ?> MultiMart Inc. Major Project in B.Tech CSE / Data Science. All Rights Reserved.
        </div>
        <div class="d-flex align-items-center gap-3">
          <span class="badge bg-secondary-subtle text-secondary-emphasis border">PHP 8+</span>
          <span class="badge bg-secondary-subtle text-secondary-emphasis border">MySQL / MariaDB</span>
          <span class="badge bg-secondary-subtle text-secondary-emphasis border">Bootstrap 5</span>
          <span class="badge bg-secondary-subtle text-secondary-emphasis border">Vanilla JS</span>
          <span class="badge bg-secondary-subtle text-secondary-emphasis border">PDO Prepared</span>
        </div>
      </div>
    </div>
  </footer>

  <!-- Bootstrap 5.3 Bundle JS with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Chart.js (for Dashboards) -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- Custom Core Script -->
  <script src="<?= base_url('public/js/main.js') ?>"></script>
</body>
</html>
