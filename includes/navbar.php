<?php ?>
<nav class="navbar navbar-expand-lg navbar-dark app-navbar">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="<?php echo sanitize(isLoggedIn() && (int)($_SESSION['role_id'] ?? 0) === 1 ? elderly_url('index.php') : public_url('index.php')); ?>">
            Elderly Care
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if (isLoggedIn() && (int)($_SESSION['role_id'] ?? 0) === 1): ?>
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
            </ul>

            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item">
                        <span class="navbar-text text-white me-3">
                            Hello, <?php echo sanitize($_SESSION['name'] ?? ''); ?>
                        </span>
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
