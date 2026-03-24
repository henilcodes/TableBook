<?php
ob_start();
?>

<div class="container my-5">
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold">Customer Support</h1>
        <p class="lead text-muted">How can we help you today?</p>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm text-center p-4 rounded-4">
                <div class="bg-primary bg-opacity-10 p-3 rounded-circle d-inline-block mx-auto mb-3">
                    <i class="bi bi-question-circle-fill text-primary fs-3"></i>
                </div>
                <h4>FAQs</h4>
                <p class="text-muted">Find answers to commonly asked questions about reservations and accounts.</p>
                <a href="#faqs" class="btn btn-outline-primary btn-sm mt-auto">View FAQs</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm text-center p-4 rounded-4">
                <div class="bg-success bg-opacity-10 p-3 rounded-circle d-inline-block mx-auto mb-3">
                    <i class="bi bi-chat-dots-fill text-success fs-3"></i>
                </div>
                <h4>Live Support</h4>
                <p class="text-muted">Chat with our support team for immediate assistance during business hours.</p>
                <button class="btn btn-outline-success btn-sm mt-auto">Start Chat</button>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm text-center p-4 rounded-4">
                <div class="bg-info bg-opacity-10 p-3 rounded-circle d-inline-block mx-auto mb-3">
                    <i class="bi bi-envelope-fill text-info fs-3"></i>
                </div>
                <h4>Email Support</h4>
                <p class="text-muted">Send us an email and we'll get back to you within 24 hours.</p>
                <a href="<?= url('/contact') ?>" class="btn btn-outline-info btn-sm mt-auto">Send Email</a>
            </div>
        </div>
    </div>

    <div id="faqs" class="mt-5 pt-5">
        <h2 class="fw-bold mb-4 text-center">Frequently Asked Questions</h2>
        <div class="accordion accordion-flush shadow-sm rounded-4 overflow-hidden" id="faqAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                        How do I make a reservation?
                    </button>
                </h2>
                <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-muted">
                        Simply search for a restaurant, select your preferred date, time, and party size, and follow the checkout process. You'll receive an instant confirmation.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                        Can I cancel or modify my booking?
                    </button>
                </h2>
                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-muted">
                        Yes, you can manage your bookings through your Account Dashboard. Please note that cancellations may have specific time windows depending on the restaurant.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                        Is there a fee to book?
                    </button>
                </h2>
                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body text-muted">
                        TableTap is free for diners! Some restaurants might require a small deposit for large parties, which will be clearly shown during checkout.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Support & FAQs - TableTap';
require APP_PATH . '/Views/layouts/public.php';
?>
