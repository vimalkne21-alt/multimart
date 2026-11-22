<?php
/**
 * MultiMart - Chronological Direct Chat Interface
 * Academic Major Project: B.Tech CSE / Data Science
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

require_login();
$userId = (int)$_SESSION['user_id'];

$convId = isset($_GET['conversation_id']) ? (int)$_GET['conversation_id'] : 0;
$receiverId = isset($_GET['receiver']) ? (int)$_GET['receiver'] : 0;
$productId = isset($_GET['product']) ? (int)$_GET['product'] : null;
$serviceId = isset($_GET['service']) ? (int)$_GET['service'] : null;

// Resolve or Create Conversation
if ($convId <= 0 && $receiverId > 0 && $receiverId !== $userId) {
    $existing = db_fetch_one("
        SELECT id FROM conversations
        WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
    ", [$userId, $receiverId, $receiverId, $userId]);

    if ($existing) {
        $convId = (int)$existing['id'];
    } else {
        $partner = db_fetch_one("SELECT full_name FROM users WHERE id = ?", [$receiverId]);
        $subject = "Direct discussion with " . ($partner['full_name'] ?? 'User');
        $convId = db_insert('conversations', [
            'sender_id' => $userId,
            'receiver_id' => $receiverId,
            'subject' => $subject,
            'related_type' => $productId ? 'product' : ($serviceId ? 'service' : 'general'),
            'related_id' => $productId ?: $serviceId
        ]);
    }
}

$conversation = db_fetch_one("
    SELECT c.*,
           CASE WHEN c.sender_id = ? THEN u2.full_name ELSE u1.full_name END as partner_name,
           CASE WHEN c.sender_id = ? THEN u2.avatar ELSE u1.avatar END as partner_avatar,
           CASE WHEN c.sender_id = ? THEN u2.id ELSE u1.id END as partner_id
    FROM conversations c
    JOIN users u1 ON c.sender_id = u1.id
    JOIN users u2 ON c.receiver_id = u2.id
    WHERE c.id = ? AND (c.sender_id = ? OR c.receiver_id = ?)
", [$userId, $userId, $userId, $convId, $userId, $userId]);

if (!$conversation) {
    set_flash('error', "Conversation thread not found.");
    header("Location: " . base_url('messages/inbox.php'));
    exit;
}

// Mark incoming messages as read
db_query("UPDATE messages SET is_read = 1 WHERE conversation_id = ? AND sender_id != ?", [$convId, $userId]);

// Handle Message Dispatch
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $messageText = trim(sanitize_input($_POST['message_text'] ?? ''));
        if (!empty($messageText)) {
            db_insert('messages', [
                'conversation_id' => $convId,
                'sender_id' => $userId,
                'message_text' => $messageText,
                'is_read' => 0
            ]);

            db_query("UPDATE conversations SET updated_at = CURRENT_TIMESTAMP WHERE id = ?", [$convId]);

            // Notify partner
            send_notification($conversation['partner_id'], "New Message from " . $_SESSION['user_name'], substr($messageText, 0, 70), "messages/chat.php?conversation_id=$convId", 'message');

            header("Location: " . base_url("messages/chat.php?conversation_id=$convId"));
            exit;
        }
    }
}

// Fetch all messages
$messages = db_fetch_all("
    SELECT m.*, u.full_name as sender_name, u.avatar as sender_avatar
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE m.conversation_id = ?
    ORDER BY m.id ASC
", [$convId]);

$pageTitle = "Chat with " . e($conversation['partner_name']) . " – MultiMart";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4" style="max-width: 800px;">
  <!-- Chat Header -->
  <div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-3 d-flex flex-row align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
      <a href="<?= base_url('messages/inbox.php') ?>" class="btn btn-outline-secondary btn-sm rounded-circle p-2">
        <i class="bi bi-arrow-left"></i>
      </a>
      <img src="<?= base_url($conversation['partner_avatar'] ?: 'assets/images/default-avatar.png') ?>" alt="Partner" class="rounded-circle border" width="45" height="45">
      <div>
        <h6 class="fw-bold mb-0"><?= e($conversation['partner_name']) ?></h6>
        <div class="text-muted small" style="font-size: 0.72rem;"><?= e($conversation['subject']) ?></div>
      </div>
    </div>
    <span class="badge bg-success-subtle text-success small">End-to-End In-App Chat</span>
  </div>

  <!-- Messages Scrollable Body -->
  <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-3">
    <div class="chat-container" id="chatContainer">
      <?php if (empty($messages)): ?>
        <div class="text-center py-5 text-muted small">
          <i class="bi bi-chat-heart fs-1 text-secondary mb-2 d-block"></i> No messages in this thread yet. Say hello!
        </div>
      <?php else: ?>
        <?php foreach ($messages as $msg): 
          $isMe = ($msg['sender_id'] == $userId);
        ?>
          <div class="chat-bubble <?= $isMe ? 'sent' : 'received' ?>">
            <div class="small fw-bold mb-1" style="font-size: 0.72rem; opacity: 0.85;">
              <?= $isMe ? 'You' : e($msg['sender_name']) ?>
            </div>
            <div><?= nl2br(e($msg['message_text'])) ?></div>
            <div class="text-end mt-1" style="font-size: 0.65rem; opacity: 0.75;">
              <?= format_date($msg['created_at'], 'h:i A') ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Message Input Bar -->
    <form method="POST" action="" class="mt-3">
      <?= csrf_field() ?>
      <div class="input-group">
        <input type="text" name="message_text" class="form-control rounded-start-pill px-3" placeholder="Type your message here..." required autocomplete="off">
        <button type="submit" class="btn btn-primary rounded-end-pill px-4 fw-semibold">
          <i class="bi bi-send-fill me-1"></i> Send
        </button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const container = document.getElementById('chatContainer');
  if (container) {
    container.scrollTop = container.scrollHeight;
  }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
