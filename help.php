<?php
/**
 * MultiMart - Help & FAQ Center
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

$pageTitle = "Help & FAQ Center – MultiMart";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5" style="max-width: 900px;">
  <div class="text-center mb-5">
    <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase mb-2">Knowledge Base</span>
    <h1 class="display-6 fw-bold">Frequently Asked Questions & Help Desk</h1>
    <p class="text-muted">Everything you need to understand MultiMart's architecture, 4 business models, and demo operations.</p>
  </div>

  <div class="accordion shadow-sm rounded-4 overflow-hidden" id="faqAccordion">
    <!-- Item 1 -->
    <div class="accordion-item border-0 border-bottom">
      <h2 class="accordion-header">
        <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
          1. What are the four e-commerce business models supported by MultiMart?
        </button>
      </h2>
      <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
        <div class="accordion-body text-secondary small">
          MultiMart implements all four principal e-commerce archetypes:
          <ul>
            <li><strong>B2B (Business-to-Business):</strong> Bulk wholesale supply with Minimum Order Quantities (MOQ), volume discounts, and Request for Quotation (RFQ) bidding.</li>
            <li><strong>B2C (Business-to-Consumer):</strong> Direct retail shopping with inventory management, cart, coupons, and customer tracking.</li>
            <li><strong>C2C (Consumer-to-Consumer):</strong> Peer-to-peer pre-owned marketplace with condition grading and direct consumer messaging.</li>
            <li><strong>C2B (Consumer-to-Business):</strong> Freelance marketplace where skilled professionals offer technical services to enterprise clients.</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Item 2 -->
    <div class="accordion-item border-0 border-bottom">
      <h2 class="accordion-header">
        <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
          2. How does the simulated payment gateway work? Are real bank cards charged?
        </button>
      </h2>
      <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
        <div class="accordion-body text-secondary small">
          <strong>No real banking transactions are executed.</strong> MultiMart implements an academic-grade simulated gateway. All UPI identifiers, card numbers, and banking details are simulated test identifiers. The payment processor simulates latency, authorization code generation, ledger transaction records, and order fulfillment updates safely without collecting sensitive credentials.
        </div>
      </div>
    </div>

    <!-- Item 3 -->
    <div class="accordion-item border-0 border-bottom">
      <h2 class="accordion-header">
        <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
          3. What demo accounts can be used to test different role dashboards?
        </button>
      </h2>
      <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
        <div class="accordion-body text-secondary small">
          <div class="table-responsive">
            <table class="table table-sm table-bordered">
              <thead class="table-light">
                <tr><th>Role</th><th>Email</th><th>Password</th><th>Dashboard Access</th></tr>
              </thead>
              <tbody>
                <tr><td>Admin</td><td><code>admin@multimart.com</code></td><td><code>Admin@123</code></td><td><code>/admin/dashboard.php</code></td></tr>
                <tr><td>Business Seller</td><td><code>apex.supplies@multimart.com</code></td><td><code>Seller@123</code></td><td><code>/seller/dashboard.php</code></td></tr>
                <tr><td>Individual Seller</td><td><code>john.doe@multimart.com</code></td><td><code>User@123</code></td><td><code>/c2c/index.php</code></td></tr>
                <tr><td>Customer</td><td><code>priya.sharma@multimart.com</code></td><td><code>Customer@123</code></td><td><code>/customer/dashboard.php</code></td></tr>
                <tr><td>Freelancer</td><td><code>rahul.tech@multimart.com</code></td><td><code>Freelance@123</code></td><td><code>/services/dashboard.php</code></td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Item 4 -->
    <div class="accordion-item border-0 border-bottom">
      <h2 class="accordion-header">
        <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
          4. How does the B2B Request for Quotation (RFQ) cycle operate?
        </button>
      </h2>
      <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
        <div class="accordion-body text-secondary small">
          A registered buyer identifies a wholesale product, inputs required quantities and target price, and submits an RFQ. The vendor receives a high-priority notification in their Seller Dashboard, calculates customized unit tax and logistics pricing, and responds with a legally binding digital quote valid until an expiry date. The buyer can review and convert accepted quotes directly into orders.
        </div>
      </div>
    </div>

    <!-- Item 5 -->
    <div class="accordion-item border-0">
      <h2 class="accordion-header">
        <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
          5. Is the database design fully normalized?
        </button>
      </h2>
      <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
        <div class="accordion-body text-secondary small">
          Yes. MultiMart's database features 30 distinct normalized tables compliant with Third Normal Form (3NF), containing foreign key constraints with cascade/restrict rules, structured timestamps, and comprehensive audit logs.
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
