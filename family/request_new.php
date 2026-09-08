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

$selected_elderly_id = isset($_GET['elderly_id']) ? (int)$_GET['elderly_id'] : (int)$connections[0]['elderly_profile_id'];
$active_connection = null;
foreach ($connections as $conn) {
    if ((int)$conn['elderly_profile_id'] === $selected_elderly_id) {
        $active_connection = $conn;
        break;
    }
}
if (!$active_connection) {
    $active_connection = $connections[0];
    $selected_elderly_id = (int)$active_connection['elderly_profile_id'];
}
$elderly_name = $active_connection['elderly_name'];

$error = '';

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
        $stmt->execute([$selected_elderly_id, $user_id, $request_type, $title, $description, $priority]);
        header('Location: requests.php?elderly_id=' . $selected_elderly_id . '&success=created');
        exit;
    }
}

$pageTitle = 'New Request - ' . sanitize($elderly_name);
require_once '../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
    <div>
        <h1 class="h3 mb-1">New Service Request</h1>
        <p class="text-muted mb-0">Submit a request on behalf of <?php echo sanitize($elderly_name); ?></p>
    </div>
    <div class="mt-3 mt-md-0 d-flex gap-2">
        <a href="requests.php?elderly_id=<?php echo $selected_elderly_id; ?>" class="btn btn-outline-secondary btn-sm">
            &larr; Back to Requests
        </a>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger border-0 shadow-sm"><?php echo sanitize($error); ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm col-lg-8">
    <div class="card-body p-4">
        <form method="POST">
            <div class="mb-3">
                <label for="title" class="form-label fw-bold">Title / Summary <span class="text-danger">*</span></label>
                <input type="text" class="form-control bg-light border-0" id="title" name="title" required maxlength="200"
                       value="<?php echo sanitize($_POST['title'] ?? ''); ?>"
                       placeholder="e.g. Needs new reading glasses">
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="request_type" class="form-label fw-bold">Request Type</label>
                    <select class="form-select bg-light border-0" id="request_type" name="request_type">
                        <option value="general">General</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="housekeeping">Housekeeping</option>
                        <option value="medical">Medical</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="col-md-6 mt-3 mt-md-0">
                    <label for="priority" class="form-label fw-bold">Priority</label>
                    <select class="form-select bg-light border-0" id="priority" name="priority">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label for="description" class="form-label fw-bold">Description (optional)</label>
                <textarea class="form-control bg-light border-0" id="description" name="description" rows="5"
                          placeholder="Please provide any additional details..."><?php echo sanitize($_POST['description'] ?? ''); ?></textarea>
            </div>

            <div class="d-flex gap-2 pt-3 border-top">
                <button type="submit" class="btn btn-brand px-4">Submit Request</button>
                <a href="requests.php?elderly_id=<?php echo $selected_elderly_id; ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
