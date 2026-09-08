<?php
require_once '../config/database.php';
require_once '../config/auth.php';

checkRole(2);

$user_id = (int)$_SESSION['user_id'];
$message_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$message_id) {
    header('Location: messages.php');
    exit;
}

// Fetch message details
$stmt = $pdo->prepare("
    SELECT m.*, 
           sender.name as sender_name, sender.role_id as sender_role,
           receiver.name as receiver_name
    FROM messages m
    JOIN users sender ON sender.id = m.sender_id
    JOIN users receiver ON receiver.id = m.receiver_id
    WHERE m.id = ? AND (m.sender_id = ? OR m.receiver_id = ?)
");
$stmt->execute([$message_id, $user_id, $user_id]);
$message = $stmt->fetch();

if (!$message) {
    header('Location: messages.php');
    exit;
}

// Mark as read if user is receiver
if ($message['receiver_id'] == $user_id && !$message['is_read']) {
    $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = ?");
    $stmt->execute([$message_id]);
}

$is_inbox = ($message['receiver_id'] == $user_id);

$pageTitle = 'View Message';
require_once '../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
    <div>
        <h1 class="h3 mb-1">Message Details</h1>
        <p class="text-muted mb-0">
            <?php echo $is_inbox ? 'Received from ' . sanitize($message['sender_name']) : 'Sent to ' . sanitize($message['receiver_name']); ?>
        </p>
    </div>
    <div class="mt-3 mt-md-0 d-flex gap-2">
        <?php if ($is_inbox): ?>
            <a href="message_new.php?to=<?php echo $message['sender_id']; ?>&subject=Re: <?php echo urlencode($message['subject'] ?? ''); ?>" class="btn btn-brand btn-sm">
                <i class="bi bi-reply"></i> Reply
            </a>
        <?php endif; ?>
        <a href="messages.php" class="btn btn-outline-secondary btn-sm">
            &larr; Back to Messages
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm col-lg-8">
    <div class="card-body p-4">
        <h2 class="h5 fw-bold mb-4"><?php echo sanitize($message['subject'] ?? 'No Subject'); ?></h2>
        
        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
            <div class="d-flex align-items-center">
                <div class="bg-light rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 48px; height: 48px;">
                    <i class="bi bi-person fs-4 text-secondary"></i>
                </div>
                <div>
                    <div class="fw-bold text-dark">
                        <?php echo $is_inbox ? sanitize($message['sender_name']) : 'You'; ?>
                    </div>
                    <div class="text-muted small">
                        to <?php echo $is_inbox ? 'You' : sanitize($message['receiver_name']); ?>
                    </div>
                </div>
            </div>
            <div class="text-muted small text-end">
                <div><?php echo date('M j, Y', strtotime($message['created_at'])); ?></div>
                <div><?php echo date('g:i A', strtotime($message['created_at'])); ?></div>
            </div>
        </div>
        
        <div class="message-body text-dark" style="min-height: 150px; white-space: pre-wrap; line-height: 1.6;">
<?php echo sanitize($message['body']); ?>
        </div>
        
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
