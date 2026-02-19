<?php
ob_start();
?>

<div class="container public-section">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <h1 class="mb-0">All Restaurants</h1>
        <small class="text-muted">Choose a restaurant to start your reservation</small>
    </div>
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
                                View Details & Reserve
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Restaurants - TableTap';
require APP_PATH . '/Views/layouts/public.php';
?>
