document.addEventListener('DOMContentLoaded', function () {

    // ===== Mobile nav toggle =====
    var navToggle = document.getElementById('navToggle');
    var navMenu = document.getElementById('navMenu');

    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function () {
            navMenu.classList.toggle('open');
            var isOpen = navMenu.classList.contains('open');
            navToggle.setAttribute('aria-expanded', isOpen);
        });
    }

    // ===== Mobile dropdown toggle =====
    var dropdownLinks = document.querySelectorAll('.has-dropdown > a');
    dropdownLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            if (window.innerWidth <= 992) {
                e.preventDefault();
                var parent = link.parentElement;
                // close other open dropdowns
                document.querySelectorAll('.has-dropdown.dropdown-open').forEach(function (other) {
                    if (other !== parent) other.classList.remove('dropdown-open');
                });
                parent.classList.toggle('dropdown-open');
            }
        });
    });

    // close mobile menu when clicking a non-dropdown link
    document.querySelectorAll('.nav-links > li:not(.has-dropdown) > a').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth <= 992 && navMenu) {
                navMenu.classList.remove('open');
                if (navToggle) navToggle.setAttribute('aria-expanded', 'false');
            }
        });
    });

    // ===== FAQ accordion =====
    var faqItems = document.querySelectorAll('.faq-item');
    faqItems.forEach(function (item) {
        var btn = item.querySelector('.faq-question');
        if (!btn) return;

        btn.addEventListener('click', function () {
            var wasOpen = item.classList.contains('open');

            // close all
            faqItems.forEach(function (other) {
                other.classList.remove('open');
                var otherBtn = other.querySelector('.faq-question');
                if (otherBtn) otherBtn.setAttribute('aria-expanded', 'false');
            });

            // toggle this one
            if (!wasOpen) {
                item.classList.add('open');
                btn.setAttribute('aria-expanded', 'true');
            }
        });
    });

    // ===== Donation amount buttons =====
    var amountBtns = document.querySelectorAll('.amount-btn');
    amountBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            amountBtns.forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
        });
    });

    // ===== Contact form validation =====
    var contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function (e) {
            var valid = true;

            // clear previous errors
            contactForm.querySelectorAll('.form-error').forEach(function (el) {
                el.classList.remove('show');
            });

            var name = contactForm.querySelector('[name="contact_name"]');
            var email = contactForm.querySelector('[name="contact_email"]');
            var subject = contactForm.querySelector('[name="contact_subject"]');
            var message = contactForm.querySelector('[name="contact_message"]');

            if (name && name.value.trim().length < 2) {
                showError(name, 'Please enter your name.');
                valid = false;
            }
            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
                showError(email, 'Please enter a valid email address.');
                valid = false;
            }
            if (subject && subject.value.trim().length < 2) {
                showError(subject, 'Please enter a subject.');
                valid = false;
            }
            if (message && message.value.trim().length < 10) {
                showError(message, 'Message should be at least 10 characters.');
                valid = false;
            }

            if (!valid) e.preventDefault();
        });
    }

    function showError(input, msg) {
        var errEl = input.parentElement.querySelector('.form-error');
        if (errEl) {
            errEl.textContent = msg;
            errEl.classList.add('show');
        }
    }

});
