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

// get current room
$stmt = $pdo->prepare("
    SELECT r.id as room_id, r.room_number, r.floor, r.room_type
    FROM room_assignments ra
    JOIN rooms r ON r.id = ra.room_id
    WHERE ra.elderly_profile_id = ? AND ra.end_date IS NULL
    ORDER BY ra.start_date DESC LIMIT 1
");
$stmt->execute([$profile_id]);
$current_room = $stmt->fetch();

// handle new request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_request'])) {
    $reason = trim($_POST['reason'] ?? '');
    $preferred_room_id = !empty($_POST['preferred_room_id']) ? (int)$_POST['preferred_room_id'] : null;

    if (empty($reason)) {
        $error = 'Please explain the reason for the room change.';
    } else {
        // check if there's already a pending request
        $stmt = $pdo->prepare("SELECT id FROM room_change_requests WHERE elderly_profile_id = ? AND status = 'pending'");
        $stmt->execute([$profile_id]);
        if ($stmt->fetch()) {
            $error = 'You already have a pending room change request.';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO room_change_requests (elderly_profile_id, current_room_id, preferred_room_id, reason, status)
                VALUES (?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([$profile_id, $current_room ? $current_room['room_id'] : null, $preferred_room_id, $reason]);
            $success = 'Room change request submitted!';
        }
    }
}

// get available rooms (for preferred room dropdown)
$stmt = $pdo->query("SELECT id, room_number, floor, room_type FROM rooms WHERE status = 'available' ORDER BY room_number");
$available_rooms = $stmt->fetchAll();

// get existing requests
$stmt = $pdo->prepare("
    SELECT rcr.*, r1.room_number as current_room_number, r2.room_number as preferred_room_number
    FROM room_change_requests rcr
    LEFT JOIN rooms r1 ON r1.id = rcr.current_room_id
    LEFT JOIN rooms r2 ON r2.id = rcr.preferred_room_id
    WHERE rcr.elderly_profile_id = ?
    ORDER BY rcr.created_at DESC
");
$stmt->execute([$profile_id]);
$requests = $stmt->fetchAll();

$pageTitle = 'Room Change';
require_once '../includes/header.php';
?>

<div class="elderly-page">
    <div class="elderly-page-header">
        <h1>Room Change Request</h1>
        <p class="text-muted mb-0">Request a room change</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo sanitize($success); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo sanitize($error); ?></div>
    <?php endif; ?>

    <!-- Current room -->
    <div class="elderly-card elderly-card--highlight mb-4">
        <h2 class="h5 mb-2">Current Room</h2>
        <?php if ($current_room): ?>
            <p class="mb-0">
                <strong>Room <?php echo sanitize($current_room['room_number']); ?></strong>
                — Floor <?php echo sanitize($current_room['floor']); ?>
                (<?php echo sanitize(ucfirst($current_room['room_type'])); ?>)
            </p>
        <?php else: ?>
            <p class="text-muted mb-0">No room currently assigned.</p>
        <?php endif; ?>
    </div>

    <!-- New request form -->
    <div class="elderly-card mb-4">
        <h2 class="elderly-section-title">Submit a Request</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="preferred_room_id" class="form-label">Preferred Room (optional)</label>
                <select class="form-select" id="preferred_room_id" name="preferred_room_id">
                    <option value="">Any available room</option>
                    <?php foreach ($available_rooms as $r): ?>
                        <option value="<?php echo $r['id']; ?>">
                            Room <?php echo sanitize($r['room_number']); ?> — Floor <?php echo sanitize($r['floor']); ?>
                            (<?php echo sanitize(ucfirst($r['room_type'])); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="reason" class="form-label">Reason *</label>
                <textarea class="form-control" id="reason" name="reason" rows="3" required
                          placeholder="Please explain why you'd like to change rooms..."></textarea>
            </div>
            <button type="submit" name="submit_request" class="btn btn-brand btn-elderly">Submit Request</button>
        </form>
    </div>

    <!-- Previous requests -->
    <?php if (!empty($requests)): ?>
        <h2 class="elderly-section-title">Request History</h2>
        <?php foreach ($requests as $req): ?>
            <div class="elderly-card elderly-card--<?php echo $req['status'] === 'approved' ? 'success' : ($req['status'] === 'rejected' ? 'danger' : 'highlight'); ?>">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="mb-1">
                            <?php if ($req['current_room_number']): ?>
                                From Room <?php echo sanitize($req['current_room_number']); ?>
                            <?php endif; ?>
                            → <?php echo $req['preferred_room_number'] ? 'Room ' . sanitize($req['preferred_room_number']) : 'Any available'; ?>
                        </p>
                        <p class="mb-1"><?php echo sanitize($req['reason']); ?></p>
                        <small class="text-muted"><?php echo date('M j, Y', strtotime($req['created_at'])); ?></small>
                    </div>
                    <span class="status-badge status-<?php echo sanitize($req['status']); ?>">
                        <?php echo sanitize(ucfirst($req['status'])); ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
