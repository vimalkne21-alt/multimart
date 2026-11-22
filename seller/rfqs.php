<?php
/**
 * MultiMart - Seller B2B Quotations & RFQ Bidding Desk
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

require_role(['business_seller', 'admin']);
$pageTitle = "B2B Quotations & RFQ Requests – Seller Portal";

$sellerId = (int)$_SESSION['user_id'];
$isAdmin = ($_SESSION['user_role_name'] === 'admin');
$where = $isAdmin ? "1=1" : "q.seller_id = $sellerId";

// Handle Quotation Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'respond_quote') {
    if (validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $quoteId = (int)($_POST['quote_id'] ?? 0);
        $quotePrice = (float)($_POST['seller_quote_price'] ?? 0);
        $validityDate = sanitize_input($_POST['validity_date'] ?? date('Y-m-d', strtotime('+30 days')));
        $notes = trim(sanitize_input($_POST['seller_notes'] ?? ''));

        if ($quotePrice > 0) {
            db_update('business_quotes', [
                'seller_quote_price' => $quotePrice,
                'validity_date' => $validityDate,
                'seller_notes' => $notes,
                'status' => 'responded'
            ], "id = ? AND ($where)", [$quoteId]);

            $q = db_fetch_one("SELECT * FROM business_quotes WHERE id = ?", [$quoteId]);
            send_notification($q['buyer_id'], "Quotation Responded: {$q['quote_number']}", "The supplier responded to your RFQ with an offered price of " . format_currency($quotePrice), 'b2b/rfq_list.php', 'quote');
            log_activity('RFQ_RESPONDED', 'QUOTE', $quoteId, "Vendor submitted quotation: " . format_currency($quotePrice));
            set_flash('success', "Quotation proposal submitted to buyer!");
        }
    }
}

$quotes = db_fetch_all("
    SELECT q.*, p.title as product_title, p.price as catalog_price, u.full_name as buyer_name, u.email as buyer_email
    FROM business_quotes q
    JOIN products p ON q.product_id = p.id
    JOIN users u ON q.buyer_id = u.id
    WHERE {$where}
    ORDER BY q.id DESC
");

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <span class="badge bg-purple text-white text-uppercase fw-bold mb-1" style="background-color: #7c3aed;">B2B Wholesale Portal</span>
      <h3 class="fw-bold mb-0">Incoming RFQ Quotation Bids (<?= count($quotes) ?>)</h3>
    </div>
    <a href="<?= base_url('seller/dashboard.php') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
      Workspace
    </a>
  </div>

  <?php if (empty($quotes)): ?>
    <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
      <i class="bi bi-file-earmark-spreadsheet fs-1 text-muted mb-2"></i>
      <h5 class="fw-bold">No RFQ Inquiries at this Time</h5>
      <p class="text-muted small">When business buyers request bulk quotations, they will appear here for your review.</p>
    </div>
  <?php else: ?>
    <div class="row g-4">
      <?php foreach ($quotes as $q): ?>
        <div class="col-12">
          <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom pb-3 mb-3 gap-2">
              <div>
                <span class="badge bg-secondary-subtle text-secondary fw-bold me-2"><?= e($q['quote_number']) ?></span>
                <span class="text-muted small">Submitted: <?= format_date($q['created_at']) ?></span>
              </div>
              <span class="badge <?= $q['status'] === 'responded' ? 'bg-info text-dark' : ($q['status'] === 'accepted' ? 'bg-success' : 'bg-warning text-dark') ?> text-uppercase px-3 py-1">
                <?= e($q['status']) ?>
              </span>
            </div>

            <div class="row g-3">
              <div class="col-md-5">
                <div class="text-muted small">Wholesale Product:</div>
                <h6 class="fw-bold mb-1"><?= e($q['product_title']) ?></h6>
                <div class="text-secondary small">Buyer: <strong><?= e($q['buyer_name']) ?></strong> (<?= e($q['buyer_email']) ?>)</div>
                <div class="mt-2 p-2 bg-light rounded small border">
                  <strong>Buyer Requirements:</strong><br><?= nl2br(e($q['message'])) ?>
                </div>
              </div>

              <div class="col-md-2">
                <div class="text-muted small">Quantity:</div>
                <div class="fs-5 fw-bold"><?= $q['quantity'] ?> Units</div>
                <?php if ($q['target_price']): ?>
                  <div class="text-muted small">Buyer Target: <?= format_currency($q['target_price']) ?></div>
                <?php endif; ?>
              </div>

              <!-- Quote Response Area -->
              <div class="col-md-5">
                <?php if ($q['status'] === 'requested'): ?>
                  <div class="p-3 bg-light rounded-3 border">
                    <h6 class="fw-bold small text-primary mb-2"><i class="bi bi-pencil-square me-1"></i> Submit Vendor Bid Proposal</h6>
                    <form method="POST" action="">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="respond_quote">
                      <input type="hidden" name="quote_id" value="<?= $q['id'] ?>">

                      <div class="row g-2 mb-2">
                        <div class="col-6">
                          <label class="form-label small fw-semibold">Offered Total (₹)</label>
                          <input type="number" name="seller_quote_price" class="form-control form-control-sm" placeholder="e.g. 19500" step="0.01" required>
                        </div>
                        <div class="col-6">
                          <label class="form-label small fw-semibold">Quote Expiry</label>
                          <input type="date" name="validity_date" class="form-control form-control-sm" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
                        </div>
                      </div>

                      <div class="mb-2">
                        <textarea name="seller_notes" class="form-control form-control-sm" rows="2" placeholder="Include warranty terms, logistics inclusion, and tax details..." required></textarea>
                      </div>

                      <button type="submit" class="btn btn-purple text-white btn-sm rounded-pill w-100 fw-semibold" style="background-color: #7c3aed;">
                        Send Commercial Quotation
                      </button>
                    </form>
                  </div>
                <?php else: ?>
                  <div class="p-3 bg-success-subtle rounded-3 border border-success-subtle">
                    <div class="fw-bold text-success small mb-1"><i class="bi bi-check-circle me-1"></i> Quoted Bid Details:</div>
                    <div>Offered Total: <strong class="fs-5"><?= format_currency($q['seller_quote_price']) ?></strong></div>
                    <div class="small text-muted">Valid Until: <?= format_date($q['validity_date'], 'd M Y') ?></div>
                    <?php if ($q['seller_notes']): ?>
                      <div class="small mt-1 text-secondary"><em>"<?= e($q['seller_notes']) ?>"</em></div>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
