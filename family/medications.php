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

// Determine filter date (Default: today)
$filter_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
if (!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $filter_date)) {
    $filter_date = date('Y-m-d');
}

// Get active medications and their event status for the selected date
$stmt = $pdo->prepare("
    SELECT m.id, m.name, m.dosage, m.frequency, m.time_slot, m.instructions,
           me.status as event_status, me.taken_at, me.notes
    FROM medications m
    LEFT JOIN medication_events me ON me.medication_id = m.id AND me.event_date = ?
    WHERE m.elderly_profile_id = ? AND m.active = 1
    ORDER BY m.time_slot ASC
");
$stmt->execute([$filter_date, $selected_elderly_id]);
$medications = $stmt->fetchAll();

$pageTitle = 'Medications - ' . sanitize($elderly_name);
require_once '../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
    <div>
        <h1 class="h3 mb-1">Medication Status</h1>
        <p class="text-muted mb-0">Authorized schedule for <?php echo sanitize($elderly_name); ?></p>
    </div>
    <div class="mt-3 mt-md-0 d-flex gap-2">
        <form action="" method="GET" class="d-inline-flex">
            <input type="hidden" name="elderly_id" value="<?php echo $selected_elderly_id; ?>">
            <input type="date" name="date" class="form-control form-control-sm me-2 shadow-sm border-0" value="<?php echo sanitize($filter_date); ?>" onchange="this.form.submit()">
        </form>
        <a href="index.php?elderly_id=<?php echo $selected_elderly_id; ?>" class="btn btn-outline-secondary btn-sm">
            &larr; Dashboard
        </a>
    </div>
</div>

<div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4">
    <i class="bi bi-info-circle fs-4 me-3"></i>
    <div>
        <strong>Notice:</strong> This is a read-only view of the authorized medication schedule. Only medical staff and caregivers can modify prescriptions or administer doses.
    </div>
</div>

<div class="row g-4">
    <?php if (count($medications) > 0): ?>
        <?php foreach ($medications as $med): ?>
            <?php
            // Badge color based on event status
            $status = $med['event_status'] ?? 'pending';
            $badge_class = 'bg-secondary';
            if ($status === 'taken') $badge_class = 'bg-success';
            elseif ($status === 'missed') $badge_class = 'bg-danger';
            elseif ($status === 'skipped') $badge_class = 'bg-warning text-dark';
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                        <h6 class="text-primary fw-bold mb-0">
                            <i class="bi bi-clock me-1"></i> <?php echo date('g:i A', strtotime($med['time_slot'])); ?>
                        </h6>
                        <span class="badge <?php echo $badge_class; ?> rounded-pill">
                            <?php echo ucfirst(sanitize($status)); ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title fw-bold text-dark mb-1"><?php echo sanitize($med['name']); ?></h5>
                        <p class="text-muted small mb-3">
                            <strong>Dosage:</strong> <?php echo sanitize($med['dosage']); ?> <br>
                            <strong>Frequency:</strong> <?php echo sanitize($med['frequency']); ?>
                        </p>
                        
                        <?php if (!empty($med['instructions'])): ?>
                            <div class="p-2 bg-light rounded text-muted small mb-3">
                                <strong>Instructions:</strong> <?php echo sanitize($med['instructions']); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($status === 'taken' && $med['taken_at']): ?>
                            <hr class="my-2">
                            <div class="text-success small fw-bold">
                                Administered at <?php echo date('g:i A', strtotime($med['taken_at'])); ?>
                            </div>
                        <?php elseif (in_array($status, ['missed', 'skipped']) && !empty($med['notes'])): ?>
                            <hr class="my-2">
                            <div class="text-danger small">
                                <strong>Note:</strong> <?php echo sanitize($med['notes']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm text-center py-5">
                <div class="card-body">
                    <h4 class="text-muted fw-normal">No Medications</h4>
                    <p class="text-muted mb-0">No active medications are recorded for <?php echo sanitize($elderly_name); ?>.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
