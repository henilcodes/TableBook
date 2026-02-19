<?php
namespace App\Repositories;

use App\Core\Database;
use PDO;

class CustomerRepository
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function findByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM customers WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function create($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO customers (name, email, phone, password_hash, email_verified)
            VALUES (?, ?, ?, ?, ?)
        ");
        $emailVerified = !empty($data['email_verified']) ? 1 : 0;
        return $stmt->execute([
            $data['name'],
            $data['email'],
            $data['phone'] ?? null,
            $data['password_hash'],
            $emailVerified
        ]);
    }
    
    public function update($id, $data)
    {
        $fields = [];
        $values = [];
        
        if (isset($data['name'])) {
            $fields[] = "name = ?";
            $values[] = $data['name'];
        }
        if (isset($data['phone'])) {
            $fields[] = "phone = ?";
            $values[] = $data['phone'];
        }
        if (isset($data['password_hash'])) {
            $fields[] = "password_hash = ?";
            $values[] = $data['password_hash'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $values[] = $id;
        $sql = "UPDATE customers SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }
}
