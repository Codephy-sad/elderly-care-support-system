<?php
require_once __DIR__ . '/../config/auth.php';
$pageTitle = 'Food & Nutrition — Elderly Care';
$isLandingPage = true;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-hero" style="background: linear-gradient(135deg, var(--amber), #c49530); color: var(--text-dark);">
    <div class="page-inner">
        <h1 style="color: var(--text-dark);">Food &amp; Nutrition</h1>
        <p class="page-hero-sub" style="color: rgba(31,41,55,0.85);">Delicious, wholesome meals planned by nutritionists to keep residents healthy and happy.</p>
    </div>
</div>

<section class="page-section section-cream">
    <div class="page-inner two-col">
        <div class="two-col-text">
            <h2>Nourishing Body &amp; Soul</h2>
            <p>
                Food is one of life's greatest pleasures. Our dedicated kitchen staff prepares three hot, fresh meals daily, along with nutritious snacks. We source high-quality local ingredients and design our menus to provide the right balance of vitamins and minerals needed for senior health.
            </p>
            <ul class="feature-list">
                <li><span class="fl-icon">✔</span> <strong>Customized Diets:</strong> Diabetic, low-sodium, vegetarian, and soft-food options.</li>
                <li><span class="fl-icon">✔</span> <strong>Fresh Ingredients:</strong> We avoid processed foods in favor of fresh, seasonal produce.</li>
                <li><span class="fl-icon">✔</span> <strong>Hydration Focus:</strong> Regular hydration rounds to ensure seniors drink enough water.</li>
                <li><span class="fl-icon">✔</span> <strong>Family Visibility:</strong> Families can view weekly menus and daily meal logs online.</li>
            </ul>
        </div>
        <div>
            <div class="img-placeholder" style="min-height: 400px; background: var(--amber-light);" role="img" aria-label="A beautifully plated healthy meal for seniors">
                📷 Photo: Beautifully plated healthy meal
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
