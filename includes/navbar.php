<?php ?>
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
                        <li><a href="<?php echo sanitize(public_url('care.php')); ?>">Care & Wellness</a></li>
                        <li><a href="<?php echo sanitize(public_url('food.php')); ?>">Food & Nutrition</a></li>
                        <li><a href="<?php echo sanitize(public_url('activities.php')); ?>">Activities</a></li>
                        <li><a href="<?php echo sanitize(public_url('facilities.php')); ?>">Facilities & Rooms</a></li>
                    </ul>
                </li>
                <li class="has-dropdown">
                    <a href="#">Get Involved <span class="dd-arrow">▼</span></a>
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
                <?php if (isLoggedIn()): ?>
                    <span class="nav-user-greeting">Hello, <?php echo sanitize($_SESSION['name'] ?? ''); ?></span>
                    <a href="<?php echo sanitize(public_url('logout.php')); ?>" class="nav-login-btn">Logout</a>
                <?php else: ?>
                    <a href="<?php echo sanitize(public_url('login.php')); ?>" class="nav-login-btn">Login</a>
                    <a href="<?php echo sanitize(public_url('register.php')); ?>" class="nav-register-btn">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
