<?php
require_once '../config/database.php';
require_once '../config/auth.php';

// only elderly residents (role_id = 1) can see this page
checkRole(1);

$user_id = (int)$_SESSION['user_id'];
$name    = $_SESSION['name'] ?? 'Resident';

// figure out greeting based on time of day
$hour = (int)date('G');
if ($hour < 12) {
    $greeting = "Good morning";
} elseif ($hour < 17) {
    $greeting = "Good afternoon";
} else {
    $greeting = "Good evening";
}

// get current room assignment
$stmt = $pdo->prepare("
    SELECT r.room_number, r.floor
    FROM room_assignments ra
    JOIN rooms r ON r.id = ra.room_id
    JOIN elderly_profiles ep ON ep.id = ra.elderly_profile_id
    WHERE ep.user_id = ?
      AND ra.end_date IS NULL
    ORDER BY ra.start_date DESC
    LIMIT 1
");
$stmt->execute([$user_id]);
$room = $stmt->fetch();

// get open/in-progress assistance requests
$stmt = $pdo->prepare("
    SELECT sr.id, sr.title, sr.priority, sr.status, sr.created_at
    FROM service_requests sr
    JOIN elderly_profiles ep ON ep.id = sr.elderly_profile_id
    WHERE ep.user_id = ?
      AND sr.status IN ('open', 'in_progress')
    ORDER BY sr.created_at DESC
    LIMIT 3
");
$stmt->execute([$user_id]);
$open_requests = $stmt->fetchAll();

// total open request count
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total
    FROM service_requests sr
    JOIN elderly_profiles ep ON ep.id = sr.elderly_profile_id
    WHERE ep.user_id = ?
      AND sr.status IN ('open', 'in_progress')
");
$stmt->execute([$user_id]);
$request_count = (int)$stmt->fetch()['total'];

$pageTitle = 'My Dashboard';
require_once '../includes/header.php';
?>

<!-- Welcome Banner -->
<div class="dashboard-welcome mb-4">
    <h1 class="h3 mb-0"><?php echo sanitize($greeting) . ', ' . sanitize($name); ?>!</h1>
    <p class="text-muted mb-0"><?php echo date('l, F j, Y'); ?></p>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <!-- Room -->
    <div class="col-sm-6 col-lg-3">
        <div class="dashboard-card h-100">
            <div class="dashboard-card-label">My Room</div>
            <?php if ($room): ?>
                <div class="dashboard-card-value"><?php echo sanitize($room['room_number']); ?></div>
                <small class="text-muted">Floor <?php echo sanitize($room['floor'] ?? '—'); ?></small>
            <?php else: ?>
                <div class="dashboard-card-value text-muted">—</div>
                <small class="text-muted">Not assigned yet</small>
            <?php endif; ?>
        </div>
    </div>

    <!-- Open Requests -->
    <div class="col-sm-6 col-lg-3">
        <div class="dashboard-card h-100">
            <div class="dashboard-card-label">Open Requests</div>
            <div class="dashboard-card-value"><?php echo $request_count; ?></div>
            <small class="text-muted">Pending assistance</small>
        </div>
    </div>

    <!-- Meals (coming soon) -->
    <div class="col-sm-6 col-lg-3">
        <div class="dashboard-card dashboard-card--muted h-100">
            <div class="dashboard-card-label">Today's Meals</div>
            <div class="dashboard-card-value text-muted">—</div>
            <small class="text-muted">Coming soon</small>
        </div>
    </div>

    <!-- Activities (coming soon) -->
    <div class="col-sm-6 col-lg-3">
        <div class="dashboard-card dashboard-card--muted h-100">
            <div class="dashboard-card-label">Next Activity</div>
            <div class="dashboard-card-value text-muted">—</div>
            <small class="text-muted">Coming soon</small>
        </div>
    </div>
</div>

<!-- Open Assistance Requests -->
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h5 mb-3">My Assistance Requests</h2>
                <?php if (count($open_requests) > 0): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($open_requests as $req): ?>
                            <li class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong><?php echo sanitize($req['title']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo date('M j, Y', strtotime($req['created_at'])); ?></small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-warning text-dark"><?php echo sanitize($req['priority']); ?></span>
                                        <br>
                                        <small class="text-muted"><?php echo sanitize($req['status']); ?></small>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted mb-0">No open requests right now.</p>
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
                    <a href="#" class="btn btn-brand">New Assistance Request</a>
                    <a href="#" class="btn btn-outline-secondary">My Profile</a>
                    <a href="<?php echo sanitize(public_url('logout.php')); ?>" class="btn btn-outline-danger">Log Out</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
