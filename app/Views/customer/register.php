<?php
ob_start();
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex p-3 mb-3">
                            <i class="bi bi-person-plus-fill fs-2"></i>
                        </div>
                        <h3 class="fw-bold">Create Account</h3>
                        <p class="text-muted">Join TableTap to start booking your favorite tables</p>
                    </div>

                    <form method="POST" action="<?= url('/register') ?>" class="needs-validation" novalidate>
                        <input type="hidden" name="_token" value="<?= $csrf_token ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" name="name" class="form-control" placeholder="John Doe" required autofocus>
                                <div class="invalid-feedback">Please enter your full name.</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="bi bi-envelope text-muted"></i></span>
                                    <input type="email" name="email" class="form-control" placeholder="john@example.com" required>
                                    <div class="invalid-feedback">Valid email required.</div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="bi bi-telephone text-muted"></i></span>
                                    <input type="tel" name="phone" class="form-control" placeholder="+1 (555) 000-0000">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="bi bi-shield-lock text-muted"></i></span>
                                    <input type="password" name="password" class="form-control" placeholder="••••••••" required minlength="8">
                                    <div class="invalid-feedback">Minimum 8 characters.</div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="bi bi-shield-check text-muted"></i></span>
                                    <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                                    <div class="invalid-feedback">Please confirm your password.</div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-3 shadow-sm mb-3">
                            Create Account <i class="bi bi-check2-circle ms-2"></i>
                        </button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p class="mb-0 text-muted">
                            Already have an account? <a href="<?= url('/login') ?>" class="fw-bold text-decoration-none">Sign in here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Register - TableTap';
require APP_PATH . '/Views/layouts/public.php';
?>
