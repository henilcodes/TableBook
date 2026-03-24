<?php
ob_start();
?>

<!-- Hero Section -->
<section class="hero-section d-flex align-items-center" style="min-height: 80vh;">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 text-center text-lg-start animate-fade-up">
                <h1 class="display-3 fw-bold mb-4 lh-sm">Reserve Your <br><span class="text-warning">Perfect Table</span></h1>
                <p class="lead mb-4 fw-normal opacity-75">Discover great places, pick your time, pre-order your favorite dishes, and confirm your reservation in just a few taps.</p>
                <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-lg-start gap-3 mt-5">
                    <a href="<?= url('/restaurants') ?>" class="btn btn-light btn-lg px-5 py-3 shadow-sm rounded-pill fw-bold text-primary">
                        <i class="bi bi-search me-2"></i> Browse Restaurants
                    </a>
                </div>
                
                <div class="mt-5 pt-4 d-flex align-items-center justify-content-center justify-content-lg-start gap-4 opacity-75">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill fs-4 text-warning"></i>
                        <span class="fw-semibold">Instant Booking</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-star-fill fs-4 text-warning"></i>
                        <span class="fw-semibold">Verified Reviews</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block animate-fade-up" style="animation-delay: 0.2s;">
                <div class="position-relative">
                    <div class="bg-white rounded-5 shadow-lg p-4 position-relative z-1" style="transform: rotate(2deg);">
                        <img src="<?= url('/public/assets/img/restaurant-placeholder.svg') ?>" class="img-fluid rounded-4 w-100" alt="Dining App" style="height: 400px; object-fit: cover;">
                    </div>
                    <!-- Decorative back blob -->
                    <div class="position-absolute bg-warning rounded-circle opacity-50 blur-lg" style="width: 300px; height: 300px; top: -50px; right: -20px; z-index: 0; filter: blur(40px);"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How it Works -->
<section class="py-5 bg-white">
    <div class="container py-5">
        <div class="text-center mb-5 pb-3">
            <h2 class="display-6 fw-bold">How <span class="text-gradient">TableTap</span> Works</h2>
            <p class="text-muted lead">Your perfect dining experience in four simple steps</p>
        </div>
        <div class="row g-4 text-center">
            <div class="col-md-6 col-lg-3">
                <div class="p-4 rounded-4 bg-light h-100 border border-light transition-hover text-center">
                    <div class="bg-primary text-white d-inline-flex align-items-center justify-content-center rounded-circle mb-4 shadow-sm" style="width: 80px; height: 80px; font-size: 2rem;">
                        <i class="bi bi-search"></i>
                    </div>
                    <h5 class="fw-bold">1. Find a Restaurant</h5>
                    <p class="text-muted small">Explore top-rated venues by cuisine, location, or rating.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="p-4 rounded-4 bg-light h-100 border border-light transition-hover text-center">
                    <div class="bg-primary text-white d-inline-flex align-items-center justify-content-center rounded-circle mb-4 shadow-sm" style="width: 80px; height: 80px; font-size: 2rem;">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <h5 class="fw-bold">2. Pick a Time</h5>
                    <p class="text-muted small">Select your preferred date, time, and party size instantly.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="p-4 rounded-4 bg-light h-100 border border-light transition-hover text-center">
                    <div class="bg-primary text-white d-inline-flex align-items-center justify-content-center rounded-circle mb-4 shadow-sm" style="width: 80px; height: 80px; font-size: 2rem;">
                        <i class="bi bi-menu-up"></i>
                    </div>
                    <h5 class="fw-bold">3. Pre-order Menu</h5>
                    <p class="text-muted small">Optionally pre-order your favorite dishes right to your table.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="p-4 rounded-4 bg-light h-100 border border-light transition-hover text-center">
                    <div class="bg-primary text-white d-inline-flex align-items-center justify-content-center rounded-circle mb-4 shadow-sm" style="width: 80px; height: 80px; font-size: 2rem;">
                        <i class="bi bi-emoji-smile"></i>
                    </div>
                    <h5 class="fw-bold">4. Dine & Enjoy</h5>
                    <p class="text-muted small">Arrive at the restaurant. Your table is ready and waiting.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Restaurants -->
<section class="public-section py-5 bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-end mb-5">
            <div>
                <h2 class="display-6 fw-bold mb-0">Featured <span class="text-gradient">Restaurants</span></h2>
                <p class="text-muted lead mb-0">Discover our users' top picks this week.</p>
            </div>
            <a href="<?= url('/restaurants') ?>" class="btn btn-outline-primary rounded-pill d-none d-md-inline-block">View All <i class="bi bi-arrow-right"></i></a>
        </div>
        
        <div class="row g-4">
            <?php if (empty($restaurants)): ?>
                <div class="col-12 text-center py-5 bg-white rounded-4 shadow-sm">
                    <div class="text-muted mb-3"><i class="bi bi-shop fs-1"></i></div>
                    <h5 class="fw-bold">No restaurants available right now.</h5>
                    <p class="text-muted">Please check back later.</p>
                </div>
            <?php else: ?>
                <?php foreach (array_slice($restaurants, 0, 6) as $restaurant): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card restaurant-card h-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative group">
                            <span class="position-absolute top-0 end-0 m-3 badge bg-white text-dark shadow-sm rounded-pill px-3 py-2 z-1">
                                <i class="bi bi-star-fill text-warning"></i> <?= number_format($restaurant['rating'], 1) ?>
                            </span>
                            
                            <img src="<?= htmlspecialchars(image_url($restaurant['image_url'] ?? null)) ?>" class="card-img-top" alt="<?= htmlspecialchars($restaurant['name']) ?>" style="height: 240px; object-fit: cover; transition: transform 0.5s ease;">
                            
                            <div class="card-body p-4 position-relative bg-white z-1">
                                <span class="badge text-bg-primary bg-opacity-10 text-primary mb-2 px-3 py-2 rounded-pill"><?= htmlspecialchars($restaurant['cuisine_type']) ?></span>
                                <h4 class="card-title fw-bold mb-2"><?= htmlspecialchars($restaurant['name']) ?></h4>
                                <p class="card-text text-muted mb-4 small" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?= htmlspecialchars(strip_tags($restaurant['description'] ?? '')) ?></p>
                                
                                <a href="<?= url('/restaurants/' . htmlspecialchars($restaurant['slug'])) ?>" class="btn btn-outline-primary w-100 rounded-pill py-2 fw-bold">
                                    Reserve a Table
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="text-center mt-5 d-md-none">
            <a href="<?= url('/restaurants') ?>" class="btn btn-outline-primary rounded-pill btn-lg w-100">View All Restaurants</a>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
$title = 'Home - TableTap';
require APP_PATH . '/Views/layouts/public.php';
?>
