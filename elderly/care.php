<?php
require_once '../config/database.php';
require_once '../config/auth.php';
checkRole(1);

$user_id = (int)$_SESSION['user_id'];
$profile_id = getElderlyProfileId($pdo, $user_id);

$care_plans = [];
if ($profile_id) {
    $stmt = $pdo->prepare("
        SELECT cp.title, cp.description, cp.category, cp.created_at,
               u.name as created_by_name
        FROM care_plans cp
        LEFT JOIN users u ON u.id = cp.created_by
        WHERE cp.elderly_profile_id = ? AND cp.active = 1
        ORDER BY cp.category, cp.title
    ");
    $stmt->execute([$profile_id]);
    $care_plans = $stmt->fetchAll();
}

$pageTitle = 'Care & Wellness';
require_once '../includes/header.php';
?>

<div class="elderly-page">
    <div class="elderly-page-header">
        <h1>My Care Plans</h1>
        <p class="text-muted mb-0">Your personalized wellness routines and guidance</p>
    </div>

    <?php if (empty($care_plans)): ?>
        <div class="elderly-empty">
            <p>No care plans have been set up for you yet.</p>
            <p class="text-muted">Your caregiver will add your routines soon.</p>
        </div>
    <?php else: ?>
        <?php
            // group by category
            $grouped = [];
            foreach ($care_plans as $plan) {
                $cat = $plan['category'] ?? 'general';
                $grouped[$cat][] = $plan;
            }
        ?>
        <?php foreach ($grouped as $category => $plans): ?>
            <h2 class="elderly-section-title"><?php echo sanitize(ucfirst($category)); ?></h2>
            <?php foreach ($plans as $plan): ?>
                <div class="elderly-card elderly-card--highlight">
                    <h3 class="h5 mb-2"><?php echo sanitize($plan['title']); ?></h3>
                    <?php if ($plan['description']): ?>
                        <p class="mb-2"><?php echo nl2br(sanitize($plan['description'])); ?></p>
                    <?php endif; ?>
                    <small class="text-muted">
                        Added by <?php echo sanitize($plan['created_by_name'] ?? 'Staff'); ?>
                        on <?php echo date('M j, Y', strtotime($plan['created_at'])); ?>
                    </small>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
