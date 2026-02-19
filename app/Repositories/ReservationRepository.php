<?php
namespace App\Repositories;

use App\Core\Database;
use PDO;

class ReservationRepository
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data)
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                INSERT INTO reservations 
                (restaurant_id, customer_id, table_id, reservation_code, reservation_date, reservation_time, party_size, status, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['restaurant_id'],
                $data['customer_id'] ?? null,
                $data['table_id'],
                $data['reservation_code'],
                $data['reservation_date'],
                $data['reservation_time'],
                $data['party_size'],
                $data['status'] ?? 'pending',
                $data['notes'] ?? null
            ]);
            
            $reservationId = $this->db->lastInsertId();
            
            // Add guest details if provided
            if (!empty($data['guest_name'])) {
                $guestStmt = $this->db->prepare("
                    INSERT INTO reservation_guests (reservation_id, guest_name, guest_email, guest_phone)
                    VALUES (?, ?, ?, ?)
                ");
                $guestStmt->execute([
                    $reservationId,
                    $data['guest_name'],
                    $data['guest_email'] ?? null,
                    $data['guest_phone'] ?? null
                ]);
            }
            
            $this->db->commit();
            return $reservationId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function findByCode($code)
    {
        $stmt = $this->db->prepare("
            SELECT r.*, 
                   res.name as restaurant_name,
                   res.slug as restaurant_slug,
                   t.table_number,
                   c.name as customer_name,
                   COALESCE(SUM(ci.quantity), 0) as preorder_qty,
                   COALESCE(SUM(ci.quantity * ci.price), 0) as preorder_total
            FROM reservations r
            LEFT JOIN restaurants res ON r.restaurant_id = res.id
            LEFT JOIN tables t ON r.table_id = t.id
            LEFT JOIN customers c ON r.customer_id = c.id
            LEFT JOIN carts ct ON ct.reservation_id = r.id
            LEFT JOIN cart_items ci ON ci.cart_id = ct.id
            WHERE r.reservation_code = ?
            GROUP BY r.id
        ");
        $stmt->execute([$code]);
        return $stmt->fetch();
    }
    
    public function findById($id)
    {
        $stmt = $this->db->prepare("
            SELECT r.*, 
                   res.name as restaurant_name,
                   t.table_number,
                   c.name as customer_name,
                   COALESCE(SUM(ci.quantity), 0) as preorder_qty,
                   COALESCE(SUM(ci.quantity * ci.price), 0) as preorder_total
            FROM reservations r
            LEFT JOIN restaurants res ON r.restaurant_id = res.id
            LEFT JOIN tables t ON r.table_id = t.id
            LEFT JOIN customers c ON r.customer_id = c.id
            LEFT JOIN carts ct ON ct.reservation_id = r.id
            LEFT JOIN cart_items ci ON ci.cart_id = ct.id
            WHERE r.id = ?
            GROUP BY r.id
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function findByCustomer($customerId, $upcomingOnly = false)
    {
        $sql = "
            SELECT r.*, 
                   res.name as restaurant_name,
                   res.slug as restaurant_slug,
                   t.table_number
            FROM reservations r
            LEFT JOIN restaurants res ON r.restaurant_id = res.id
            LEFT JOIN tables t ON r.table_id = t.id
            WHERE r.customer_id = ?
        ";
        
        if ($upcomingOnly) {
            $sql .= " AND (r.reservation_date > CURDATE() OR (r.reservation_date = CURDATE() AND r.reservation_time > CURTIME()))";
            $sql .= " AND r.status IN ('pending', 'confirmed')";
        }
        
        $sql .= " ORDER BY r.reservation_date DESC, r.reservation_time DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId]);
        return $stmt->fetchAll();
    }
    
    public function getHistory($customerId)
    {
        $stmt = $this->db->prepare("
            SELECT r.*, 
                   res.name as restaurant_name,
                   res.slug as restaurant_slug,
                   t.table_number
            FROM reservations r
            LEFT JOIN restaurants res ON r.restaurant_id = res.id
            LEFT JOIN tables t ON r.table_id = t.id
            WHERE r.customer_id = ?
            AND (r.reservation_date < CURDATE() OR (r.reservation_date = CURDATE() AND r.reservation_time < CURTIME()) OR r.status IN ('completed', 'cancelled', 'no_show'))
            ORDER BY r.reservation_date DESC, r.reservation_time DESC
        ");
        $stmt->execute([$customerId]);
        return $stmt->fetchAll();
    }
    
    public function getGuestDetails($reservationId)
    {
        $stmt = $this->db->prepare("SELECT * FROM reservation_guests WHERE reservation_id = ?");
        $stmt->execute([$reservationId]);
        return $stmt->fetch();
    }
    
    public function checkAvailability($restaurantId, $date, $time, $duration, $buffer)
    {
        // Calculate time ranges
        $requestStart = strtotime($time);
        $requestEnd = $requestStart + ($duration * 60);
        $bufferStart = $requestStart - ($buffer * 60);
        $bufferEnd = $requestEnd + ($buffer * 60);
        
        $startBufferTime = date('H:i:s', $bufferStart);
        $endBufferTime = date('H:i:s', $bufferEnd);
        $durationInterval = sprintf('%02d:%02d:00', floor($duration / 60), $duration % 60);
        
        $stmt = $this->db->prepare("
            SELECT DISTINCT t.id, t.table_number, t.capacity, t.seating_preference
            FROM tables t
            WHERE t.restaurant_id = ? 
            AND t.is_active = TRUE
            AND t.id NOT IN (
                SELECT r.table_id
                FROM reservations r
                WHERE r.restaurant_id = ?
                AND r.reservation_date = ?
                AND r.status NOT IN ('cancelled', 'no_show')
                AND (
                    (r.reservation_time >= ? AND r.reservation_time < ?)
                    OR (r.reservation_time <= ? AND ADDTIME(r.reservation_time, ?) > ?)
                )
            )
        ");
        $stmt->execute([
            $restaurantId,
            $restaurantId,
            $date,
            $startBufferTime,
            $endBufferTime,
            $startBufferTime,
            $durationInterval,
            $endBufferTime
        ]);
        return $stmt->fetchAll();
    }
    
    public function updateStatus($id, $status)
    {
        $stmt = $this->db->prepare("UPDATE reservations SET status = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
    
    public function cancel($id)
    {
        return $this->updateStatus($id, 'cancelled');
    }
    
    public function getAllForRestaurant($restaurantId, $date = null, $status = null, $search = null)
    {
        $sql = "
            SELECT r.*, 
                   t.table_number,
                   COALESCE(c.name, rg.guest_name) as customer_name,
                   COALESCE(c.email, rg.guest_email) as customer_email,
                   COALESCE(c.phone, rg.guest_phone) as customer_phone,
                   COALESCE(SUM(ci.quantity), 0) as preorder_qty,
                   COALESCE(SUM(ci.quantity * ci.price), 0) as preorder_total
            FROM reservations r
            LEFT JOIN tables t ON r.table_id = t.id
            LEFT JOIN customers c ON r.customer_id = c.id
            LEFT JOIN reservation_guests rg ON r.id = rg.reservation_id
            LEFT JOIN carts ct ON ct.reservation_id = r.id
            LEFT JOIN cart_items ci ON ci.cart_id = ct.id
            WHERE r.restaurant_id = ?
        ";
        
        $params = [$restaurantId];
        
        if ($date) {
            $sql .= " AND r.reservation_date = ?";
            $params[] = $date;
        }

        if ($status && $status !== 'all') {
            $sql .= " AND r.status = ?";
            $params[] = $status;
        }

        $search = trim((string)$search);
        if ($search !== '') {
            $sql .= " AND (
                r.reservation_code LIKE ?
                OR t.table_number LIKE ?
                OR COALESCE(c.name, rg.guest_name, '') LIKE ?
                OR COALESCE(c.phone, rg.guest_phone, '') LIKE ?
            )";
            $searchLike = '%' . $search . '%';
            $params[] = $searchLike;
            $params[] = $searchLike;
            $params[] = $searchLike;
            $params[] = $searchLike;
        }
        
        $sql .= " GROUP BY r.id";
        $sql .= " ORDER BY r.reservation_date DESC, r.reservation_time DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function generateReservationCode()
    {
        do {
            $code = 'RES-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $stmt = $this->db->prepare("SELECT id FROM reservations WHERE reservation_code = ?");
            $stmt->execute([$code]);
        } while ($stmt->fetch());
        
        return $code;
    }
}
