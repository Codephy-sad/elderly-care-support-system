<?php ?>
</main>
<?php if (isset($isLandingPage) && $isLandingPage): ?>
<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-grid">
            <div class="footer-col">
                <h4>🏡 Elderly Care &amp; Support</h4>
                <p>
                    A caring community for senior residents, their families, and everyone who
                    believes in compassionate aging. Built with love, run by people who care.
                </p>
            </div>
            <div class="footer-col">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="<?php echo sanitize(public_url('index.php#about')); ?>">About Us</a></li>
                    <li><a href="<?php echo sanitize(public_url('services.php')); ?>">Services</a></li>
                    <li><a href="<?php echo sanitize(public_url('facilities.php')); ?>">Facilities</a></li>
                    <li><a href="<?php echo sanitize(public_url('index.php#faq')); ?>">FAQ</a></li>
                    <li><a href="<?php echo sanitize(public_url('contact.php')); ?>">Contact</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Get Involved</h4>
                <ul>
                    <li><a href="<?php echo sanitize(public_url('register.php')); ?>">Register</a></li>
                    <li><a href="<?php echo sanitize(public_url('login.php')); ?>">Login</a></li>
                    <li><a href="<?php echo sanitize(public_url('donation.php')); ?>">Make a Donation</a></li>
                    <li><a href="<?php echo sanitize(public_url('volunteer.php')); ?>">Volunteer</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Contact</h4>
                <ul>
                    <li>📍 123 Care Lane, Dhaka</li>
                    <li><a href="tel:+8801700000000">📞 +880 1700-000-000</a></li>
                    <li><a href="mailto:care@elderlycare.org">✉️ care@elderlycare.org</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; <?php echo date('Y'); ?> Elderly Care &amp; Support Platform. All rights reserved.
        </div>
    </div>
</footer>
<?php else: ?>
<footer class="app-footer py-3 mt-auto">
    <div class="container text-center">
        <small>&copy; <?php echo sanitize((string)date('Y')); ?> Elderly Care &amp; Support Platform</small>
    </div>
</footer>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if (isset($isLandingPage) && $isLandingPage): ?>
<script src="<?php echo sanitize(app_base_url() . '/assets/js/landing.js'); ?>"></script>
<?php endif; ?>
</body>
</html>
