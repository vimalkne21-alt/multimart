<?php
/**
 * MultiMart - Terms and Conditions
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

$pageTitle = "Terms & Conditions – MultiMart";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5" style="max-width: 850px;">
  <span class="badge bg-secondary-subtle text-secondary fw-bold text-uppercase mb-2">Legal Terms</span>
  <h1 class="display-6 fw-bold mb-4">Terms and Commercial Conditions</h1>

  <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
    <h5 class="fw-bold mb-3">1. Academic Major Project Context</h5>
    <p class="text-secondary small">
      MultiMart is an academic software demonstration created for B.Tech degree evaluation. Products, vendors, quotations, and services featured on this platform serve educational, demonstration, and architectural evaluation purposes.
    </p>

    <h5 class="fw-bold mb-3 mt-4">2. B2B Wholesale & Quotation Obligations</h5>
    <p class="text-secondary small">
      Wholesale inquiries, Minimum Order Quantities (MOQs), and Request for Quotation (RFQ) responses constitute simulated contract biddings. Suppliers must specify warranty periods, bulk lot dimensions, and institutional tax compliance.
    </p>

    <h5 class="fw-bold mb-3 mt-4">3. C2C Peer-to-Peer Marketplace Guidelines</h5>
    <p class="text-secondary small">
      Consumers listing used or pre-owned merchandise must provide accurate condition ratings (Brand New, Like New, Good, Fair) and transparent pricing. Any deceptive listing is subject to community reporting and administrator moderation.
    </p>

    <h5 class="fw-bold mb-3 mt-4">4. C2B Freelancing & Proposal Standards</h5>
    <p class="text-secondary small">
      Freelancers offering technical or design services must uphold accurate skill representations and respect milestone deadlines established in service contracts.
    </p>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
