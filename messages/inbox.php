<?php
/**
 * MultiMart - Internal Direct Messaging Center (Inbox)
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

require_login();
$pageTitle = "Internal Messages & Inquiries – MultiMart";
$userId = (int)$_SESSION['user_id'];

// Fetch all conversations where user is sender or receiver
$conversations = db_fetch_all("
    SELECT c.*,
           CASE WHEN c.sender_id = ? THEN u2.full_name ELSE u1.full_name END as partner_name,
           CASE WHEN c.sender_id = ? THEN u2.avatar ELSE u1.avatar END as partner_avatar,
           CASE WHEN c.sender_id = ? THEN u2.id ELSE u1.id END as partner_id,
           (SELECT message_text FROM messages WHERE conversation_id = c.id ORDER BY id DESC LIMIT 1) as last_message,
           (SELECT created_at FROM messages WHERE conversation_id = c.id ORDER BY id DESC LIMIT 1) as last_message_time,
           (SELECT COUNT(*) FROM messages WHERE conversation_id = c.id AND receiver_id = ? AND is_read = 0) as unread_count
    FROM conversations c
    JOIN users u1 ON c.sender_id = u1.id
    JOIN users u2 ON c.receiver_id = u2.id
    WHERE c.sender_id = ? OR c.receiver_id = ?
    ORDER BY c.updated_at DESC
", [$userId, $userId, $userId, $userId, $userId, $userId]);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4" style="max-width: 850px;">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h3 class="fw-bold mb-0"><i class="bi bi-chat-dots-fill text-primary me-2"></i> In-App Messaging Center</h3>
      <p class="text-muted small mb-0">Direct communication with peer sellers, wholesale suppliers, and service providers.</p>
    </div>
  </div>

  <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
    <?php if (empty($conversations)): ?>
      <div class="text-center py-5 text-muted small">
        <i class="bi bi-chat-square-text fs-1 mb-2 d-block text-secondary"></i>
        <h6 class="fw-bold">No Active Conversations</h6>
        <p class="text-muted small">When you inquire about a C2C listing or communicate with a supplier/freelancer, your chats will appear here.</p>
      </div>
    <?php else: ?>
      <div class="list-group list-group-flush">
        <?php foreach ($conversations as $conv): ?>
          <a href="<?= base_url('messages/chat.php?conversation_id=' . $conv['id']) ?>" class="list-group-item list-group-item-action p-3 rounded-3 mb-2 border d-flex align-items-center gap-3">
            <img src="<?= base_url($conv['partner_avatar'] ?: 'assets/images/default-avatar.png') ?>" alt="User" class="rounded-circle border" width="48" height="48">
            <div class="flex-grow-1 overflow-hidden">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <h6 class="fw-bold mb-0 small"><?= e($conv['partner_name']) ?></h6>
                <span class="text-muted" style="font-size: 0.72rem;"><?= format_date($conv['last_message_time'] ?: $conv['created_at']) ?></span>
              </div>
              <div class="text-dark small fw-semibold text-truncate"><?= e($conv['subject']) ?></div>
              <div class="text-secondary small text-truncate"><?= e($conv['last_message'] ?: 'Conversation initiated.') ?></div>
            </div>
            <?php if (($conv['unread_count'] ?? 0) > 0): ?>
              <span class="badge bg-primary rounded-pill small"><?= $conv['unread_count'] ?></span>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
