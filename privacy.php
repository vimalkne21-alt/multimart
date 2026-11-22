<?php
/**
 * MultiMart - Privacy Policy & Data Security
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

$pageTitle = "Privacy Policy & Cyber Security Standards – MultiMart";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5" style="max-width: 850px;">
  <span class="badge bg-success-subtle text-success fw-bold text-uppercase mb-2">Data Protection Standard</span>
  <h1 class="display-6 fw-bold mb-4">Privacy Policy & Cybersecurity Controls</h1>
  
  <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
    <h5 class="fw-bold text-primary mb-3"><i class="bi bi-shield-lock me-2"></i> 1. Security Architecture & Hashing</h5>
    <p class="text-secondary small">
      MultiMart enforces password hashing via PHP's native <code>password_hash()</code> using the bcrypt algorithm with a work factor cost of 10. User passwords are never retained, logged, or serialized in cleartext anywhere in storage or memory.
    </p>

    <h5 class="fw-bold text-primary mb-3 mt-4"><i class="bi bi-shield-check me-2"></i> 2. SQL Injection & XSS Defense</h5>
    <p class="text-secondary small">
      All persistent data queries are routed strictly through PHP Data Objects (PDO) with prepared statements and parameter binding. Output rendering uniformly applies <code>htmlspecialchars()</code> with <code>ENT_QUOTES, 'UTF-8'</code> to prevent Cross-Site Scripting vulnerabilities.
    </p>

    <h5 class="fw-bold text-primary mb-3 mt-4"><i class="bi bi-safe me-2"></i> 3. Role-Based Access Isolation</h5>
    <p class="text-secondary small">
      Individual sellers, business vendors, and consumers are strictly constrained to their designated data domains. Server-side middleware verifies <code>$_SESSION['user_role_name']</code> before granting access to sensitive portal endpoints.
    </p>

    <h5 class="fw-bold text-primary mb-3 mt-4"><i class="bi bi-credit-card me-2"></i> 4. Simulated Financial Safety</h5>
    <p class="text-secondary small">
      As an academic demonstration platform, MultiMart explicitly prohibits the collection or storage of genuine banking account numbers, CVVs, debit/credit PINs, or OTPs. All payment transactions represent safe cryptographic simulations.
    </p>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
