<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../config/database.php';
require_once '../config/auth.php';

// only elderly residents (role_id = 1) can see this page
checkRole(1);

$user_id = (int)$_SESSION['user_id'];
$name    = $_SESSION['name'] ?? 'Resident';

// get profile_id safely
$profile_id = getElderlyProfileId($pdo, $user_id);

// figure out greeting based on time of day
$hour = (int)date('G');
if ($hour < 12) {
    $greeting = "Good morning";
} elseif ($hour < 17) {
    $greeting = "Good afternoon";
} else {
    $greeting = "Good evening";
}

// 1. Get current room assignment
$room = null;
if ($profile_id) {
    $stmt = $pdo->prepare("
        SELECT r.room_number, r.floor
        FROM room_assignments ra
        JOIN rooms r ON r.id = ra.room_id
        WHERE ra.elderly_profile_id = ?
          AND ra.end_date IS NULL
        ORDER BY ra.start_date DESC
        LIMIT 1
    ");
    $stmt->execute([$profile_id]);
    $room = $stmt->fetch();
}

// 2. Get open/in-progress assistance requests
$open_requests = [];
$request_count = 0;
if ($profile_id) {
    $stmt = $pdo->prepare("
        SELECT id, title, priority, status, created_at
        FROM service_requests
        WHERE elderly_profile_id = ?
          AND status IN ('open', 'in_progress')
        ORDER BY created_at DESC
        LIMIT 3
    ");
    $stmt->execute([$profile_id]);
    $open_requests = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM service_requests WHERE elderly_profile_id = ? AND status IN ('open', 'in_progress')");
    $stmt->execute([$profile_id]);
    $request_count = (int)$stmt->fetch()['total'];
}

// 3. Get today's meals count
$meals_count = 0;
if ($profile_id) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total
        FROM meal_assignments ma
        JOIN meals m ON m.id = ma.meal_id
        JOIN menus mn ON mn.id = m.menu_id
        WHERE ma.elderly_profile_id = ? AND mn.meal_date = CURDATE()
    ");
    $stmt->execute([$profile_id]);
    $meals_count = (int)$stmt->fetch()['total'];
}

// 4. Get next activity today
$next_activity = null;
if ($profile_id) {
    $stmt = $pdo->prepare("
        SELECT a.title, a.start_time
        FROM activity_registrations ar
        JOIN activities a ON a.id = ar.activity_id
        WHERE ar.elderly_profile_id = ?
          AND ar.status = 'registered'
          AND a.activity_date = CURDATE()
          AND a.start_time >= CURTIME()
        ORDER BY a.start_time ASC
        LIMIT 1
    ");
    $stmt->execute([$profile_id]);
    $next_activity = $stmt->fetch();
}

// 5. Unread notifications count
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$user_id]);
$unread_notifications = (int)$stmt->fetch()['total'];

$pageTitle = 'My Dashboard';
require_once '../includes/header.php';
?>

<!-- Welcome Banner -->
<div class="dashboard-welcome mb-4 position-relative">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0"><?php echo sanitize($greeting) . ', ' . sanitize($name); ?>!</h1>
            <p class="text-muted mb-0"><?php echo date('l, F j, Y'); ?></p>
        </div>
        <?php if ($unread_notifications > 0): ?>
            <a href="notifications.php" class="btn btn-warning position-relative">
                🔔 Notifications
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?php echo $unread_notifications; ?>
                </span>
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <!-- Room -->
    <div class="col-sm-6 col-lg-3">
        <a href="room_change.php" class="text-decoration-none">
            <div class="dashboard-card h-100">
                <div class="dashboard-card-label">My Room</div>
                <?php if ($room): ?>
                    <div class="dashboard-card-value text-dark"><?php echo sanitize($room['room_number']); ?></div>
                    <small class="text-muted">Floor <?php echo sanitize($room['floor'] ?? '—'); ?></small>
                <?php else: ?>
                    <div class="dashboard-card-value text-muted">—</div>
                    <small class="text-muted">Not assigned yet</small>
                <?php endif; ?>
            </div>
        </a>
    </div>

    <!-- Meals -->
    <div class="col-sm-6 col-lg-3">
        <a href="meals.php" class="text-decoration-none">
            <div class="dashboard-card h-100 <?php echo $meals_count > 0 ? '' : 'dashboard-card--muted'; ?>">
                <div class="dashboard-card-label">Today's Meals</div>
                <div class="dashboard-card-value text-dark"><?php echo $meals_count; ?></div>
                <small class="text-muted">Assigned for today</small>
            </div>
        </a>
    </div>

    <!-- Next Activity -->
    <div class="col-sm-6 col-lg-3">
        <a href="activities.php" class="text-decoration-none">
            <div class="dashboard-card h-100 <?php echo $next_activity ? '' : 'dashboard-card--muted'; ?>">
                <div class="dashboard-card-label">Next Activity</div>
                <?php if ($next_activity): ?>
                    <div class="dashboard-card-value text-dark" style="font-size: 1.25rem; margin-top: 0.5rem; margin-bottom: 0.25rem;">
                        <?php echo sanitize($next_activity['title']); ?>
                    </div>
                    <small class="text-muted">at <?php echo date('g:i A', strtotime($next_activity['start_time'])); ?></small>
                <?php else: ?>
                    <div class="dashboard-card-value text-muted">—</div>
                    <small class="text-muted">Nothing scheduled today</small>
                <?php endif; ?>
            </div>
        </a>
    </div>

    <!-- Open Requests -->
    <div class="col-sm-6 col-lg-3">
        <a href="requests.php" class="text-decoration-none">
            <div class="dashboard-card h-100 <?php echo $request_count > 0 ? '' : 'dashboard-card--muted'; ?>">
                <div class="dashboard-card-label">Open Requests</div>
                <div class="dashboard-card-value text-dark"><?php echo $request_count; ?></div>
                <small class="text-muted">Pending assistance</small>
            </div>
        </a>
    </div>
</div>

<!-- Main Content Row -->
<div class="row g-3">
    <!-- Recent Requests -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 mb-0">My Assistance Requests</h2>
                    <a href="requests.php" class="btn btn-sm btn-outline-secondary btn-elderly">View All</a>
                </div>
                
                <?php if (count($open_requests) > 0): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($open_requests as $req): ?>
                            <li class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>
                                            <a href="request_view.php?id=<?php echo $req['id']; ?>" class="text-decoration-none text-dark">
                                                <?php echo sanitize($req['title']); ?>
                                            </a>
                                        </strong>
                                        <br>
                                        <small class="text-muted"><?php echo date('M j, Y', strtotime($req['created_at'])); ?></small>
                                    </div>
                                    <div class="text-end">
                                        <span class="status-badge status-<?php echo sanitize($req['status']); ?>">
                                            <?php echo sanitize(ucfirst(str_replace('_', ' ', $req['status']))); ?>
                                        </span>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="elderly-empty">
                        <p class="mb-0">No open requests right now.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5 mb-3">Quick Actions</h2>
                <div class="d-grid gap-2">
                    <a href="emergency.php" class="btn btn-danger btn-elderly">🚨 Emergency Help</a>
                    <a href="request_new.php" class="btn btn-brand btn-elderly">Ask for Assistance</a>
                    <a href="medications.php" class="btn btn-outline-secondary btn-elderly">My Medications</a>
                    <a href="messages.php" class="btn btn-outline-secondary btn-elderly">Messages</a>
                    <a href="profile.php" class="btn btn-outline-secondary btn-elderly">My Profile</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
