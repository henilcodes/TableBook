<?php
namespace App\Middleware;

class AuthMiddleware
{
    public function handle()
    {
        if (empty($_SESSION['admin_id'])) {
            header('Location: /admin/login');
            exit;
        }
        return true;
    }
}

