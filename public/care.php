<?php
require_once __DIR__ . '/../config/auth.php';
$pageTitle = 'Care & Wellness — Elderly Care';
$isLandingPage = true;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-hero">
    <div class="page-inner">
        <h1>Care &amp; Wellness</h1>
        <p class="page-hero-sub">Compassionate, professional health monitoring and daily assistance tailored to each resident.</p>
    </div>
</div>

<section class="page-section section-white">
    <div class="page-inner two-col">
        <div>
            <div class="img-placeholder" style="min-height: 400px;" role="img" aria-label="Caregiver checking vitals of a smiling resident">
                📷 Photo: Caregiver checking vitals
            </div>
        </div>
        <div class="two-col-text">
            <h2>Personalized Care Plans</h2>
            <p>
                We understand that every senior has unique needs. Upon admission, our medical team and head caregivers work with families to create a tailored care and wellness plan. We continuously monitor and adjust this plan to ensure optimal health and comfort.
            </p>
            <ul class="feature-list">
                <li><span class="fl-icon">✔</span> <strong>24/7 Monitoring:</strong> Round-the-clock trained caregivers on duty.</li>
                <li><span class="fl-icon">✔</span> <strong>Medication Management:</strong> Strict tracking and dispensing of daily medications.</li>
                <li><span class="fl-icon">✔</span> <strong>Vitals Tracking:</strong> Daily logging of blood pressure, blood sugar, and weight.</li>
                <li><span class="fl-icon">✔</span> <strong>Emergency Response:</strong> Immediate access to medical professionals and hospitals.</li>
                <li><span class="fl-icon">✔</span> <strong>Mental Health:</strong> Emotional support, counseling, and companionship.</li>
            </ul>
        </div>
    </div>
</section>

<section class="page-cta">
    <h2>Have specific medical requirements?</h2>
    <p>Our team is equipped to handle various chronic conditions. Let's discuss your needs.</p>
    <a href="<?php echo sanitize(public_url('contact.php')); ?>" class="btn-coral">Talk to our Care Team</a>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
