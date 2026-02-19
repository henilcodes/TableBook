<?php
namespace App\Repositories;

use App\Core\Database;
use PDO;

class CartRepository
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getOrCreateCart($customerId = null, $sessionId = null)
    {
        $cart = $this->getActiveCart($customerId, $sessionId);
        if ($cart) {
            return $cart;
        }

        $stmt = $this->db->prepare("INSERT INTO carts (customer_id, session_id, status) VALUES (?, ?, 'active')");
        $stmt->execute([$customerId, $sessionId]);
        $cartId = $this->db->lastInsertId();
        return $this->findById($cartId);
    }

    public function getActiveCart($customerId = null, $sessionId = null)
    {
        if ($customerId) {
            $stmt = $this->db->prepare("SELECT * FROM carts WHERE customer_id = ? AND reservation_id IS NULL AND status = 'active' ORDER BY id DESC LIMIT 1");
            $stmt->execute([$customerId]);
            $cart = $stmt->fetch();
        } else {
            $stmt = $this->db->prepare("SELECT * FROM carts WHERE session_id = ? AND reservation_id IS NULL AND status = 'active' ORDER BY id DESC LIMIT 1");
            $stmt->execute([$sessionId]);
            $cart = $stmt->fetch();
        }

        return $cart;
    }
    
    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM carts WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getItems($cartId)
    {
        $stmt = $this->db->prepare("
            SELECT ci.*, mi.name as item_name, mi.description, mi.image_url, mi.restaurant_id
            FROM cart_items ci
            JOIN menu_items mi ON ci.menu_item_id = mi.id
            WHERE ci.cart_id = ?
            ORDER BY ci.id DESC
        ");
        $stmt->execute([$cartId]);
        return $stmt->fetchAll();
    }
    
    public function addItem($cartId, $menuItemId, $quantity, $price)
    {
        // Check if item already exists
        $stmt = $this->db->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND menu_item_id = ?");
        $stmt->execute([$cartId, $menuItemId]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            $newQuantity = $existing['quantity'] + $quantity;
            $stmt = $this->db->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
            return $stmt->execute([$newQuantity, $existing['id']]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO cart_items (cart_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)");
            return $stmt->execute([$cartId, $menuItemId, $quantity, $price]);
        }
    }
    
    public function updateItem($cartId, $cartItemId, $quantity)
    {
        if ($quantity <= 0) {
            $stmt = $this->db->prepare("DELETE FROM cart_items WHERE id = ? AND cart_id = ?");
            return $stmt->execute([$cartItemId, $cartId]);
        } else {
            $stmt = $this->db->prepare("UPDATE cart_items SET quantity = ? WHERE id = ? AND cart_id = ?");
            return $stmt->execute([$quantity, $cartItemId, $cartId]);
        }
    }
    
    public function attachToReservation($cartId, $reservationId)
    {
        $stmt = $this->db->prepare("UPDATE carts SET reservation_id = ?, status = 'attached' WHERE id = ?");
        return $stmt->execute([$reservationId, $cartId]);
    }
    
    public function getCartByReservation($reservationId)
    {
        $stmt = $this->db->prepare("SELECT * FROM carts WHERE reservation_id = ?");
        $stmt->execute([$reservationId]);
        return $stmt->fetch();
    }

    public function getCartSummary($cartId): array
    {
        $items = $this->getItems($cartId);
        $itemCount = 0;
        $total = 0.0;

        foreach ($items as &$item) {
            $item['line_total'] = (float)$item['price'] * (int)$item['quantity'];
            $itemCount += (int)$item['quantity'];
            $total += (float)$item['line_total'];
        }

        return [
            'items' => $items,
            'item_count' => $itemCount,
            'total' => $total,
        ];
    }

    public function mergeGuestCartToCustomer(int $customerId, string $sessionId): void
    {
        $customerCart = $this->getActiveCart($customerId, null);
        $guestCart = $this->getActiveCart(null, $sessionId);

        if (!$guestCart || ($customerCart && (int)$customerCart['id'] === (int)$guestCart['id'])) {
            return;
        }

        if (!$customerCart) {
            $stmt = $this->db->prepare("UPDATE carts SET customer_id = ?, session_id = NULL WHERE id = ?");
            $stmt->execute([$customerId, $guestCart['id']]);
            return;
        }

        $items = $this->getItems($guestCart['id']);
        foreach ($items as $item) {
            $this->addItem($customerCart['id'], (int)$item['menu_item_id'], (int)$item['quantity'], (float)$item['price']);
        }

        $stmt = $this->db->prepare("DELETE FROM carts WHERE id = ?");
        $stmt->execute([$guestCart['id']]);
    }
}
