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

$success_msg = '';
$error_msg = '';

// Handle New Visit Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request_visit') {
    $visit_date = trim($_POST['visit_date'] ?? '');
    $visit_time = trim($_POST['visit_time'] ?? '');
    $duration = (int)($_POST['duration_minutes'] ?? 60);
    $notes = trim($_POST['notes'] ?? '');
    
    // Basic validation
    if (empty($visit_date) || empty($visit_time)) {
        $error_msg = "Please provide both date and time for the visit.";
    } elseif (strtotime("$visit_date $visit_time") < time()) {
        $error_msg = "Visit must be scheduled in the future.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO visits (elderly_profile_id, family_user_id, visit_date, visit_time, duration_minutes, status, notes) VALUES (?, ?, ?, ?, ?, 'pending', ?)");
        if ($stmt->execute([$selected_elderly_id, $user_id, $visit_date, $visit_time, $duration, $notes])) {
            $success_msg = "Visit request submitted successfully. It is pending approval by management.";
        } else {
            $error_msg = "An error occurred while scheduling the visit.";
        }
    }
}

// Handle Visit Cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_visit') {
    $visit_id = (int)$_POST['visit_id'];
    
    $stmt = $pdo->prepare("UPDATE visits SET status = 'cancelled' WHERE id = ? AND family_user_id = ? AND elderly_profile_id = ? AND status IN ('pending', 'approved')");
    if ($stmt->execute([$visit_id, $user_id, $selected_elderly_id]) && $stmt->rowCount() > 0) {
        $success_msg = "Visit has been cancelled.";
    } else {
        $error_msg = "Could not cancel this visit. It may already be completed or cancelled.";
    }
}

// Fetch all visits for this elderly/family combo
$stmt = $pdo->prepare("
    SELECT * FROM visits 
    WHERE elderly_profile_id = ? AND family_user_id = ?
    ORDER BY visit_date DESC, visit_time DESC
");
$stmt->execute([$selected_elderly_id, $user_id]);
$visits = $stmt->fetchAll();

$pageTitle = 'Visits - ' . sanitize($elderly_name);
require_once '../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
    <div>
        <h1 class="h3 mb-1">Visits</h1>
        <p class="text-muted mb-0">Schedule and manage visits with <?php echo sanitize($elderly_name); ?></p>
    </div>
    <div class="mt-3 mt-md-0 d-flex gap-2">
        <button class="btn btn-brand btn-sm" data-bs-toggle="modal" data-bs-target="#newVisitModal">
            + Schedule New Visit
        </button>
        <a href="index.php?elderly_id=<?php echo $selected_elderly_id; ?>" class="btn btn-outline-secondary btn-sm">
            &larr; Dashboard
        </a>
    </div>
</div>

<?php if ($success_msg): ?>
    <div class="alert alert-success border-0 shadow-sm"><?php echo sanitize($success_msg); ?></div>
<?php endif; ?>
<?php if ($error_msg): ?>
    <div class="alert alert-danger border-0 shadow-sm"><?php echo sanitize($error_msg); ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Date & Time</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Notes</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($visits) > 0): ?>
                        <?php foreach ($visits as $visit): ?>
                            <?php
                                $status = $visit['status'];
                                $badge_class = 'bg-secondary';
                                if ($status === 'approved') $badge_class = 'bg-success';
                                elseif ($status === 'pending') $badge_class = 'bg-warning text-dark';
                                elseif ($status === 'cancelled') $badge_class = 'bg-danger';
                                
                                $datetime = strtotime($visit['visit_date'] . ' ' . $visit['visit_time']);
                                $is_future = $datetime > time() && in_array($status, ['pending', 'approved']);
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark"><?php echo date('M j, Y', $datetime); ?></div>
                                    <div class="text-muted small"><?php echo date('g:i A', $datetime); ?></div>
                                </td>
                                <td><?php echo sanitize($visit['duration_minutes']); ?> mins</td>
                                <td>
                                    <span class="badge <?php echo $badge_class; ?> rounded-pill">
                                        <?php echo ucfirst(sanitize($status)); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($visit['notes'])): ?>
                                        <span class="text-truncate d-inline-block" style="max-width: 200px;" title="<?php echo sanitize($visit['notes']); ?>">
                                            <?php echo sanitize($visit['notes']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic">None</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <?php if ($is_future): ?>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this visit?');">
                                            <input type="hidden" name="action" value="cancel_visit">
                                            <input type="hidden" name="visit_id" value="<?php echo $visit['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Cancel</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                No visits scheduled yet.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- New Visit Modal -->
<div class="modal fade" id="newVisitModal" tabindex="-1" aria-labelledby="newVisitModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <form method="POST">
          <div class="modal-header bg-light border-bottom-0">
            <h5 class="modal-title h6 fw-bold" id="newVisitModalLabel">Schedule a Visit with <?php echo sanitize($elderly_name); ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="action" value="request_visit">
            
            <div class="mb-3">
                <label for="visit_date" class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control bg-light border-0" id="visit_date" name="visit_date" min="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div class="row mb-3">
                <div class="col-6">
                    <label for="visit_time" class="form-label fw-bold">Time <span class="text-danger">*</span></label>
                    <input type="time" class="form-control bg-light border-0" id="visit_time" name="visit_time" required>
                </div>
                <div class="col-6">
                    <label for="duration_minutes" class="form-label fw-bold">Duration</label>
                    <select class="form-select bg-light border-0" id="duration_minutes" name="duration_minutes">
                        <option value="30">30 minutes</option>
                        <option value="60" selected>1 hour</option>
                        <option value="120">2 hours</option>
                        <option value="240">Half day (4 hours)</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="notes" class="form-label fw-bold">Notes (Optional)</label>
                <textarea class="form-control bg-light border-0" id="notes" name="notes" rows="3" placeholder="Any special requests or items you are bringing..."></textarea>
            </div>
          </div>
          <div class="modal-footer border-top-0">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-brand">Submit Request</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
