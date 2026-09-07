<?php
require_once __DIR__ . '/../config/auth.php';
$pageTitle = 'Our Services — Elderly Care';
$isLandingPage = true;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-hero">
    <div class="page-inner">
        <h1>Comprehensive Care Services</h1>
        <p class="page-hero-sub">Everything your loved ones need to live safely, comfortably, and happily under one roof.</p>
    </div>
</div>

<section class="page-section">
    <div class="page-inner">
        <div class="card-grid card-grid-3">
            <div class="landing-card detail-card">
                <span class="card-icon" aria-hidden="true">🏥</span>
                <h3>Care &amp; Wellness</h3>
                <p>Personalized healthcare plans, 24/7 monitoring, medication tracking, and regular checkups.</p>
                <a href="<?php echo sanitize(public_url('care.php')); ?>" class="learn-more-link">Learn More <span>→</span></a>
            </div>
            <div class="landing-card detail-card">
                <span class="card-icon" aria-hidden="true">🍽️</span>
                <h3>Food &amp; Nutrition</h3>
                <p>Freshly prepared daily meals catering to all dietary requirements and preferences.</p>
                <a href="<?php echo sanitize(public_url('food.php')); ?>" class="learn-more-link">Learn More <span>→</span></a>
            </div>
            <div class="landing-card detail-card">
                <span class="card-icon" aria-hidden="true">🎨</span>
                <h3>Activities</h3>
                <p>A full schedule of social, physical, and creative activities to keep residents engaged.</p>
                <a href="<?php echo sanitize(public_url('activities.php')); ?>" class="learn-more-link">Learn More <span>→</span></a>
            </div>
            <div class="landing-card detail-card">
                <span class="card-icon" aria-hidden="true">🛏️</span>
                <h3>Facilities &amp; Rooms</h3>
                <p>Accessible, comfortable private and shared living spaces designed for seniors.</p>
                <a href="<?php echo sanitize(public_url('facilities.php')); ?>" class="learn-more-link">Learn More <span>→</span></a>
            </div>
            <div class="landing-card detail-card">
                <span class="card-icon" aria-hidden="true">👨‍👩‍👧</span>
                <h3>Family Connection</h3>
                <p>Real-time updates for families through our dedicated portal, keeping you informed.</p>
            </div>
            <div class="landing-card detail-card">
                <span class="card-icon" aria-hidden="true">🤝</span>
                <h3>Community Support</h3>
                <p>Trained volunteers who bring companionship, conversation, and warmth.</p>
                <a href="<?php echo sanitize(public_url('volunteer.php')); ?>" class="learn-more-link">Volunteer with us <span>→</span></a>
            </div>
        </div>
    </div>
</section>

<section class="page-cta">
    <h2>Ready to learn more?</h2>
    <p>Contact our admission team today to discuss a tailored plan for your loved one.</p>
    <a href="<?php echo sanitize(public_url('contact.php')); ?>" class="btn-coral">Contact Us</a>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
