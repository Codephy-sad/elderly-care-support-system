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

// 1. Get detailed elderly profile
$stmt = $pdo->prepare("SELECT * FROM elderly_profiles WHERE id = ?");
$stmt->execute([$selected_elderly_id]);
$profile = $stmt->fetch();

// Calculate age if DOB exists
$age = 'Unknown';
if (!empty($profile['date_of_birth'])) {
    $dob = new DateTime($profile['date_of_birth']);
    $now = new DateTime();
    $age = $now->diff($dob)->y;
}

// 2. Get current room assignment
$stmt = $pdo->prepare("
    SELECT r.room_number, r.floor, r.room_type
    FROM room_assignments ra
    JOIN rooms r ON r.id = ra.room_id
    WHERE ra.elderly_profile_id = ?
      AND ra.end_date IS NULL
    ORDER BY ra.start_date DESC
    LIMIT 1
");
$stmt->execute([$selected_elderly_id]);
$room = $stmt->fetch();

// 3. Get active care plans
$stmt = $pdo->prepare("
    SELECT title, description, category
    FROM care_plans
    WHERE elderly_profile_id = ? AND active = 1
    ORDER BY created_at DESC
");
$stmt->execute([$selected_elderly_id]);
$care_plans = $stmt->fetchAll();

$pageTitle = 'My Elderly - ' . sanitize($elderly_name);
require_once '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><?php echo sanitize($elderly_name); ?>'s Profile</h1>
    <a href="index.php?elderly_id=<?php echo $selected_elderly_id; ?>" class="btn btn-outline-secondary btn-sm">
        &larr; Back to Dashboard
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <!-- Basic Information Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5 mb-3 border-bottom pb-2">Basic Information</h2>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><strong>Age:</strong> <?php echo $age; ?></li>
                    <li class="mb-2"><strong>Gender:</strong> <?php echo sanitize($profile['gender'] ?? 'Not specified'); ?></li>
                    <li class="mb-2"><strong>Blood Group:</strong> <?php echo sanitize($profile['blood_group'] ?? 'Not specified'); ?></li>
                    <li class="mb-0"><strong>Relationship:</strong> <?php echo sanitize($active_connection['relationship']); ?></li>
                </ul>
            </div>
        </div>

        <!-- Room Information Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h5 mb-3 border-bottom pb-2">Room Assignment</h2>
                <?php if ($room): ?>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><strong>Room:</strong> <?php echo sanitize($room['room_number']); ?></li>
                        <li class="mb-2"><strong>Floor:</strong> <?php echo sanitize($room['floor'] ?? 'N/A'); ?></li>
                        <li class="mb-0"><strong>Type:</strong> <?php echo sanitize(ucfirst($room['room_type'])); ?></li>
                    </ul>
                <?php else: ?>
                    <p class="text-muted mb-0">No active room assignment.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <!-- Health & Medical Notes -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5 mb-3 border-bottom pb-2">Health & Diet</h2>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <strong>Allergies:</strong>
                        <p class="mb-0 text-muted">
                            <?php echo nl2br(sanitize($profile['allergies'] ?? 'None recorded')); ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <strong>Dietary Requirements:</strong>
                        <p class="mb-0 text-muted">
                            <?php echo nl2br(sanitize($profile['dietary_requirements'] ?? 'Standard diet')); ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <strong>Medical Conditions (Overview):</strong>
                        <p class="mb-0 text-muted">
                            <?php echo nl2br(sanitize($profile['medical_conditions'] ?? 'None recorded')); ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <strong>Mobility Notes:</strong>
                        <p class="mb-0 text-muted">
                            <?php echo nl2br(sanitize($profile['mobility_notes'] ?? 'Independent')); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Care Plans -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h5 mb-3 border-bottom pb-2">Active Care & Support Plans</h2>
                <?php if (count($care_plans) > 0): ?>
                    <div class="accordion" id="carePlanAccordion">
                        <?php foreach ($care_plans as $index => $plan): ?>
                            <div class="accordion-item border-0 border-bottom">
                                <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                                    <button class="accordion-button collapsed px-0 bg-transparent fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>" aria-expanded="false" aria-controls="collapse<?php echo $index; ?>">
                                        <span class="badge bg-secondary me-2"><?php echo sanitize($plan['category'] ?? 'General'); ?></span>
                                        <?php echo sanitize($plan['title']); ?>
                                    </button>
                                </h2>
                                <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $index; ?>" data-bs-parent="#carePlanAccordion">
                                    <div class="accordion-body px-0 text-muted">
                                        <?php echo nl2br(sanitize($plan['description'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">No active care plans.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
