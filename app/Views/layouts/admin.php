<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin - TableTap' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= url('/public/assets/css/app.css') ?>">
    <style>
        body.admin-body {
            background: linear-gradient(180deg, #f5f7fb 0%, #eef2f8 100%);
            color: #12263a;
        }
        .admin-topbar {
            background: linear-gradient(120deg, #17324d 0%, #1f7a8c 100%);
            box-shadow: 0 10px 26px rgba(18,38,58,.2);
            padding-top: .7rem;
            padding-bottom: .7rem;
        }
        .admin-topbar .navbar-brand {
            font-weight: 800;
            letter-spacing: .2px;
        }
        .admin-user-pill {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: rgba(255,255,255,.16);
            color: #fff;
            border: 1px solid rgba(255,255,255,.24);
            border-radius: 999px;
            padding: .28rem .8rem;
            font-size: .9rem;
        }
        .admin-sidebar {
            min-height: calc(100vh - 64px);
            background: linear-gradient(180deg, #162b43 0%, #102033 100%);
            border-right: 1px solid rgba(255,255,255,.06);
            padding: 1rem .75rem;
        }
        .admin-nav {
            display: flex;
            flex-direction: column;
            gap: .35rem;
        }
        .admin-nav .list-group-item {
            border: 0;
            border-radius: 12px;
            background: transparent;
            color: rgba(255,255,255,.83);
            font-weight: 600;
            padding: .68rem .82rem;
        }
        .admin-nav .list-group-item:hover {
            background: rgba(255,255,255,.09);
            color: #fff;
        }
        .admin-nav .list-group-item.active {
            background: rgba(255,255,255,.16) !important;
            color: #fff !important;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,.14);
        }
        .admin-content {
            padding: 1.5rem;
        }
        .admin-content .card {
            border: 1px solid #e7ebf3;
            border-radius: 16px;
            box-shadow: 0 10px 26px rgba(15,23,42,.05);
        }
        .admin-content .card-header {
            background: #f9fbff;
            border-bottom-color: #e7ebf3;
            padding: .9rem 1rem;
        }
        .admin-content .card-body {
            padding: 1rem 1.05rem;
        }
        .admin-page-head {
            margin-bottom: 1rem;
        }
        .admin-page-head h2 {
            margin-bottom: .2rem;
            font-weight: 800;
            letter-spacing: .15px;
        }
        .admin-form-card .form-label {
            font-size: .85rem;
            text-transform: uppercase;
            letter-spacing: .35px;
            color: #475467;
            margin-bottom: .35rem;
        }
        .admin-form-card .form-control,
        .admin-form-card .form-select {
            min-height: 44px;
            border-color: #d8dfeb;
        }
        .admin-form-card .form-control:focus,
        .admin-form-card .form-select:focus {
            border-color: #1f7a8c;
            box-shadow: 0 0 0 .22rem rgba(31,122,140,.14);
        }
        .admin-form-card h6.text-uppercase {
            font-size: .76rem;
            letter-spacing: .7px;
            color: #667085 !important;
            border-bottom: 1px dashed #d6dde8;
            padding-bottom: .45rem;
            margin-bottom: .8rem !important;
        }
        .admin-filter-card {
            background: #fbfcff;
        }
        .admin-alert {
            border-radius: 12px;
            border: 1px solid transparent;
            box-shadow: 0 8px 18px rgba(0,0,0,.04);
        }
    </style>
</head>
<body class="admin-body">
    <nav class="navbar navbar-dark admin-topbar">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= url('/admin') ?>">
                <i class="bi bi-shield-check"></i> TableTap Admin
            </a>
            <div>
                <span class="admin-user-pill me-2">
                    <i class="bi bi-person-circle"></i>
                    <?= htmlspecialchars($_SESSION['admin_username'] ?? '') ?>
                </span>
                <form method="POST" action="<?= url('/admin/logout') ?>" class="d-inline">
                    <input type="hidden" name="_token" value="<?= $csrf_token ?? '' ?>">
                    <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-2 col-md-3 admin-sidebar">
                <?php $uri = $_SERVER['REQUEST_URI'] ?? ''; ?>
                <div class="list-group list-group-flush admin-nav">
                    <a href="<?= url('/admin') ?>" class="list-group-item list-group-item-action <?= (strpos($uri, '/admin') !== false && strpos($uri, '/admin/restaurants') === false && strpos($uri, '/admin/reservations') === false) ? 'active' : '' ?>">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a href="<?= url('/admin/restaurants') ?>" class="list-group-item list-group-item-action <?= (strpos($uri, '/admin/restaurants') !== false) ? 'active' : '' ?>">
                        <i class="bi bi-shop"></i> Restaurants
                    </a>
                    <a href="<?= url('/admin/reservations') ?>" class="list-group-item list-group-item-action <?= (strpos($uri, '/admin/reservations') !== false) ? 'active' : '' ?>">
                        <i class="bi bi-calendar-event"></i> Reservations
                    </a>
                    <a href="<?= url('/') ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-house"></i> View Site
                    </a>
                </div>
            </div>
            <div class="col-lg-10 col-md-9 admin-content">
                <?php if (!empty($_SESSION['success'])): ?>
                    <div class="alert alert-success admin-alert alert-dismissible fade show">
                        <?= htmlspecialchars($_SESSION['success']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                <?php if (!empty($_SESSION['error'])): ?>
                    <div class="alert alert-danger admin-alert alert-dismissible fade show">
                        <?= htmlspecialchars($_SESSION['error']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                
                <?= $content ?? '' ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php if (isset($scripts)): ?>
        <?= $scripts ?>
    <?php endif; ?>
</body>
</html>
