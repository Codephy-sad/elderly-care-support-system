<?php
require_once __DIR__ . '/../config/auth.php';
$pageTitle = 'Donate — Elderly Care';
$isLandingPage = true;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-hero" style="background: linear-gradient(135deg, var(--coral-dark), var(--coral));">
    <div class="page-inner">
        <h1>Make a Donation</h1>
        <p class="page-hero-sub">Your generosity allows us to provide subsidized care, better facilities, and a higher quality of life for seniors in need.</p>
    </div>
</div>

<section class="page-section section-cream">
    <div class="page-inner">
        <div class="two-col">
            <div class="two-col-text">
                <h2>Why Your Support Matters</h2>
                <p>
                    While many of our residents are supported by their families, some seniors come to us with little to no financial backing. Your donations directly fund:
                </p>
                <ul class="feature-list">
                    <li><span class="fl-icon">✔</span> Subsidized living costs for low-income residents.</li>
                    <li><span class="fl-icon">✔</span> Essential medical supplies and emergency care funds.</li>
                    <li><span class="fl-icon">✔</span> Fresh, nutritious food ingredients.</li>
                    <li><span class="fl-icon">✔</span> Facility upgrades like new wheelchairs and accessible furniture.</li>
                </ul>
            </div>
            
            <div class="detail-card" style="background: var(--white);">
                <h3 class="text-center mb-4">Choose a Donation Amount</h3>
                <div class="donation-amounts justify-content-center">
                    <button type="button" class="amount-btn">৳500</button>
                    <button type="button" class="amount-btn active">৳1,000</button>
                    <button type="button" class="amount-btn">৳2,500</button>
                    <button type="button" class="amount-btn">৳5,000</button>
                    <button type="button" class="amount-btn">৳10,000</button>
                </div>
                
                <div class="text-center mt-4">
                    <a href="<?php echo sanitize(public_url('register.php')); ?>" class="btn-coral" style="width: 100%;">Proceed to Donate</a>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 1rem;">You must be logged in as a Donor to make a contribution securely.</p>
                </div>
            </div>
        </div>

        <div class="impact-grid">
            <div class="impact-item">
                <div class="impact-number">৳2,500</div>
                <div class="impact-label">Provides meals for a resident for a week</div>
            </div>
            <div class="impact-item">
                <div class="impact-number">৳5,000</div>
                <div class="impact-label">Covers monthly medical supplies</div>
            </div>
            <div class="impact-item">
                <div class="impact-number">৳10,000+</div>
                <div class="impact-label">Subsidizes a low-income resident's room</div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
