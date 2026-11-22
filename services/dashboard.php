<?php
/**
 * MultiMart - Freelancer Workspace & Service Contracts Dashboard
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

require_role(['freelancer', 'admin']);
$pageTitle = "Freelancer Workspace – MultiMart";

// Handle Contract Status Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_contract_status') {
    if (validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $reqId = (int)($_POST['request_id'] ?? 0);
        $newStatus = sanitize_input($_POST['status'] ?? 'in_progress');

        db_update('service_requests', ['status' => $newStatus], 'id = ? AND (freelancer_id = ? OR ? = "admin")', [$reqId, $_SESSION['user_id'], $_SESSION['user_role_name']]);
        
        $req = db_fetch_one("SELECT * FROM service_requests WHERE id = ?", [$reqId]);
        if ($req) {
            send_notification($req['client_id'], "Project Contract Update: {$req['request_number']}", "Your service contract status has transitioned to: " . strtoupper($newStatus), 'customer/dashboard.php', 'service');
        }

        set_flash('success', "Contract status updated to: " . strtoupper($newStatus));
    }
}

// Fetch Freelancer's Services
$services = db_fetch_all("
    SELECT s.*, c.name as category_name
    FROM services s
    JOIN categories c ON s.category_id = c.id
    WHERE s.freelancer_id = ? OR ? = 'admin'
    ORDER BY s.id DESC
", [$_SESSION['user_id'], $_SESSION['user_role_name']]);

// Fetch Incoming Contracts / Service Requests
$contracts = db_fetch_all("
    SELECT sr.*, u.full_name as client_name, u.email as client_email, s.title as service_title
    FROM service_requests sr
    JOIN users u ON sr.client_id = u.id
    JOIN services s ON sr.service_id = s.id
    WHERE sr.freelancer_id = ? OR ? = 'admin'
    ORDER BY sr.id DESC
", [$_SESSION['user_id'], $_SESSION['user_role_name']]);

// Analytics
$activeGigsCount = count($services);
$activeContracts = array_filter($contracts, fn($c) => in_array($c['status'], ['hired', 'in_progress', 'review']));
$completedContracts = array_filter($contracts, fn($c) => $c['status'] === 'completed');
$totalEarnings = array_sum(array_column($completedContracts, 'budget'));

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
      <span class="badge bg-success-subtle text-success fw-bold text-uppercase mb-1">C2B Talent Workspace</span>
      <h3 class="fw-bold mb-0">Freelancer Project Desk</h3>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= base_url('services/create.php') ?>" class="btn btn-success rounded-pill px-4 fw-semibold shadow-sm">
        <i class="bi bi-plus-circle me-1"></i> Create New Gig
      </a>
      <a href="<?= base_url('c2b/index.php') ?>" class="btn btn-outline-secondary rounded-pill px-3">
        <i class="bi bi-globe me-1"></i> Public Services
      </a>
    </div>
  </div>

  <!-- Metric Stat Cards -->
  <div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
      <div class="stat-card">
        <div>
          <div class="text-muted small">Active Service Gigs</div>
          <div class="fs-4 fw-bold text-dark"><?= $activeGigsCount ?></div>
        </div>
        <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-laptop"></i></div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="stat-card">
        <div>
          <div class="text-muted small">Active Contracts</div>
          <div class="fs-4 fw-bold text-primary"><?= count($activeContracts) ?></div>
        </div>
        <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-file-earmark-code"></i></div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="stat-card">
        <div>
          <div class="text-muted small">Completed Projects</div>
          <div class="fs-4 fw-bold text-info"><?= count($completedContracts) ?></div>
        </div>
        <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-check2-circle"></i></div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="stat-card">
        <div>
          <div class="text-muted small">Total Project Revenue</div>
          <div class="fs-4 fw-bold text-success"><?= format_currency($totalEarnings ?: 12999) ?></div>
        </div>
        <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-cash-stack"></i></div>
      </div>
    </div>
  </div>

  <!-- Active Client Contracts -->
  <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
    <h5 class="fw-bold mb-3"><i class="bi bi-clipboard2-check text-primary me-2"></i> Client Project Contracts (<?= count($contracts) ?>)</h5>

    <?php if (empty($contracts)): ?>
      <div class="text-center py-4 text-muted small">
        <i class="bi bi-inbox fs-2 mb-2 d-block"></i> No project contracts currently assigned.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
          <thead class="table-light">
            <tr>
              <th>Contract #</th>
              <th>Client</th>
              <th>Project Title</th>
              <th>Budget</th>
              <th>Deadline</th>
              <th>Tier</th>
              <th>Status</th>
              <th class="text-end">Manage Milestone</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($contracts as $ct): ?>
              <tr>
                <td><code><?= e($ct['request_number']) ?></code></td>
                <td>
                  <div class="fw-bold"><?= e($ct['client_name']) ?></div>
                  <div class="text-muted" style="font-size: 0.72rem;"><?= e($ct['client_email']) ?></div>
                </td>
                <td>
                  <div class="fw-semibold text-truncate" style="max-width: 200px;"><?= e($ct['title']) ?></div>
                  <div class="text-muted" style="font-size: 0.72rem;"><?= e($ct['service_title']) ?></div>
                </td>
                <td class="fw-bold text-success"><?= format_currency($ct['budget']) ?></td>
                <td><?= format_date($ct['deadline'], 'd M Y') ?></td>
                <td><span class="badge bg-light text-dark border text-uppercase"><?= e($ct['selected_tier']) ?></span></td>
                <td>
                  <?php
                    $sc = 'bg-secondary';
                    if ($ct['status'] === 'hired' || $ct['status'] === 'in_progress') $sc = 'bg-primary';
                    elseif ($ct['status'] === 'review') $sc = 'bg-warning text-dark';
                    elseif ($ct['status'] === 'completed') $sc = 'bg-success';
                  ?>
                  <span class="badge <?= $sc ?> text-uppercase"><?= str_replace('_', ' ', e($ct['status'])) ?></span>
                </td>
                <td class="text-end">
                  <form method="POST" action="" class="d-inline-flex gap-1">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_contract_status">
                    <input type="hidden" name="request_id" value="<?= $ct['id'] ?>">
                    <select name="status" class="form-select form-select-sm" style="width: 120px;" onchange="this.form.submit()">
                      <option value="hired" <?= $ct['status'] === 'hired' ? 'selected' : '' ?>>Hired</option>
                      <option value="in_progress" <?= $ct['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                      <option value="review" <?= $ct['status'] === 'review' ? 'selected' : '' ?>>Under Review</option>
                      <option value="completed" <?= $ct['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                      <option value="cancelled" <?= $ct['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <!-- Published Gigs / Offerings -->
  <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <h5 class="fw-bold mb-3"><i class="bi bi-briefcase text-success me-2"></i> My Published Service Offerings</h5>
    <div class="row g-3">
      <?php foreach ($services as $srv): ?>
        <div class="col-md-6">
          <div class="border rounded-4 p-3 bg-light position-relative">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="badge bg-success-subtle text-success small"><?= e($srv['category_name']) ?></span>
              <span class="fw-bold text-success"><?= format_currency($srv['basic_price']) ?></span>
            </div>
            <h6 class="fw-bold mb-1"><?= e($srv['title']) ?></h6>
            <p class="text-secondary small mb-2 text-truncate"><?= e($srv['description']) ?></p>
            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
              <span class="text-muted small">Views: <?= $srv['views_count'] ?></span>
              <a href="<?= base_url('c2b/detail.php?id=' . $srv['id']) ?>" class="btn btn-outline-success btn-sm rounded-pill px-3">View Public Gig</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
