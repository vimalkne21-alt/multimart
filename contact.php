<?php
/**
 * MultiMart - Contact & Support Page
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

$pageTitle = "Contact & Helpdesk – MultiMart";
$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $errorMsg = "Security validation failed. Please refresh and try again.";
    } else {
        $name = sanitize_input($_POST['name'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $subject = sanitize_input($_POST['subject'] ?? '');
        $message = sanitize_input($_POST['message'] ?? '');

        if (empty($name) || empty($email) || empty($message)) {
            $errorMsg = "Please fill in all required fields.";
        } else {
            // Log as system message or activity
            log_activity('CONTACT_INQUIRY', 'SUPPORT', null, "Contact form from $name ($email): $subject");
            $successMsg = "Thank you, $name! Your inquiry has been logged. Our academic support simulation team will review it shortly.";
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
  <div class="row g-5">
    <div class="col-lg-6">
      <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase mb-2">Get in Touch</span>
      <h1 class="display-6 fw-bold mb-3">MultiMart Support & Developer Desk</h1>
      <p class="text-muted mb-4">
        Have questions regarding the multi-model architecture, demo accounts, or local environment setup? Fill out the form or reach our academic coordinators.
      </p>

      <div class="d-flex flex-column gap-3 mb-4">
        <div class="d-flex align-items-center gap-3 p-3 bg-white border rounded-3 shadow-sm">
          <div class="fs-3 text-primary"><i class="bi bi-geo-alt"></i></div>
          <div>
            <div class="fw-bold">Campus / Project Lab</div>
            <div class="text-muted small">Department of Computer Science & Engineering, Academic Block IV</div>
          </div>
        </div>

        <div class="d-flex align-items-center gap-3 p-3 bg-white border rounded-3 shadow-sm">
          <div class="fs-3 text-success"><i class="bi bi-envelope"></i></div>
          <div>
            <div class="fw-bold">Official Inquiries</div>
            <div class="text-muted small">support@multimart.com / coordinator@multimart.edu</div>
          </div>
        </div>

        <div class="d-flex align-items-center gap-3 p-3 bg-white border rounded-3 shadow-sm">
          <div class="fs-3 text-warning"><i class="bi bi-telephone"></i></div>
          <div>
            <div class="fw-bold">Toll-Free Support Line</div>
            <div class="text-muted small">+91 1800 555 MULTI (Simulated Helpline)</div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card border-0 shadow rounded-4 p-4 p-lg-5 bg-white">
        <h4 class="fw-bold mb-4"><i class="bi bi-chat-left-dots text-primary me-2"></i> Send us a Message</h4>

        <?php if (!empty($successMsg)): ?>
          <div class="alert alert-success"><i class="bi bi-check-circle me-1"></i> <?= e($successMsg) ?></div>
        <?php endif; ?>

        <?php if (!empty($errorMsg)): ?>
          <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-1"></i> <?= e($errorMsg) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Your Full Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" placeholder="Priya Sharma" required value="<?= e($currentUser['full_name'] ?? '') ?>">
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Email Address <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" placeholder="student@university.edu" required value="<?= e($currentUser['email'] ?? '') ?>">
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Subject / Business Model</label>
            <select name="subject" class="form-select">
              <option value="General Technical Question">General Technical Question</option>
              <option value="B2B Wholesale Inquiry">B2B Wholesale & RFQ Inquiry</option>
              <option value="C2C Listing Moderation">C2C Pre-Owned Listing Question</option>
              <option value="C2B Freelancer Profile">C2B Freelancer Collaboration</option>
              <option value="Academic Project Evaluation">Academic Project Evaluation Inquiry</option>
            </select>
          </div>

          <div class="mb-4">
            <label class="form-label small fw-semibold">Your Message <span class="text-danger">*</span></label>
            <textarea name="message" class="form-control" rows="4" placeholder="Detail your query or feedback here..." required></textarea>
          </div>

          <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill fw-semibold shadow-sm">
            <i class="bi bi-send-fill me-1"></i> Submit Message
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
