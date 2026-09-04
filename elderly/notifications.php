<?php
require_once '../config/database.php';
require_once '../config/auth.php';
checkRole(1);

$user_id = (int)$_SESSION['user_id'];

// mark as read if requested
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    $notif_id = (int)$_POST['notif_id'];
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$notif_id, $user_id]);
    header('Location: notifications.php');
    exit;
}

// mark all as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all_read'])) {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    header('Location: notifications.php');
    exit;
}

// get notifications
$stmt = $pdo->prepare("
    SELECT * FROM notifications
    WHERE user_id = ?
    ORDER BY is_read ASC, created_at DESC
    LIMIT 50
");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

$unread_count = 0;
foreach ($notifications as $n) {
    if (!$n['is_read']) $unread_count++;
}

$pageTitle = 'Notifications';
require_once '../includes/header.php';
?>

<div class="elderly-page">
    <div class="elderly-page-header">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h1>Notifications</h1>
                <p class="text-muted mb-0"><?php echo $unread_count; ?> unread</p>
            </div>
            <?php if ($unread_count > 0): ?>
                <form method="POST">
                    <button type="submit" name="mark_all_read" class="btn btn-outline-secondary btn-elderly">
                        Mark All as Read
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($notifications)): ?>
        <div class="elderly-empty">
            <p>No notifications yet.</p>
        </div>
    <?php else: ?>
        <?php foreach ($notifications as $notif): ?>
            <div class="elderly-card <?php echo $notif['is_read'] ? 'elderly-card--muted' : 'elderly-card--highlight'; ?>">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <?php if (!$notif['is_read']): ?>
                            <span class="badge bg-primary me-1">New</span>
                        <?php endif; ?>
                        <strong><?php echo sanitize($notif['title']); ?></strong>
                        <?php if ($notif['message']): ?>
                            <p class="mb-1 mt-1"><?php echo sanitize($notif['message']); ?></p>
                        <?php endif; ?>
                        <small class="text-muted">
                            <?php echo date('M j, Y \a\t g:i A', strtotime($notif['created_at'])); ?>
                        </small>
                    </div>
                    <div class="text-end">
                        <?php if (!$notif['is_read']): ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="notif_id" value="<?php echo $notif['id']; ?>">
                                <button type="submit" name="mark_read" class="btn btn-sm btn-outline-secondary">
                                    Mark Read
                                </button>
                            </form>
                        <?php endif; ?>
                        <?php if ($notif['link']): ?>
                            <a href="<?php echo sanitize($notif['link']); ?>" class="btn btn-sm btn-brand mt-1">
                                View
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
