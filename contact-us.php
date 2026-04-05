<?php
session_start();
$pageTitle = "Contact Us — Village Foods";
include 'includes/header.php';
include 'includes/navbar.php';
?>

<style>
    .contact-hero { background: linear-gradient(135deg, var(--primary), #15803d); padding: 80px 0 60px; color: white; text-align: center; border-radius: 0 0 40px 40px; }
    .contact-container { max-width: 1000px; margin: -40px auto 80px; padding: 0 20px; display: grid; grid-template-columns: 1fr 1.5fr; gap: 30px; }
    
    .contact-info-card { background: white; padding: 40px; border-radius: 24px; box-shadow: var(--shadow-lg); height: fit-content; }
    .contact-info-item { display: flex; align-items: flex-start; gap: 16px; margin-bottom: 24px; }
    .contact-info-icon { width: 48px; height: 48px; min-width: 48px; border-radius: 14px; background: var(--primary-pale); color: var(--primary); display: flex; align-items: center; justify-content: center; }
    .contact-info-text h4 { margin: 0 0 4px 0; font-size: 16px; font-weight: 800; color: var(--text); }
    .contact-info-text p { margin: 0; font-size: 14px; color: var(--text-muted); font-weight: 500; line-height: 1.5; }

    .contact-social-icon { 
        width: 40px; height: 40px; border-radius: 12px; background: var(--bg); color: var(--text-muted); 
        display: flex; align-items: center; justify-content: center; transition: all 0.2s; 
        border: 1px solid var(--border-light); 
    }
    .contact-social-icon:hover { 
        background: var(--primary); color: white; border-color: var(--primary); transform: translateY(-2px); 
    }
    .contact-social-icon i { width: 20px; height: 20px; }

    .contact-form-card { background: white; padding: 40px; border-radius: 24px; box-shadow: var(--shadow-lg); }
    .contact-form-title { font-size: 24px; font-weight: 800; color: var(--text); margin-bottom: 8px; }
    .contact-form-sub { color: var(--text-muted); font-size: 14px; font-weight: 500; margin-bottom: 30px; }

    .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .contact-textarea { min-height: 150px; resize: none; }

    @media (max-width: 768px) {
        .contact-container { grid-template-columns: 1fr; margin-top: 20px; }
        .contact-hero { padding: 60px 0 40px; border-radius: 0 0 30px 30px; }
        .form-grid-2 { grid-template-columns: 1fr; }
    }
</style>

<div class="contact-hero">
    <div class="container">
        <h1 style="font-family:'Sora', sans-serif; font-weight:800; font-size:36px; margin-bottom:12px">Get in Touch</h1>
        <p style="font-size:16px; opacity:0.9; max-width:600px; margin:0 auto; font-weight:500">Have questions about your order or our services? We're here to help!</p>
    </div>
</div>

<div class="contact-container">
    <!-- Contact Info -->
    <div class="contact-info-card">
        <div class="contact-info-item">
            <div class="contact-info-icon"><i data-lucide="phone-call" style="width:24px;height:24px"></i></div>
            <div class="contact-info-text">
                <h4>Call Us</h4>
                <p>+91 63800 91001</p>
                <p>Mon - Sun, 9:00 AM - 10:00 PM</p>
            </div>
        </div>
        <div class="contact-info-item">
            <div class="contact-info-icon"><i data-lucide="mail" style="width:24px;height:24px"></i></div>
            <div class="contact-info-text">
                <h4>Email Us</h4>
                <p>manvaasamtech@gmail.com</p>
                <p>hello@villagefoods.in</p>
            </div>
        </div>
        <div class="contact-info-item">
            <div class="contact-info-icon"><i data-lucide="map-pin" style="width:24px;height:24px"></i></div>
            <div class="contact-info-text">
                <h4>Visit Us</h4>
                <p>Thirupathur District,</p>
                <p>Tamil Nadu, India - 635851</p>
            </div>
        </div>
        <div class="contact-info-item" style="margin-bottom:0">
            <div class="contact-info-icon"><i data-lucide="share-2" style="width:24px;height:24px"></i></div>
            <div class="contact-info-text">
                <h4>Follow Us</h4>
                <div style="display:flex; gap:10px; margin-top:12px; flex-wrap:wrap">
                    <a href="#" class="contact-social-icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg></a>
                    <a href="#" class="contact-social-icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg></a>
                    <a href="#" class="contact-social-icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.42a2.78 2.78 0 0 0-1.94 2C1 8.11 1 12 1 12s0 3.89.46 5.58a2.78 2.78 0 0 0 1.94 2C5.12 20 12 20 12 20s6.88 0 8.6-.42a2.78 2.78 0 0 0 1.94-2C23 15.89 23 12 23 12s0-3.89-.46-5.58z"/><polyline points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02"/></svg></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Form -->
    <div class="contact-form-card">
        <h2 class="contact-form-title">Send a Message</h2>
        <p class="contact-form-sub">We'll respond to your inquiry within 24 hours.</p>

        <form id="contactForm" onsubmit="submitForm(event)">
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" id="contactName" class="form-input" placeholder="Your Name">
                    <div class="invalid-feedback" id="nameError" style="display:none"></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address *</label>
                    <input type="email" id="contactEmail" class="form-input" placeholder="name@example.com">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Subject</label>
                <input type="text" id="contactSubject" class="form-input" placeholder="What is this about?">
            </div>

            <div class="form-group">
                <label class="form-label">Message *</label>
                <textarea id="contactMessage" class="form-input contact-textarea" placeholder="How can we help you?"></textarea>
            </div>

            <button type="submit" class="form-btn" id="submitBtn">
                <span>Send Message</span>
                <i data-lucide="send" style="width:18px;height:18px"></i>
            </button>
        </form>
    </div>
</div>

<script>
    // Real-time validation for Name
    document.getElementById('contactName').addEventListener('input', function(e) {
        Validation.validateInput(e.target, 'alpha', "Only letters and spaces are allowed", document.getElementById('nameError'));
    });

    // Form submission logic
    async function submitForm(e) {
        e.preventDefault();
        
        const nameEl = document.getElementById('contactName');
        const emailEl = document.getElementById('contactEmail');
        const subjectEl = document.getElementById('contactSubject');
        const messageEl = document.getElementById('contactMessage');

        const name = nameEl.value.trim();
        const email = emailEl.value.trim();
        const subject = subjectEl.value.trim();
        const message = messageEl.value.trim();

        if (!name || !email || !message) {
            Toast.show('Please fill all required fields (*)', 'warning');
            return;
        }

        const isNameValid = Validation.validateInput(nameEl, 'alpha', "Only letters and spaces are allowed", document.getElementById('nameError'));
        if (!isNameValid) {
            Toast.show('Please fix the errors before sending', 'error');
            return;
        }

        const btn = document.getElementById('submitBtn');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'Sending...';

        try {
            const res = await fetch('api/contact/submit.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name, email, subject, message })
            });
            const data = await res.json();

            if (data.status === 'success') {
                Toast.show(data.message, 'success');
                document.getElementById('contactForm').reset();
            } else {
                Toast.show(data.message, 'error');
            }
        } catch (error) {
            Toast.show('An error occurred. Please try again.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            if (window.lucide) lucide.createIcons();
        }
    }

    // Force icon creation immediately and on multiple events to ensure visibility
    function initIcons() {
        if (window.lucide) {
            lucide.createIcons();
        }
    }

    // Run immediately
    initIcons();
    
    // Also run on various events just in case
    window.addEventListener('load', initIcons);
    document.addEventListener('DOMContentLoaded', initIcons);
    
    // And one small delay to be absolutely certain (handles slow script loads)
    setTimeout(initIcons, 500);
    setTimeout(initIcons, 2000);
</script>

<?php include 'includes/modals.php'; ?>
<?php include 'includes/footer.php'; ?>
