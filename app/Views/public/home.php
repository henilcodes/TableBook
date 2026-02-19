<?php
ob_start();
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container text-center">
        <h1 class="display-4 mb-4">Reserve Your Perfect Table</h1>
        <p class="lead mb-4">Discover great places, pick your time, and confirm in minutes.</p>
        <a href="<?= url('/restaurants') ?>" class="btn btn-light btn-lg">
            <i class="bi bi-search"></i> Browse Restaurants
        </a>
    </div>
</section>

<!-- Featured Restaurants -->
<section class="public-section">
    <div class="container">
        <h2 class="text-center mb-5">Featured Restaurants</h2>
        <div class="row g-4">
            <?php if (empty($restaurants)): ?>
                <div class="col-12 text-center">
                    <p class="text-muted">No restaurants available at the moment.</p>
                </div>
            <?php else: ?>
                <?php foreach ($restaurants as $restaurant): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card restaurant-card h-100">
                            <img src="<?= htmlspecialchars(image_url($restaurant['image_url'] ?? null)) ?>" class="card-img-top" alt="<?= htmlspecialchars($restaurant['name']) ?>">
                            <div class="card-body">
                                <span class="badge bg-primary mb-2"><?= htmlspecialchars($restaurant['cuisine_type']) ?></span>
                                <h5 class="card-title"><?= htmlspecialchars($restaurant['name']) ?></h5>
                                <p class="card-text text-muted"><?= htmlspecialchars(substr($restaurant['description'] ?? '', 0, 100)) ?>...</p>
                                <div class="mb-3">
                                    <i class="bi bi-star-fill text-warning"></i>
                                    <span><?= number_format($restaurant['rating'], 1) ?></span>
                                </div>
                                <a href="<?= url('/restaurants/' . htmlspecialchars($restaurant['slug'])) ?>" class="btn btn-primary w-100">
                                    Reserve Table
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
$title = 'Home - TableTap';
require APP_PATH . '/Views/layouts/public.php';
?>
