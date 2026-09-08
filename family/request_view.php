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

$request_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$request_id) {
    header('Location: requests.php');
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
    header('Location: requests.php');
    exit;
}
$elderly_name = $active_connection['elderly_name'];

// Get Request Details
$stmt = $pdo->prepare("
    SELECT sr.*, u.name as requested_by_name 
    FROM service_requests sr
    LEFT JOIN users u ON u.id = sr.requested_by
    WHERE sr.id = ? AND sr.elderly_profile_id = ?
");
$stmt->execute([$request_id, $selected_elderly_id]);
$request = $stmt->fetch();

if (!$request) {
    header('Location: requests.php?elderly_id=' . $selected_elderly_id);
    exit;
}

$status = $request['status'];
$badge_class = 'bg-secondary';
if ($status === 'open') $badge_class = 'bg-warning text-dark';
elseif ($status === 'in_progress') $badge_class = 'bg-primary';
elseif ($status === 'resolved') $badge_class = 'bg-success';

$priority_color = 'text-secondary';
if ($request['priority'] === 'high') $priority_color = 'text-danger';
elseif ($request['priority'] === 'medium') $priority_color = 'text-warning';

$pageTitle = 'Request Details - ' . sanitize($elderly_name);
require_once '../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
    <div>
        <h1 class="h3 mb-1">Request #<?php echo $request['id']; ?></h1>
        <p class="text-muted mb-0">Submitted on <?php echo date('M j, Y \a\t g:i A', strtotime($request['created_at'])); ?></p>
    </div>
    <div class="mt-3 mt-md-0 d-flex gap-2">
        <a href="requests.php?elderly_id=<?php echo $selected_elderly_id; ?>" class="btn btn-outline-secondary btn-sm">
            &larr; Back to Requests
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-4 border-bottom pb-3">
                    <h2 class="h4 mb-0 fw-bold"><?php echo sanitize($request['title']); ?></h2>
                    <span class="badge <?php echo $badge_class; ?> fs-6 rounded-pill">
                        <?php echo ucfirst(str_replace('_', ' ', sanitize($status))); ?>
                    </span>
                </div>
                
                <h5 class="h6 text-muted fw-bold mb-2">Description</h5>
                <div class="p-3 bg-light rounded text-dark mb-4" style="min-height: 100px;">
                    <?php echo nl2br(sanitize($request['description'] ?? 'No description provided.')); ?>
                </div>
                
                <?php if ($request['resolution_notes']): ?>
                    <h5 class="h6 text-success fw-bold mb-2"><i class="bi bi-check-circle-fill me-2"></i>Resolution Notes</h5>
                    <div class="p-3 bg-success bg-opacity-10 rounded text-dark border border-success border-opacity-25">
                        <?php echo nl2br(sanitize($request['resolution_notes'])); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h3 class="h5 mb-4 border-bottom pb-2">Request Info</h3>
                
                <ul class="list-unstyled mb-0">
                    <li class="mb-3">
                        <span class="text-muted d-block small fw-bold text-uppercase">Type</span>
                        <span class="fs-6"><?php echo ucfirst(sanitize($request['request_type'])); ?></span>
                    </li>
                    <li class="mb-3">
                        <span class="text-muted d-block small fw-bold text-uppercase">Priority</span>
                        <span class="fs-6 <?php echo $priority_color; ?> fw-bold"><?php echo ucfirst(sanitize($request['priority'])); ?></span>
                    </li>
                    <li class="mb-3">
                        <span class="text-muted d-block small fw-bold text-uppercase">Requested By</span>
                        <span class="fs-6"><?php echo sanitize($request['requested_by_name'] ?? 'System/Unknown'); ?></span>
                    </li>
                    <li class="mb-0">
                        <span class="text-muted d-block small fw-bold text-uppercase">Last Updated</span>
                        <span class="fs-6"><?php echo date('M j, Y g:i A', strtotime($request['updated_at'])); ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
