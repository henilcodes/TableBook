<?php
ob_start();
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0"><i class="bi bi-shield-lock"></i> Admin Login</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= url('/admin/login') ?>">
                        <input type="hidden" name="_token" value="<?= $csrf_token ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required autofocus>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        
                        <button type="submit" class="btn btn-dark w-100">Login</button>
                    </form>
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

