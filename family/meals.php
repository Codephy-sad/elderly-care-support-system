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

// 1. Get menu for this date
$stmt = $pdo->prepare("SELECT title, notes FROM menus WHERE meal_date = ?");
$stmt->execute([$filter_date]);
$menu = $stmt->fetch();

// 2. Get assigned meals and distribution status
$meals = [];
if ($menu) {
    $stmt = $pdo->prepare("
        SELECT m.id, m.meal_type, m.title, m.description, m.dietary_tags,
               md.status as distribution_status, md.served_at, md.notes as distribution_notes
        FROM meal_assignments ma
        JOIN meals m ON m.id = ma.meal_id
        JOIN menus mn ON mn.id = m.menu_id
        LEFT JOIN meal_distributions md ON md.meal_id = m.id AND md.elderly_profile_id = ma.elderly_profile_id
        WHERE ma.elderly_profile_id = ? AND mn.meal_date = ?
        ORDER BY FIELD(m.meal_type, 'breakfast', 'lunch', 'snack', 'dinner')
    ");
    $stmt->execute([$selected_elderly_id, $filter_date]);
    $meals = $stmt->fetchAll();
}

$pageTitle = 'Meals - ' . sanitize($elderly_name);
require_once '../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
    <div>
        <h1 class="h3 mb-1">Meal Information</h1>
        <p class="text-muted mb-0">Dietary overview for <?php echo sanitize($elderly_name); ?></p>
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

<?php if ($menu): ?>
    <div class="alert alert-info border-0 shadow-sm mb-4">
        <h5 class="alert-heading">Menu: <?php echo sanitize($menu['title'] ?? 'Daily Menu'); ?></h5>
        <?php if (!empty($menu['notes'])): ?>
            <p class="mb-0"><?php echo sanitize($menu['notes']); ?></p>
        <?php endif; ?>
    </div>
    
    <div class="row g-4">
        <?php if (count($meals) > 0): ?>
            <?php foreach ($meals as $meal): ?>
                <?php
                // Badge color based on status
                $status = $meal['distribution_status'] ?? 'pending';
                $badge_class = 'bg-secondary';
                if ($status === 'served') $badge_class = 'bg-success';
                elseif ($status === 'skipped') $badge_class = 'bg-warning text-dark';
                ?>
                <div class="col-md-6 col-xl-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                            <h6 class="text-uppercase text-muted fw-bold mb-0">
                                <?php echo sanitize($meal['meal_type']); ?>
                            </h6>
                            <span class="badge <?php echo $badge_class; ?> rounded-pill">
                                <?php echo ucfirst(sanitize($status)); ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold text-dark"><?php echo sanitize($meal['title']); ?></h5>
                            <p class="card-text text-muted small mb-3"><?php echo sanitize($meal['description']); ?></p>
                            
                            <?php if (!empty($meal['dietary_tags'])): ?>
                                <div class="mb-3">
                                    <?php
                                    $tags = explode(',', $meal['dietary_tags']);
                                    foreach ($tags as $tag):
                                    ?>
                                        <span class="badge bg-light text-secondary border px-2 py-1 me-1 mb-1 fw-normal">
                                            <?php echo sanitize(trim($tag)); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($status === 'served' && $meal['served_at']): ?>
                                <hr>
                                <div class="text-success small fw-bold">
                                    <i class="bi bi-check-circle-fill"></i> Served at <?php echo date('g:i A', strtotime($meal['served_at'])); ?>
                                </div>
                            <?php elseif ($status === 'skipped' && !empty($meal['distribution_notes'])): ?>
                                <hr>
                                <div class="text-warning small fw-bold">
                                    <i class="bi bi-exclamation-triangle-fill"></i> Reason: <?php echo sanitize($meal['distribution_notes']); ?>
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
                        <h4 class="text-muted fw-normal">No Meals Assigned</h4>
                        <p class="text-muted mb-0">No specific meals have been assigned to <?php echo sanitize($elderly_name); ?> for this date.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm text-center py-5">
        <div class="card-body">
            <h4 class="text-muted fw-normal">No Menu Available</h4>
            <p class="text-muted mb-0">No menu has been published for <?php echo date('F j, Y', strtotime($filter_date)); ?> yet.</p>
        </div>
    </div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
