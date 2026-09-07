<?php
require_once '../config/database.php';
require_once '../config/auth.php';
checkRole(1);

$user_id = (int)$_SESSION['user_id'];
$profile_id = getElderlyProfileId($pdo, $user_id);

$requests = [];
if ($profile_id) {
    $stmt = $pdo->prepare("
        SELECT * FROM service_requests
        WHERE elderly_profile_id = ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$profile_id]);
    $requests = $stmt->fetchAll();
}

$pageTitle = 'My Requests';
require_once '../includes/header.php';
?>

<div class="elderly-page">
    <div class="elderly-page-header">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h1>My Assistance Requests</h1>
                <p class="text-muted mb-0">Track your requests and their status</p>
            </div>
            <a href="request_new.php" class="btn btn-brand btn-elderly">New Request</a>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <?php if ($_GET['success'] === 'created'): ?>Request submitted successfully.
            <?php elseif ($_GET['success'] === 'cancelled'): ?>Request cancelled.
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($requests)): ?>
        <div class="elderly-empty">
            <p>You haven't made any requests yet.</p>
            <a href="request_new.php" class="btn btn-brand btn-elderly">Make a Request</a>
        </div>
    <?php else: ?>
        <?php foreach ($requests as $req): ?>
            <a href="request_view.php?id=<?php echo $req['id']; ?>" class="text-decoration-none">
                <div class="elderly-card elderly-card--highlight">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h3 class="h5 mb-1 text-dark"><?php echo sanitize($req['title']); ?></h3>
                            <p class="text-muted mb-1">
                                <?php echo sanitize(ucfirst($req['request_type'] ?? 'general')); ?> —
                                <?php echo date('M j, Y', strtotime($req['created_at'])); ?>
                            </p>
                        </div>
                        <div class="text-end">
                            <span class="status-badge status-<?php echo sanitize($req['status']); ?>">
                                <?php echo sanitize(ucfirst(str_replace('_', ' ', $req['status']))); ?>
                            </span>
                            <br>
                            <span class="badge bg-secondary mt-1"><?php echo sanitize($req['priority'] ?? 'medium'); ?></span>
                        </div>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
