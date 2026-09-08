<?php
require_once '../config/database.php';
require_once '../config/auth.php';

checkRole(2);

$user_id = (int)$_SESSION['user_id'];
$connections = getFamilyConnections($pdo, $user_id);

if (empty($connections)) {
    header('Location: index.php');
    exit;
}

// Fetch user's inbox
$stmt = $pdo->prepare("
    SELECT m.*, u.name as sender_name 
    FROM messages m
    JOIN users u ON u.id = m.sender_id
    WHERE m.receiver_id = ?
    ORDER BY m.created_at DESC
");
$stmt->execute([$user_id]);
$inbox = $stmt->fetchAll();

// Fetch user's sent messages
$stmt = $pdo->prepare("
    SELECT m.*, u.name as receiver_name 
    FROM messages m
    JOIN users u ON u.id = m.receiver_id
    WHERE m.sender_id = ?
    ORDER BY m.created_at DESC
");
$stmt->execute([$user_id]);
$sent = $stmt->fetchAll();

$pageTitle = 'Messages';
require_once '../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
    <div>
        <h1 class="h3 mb-1">Messages</h1>
        <p class="text-muted mb-0">Communicate with staff and management</p>
    </div>
    <div class="mt-3 mt-md-0 d-flex gap-2">
        <a href="message_new.php" class="btn btn-brand btn-sm">
            + Compose
        </a>
        <a href="index.php" class="btn btn-outline-secondary btn-sm">
            &larr; Dashboard
        </a>
    </div>
</div>

<?php if (isset($_GET['success']) && $_GET['success'] === 'sent'): ?>
    <div class="alert alert-success border-0 shadow-sm">Message sent successfully.</div>
<?php endif; ?>

<ul class="nav nav-tabs mb-4 border-bottom-0" id="messageTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active bg-light text-dark border border-bottom-0 fw-bold" id="inbox-tab" data-bs-toggle="tab" data-bs-target="#inbox" type="button" role="tab" aria-controls="inbox" aria-selected="true">
        Inbox
        <?php
            $unread = array_filter($inbox, function($m) { return !$m['is_read']; });
            if (count($unread) > 0) {
                echo '<span class="badge bg-danger ms-1">' . count($unread) . '</span>';
            }
        ?>
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link text-muted border border-bottom-0 ms-1" id="sent-tab" data-bs-toggle="tab" data-bs-target="#sent" type="button" role="tab" aria-controls="sent" aria-selected="false">Sent</button>
  </li>
</ul>

<div class="tab-content" id="messageTabsContent">
    <!-- Inbox Tab -->
    <div class="tab-pane fade show active" id="inbox" role="tabpanel" aria-labelledby="inbox-tab">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="list-group list-group-flush rounded">
                    <?php if (count($inbox) > 0): ?>
                        <?php foreach ($inbox as $msg): ?>
                            <a href="message_view.php?id=<?php echo $msg['id']; ?>" class="list-group-item list-group-item-action p-4 <?php echo !$msg['is_read'] ? 'bg-light border-start border-brand border-4' : ''; ?>">
                                <div class="d-flex w-100 justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0 fw-bold <?php echo !$msg['is_read'] ? 'text-dark' : 'text-muted'; ?>">
                                        <?php echo sanitize($msg['sender_name']); ?>
                                    </h6>
                                    <small class="text-muted"><?php echo date('M j, Y g:i A', strtotime($msg['created_at'])); ?></small>
                                </div>
                                <p class="mb-1 fw-bold text-dark"><?php echo sanitize($msg['subject'] ?? 'No Subject'); ?></p>
                                <p class="mb-0 text-muted small text-truncate" style="max-width: 80%;">
                                    <?php echo sanitize($msg['body']); ?>
                                </p>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-5 text-center text-muted">
                            Your inbox is empty.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sent Tab -->
    <div class="tab-pane fade" id="sent" role="tabpanel" aria-labelledby="sent-tab">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="list-group list-group-flush rounded">
                    <?php if (count($sent) > 0): ?>
                        <?php foreach ($sent as $msg): ?>
                            <a href="message_view.php?id=<?php echo $msg['id']; ?>" class="list-group-item list-group-item-action p-4">
                                <div class="d-flex w-100 justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0 fw-bold text-muted">
                                        To: <?php echo sanitize($msg['receiver_name']); ?>
                                    </h6>
                                    <small class="text-muted"><?php echo date('M j, Y g:i A', strtotime($msg['created_at'])); ?></small>
                                </div>
                                <p class="mb-1 fw-bold text-dark"><?php echo sanitize($msg['subject'] ?? 'No Subject'); ?></p>
                                <p class="mb-0 text-muted small text-truncate" style="max-width: 80%;">
                                    <?php echo sanitize($msg['body']); ?>
                                </p>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-5 text-center text-muted">
                            You haven't sent any messages yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
