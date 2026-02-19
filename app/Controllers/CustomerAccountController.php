<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\CustomerRepository;
use App\Repositories\ReservationRepository;
use App\Services\ReservationService;
use App\Repositories\CartRepository;

class CustomerAccountController extends Controller
{
    private $customerRepo;
    private $reservationRepo;
    private $reservationService;
    private $cartRepo;
    
    public function __construct()
    {
        $this->customerRepo = new CustomerRepository();
        $this->reservationRepo = new ReservationRepository();
        $this->reservationService = new ReservationService();
        $this->cartRepo = new CartRepository();
    }
    
    public function dashboard()
    {
        $customerId = $_SESSION['customer_id'];
        $customer = $this->customerRepo->findById($customerId);
        
        // Get upcoming reservations
        $upcomingReservations = $this->reservationRepo->findByCustomer($customerId, true);
        
        // Get recent history (last 5)
        $history = array_slice($this->reservationRepo->getHistory($customerId), 0, 5);
        
        $this->view('customer/dashboard', [
            'customer' => $customer,
            'upcomingReservations' => $upcomingReservations,
            'recentHistory' => $history,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    public function reservations()
    {
        $customerId = $_SESSION['customer_id'];
        $reservations = $this->reservationRepo->findByCustomer($customerId, true);
        
        // Get cart items for each reservation
        foreach ($reservations as &$reservation) {
            $cart = $this->cartRepo->getCartByReservation($reservation['id']);
            $reservation['cart_items'] = $cart ? $this->cartRepo->getItems($cart['id']) : [];
        }
        
        $this->view('customer/reservations', [
            'reservations' => $reservations,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    public function history()
    {
        $customerId = $_SESSION['customer_id'];
        $reservations = $this->reservationRepo->getHistory($customerId);
        
        // Get cart items for each reservation
        foreach ($reservations as &$reservation) {
            $cart = $this->cartRepo->getCartByReservation($reservation['id']);
            $reservation['cart_items'] = $cart ? $this->cartRepo->getItems($cart['id']) : [];
        }
        
        $this->view('customer/history', [
            'reservations' => $reservations,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    public function cancelReservation($params)
    {
        $this->validateCsrf();
        
        $reservationId = $params['id'] ?? null;
        $customerId = $_SESSION['customer_id'];
        
        if (!$reservationId) {
            $_SESSION['error'] = 'Invalid reservation';
            $this->redirect('/account/reservations');
            return;
        }
        
        // Verify reservation belongs to customer
        $reservation = $this->reservationRepo->findById($reservationId);
        if (!$reservation || $reservation['customer_id'] != $customerId) {
            $_SESSION['error'] = 'Reservation not found';
            $this->redirect('/account/reservations');
            return;
        }
        
        try {
            $this->reservationService->cancelReservation($reservationId);
            $_SESSION['success'] = 'Reservation cancelled successfully';
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        
        $this->redirect('/account/reservations');
    }
    
    public function updateProfile()
    {
        $this->validateCsrf();
        
        $customerId = $_SESSION['customer_id'];
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        
        if (empty($name)) {
            $_SESSION['error'] = 'Name is required';
            $this->redirect('/account');
            return;
        }
        
        $data = ['name' => $name];
        if (!empty($phone)) {
            $data['phone'] = $phone;
        }
        
        if ($this->customerRepo->update($customerId, $data)) {
            $_SESSION['customer_name'] = $name;
            $_SESSION['success'] = 'Profile updated successfully';
        } else {
            $_SESSION['error'] = 'Failed to update profile';
        }
        
        $this->redirect('/account');
    }
    
    public function updatePassword()
    {
        $this->validateCsrf();
        
        $customerId = $_SESSION['customer_id'];
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($currentPassword) || empty($newPassword)) {
            $_SESSION['error'] = 'Please fill in all password fields';
            $this->redirect('/account');
            return;
        }
        
        if ($newPassword !== $confirmPassword) {
            $_SESSION['error'] = 'New passwords do not match';
            $this->redirect('/account');
            return;
        }
        
        $customer = $this->customerRepo->findById($customerId);
        if (!password_verify($currentPassword, $customer['password_hash'])) {
            $_SESSION['error'] = 'Current password is incorrect';
            $this->redirect('/account');
            return;
        }
        
        $config = require CONFIG_PATH . '/config.php';
        if (strlen($newPassword) < $config['security']['password_min_length']) {
            $_SESSION['error'] = 'Password must be at least ' . $config['security']['password_min_length'] . ' characters';
            $this->redirect('/account');
            return;
        }
        
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        if ($this->customerRepo->update($customerId, ['password_hash' => $passwordHash])) {
            $_SESSION['success'] = 'Password updated successfully';
        } else {
            $_SESSION['error'] = 'Failed to update password';
        }
        
        $this->redirect('/account');
    }
}

