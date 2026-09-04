<?php
require_once '../config/database.php';
require_once '../config/auth.php';
checkRole(1);

$user_id = (int)$_SESSION['user_id'];
$profile_id = getElderlyProfileId($pdo, $user_id);

// get today's assigned meals
$today_meals = [];
if ($profile_id) {
    $stmt = $pdo->prepare("
        SELECT m.id, m.meal_type, m.title, m.description, m.dietary_tags,
               ma.id as assignment_id,
               md.status as distribution_status, md.served_at, md.notes as dist_notes
        FROM meal_assignments ma
        JOIN meals m ON m.id = ma.meal_id
        JOIN menus mn ON mn.id = m.menu_id
        LEFT JOIN meal_distributions md ON md.meal_id = m.id AND md.elderly_profile_id = ma.elderly_profile_id
        WHERE ma.elderly_profile_id = ?
          AND mn.meal_date = CURDATE()
        ORDER BY FIELD(m.meal_type, 'breakfast', 'lunch', 'snack', 'dinner')
    ");
    $stmt->execute([$profile_id]);
    $today_meals = $stmt->fetchAll();
}

$pageTitle = 'My Meals';
require_once '../includes/header.php';
?>

<div class="elderly-page">
    <div class="elderly-page-header">
        <h1>Today's Meals</h1>
        <p class="text-muted mb-0"><?php echo date('l, F j, Y'); ?></p>
    </div>

    <?php if (empty($today_meals)): ?>
        <div class="elderly-empty">
            <p>No meals have been assigned for today yet.</p>
            <p class="text-muted">The kitchen will update the menu soon.</p>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($today_meals as $meal): ?>
                <div class="col-md-6">
                    <div class="elderly-card elderly-card--highlight">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="meal-type meal-<?php echo sanitize($meal['meal_type']); ?>">
                                <?php echo sanitize(ucfirst($meal['meal_type'])); ?>
                            </span>
                            <?php
                                $dist_status = $meal['distribution_status'] ?? 'pending';
                                $status_label = ucfirst(str_replace('_', ' ', $dist_status));
                            ?>
                            <span class="status-badge status-<?php echo sanitize($dist_status); ?>">
                                <?php echo sanitize($status_label); ?>
                            </span>
                        </div>
                        <h3 class="h5 mb-1"><?php echo sanitize($meal['title']); ?></h3>
                        <?php if ($meal['description']): ?>
                            <p class="text-muted mb-2"><?php echo sanitize($meal['description']); ?></p>
                        <?php endif; ?>
                        <?php if ($meal['dietary_tags']): ?>
                            <small class="text-muted">
                                <strong>Dietary:</strong> <?php echo sanitize($meal['dietary_tags']); ?>
                            </small>
                        <?php endif; ?>
                        <?php if ($meal['served_at']): ?>
                            <br><small class="text-muted">Served at <?php echo date('g:i A', strtotime($meal['served_at'])); ?></small>
                        <?php endif; ?>
                        <?php if ($meal['dist_notes']): ?>
                            <br><small class="text-muted">Note: <?php echo sanitize($meal['dist_notes']); ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
