<?php
ob_start();
?>

<div class="container my-5 py-5 text-center">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5">
                <div class="mb-4">
                    <div class="bg-primary bg-opacity-10 p-4 rounded-circle d-inline-block mb-3">
                        <i class="bi bi-credit-card-fill text-primary display-4"></i>
                    </div>
                    <h2 class="fw-bold">Checkout & Secure Table</h2>
                    <p class="text-muted">You're almost there! Complete the payment to securely lock in your reservation at <strong><?= htmlspecialchars($restaurant['name']) ?></strong>.</p>
                </div>
                
                <div class="payment-summary bg-light rounded-4 p-4 mb-4 text-start">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Date & Time</span>
                        <span class="fw-bold"><?= date('M j, Y', strtotime($pending['reservation_date'])) ?> at <?= date('g:i A', strtotime($pending['reservation_time'])) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Party Size</span>
                        <span class="fw-bold"><?= htmlspecialchars($pending['party_size']) ?> Guests</span>
                    </div>
                    
                    <hr class="my-3">
                    
                    <?php if ($preorder_total > 0): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Pre-order Menu Total</span>
                        <span class="fw-bold"><?= currency($preorder_total) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Table Booking Fee</span>
                        <span class="fw-bold text-danger">+<?= currency($booking_fee) ?></span>
                    </div>
                    
                    <hr class="my-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold fs-5">Final Total</span>
                        <span class="fw-bold fs-4 text-primary"><?= currency($total) ?></span>
                    </div>
                </div>
                
                <button id="rzp-button" class="btn btn-primary btn-lg w-100 rounded-pill py-3 fw-bold shadow-sm">
                    <i class="bi bi-shield-lock-fill me-2 border-end pe-2"></i> Pay Now with Razorpay
                </button>
                
                <div class="mt-4 text-muted small">
                    <i class="bi bi-info-circle me-1"></i> Your payment is processed securely by Razorpay.
                </div>
            </div>
            
            <a href="<?= url('/account') ?>" class="btn btn-link link-secondary mt-3 text-decoration-none">
                <i class="bi bi-arrow-left me-1 small"></i> Cancel and return to dashboard
            </a>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loading-overlay" class="position-fixed w-100 h-100 top-0 start-0 d-flex flex-column justify-content-center align-items-center bg-white bg-opacity-75 d-none" style="z-index: 9999 !important; backdrop-filter: blur(4px);">
    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <h4 class="mt-3 fw-bold text-dark">Confirming your reservation...</h4>
    <p class="text-muted">Sending confirmation emails. Please don't close this window.</p>
</div>

<form id="razorpay-form" action="<?= url('/verify-payment') ?>" method="POST" style="display:none;">
    <input type="hidden" name="_token" value="<?= $csrf_token ?>">
    <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
    <input type="hidden" name="razorpay_order_id" id="razorpay_order_id" value="<?= htmlspecialchars($order['id']) ?>">
    <input type="hidden" name="razorpay_signature" id="razorpay_signature">
</form>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    const options = {
        "key": "<?= $razorpay_key ?>",
        "amount": "<?= $order['amount'] ?>",
        "currency": "<?= $order['currency'] ?>",
        "name": "TableTap",
        "description": "Table Reservation - <?= htmlspecialchars($restaurant['name']) ?>",
        "image": "<?= url('/public/assets/img/logo.png') ?>",
        "order_id": "<?= $order['id'] ?>",
        "handler": function (response){
            document.getElementById('loading-overlay').classList.remove('d-none');
            document.getElementById('rzp-button').disabled = true;
            
            document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
            document.getElementById('razorpay_order_id').value = response.razorpay_order_id;
            document.getElementById('razorpay_signature').value = response.razorpay_signature;
            document.getElementById('razorpay-form').submit();
        },
        "prefill": {
            "name": "<?= htmlspecialchars($_SESSION['customer_name'] ?? '') ?>",
            "email": "<?= htmlspecialchars($_SESSION['customer_email'] ?? '') ?>",
            "contact": "<?= htmlspecialchars($_SESSION['customer_phone'] ?? '') ?>"
        },
        "theme": {
            "color": "#0d6efd"
        }
    };
    const rzp = new Razorpay(options);
    document.getElementById('rzp-button').onclick = function(e){
        rzp.open();
        e.preventDefault();
    }
</script>

<?php
$content = ob_get_clean();
require APP_PATH . '/Views/layouts/public.php';
?>
