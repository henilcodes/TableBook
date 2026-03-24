<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'My Account - TableTap' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700;800&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= url('/public/assets/css/app.css') ?>">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= url('/') ?>">
                <i class="bi bi-calendar-check"></i> TableTap
            </a>
            <div>
                <span class="text-white me-3"><?= htmlspecialchars($_SESSION['customer_name'] ?? '') ?></span>
                <form method="POST" action="<?= url('/logout') ?>" class="d-inline">
                    <input type="hidden" name="_token" value="<?= $csrf_token ?? '' ?>">
                    <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row min-vh-100">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 bg-white border-end px-0">
                <div class="d-flex flex-column h-100 py-4">
                    <div class="px-4 mb-4">
                        <small class="text-uppercase text-muted fw-bold letter-spacing-1">Menu</small>
                    </div>
                    <div class="list-group list-group-flush px-2">
                        <a href="<?= url('/account') ?>" class="list-group-item list-group-item-action border-0 rounded-3 mb-1 py-2 d-flex align-items-center <?= (strpos($_SERVER['REQUEST_URI'], '/account') !== false && strpos($_SERVER['REQUEST_URI'], '/account/reservations') === false && strpos($_SERVER['REQUEST_URI'], '/account/history') === false) ? 'active bg-primary bg-opacity-10 text-primary fw-bold' : 'text-secondary' ?>">
                            <i class="bi bi-grid-1x2-fill me-3 fs-5"></i> Dashboard
                        </a>
                        <a href="<?= url('/account/reservations') ?>" class="list-group-item list-group-item-action border-0 rounded-3 mb-1 py-2 d-flex align-items-center <?= (strpos($_SERVER['REQUEST_URI'], '/account/reservations') !== false) ? 'active bg-primary bg-opacity-10 text-primary fw-bold' : 'text-secondary' ?>">
                            <i class="bi bi-calendar2-check-fill me-3 fs-5"></i> Reservations
                        </a>
                        <a href="<?= url('/account/history') ?>" class="list-group-item list-group-item-action border-0 rounded-3 mb-1 py-2 d-flex align-items-center <?= (strpos($_SERVER['REQUEST_URI'], '/account/history') !== false) ? 'active bg-primary bg-opacity-10 text-primary fw-bold' : 'text-secondary' ?>">
                            <i class="bi bi-clock-history me-3 fs-5"></i> History
                        </a>
                        <hr class="my-3 opacity-10">
                        <a href="<?= url('/') ?>" class="list-group-item list-group-item-action border-0 rounded-3 mb-1 py-2 d-flex align-items-center text-secondary">
                            <i class="bi bi-arrow-left-circle-fill me-3 fs-5"></i> Back to Home
                        </a>
                    </div>
                    
                    <div class="mt-auto px-4">
                        <div class="card bg-primary bg-opacity-10 border-0 rounded-4 p-3 mb-3">
                            <p class="small text-primary mb-2 fw-bold">Ready to dine?</p>
                            <a href="<?= url('/restaurants') ?>" class="btn btn-primary btn-sm rounded-pill w-100">Find a Table</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="col-md-9 col-lg-10 bg-light-subtle py-4 px-4 px-md-5">
                <?php if (!empty($_SESSION['success'])): ?>
                    <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show rounded-4">
                        <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($_SESSION['success']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                <?php if (!empty($_SESSION['error'])): ?>
                    <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show rounded-4">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($_SESSION['error']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                
                <?= $content ?? '' ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= url('/public/assets/js/app.js') ?>"></script>
    <?php if (isset($scripts)): ?>
        <?= $scripts ?>
    <?php endif; ?>
</body>
</html>
