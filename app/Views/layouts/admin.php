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
            background-color: var(--tt-bg);
            background-image: radial-gradient(at 0% 0%, rgba(31, 122, 140, 0.03) 0px, transparent 50%);
            color: var(--tt-text);
        }
        .admin-topbar {
            background: linear-gradient(135deg, var(--tt-secondary) 0%, var(--tt-primary) 100%);
            box-shadow: var(--tt-shadow-md);
            padding-top: .7rem;
            padding-bottom: .7rem;
            border-bottom: 1px solid rgba(255,255,255,.1);
        }
        .admin-topbar .navbar-brand {
            font-weight: 800;
            letter-spacing: .2px;
        }
        .admin-user-pill {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: rgba(255,255,255,.12);
            color: #fff;
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 999px;
            padding: .28rem .8rem;
            font-size: .9rem;
        }
        .admin-sidebar {
            min-height: calc(100vh - 64px);
            background: var(--tt-secondary);
            border-right: 1px solid rgba(255,255,255,.05);
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
            color: rgba(255,255,255,.75);
            font-weight: 600;
            padding: .68rem .82rem;
            transition: all 0.2s ease;
        }
        .admin-nav .list-group-item:hover {
            background: rgba(255,255,255,.08);
            color: #fff;
        }
        .admin-nav .list-group-item.active {
            background: var(--tt-primary) !important;
            color: #fff !important;
            box-shadow: 0 4px 12px rgba(31, 122, 140, 0.3);
        }
        .admin-content {
            padding: 1.5rem;
        }
        .admin-content .card {
            border: 1px solid var(--tt-border);
            border-radius: 16px;
            box-shadow: var(--tt-shadow-sm);
            background: #fff;
        }
        .admin-content .card-header {
            background: #fff;
            border-bottom: 1px solid var(--tt-border);
            padding: .9rem 1rem;
            font-weight: 700;
        }
        .admin-content .card-body {
            padding: 1rem 1.05rem;
        }
        .admin-page-head {
            margin-bottom: 1.5rem;
        }
        .admin-page-head h2 {
            margin-bottom: .2rem;
            font-weight: 800;
            color: var(--tt-secondary);
        }
        .admin-form-card .form-label {
            font-size: .8rem;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--tt-text-light);
            font-weight: 700;
        }
        .admin-form-card .form-control,
        .admin-form-card .form-select {
            border-radius: 10px;
            border: 2px solid #edf2f7;
            padding: 0.6rem 0.8rem;
        }
        .admin-form-card .form-control:focus,
        .admin-form-card .form-select:focus {
            border-color: var(--tt-primary);
            box-shadow: 0 0 0 3px var(--tt-primary-glow);
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
