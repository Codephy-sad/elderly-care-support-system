<?php
require_once __DIR__ . '/../config/auth.php';
$pageTitle = 'Facilities & Rooms — Elderly Care';
$isLandingPage = true;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-hero" style="background: linear-gradient(135deg, var(--navy-dark), #2a6478);">
    <div class="page-inner">
        <h1>Facilities &amp; Rooms</h1>
        <p class="page-hero-sub">Designed for safety, accessibility, and the comfort of home.</p>
    </div>
</div>

<section class="page-section section-white">
    <div class="page-inner">
        <div class="two-col mb-5">
            <div class="two-col-text">
                <h2>A Place to Call Home</h2>
                <p>
                    Transitioning to a care facility can be difficult, which is why we've designed our living spaces to feel as warm and homelike as possible. All our rooms are fully furnished, easily accessible, and equipped with modern safety features.
                </p>
                <ul class="feature-list">
                    <li><span class="fl-icon">✔</span> <strong>Emergency Call System:</strong> Reach caregivers instantly from bed or bathroom.</li>
                    <li><span class="fl-icon">✔</span> <strong>Accessibility:</strong> Wheelchair-friendly doorways, ramps, and grab bars.</li>
                    <li><span class="fl-icon">✔</span> <strong>Climate Control:</strong> Comfortable temperatures maintained year-round.</li>
                    <li><span class="fl-icon">✔</span> <strong>Housekeeping:</strong> Daily cleaning and laundry services included.</li>
                </ul>
            </div>
            <div>
                <div class="img-placeholder" style="min-height: 350px;" role="img" aria-label="A bright, clean private room with natural sunlight">
                    📷 Photo: Bright private room with natural lighting
                </div>
            </div>
        </div>

        <div class="section-header text-center mt-5 mb-4">
            <h2>Room Options</h2>
        </div>
        
        <div class="detail-grid" style="grid-template-columns: 1fr 1fr; max-width: 800px; margin: 0 auto;">
            <div class="detail-card text-center">
                <span class="card-icon" aria-hidden="true">🛏️</span>
                <h3>Private Rooms</h3>
                <p>Spacious single-occupancy rooms offering maximum privacy. Includes an attached accessible bathroom, a large window, a television, and personal storage space.</p>
            </div>
            <div class="detail-card text-center">
                <span class="card-icon" aria-hidden="true">🛋️</span>
                <h3>Shared Suites</h3>
                <p>Comfortable two-person suites perfect for residents who enjoy companionship. Features privacy dividers, individual wardrobes, and a shared accessible bathroom.</p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
