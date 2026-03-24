<?php
ob_start();
?>

<style>
    .admin-login-wrap {
        min-height: 80vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: radial-gradient(circle at top right, rgba(31, 122, 140, 0.1), transparent),
                    radial-gradient(circle at bottom left, rgba(21, 34, 56, 0.05), transparent);
        padding: 2rem 0;
    }
    .login-glass-card {
        background: rgba(255, 255, 255, 0.8) !important;
        backdrop-filter: blur(12px) saturate(180%);
        -webkit-backdrop-filter: blur(12px) saturate(180%);
        border: 1px solid rgba(255, 255, 255, 0.3) !important;
        border-radius: 24px !important;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1) !important;
        overflow: hidden;
    }
    .login-header {
        background: linear-gradient(135deg, var(--tt-secondary) 0%, var(--tt-primary) 100%);
        padding: 2.5rem 2rem;
        text-align: center;
        color: white;
    }
    .login-header i {
        font-size: 3rem;
        margin-bottom: 1rem;
        display: block;
        opacity: 0.9;
    }
    .login-body {
        padding: 2.5rem;
    }
    .login-form-control {
        border-radius: 12px !important;
        padding: 0.8rem 1rem !important;
        border: 2px solid #edf2f7 !important;
        background: #f8fafc !important;
        transition: all 0.2s ease;
    }
    .login-form-control:focus {
        border-color: var(--tt-primary) !important;
        box-shadow: 0 0 0 4px var(--tt-primary-glow) !important;
        background: white !important;
    }
    .btn-login {
        background: linear-gradient(135deg, var(--tt-primary) 0%, var(--tt-accent) 100%) !important;
        border: none !important;
        padding: 0.9rem !important;
        border-radius: 12px !important;
        font-weight: 800 !important;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        margin-top: 1rem;
        box-shadow: 0 8px 20px var(--tt-primary-glow) !important;
    }
    .btn-login:hover {
        transform: translateY(-2px);
        filter: brightness(1.1);
        box-shadow: 0 12px 25px var(--tt-primary-glow) !important;
    }
</style>

<div class="admin-login-wrap">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card login-glass-card animate-fade-up">
                    <div class="login-header">
                        <i class="bi bi-shield-lock-fill"></i>
                        <h3 class="fw-bold mb-0">Admin Access</h3>
                        <p class="small opacity-75 mb-0 mt-1">TableTap Management Portal</p>
                    </div>
                    <div class="login-body">
                        <?php if (!empty($_SESSION['error'])): ?>
                            <div class="alert alert-danger border-0 rounded-4 small mb-4 py-2">
                                <i class="bi bi-exclamation-circle-fill me-2"></i> <?= htmlspecialchars($_SESSION['error']) ?>
                            </div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>

                        <form method="POST" action="<?= url('/admin/login') ?>">
                            <input type="hidden" name="_token" value="<?= $csrf_token ?>">
                            
                            <div class="mb-4">
                                <label class="form-label text-uppercase small fw-bold text-muted letter-spacing-1">Admin Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0 rounded-start-4 px-3"><i class="bi bi-envelope text-muted"></i></span>
                                    <input type="email" name="email" class="form-control login-form-control rounded-end-4" value="admin@tabletap.com" required autofocus>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label text-uppercase small fw-bold text-muted letter-spacing-1">Secret Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0 rounded-start-4 px-3"><i class="bi bi-key text-muted"></i></span>
                                    <input type="password" name="password" class="form-control login-form-control rounded-end-4" value="password" required>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-login w-100">
                                <i class="bi bi-box-arrow-in-right me-2"></i> Authenticate
                            </button>
                        </form>
                        
                        <div class="text-center mt-4">
                            <a href="<?= url('/') ?>" class="text-decoration-none small text-muted hover-primary">
                                <i class="bi bi-arrow-left me-1"></i> Back to Public Site
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Admin Login - TableTap';
require APP_PATH . '/Views/layouts/public.php';
?>

