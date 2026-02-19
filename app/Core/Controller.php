<?php
namespace App\Core;

class Controller
{
    protected function view($view, $data = [])
    {
        extract($data);
        require_once APP_PATH . '/Views/' . $view . '.php';
    }
    
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function redirect($url)
    {
        // If URL doesn't start with http:// or https://, prepend base URL
        if (!preg_match('#^https?://#', $url)) {
            $basePath = defined('BASE_URL') ? BASE_URL : '';
            $url = $basePath . '/' . ltrim($url, '/');
        }
        header('Location: ' . $url);
        exit;
    }
    
    protected function url($path = '')
    {
        $basePath = defined('BASE_URL') ? BASE_URL : '';
        $path = ltrim($path, '/');
        return $basePath . '/' . $path;
    }
    
    protected function validateCsrf()
    {
        $token = $_POST['_token'] ?? '';
        if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            die('CSRF token validation failed');
        }
    }
    
    protected function generateCsrfToken()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    protected function sanitize($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
}

