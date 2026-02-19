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
    
    public function __construct()
    {
        $this->restaurantRepo = new RestaurantRepository();
        $this->reservationService = new ReservationService();
        $this->reservationRepo = new ReservationRepository();
        $this->cartRepo = new CartRepository();
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
        
        $restaurantId = $_POST['restaurant_id'] ?? null;
        $tableId = $_POST['table_id'] ?? null;
        $date = $_POST['reservation_date'] ?? null;
        $time = $_POST['reservation_time'] ?? null;
        $partySize = $_POST['party_size'] ?? null;
        $guestName = $_POST['guest_name'] ?? null;
        $guestEmail = $_POST['guest_email'] ?? null;
        $guestPhone = $_POST['guest_phone'] ?? null;
        $notes = $_POST['notes'] ?? null;
        
        if (!$restaurantId || !$tableId || !$date || !$time || !$partySize) {
            $_SESSION['error'] = 'Please fill in all required fields';
            $this->redirect('/restaurants/' . ($_POST['restaurant_slug'] ?? ''));
            return;
        }
        
        try {
            $data = [
                'restaurant_id' => $restaurantId,
                'table_id' => $tableId,
                'reservation_date' => $date,
                'reservation_time' => $time,
                'party_size' => $partySize,
                'notes' => $notes
            ];
            
            // Add customer ID if logged in
            if (!empty($_SESSION['customer_id'])) {
                $data['customer_id'] = $_SESSION['customer_id'];
            } else {
                // Guest booking
                $data['guest_name'] = $guestName;
                $data['guest_email'] = $guestEmail;
                $data['guest_phone'] = $guestPhone;
            }
            
            $reservation = $this->reservationService->createReservation($data);
            
            // Attach cart if exists
            $customerId = $_SESSION['customer_id'] ?? null;
            $sessionId = session_id();
            $cart = $this->cartRepo->getActiveCart($customerId, $sessionId);
            if ($cart) {
                $cartItems = $this->cartRepo->getItems($cart['id']);
                $cartMatchesRestaurant = !empty($cartItems) && !array_filter($cartItems, function ($item) use ($restaurantId) {
                    return (int)$item['restaurant_id'] !== (int)$restaurantId;
                });
            } else {
                $cartItems = [];
                $cartMatchesRestaurant = false;
            }

            if ($cart && !empty($cartItems) && $cartMatchesRestaurant) {
                $this->cartRepo->attachToReservation($cart['id'], $reservation['id']);
            }
            
            $_SESSION['success'] = 'Reservation created successfully!';
            $this->redirect('/reservation/' . $reservation['reservation_code']);
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $this->redirect('/restaurants/' . ($_POST['restaurant_slug'] ?? ''));
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
}
