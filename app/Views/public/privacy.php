<?php
ob_start();
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-sm p-4 p-md-5 rounded-4">
                <h1 class="fw-bold mb-4 text-center">Privacy Policy</h1>
                <p class="text-muted text-center mb-5">Last Updated: March 24, 2026</p>

                <div class="privacy-content">
                    <section class="mb-5">
                        <h4 class="fw-bold">1. Information We Collect</h4>
                        <p>We collect information that you provide directly to us when you make a reservation, create an account, or contact us. This may include your name, email address, phone number, and any special requests or preferences you share.</p>
                    </section>

                    <section class="mb-5">
                        <h4 class="fw-bold">2. How We Use Your Information</h4>
                        <p>Your information is used to facilitate your restaurant reservations, provide customer support, and improve our services. We may also send you confirmation emails and updates regarding your bookings.</p>
                    </section>

                    <section class="mb-5">
                        <h4 class="fw-bold">3. Sharing of Information</h4>
                        <p>We share your reservation details with the respective restaurant to fulfill your booking. We do not sell your personal information to third parties.</p>
                    </section>

                    <section class="mb-5">
                        <h4 class="fw-bold">4. Data Security</h4>
                        <p>We implement industry-standard security measures to protect your personal information. However, no method of transmission over the internet is 100% secure.</p>
                    </section>

                    <section class="mb-5">
                        <h4 class="fw-bold">5. Your Choices</h4>
                        <p>You can access and update your account information at any time through your dashboard. You may also opt-out of marketing communications by following the instructions in those emails.</p>
                    </section>
                </div>
                
                <div class="text-center mt-4 pt-4 border-top">
                    <p class="mb-0 text-muted">If you have any questions about our Privacy Policy, please <a href="<?= url('/contact') ?>">contact us</a>.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Privacy Policy - TableTap';
require APP_PATH . '/Views/layouts/public.php';
?>
