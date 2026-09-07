<?php
require_once __DIR__ . '/../config/auth.php';

$contactSuccess = false;
$contactError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $cName    = trim($_POST['contact_name'] ?? '');
    $cEmail   = trim($_POST['contact_email'] ?? '');
    $cSubject = trim($_POST['contact_subject'] ?? '');
    $cMessage = trim($_POST['contact_message'] ?? '');

    if (strlen($cName) < 2) {
        $contactError = 'Please enter your name.';
    } elseif (!filter_var($cEmail, FILTER_VALIDATE_EMAIL)) {
        $contactError = 'Please enter a valid email address.';
    } elseif (strlen($cSubject) < 2) {
        $contactError = 'Please enter a subject.';
    } elseif (strlen($cMessage) < 10) {
        $contactError = 'Message should be at least 10 characters.';
    } else {
        $contactSuccess = true;
    }
}

$pageTitle = 'Contact Us — Elderly Care';
$isLandingPage = true;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-hero">
    <div class="page-inner">
        <h1>Contact Us</h1>
        <p class="page-hero-sub">We're here to answer your questions and guide you through our care options.</p>
    </div>
</div>

<section class="page-section section-alt">
    <div class="page-inner contact-grid">
        <div class="contact-form-container detail-card">
            <h2 style="font-family: 'Merriweather', serif; margin-bottom: 1.5rem;">Send us a message</h2>
            
            <?php if ($contactSuccess): ?>
                <div class="form-success">
                    Thank you for reaching out! Your message has been sent successfully. We will get back to you shortly.
                </div>
            <?php else: ?>
                <?php if ($contactError !== ''): ?>
                    <div class="form-error show" style="margin-bottom: 1rem;">
                        <?php echo sanitize($contactError); ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="contact.php" id="contactForm" novalidate>
                    <div class="form-group">
                        <label for="contactName">Your Name</label>
                        <input type="text" id="contactName" name="contact_name" required
                               value="<?php echo sanitize($_POST['contact_name'] ?? ''); ?>"
                               placeholder="e.g. Ayesha Rahman">
                        <span class="form-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="contactEmail">Email Address</label>
                        <input type="email" id="contactEmail" name="contact_email" required
                               value="<?php echo sanitize($_POST['contact_email'] ?? ''); ?>"
                               placeholder="you@example.com">
                        <span class="form-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="contactSubject">Subject</label>
                        <input type="text" id="contactSubject" name="contact_subject" required
                               value="<?php echo sanitize($_POST['contact_subject'] ?? ''); ?>"
                               placeholder="e.g. Admission inquiry">
                        <span class="form-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="contactMessage">Message</label>
                        <textarea id="contactMessage" name="contact_message" required
                                  placeholder="Tell us how we can help..."><?php echo sanitize($_POST['contact_message'] ?? ''); ?></textarea>
                        <span class="form-error"></span>
                    </div>

                    <button type="submit" name="contact_submit" class="btn-navy" style="width: 100%; font-size: 1.1rem; padding: 1rem;">
                        Send Message
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <div class="contact-sidebar">
            <div class="detail-card" style="background: var(--navy); color: var(--white); border: none;">
                <h3 style="color: var(--white);">Contact Information</h3>
                <p style="color: rgba(255,255,255,0.8); margin-bottom: 2rem;">Reach out directly via phone or email, or visit our center during office hours.</p>

                <div class="contact-item" style="color: var(--white);">
                    <span class="ci-icon" aria-hidden="true">📍</span>
                    <div>
                        <h4 style="color: var(--white);">Address</h4>
                        <p style="color: rgba(255,255,255,0.8);">Elderly Care &amp; Support Center<br>123 Care Lane, Dhaka 1205</p>
                    </div>
                </div>

                <div class="contact-item" style="color: var(--white);">
                    <span class="ci-icon" aria-hidden="true">📞</span>
                    <div>
                        <h4 style="color: var(--white);">Phone</h4>
                        <p style="color: rgba(255,255,255,0.8);"><a href="tel:+8801700000000" style="color: var(--amber);">+880 1700-000-000</a></p>
                    </div>
                </div>

                <div class="contact-item" style="color: var(--white);">
                    <span class="ci-icon" aria-hidden="true">✉️</span>
                    <div>
                        <h4 style="color: var(--white);">Email</h4>
                        <p style="color: rgba(255,255,255,0.8);"><a href="mailto:care@elderlycare.org" style="color: var(--amber);">care@elderlycare.org</a></p>
                    </div>
                </div>

                <div class="contact-item" style="color: var(--white);">
                    <span class="ci-icon" aria-hidden="true">🕐</span>
                    <div>
                        <h4 style="color: var(--white);">Office Hours</h4>
                        <p style="color: rgba(255,255,255,0.8);">Sat – Thu: 8:00 AM – 6:00 PM<br>Fri: 10:00 AM – 2:00 PM</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
