<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\CartRepository;
use App\Repositories\RestaurantRepository;

class CartController extends Controller
{
    private $cartRepo;
    private $restaurantRepo;
    
    public function __construct()
    {
        $this->cartRepo = new CartRepository();
        $this->restaurantRepo = new RestaurantRepository();
    }
    
    public function addItem()
    {
        header('Content-Type: application/json');
        if (!$this->validateAjaxCsrf()) {
            return;
        }

        $menuItemId = $_POST['menu_item_id'] ?? null;
        $quantity = max(1, intval($_POST['quantity'] ?? 1));
        $restaurantId = $_POST['restaurant_id'] ?? null;

        try {
            if (!$menuItemId || !$restaurantId) {
                $this->json(['error' => 'Missing required parameters'], 400);
                return;
            }

            $menuItem = $this->restaurantRepo->findMenuItemById((int)$menuItemId);
            if (
                !$menuItem ||
                (int)$menuItem['restaurant_id'] !== (int)$restaurantId ||
                empty($menuItem['is_available'])
            ) {
                $this->json(['error' => 'Menu item not found'], 404);
                return;
            }

            // Get or create cart
            $customerId = $_SESSION['customer_id'] ?? null;
            $sessionId = session_id();
            $cart = $this->cartRepo->getOrCreateCart($customerId, $sessionId);

            // Add item to cart
            $this->cartRepo->addItem($cart['id'], $menuItemId, $quantity, $menuItem['price']);
            $summary = $this->cartRepo->getCartSummary($cart['id']);

            $this->json([
                'success' => true,
                'cart_count' => $summary['item_count'],
                'cart_total' => number_format($summary['total'], 2),
                'items' => $summary['items'],
                'message' => 'Item added to cart'
            ]);
        } catch (\Throwable $e) {
            error_log('[CartController::addItem] ' . $e->getMessage());
            $this->json(['error' => 'Unable to add item to cart. Please try again.'], 500);
        }
    }
    
    public function updateItem()
    {
        header('Content-Type: application/json');
        if (!$this->validateAjaxCsrf()) {
            return;
        }
        
        $cartItemId = $_POST['cart_item_id'] ?? null;
        $quantity = intval($_POST['quantity'] ?? 0);
        
        if (!$cartItemId) {
            $this->json(['error' => 'Missing cart item ID'], 400);
            return;
        }

        $customerId = $_SESSION['customer_id'] ?? null;
        $sessionId = session_id();
        $cart = $this->cartRepo->getActiveCart($customerId, $sessionId);
        if (!$cart) {
            $this->json(['error' => 'Cart not found'], 404);
            return;
        }

        try {
            $this->cartRepo->updateItem((int)$cart['id'], (int)$cartItemId, $quantity);
            $summary = $this->cartRepo->getCartSummary((int)$cart['id']);

            $this->json([
                'success' => true,
                'message' => 'Cart updated',
                'cart_count' => $summary['item_count'],
                'cart_total' => number_format($summary['total'], 2),
                'items' => $summary['items'],
            ]);
        } catch (\Throwable $e) {
            error_log('[CartController::updateItem] ' . $e->getMessage());
            $this->json(['error' => 'Unable to update cart. Please try again.'], 500);
        }
    }
    
    public function attachToReservation()
    {
        header('Content-Type: application/json');
        if (!$this->validateAjaxCsrf()) {
            return;
        }
        
        $reservationId = $_POST['reservation_id'] ?? null;
        
        if (!$reservationId) {
            $this->json(['error' => 'Missing reservation ID'], 400);
            return;
        }
        
        $customerId = $_SESSION['customer_id'] ?? null;
        $sessionId = session_id();
        $cart = $this->cartRepo->getActiveCart($customerId, $sessionId);
        if (!$cart) {
            $this->json(['error' => 'No active cart found'], 404);
            return;
        }
        
        try {
            $this->cartRepo->attachToReservation($cart['id'], $reservationId);
            $this->json(['success' => true, 'message' => 'Pre-order attached to reservation']);
        } catch (\Throwable $e) {
            error_log('[CartController::attachToReservation] ' . $e->getMessage());
            $this->json(['error' => 'Unable to attach pre-order right now.'], 500);
        }
    }

    public function summary()
    {
        header('Content-Type: application/json');

        $customerId = $_SESSION['customer_id'] ?? null;
        $sessionId = session_id();

        $cart = $this->cartRepo->getActiveCart($customerId, $sessionId);
        if (!$cart) {
            $this->json([
                'success' => true,
                'cart_count' => 0,
                'cart_total' => number_format(0, 2),
                'items' => [],
            ]);
            return;
        }
        $summary = $this->cartRepo->getCartSummary((int)$cart['id']);

        $this->json([
            'success' => true,
            'cart_count' => $summary['item_count'],
            'cart_total' => number_format($summary['total'], 2),
            'items' => $summary['items'],
        ]);
    }

    private function validateAjaxCsrf(): bool
    {
        $token = $_POST['_token'] ?? '';
        if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            $this->json(['error' => 'Session expired. Please refresh and try again.'], 403);
            return false;
        }
        return true;
    }
}
