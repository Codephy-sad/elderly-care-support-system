<?php
require_once '../config/database.php';
require_once '../config/auth.php';
checkRole(1);

$user_id = (int)$_SESSION['user_id'];
$profile_id = getElderlyProfileId($pdo, $user_id);
$error = '';

if (!$profile_id) {
    header('Location: profile.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $request_type = trim($_POST['request_type'] ?? 'general');
    $priority = trim($_POST['priority'] ?? 'medium');

    if (empty($title)) {
        $error = 'Please enter a title for your request.';
    } elseif (strlen($title) > 200) {
        $error = 'Title is too long (max 200 characters).';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO service_requests (elderly_profile_id, requested_by, request_type, title, description, priority, status)
            VALUES (?, ?, ?, ?, ?, ?, 'open')
        ");
        $stmt->execute([$profile_id, $user_id, $request_type, $title, $description, $priority]);
        header('Location: requests.php?success=created');
        exit;
    }
}

$pageTitle = 'New Request';
require_once '../includes/header.php';
?>

<div class="elderly-page">
    <div class="elderly-page-header">
        <h1>New Assistance Request</h1>
        <p class="text-muted mb-0">Tell us what you need help with</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo sanitize($error); ?></div>
    <?php endif; ?>

    <div class="elderly-card">
        <form method="POST">
            <div class="mb-3">
                <label for="title" class="form-label">What do you need? *</label>
                <input type="text" class="form-control" id="title" name="title" required maxlength="200"
                       value="<?php echo sanitize($_POST['title'] ?? ''); ?>"
                       placeholder="e.g. Light bulb needs replacing">
            </div>

            <div class="mb-3">
                <label for="request_type" class="form-label">Type</label>
                <select class="form-select" id="request_type" name="request_type">
                    <option value="general">General</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="housekeeping">Housekeeping</option>
                    <option value="medical">Medical</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="priority" class="form-label">Priority</label>
                <select class="form-select" id="priority" name="priority">
                    <option value="low">Low</option>
                    <option value="medium" selected>Medium</option>
                    <option value="high">High</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Details (optional)</label>
                <textarea class="form-control" id="description" name="description" rows="4"
                          placeholder="Any extra details..."><?php echo sanitize($_POST['description'] ?? ''); ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-brand btn-elderly">Submit Request</button>
                <a href="requests.php" class="btn btn-outline-secondary btn-elderly">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
