<?php
namespace App\Middleware;

class CustomerAuthMiddleware
{
    public function handle()
    {
        if (empty($_SESSION['customer_id'])) {
            $basePath = defined('BASE_URL') ? BASE_URL : '';
            $requested = $_SERVER['REQUEST_URI'] ?? '/account';
            $requestedPath = parse_url($requested, PHP_URL_PATH) ?: '/account';
            $requestedQuery = parse_url($requested, PHP_URL_QUERY);
            if ($basePath !== '' && strpos($requestedPath, $basePath) === 0) {
                $requestedPath = substr($requestedPath, strlen($basePath));
                if ($requestedPath === '') {
                    $requestedPath = '/';
                }
            }
            $normalized = $requestedPath;
            if (!empty($requestedQuery)) {
                $normalized .= '?' . $requestedQuery;
            }
            $_SESSION['intended_url'] = $normalized;
            header('Location: ' . $basePath . '/login');
            exit;
        }
        return true;
    }
}
