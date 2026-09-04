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

// handle new request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_request'])) {
    $destination = trim($_POST['destination'] ?? '');
    $pickup_date = trim($_POST['pickup_date'] ?? '');
    $pickup_time = trim($_POST['pickup_time'] ?? '');
    $purpose = trim($_POST['purpose'] ?? '');

    if (empty($destination) || empty($pickup_date) || empty($pickup_time)) {
        $error = 'Please fill in destination, date, and time.';
    } elseif (strtotime($pickup_date) < strtotime(date('Y-m-d'))) {
        $error = 'Pickup date cannot be in the past.';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO transportation_requests (elderly_profile_id, destination, pickup_date, pickup_time, purpose, status)
            VALUES (?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([$profile_id, $destination, $pickup_date, $pickup_time, $purpose ?: null]);
        $success = 'Transportation request submitted!';
    }
}

// handle cancel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_request'])) {
    $req_id = (int)$_POST['req_id'];
    $stmt = $pdo->prepare("
        UPDATE transportation_requests SET status = 'cancelled'
        WHERE id = ? AND elderly_profile_id = ? AND status = 'pending'
    ");
    $stmt->execute([$req_id, $profile_id]);
    if ($stmt->rowCount() > 0) {
        $success = 'Request cancelled.';
    }
}

// get requests
$stmt = $pdo->prepare("
    SELECT * FROM transportation_requests
    WHERE elderly_profile_id = ?
    ORDER BY pickup_date DESC, pickup_time DESC
");
$stmt->execute([$profile_id]);
$requests = $stmt->fetchAll();

$pageTitle = 'Transportation';
require_once '../includes/header.php';
?>

<div class="elderly-page">
    <div class="elderly-page-header">
        <h1>Transportation</h1>
        <p class="text-muted mb-0">Request transportation for appointments and outings</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo sanitize($success); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo sanitize($error); ?></div>
    <?php endif; ?>

    <!-- New request form -->
    <div class="elderly-card mb-4">
        <h2 class="elderly-section-title">Request Transportation</h2>
        <form method="POST">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="destination" class="form-label">Destination *</label>
                    <input type="text" class="form-control" id="destination" name="destination" required
                           placeholder="e.g. Dhaka Medical College">
                </div>
                <div class="col-md-3">
                    <label for="pickup_date" class="form-label">Pickup Date *</label>
                    <input type="date" class="form-control" id="pickup_date" name="pickup_date" required
                           min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="col-md-3">
                    <label for="pickup_time" class="form-label">Pickup Time *</label>
                    <input type="time" class="form-control" id="pickup_time" name="pickup_time" required>
                </div>
                <div class="col-12">
                    <label for="purpose" class="form-label">Purpose (optional)</label>
                    <input type="text" class="form-control" id="purpose" name="purpose"
                           placeholder="e.g. Doctor's appointment">
                </div>
                <div class="col-12">
                    <button type="submit" name="create_request" class="btn btn-brand btn-elderly">Submit Request</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Existing requests -->
    <h2 class="elderly-section-title">My Requests</h2>
    <?php if (empty($requests)): ?>
        <div class="elderly-empty"><p>No transportation requests yet.</p></div>
    <?php else: ?>
        <?php foreach ($requests as $req): ?>
            <div class="elderly-card elderly-card--highlight">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="h5 mb-1"><?php echo sanitize($req['destination']); ?></h3>
                        <p class="mb-1">
                            <?php echo date('l, M j, Y', strtotime($req['pickup_date'])); ?>
                            at <?php echo date('g:i A', strtotime($req['pickup_time'])); ?>
                        </p>
                        <?php if ($req['purpose']): ?>
                            <small class="text-muted">Purpose: <?php echo sanitize($req['purpose']); ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="text-end">
                        <span class="status-badge status-<?php echo sanitize($req['status']); ?>">
                            <?php echo sanitize(ucfirst($req['status'])); ?>
                        </span>
                        <?php if ($req['status'] === 'pending'): ?>
                            <form method="POST" class="mt-2">
                                <input type="hidden" name="req_id" value="<?php echo $req['id']; ?>">
                                <button type="submit" name="cancel_request" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Cancel this request?')">Cancel</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
