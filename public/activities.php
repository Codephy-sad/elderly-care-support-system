<?php
require_once __DIR__ . '/../config/auth.php';
$pageTitle = 'Activities & Community — Elderly Care';
$isLandingPage = true;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-hero" style="background: linear-gradient(135deg, #5b3e7a, var(--coral));">
    <div class="page-inner">
        <h1>Activities &amp; Community</h1>
        <p class="page-hero-sub">Staying active, learning new things, and building friendships every day.</p>
    </div>
</div>

<section class="page-section">
    <div class="page-inner">
        <div class="section-header text-center mb-5">
            <h2>A Vibrant Daily Schedule</h2>
            <p style="color: var(--text-muted); max-width: 600px; margin: 0 auto;">We believe in keeping the mind sharp and the spirit lifted. Our activity coordinators and volunteers organize a variety of daily events.</p>
        </div>

        <div class="detail-grid">
            <div class="detail-card">
                <span class="card-icon" aria-hidden="true">🧘</span>
                <h3>Physical Wellness</h3>
                <p>Gentle morning stretching, chair yoga, and guided indoor walks to maintain mobility and balance safely.</p>
            </div>
            <div class="detail-card">
                <span class="card-icon" aria-hidden="true">🎨</span>
                <h3>Creative Arts</h3>
                <p>Painting, pottery, and craft workshops that encourage self-expression and improve fine motor skills.</p>
            </div>
            <div class="detail-card">
                <span class="card-icon" aria-hidden="true">🎶</span>
                <h3>Music &amp; Entertainment</h3>
                <p>Weekly musical performances, sing-alongs, and movie nights in our comfortable common lounges.</p>
            </div>
            <div class="detail-card">
                <span class="card-icon" aria-hidden="true">🧠</span>
                <h3>Cognitive Games</h3>
                <p>Puzzles, board games, bingo, and memory exercises designed to keep the brain active.</p>
            </div>
            <div class="detail-card">
                <span class="card-icon" aria-hidden="true">🌿</span>
                <h3>Gardening Club</h3>
                <p>Tending to our community garden, planting flowers and herbs, and enjoying the fresh air.</p>
            </div>
            <div class="detail-card">
                <span class="card-icon" aria-hidden="true">🎂</span>
                <h3>Celebrations</h3>
                <p>We make every birthday, holiday, and cultural festival a special event for the entire community.</p>
            </div>
        </div>
    </div>
</section>

<section class="page-cta">
    <h2>Want to help run an activity?</h2>
    <p>We are always looking for enthusiastic volunteers to lead workshops or simply join in.</p>
    <a href="<?php echo sanitize(public_url('volunteer.php')); ?>" class="btn-outline-white">Become a Volunteer</a>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
