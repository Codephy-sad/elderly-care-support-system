<?php
require_once __DIR__ . '/../config/auth.php';

// if user is already logged in, send them to their role dashboard
if (isLoggedIn()) {
    $roleId = (int)($_SESSION['role_id'] ?? 0);
    $dashboards = [
        1 => 'elderly',
        2 => 'family',
        3 => 'caregiver',
        4 => 'kitchen',
        5 => 'manager',
        6 => 'admin',
        7 => 'donor',
        8 => 'volunteer',
    ];
    if (isset($dashboards[$roleId])) {
        header('Location: ' . app_base_url() . '/' . $dashboards[$roleId] . '/');
        exit;
    }
}

$pageTitle = 'Elderly Care & Support Platform — Compassionate Senior Living';
$isLandingPage = true;
require_once __DIR__ . '/../includes/header.php';
?>

<a href="#welcome" class="skip-link">Skip to main content</a>

<!-- ========== 1. WELCOME / HERO ========== -->
<section id="welcome" class="hero-section landing-section">
    <div class="hero-inner">
        <div class="hero-text">
            <h1>A Caring Home for Your Loved Ones</h1>
            <p class="hero-subtitle">
                We bring together senior residents, families, caregivers, and community volunteers
                to create a warm, safe, and active environment where every elder is valued and supported.
            </p>
            <div class="hero-buttons">
                <a href="<?php echo sanitize(public_url('services.php')); ?>" class="btn-coral">Explore Our Services</a>
                <a href="<?php echo sanitize(public_url('contact.php')); ?>" class="btn-outline-white">Get Support</a>
            </div>
        </div>
        <div class="hero-image">
            <div class="img-placeholder" role="img" aria-label="Senior residents enjoying time in a garden setting">
                <span class="ph-icon">🌿</span>
                Seniors enjoying a garden gathering
            </div>
        </div>
    </div>
    <div class="hero-stats">
        <div class="hero-stat">
            <div class="stat-number">150+</div>
            <div class="stat-label">Residents Cared For</div>
        </div>
        <div class="hero-stat">
            <div class="stat-number">40+</div>
            <div class="stat-label">Trained Caregivers</div>
        </div>
        <div class="hero-stat">
            <div class="stat-number">200+</div>
            <div class="stat-label">Active Volunteers</div>
        </div>
        <div class="hero-stat">
            <div class="stat-number">10+</div>
            <div class="stat-label">Years of Service</div>
        </div>
    </div>
</section>

<!-- ========== 2. ABOUT / WHY ELDERLY SUPPORT MATTERS ========== -->
<section id="about" class="landing-section section-cream">
    <div class="section-inner">
        <div class="two-col">
            <div class="two-col-text">
                <h2>Why Elderly Support Matters</h2>
                <p>
                    As our population ages, the need for thoughtful, respectful elder care grows every year.
                    Many seniors face loneliness, health challenges, and difficulty with day-to-day tasks.
                    Our platform connects the people who care most — families, trained caregivers, kitchen staff,
                    and kind-hearted volunteers — into one supportive community.
                </p>
                <p>
                    We believe that growing older should mean growing into a life filled with dignity,
                    friendship, and the comfort of knowing someone is always there.
                </p>
                <ul class="about-features">
                    <li>Round-the-clock health monitoring</li>
                    <li>Nutritious meals tailored to diets</li>
                    <li>Social activities for active living</li>
                    <li>Real-time updates for family</li>
                </ul>
            </div>
            <div>
                <div class="img-placeholder" style="min-height: 380px; background: var(--white);" role="img" aria-label="Caregiver and resident laughing together">
                    <span class="ph-icon">😊</span>
                    Caregiver and resident laughing
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== 3. ECOSYSTEM OVERVIEW ========== -->
<section id="ecosystem" class="landing-section section-white">
    <div class="section-inner">
        <div class="section-heading">
            <h2>Our Care Ecosystem</h2>
            <p>Everything your loved ones need, beautifully integrated into one caring environment.</p>
        </div>
        
        <div class="eco-grid">
            <a href="<?php echo sanitize(public_url('care.php')); ?>" class="eco-card eco-care">
                <div class="eco-card-content">
                    <span class="eco-icon" aria-hidden="true">🏥</span>
                    <h3>Care &amp; Wellness</h3>
                    <p>24/7 monitoring and personalized healthcare.</p>
                </div>
            </a>
            <a href="<?php echo sanitize(public_url('food.php')); ?>" class="eco-card eco-food">
                <div class="eco-card-content">
                    <span class="eco-icon" aria-hidden="true">🍽️</span>
                    <h3>Food &amp; Nutrition</h3>
                    <p>Wholesome meals tailored to special diets.</p>
                </div>
            </a>
            <a href="<?php echo sanitize(public_url('activities.php')); ?>" class="eco-card eco-activities">
                <div class="eco-card-content">
                    <span class="eco-icon" aria-hidden="true">🎨</span>
                    <h3>Activities &amp; Community</h3>
                    <p>Yoga, art, music, and daily social events.</p>
                </div>
            </a>
            <a href="<?php echo sanitize(public_url('facilities.php')); ?>" class="eco-card eco-facilities">
                <div class="eco-card-content">
                    <span class="eco-icon" aria-hidden="true">🛏️</span>
                    <h3>Facilities &amp; Rooms</h3>
                    <p>Accessible, comfortable private and shared spaces.</p>
                </div>
            </a>
        </div>
        <div class="text-center" style="margin-top: 2.5rem;">
            <a href="<?php echo sanitize(public_url('services.php')); ?>" class="btn-navy">View All Services</a>
        </div>
    </div>
</section>

<!-- ========== 4. SUPPORT (DONATE & VOLUNTEER) ========== -->
<section id="support" class="landing-section section-lavender">
    <div class="section-inner">
        <div class="section-heading">
            <h2>Get Involved</h2>
            <p>Join our mission to provide the best possible care and support for seniors.</p>
        </div>
        
        <div class="support-grid">
            <div class="support-block support-donate">
                <h2>Make a Donation</h2>
                <p>Your contributions help us provide subsidized care and better facilities for seniors in need.</p>
                <a href="<?php echo sanitize(public_url('donation.php')); ?>" class="btn-outline-white">Donate Now</a>
            </div>
            <div class="support-block support-volunteer">
                <h2>Become a Volunteer</h2>
                <p>Give the gift of your time. Lead an activity or simply be a companion to a resident.</p>
                <a href="<?php echo sanitize(public_url('volunteer.php')); ?>" class="btn-navy">Join as a Volunteer</a>
            </div>
        </div>
    </div>
</section>

<!-- ========== 5. STORIES ========== -->
<section id="stories" class="landing-section section-white">
    <div class="section-inner">
        <div class="section-heading">
            <h2>Stories from Our Community</h2>
            <p>Real experiences from the people who live, work, and volunteer here.</p>
        </div>

        <div class="card-grid">
            <article class="overview-card">
                <h3>"I finally feel like I belong somewhere."</h3>
                <p>After losing my husband, I thought I'd spend the rest of my days alone. Here, I have friends, activities, and people who genuinely care about me.</p>
                <strong style="color: var(--navy); font-size: 0.9rem;">— Rina Begum, Resident</strong>
            </article>

            <article class="overview-card">
                <h3>"The daily updates give me real peace of mind."</h3>
                <p>I live in another city but I always know how my mother is doing — what she ate, what activities she joined, and how she's feeling. It means everything.</p>
                <strong style="color: var(--navy); font-size: 0.9rem;">— Kamal Hossain, Family Member</strong>
            </article>

            <article class="overview-card">
                <h3>"Volunteering here changed my perspective."</h3>
                <p>I started coming on weekends to read stories. Now I can't imagine my week without it. The residents teach me more than I could ever give.</p>
                <strong style="color: var(--navy); font-size: 0.9rem;">— Fatema Akter, Volunteer</strong>
            </article>
        </div>
    </div>
</section>

<!-- ========== 6. FAQ & CONTACT CTA ========== -->
<section id="faq" class="landing-section section-cream">
    <div class="section-inner">
        <div class="section-heading">
            <h2>Frequently Asked Questions</h2>
            <p>Answers to the things families and visitors ask most often.</p>
        </div>

        <div class="faq-list" role="region" aria-label="Frequently asked questions">
            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    How do I admit a family member as a resident?
                    <span class="faq-toggle" aria-hidden="true">+</span>
                </button>
                <div class="faq-answer" role="region">
                    <p>Start by registering on the platform as a Family member. Once registered, contact our management team through the contact form or call us. We'll walk you through the admission process, health assessment, and room selection.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    What are the visiting hours?
                    <span class="faq-toggle" aria-hidden="true">+</span>
                </button>
                <div class="faq-answer" role="region">
                    <p>Families can visit daily between 9:00 AM and 7:00 PM. Special arrangements can be made for visits outside these hours — just contact us in advance.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    Can residents choose their meals?
                    <span class="faq-toggle" aria-hidden="true">+</span>
                </button>
                <div class="faq-answer" role="region">
                    <p>Yes. We publish weekly menus and residents can share their preferences. Our kitchen accommodates dietary restrictions including diabetic, low-sodium, and vegetarian options.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    Is there a doctor on site?
                    <span class="faq-toggle" aria-hidden="true">+</span>
                </button>
                <div class="faq-answer" role="region">
                    <p>We have trained caregivers on site around the clock. A visiting doctor conducts regular checkups, and our emergency response system ensures medical help is always close by.</p>
                </div>
            </div>
        </div>

        <div class="contact-cta-block">
            <h3>Still have questions?</h3>
            <p>We're here to help you and your family.</p>
            <div class="contact-info-row">
                <span>📞 <a href="tel:+8801700000000" style="color: var(--navy); font-weight: 600; text-decoration: none;">+880 1700-000-000</a></span>
                <span>✉️ <a href="mailto:care@elderlycare.org" style="color: var(--navy); font-weight: 600; text-decoration: none;">care@elderlycare.org</a></span>
            </div>
            <a href="<?php echo sanitize(public_url('contact.php')); ?>" class="btn-coral">Send us a message</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
