<?php
require_once '../config/database.php';
require_once '../config/auth.php';
checkRole(1);

$user_id = (int)$_SESSION['user_id'];
$profile_id = getElderlyProfileId($pdo, $user_id);
$success = '';
$error = '';

// handle "mark as taken"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_taken']) && $profile_id) {
    $event_id = (int)$_POST['event_id'];

    // verify this event belongs to this resident
    $stmt = $pdo->prepare("
        SELECT me.id FROM medication_events me
        JOIN medications m ON m.id = me.medication_id
        WHERE me.id = ? AND m.elderly_profile_id = ? AND me.status = 'pending'
    ");
    $stmt->execute([$event_id, $profile_id]);
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("UPDATE medication_events SET status = 'taken', taken_at = NOW() WHERE id = ?");
        $stmt->execute([$event_id]);
        $success = 'Medication marked as taken.';
    } else {
        $error = 'Could not update this medication.';
    }
}

// get today's medications with events
$medications = [];
if ($profile_id) {
    $stmt = $pdo->prepare("
        SELECT m.id as med_id, m.name, m.dosage, m.frequency, m.time_slot, m.instructions,
               me.id as event_id, me.status as event_status, me.taken_at
        FROM medications m
        LEFT JOIN medication_events me ON me.medication_id = m.id AND me.event_date = CURDATE()
        WHERE m.elderly_profile_id = ? AND m.active = 1
        ORDER BY m.time_slot ASC
    ");
    $stmt->execute([$profile_id]);
    $medications = $stmt->fetchAll();
}

// get recent history (last 7 days)
$history = [];
if ($profile_id) {
    $stmt = $pdo->prepare("
        SELECT m.name, me.event_date, me.status, me.taken_at
        FROM medication_events me
        JOIN medications m ON m.id = me.medication_id
        WHERE m.elderly_profile_id = ?
          AND me.event_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
          AND me.event_date < CURDATE()
        ORDER BY me.event_date DESC, m.time_slot ASC
    ");
    $stmt->execute([$profile_id]);
    $history = $stmt->fetchAll();
}

$pageTitle = 'My Medications';
require_once '../includes/header.php';
?>

<div class="elderly-page">
    <div class="elderly-page-header">
        <h1>My Medications</h1>
        <p class="text-muted mb-0"><?php echo date('l, F j, Y'); ?></p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo sanitize($success); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo sanitize($error); ?></div>
    <?php endif; ?>

    <h2 class="elderly-section-title">Today's Schedule</h2>

    <?php if (empty($medications)): ?>
        <div class="elderly-empty">
            <p>No medications scheduled.</p>
            <p class="text-muted">Your caregiver will set up your medication schedule.</p>
        </div>
    <?php else: ?>
        <?php foreach ($medications as $med): ?>
            <?php
                $event_status = $med['event_status'] ?? 'no_event';
                $is_taken = ($event_status === 'taken');
                $card_class = $is_taken ? 'elderly-card--success' : ($event_status === 'missed' ? 'elderly-card--danger' : 'elderly-card--highlight');
            ?>
            <div class="elderly-card <?php echo $card_class; ?>">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="h5 mb-1"><?php echo sanitize($med['name']); ?></h3>
                        <?php if ($med['dosage']): ?>
                            <p class="mb-1">Dosage: <?php echo sanitize($med['dosage']); ?></p>
                        <?php endif; ?>
                        <p class="mb-1 text-muted">
                            <?php echo sanitize($med['frequency']); ?> —
                            Scheduled at <?php echo date('g:i A', strtotime($med['time_slot'])); ?>
                        </p>
                        <?php if ($med['instructions']): ?>
                            <small class="text-muted"><?php echo sanitize($med['instructions']); ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="text-end">
                        <?php if ($event_status === 'taken'): ?>
                            <span class="status-badge status-taken">Taken</span>
                            <?php if ($med['taken_at']): ?>
                                <br><small class="text-muted">at <?php echo date('g:i A', strtotime($med['taken_at'])); ?></small>
                            <?php endif; ?>
                        <?php elseif ($event_status === 'missed'): ?>
                            <span class="status-badge status-missed">Missed</span>
                        <?php elseif ($event_status === 'skipped'): ?>
                            <span class="status-badge status-skipped">Skipped</span>
                        <?php elseif ($med['event_id']): ?>
                            <form method="POST">
                                <input type="hidden" name="event_id" value="<?php echo $med['event_id']; ?>">
                                <button type="submit" name="mark_taken" class="btn btn-brand btn-elderly">
                                    Mark as Taken
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="status-badge status-pending">No event today</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!empty($history)): ?>
        <h2 class="elderly-section-title mt-4">Recent History (Last 7 Days)</h2>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Medication</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $h): ?>
                        <tr>
                            <td><?php echo date('M j', strtotime($h['event_date'])); ?></td>
                            <td><?php echo sanitize($h['name']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo sanitize($h['status']); ?>">
                                    <?php echo sanitize(ucfirst($h['status'])); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
// Extract pending medications for JS reminder
$pending_meds_js = [];
if (!empty($medications)) {
    foreach ($medications as $med) {
        if (($med['event_status'] ?? 'no_event') === 'pending') {
            $pending_meds_js[] = [
                'name' => $med['name'],
                'time_slot' => $med['time_slot']
            ];
        }
    }
}
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const pendingMeds = <?php echo json_encode($pending_meds_js); ?>;
    if (pendingMeds.length === 0) return;

    // To prevent spamming the alert every second/minute for the same medication
    let alertedMeds = {};

    function checkMeds() {
        const now = new Date();
        const currentHours = String(now.getHours()).padStart(2, '0');
        const currentMinutes = String(now.getMinutes()).padStart(2, '0');
        const currentTime = `${currentHours}:${currentMinutes}`;
        
        pendingMeds.forEach(med => {
            const medTime = med.time_slot.substring(0, 5);
            if (medTime === currentTime && !alertedMeds[med.name]) {
                alertedMeds[med.name] = true;
                alert("Reminder: It's time to take your medication: " + med.name);
            }
        });
    }

    // Check immediately on load
    checkMeds();
    
    // Check every 30 seconds
    setInterval(checkMeds, 30000);
});
</script>

<?php require_once '../includes/footer.php'; ?>
