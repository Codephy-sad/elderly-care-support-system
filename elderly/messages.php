<?php
require_once '../config/database.php';
require_once '../config/auth.php';
checkRole(1);

$user_id = (int)$_SESSION['user_id'];
$profile_id = getElderlyProfileId($pdo, $user_id);
$success = '';
$error = '';

// mark message as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    $msg_id = (int)$_POST['msg_id'];
    $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = ? AND receiver_id = ?");
    $stmt->execute([$msg_id, $user_id]);
    header('Location: messages.php');
    exit;
}

// send a new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $receiver_id = (int)$_POST['receiver_id'];
    $subject = trim($_POST['subject'] ?? '');
    $body = trim($_POST['body'] ?? '');

    if (empty($body)) {
        $error = 'Please enter a message.';
    } else {
        // verify the receiver is an authorized contact (family, caregiver, or manager)
        $stmt = $pdo->prepare("
            SELECT u.id FROM users u
            WHERE u.id = ? AND (
                u.role_id IN (3, 5)
                OR u.id IN (
                    SELECT fc.family_user_id FROM family_connections fc
                    WHERE fc.elderly_profile_id = ?
                )
            )
        ");
        $stmt->execute([$receiver_id, $profile_id ?? 0]);
        if ($stmt->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, subject, body) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $receiver_id, $subject ?: null, $body]);
            $success = 'Message sent!';
        } else {
            $error = 'You can only send messages to your family members, caregivers, or managers.';
        }
    }
}

// get received messages
$stmt = $pdo->prepare("
    SELECT m.*, u.name as sender_name
    FROM messages m
    JOIN users u ON u.id = m.sender_id
    WHERE m.receiver_id = ?
    ORDER BY m.is_read ASC, m.created_at DESC
    LIMIT 50
");
$stmt->execute([$user_id]);
$inbox = $stmt->fetchAll();

// get sent messages
$stmt = $pdo->prepare("
    SELECT m.*, u.name as receiver_name
    FROM messages m
    JOIN users u ON u.id = m.receiver_id
    WHERE m.sender_id = ?
    ORDER BY m.created_at DESC
    LIMIT 20
");
$stmt->execute([$user_id]);
$sent = $stmt->fetchAll();

// get authorized recipients for the compose form
$recipients = [];
if ($profile_id) {
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, r.name as role_name FROM users u
        JOIN roles r ON r.id = u.role_id
        WHERE u.role_id IN (3, 5)
           OR u.id IN (SELECT fc.family_user_id FROM family_connections fc WHERE fc.elderly_profile_id = ?)
        ORDER BY r.name, u.name
    ");
    $stmt->execute([$profile_id]);
    $recipients = $stmt->fetchAll();
}

$pageTitle = 'Messages';
require_once '../includes/header.php';
?>

<div class="elderly-page">
    <div class="elderly-page-header">
        <h1>Messages</h1>
        <p class="text-muted mb-0">Send and receive messages</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo sanitize($success); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo sanitize($error); ?></div>
    <?php endif; ?>

    <!-- Compose -->
    <?php if (!empty($recipients)): ?>
        <div class="elderly-card mb-4">
            <h2 class="elderly-section-title">Send a Message</h2>
            <form method="POST">
                <div class="mb-3">
                    <label for="receiver_id" class="form-label">To *</label>
                    <select class="form-select" id="receiver_id" name="receiver_id" required>
                        <option value="">Select recipient...</option>
                        <?php foreach ($recipients as $r): ?>
                            <option value="<?php echo $r['id']; ?>">
                                <?php echo sanitize($r['name']); ?> (<?php echo sanitize($r['role_name']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="subject" class="form-label">Subject</label>
                    <input type="text" class="form-control" id="subject" name="subject" maxlength="160">
                </div>
                <div class="mb-3">
                    <label for="body" class="form-label">Message *</label>
                    <textarea class="form-control" id="body" name="body" rows="3" required></textarea>
                </div>
                <button type="submit" name="send_message" class="btn btn-brand btn-elderly">Send</button>
            </form>
        </div>
    <?php endif; ?>

    <!-- Inbox -->
    <h2 class="elderly-section-title">Inbox</h2>
    <?php if (empty($inbox)): ?>
        <div class="elderly-empty"><p>No messages received.</p></div>
    <?php else: ?>
        <?php foreach ($inbox as $msg): ?>
            <div class="elderly-card <?php echo $msg['is_read'] ? 'elderly-card--muted' : 'elderly-card--highlight'; ?>">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <?php if (!$msg['is_read']): ?>
                            <span class="badge bg-primary me-1">New</span>
                        <?php endif; ?>
                        <strong>From: <?php echo sanitize($msg['sender_name']); ?></strong>
                        <?php if ($msg['subject']): ?>
                            <br><strong><?php echo sanitize($msg['subject']); ?></strong>
                        <?php endif; ?>
                        <p class="mb-1 mt-1"><?php echo nl2br(sanitize($msg['body'])); ?></p>
                        <small class="text-muted"><?php echo date('M j, Y \a\t g:i A', strtotime($msg['created_at'])); ?></small>
                    </div>
                    <?php if (!$msg['is_read']): ?>
                        <form method="POST">
                            <input type="hidden" name="msg_id" value="<?php echo $msg['id']; ?>">
                            <button type="submit" name="mark_read" class="btn btn-sm btn-outline-secondary">Mark Read</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Sent -->
    <?php if (!empty($sent)): ?>
        <h2 class="elderly-section-title mt-4">Sent Messages</h2>
        <?php foreach ($sent as $msg): ?>
            <div class="elderly-card elderly-card--muted">
                <strong>To: <?php echo sanitize($msg['receiver_name']); ?></strong>
                <?php if ($msg['subject']): ?>
                    <br><strong><?php echo sanitize($msg['subject']); ?></strong>
                <?php endif; ?>
                <p class="mb-1 mt-1"><?php echo nl2br(sanitize($msg['body'])); ?></p>
                <small class="text-muted"><?php echo date('M j, Y \a\t g:i A', strtotime($msg['created_at'])); ?></small>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
