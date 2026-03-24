<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $title ?? 'TableTap - Restaurant Reservations'?>
    </title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700;800&family=Poppins:wght@600;700&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= url('/public/assets/css/app.css')?>">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?= url('/')?>">
                <i class="bi bi-calendar-check"></i> TableTap
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('/')?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('/restaurants')?>">Restaurants</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('/about')?>">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('/contact')?>">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('/support')?>">Support</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (!empty($_SESSION['customer_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('/account')?>">
                            <i class="bi bi-person-circle"></i> My Account
                        </a>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="<?= url('/logout')?>" class="d-inline">
                            <input type="hidden" name="_token" value="<?= $csrf_token ?? ''?>">
                            <button type="submit" class="btn btn-link nav-link">Logout</button>
                        </form>
                    </li>
                    <?php
else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('/login')?>">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('/register')?>">Register</a>
                    </li>
                    <?php
endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Toast Notifications -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <?php if (!empty($_SESSION['success'])): ?>
        <div class="toast show" role="alert">
            <div class="toast-header bg-success text-white">
                <strong class="me-auto">Success</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                <?= htmlspecialchars($_SESSION['success'])?>
            </div>
        </div>
        <?php unset($_SESSION['success']); ?>
        <?php
endif; ?>
        <?php if (!empty($_SESSION['error'])): ?>
        <div class="toast show" role="alert">
            <div class="toast-header bg-danger text-white">
                <strong class="me-auto">Error</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                <?= htmlspecialchars($_SESSION['error'])?>
            </div>
        </div>
        <?php unset($_SESSION['error']); ?>
        <?php
endif; ?>
    </div>

    <!-- Main Content -->
    <main class="main-content">
        <?= $content ?? ''?>
    </main>

    <!-- Footer -->
    <footer class="site-footer bg-dark text-white pt-5 pb-4 mt-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <h5 class="fw-bold mb-4"><i class="bi bi-calendar-check-fill text-white"></i> TableTap</h5>
                    <p class="text-secondary">
                        TableTap is the easiest way to find and book the best tables at your favorite restaurants.
                        Experience seamless dining with instant confirmations and personalized service.
                    </p>
                    <div class="d-flex gap-3 fs-5 mt-4">
                        <a href="#" class="text-secondary"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-secondary"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="text-secondary"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-secondary"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6">
                    <h6 class="text-uppercase fw-bold mb-4">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?= url('/')?>" class="text-secondary text-decoration-none">Home</a>
                        </li>
                        <li class="mb-2"><a href="<?= url('/restaurants')?>"
                                class="text-secondary text-decoration-none">Restaurants</a></li>
                        <li class="mb-2"><a href="<?= url('/about')?>"
                                class="text-secondary text-decoration-none">About Us</a></li>
                        <li class="mb-2"><a href="<?= url('/contact')?>"
                                class="text-secondary text-decoration-none">Contact Us</a></li>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-6">
                    <h6 class="text-uppercase fw-bold mb-4">Support</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?= url('/support')?>"
                                class="text-secondary text-decoration-none">Help Center</a></li>
                        <li class="mb-2"><a href="<?= url('/privacy')?>"
                                class="text-secondary text-decoration-none">Privacy Policy</a></li>
                        <li class="mb-2"><a href="#" class="text-secondary text-decoration-none">Terms of Service</a>
                        </li>
                        <li class="mb-2"><a href="#" class="text-secondary text-decoration-none">Cookie Policy</a></li>
                    </ul>
                </div>

                <div class="col-lg-4 col-md-6">
                    <h6 class="text-uppercase fw-bold mb-4">Newsletter</h6>
                    <p class="text-secondary mb-4">Subscribe to our newsletter for the latest dining updates and offers.
                    </p>
                    <form class="d-flex">
                        <input type="email" class="form-control rounded-start-pill border-0" placeholder="Your email">
                        <button type="button" class="btn btn-primary rounded-end-pill px-4">Join</button>
                    </form>
                </div>
            </div>

            <hr class="my-4 border-secondary opacity-25">

            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="text-secondary mb-0 small">&copy;
                        <?= date('Y')?> TableTap Inc. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="text-secondary mb-0 small">Made with <i class="bi bi-heart-fill text-danger"></i> for food
                        lovers.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= url('/public/assets/js/app.js')?>"></script>
    <?php if (isset($scripts)): ?>
    <?= $scripts?>
    <?php
endif; ?>
</body>

</html>