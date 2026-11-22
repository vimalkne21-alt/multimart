<?php
/**
 * MultiMart - About Us Page
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

$pageTitle = "About MultiMart – Academic Major Project Overview";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
  <div class="row align-items-center mb-5">
    <div class="col-lg-7">
      <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase mb-2">Academic Engineering Portfolio</span>
      <h1 class="display-6 fw-bold mb-3">About MultiMart E-Commerce Marketplace</h1>
      <p class="lead text-muted">
        MultiMart was conceptualized and engineered as a comprehensive capstone major project for B.Tech Computer Science & Engineering / Data Science.
      </p>
      <p class="text-secondary">
        Contemporary online commerce systems typically segregate consumers, enterprises, peer-to-peer sellers, and service providers into disjoint silos. MultiMart pioneers an integrated architectural blueprint that harmonizes all four principal e-commerce business models—<strong>B2B, B2C, C2C, and C2B</strong>—within a unified normalized database and single-sign-on authentication framework.
      </p>
    </div>
    <div class="col-lg-5">
      <div class="card border-0 shadow-lg rounded-4 p-4 bg-primary text-white">
        <h4 class="fw-bold mb-3"><i class="bi bi-mortarboard-fill me-2"></i> Project Metadata</h4>
        <ul class="list-unstyled d-flex flex-column gap-2 small">
          <li><strong>Academic Program:</strong> Bachelor of Technology (B.Tech)</li>
          <li><strong>Specialization:</strong> CSE / Data Science & Software Engineering</li>
          <li><strong>Core Stack:</strong> PHP 8+, MySQL / MariaDB, Bootstrap 5, Vanilla JS</li>
          <li><strong>Architecture:</strong> Modular MVC Pattern, PDO Prepared Statements</li>
          <li><strong>Database Normalization:</strong> 3NF (Third Normal Form) across 30 tables</li>
          <li><strong>Payment Handling:</strong> Simulated Safe Gateway with Zero Real Credentials</li>
        </ul>
      </div>
    </div>
  </div>

  <div class="row g-4 mb-5">
    <div class="col-md-3">
      <div class="card h-100 border rounded-4 p-4 text-center">
        <div class="fs-1 text-primary mb-2"><i class="bi bi-cart4"></i></div>
        <h5 class="fw-bold">B2C Retail</h5>
        <p class="text-muted small mb-0">Consumer catalog with full-text search, persistent shopping cart, coupon discounts and simulated checkout.</p>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card h-100 border rounded-4 p-4 text-center">
        <div class="fs-1 text-purple mb-2" style="color: #7c3aed;"><i class="bi bi-building"></i></div>
        <h5 class="fw-bold">B2B Wholesale</h5>
        <p class="text-muted small mb-0">Enterprise portal with Minimum Order Quantities, wholesale tiered pricing, and formal Request for Quotation (RFQ).</p>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card h-100 border rounded-4 p-4 text-center">
        <div class="fs-1 text-warning mb-2"><i class="bi bi-arrow-repeat"></i></div>
        <h5 class="fw-bold">C2C Pre-Owned</h5>
        <p class="text-muted small mb-0">Peer-to-peer consumer listings with item condition ratings, location metadata, listing moderation, and buyer chat.</p>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card h-100 border rounded-4 p-4 text-center">
        <div class="fs-1 text-success mb-2"><i class="bi bi-laptop"></i></div>
        <h5 class="fw-bold">C2B Freelancing</h5>
        <p class="text-muted small mb-0">Specialist skills marketplace enabling businesses to contract independent freelancers for custom projects.</p>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
