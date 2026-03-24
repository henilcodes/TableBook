<?php
ob_start();
?>

<div class="container my-5">
    <div class="row align-items-center mb-5">
        <div class="col-lg-6">
            <h1 class="display-4 fw-bold mb-4">Our Story</h1>
            <p class="lead text-muted mb-4">
                Founded in 2024, TableTap was born out of a simple passion: making fine dining accessible and table reservations effortless. 
                We believe that the best memories are made over a great meal, and we're here to ensure your journey starts perfectly.
            </p>
            <p class="mb-4">
                Our platform connects food enthusiasts with the finest culinary experiences. Whether it's a romantic dinner for two, 
                a family celebration, or a corporate event, TableTap provides the tools you need to find the perfect table at the right time.
            </p>
            <div class="row g-4 mt-2">
                <div class="col-6">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="bi bi-shop text-primary fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">100+</h5>
                            <small class="text-muted">Partners</small>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="bi bi-people text-success fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">50k+</h5>
                            <small class="text-muted">Happy Diners</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mt-5 mt-lg-0">
            <img src="<?= htmlspecialchars(image_url('public/assets/img/about_us_restaurant.webp')) ?>" alt="About TableTap" class="img-fluid rounded-4 shadow-lg">
        </div>
    </div>

    <div class="row text-center mt-5 pt-5 border-top">
        <div class="col-12 mb-5">
            <h2 class="fw-bold">Why Choose Us?</h2>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm p-4">
                <div class="bg-primary bg-opacity-10 p-3 rounded-3 d-inline-block mx-auto mb-3">
                    <i class="bi bi-lightning-charge-fill text-primary fs-3"></i>
                </div>
                <h4>Instant Booking</h4>
                <p class="text-muted">Real-time availability and instant confirmation for your peace of mind.</p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm p-4">
                <div class="bg-warning bg-opacity-10 p-3 rounded-3 d-inline-block mx-auto mb-3">
                    <i class="bi bi-star-fill text-warning fs-3"></i>
                </div>
                <h4>Curated Selection</h4>
                <p class="text-muted">We partner only with the best local restaurants to ensure quality experiences.</p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm p-4">
                <div class="bg-info bg-opacity-10 p-3 rounded-3 d-inline-block mx-auto mb-3">
                    <i class="bi bi-heart-fill text-info fs-3"></i>
                </div>
                <h4>Personalized</h4>
                <p class="text-muted">Tailored recommendations and easy management of your preferences.</p>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'About Us - TableTap';
require APP_PATH . '/Views/layouts/public.php';
?>
