<?php
/**
 * TableTap Reservation System
 * Front Controller - Entry Point
 */

use App\Core\Autoloader;

session_start();

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define base paths
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// Define base URL
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = dirname($scriptName);
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}
define('BASE_URL', $basePath);

// Autoloader
require_once APP_PATH . '/Core/Autoloader.php';
Autoloader::register();

// Load configuration
require_once CONFIG_PATH . '/config.php';

// Helper function for generating URLs with base path
if (!function_exists('url')) {
    function url($path = '') {
        $path = ltrim($path, '/');
        return BASE_URL . '/' . $path;
    }
}

if (!function_exists('currency')) {
    function currency($amount) {
        return '₹' . number_format((float)$amount, 2);
    }
}

if (!function_exists('image_url')) {
    function image_url($path = null, $fallback = '/public/assets/img/restaurant-placeholder.svg') {
        $path = trim((string)$path);
        if ($path === '') {
            return url($fallback);
        }

        if (preg_match('#^(https?:)?//#i', $path) || strpos($path, 'data:') === 0) {
            return $path;
        }

        if (defined('BASE_URL') && BASE_URL !== '' && strpos($path, BASE_URL . '/') === 0) {
            return $path;
        }

        if (strpos($path, '/') === 0) {
            if (defined('ROOT_PATH') && !preg_match('#^/[A-Za-z]:/#', $path)) {
                $fullPath = ROOT_PATH . $path;
                if (!is_file($fullPath)) {
                    return url($fallback);
                }
            }
            return BASE_URL . $path;
        }

        if (defined('ROOT_PATH')) {
            $fullPath = ROOT_PATH . '/' . ltrim($path, '/');
            if (!is_file($fullPath)) {
                return url($fallback);
            }
        }

        return BASE_URL . '/' . ltrim($path, '/');
    }
}

// Initialize and run application
$app = new App\Core\Application();
$app->run();
