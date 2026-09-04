<?php
require_once __DIR__ . '/../config/auth.php';

$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <h1 class="h3 mb-3">Welcome</h1>
        <?php if (isLoggedIn()): ?>
            <p class="mb-2">You are signed in as <strong><?php echo sanitize($_SESSION['name'] ?? ''); ?></strong>.</p>
            <p class="text-muted mb-4">Role dashboards will be connected in a later step.</p>
            <a class="btn btn-brand" href="<?php echo sanitize(public_url('logout.php')); ?>">Log out</a>
        <?php else: ?>
            <p class="mb-4">Please log in or create an account to continue.</p>
            <a class="btn btn-brand me-2" href="<?php echo sanitize(public_url('login.php')); ?>">Log in</a>
            <a class="btn btn-outline-secondary" href="<?php echo sanitize(public_url('register.php')); ?>">Register</a>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
