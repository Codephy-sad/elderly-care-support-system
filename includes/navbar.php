<?php ?>
<?php
// Determine if we're on the public landing page or the authenticated app
$_isLanding = isset($isLandingPage) && $isLandingPage;
$_isElderly = isLoggedIn() && (int)($_SESSION['role_id'] ?? 0) === 1;
$_isFamily = isLoggedIn() && (int)($_SESSION['role_id'] ?? 0) === 2;
?>

<?php if ($_isLanding): ?>
<!-- ===== PUBLIC LANDING NAVBAR ===== -->
<nav class="site-navbar" role="navigation" aria-label="Main navigation">
    <div class="nav-container">
        <!-- Logo Top Left -->
        <a href="<?php echo sanitize(public_url('index.php')); ?>" class="nav-logo">
            🏡 Elderly Care
        </a>

        <button class="nav-toggle" id="navToggle" aria-label="Open menu" aria-expanded="false" aria-controls="navMenu">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <div class="nav-menu" id="navMenu">
            <ul class="nav-links">
                <li><a href="<?php echo sanitize(public_url('index.php')); ?>">Home</a></li>
                <li class="has-dropdown">
                    <a href="<?php echo sanitize(public_url('services.php')); ?>">Services <span class="dd-arrow">▼</span></a>
                    <ul class="dropdown-menu">
                        <li><a href="<?php echo sanitize(public_url('care.php')); ?>">Care &amp; Wellness</a></li>
                        <li><a href="<?php echo sanitize(public_url('food.php')); ?>">Food &amp; Nutrition</a></li>
                        <li><a href="<?php echo sanitize(public_url('activities.php')); ?>">Activities</a></li>
                        <li><a href="<?php echo sanitize(public_url('facilities.php')); ?>">Facilities &amp; Rooms</a></li>
                    </ul>
                </li>
                <li class="has-dropdown">
                    <a href="<?php echo sanitize(public_url('donation.php')); ?>">Get Involved <span class="dd-arrow">▼</span></a>
                    <ul class="dropdown-menu">
                        <li><a href="<?php echo sanitize(public_url('donation.php')); ?>">Donation</a></li>
                        <li><a href="<?php echo sanitize(public_url('volunteer.php')); ?>">Volunteer</a></li>
                    </ul>
                </li>
                <li><a href="<?php echo sanitize(public_url('contact.php')); ?>">Contact</a></li>
                <li><a href="<?php echo sanitize(public_url('donation.php')); ?>" style="color: var(--amber);">Donate</a></li>
            </ul>

            <!-- Auth Top Right -->
            <div class="nav-auth">
                <?php if (isLoggedIn()):
                    // Build the role-to-dashboard URL map (mirrors index.php redirect logic)
                    $_roleId = (int)($_SESSION['role_id'] ?? 0);
                    $_roleDirs = [
                        1 => 'elderly',
                        2 => 'family',
                        3 => 'caregiver',
                        4 => 'kitchen',
                        5 => 'manager',
                        6 => 'admin',
                        7 => 'donor',
                        8 => 'volunteer',
                    ];
                    $_dashboardUrl = isset($_roleDirs[$_roleId])
                        ? sanitize(app_base_url() . '/' . $_roleDirs[$_roleId] . '/')
                        : sanitize(public_url('index.php'));
                ?>
                    <span class="nav-user-greeting">Hello, <?php echo sanitize($_SESSION['name'] ?? ''); ?></span>
                    <a href="<?php echo $_dashboardUrl; ?>" class="nav-register-btn">Dashboard</a>
                    <a href="<?php echo sanitize(public_url('logout.php')); ?>" class="nav-login-btn">Logout</a>
                <?php else: ?>
                    <a href="<?php echo sanitize(public_url('login.php')); ?>" class="nav-login-btn">Login</a>
                    <a href="<?php echo sanitize(public_url('register.php')); ?>" class="nav-register-btn">Register</a>
                <?php endif; ?>
            </div>

        </div>
    </div>
</nav>

<?php else: ?>
<!-- ===== AUTHENTICATED APP NAVBAR (Bootstrap) ===== -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark app-navbar">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="<?php echo sanitize($_isElderly ? elderly_url('index.php') : public_url('index.php')); ?>">
            Elderly Care
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if ($_isElderly): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo sanitize(elderly_url('index.php')); ?>">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo sanitize(elderly_url('meals.php')); ?>">Meals</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo sanitize(elderly_url('medications.php')); ?>">Medications</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo sanitize(elderly_url('activities.php')); ?>">Activities</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">More</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo sanitize(elderly_url('requests.php')); ?>">My Requests</a></li>
                            <li><a class="dropdown-item" href="<?php echo sanitize(elderly_url('care.php')); ?>">Care Plans</a></li>
                            <li><a class="dropdown-item" href="<?php echo sanitize(elderly_url('messages.php')); ?>">Messages</a></li>
                            <li><a class="dropdown-item" href="<?php echo sanitize(elderly_url('notifications.php')); ?>">Notifications</a></li>
                            <li><a class="dropdown-item" href="<?php echo sanitize(elderly_url('transportation.php')); ?>">Transportation</a></li>
                            <li><a class="dropdown-item" href="<?php echo sanitize(elderly_url('payments.php')); ?>">Payments</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo sanitize(elderly_url('profile.php')); ?>">My Profile</a></li>
                            <li><a class="dropdown-item" href="<?php echo sanitize(elderly_url('room_change.php')); ?>">Room Change</a></li>
                            <li><a class="dropdown-item text-danger fw-bold" href="<?php echo sanitize(elderly_url('emergency.php')); ?>">Emergency Help</a></li>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if ($_isFamily): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo sanitize(app_base_url() . '/family/index.php'); ?>">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo sanitize(app_base_url() . '/family/elderly.php'); ?>">My Elderly</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo sanitize(app_base_url() . '/family/daily_updates.php'); ?>">Daily Updates</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">More</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo sanitize(app_base_url() . '/family/meals.php'); ?>">Meals</a></li>
                            <li><a class="dropdown-item" href="<?php echo sanitize(app_base_url() . '/family/medications.php'); ?>">Medications</a></li>
                            <li><a class="dropdown-item" href="<?php echo sanitize(app_base_url() . '/family/activities.php'); ?>">Activities</a></li>
                            <li><a class="dropdown-item" href="<?php echo sanitize(app_base_url() . '/family/visits.php'); ?>">Visits</a></li>
                            <li><a class="dropdown-item" href="<?php echo sanitize(app_base_url() . '/family/requests.php'); ?>">Requests</a></li>
                            <li><a class="dropdown-item" href="<?php echo sanitize(app_base_url() . '/family/messages.php'); ?>">Messages</a></li>
                            <li><a class="dropdown-item" href="<?php echo sanitize(app_base_url() . '/family/memories.php'); ?>">Memories</a></li>
                            <li><a class="dropdown-item" href="<?php echo sanitize(app_base_url() . '/family/payments.php'); ?>">Payments</a></li>
                            <li><a class="dropdown-item" href="<?php echo sanitize(app_base_url() . '/family/notifications.php'); ?>">Notifications</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo sanitize(app_base_url() . '/family/profile.php'); ?>">My Profile</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item">
                        <span class="nav-link text-white-50">Hello, <?php echo sanitize($_SESSION['name'] ?? ''); ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo sanitize(public_url('logout.php')); ?>">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo sanitize(public_url('login.php')); ?>">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo sanitize(public_url('register.php')); ?>">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<?php endif; ?>
