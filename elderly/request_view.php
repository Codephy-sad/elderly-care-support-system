<?php
require_once '../config/database.php';
require_once '../config/auth.php';
checkRole(1);

$user_id = (int)$_SESSION['user_id'];
$profile_id = getElderlyProfileId($pdo, $user_id);
$id = (int)($_GET['id'] ?? 0);
$success = '';
$error = '';

// get the request and verify it belongs to this user
$request = null;
if ($profile_id && $id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM service_requests WHERE id = ? AND elderly_profile_id = ?");
    $stmt->execute([$id, $profile_id]);
    $request = $stmt->fetch();
}

if (!$request) {
    header('Location: requests.php');
    exit;
}

// handle cancel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_request'])) {
    if (in_array($request['status'], ['open', 'in_progress'])) {
        $stmt = $pdo->prepare("UPDATE service_requests SET status = 'cancelled' WHERE id = ? AND elderly_profile_id = ?");
        $stmt->execute([$id, $profile_id]);
        header('Location: requests.php?success=cancelled');
        exit;
    } else {
        $error = 'This request cannot be cancelled.';
    }
}

$pageTitle = 'Request Details';
require_once '../includes/header.php';
?>

<div class="elderly-page">
    <div class="elderly-page-header">
        <h1>Request Details</h1>
        <a href="requests.php" class="text-muted">← Back to My Requests</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo sanitize($error); ?></div>
    <?php endif; ?>

    <div class="elderly-card elderly-card--highlight">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <h2 class="h4 mb-0"><?php echo sanitize($request['title']); ?></h2>
            <span class="status-badge status-<?php echo sanitize($request['status']); ?>">
                <?php echo sanitize(ucfirst(str_replace('_', ' ', $request['status']))); ?>
            </span>
        </div>

        <div class="row mb-3">
            <div class="col-sm-4">
                <small class="text-muted d-block">Type</small>
                <strong><?php echo sanitize(ucfirst($request['request_type'] ?? 'General')); ?></strong>
            </div>
            <div class="col-sm-4">
                <small class="text-muted d-block">Priority</small>
                <strong><?php echo sanitize(ucfirst($request['priority'] ?? 'Medium')); ?></strong>
            </div>
            <div class="col-sm-4">
                <small class="text-muted d-block">Submitted</small>
                <strong><?php echo date('M j, Y \a\t g:i A', strtotime($request['created_at'])); ?></strong>
            </div>
        </div>

        <?php if ($request['description']): ?>
            <div class="mb-3">
                <small class="text-muted d-block">Description</small>
                <p><?php echo nl2br(sanitize($request['description'])); ?></p>
            </div>
        <?php endif; ?>

        <?php if (in_array($request['status'], ['open', 'in_progress'])): ?>
            <hr>
            <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this request?')">
                <button type="submit" name="cancel_request" class="btn btn-outline-danger btn-elderly">
                    Cancel This Request
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
