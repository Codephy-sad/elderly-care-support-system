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
// Validate date format briefly
if (!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $filter_date)) {
    $filter_date = date('Y-m-d');
}

$timeline = [];

// 1. Meals Served
$stmt = $pdo->prepare("
    SELECT m.title, m.meal_type, md.status, md.served_at
    FROM meal_distributions md
    JOIN meals m ON m.id = md.meal_id
    JOIN menus mn ON mn.id = m.menu_id
    WHERE md.elderly_profile_id = ? AND mn.meal_date = ? AND md.status = 'served' AND md.served_at IS NOT NULL
");
$stmt->execute([$selected_elderly_id, $filter_date]);
while ($row = $stmt->fetch()) {
    $timeline[] = [
        'time' => $row['served_at'],
        'type' => 'meal',
        'title' => ucfirst($row['meal_type']) . ' Served',
        'desc' => $row['title'],
        'icon' => '🍽️'
    ];
}

// 2. Medications Taken
$stmt = $pdo->prepare("
    SELECT m.name, me.status, me.taken_at
    FROM medication_events me
    JOIN medications m ON m.id = me.medication_id
    WHERE m.elderly_profile_id = ? AND me.event_date = ? AND me.status = 'taken' AND me.taken_at IS NOT NULL
");
$stmt->execute([$selected_elderly_id, $filter_date]);
while ($row = $stmt->fetch()) {
    $timeline[] = [
        'time' => $row['taken_at'],
        'type' => 'medication',
        'title' => 'Medication Taken',
        'desc' => $row['name'],
        'icon' => '💊'
    ];
}

// 3. Activities
$stmt = $pdo->prepare("
    SELECT a.title, a.start_time, ar.status, a.activity_date
    FROM activity_registrations ar
    JOIN activities a ON a.id = ar.activity_id
    WHERE ar.elderly_profile_id = ? AND a.activity_date = ? AND ar.status = 'attended'
");
$stmt->execute([$selected_elderly_id, $filter_date]);
while ($row = $stmt->fetch()) {
    $datetime = $row['activity_date'] . ' ' . $row['start_time'];
    $timeline[] = [
        'time' => $datetime,
        'type' => 'activity',
        'title' => 'Attended Activity',
        'desc' => $row['title'],
        'icon' => '🎨'
    ];
}

// 4. Service Requests (Created or Updated on this date)
$stmt = $pdo->prepare("
    SELECT title, status, updated_at
    FROM service_requests
    WHERE elderly_profile_id = ? AND DATE(updated_at) = ?
");
$stmt->execute([$selected_elderly_id, $filter_date]);
while ($row = $stmt->fetch()) {
    $timeline[] = [
        'time' => $row['updated_at'],
        'type' => 'request',
        'title' => 'Request Update: ' . ucfirst(str_replace('_', ' ', $row['status'])),
        'desc' => $row['title'],
        'icon' => '📝'
    ];
}

// 5. Emergency Alerts (If any on this date)
$stmt = $pdo->prepare("
    SELECT alert_type, status, created_at
    FROM emergency_alerts
    WHERE elderly_profile_id = ? AND DATE(created_at) = ?
");
$stmt->execute([$selected_elderly_id, $filter_date]);
while ($row = $stmt->fetch()) {
    $timeline[] = [
        'time' => $row['created_at'],
        'type' => 'emergency',
        'title' => 'Emergency Alert: ' . ucfirst($row['alert_type']),
        'desc' => 'Status: ' . ucfirst($row['status']),
        'icon' => '🚨'
    ];
}

// Sort timeline by time descending (latest first)
usort($timeline, function($a, $b) {
    return strtotime($b['time']) - strtotime($a['time']);
});

$pageTitle = 'Daily Updates - ' . sanitize($elderly_name);
require_once '../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
    <div>
        <h1 class="h3 mb-1">Daily Updates</h1>
        <p class="text-muted mb-0">Timeline of events for <?php echo sanitize($elderly_name); ?></p>
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

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <h2 class="h5 mb-4 border-bottom pb-2">Timeline for <?php echo date('F j, Y', strtotime($filter_date)); ?></h2>
        
        <?php if (count($timeline) > 0): ?>
            <div class="timeline position-relative ps-4" style="border-left: 2px solid #e9ecef;">
                <?php foreach ($timeline as $event): ?>
                    <div class="timeline-event position-relative mb-4">
                        <div class="timeline-badge bg-white shadow-sm border rounded-circle text-center d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; position: absolute; left: -3.6rem; top: 0;">
                            <?php echo $event['icon']; ?>
                        </div>
                        <div class="timeline-content bg-light rounded p-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h3 class="h6 mb-0 fw-bold"><?php echo sanitize($event['title']); ?></h3>
                                <small class="text-muted fw-bold">
                                    <?php echo date('g:i A', strtotime($event['time'])); ?>
                                </small>
                            </div>
                            <p class="mb-0 text-muted"><?php echo sanitize($event['desc']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <h3 class="h6 text-muted mb-2">No updates recorded for this day</h3>
                <p class="small text-muted mb-0">Try selecting a different date.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
