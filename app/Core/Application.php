<?php
namespace App\Core;

class Application
{
    private $router;
    
    public function __construct()
    {
        $this->router = new Router();
        $this->registerRoutes();
    }
    
    private function registerRoutes()
    {
        // Public routes
        $this->router->get('/', [\App\Controllers\PublicController::class, 'home']);
        $this->router->get('/restaurants', [\App\Controllers\PublicController::class, 'restaurants']);
        $this->router->get('/restaurants/{slug}', [\App\Controllers\PublicController::class, 'restaurantDetail']);
        $this->router->get('/availability', [\App\Controllers\PublicController::class, 'checkAvailability']);
        $this->router->post('/reservation', [\App\Controllers\PublicController::class, 'createReservation']);
        $this->router->get('/reservation/{code}', [\App\Controllers\PublicController::class, 'viewReservation']);
        
        // Customer Auth routes
        $this->router->get('/login', [\App\Controllers\CustomerAuthController::class, 'showLogin']);
        $this->router->post('/login', [\App\Controllers\CustomerAuthController::class, 'login']);
        $this->router->get('/register', [\App\Controllers\CustomerAuthController::class, 'showRegister']);
        $this->router->post('/register', [\App\Controllers\CustomerAuthController::class, 'register']);
        $this->router->post('/logout', [\App\Controllers\CustomerAuthController::class, 'logout']);
        
        // Customer Account routes (protected)
        $this->router->get('/account', [\App\Controllers\CustomerAccountController::class, 'dashboard'], [\App\Middleware\CustomerAuthMiddleware::class]);
        $this->router->get('/account/reservations', [\App\Controllers\CustomerAccountController::class, 'reservations'], [\App\Middleware\CustomerAuthMiddleware::class]);
        $this->router->get('/account/history', [\App\Controllers\CustomerAccountController::class, 'history'], [\App\Middleware\CustomerAuthMiddleware::class]);
        $this->router->post('/account/reservation/{id}/cancel', [\App\Controllers\CustomerAccountController::class, 'cancelReservation'], [\App\Middleware\CustomerAuthMiddleware::class]);
        $this->router->post('/account/profile/update', [\App\Controllers\CustomerAccountController::class, 'updateProfile'], [\App\Middleware\CustomerAuthMiddleware::class]);
        $this->router->post('/account/password/update', [\App\Controllers\CustomerAccountController::class, 'updatePassword'], [\App\Middleware\CustomerAuthMiddleware::class]);
        
        // Cart/Preorder routes
        $this->router->post('/cart/add', [\App\Controllers\CartController::class, 'addItem']);
        $this->router->post('/cart/update', [\App\Controllers\CartController::class, 'updateItem']);
        $this->router->post('/cart/attach', [\App\Controllers\CartController::class, 'attachToReservation']);
        $this->router->get('/cart/summary', [\App\Controllers\CartController::class, 'summary']);
        
        // Admin routes
        $this->router->get('/admin/login', [\App\Controllers\AdminController::class, 'showLogin']);
        $this->router->post('/admin/login', [\App\Controllers\AdminController::class, 'login']);
        $this->router->post('/admin/logout', [\App\Controllers\AdminController::class, 'logout'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->get('/admin', [\App\Controllers\AdminController::class, 'dashboard'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->get('/admin/reservations', [\App\Controllers\AdminController::class, 'reservations'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->post('/admin/reservations/{id}/status', [\App\Controllers\AdminController::class, 'updateReservationStatus'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->get('/admin/export/reservations', [\App\Controllers\AdminController::class, 'exportReservations'], [\App\Middleware\AuthMiddleware::class]);

        // Admin restaurant management
        $this->router->get('/admin/restaurants', [\App\Controllers\AdminController::class, 'restaurants'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->get('/admin/restaurants/create', [\App\Controllers\AdminController::class, 'showCreateRestaurant'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->post('/admin/restaurants', [\App\Controllers\AdminController::class, 'createRestaurant'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->get('/admin/restaurants/{id}/edit', [\App\Controllers\AdminController::class, 'showEditRestaurant'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->post('/admin/restaurants/{id}/update', [\App\Controllers\AdminController::class, 'updateRestaurant'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->post('/admin/restaurants/{id}/delete', [\App\Controllers\AdminController::class, 'deleteRestaurant'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->get('/admin/restaurants/{restaurant_id}/hours', [\App\Controllers\AdminController::class, 'restaurantHours'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->post('/admin/restaurants/{restaurant_id}/hours', [\App\Controllers\AdminController::class, 'updateRestaurantHours'], [\App\Middleware\AuthMiddleware::class]);

        // Admin table management (restaurant-scoped)
        $this->router->get('/admin/restaurants/{restaurant_id}/tables', [\App\Controllers\AdminController::class, 'tables'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->get('/admin/restaurants/{restaurant_id}/tables/create', [\App\Controllers\AdminController::class, 'showCreateTable'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->post('/admin/restaurants/{restaurant_id}/tables', [\App\Controllers\AdminController::class, 'createTable'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->get('/admin/restaurants/{restaurant_id}/tables/{id}/edit', [\App\Controllers\AdminController::class, 'showEditTable'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->post('/admin/restaurants/{restaurant_id}/tables/{id}/update', [\App\Controllers\AdminController::class, 'updateTable'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->post('/admin/restaurants/{restaurant_id}/tables/{id}/delete', [\App\Controllers\AdminController::class, 'deleteTable'], [\App\Middleware\AuthMiddleware::class]);

        // Admin menu management (restaurant-scoped)
        $this->router->get('/admin/restaurants/{restaurant_id}/menu/categories', [\App\Controllers\AdminController::class, 'menuCategories'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->get('/admin/restaurants/{restaurant_id}/menu/categories/create', [\App\Controllers\AdminController::class, 'showCreateMenuCategory'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->post('/admin/restaurants/{restaurant_id}/menu/categories', [\App\Controllers\AdminController::class, 'createMenuCategory'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->get('/admin/restaurants/{restaurant_id}/menu/categories/{id}/edit', [\App\Controllers\AdminController::class, 'showEditMenuCategory'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->post('/admin/restaurants/{restaurant_id}/menu/categories/{id}/update', [\App\Controllers\AdminController::class, 'updateMenuCategory'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->post('/admin/restaurants/{restaurant_id}/menu/categories/{id}/delete', [\App\Controllers\AdminController::class, 'deleteMenuCategory'], [\App\Middleware\AuthMiddleware::class]);

        $this->router->get('/admin/restaurants/{restaurant_id}/menu/items', [\App\Controllers\AdminController::class, 'menuItems'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->get('/admin/restaurants/{restaurant_id}/menu/items/create', [\App\Controllers\AdminController::class, 'showCreateMenuItem'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->post('/admin/restaurants/{restaurant_id}/menu/items', [\App\Controllers\AdminController::class, 'createMenuItem'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->get('/admin/restaurants/{restaurant_id}/menu/items/{id}/edit', [\App\Controllers\AdminController::class, 'showEditMenuItem'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->post('/admin/restaurants/{restaurant_id}/menu/items/{id}/update', [\App\Controllers\AdminController::class, 'updateMenuItem'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->post('/admin/restaurants/{restaurant_id}/menu/items/{id}/delete', [\App\Controllers\AdminController::class, 'deleteMenuItem'], [\App\Middleware\AuthMiddleware::class]);

        // Legacy aliases for older admin URLs
        $this->router->get('/admin/tables', [\App\Controllers\AdminController::class, 'tables'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->get('/admin/tables/create', [\App\Controllers\AdminController::class, 'showCreateTable'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->post('/admin/tables', [\App\Controllers\AdminController::class, 'createTable'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->get('/admin/tables/{id}/edit', [\App\Controllers\AdminController::class, 'showEditTable'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->post('/admin/tables/{id}/update', [\App\Controllers\AdminController::class, 'updateTable'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->post('/admin/tables/{id}/delete', [\App\Controllers\AdminController::class, 'deleteTable'], [\App\Middleware\AuthMiddleware::class]);

        $this->router->get('/admin/menu/categories', [\App\Controllers\AdminController::class, 'menuCategories'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->get('/admin/menu/categories/create', [\App\Controllers\AdminController::class, 'showCreateMenuCategory'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->post('/admin/menu/categories', [\App\Controllers\AdminController::class, 'createMenuCategory'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->get('/admin/menu/categories/{id}/edit', [\App\Controllers\AdminController::class, 'showEditMenuCategory'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->post('/admin/menu/categories/{id}/update', [\App\Controllers\AdminController::class, 'updateMenuCategory'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->post('/admin/menu/categories/{id}/delete', [\App\Controllers\AdminController::class, 'deleteMenuCategory'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->get('/admin/menu/items', [\App\Controllers\AdminController::class, 'menuItems'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->get('/admin/menu/items/create', [\App\Controllers\AdminController::class, 'showCreateMenuItem'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->post('/admin/menu/items', [\App\Controllers\AdminController::class, 'createMenuItem'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->get('/admin/menu/items/{id}/edit', [\App\Controllers\AdminController::class, 'showEditMenuItem'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->post('/admin/menu/items/{id}/update', [\App\Controllers\AdminController::class, 'updateMenuItem'], [\App\Middleware\AuthMiddleware::class]);
        $this->router->post('/admin/menu/items/{id}/delete', [\App\Controllers\AdminController::class, 'deleteMenuItem'], [\App\Middleware\AuthMiddleware::class]);
    }
    
    public function run()
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // Strip base path from URI
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = dirname($scriptName);
        
        // If base path is not root, strip it from URI
        if ($basePath !== '/' && $basePath !== '\\') {
            $uri = preg_replace('#^' . preg_quote($basePath, '#') . '#', '', $uri);
        }
        
        // Ensure URI starts with /
        if (empty($uri) || $uri[0] !== '/') {
            $uri = '/' . $uri;
        }
        
        $this->router->dispatch($uri, $method);
    }
}
