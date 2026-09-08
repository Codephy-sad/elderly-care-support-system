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

// Fetch requests
$stmt = $pdo->prepare("
    SELECT * FROM service_requests 
    WHERE elderly_profile_id = ? 
    ORDER BY FIELD(status, 'open', 'in_progress', 'resolved', 'closed'), created_at DESC
");
$stmt->execute([$selected_elderly_id]);
$requests = $stmt->fetchAll();

$pageTitle = 'Requests - ' . sanitize($elderly_name);
require_once '../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
    <div>
        <h1 class="h3 mb-1">Service Requests</h1>
        <p class="text-muted mb-0">View and manage requests for <?php echo sanitize($elderly_name); ?></p>
    </div>
    <div class="mt-3 mt-md-0 d-flex gap-2">
        <a href="request_new.php?elderly_id=<?php echo $selected_elderly_id; ?>" class="btn btn-brand btn-sm">
            + New Request
        </a>
        <a href="index.php?elderly_id=<?php echo $selected_elderly_id; ?>" class="btn btn-outline-secondary btn-sm">
            &larr; Dashboard
        </a>
    </div>
</div>

<?php if (isset($_GET['success']) && $_GET['success'] === 'created'): ?>
    <div class="alert alert-success border-0 shadow-sm">Request submitted successfully.</div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Title / ID</th>
                        <th>Type</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($requests) > 0): ?>
                        <?php foreach ($requests as $req): ?>
                            <?php
                                $status = $req['status'];
                                $badge_class = 'bg-secondary';
                                if ($status === 'open') $badge_class = 'bg-warning text-dark';
                                elseif ($status === 'in_progress') $badge_class = 'bg-primary';
                                elseif ($status === 'resolved') $badge_class = 'bg-success';
                                
                                $priority_color = 'text-secondary';
                                if ($req['priority'] === 'high') $priority_color = 'text-danger';
                                elseif ($req['priority'] === 'medium') $priority_color = 'text-warning';
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark"><?php echo sanitize($req['title']); ?></div>
                                    <div class="text-muted small">#<?php echo $req['id']; ?></div>
                                </td>
                                <td><?php echo ucfirst(sanitize($req['request_type'])); ?></td>
                                <td>
                                    <span class="<?php echo $priority_color; ?> fw-bold">
                                        <?php echo ucfirst(sanitize($req['priority'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo $badge_class; ?> rounded-pill">
                                        <?php echo ucfirst(str_replace('_', ' ', sanitize($status))); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($req['created_at'])); ?></td>
                                <td class="text-end pe-4">
                                    <a href="request_view.php?id=<?php echo $req['id']; ?>&elderly_id=<?php echo $selected_elderly_id; ?>" class="btn btn-sm btn-outline-secondary">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                No requests found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
