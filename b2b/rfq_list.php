<?php
/**
 * MultiMart - B2B Quotations Dashboard (Buyer View)
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

require_login();
$pageTitle = "My B2B Quotation Requests (RFQ) – MultiMart";

// Handle Accept / Reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $quoteId = (int)($_POST['quote_id'] ?? 0);
        $action = $_POST['action'] ?? '';

        if ($action === 'accept') {
            db_update('business_quotes', ['status' => 'accepted'], 'id = ? AND buyer_id = ?', [$quoteId, $_SESSION['user_id']]);
            $quote = db_fetch_one("SELECT * FROM business_quotes WHERE id = ?", [$quoteId]);
            send_notification($quote['seller_id'], 'B2B Quotation Accepted by Buyer!', "Quotation {$quote['quote_number']} was accepted by the buyer.", 'seller/rfqs.php', 'quote');
            set_flash('success', "Quotation accepted! The supplier will proceed with contract dispatch.");
        } elseif ($action === 'reject') {
            db_update('business_quotes', ['status' => 'rejected'], 'id = ? AND buyer_id = ?', [$quoteId, $_SESSION['user_id']]);
            set_flash('info', "Quotation rejected.");
        }
    }
}

$quotes = db_fetch_all("
    SELECT q.*, p.title as product_title, u.full_name as seller_name, b.company_name
    FROM business_quotes q
    JOIN products p ON q.product_id = p.id
    JOIN users u ON q.seller_id = u.id
    LEFT JOIN businesses b ON p.business_id = b.id
    WHERE q.buyer_id = ?
    ORDER BY q.id DESC
", [$_SESSION['user_id']]);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h3 class="fw-bold mb-1">My B2B Quotations & RFQs</h3>
      <p class="text-muted small mb-0">Track responses, negotiate volume bids and review supplier proposals.</p>
    </div>
    <a href="<?= base_url('b2b/rfq_create.php') ?>" class="btn btn-warning rounded-pill px-4 fw-bold text-dark shadow-sm">
      <i class="bi bi-plus-circle me-1"></i> New RFQ Request
    </a>
  </div>

  <?php if (empty($quotes)): ?>
    <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
      <i class="bi bi-file-earmark-spreadsheet fs-1 text-muted mb-2"></i>
      <h5 class="fw-bold">No Quotation Requests Found</h5>
      <p class="text-muted small">You haven't requested any bulk wholesale quotations yet.</p>
      <div>
        <a href="<?= base_url('b2b/index.php') ?>" class="btn btn-purple text-white btn-sm rounded-pill px-4" style="background-color: #7c3aed;">
          Explore B2B Wholesale Catalog
        </a>
      </div>
    </div>
  <?php else: ?>
    <div class="row g-4">
      <?php foreach ($quotes as $q): ?>
        <div class="col-12">
          <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom pb-3 mb-3 gap-2">
              <div>
                <span class="badge bg-secondary-subtle text-secondary fw-bold me-2"><?= e($q['quote_number']) ?></span>
                <span class="text-muted small"><?= format_date($q['created_at']) ?></span>
              </div>
              <div>
                <?php
                  $badgeClass = 'bg-secondary';
                  if ($q['status'] === 'responded') $badgeClass = 'bg-info text-dark';
                  elseif ($q['status'] === 'accepted') $badgeClass = 'bg-success';
                  elseif ($q['status'] === 'rejected') $badgeClass = 'bg-danger';
                  elseif ($q['status'] === 'requested') $badgeClass = 'bg-warning text-dark';
                ?>
                <span class="badge <?= $badgeClass ?> px-3 py-2 rounded-pill text-uppercase" style="font-size: 0.72rem;">
                  Status: <?= e($q['status']) ?>
                </span>
              </div>
            </div>

            <div class="row g-3 align-items-center">
              <div class="col-md-5">
                <div class="text-muted small">Product & Supplier:</div>
                <h6 class="fw-bold mb-1"><?= e($q['product_title']) ?></h6>
                <div class="text-secondary small"><i class="bi bi-building me-1"></i> <?= e($q['company_name'] ?: $q['seller_name']) ?></div>
              </div>

              <div class="col-md-2">
                <div class="text-muted small">Requested Qty:</div>
                <div class="fw-bold"><?= $q['quantity'] ?> Units</div>
                <?php if ($q['target_price']): ?>
                  <div class="text-muted small" style="font-size: 0.72rem;">Target: <?= format_currency($q['target_price']) ?></div>
                <?php endif; ?>
              </div>

              <div class="col-md-3">
                <div class="text-muted small">Supplier Quoted Price:</div>
                <?php if ($q['seller_quote_price']): ?>
                  <div class="fs-5 fw-extrabold text-success"><?= format_currency($q['seller_quote_price']) ?></div>
                  <div class="text-muted small" style="font-size: 0.7rem;">Valid Thru: <?= format_date($q['validity_date'], 'd M Y') ?></div>
                <?php else: ?>
                  <div class="text-muted fst-italic small">Awaiting Supplier Response...</div>
                <?php endif; ?>
              </div>

              <div class="col-md-2 text-md-end">
                <?php if ($q['status'] === 'responded'): ?>
                  <form method="POST" action="" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="quote_id" value="<?= $q['id'] ?>">
                    <input type="hidden" name="action" value="accept">
                    <button type="submit" class="btn btn-success btn-sm rounded-pill px-3 fw-bold mb-1">
                      <i class="bi bi-check2 me-1"></i> Accept Quote
                    </button>
                  </form>
                  <form method="POST" action="" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="quote_id" value="<?= $q['id'] ?>">
                    <input type="hidden" name="action" value="reject">
                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-2">Decline</button>
                  </form>
                <?php elseif ($q['status'] === 'accepted'): ?>
                  <span class="badge bg-success-subtle text-success py-2 px-3 rounded-pill fw-semibold">
                    <i class="bi bi-check-circle-fill me-1"></i> Contract Confirmed
                  </span>
                <?php endif; ?>
              </div>
            </div>

            <?php if ($q['seller_notes']): ?>
              <div class="mt-3 p-3 bg-light rounded-3 small border">
                <strong><i class="bi bi-chat-left-quote me-1 text-primary"></i> Supplier Note:</strong> <?= e($q['seller_notes']) ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
