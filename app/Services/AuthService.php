<?php
namespace App\Services;

use App\Repositories\CustomerRepository;
use App\Repositories\UserRepository;
use App\Repositories\CartRepository;

class AuthService
{
    private $customerRepo;
    private $userRepo;
    private $cartRepo;
    
    public function __construct()
    {
        $this->customerRepo = new CustomerRepository();
        $this->userRepo = new UserRepository();
        $this->cartRepo = new CartRepository();
    }
    
    public function registerCustomer($data)
    {
        // Check if email already exists
        $existing = $this->customerRepo->findByEmail($data['email']);
        if ($existing) {
            throw new \Exception('Email already registered');
        }
        
        // Validate password
        $config = require CONFIG_PATH . '/config.php';
        if (strlen($data['password']) < $config['security']['password_min_length']) {
            throw new \Exception('Password must be at least ' . $config['security']['password_min_length'] . ' characters');
        }
        
        // Hash password
        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        unset($data['password']);
        
        // Create customer
        if ($this->customerRepo->create($data)) {
            return $this->customerRepo->findByEmail($data['email']);
        }
        
        throw new \Exception('Registration failed');
    }
    
    public function loginCustomer($email, $password, $rememberMe = false, $guestSessionId = null)
    {
        $customer = $this->customerRepo->findByEmail($email);
        if (!$customer) {
            return false;
        }
        
        if (password_verify($password, $customer['password_hash'])) {
            $oldSessionId = $guestSessionId ?: session_id();
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_regenerate_id(true);
            }

            $_SESSION['customer_id'] = $customer['id'];
            $_SESSION['customer_name'] = $customer['name'];
            $_SESSION['customer_email'] = $customer['email'];

            if (!empty($oldSessionId)) {
                $this->cartRepo->mergeGuestCartToCustomer((int)$customer['id'], (string)$oldSessionId);
            }
            
            if ($rememberMe) {
                // Set cookie for 30 days
                setcookie('remember_token', bin2hex(random_bytes(32)), time() + (30 * 24 * 60 * 60), '/');
            }
            
            return $customer;
        }
        
        return false;
    }
    
    public function loginAdmin($email, $password)
    {
        $user = $this->userRepo->findByEmail($email);
        if (!$user) {
            return false;
        }
        
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_role'] = $user['role'];
            return $user;
        }
        
        return false;
    }
    
    public function logout()
    {
        unset($_SESSION['customer_id'], $_SESSION['customer_name'], $_SESSION['customer_email']);
        setcookie('remember_token', '', time() - 3600, '/');
    }
}
