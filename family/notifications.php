<?php
require_once '../config/database.php';
require_once '../config/auth.php';

checkRole(2);

$user_id = (int)$_SESSION['user_id'];

// Handle Mark as Read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'mark_read') {
        $notification_id = (int)$_POST['notification_id'];
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$notification_id, $user_id]);
    } elseif ($_POST['action'] === 'mark_all_read') {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
    }
    // Redirect to prevent form resubmission
    header('Location: notifications.php');
    exit;
}

// Fetch notifications
$stmt = $pdo->prepare("
    SELECT * FROM notifications 
    WHERE user_id = ? 
    ORDER BY is_read ASC, created_at DESC
");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

$unread_count = array_reduce($notifications, function($carry, $item) {
    return $carry + ($item['is_read'] ? 0 : 1);
}, 0);

$pageTitle = 'Notifications';
require_once '../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
    <div>
        <h1 class="h3 mb-1">Notifications</h1>
        <p class="text-muted mb-0">System alerts and updates</p>
    </div>
    <div class="mt-3 mt-md-0 d-flex gap-2">
        <?php if ($unread_count > 0): ?>
            <form method="POST">
                <input type="hidden" name="action" value="mark_all_read">
                <button type="submit" class="btn btn-outline-brand btn-sm">Mark All as Read</button>
            </form>
        <?php endif; ?>
        <a href="index.php" class="btn btn-outline-secondary btn-sm">
            &larr; Dashboard
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm col-lg-8">
    <div class="card-body p-0">
        <div class="list-group list-group-flush rounded">
            <?php if (count($notifications) > 0): ?>
                <?php foreach ($notifications as $notif): ?>
                    <?php 
                        $is_read = (bool)$notif['is_read'];
                        $type = $notif['notification_type'];
                        
                        $icon = 'bi-bell';
                        $icon_bg = 'bg-secondary';
                        
                        if ($type === 'emergency') {
                            $icon = 'bi-exclamation-triangle-fill';
                            $icon_bg = 'bg-danger';
                        } elseif ($type === 'request_update') {
                            $icon = 'bi-check-circle-fill';
                            $icon_bg = 'bg-success';
                        } elseif ($type === 'message') {
                            $icon = 'bi-envelope-fill';
                            $icon_bg = 'bg-primary';
                        } elseif ($type === 'system') {
                            $icon = 'bi-info-circle-fill';
                            $icon_bg = 'bg-info text-dark';
                        }
                    ?>
                    <div class="list-group-item p-4 <?php echo !$is_read ? 'bg-light border-start border-brand border-4' : ''; ?>">
                        <div class="d-flex">
                            <div class="me-3">
                                <div class="rounded-circle d-flex justify-content-center align-items-center text-white <?php echo $icon_bg; ?>" style="width: 40px; height: 40px;">
                                    <i class="bi <?php echo $icon; ?>"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0 fw-bold <?php echo !$is_read ? 'text-dark' : 'text-muted'; ?>">
                                        <?php echo sanitize($notif['title']); ?>
                                    </h6>
                                    <small class="text-muted"><?php echo date('M j, g:i A', strtotime($notif['created_at'])); ?></small>
                                </div>
                                <p class="mb-2 text-dark small">
                                    <?php echo sanitize($notif['message']); ?>
                                </p>
                                
                                <?php if (!$is_read): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="mark_read">
                                        <input type="hidden" name="notification_id" value="<?php echo $notif['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-link text-decoration-none p-0">Mark as read</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-bell-slash fs-1 d-block mb-3 text-light-brand"></i>
                    You have no notifications.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
