<?php
require_once '../config/database.php';
require_once '../config/auth.php';
checkRole(1);

$user_id = (int)$_SESSION['user_id'];
$profile_id = getElderlyProfileId($pdo, $user_id);
$success = '';
$error = '';

if (!$profile_id) {
    header('Location: profile.php');
    exit;
}

// handle sending an alert
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_alert'])) {
    $alert_type = trim($_POST['alert_type'] ?? 'medical');
    $message = trim($_POST['message'] ?? '');

    $allowed_types = ['medical', 'fall', 'fire', 'other'];
    if (!in_array($alert_type, $allowed_types)) {
        $alert_type = 'medical';
    }

    $stmt = $pdo->prepare("
        INSERT INTO emergency_alerts (elderly_profile_id, alert_type, message, status)
        VALUES (?, ?, ?, 'active')
    ");
    $stmt->execute([$profile_id, $alert_type, $message ?: null]);
    $success = 'Emergency alert sent! Help is on the way.';
}

// get recent alerts
$stmt = $pdo->prepare("
    SELECT ea.*, u.name as resolved_by_name
    FROM emergency_alerts ea
    LEFT JOIN users u ON u.id = ea.resolved_by
    WHERE ea.elderly_profile_id = ?
    ORDER BY ea.created_at DESC
    LIMIT 5
");
$stmt->execute([$profile_id]);
$alerts = $stmt->fetchAll();

// check if there's an active alert
$has_active = false;
foreach ($alerts as $a) {
    if ($a['status'] === 'active') { $has_active = true; break; }
}

$pageTitle = 'Emergency Help';
require_once '../includes/header.php';
?>

<div class="elderly-page">
    <div class="elderly-page-header">
        <h1>Emergency Help</h1>
        <p class="text-muted mb-0">Press the button below if you need immediate help</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible">
            <strong><?php echo sanitize($success); ?></strong>
        </div>
    <?php endif; ?>

    <?php if ($has_active): ?>
        <div class="alert alert-danger">
            <strong>You have an active emergency alert.</strong> Staff has been notified.
        </div>
    <?php endif; ?>

    <div class="elderly-card text-center" style="padding: 2rem;">
        <form method="POST" onsubmit="return confirm('Are you sure you want to send an emergency alert? Staff will be notified immediately.')">
            <div class="mb-4">
                <label for="alert_type" class="form-label">What kind of emergency?</label>
                <select class="form-select mx-auto" id="alert_type" name="alert_type" style="max-width: 300px;">
                    <option value="medical">Medical Emergency</option>
                    <option value="fall">I've Fallen</option>
                    <option value="fire">Fire / Safety</option>
                    <option value="other">Other Emergency</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="message" class="form-label">Brief message (optional)</label>
                <textarea class="form-control mx-auto" id="message" name="message" rows="2"
                          style="max-width: 400px;" placeholder="Any details..."></textarea>
            </div>

            <button type="submit" name="send_alert" class="btn btn-emergency d-block mx-auto">
                🚨 SEND EMERGENCY ALERT
            </button>
            <small class="text-muted d-block mt-2">Staff will be notified immediately</small>
        </form>
    </div>

    <?php if (!empty($alerts)): ?>
        <h2 class="elderly-section-title mt-4">Recent Alerts</h2>
        <?php foreach ($alerts as $alert): ?>
            <div class="elderly-card elderly-card--<?php echo $alert['status'] === 'active' ? 'danger' : ($alert['status'] === 'resolved' ? 'success' : 'warning'); ?>">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong><?php echo sanitize(ucfirst($alert['alert_type'])); ?> Emergency</strong>
                        <?php if ($alert['message']): ?>
                            <p class="mb-1"><?php echo sanitize($alert['message']); ?></p>
                        <?php endif; ?>
                        <small class="text-muted"><?php echo date('M j, Y \a\t g:i A', strtotime($alert['created_at'])); ?></small>
                    </div>
                    <div class="text-end">
                        <span class="status-badge status-<?php echo sanitize($alert['status']); ?>">
                            <?php echo sanitize(ucfirst($alert['status'])); ?>
                        </span>
                        <?php if ($alert['resolved_by_name']): ?>
                            <br><small class="text-muted">by <?php echo sanitize($alert['resolved_by_name']); ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
