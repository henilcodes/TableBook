<?php
ob_start();
?>

<div class="container my-5">
    <div class="row mb-5">
        <div class="col-lg-6">
            <h1 class="display-4 fw-bold mb-4">Contact Us</h1>
            <p class="lead text-muted mb-4">
                Have questions or need assistance? Our team is here to help you with your reservations and any inquiries you might have.
            </p>
            
            <div class="d-flex align-items-start mb-4">
                <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3">
                    <i class="bi bi-geo-alt-fill text-primary fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1">Visit Us</h5>
                    <p class="text-muted mb-0">123 Culinary Avenue, Foodie District, NY 10001</p>
                </div>
            </div>

            <div class="d-flex align-items-start mb-4">
                <div class="bg-success bg-opacity-10 p-3 rounded-3 me-3">
                    <i class="bi bi-telephone-fill text-success fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1">Call Us</h5>
                    <p class="text-muted mb-0">+1 (555) 123-4567</p>
                </div>
            </div>

            <div class="d-flex align-items-start mb-4">
                <div class="bg-info bg-opacity-10 p-3 rounded-3 me-3">
                    <i class="bi bi-envelope-fill text-info fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1">Email Us</h5>
                    <p class="text-muted mb-0">support@tabletap.com</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card border-0 shadow-lg p-4 p-md-5 rounded-4">
                <h3 class="fw-bold mb-4">Send us a message</h3>
                <form action="<?= url('/contact/send') ?>" method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="_token" value="<?= $csrf_token ?? '' ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" name="first_name" class="form-control" placeholder="John" required>
                                <div class="invalid-feedback">First name required.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" name="last_name" class="form-control" placeholder="Doe" required>
                                <div class="invalid-feedback">Last name required.</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="bi bi-envelope text-muted"></i></span>
                                <input type="email" name="email" class="form-control" placeholder="john@example.com" required>
                                <div class="invalid-feedback">Valid email required.</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Subject</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="bi bi-chat-dots text-muted"></i></span>
                                <select name="subject" class="form-select">
                                    <option value="General Inquiry" selected>General Inquiry</option>
                                    <option value="Reservation Help">Reservation Help</option>
                                    <option value="Restaurant Partnership">Restaurant Partnership</option>
                                    <option value="Feedback">Feedback</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Message</label>
                            <textarea name="message" class="form-control" rows="4" placeholder="How can we help you today?" required></textarea>
                            <div class="invalid-feedback">Please enter your message.</div>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg w-100 rounded-3 shadow-sm" id="contactSubmitBtn">
                                <span id="btnDefault">Send Message <i class="bi bi-send ms-2"></i></span>
                                <span id="btnLoading" class="d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                    Sending...
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Contact Us - TableTap';

$scripts = <<<HTML
<!-- Loading Overlay -->
<div id="contactLoadingOverlay" style="
    display:none; position:fixed; inset:0; z-index:9999;
    background:rgba(21,34,56,0.65); backdrop-filter:blur(6px);
    justify-content:center; align-items:center; flex-direction:column;
">
    <div style="text-align:center; color:#fff;">
        <div style="
            width:72px; height:72px; border-radius:50%;
            border:5px solid rgba(255,255,255,0.2);
            border-top-color:#1f7a8c;
            animation:spin 0.85s linear infinite;
            margin:0 auto 20px;
        "></div>
        <h5 style="margin:0; font-family:Poppins,sans-serif; font-weight:700;">Sending your message…</h5>
        <p style="margin:8px 0 0; font-size:14px; opacity:.75;">Please wait a moment</p>
    </div>
</div>
<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>
<script>
(function () {
    const form = document.querySelector('form.needs-validation');
    const overlay = document.getElementById('contactLoadingOverlay');
    const btnDefault = document.getElementById('btnDefault');
    const btnLoading = document.getElementById('btnLoading');
    const submitBtn = document.getElementById('contactSubmitBtn');

    if (!form) return;

    form.addEventListener('submit', function (e) {
        // Run Bootstrap native validation
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            form.classList.add('was-validated');
            return;
        }
        // Show overlay and loading button
        overlay.style.display = 'flex';
        btnDefault.classList.add('d-none');
        btnLoading.classList.remove('d-none');
        submitBtn.disabled = true;
    });
})();
</script>
HTML;

require APP_PATH . '/Views/layouts/public.php';
?>
