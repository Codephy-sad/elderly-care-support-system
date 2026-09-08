<?php
require_once '../config/database.php';
require_once '../config/auth.php';

// Only family members (role_id = 2) can see this page
checkRole(2);

$user_id = (int)$_SESSION['user_id'];
$name    = $_SESSION['name'] ?? 'Family Member';

// Get connected elderly profiles
$connections = getFamilyConnections($pdo, $user_id);

// If there are no connections
if (empty($connections)) {
    $pageTitle = 'Family Dashboard';
    require_once '../includes/header.php';
    ?>
    <div class="dashboard-welcome mb-4">
        <h1 class="h3">Welcome, <?php echo sanitize($name); ?></h1>
        <p class="text-muted">No elderly profile is connected to your account yet. Please contact administration.</p>
    </div>
    <?php
    require_once '../includes/footer.php';
    exit;
}

// Select the active elderly profile.
// Default to the first one if not set in query string.
$selected_elderly_id = isset($_GET['elderly_id']) ? (int)$_GET['elderly_id'] : (int)$connections[0]['elderly_profile_id'];

// Validate that the selected profile actually belongs to this family member
$active_connection = null;
foreach ($connections as $conn) {
    if ((int)$conn['elderly_profile_id'] === $selected_elderly_id) {
        $active_connection = $conn;
        break;
    }
}

// If invalid, fallback to the first connection
if (!$active_connection) {
    $active_connection = $connections[0];
    $selected_elderly_id = (int)$active_connection['elderly_profile_id'];
}

$elderly_name = $active_connection['elderly_name'];

// 1. Get today's meals count for this elderly
$meals_count = 0;
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total
    FROM meal_assignments ma
    JOIN meals m ON m.id = ma.meal_id
    JOIN menus mn ON mn.id = m.menu_id
    WHERE ma.elderly_profile_id = ? AND mn.meal_date = CURDATE()
");
$stmt->execute([$selected_elderly_id]);
$meals_count = (int)$stmt->fetch()['total'];

// 2. Get next activity today
$next_activity = null;
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
$stmt->execute([$selected_elderly_id]);
$next_activity = $stmt->fetch();

// 3. Open requests count
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM service_requests WHERE elderly_profile_id = ? AND status IN ('open', 'in_progress')");
$stmt->execute([$selected_elderly_id]);
$request_count = (int)$stmt->fetch()['total'];

// 4. Pending payment amount
$stmt = $pdo->prepare("SELECT SUM(amount) as total_due FROM invoices WHERE elderly_profile_id = ? AND status IN ('unpaid', 'partial')");
$stmt->execute([$selected_elderly_id]);
$pending_payment = $stmt->fetch()['total_due'];

// 5. Unread notifications count for family user
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$user_id]);
$unread_notifications = (int)$stmt->fetch()['total'];

// Figure out greeting based on time of day
$hour = (int)date('G');
if ($hour < 12) {
    $greeting = "Good morning";
} elseif ($hour < 17) {
    $greeting = "Good afternoon";
} else {
    $greeting = "Good evening";
}

$pageTitle = 'Family Dashboard';
require_once '../includes/header.php';
?>

<!-- Welcome Banner -->
<div class="dashboard-welcome mb-4 position-relative">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div class="mb-3 mb-md-0">
            <h1 class="h3 mb-1"><?php echo sanitize($greeting) . ', ' . sanitize($name); ?>!</h1>
            <p class="text-muted mb-0"><?php echo date('l, F j, Y'); ?></p>
        </div>
        
        <div class="d-flex align-items-center gap-3">
            <!-- Elderly Selector -->
            <?php if (count($connections) > 1): ?>
            <form action="" method="GET" class="d-inline-flex align-items-center">
                <label for="elderly_id" class="me-2 text-muted small">Viewing:</label>
                <select name="elderly_id" id="elderly_id" class="form-select form-select-sm border-0 shadow-sm" onchange="this.form.submit()">
                    <?php foreach ($connections as $conn): ?>
                        <option value="<?php echo $conn['elderly_profile_id']; ?>" <?php echo $conn['elderly_profile_id'] == $selected_elderly_id ? 'selected' : ''; ?>>
                            <?php echo sanitize($conn['elderly_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            <?php else: ?>
                <span class="badge bg-light text-dark shadow-sm px-3 py-2 border">
                    Viewing: <strong><?php echo sanitize($elderly_name); ?></strong>
                </span>
            <?php endif; ?>

            <?php if ($unread_notifications > 0): ?>
                <a href="notifications.php" class="btn btn-warning position-relative btn-sm">
                    🔔 <span class="d-none d-md-inline">Notifications</span>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?php echo $unread_notifications; ?>
                    </span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <!-- Meals -->
    <div class="col-sm-6 col-lg-3">
        <a href="meals.php?elderly_id=<?php echo $selected_elderly_id; ?>" class="text-decoration-none">
            <div class="dashboard-card h-100 <?php echo $meals_count > 0 ? '' : 'dashboard-card--muted'; ?>">
                <div class="dashboard-card-label">Today's Meals</div>
                <div class="dashboard-card-value text-dark"><?php echo $meals_count; ?></div>
                <small class="text-muted">Assigned for <?php echo sanitize($elderly_name); ?></small>
            </div>
        </a>
    </div>

    <!-- Next Activity -->
    <div class="col-sm-6 col-lg-3">
        <a href="activities.php?elderly_id=<?php echo $selected_elderly_id; ?>" class="text-decoration-none">
            <div class="dashboard-card h-100 <?php echo $next_activity ? '' : 'dashboard-card--muted'; ?>">
                <div class="dashboard-card-label">Next Activity</div>
                <?php if ($next_activity): ?>
                    <div class="dashboard-card-value text-dark" style="font-size: 1.25rem; margin-top: 0.5rem; margin-bottom: 0.25rem;">
                        <?php echo sanitize($next_activity['title']); ?>
                    </div>
                    <small class="text-muted">at <?php echo date('g:i A', strtotime($next_activity['start_time'])); ?></small>
                <?php else: ?>
                    <div class="dashboard-card-value text-muted">—</div>
                    <small class="text-muted">No upcoming activities</small>
                <?php endif; ?>
            </div>
        </a>
    </div>

    <!-- Open Requests -->
    <div class="col-sm-6 col-lg-3">
        <a href="requests.php?elderly_id=<?php echo $selected_elderly_id; ?>" class="text-decoration-none">
            <div class="dashboard-card h-100 <?php echo $request_count > 0 ? '' : 'dashboard-card--muted'; ?>">
                <div class="dashboard-card-label">Open Requests</div>
                <div class="dashboard-card-value text-dark"><?php echo $request_count; ?></div>
                <small class="text-muted">Pending resolution</small>
            </div>
        </a>
    </div>

    <!-- Payments -->
    <div class="col-sm-6 col-lg-3">
        <a href="payments.php?elderly_id=<?php echo $selected_elderly_id; ?>" class="text-decoration-none">
            <div class="dashboard-card h-100 <?php echo $pending_payment > 0 ? '' : 'dashboard-card--muted'; ?>">
                <div class="dashboard-card-label">Pending Payments</div>
                <?php if ($pending_payment > 0): ?>
                    <div class="dashboard-card-value text-danger">$<?php echo number_format($pending_payment, 2); ?></div>
                    <small class="text-muted">Due invoices</small>
                <?php else: ?>
                    <div class="dashboard-card-value text-success">$0.00</div>
                    <small class="text-muted">All clear!</small>
                <?php endif; ?>
            </div>
        </a>
    </div>
</div>

<!-- Main Content Row -->
<div class="row g-3">
    <!-- Quick Overview -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5 mb-4">Quick Overview</h2>
                <p>Welcome to the Family Portal. This dashboard provides a snapshot of <strong><?php echo sanitize($elderly_name); ?></strong>'s day.</p>
                <p>Use the navigation menu or quick actions to view detailed updates on meals, medications, upcoming activities, and more.</p>
                
                <div class="d-flex flex-wrap gap-2 mt-4">
                    <a href="daily_updates.php?elderly_id=<?php echo $selected_elderly_id; ?>" class="btn btn-outline-brand">Daily Timeline</a>
                    <a href="visits.php?elderly_id=<?php echo $selected_elderly_id; ?>" class="btn btn-outline-secondary">Schedule a Visit</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5 mb-3">Quick Actions</h2>
                <div class="d-grid gap-2">
                    <a href="elderly.php?elderly_id=<?php echo $selected_elderly_id; ?>" class="btn btn-outline-secondary text-start">👤 View Profile</a>
                    <a href="messages.php" class="btn btn-outline-secondary text-start">💬 Send a Message</a>
                    <a href="request_new.php?elderly_id=<?php echo $selected_elderly_id; ?>" class="btn btn-outline-secondary text-start">📝 New Request</a>
                    <a href="memories.php?elderly_id=<?php echo $selected_elderly_id; ?>" class="btn btn-outline-secondary text-start">📸 Photos / Memories</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
