<?php
require_once '../config/database.php';
require_once '../config/auth.php';
checkRole(1);

$user_id = (int)$_SESSION['user_id'];
$profile_id = getElderlyProfileId($pdo, $user_id);
$success = '';
$error = '';

// handle registration / cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $profile_id) {
    $activity_id = (int)$_POST['activity_id'];

    if (isset($_POST['register'])) {
        // check if already registered
        $stmt = $pdo->prepare("SELECT id FROM activity_registrations WHERE activity_id = ? AND elderly_profile_id = ?");
        $stmt->execute([$activity_id, $profile_id]);
        if ($stmt->fetch()) {
            $error = 'You are already registered for this activity.';
        } else {
            // check capacity
            $stmt = $pdo->prepare("SELECT max_participants FROM activities WHERE id = ?");
            $stmt->execute([$activity_id]);
            $activity = $stmt->fetch();

            if ($activity && $activity['max_participants'] !== null) {
                $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM activity_registrations WHERE activity_id = ? AND status = 'registered'");
                $stmt->execute([$activity_id]);
                $count = (int)$stmt->fetch()['cnt'];
                if ($count >= (int)$activity['max_participants']) {
                    $error = 'Sorry, this activity is full.';
                }
            }

            if (!$error) {
                $stmt = $pdo->prepare("INSERT INTO activity_registrations (activity_id, elderly_profile_id, status) VALUES (?, ?, 'registered')");
                $stmt->execute([$activity_id, $profile_id]);
                $success = 'You have been registered!';
            }
        }
    }

    if (isset($_POST['cancel'])) {
        $stmt = $pdo->prepare("
            UPDATE activity_registrations SET status = 'cancelled'
            WHERE activity_id = ? AND elderly_profile_id = ? AND status = 'registered'
        ");
        $stmt->execute([$activity_id, $profile_id]);
        if ($stmt->rowCount() > 0) {
            $success = 'Registration cancelled.';
        } else {
            $error = 'Could not cancel this registration.';
        }
    }
}

// get upcoming activities with registration status
$stmt = $pdo->prepare("
    SELECT a.*,
           ar.status as reg_status,
           (SELECT COUNT(*) FROM activity_registrations WHERE activity_id = a.id AND status = 'registered') as current_count
    FROM activities a
    LEFT JOIN activity_registrations ar ON ar.activity_id = a.id AND ar.elderly_profile_id = ?
    WHERE a.activity_date >= CURDATE()
    ORDER BY a.activity_date ASC, a.start_time ASC
");
$stmt->execute([$profile_id ?? 0]);
$activities = $stmt->fetchAll();

// my registrations (upcoming)
$stmt = $pdo->prepare("
    SELECT a.*, ar.status as reg_status
    FROM activity_registrations ar
    JOIN activities a ON a.id = ar.activity_id
    WHERE ar.elderly_profile_id = ? AND ar.status = 'registered' AND a.activity_date >= CURDATE()
    ORDER BY a.activity_date ASC
");
$stmt->execute([$profile_id ?? 0]);
$my_activities = $stmt->fetchAll();

$pageTitle = 'Activities';
require_once '../includes/header.php';
?>

<div class="elderly-page">
    <div class="elderly-page-header">
        <h1>Activities</h1>
        <p class="text-muted mb-0">Browse and register for upcoming activities</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo sanitize($success); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo sanitize($error); ?></div>
    <?php endif; ?>

    <?php if (!empty($my_activities)): ?>
        <h2 class="elderly-section-title">My Upcoming Activities</h2>
        <?php foreach ($my_activities as $act): ?>
            <div class="elderly-card elderly-card--success">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="h5 mb-1"><?php echo sanitize($act['title']); ?></h3>
                        <p class="mb-1">
                            <?php echo date('l, M j', strtotime($act['activity_date'])); ?>
                            at <?php echo date('g:i A', strtotime($act['start_time'])); ?>
                            <?php if ($act['end_time']): ?>
                                – <?php echo date('g:i A', strtotime($act['end_time'])); ?>
                            <?php endif; ?>
                        </p>
                        <?php if ($act['location']): ?>
                            <small class="text-muted">Location: <?php echo sanitize($act['location']); ?></small>
                        <?php endif; ?>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="activity_id" value="<?php echo $act['id']; ?>">
                        <button type="submit" name="cancel" class="btn btn-outline-danger btn-elderly"
                                onclick="return confirm('Cancel your registration?')">
                            Cancel
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <h2 class="elderly-section-title">All Upcoming Activities</h2>

    <?php if (empty($activities)): ?>
        <div class="elderly-empty">
            <p>No upcoming activities right now.</p>
        </div>
    <?php else: ?>
        <?php foreach ($activities as $act): ?>
            <?php
                $is_registered = ($act['reg_status'] === 'registered');
                $is_full = ($act['max_participants'] !== null && (int)$act['current_count'] >= (int)$act['max_participants']);
            ?>
            <div class="elderly-card <?php echo $is_registered ? 'elderly-card--success' : 'elderly-card--highlight'; ?>">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="h5 mb-1"><?php echo sanitize($act['title']); ?></h3>
                        <?php if ($act['description']): ?>
                            <p class="mb-1"><?php echo sanitize($act['description']); ?></p>
                        <?php endif; ?>
                        <p class="mb-1 text-muted">
                            <?php echo date('l, M j', strtotime($act['activity_date'])); ?>
                            at <?php echo date('g:i A', strtotime($act['start_time'])); ?>
                            <?php if ($act['end_time']): ?>
                                – <?php echo date('g:i A', strtotime($act['end_time'])); ?>
                            <?php endif; ?>
                        </p>
                        <?php if ($act['location']): ?>
                            <small class="text-muted">Location: <?php echo sanitize($act['location']); ?></small>
                        <?php endif; ?>
                        <?php if ($act['max_participants']): ?>
                            <br><small class="text-muted">
                                Spots: <?php echo $act['current_count']; ?>/<?php echo $act['max_participants']; ?>
                                <?php if ($is_full): ?> (Full)<?php endif; ?>
                            </small>
                        <?php endif; ?>
                    </div>
                    <div class="text-end">
                        <?php if ($is_registered): ?>
                            <span class="status-badge status-registered">Registered</span>
                        <?php elseif ($is_full): ?>
                            <span class="status-badge status-cancelled">Full</span>
                        <?php else: ?>
                            <form method="POST">
                                <input type="hidden" name="activity_id" value="<?php echo $act['id']; ?>">
                                <button type="submit" name="register" class="btn btn-brand btn-elderly">
                                    Register
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
