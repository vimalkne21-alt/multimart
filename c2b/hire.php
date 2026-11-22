<?php
/**
 * MultiMart - C2B Client Hire & Service Contract Initiation
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

require_login();

$serviceId = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;
$tier = $_GET['tier'] ?? 'standard';

$service = db_fetch_one("
    SELECT s.*, u.full_name as freelancer_name 
    FROM services s 
    JOIN users u ON s.freelancer_id = u.id 
    WHERE s.id = ?
", [$serviceId]);

if (!$service) {
    header("Location: " . base_url('c2b/index.php'));
    exit;
}

$pageTitle = "Hire " . e($service['freelancer_name']) . " – MultiMart";

$baseBudget = $service['standard_price'];
$deliveryDays = $service['delivery_days'] + 3;

if ($tier === 'basic') {
    $baseBudget = $service['basic_price'];
    $deliveryDays = $service['delivery_days'];
} elseif ($tier === 'premium') {
    $baseBudget = $service['premium_price'];
    $deliveryDays = $service['delivery_days'] + 7;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Security validation failed.";
    } else {
        $title = trim(sanitize_input($_POST['title'] ?? ''));
        $requirements = trim(sanitize_input($_POST['requirements'] ?? ''));
        $budget = (float)($_POST['budget'] ?? $baseBudget);
        $deadline = sanitize_input($_POST['deadline'] ?? date('Y-m-d', strtotime("+$deliveryDays days")));

        if (empty($title) || empty($requirements) || $budget <= 0) {
            $error = "Please provide project title, specifications and budget.";
        } else {
            $reqNumber = 'REQ-2026-SRV-' . rand(1000, 9999);
            $reqId = db_insert('service_requests', [
                'request_number' => $reqNumber,
                'client_id' => $_SESSION['user_id'],
                'freelancer_id' => $service['freelancer_id'],
                'service_id' => $serviceId,
                'title' => $title,
                'requirements' => $requirements,
                'budget' => $budget,
                'deadline' => $deadline,
                'selected_tier' => $tier,
                'status' => 'hired'
            ]);

            // Auto-generate accepted proposal for milestone tracking
            db_insert('proposals', [
                'service_request_id' => $reqId,
                'freelancer_id' => $service['freelancer_id'],
                'cover_letter' => 'Client selected pre-packaged ' . ucfirst($tier) . ' tier milestone specification.',
                'proposed_amount' => $budget,
                'delivery_days' => $deliveryDays,
                'status' => 'accepted'
            ]);

            send_notification($service['freelancer_id'], "New Freelance Contract Hired: $reqNumber", "You have been hired for \"$title\" ($tier tier, " . format_currency($budget) . ").", 'services/dashboard.php', 'service');

            log_activity('SERVICE_HIRED', 'SERVICE_REQUEST', $reqId, "Hired freelancer #{$service['freelancer_id']} for $reqNumber");
            set_flash('success', "Service contract #$reqNumber established! The freelancer has been notified and will commence project work.");
            header("Location: " . base_url('customer/dashboard.php'));
            exit;
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5" style="max-width: 700px;">
  <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
    <div class="text-center mb-4">
      <span class="badge bg-success text-white px-3 py-1 rounded-pill fw-bold text-uppercase mb-2">
        <i class="bi bi-briefcase me-1"></i> Freelance Contract
      </span>
      <h3 class="fw-bold">Hire Specialist Talent</h3>
      <p class="text-muted small">Contracting <strong><?= e($service['freelancer_name']) ?></strong> for <em><?= e($service['title']) ?></em></p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger py-2 small shadow-sm"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <?= csrf_field() ?>

      <div class="p-3 bg-light rounded-3 mb-3 border">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="text-muted small">Selected Package Tier:</div>
            <div class="fw-bold text-success text-uppercase"><?= e($tier) ?> Tier</div>
          </div>
          <div class="text-end">
            <div class="text-muted small">Standard Cost:</div>
            <div class="fw-bold text-dark fs-5"><?= format_currency($baseBudget) ?></div>
          </div>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label small fw-semibold">Project Title <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control" placeholder="e.g. Demand Forecasting Time-Series Script for Supply Chain" required value="<?= e($_POST['title'] ?? '') ?>">
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label small fw-semibold">Contract Budget (₹) <span class="text-danger">*</span></label>
          <input type="number" name="budget" class="form-control" value="<?= $baseBudget ?>" step="0.01" required>
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-semibold">Target Completion Deadline <span class="text-danger">*</span></label>
          <input type="date" name="deadline" class="form-control" value="<?= date('Y-m-d', strtotime("+$deliveryDays days")) ?>" required>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label small fw-semibold">Detailed Project Specifications & Deliverables <span class="text-danger">*</span></label>
        <textarea name="requirements" class="form-control" rows="4" placeholder="Detail your exact software architecture, data input format, expected APIs or notebook deliverables..." required><?= e($_POST['requirements'] ?? '') ?></textarea>
      </div>

      <button type="submit" class="btn btn-success w-100 py-2 rounded-pill fw-bold shadow-sm mb-3">
        <i class="bi bi-check2-circle me-1"></i> Confirm & Establish Contract
      </button>

      <div class="text-center small">
        <a href="<?= base_url('c2b/detail.php?id=' . $serviceId) ?>" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Service Packages</a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
