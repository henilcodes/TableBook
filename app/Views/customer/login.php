<?php
ob_start();
?>

<div class="container py-5">
    <div class="row justify-content-center min-vh-75 align-items-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-lg overflow-hidden rounded-4">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex p-3 mb-3">
                            <i class="bi bi-person-lock fs-2"></i>
                        </div>
                        <h3 class="fw-bold">Welcome Back</h3>
                        <p class="text-muted">Sign in to manage your reservations</p>
                    </div>

                    <form method="POST" action="<?= url('/login')?>" class="needs-validation" novalidate>
                        <input type="hidden" name="_token" value="<?= $csrf_token?>">
                        <?php if (!empty($redirect)): ?>
                        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect)?>">
                        <?php
endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i
                                        class="bi bi-envelope text-muted"></i></span>
                                <input type="email" name="email" class="form-control" value="john@example.com"
                                    placeholder="name@example.com" required autofocus>
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between">
                                <label class="form-label">Password</label>
                                <a href="#" class="small text-decoration-none">Forgot?</a>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i
                                        class="bi bi-shield-lock text-muted"></i></span>
                                <input type="password" name="password" value="password" class="form-control"
                                    placeholder="••••••••" required>
                                <div class="invalid-feedback">Please enter your password.</div>
                            </div>
                        </div>

                        <div class="mb-4 d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input type="checkbox" name="remember_me" class="form-check-input" id="rememberMe">
                                <label class="form-check-label small" for="rememberMe">Stay signed in</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-3 shadow-sm mb-3">
                            Sign In <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-0 text-muted">
                            New to TableTap? <a href="<?= url('/register')?>"
                                class="fw-bold text-decoration-none">Create an account</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Login - TableTap';
require APP_PATH . '/Views/layouts/public.php';
?>