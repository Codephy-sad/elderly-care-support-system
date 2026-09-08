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

// Handle Activity Registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $activity_id = (int)$_POST['activity_id'];
    
    // Check if activity exists and is in the future
    $stmt = $pdo->prepare("SELECT * FROM activities WHERE id = ? AND CONCAT(activity_date, ' ', start_time) > NOW()");
    $stmt->execute([$activity_id]);
    $activity = $stmt->fetch();
    
    if ($activity) {
        // Check if already registered
        $stmt = $pdo->prepare("SELECT id FROM activity_registrations WHERE activity_id = ? AND elderly_profile_id = ?");
        $stmt->execute([$activity_id, $selected_elderly_id]);
        if (!$stmt->fetch()) {
            // Register
            $stmt = $pdo->prepare("INSERT INTO activity_registrations (activity_id, elderly_profile_id, status) VALUES (?, ?, 'registered')");
            if ($stmt->execute([$activity_id, $selected_elderly_id])) {
                $success_msg = "Successfully registered " . sanitize($elderly_name) . " for " . sanitize($activity['title']) . ".";
            } else {
                $error_msg = "Failed to register for the activity.";
            }
        } else {
            $error_msg = "Already registered for this activity.";
        }
    } else {
        $error_msg = "Invalid or expired activity.";
    }
}

// Handle Activity Unregistration (Cancel)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    $activity_id = (int)$_POST['activity_id'];
    
    $stmt = $pdo->prepare("
        UPDATE activity_registrations 
        SET status = 'cancelled' 
        WHERE activity_id = ? AND elderly_profile_id = ? AND status = 'registered'
    ");
    if ($stmt->execute([$activity_id, $selected_elderly_id]) && $stmt->rowCount() > 0) {
        $success_msg = "Successfully cancelled registration for the activity.";
    } else {
        $error_msg = "Failed to cancel registration (it may have already happened or is invalid).";
    }
}

// 1. Get Upcoming Activities
$stmt = $pdo->prepare("
    SELECT a.*, 
           ar.status as registration_status
    FROM activities a
    LEFT JOIN activity_registrations ar ON ar.activity_id = a.id AND ar.elderly_profile_id = ?
    WHERE CONCAT(a.activity_date, ' ', a.start_time) >= NOW()
    ORDER BY a.activity_date ASC, a.start_time ASC
    LIMIT 10
");
$stmt->execute([$selected_elderly_id]);
$upcoming_activities = $stmt->fetchAll();

// 2. Get Past Attended Activities
$stmt = $pdo->prepare("
    SELECT a.*, ar.status as registration_status
    FROM activities a
    JOIN activity_registrations ar ON ar.activity_id = a.id
    WHERE ar.elderly_profile_id = ? AND CONCAT(a.activity_date, ' ', a.start_time) < NOW() AND ar.status = 'attended'
    ORDER BY a.activity_date DESC, a.start_time DESC
    LIMIT 5
");
$stmt->execute([$selected_elderly_id]);
$past_activities = $stmt->fetchAll();

$pageTitle = 'Activities - ' . sanitize($elderly_name);
require_once '../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
    <div>
        <h1 class="h3 mb-1">Activities</h1>
        <p class="text-muted mb-0">Events and engagements for <?php echo sanitize($elderly_name); ?></p>
    </div>
    <div class="mt-3 mt-md-0 d-flex gap-2">
        <form action="" method="GET" class="d-inline-flex">
            <input type="hidden" name="elderly_id" value="<?php echo $selected_elderly_id; ?>">
            <button type="button" class="btn btn-outline-secondary btn-sm" disabled>Current View</button>
        </form>
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

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5 mb-4 border-bottom pb-2">Upcoming Activities</h2>
                
                <?php if (count($upcoming_activities) > 0): ?>
                    <div class="row g-3">
                        <?php foreach ($upcoming_activities as $activity): ?>
                            <?php 
                                $is_registered = ($activity['registration_status'] === 'registered'); 
                                $is_cancelled = ($activity['registration_status'] === 'cancelled');
                            ?>
                            <div class="col-md-6">
                                <div class="card h-100 border rounded-3 <?php echo $is_registered ? 'border-primary shadow-sm' : ''; ?>">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h3 class="h6 fw-bold mb-0"><?php echo sanitize($activity['title']); ?></h3>
                                            <?php if ($is_registered): ?>
                                                <span class="badge bg-primary rounded-pill">Registered</span>
                                            <?php elseif ($is_cancelled): ?>
                                                <span class="badge bg-secondary rounded-pill">Cancelled</span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <p class="small text-muted mb-3"><?php echo sanitize($activity['description'] ?? 'No description provided.'); ?></p>
                                        
                                        <ul class="list-unstyled small text-muted mb-3">
                                            <li class="mb-1"><i class="bi bi-calendar-event me-2"></i> <?php echo date('M j, Y', strtotime($activity['activity_date'])); ?></li>
                                            <li class="mb-1">
                                                <i class="bi bi-clock me-2"></i> 
                                                <?php echo date('g:i A', strtotime($activity['start_time'])); ?>
                                                <?php if ($activity['end_time']) echo ' - ' . date('g:i A', strtotime($activity['end_time'])); ?>
                                            </li>
                                            <li class="mb-0"><i class="bi bi-geo-alt me-2"></i> <?php echo sanitize($activity['location'] ?? 'TBD'); ?></li>
                                        </ul>
                                    </div>
                                    <div class="card-footer bg-transparent border-top-0 pt-0">
                                        <?php if ($is_registered): ?>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this registration?');">
                                                <input type="hidden" name="action" value="cancel">
                                                <input type="hidden" name="activity_id" value="<?php echo $activity['id']; ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm w-100">Cancel Registration</button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="register">
                                                <input type="hidden" name="activity_id" value="<?php echo $activity['id']; ?>">
                                                <button type="submit" class="btn btn-outline-primary btn-sm w-100">Register</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No upcoming activities scheduled at the moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h5 mb-4 border-bottom pb-2">Recently Attended</h2>
                
                <?php if (count($past_activities) > 0): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($past_activities as $past): ?>
                            <li class="list-group-item px-0 py-3">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1 fw-bold"><?php echo sanitize($past['title']); ?></h6>
                                    <small class="text-muted"><?php echo date('M j', strtotime($past['activity_date'])); ?></small>
                                </div>
                                <p class="mb-1 small text-muted">
                                    <?php echo sanitize($past['description'] ?? ''); ?>
                                </p>
                                <small class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Attended</small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted mb-0">No past activity attendance recorded yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
