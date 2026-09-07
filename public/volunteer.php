<?php
require_once __DIR__ . '/../config/auth.php';
$pageTitle = 'Volunteer — Elderly Care';
$isLandingPage = true;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-hero" style="background: linear-gradient(135deg, #3a7a65, var(--navy));">
    <div class="page-inner">
        <h1>Become a Volunteer</h1>
        <p class="page-hero-sub">Give the gift of your time. A simple conversation can brighten a senior's entire week.</p>
    </div>
</div>

<section class="page-section section-white">
    <div class="page-inner two-col">
        <div>
            <div class="img-placeholder" style="min-height: 400px;" role="img" aria-label="A volunteer smiling while playing a board game with a resident">
                📷 Photo: Volunteer playing a game with a resident
            </div>
        </div>
        <div class="two-col-text">
            <h2>Make a Real Difference</h2>
            <p>
                Our volunteers are the heartbeat of our community. You don't need any special medical training to volunteer — you just need empathy, patience, and a willingness to connect.
            </p>
            <h3 class="mt-4 mb-3">Ways You Can Help:</h3>
            <ul class="feature-list">
                <li><span class="fl-icon">✔</span> <strong>Companion Visitor:</strong> Sit, chat, and listen to stories.</li>
                <li><span class="fl-icon">✔</span> <strong>Activity Leader:</strong> Lead an art class, yoga session, or music hour.</li>
                <li><span class="fl-icon">✔</span> <strong>Reading Buddy:</strong> Read books or daily news to visually impaired residents.</li>
                <li><span class="fl-icon">✔</span> <strong>Event Helper:</strong> Assist during birthday parties and holiday celebrations.</li>
                <li><span class="fl-icon">✔</span> <strong>Tech Guide:</strong> Help seniors use tablets to video call their families.</li>
            </ul>
            
            <div class="mt-4">
                <a href="<?php echo sanitize(public_url('register.php')); ?>" class="btn-navy">Register as a Volunteer</a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
