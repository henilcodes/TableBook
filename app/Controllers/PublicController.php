<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\RestaurantRepository;
use App\Services\ReservationService;
use App\Repositories\ReservationRepository;
use App\Repositories\CartRepository;

class PublicController extends Controller
{
    private $restaurantRepo;
    private $reservationService;
    private $reservationRepo;
    private $cartRepo;
    private $db;
    
    public function __construct()
    {
        $this->restaurantRepo = new RestaurantRepository();
        $this->reservationService = new ReservationService();
        $this->reservationRepo = new ReservationRepository();
        $this->cartRepo = new CartRepository();
        $this->db = \App\Core\Database::getInstance()->getConnection();
    }
    
    public function home()
    {
        $restaurants = $this->restaurantRepo->findAll();
        $this->view('public/home', [
            'restaurants' => $restaurants,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    public function restaurants()
    {
        $restaurants = $this->restaurantRepo->findAll();
        $this->view('public/restaurants', [
            'restaurants' => $restaurants,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    public function restaurantDetail($params)
    {
        $slug = $params['slug'] ?? '';
        $restaurant = $this->restaurantRepo->findBySlug($slug);
        
        if (!$restaurant) {
            http_response_code(404);
            require_once APP_PATH . '/Views/errors/404.php';
            return;
        }
        
        $hours = $this->restaurantRepo->getHours($restaurant['id']);
        $tables = $this->restaurantRepo->getTables($restaurant['id']);
        $categories = $this->restaurantRepo->getMenuCategories($restaurant['id']);
        $menuItems = [];
        foreach ($categories as $category) {
            $menuItems[$category['id']] = $this->restaurantRepo->getMenuItems($restaurant['id'], $category['id']);
        }
        
        $this->view('public/restaurant_detail', [
            'restaurant' => $restaurant,
            'hours' => $hours,
            'tables' => $tables,
            'categories' => $categories,
            'menuItems' => $menuItems,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    public function checkAvailability()
    {
        header('Content-Type: application/json');
        
        $restaurantId = $_GET['restaurant_id'] ?? null;
        $date = $_GET['date'] ?? null;
        $time = $_GET['time'] ?? null;
        $partySize = $_GET['party_size'] ?? null;
        
        if (!$restaurantId || !$date || !$time || !$partySize) {
            $this->json(['error' => 'Missing required parameters'], 400);
            return;
        }
        
        try {
            $availableTables = $this->reservationService->checkAvailability(
                $restaurantId,
                $date,
                $time,
                $partySize
            );
            
            $this->json(['success' => true, 'tables' => $availableTables]);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }
    
    public function createReservation()
    {
        $this->validateCsrf();
        
        if (empty($_SESSION['customer_id'])) {
            $_SESSION['error'] = 'Please log in to book a table.';
            $this->redirect('/login?redirect=' . urlencode($_SERVER['HTTP_REFERER'] ?? '/restaurants'));
            return;
        }

        $restaurantId = $_POST['restaurant_id'] ?? null;
        $tableId = $_POST['table_id'] ?? null;
        $date = $_POST['reservation_date'] ?? null;
        $time = $_POST['reservation_time'] ?? null;
        $partySize = $_POST['party_size'] ?? null;
        $notes = $_POST['notes'] ?? null;
        
        if (!$restaurantId || !$tableId || !$date || !$time || !$partySize) {
            $_SESSION['error'] = 'Please fill in all required fields';
            $this->redirect('/restaurants/' . ($_POST['restaurant_slug'] ?? ''));
            return;
        }
        
        try {
            // Verify availability before proceeding to payment
            $this->reservationService->checkAvailability(
                $restaurantId,
                $date,
                $time,
                $partySize
            );
            
            $data = [
                'restaurant_id' => $restaurantId,
                'customer_id' => $_SESSION['customer_id'],
                'table_id' => $tableId,
                'reservation_date' => $date,
                'reservation_time' => $time,
                'party_size' => $partySize,
                'notes' => $notes
            ];
            
            // Store temporarily in session. Do NOT create DB record yet.
            $_SESSION['pending_reservation'] = $data;
            
            $this->redirect('/payment/checkout');
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $this->redirect('/restaurants/' . ($_POST['restaurant_slug'] ?? ''));
        }
    }

    public function payment()
    {
        if (empty($_SESSION['pending_reservation'])) {
            $_SESSION['error'] = 'No pending reservation found.';
            $this->redirect('/account');
            return;
        }

        $pending = $_SESSION['pending_reservation'];
        $restaurant = $this->restaurantRepo->findById($pending['restaurant_id']);
        
        $config = require CONFIG_PATH . '/config.php';
        $razorpay = $config['razorpay'];

        // Calculate preorder items
        $customerId = $_SESSION['customer_id'];
        $sessionId = session_id();
        $cart = $this->cartRepo->getActiveCart($customerId, $sessionId);
        $cartItems = $cart ? $this->cartRepo->getItems($cart['id']) : [];
        
        $preorderTotal = 0;
        foreach ($cartItems as $item) {
            $preorderTotal += (float)$item['price'] * (int)$item['quantity'];
        }
        
        $bookingFee = 10;
        $total = $preorderTotal + $bookingFee;

        $orderData = [
            'amount' => (int)($total * 100), 
            'currency' => 'INR',
            'receipt' => 'rec_' . time(),
            'notes' => [
                'restaurant' => $restaurant['name'],
                'type' => 'reservation_checkout'
            ]
        ];

        $ch = curl_init('https://api.razorpay.com/v1/orders');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $razorpay['key_id'] . ":" . $razorpay['key_secret']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        curl_close($ch);
        $order = json_decode($response, true);

        if (isset($order['error'])) {
            $_SESSION['error'] = 'Could not initialize payment: ' . ($order['error']['description'] ?? 'Unknown error');
            $this->redirect('/');
            return;
        }

        $this->view('public/payment', [
            'title' => 'Payment - TableTap',
            'order' => $order,
            'razorpay_key' => $razorpay['key_id'],
            'pending' => $pending,
            'restaurant' => $restaurant,
            'preorder_total' => $preorderTotal,
            'booking_fee' => $bookingFee,
            'total' => $total,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function verifyPayment()
    {
        $this->validateCsrf();

        if (empty($_SESSION['pending_reservation'])) {
            $_SESSION['error'] = 'Payment session expired.';
            $this->redirect('/');
            return;
        }

        $razorpay_payment_id = $_POST['razorpay_payment_id'] ?? '';
        $razorpay_order_id = $_POST['razorpay_order_id'] ?? '';
        $razorpay_signature = $_POST['razorpay_signature'] ?? '';

        if (empty($razorpay_payment_id) || empty($razorpay_order_id) || empty($razorpay_signature)) {
            $_SESSION['error'] = 'Payment failed or cancelled.';
            $this->redirect('/');
            return;
        }

        $config = require CONFIG_PATH . '/config.php';
        $key_secret = $config['razorpay']['key_secret'];

        $expected_signature = hash_hmac('sha256', $razorpay_order_id . '|' . $razorpay_payment_id, $key_secret);
        
        if ($expected_signature !== $razorpay_signature) {
            $_SESSION['error'] = 'Invalid payment signature.';
            $this->redirect('/');
            return;
        }

        try {
            $pending = $_SESSION['pending_reservation'];
            
            // 1. THIS triggers the DB Insert and Email Confirmation
            $reservation = $this->reservationService->createReservation($pending);
            
            // 2. Set status to confirmed automatically
            $this->reservationRepo->updateStatus($reservation['id'], 'confirmed');
            
            // 3. Attach Cart
            $cart = $this->cartRepo->getActiveCart($_SESSION['customer_id'], session_id());
            $preorderTotal = 0;
            if ($cart) {
                $this->cartRepo->attachToReservation($cart['id'], $reservation['id']);
                $items = $this->cartRepo->getItems($cart['id']);
                foreach ($items as $item) {
                   $preorderTotal += (float)$item['price'] * (int)$item['quantity'];
                }
            }
            
            $finalTotal = $preorderTotal + 10;
            
            // 4. Record Payment
            $stmt = $this->db->prepare("INSERT INTO payments (reservation_id, payment_id, order_id, status, amount) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$reservation['id'], $razorpay_payment_id, $razorpay_order_id, 'paid', $finalTotal]);

            unset($_SESSION['pending_reservation']);

            $_SESSION['success'] = 'Payment successful! Your reservation is confirmed and details have been emailed to you.';
            $this->redirect('/reservation/' . $reservation['reservation_code']);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Failed to finalize reservation: ' . $e->getMessage();
            $this->redirect('/');
        }
    }
    
    public function viewReservation($params)
    {
        $code = $params['code'] ?? '';
        $reservation = $this->reservationRepo->findByCode($code);
        
        if (!$reservation) {
            http_response_code(404);
            require_once APP_PATH . '/Views/errors/404.php';
            return;
        }
        
        // Get guest details if guest booking
        $guestDetails = null;
        if (!$reservation['customer_id']) {
            $guestDetails = $this->reservationRepo->getGuestDetails($reservation['id']);
        }
        
        // Get cart/preorder if exists
        $cart = $this->cartRepo->getCartByReservation($reservation['id']);
        $cartItems = $cart ? $this->cartRepo->getItems($cart['id']) : [];
        
        $this->view('public/reservation_confirmation', [
            'reservation' => $reservation,
            'guestDetails' => $guestDetails,
            'cartItems' => $cartItems,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function about()
    {
        $this->view('public/about', [
            'title' => 'About Us - TableTap'
        ]);
    }

    public function contact()
    {
        $this->view('public/contact', [
            'title' => 'Contact Us - TableTap',
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function contactSend()
    {
        $this->validateCsrf();
        
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName  = trim($_POST['last_name'] ?? '');
        $name      = trim($firstName . ' ' . $lastName);
        $email     = trim($_POST['email'] ?? '');
        $subject   = trim($_POST['subject'] ?? 'General Inquiry');
        $message   = trim($_POST['message'] ?? '');
        
        if (empty($firstName) || empty($email) || empty($message)) {
            $_SESSION['error'] = 'Please fill out all required fields.';
            $this->redirect('/contact');
            return;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Please provide a valid email address.';
            $this->redirect('/contact');
            return;
        }
        
        $mailService = new \App\Services\MailService();
        $sent = $mailService->sendContactMessage($name, $email, $subject, $message);
        
        if ($sent) {
            $_SESSION['success'] = 'Your message has been sent successfully. We will get back to you soon!';
        } else {
            $_SESSION['error'] = 'Failed to send your message. Please try again later.';
        }
        
        $this->redirect('/contact');
    }

    public function privacy()
    {
        $this->view('public/privacy', [
            'title' => 'Privacy Policy - TableTap'
        ]);
    }

    public function support()
    {
        $this->view('public/support', [
            'title' => 'Customer Support - TableTap'
        ]);
    }
}
