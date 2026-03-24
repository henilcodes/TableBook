<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Services\AuthService;
use App\Repositories\ReservationRepository;
use App\Repositories\RestaurantRepository;

class AdminController extends Controller
{
    private $authService;
    private $reservationRepo;
    private $restaurantRepo;
    
    public function __construct()
    {
        $this->authService = new AuthService();
        $this->reservationRepo = new ReservationRepository();
        $this->restaurantRepo = new RestaurantRepository();
    }

    private function generateRestaurantSlug(string $name, ?int $excludeId = null): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
        $slug = trim($slug, '-');
        if ($slug === '') {
            $slug = 'restaurant';
        }

        $base = $slug;
        $counter = 1;
        while ($this->restaurantRepo->slugExists($slug, $excludeId)) {
            $slug = $base . '-' . $counter;
            $counter++;
        }
        return $slug;
    }
    
    public function showLogin()
    {
        if (!empty($_SESSION['admin_id'])) {
            $this->redirect('/admin');
            return;
        }
        
        $this->view('admin/login', [
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    public function login()
    {
        $this->validateCsrf();
        
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Please enter email and password';
            $this->redirect('/admin/login');
            return;
        }
        
        $user = $this->authService->loginAdmin($email, $password);
        
        if ($user) {
            $_SESSION['success'] = 'Welcome back, ' . $user['username'] . '!';
            $this->redirect('/admin');
        } else {
            $_SESSION['error'] = 'Invalid email or password';
            $this->redirect('/admin/login');
        }
    }
    
    public function logout()
    {
        $this->validateCsrf();
        unset($_SESSION['admin_id'], $_SESSION['admin_username'], $_SESSION['admin_role']);
        $_SESSION['success'] = 'You have been logged out';
        $this->redirect('/admin/login');
    }
    
    public function dashboard()
    {
        $restaurants = $this->restaurantRepo->findAll();
        $restaurant = null;
        $requestedRestaurantId = isset($_GET['restaurant_id']) ? (int)$_GET['restaurant_id'] : 0;
        if ($requestedRestaurantId > 0) {
            $restaurant = $this->restaurantRepo->findById($requestedRestaurantId);
        }
        if (!$restaurant) {
            $restaurant = $restaurants[0] ?? null;
        }
        
        if (!$restaurant) {
            $_SESSION['error'] = 'No restaurant found. Please set up a restaurant first.';
            $this->view('admin/dashboard', [
                'restaurant' => null,
                'restaurants' => [],
                'todayReservations' => [],
                'csrf_token' => $this->generateCsrfToken()
            ]);
            return;
        }
        
        // Get today's reservations
        $todayReservations = $this->reservationRepo->getAllForRestaurant($restaurant['id'], date('Y-m-d'));
        
        // Get stats
        $allReservations = $this->reservationRepo->getAllForRestaurant($restaurant['id']);
        $tables = $this->restaurantRepo->getAllTablesAdmin($restaurant['id']);
        $categories = $this->restaurantRepo->getMenuCategories($restaurant['id']);
        $items = $this->restaurantRepo->getAllMenuItemsAdmin($restaurant['id']);
        $hours = $this->restaurantRepo->getHours($restaurant['id']);

        $stats = [
            'total' => count($allReservations),
            'today' => count($todayReservations),
            'confirmed' => count(array_filter($allReservations, fn($r) => $r['status'] === 'confirmed')),
            'pending' => count(array_filter($allReservations, fn($r) => $r['status'] === 'pending')),
            'seated' => count(array_filter($allReservations, fn($r) => $r['status'] === 'seated')),
            'completed' => count(array_filter($allReservations, fn($r) => $r['status'] === 'completed')),
        ];

        $moduleStats = [
            'tables' => count($tables),
            'active_tables' => count(array_filter($tables, fn($t) => !empty($t['is_active']))),
            'menu_categories' => count($categories),
            'menu_items' => count($items),
            'available_items' => count(array_filter($items, fn($i) => !empty($i['is_available']))),
            'hours_days' => count($hours),
            'hours_open_days' => count(array_filter($hours, fn($h) => empty($h['is_closed']))),
        ];
        
        $this->view('admin/dashboard', [
            'restaurant' => $restaurant,
            'restaurants' => $restaurants,
            'todayReservations' => $todayReservations,
            'stats' => $stats,
            'moduleStats' => $moduleStats,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    public function reservations()
    {
        $restaurants = $this->restaurantRepo->findAll();
        $restaurant = null;
        $requestedRestaurantId = isset($_GET['restaurant_id']) ? (int)$_GET['restaurant_id'] : 0;
        if ($requestedRestaurantId > 0) {
            $restaurant = $this->restaurantRepo->findById($requestedRestaurantId);
        }
        if (!$restaurant) {
            $restaurant = $restaurants[0] ?? null;
        }
        
        if (!$restaurant) {
            $_SESSION['error'] = 'No restaurant found';
            $this->redirect('/admin');
            return;
        }
        
        $date = $_GET['date'] ?? date('Y-m-d');
        $status = $_GET['status'] ?? 'all';
        $search = trim((string)($_GET['search'] ?? ''));
        $validStatuses = ['all', 'pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show'];
        if (!in_array($status, $validStatuses, true)) {
            $status = 'all';
        }

        $reservations = $this->reservationRepo->getAllForRestaurant($restaurant['id'], $date, $status, $search);
        
        $this->view('admin/reservations', [
            'restaurants' => $restaurants,
            'restaurant' => $restaurant,
            'reservations' => $reservations,
            'selectedDate' => $date,
            'selectedStatus' => $status,
            'searchTerm' => $search,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    public function updateReservationStatus($params)
    {
        header('Content-Type: application/json');
        $this->validateCsrf();
        
        $reservationId = $params['id'] ?? null;
        $status = $_POST['status'] ?? null;
        
        if (!$reservationId || !$status) {
            $this->json(['error' => 'Missing required parameters'], 400);
            return;
        }
        
        $validStatuses = ['pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show'];
        if (!in_array($status, $validStatuses)) {
            $this->json(['error' => 'Invalid status'], 400);
            return;
        }

        $reservation = $this->reservationRepo->findById((int)$reservationId);
        if (!$reservation) {
            $this->json(['error' => 'Reservation not found'], 404);
            return;
        }

        $requestedRestaurantId = isset($_GET['restaurant_id']) ? (int)$_GET['restaurant_id'] : 0;
        if ($requestedRestaurantId > 0 && (int)$reservation['restaurant_id'] !== $requestedRestaurantId) {
            $this->json(['error' => 'Reservation does not belong to this restaurant'], 403);
            return;
        }
        
        if ($this->reservationRepo->updateStatus($reservationId, $status)) {
            // Send email notification to customer
            try {
                $updatedReservation = $this->reservationRepo->findById((int)$reservationId);
                // We need customer email
                $customerEmail = null;
                if (!empty($updatedReservation['customer_id'])) {
                    $customerRepo = new \App\Repositories\CustomerRepository();
                    $customer = $customerRepo->findById($updatedReservation['customer_id']);
                    $customerEmail = $customer['email'] ?? null;
                } else {
                    $guestData = $this->reservationRepo->getGuestDetails((int)$reservationId);
                    $customerEmail = $guestData['guest_email'] ?? null;
                }
                
                if ($customerEmail) {
                    $mailService = new \App\Services\MailService();
                    $mailService->sendReservationStatusUpdate($updatedReservation, $customerEmail);
                }
            } catch (\Exception $e) {
                error_log('Failed to send status update email: ' . $e->getMessage());
            }

            $this->json(['success' => true, 'message' => 'Reservation status updated']);
        } else {
            $this->json(['error' => 'Failed to update status'], 500);
        }
    }
    
    public function exportReservations()
    {
        $restaurants = $this->restaurantRepo->findAll();
        $restaurant = null;
        $requestedRestaurantId = isset($_GET['restaurant_id']) ? (int)$_GET['restaurant_id'] : 0;
        if ($requestedRestaurantId > 0) {
            $restaurant = $this->restaurantRepo->findById($requestedRestaurantId);
        }
        if (!$restaurant) {
            $restaurant = $restaurants[0] ?? null;
        }
        
        if (!$restaurant) {
            $_SESSION['error'] = 'No restaurant found';
            $this->redirect('/admin/reservations');
            return;
        }
        
        $date = $_GET['date'] ?? date('Y-m-d');
        $status = $_GET['status'] ?? 'all';
        $search = trim((string)($_GET['search'] ?? ''));
        $validStatuses = ['all', 'pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show'];
        if (!in_array($status, $validStatuses, true)) {
            $status = 'all';
        }
        $reservations = $this->reservationRepo->getAllForRestaurant($restaurant['id'], $date, $status, $search);
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="reservations_' . preg_replace('/[^a-z0-9_-]/i', '', $restaurant['slug']) . '_' . $date . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Code', 'Date', 'Time', 'Table', 'Party Size', 'Customer', 'Phone', 'Order Qty', 'Order Total', 'Source', 'Status', 'Notes'], ',', '"', '\\');
        
        foreach ($reservations as $reservation) {
            fputcsv($output, [
                $reservation['reservation_code'],
                $reservation['reservation_date'],
                $reservation['reservation_time'],
                $reservation['table_number'],
                $reservation['party_size'],
                $reservation['customer_name'] ?? 'Guest',
                $reservation['customer_phone'] ?? '',
                (int)($reservation['preorder_qty'] ?? 0),
                number_format((float)($reservation['preorder_total'] ?? 0), 2),
                $reservation['reservation_source'] ?? 'web',
                ucfirst($reservation['status']),
                $reservation['notes'] ?? ''
            ], ',', '"', '\\');
        }
        
        fclose($output);
        exit;
    }

    public function exportRestaurants()
    {
        $restaurants = $this->restaurantRepo->findAll();
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="restaurants_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Name', 'Slug', 'Cuisine', 'Address', 'Phone', 'Email', 'Rating', 'Created At'], ',', '"', '\\');
        
        foreach ($restaurants as $restaurant) {
            fputcsv($output, [
                $restaurant['id'],
                $restaurant['name'],
                $restaurant['slug'],
                $restaurant['cuisine_type'],
                $restaurant['address'],
                $restaurant['phone'],
                $restaurant['email'],
                $restaurant['rating'],
                $restaurant['created_at']
            ], ',', '"', '\\');
        }
        
        fclose($output);
        exit;
    }

    public function exportTables($params)
    {
        $restaurant = $this->getRestaurantFromParams($params);
        if (!$restaurant) $restaurant = $this->getDefaultRestaurant();
        
        if (!$restaurant) {
            $this->redirect('/admin/restaurants');
            return;
        }

        $tables = $this->restaurantRepo->getAllTablesAdmin($restaurant['id']);
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="tables_' . $restaurant['slug'] . '_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Table Number', 'Section', 'Capacity', 'Min Party', 'Max Party', 'Seating', 'Status', 'Notes'], ',', '"', '\\');
        
        foreach ($tables as $table) {
            fputcsv($output, [
                $table['table_number'],
                $table['section_name'],
                $table['capacity'],
                $table['min_party_size'],
                $table['max_party_size'] ?: 'N/A',
                ucfirst($table['seating_preference']),
                $table['is_active'] ? 'Active' : 'Inactive',
                $table['notes']
            ], ',', '"', '\\');
        }
        
        fclose($output);
        exit;
    }

    public function exportMenu($params)
    {
        $restaurant = $this->getRestaurantFromParams($params);
        if (!$restaurant) $restaurant = $this->getDefaultRestaurant();
        
        if (!$restaurant) {
            $this->redirect('/admin/restaurants');
            return;
        }

        $items = $this->restaurantRepo->getAllMenuItemsAdmin($restaurant['id']);
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="menu_' . $restaurant['slug'] . '_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Name', 'Category', 'Description', 'Price', 'Available', 'Veg/Non-Veg', 'Spiciness'], ',', '"', '\\');
        
        foreach ($items as $item) {
            fputcsv($output, [
                $item['name'],
                $item['category_name'],
                $item['description'],
                number_format($item['price'], 2),
                $item['is_available'] ? 'Yes' : 'No',
                $item['is_vegetarian'] ? 'Veg' : 'Non-Veg',
                $item['spice_level']
            ], ',', '"', '\\');
        }
        
        fclose($output);
        exit;
    }

    public function exportHours($params)
    {
        $restaurant = $this->getRestaurantFromParams($params);
        if (!$restaurant) $restaurant = $this->getDefaultRestaurant();
        
        if (!$restaurant) {
            $this->redirect('/admin/restaurants');
            return;
        }

        $hours = $this->restaurantRepo->getHours($restaurant['id']);
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="hours_' . $restaurant['slug'] . '_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Day', 'Status', 'Open Time', 'Close Time'], ',', '"', '\\');
        
        foreach ($hours as $hour) {
            fputcsv($output, [
                $days[$hour['day_of_week']],
                $hour['is_closed'] ? 'Closed' : 'Open',
                $hour['is_closed'] ? '-' : date('g:i A', strtotime($hour['open_time'])),
                $hour['is_closed'] ? '-' : date('g:i A', strtotime($hour['close_time']))
            ], ',', '"', '\\');
        }
        
        fclose($output);
        exit;
    }

    // Restaurant management
    public function restaurants()
    {
        $restaurants = $this->restaurantRepo->findAll();

        $this->view('admin/restaurants', [
            'restaurants' => $restaurants,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function showCreateRestaurant()
    {
        $this->view('admin/restaurant_form', [
            'mode' => 'create',
            'restaurant' => null,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function createRestaurant()
    {
        $this->validateCsrf();

        $name = trim($_POST['name'] ?? '');
        $cuisine = trim($_POST['cuisine_type'] ?? '');

        if ($name === '') {
            $_SESSION['error'] = 'Restaurant name is required.';
            $this->redirect('/admin/restaurants/create');
            return;
        }

        $slug = $this->generateRestaurantSlug($name);

        $data = [
            'name' => $name,
            'slug' => $slug,
            'description' => $_POST['description'] ?? null,
            'cuisine_type' => $cuisine ?: null,
            'address' => $_POST['address'] ?? null,
            'phone' => $_POST['phone'] ?? null,
            'email' => $_POST['email'] ?? null,
            'image_url' => $_POST['image_url'] ?? null,
            'rating' => $_POST['rating'] !== '' ? (float)$_POST['rating'] : 0.00,
        ];

        $newRestaurantId = (int)$this->restaurantRepo->create($data);
        $this->restaurantRepo->ensureDefaultHours($newRestaurantId);

        $_SESSION['success'] = 'Restaurant created successfully.';
        $this->redirect('/admin/restaurants');
    }

    public function showEditRestaurant($params)
    {
        $id = isset($params['id']) ? (int)$params['id'] : 0;
        $restaurant = $this->restaurantRepo->findById($id);

        if (!$restaurant) {
            $_SESSION['error'] = 'Restaurant not found.';
            $this->redirect('/admin/restaurants');
            return;
        }

        $this->view('admin/restaurant_form', [
            'mode' => 'edit',
            'restaurant' => $restaurant,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function updateRestaurant($params)
    {
        $this->validateCsrf();

        $id = isset($params['id']) ? (int)$params['id'] : 0;
        $restaurant = $this->restaurantRepo->findById($id);
        if (!$restaurant) {
            $_SESSION['error'] = 'Restaurant not found.';
            $this->redirect('/admin/restaurants');
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $cuisine = trim($_POST['cuisine_type'] ?? '');

        if ($name === '') {
            $_SESSION['error'] = 'Restaurant name is required.';
            $this->redirect('/admin/restaurants/' . $id . '/edit');
            return;
        }

        // Allow overriding slug or regenerate from name if left blank
        $slugInput = trim($_POST['slug'] ?? '');
        if ($slugInput === '') {
            $slug = $this->generateRestaurantSlug($name, $id);
        } else {
            $slug = $this->generateRestaurantSlug($slugInput, $id);
        }

        $data = [
            'name' => $name,
            'slug' => $slug,
            'description' => $_POST['description'] ?? null,
            'cuisine_type' => $cuisine ?: null,
            'address' => $_POST['address'] ?? null,
            'phone' => $_POST['phone'] ?? null,
            'email' => $_POST['email'] ?? null,
            'image_url' => $_POST['image_url'] ?? null,
            'rating' => $_POST['rating'] !== '' ? (float)$_POST['rating'] : 0.00,
        ];

        $this->restaurantRepo->update($id, $data);

        $_SESSION['success'] = 'Restaurant updated successfully.';
        $this->redirect('/admin/restaurants');
    }

    public function exportDashboardStats()
    {
        $restaurants = $this->restaurantRepo->findAll();
        $restaurant = null;
        $requestedRestaurantId = isset($_GET['restaurant_id']) ? (int)$_GET['restaurant_id'] : 0;
        if ($requestedRestaurantId > 0) {
            $restaurant = $this->restaurantRepo->findById($requestedRestaurantId);
        }
        if (!$restaurant) {
            $restaurant = $restaurants[0] ?? null;
        }
        
        if (!$restaurant) {
            $this->redirect('/admin');
            return;
        }

        // Gather same stats as dashboard()
        $allReservations = $this->reservationRepo->getAllForRestaurant($restaurant['id']);
        $tables = $this->restaurantRepo->getAllTablesAdmin($restaurant['id']);
        $items = $this->restaurantRepo->getAllMenuItemsAdmin($restaurant['id']);
        
        $stats = [
            ['Metric', 'Value'],
            ['Total Reservations', count($allReservations)],
            ['Confirmed', count(array_filter($allReservations, fn($r) => $r['status'] === 'confirmed'))],
            ['Pending', count(array_filter($allReservations, fn($r) => $r['status'] === 'pending'))],
            ['Seated', count(array_filter($allReservations, fn($r) => $r['status'] === 'seated'))],
            ['Completed', count(array_filter($allReservations, fn($r) => $r['status'] === 'completed'))],
            ['Cancelled', count(array_filter($allReservations, fn($r) => $r['status'] === 'cancelled'))],
            ['No Show', count(array_filter($allReservations, fn($r) => $r['status'] === 'no_show'))],
            ['Total Tables', count($tables)],
            ['Active Tables', count(array_filter($tables, fn($t) => !empty($t['is_active'])))],
            ['Total Menu Items', count($items)],
            ['Available Items', count(array_filter($items, fn($i) => !empty($i['is_available'])))],
        ];

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="stats_' . $restaurant['slug'] . '_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        foreach ($stats as $row) {
            fputcsv($output, $row, ',', '"', '\\');
        }
        fclose($output);
        exit;
    }

    public function deleteRestaurant($params)
    {
        $this->validateCsrf();

        $id = isset($params['id']) ? (int)$params['id'] : 0;

        $restaurant = $this->restaurantRepo->findById($id);
        if (!$restaurant) {
            $_SESSION['error'] = 'Restaurant not found.';
            $this->redirect('/admin/restaurants');
            return;
        }

        $this->restaurantRepo->delete($id);

        $_SESSION['success'] = 'Restaurant deleted successfully.';
        $this->redirect('/admin/restaurants');
    }

    private function getRestaurantFromParams(array $params): ?array
    {
        $restaurantId = isset($params['restaurant_id']) ? (int)$params['restaurant_id'] : 0;
        if ($restaurantId <= 0) {
            return null;
        }
        return $this->restaurantRepo->findById($restaurantId) ?: null;
    }

    private function getDefaultRestaurant(): ?array
    {
        $restaurants = $this->restaurantRepo->findAll();
        return $restaurants[0] ?? null;
    }

    // Table management
    public function tables($params = [])
    {
        $restaurant = $this->getRestaurantFromParams($params);
        if (!$restaurant) {
            $restaurant = $this->getDefaultRestaurant();
            if (!$restaurant) {
                $_SESSION['error'] = 'No restaurant found. Please create a restaurant first.';
                $this->redirect('/admin/restaurants');
                return;
            }
            $this->redirect('/admin/restaurants/' . $restaurant['id'] . '/tables');
            return;
        }

        $tables = $this->restaurantRepo->getAllTablesAdmin($restaurant['id']);
        $sections = $this->restaurantRepo->getSections($restaurant['id']);

        $this->view('admin/tables', [
            'restaurant' => $restaurant,
            'tables' => $tables,
            'sections' => $sections,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function showCreateTable($params = [])
    {
        $restaurant = $this->getRestaurantFromParams($params);
        if (!$restaurant) {
            $restaurant = $this->getDefaultRestaurant();
            if (!$restaurant) {
                $_SESSION['error'] = 'No restaurant found. Please create a restaurant first.';
                $this->redirect('/admin/restaurants');
                return;
            }
            $this->redirect('/admin/restaurants/' . $restaurant['id'] . '/tables/create');
            return;
        }

        $sections = $this->restaurantRepo->getSections($restaurant['id']);

        $this->view('admin/table_form', [
            'mode' => 'create',
            'restaurant' => $restaurant,
            'sections' => $sections,
            'table' => null,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function createTable($params = [])
    {
        $this->validateCsrf();

        $restaurant = $this->getRestaurantFromParams($params);
        if (!$restaurant) {
            $restaurant = $this->getDefaultRestaurant();
            if (!$restaurant) {
                $_SESSION['error'] = 'No restaurant found.';
                $this->redirect('/admin/restaurants');
                return;
            }
        }

        $tableNumber = trim($_POST['table_number'] ?? '');
        $capacity = (int)($_POST['capacity'] ?? 0);
        $minPartySize = max(1, (int)($_POST['min_party_size'] ?? 1));
        $maxPartySize = (int)($_POST['max_party_size'] ?? 0);
        $sectionName = trim($_POST['section_name'] ?? '');
        $seatingPreference = $_POST['seating_preference'] ?? 'indoor';
        $sortOrder = max(0, (int)($_POST['sort_order'] ?? 0));
        $notes = trim((string)($_POST['notes'] ?? ''));
        $isActive = !empty($_POST['is_active']);

        if ($tableNumber === '' || $capacity <= 0) {
            $_SESSION['error'] = 'Table number and positive capacity are required.';
            $this->redirect('/admin/restaurants/' . $restaurant['id'] . '/tables/create');
            return;
        }

        if ($minPartySize > $capacity) {
            $_SESSION['error'] = 'Minimum party size cannot be greater than capacity.';
            $this->redirect('/admin/restaurants/' . $restaurant['id'] . '/tables/create');
            return;
        }

        if ($maxPartySize > 0 && $maxPartySize < $minPartySize) {
            $_SESSION['error'] = 'Maximum party size must be greater than or equal to minimum party size.';
            $this->redirect('/admin/restaurants/' . $restaurant['id'] . '/tables/create');
            return;
        }

        $sectionId = $this->restaurantRepo->findOrCreateSection($restaurant['id'], $sectionName);

        $data = [
            'section_id' => $sectionId,
            'table_number' => $tableNumber,
            'capacity' => $capacity,
            'min_party_size' => $minPartySize,
            'max_party_size' => $maxPartySize > 0 ? $maxPartySize : null,
            'seating_preference' => $seatingPreference,
            'sort_order' => $sortOrder,
            'notes' => $notes !== '' ? $notes : null,
            'is_active' => $isActive,
        ];

        $this->restaurantRepo->createTable($restaurant['id'], $data);

        $_SESSION['success'] = 'Table created successfully.';
        $this->redirect('/admin/restaurants/' . $restaurant['id'] . '/tables');
    }

    public function showEditTable($params)
    {
        $restaurantId = isset($params['restaurant_id']) ? (int)$params['restaurant_id'] : 0;
        $id = isset($params['id']) ? (int)$params['id'] : 0;
        $table = $this->restaurantRepo->findTableById($id);

        if (!$table) {
            $_SESSION['error'] = 'Table not found.';
            $this->redirect($restaurantId > 0 ? '/admin/restaurants/' . $restaurantId . '/tables' : '/admin/restaurants');
            return;
        }

        if ($restaurantId > 0 && (int)$table['restaurant_id'] !== $restaurantId) {
            $_SESSION['error'] = 'Table does not belong to this restaurant.';
            $this->redirect('/admin/restaurants/' . $restaurantId . '/tables');
            return;
        }

        $restaurant = $this->restaurantRepo->findById((int)$table['restaurant_id']);
        if (!$restaurant) {
            $_SESSION['error'] = 'Restaurant not found for this table.';
            $this->redirect('/admin/restaurants');
            return;
        }

        if ($restaurantId <= 0) {
            $this->redirect('/admin/restaurants/' . $restaurant['id'] . '/tables/' . $id . '/edit');
            return;
        }

        $sections = $this->restaurantRepo->getSections($restaurant['id']);

        $this->view('admin/table_form', [
            'mode' => 'edit',
            'restaurant' => $restaurant,
            'sections' => $sections,
            'table' => $table,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function updateTable($params)
    {
        $this->validateCsrf();

        $restaurantId = isset($params['restaurant_id']) ? (int)$params['restaurant_id'] : 0;
        $id = isset($params['id']) ? (int)$params['id'] : 0;
        $table = $this->restaurantRepo->findTableById($id);

        if (!$table) {
            $_SESSION['error'] = 'Table not found.';
            $this->redirect($restaurantId > 0 ? '/admin/restaurants/' . $restaurantId . '/tables' : '/admin/restaurants');
            return;
        }

        if ($restaurantId > 0 && (int)$table['restaurant_id'] !== $restaurantId) {
            $_SESSION['error'] = 'Table does not belong to this restaurant.';
            $this->redirect('/admin/restaurants/' . $restaurantId . '/tables');
            return;
        }

        $restaurant = $this->restaurantRepo->findById((int)$table['restaurant_id']);
        if (!$restaurant) {
            $_SESSION['error'] = 'Restaurant not found for this table.';
            $this->redirect('/admin/restaurants');
            return;
        }

        $tableNumber = trim($_POST['table_number'] ?? '');
        $capacity = (int)($_POST['capacity'] ?? 0);
        $minPartySize = max(1, (int)($_POST['min_party_size'] ?? 1));
        $maxPartySize = (int)($_POST['max_party_size'] ?? 0);
        $sectionName = trim($_POST['section_name'] ?? '');
        $seatingPreference = $_POST['seating_preference'] ?? 'indoor';
        $sortOrder = max(0, (int)($_POST['sort_order'] ?? 0));
        $notes = trim((string)($_POST['notes'] ?? ''));
        $isActive = !empty($_POST['is_active']);

        if ($tableNumber === '' || $capacity <= 0) {
            $_SESSION['error'] = 'Table number and positive capacity are required.';
            $this->redirect('/admin/restaurants/' . $restaurant['id'] . '/tables/' . $id . '/edit');
            return;
        }

        if ($minPartySize > $capacity) {
            $_SESSION['error'] = 'Minimum party size cannot be greater than capacity.';
            $this->redirect('/admin/restaurants/' . $restaurant['id'] . '/tables/' . $id . '/edit');
            return;
        }

        if ($maxPartySize > 0 && $maxPartySize < $minPartySize) {
            $_SESSION['error'] = 'Maximum party size must be greater than or equal to minimum party size.';
            $this->redirect('/admin/restaurants/' . $restaurant['id'] . '/tables/' . $id . '/edit');
            return;
        }

        $sectionId = $this->restaurantRepo->findOrCreateSection($restaurant['id'], $sectionName);

        $data = [
            'section_id' => $sectionId,
            'table_number' => $tableNumber,
            'capacity' => $capacity,
            'min_party_size' => $minPartySize,
            'max_party_size' => $maxPartySize > 0 ? $maxPartySize : null,
            'seating_preference' => $seatingPreference,
            'sort_order' => $sortOrder,
            'notes' => $notes !== '' ? $notes : null,
            'is_active' => $isActive,
        ];

        $this->restaurantRepo->updateTable($id, $data);

        $_SESSION['success'] = 'Table updated successfully.';
        $this->redirect('/admin/restaurants/' . $restaurant['id'] . '/tables');
    }

    public function deleteTable($params)
    {
        $this->validateCsrf();

        $restaurantId = isset($params['restaurant_id']) ? (int)$params['restaurant_id'] : 0;
        $id = isset($params['id']) ? (int)$params['id'] : 0;
        $table = $this->restaurantRepo->findTableById($id);

        if (!$table) {
            $_SESSION['error'] = 'Table not found.';
            $this->redirect($restaurantId > 0 ? '/admin/restaurants/' . $restaurantId . '/tables' : '/admin/restaurants');
            return;
        }

        if ($restaurantId > 0 && (int)$table['restaurant_id'] !== $restaurantId) {
            $_SESSION['error'] = 'Table does not belong to this restaurant.';
            $this->redirect('/admin/restaurants/' . $restaurantId . '/tables');
            return;
        }

        $this->restaurantRepo->deleteTable($id);

        $_SESSION['success'] = 'Table deleted successfully.';
        $this->redirect('/admin/restaurants/' . (int)$table['restaurant_id'] . '/tables');
    }

    // Menu management
    public function menuCategories($params = [])
    {
        $restaurant = $this->getRestaurantFromParams($params);
        if (!$restaurant) {
            $restaurant = $this->getDefaultRestaurant();
            if (!$restaurant) {
                $_SESSION['error'] = 'No restaurant found. Please create a restaurant first.';
                $this->redirect('/admin/restaurants');
                return;
            }
            $this->redirect('/admin/restaurants/' . $restaurant['id'] . '/menu/categories');
            return;
        }

        $categories = $this->restaurantRepo->getMenuCategories($restaurant['id']);

        $this->view('admin/menu_categories', [
            'restaurant' => $restaurant,
            'categories' => $categories,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function showCreateMenuCategory($params = [])
    {
        $restaurant = $this->getRestaurantFromParams($params);
        if (!$restaurant) {
            $restaurant = $this->getDefaultRestaurant();
            if (!$restaurant) {
                $_SESSION['error'] = 'No restaurant found.';
                $this->redirect('/admin/restaurants');
                return;
            }
            $this->redirect('/admin/restaurants/' . $restaurant['id'] . '/menu/categories/create');
            return;
        }

        $this->view('admin/menu_category_form', [
            'mode' => 'create',
            'restaurant' => $restaurant,
            'category' => null,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function createMenuCategory($params = [])
    {
        $this->validateCsrf();

        $restaurant = $this->getRestaurantFromParams($params);
        if (!$restaurant) {
            $restaurant = $this->getDefaultRestaurant();
            if (!$restaurant) {
                $_SESSION['error'] = 'No restaurant found.';
                $this->redirect('/admin/restaurants');
                return;
            }
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim((string)($_POST['description'] ?? ''));
        $imageUrl = trim((string)($_POST['image_url'] ?? ''));
        $isActive = !empty($_POST['is_active']);
        $displayOrder = max(0, (int)($_POST['display_order'] ?? 0));

        if ($name === '') {
            $_SESSION['error'] = 'Category name is required.';
            $this->redirect('/admin/restaurants/' . $restaurant['id'] . '/menu/categories/create');
            return;
        }

        if (strlen($name) > 50) {
            $_SESSION['error'] = 'Category name must be 50 characters or fewer.';
            $this->redirect('/admin/restaurants/' . $restaurant['id'] . '/menu/categories/create');
            return;
        }

        if ($imageUrl !== '' && filter_var($imageUrl, FILTER_VALIDATE_URL) === false) {
            $_SESSION['error'] = 'Category image URL must be a valid URL.';
            $this->redirect('/admin/restaurants/' . $restaurant['id'] . '/menu/categories/create');
            return;
        }

        $data = [
            'name' => $name,
            'description' => $description !== '' ? $description : null,
            'image_url' => $imageUrl !== '' ? $imageUrl : null,
            'is_active' => $isActive,
            'display_order' => $displayOrder,
        ];

        $this->restaurantRepo->createCategory($restaurant['id'], $data);

        $_SESSION['success'] = 'Menu category created.';
        $this->redirect('/admin/restaurants/' . $restaurant['id'] . '/menu/categories');
    }

    public function showEditMenuCategory($params)
    {
        $restaurantId = isset($params['restaurant_id']) ? (int)$params['restaurant_id'] : 0;
        $id = isset($params['id']) ? (int)$params['id'] : 0;
        $category = $this->restaurantRepo->findCategoryById($id);

        if (!$category) {
            $_SESSION['error'] = 'Category not found.';
            $this->redirect($restaurantId > 0 ? '/admin/restaurants/' . $restaurantId . '/menu/categories' : '/admin/restaurants');
            return;
        }

        if ($restaurantId > 0 && (int)$category['restaurant_id'] !== $restaurantId) {
            $_SESSION['error'] = 'Category does not belong to this restaurant.';
            $this->redirect('/admin/restaurants/' . $restaurantId . '/menu/categories');
            return;
        }

        $restaurant = $this->restaurantRepo->findById((int)$category['restaurant_id']);
        if (!$restaurant) {
            $_SESSION['error'] = 'Restaurant not found.';
            $this->redirect('/admin/restaurants');
            return;
        }

        if ($restaurantId <= 0) {
            $this->redirect('/admin/restaurants/' . $restaurant['id'] . '/menu/categories/' . $id . '/edit');
            return;
        }

        $this->view('admin/menu_category_form', [
            'mode' => 'edit',
            'restaurant' => $restaurant,
            'category' => $category,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function updateMenuCategory($params)
    {
        $this->validateCsrf();

        $restaurantId = isset($params['restaurant_id']) ? (int)$params['restaurant_id'] : 0;
        $id = isset($params['id']) ? (int)$params['id'] : 0;
        $category = $this->restaurantRepo->findCategoryById($id);

        if (!$category) {
            $_SESSION['error'] = 'Category not found.';
            $this->redirect($restaurantId > 0 ? '/admin/restaurants/' . $restaurantId . '/menu/categories' : '/admin/restaurants');
            return;
        }

        if ($restaurantId > 0 && (int)$category['restaurant_id'] !== $restaurantId) {
            $_SESSION['error'] = 'Category does not belong to this restaurant.';
            $this->redirect('/admin/restaurants/' . $restaurantId . '/menu/categories');
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim((string)($_POST['description'] ?? ''));
        $imageUrl = trim((string)($_POST['image_url'] ?? ''));
        $isActive = !empty($_POST['is_active']);
        $displayOrder = max(0, (int)($_POST['display_order'] ?? 0));

        if ($name === '') {
            $_SESSION['error'] = 'Category name is required.';
            $this->redirect('/admin/restaurants/' . (int)$category['restaurant_id'] . '/menu/categories/' . $id . '/edit');
            return;
        }

        if (strlen($name) > 50) {
            $_SESSION['error'] = 'Category name must be 50 characters or fewer.';
            $this->redirect('/admin/restaurants/' . (int)$category['restaurant_id'] . '/menu/categories/' . $id . '/edit');
            return;
        }

        if ($imageUrl !== '' && filter_var($imageUrl, FILTER_VALIDATE_URL) === false) {
            $_SESSION['error'] = 'Category image URL must be a valid URL.';
            $this->redirect('/admin/restaurants/' . (int)$category['restaurant_id'] . '/menu/categories/' . $id . '/edit');
            return;
        }

        $data = [
            'name' => $name,
            'description' => $description !== '' ? $description : null,
            'image_url' => $imageUrl !== '' ? $imageUrl : null,
            'is_active' => $isActive,
            'display_order' => $displayOrder,
        ];

        $this->restaurantRepo->updateCategory($id, $data);

        $_SESSION['success'] = 'Menu category updated.';
        $this->redirect('/admin/restaurants/' . (int)$category['restaurant_id'] . '/menu/categories');
    }

    public function deleteMenuCategory($params)
    {
        $this->validateCsrf();

        $restaurantId = isset($params['restaurant_id']) ? (int)$params['restaurant_id'] : 0;
        $id = isset($params['id']) ? (int)$params['id'] : 0;
        $category = $this->restaurantRepo->findCategoryById($id);

        if (!$category) {
            $_SESSION['error'] = 'Category not found.';
            $this->redirect($restaurantId > 0 ? '/admin/restaurants/' . $restaurantId . '/menu/categories' : '/admin/restaurants');
            return;
        }

        if ($restaurantId > 0 && (int)$category['restaurant_id'] !== $restaurantId) {
            $_SESSION['error'] = 'Category does not belong to this restaurant.';
            $this->redirect('/admin/restaurants/' . $restaurantId . '/menu/categories');
            return;
        }

        $this->restaurantRepo->deleteCategory($id);

        $_SESSION['success'] = 'Menu category deleted.';
        $this->redirect('/admin/restaurants/' . (int)$category['restaurant_id'] . '/menu/categories');
    }

    public function menuItems($params = [])
    {
        $restaurant = $this->getRestaurantFromParams($params);
        if (!$restaurant) {
            $restaurant = $this->getDefaultRestaurant();
            if (!$restaurant) {
                $_SESSION['error'] = 'No restaurant found. Please create a restaurant first.';
                $this->redirect('/admin/restaurants');
                return;
            }
            $this->redirect('/admin/restaurants/' . $restaurant['id'] . '/menu/items');
            return;
        }

        $categories = $this->restaurantRepo->getMenuCategories($restaurant['id']);
        $items = $this->restaurantRepo->getAllMenuItemsAdmin($restaurant['id']);

        $this->view('admin/menu_items', [
            'restaurant' => $restaurant,
            'categories' => $categories,
            'items' => $items,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function showCreateMenuItem($params = [])
    {
        $restaurant = $this->getRestaurantFromParams($params);
        if (!$restaurant) {
            $restaurant = $this->getDefaultRestaurant();
            if (!$restaurant) {
                $_SESSION['error'] = 'No restaurant found.';
                $this->redirect('/admin/restaurants');
                return;
            }
            $this->redirect('/admin/restaurants/' . $restaurant['id'] . '/menu/items/create');
            return;
        }

        $categories = $this->restaurantRepo->getMenuCategories($restaurant['id']);

        $this->view('admin/menu_item_form', [
            'mode' => 'create',
            'restaurant' => $restaurant,
            'categories' => $categories,
            'item' => null,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function createMenuItem($params = [])
    {
        $this->validateCsrf();

        $restaurant = $this->getRestaurantFromParams($params);
        if (!$restaurant) {
            $restaurant = $this->getDefaultRestaurant();
            if (!$restaurant) {
                $_SESSION['error'] = 'No restaurant found.';
                $this->redirect('/admin/restaurants');
                return;
            }
        }

        $name = trim($_POST['name'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $sku = strtoupper(trim((string)($_POST['sku'] ?? '')));
        $description = trim((string)($_POST['description'] ?? ''));
        $imageUrl = trim((string)($_POST['image_url'] ?? ''));
        $displayOrder = max(0, (int)($_POST['display_order'] ?? 0));
        $prepTimeMinutes = max(1, (int)($_POST['prep_time_minutes'] ?? 15));
        $calories = (int)($_POST['calories'] ?? 0);
        $spiceLevel = $_POST['spice_level'] ?? 'none';
        $validSpiceLevels = ['none', 'mild', 'medium', 'hot'];
        if (!in_array($spiceLevel, $validSpiceLevels, true)) {
            $spiceLevel = 'none';
        }
        $isVegetarian = !empty($_POST['is_vegetarian']);
        $isVegan = !empty($_POST['is_vegan']);
        $isGlutenFree = !empty($_POST['is_gluten_free']);
        $isAvailable = !empty($_POST['is_available']);

        if ($name === '' || $price <= 0) {
            $_SESSION['error'] = 'Name and positive price are required.';
            $this->redirect('/admin/restaurants/' . $restaurant['id'] . '/menu/items/create');
            return;
        }

        if (strlen($name) > 100) {
            $_SESSION['error'] = 'Item name must be 100 characters or fewer.';
            $this->redirect('/admin/restaurants/' . $restaurant['id'] . '/menu/items/create');
            return;
        }

        if ($sku !== '' && !preg_match('/^[A-Z0-9_-]{2,50}$/', $sku)) {
            $_SESSION['error'] = 'SKU must be 2-50 chars and use only A-Z, 0-9, dash, underscore.';
            $this->redirect('/admin/restaurants/' . $restaurant['id'] . '/menu/items/create');
            return;
        }

        if ($imageUrl !== '' && filter_var($imageUrl, FILTER_VALIDATE_URL) === false) {
            $_SESSION['error'] = 'Image URL must be a valid URL.';
            $this->redirect('/admin/restaurants/' . $restaurant['id'] . '/menu/items/create');
            return;
        }

        if ($categoryId > 0) {
            $category = $this->restaurantRepo->findCategoryById($categoryId);
            if (!$category || (int)$category['restaurant_id'] !== (int)$restaurant['id']) {
                $_SESSION['error'] = 'Selected category does not belong to this restaurant.';
                $this->redirect('/admin/restaurants/' . $restaurant['id'] . '/menu/items/create');
                return;
            }
        }

        $data = [
            'category_id' => $categoryId ?: null,
            'sku' => $sku !== '' ? $sku : null,
            'name' => $name,
            'description' => $description !== '' ? $description : null,
            'price' => $price,
            'image_url' => $imageUrl !== '' ? $imageUrl : null,
            'prep_time_minutes' => $prepTimeMinutes,
            'calories' => $calories > 0 ? $calories : null,
            'spice_level' => $spiceLevel,
            'is_vegetarian' => $isVegetarian,
            'is_vegan' => $isVegan,
            'is_gluten_free' => $isGlutenFree,
            'is_available' => $isAvailable,
            'display_order' => $displayOrder,
        ];

        $this->restaurantRepo->createMenuItem($restaurant['id'], $data);

        $_SESSION['success'] = 'Menu item created.';
        $this->redirect('/admin/restaurants/' . $restaurant['id'] . '/menu/items');
    }

    public function showEditMenuItem($params)
    {
        $restaurantId = isset($params['restaurant_id']) ? (int)$params['restaurant_id'] : 0;
        $id = isset($params['id']) ? (int)$params['id'] : 0;
        $item = $this->restaurantRepo->findMenuItemById($id);

        if (!$item) {
            $_SESSION['error'] = 'Menu item not found.';
            $this->redirect($restaurantId > 0 ? '/admin/restaurants/' . $restaurantId . '/menu/items' : '/admin/restaurants');
            return;
        }

        if ($restaurantId > 0 && (int)$item['restaurant_id'] !== $restaurantId) {
            $_SESSION['error'] = 'Menu item does not belong to this restaurant.';
            $this->redirect('/admin/restaurants/' . $restaurantId . '/menu/items');
            return;
        }

        $restaurant = $this->restaurantRepo->findById((int)$item['restaurant_id']);
        if (!$restaurant) {
            $_SESSION['error'] = 'Restaurant not found.';
            $this->redirect('/admin/restaurants');
            return;
        }

        if ($restaurantId <= 0) {
            $this->redirect('/admin/restaurants/' . $restaurant['id'] . '/menu/items/' . $id . '/edit');
            return;
        }

        $categories = $this->restaurantRepo->getMenuCategories($restaurant['id']);

        $this->view('admin/menu_item_form', [
            'mode' => 'edit',
            'restaurant' => $restaurant,
            'categories' => $categories,
            'item' => $item,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function updateMenuItem($params)
    {
        $this->validateCsrf();

        $restaurantId = isset($params['restaurant_id']) ? (int)$params['restaurant_id'] : 0;
        $id = isset($params['id']) ? (int)$params['id'] : 0;
        $item = $this->restaurantRepo->findMenuItemById($id);

        if (!$item) {
            $_SESSION['error'] = 'Menu item not found.';
            $this->redirect($restaurantId > 0 ? '/admin/restaurants/' . $restaurantId . '/menu/items' : '/admin/restaurants');
            return;
        }

        if ($restaurantId > 0 && (int)$item['restaurant_id'] !== $restaurantId) {
            $_SESSION['error'] = 'Menu item does not belong to this restaurant.';
            $this->redirect('/admin/restaurants/' . $restaurantId . '/menu/items');
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $sku = strtoupper(trim((string)($_POST['sku'] ?? '')));
        $description = trim((string)($_POST['description'] ?? ''));
        $imageUrl = trim((string)($_POST['image_url'] ?? ''));
        $displayOrder = max(0, (int)($_POST['display_order'] ?? 0));
        $prepTimeMinutes = max(1, (int)($_POST['prep_time_minutes'] ?? 15));
        $calories = (int)($_POST['calories'] ?? 0);
        $spiceLevel = $_POST['spice_level'] ?? 'none';
        $validSpiceLevels = ['none', 'mild', 'medium', 'hot'];
        if (!in_array($spiceLevel, $validSpiceLevels, true)) {
            $spiceLevel = 'none';
        }
        $isVegetarian = !empty($_POST['is_vegetarian']);
        $isVegan = !empty($_POST['is_vegan']);
        $isGlutenFree = !empty($_POST['is_gluten_free']);
        $isAvailable = !empty($_POST['is_available']);

        if ($name === '' || $price <= 0) {
            $_SESSION['error'] = 'Name and positive price are required.';
            $this->redirect('/admin/restaurants/' . (int)$item['restaurant_id'] . '/menu/items/' . $id . '/edit');
            return;
        }

        if (strlen($name) > 100) {
            $_SESSION['error'] = 'Item name must be 100 characters or fewer.';
            $this->redirect('/admin/restaurants/' . (int)$item['restaurant_id'] . '/menu/items/' . $id . '/edit');
            return;
        }

        if ($sku !== '' && !preg_match('/^[A-Z0-9_-]{2,50}$/', $sku)) {
            $_SESSION['error'] = 'SKU must be 2-50 chars and use only A-Z, 0-9, dash, underscore.';
            $this->redirect('/admin/restaurants/' . (int)$item['restaurant_id'] . '/menu/items/' . $id . '/edit');
            return;
        }

        if ($imageUrl !== '' && filter_var($imageUrl, FILTER_VALIDATE_URL) === false) {
            $_SESSION['error'] = 'Image URL must be a valid URL.';
            $this->redirect('/admin/restaurants/' . (int)$item['restaurant_id'] . '/menu/items/' . $id . '/edit');
            return;
        }

        if ($categoryId > 0) {
            $category = $this->restaurantRepo->findCategoryById($categoryId);
            if (!$category || (int)$category['restaurant_id'] !== (int)$item['restaurant_id']) {
                $_SESSION['error'] = 'Selected category does not belong to this restaurant.';
                $this->redirect('/admin/restaurants/' . (int)$item['restaurant_id'] . '/menu/items/' . $id . '/edit');
                return;
            }
        }

        $data = [
            'category_id' => $categoryId ?: null,
            'sku' => $sku !== '' ? $sku : null,
            'name' => $name,
            'description' => $description !== '' ? $description : null,
            'price' => $price,
            'image_url' => $imageUrl !== '' ? $imageUrl : null,
            'prep_time_minutes' => $prepTimeMinutes,
            'calories' => $calories > 0 ? $calories : null,
            'spice_level' => $spiceLevel,
            'is_vegetarian' => $isVegetarian,
            'is_vegan' => $isVegan,
            'is_gluten_free' => $isGlutenFree,
            'is_available' => $isAvailable,
            'display_order' => $displayOrder,
        ];

        $this->restaurantRepo->updateMenuItem($id, $data);

        $_SESSION['success'] = 'Menu item updated.';
        $this->redirect('/admin/restaurants/' . (int)$item['restaurant_id'] . '/menu/items');
    }

    public function deleteMenuItem($params)
    {
        $this->validateCsrf();

        $restaurantId = isset($params['restaurant_id']) ? (int)$params['restaurant_id'] : 0;
        $id = isset($params['id']) ? (int)$params['id'] : 0;
        $item = $this->restaurantRepo->findMenuItemById($id);

        if (!$item) {
            $_SESSION['error'] = 'Menu item not found.';
            $this->redirect($restaurantId > 0 ? '/admin/restaurants/' . $restaurantId . '/menu/items' : '/admin/restaurants');
            return;
        }

        if ($restaurantId > 0 && (int)$item['restaurant_id'] !== $restaurantId) {
            $_SESSION['error'] = 'Menu item does not belong to this restaurant.';
            $this->redirect('/admin/restaurants/' . $restaurantId . '/menu/items');
            return;
        }

        $this->restaurantRepo->deleteMenuItem($id);

        $_SESSION['success'] = 'Menu item deleted.';
        $this->redirect('/admin/restaurants/' . (int)$item['restaurant_id'] . '/menu/items');
    }

    public function restaurantHours($params = [])
    {
        $restaurant = $this->getRestaurantFromParams($params);
        if (!$restaurant) {
            $restaurant = $this->getDefaultRestaurant();
            if (!$restaurant) {
                $_SESSION['error'] = 'No restaurant found. Please create a restaurant first.';
                $this->redirect('/admin/restaurants');
                return;
            }
            $this->redirect('/admin/restaurants/' . $restaurant['id'] . '/hours');
            return;
        }

        $this->restaurantRepo->ensureDefaultHours((int)$restaurant['id']);
        $hoursRows = $this->restaurantRepo->getHours((int)$restaurant['id']);
        $hoursByDay = [];
        foreach ($hoursRows as $row) {
            $hoursByDay[(int)$row['day_of_week']] = $row;
        }

        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $hours = [];
        foreach ($days as $dayIndex => $dayName) {
            $row = $hoursByDay[$dayIndex] ?? null;
            $hours[] = [
                'day_of_week' => $dayIndex,
                'day_name' => $dayName,
                'open_time' => isset($row['open_time']) ? substr((string)$row['open_time'], 0, 5) : '11:00',
                'close_time' => isset($row['close_time']) ? substr((string)$row['close_time'], 0, 5) : '22:00',
                'is_closed' => !empty($row['is_closed']),
            ];
        }

        $this->view('admin/restaurant_hours', [
            'restaurant' => $restaurant,
            'hours' => $hours,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    public function updateRestaurantHours($params = [])
    {
        $this->validateCsrf();

        $restaurant = $this->getRestaurantFromParams($params);
        if (!$restaurant) {
            $_SESSION['error'] = 'Restaurant not found.';
            $this->redirect('/admin/restaurants');
            return;
        }

        $hoursPayload = [];
        for ($day = 0; $day <= 6; $day++) {
            $isClosed = isset($_POST['is_closed'][$day]);
            $openTime = trim((string)($_POST['open_time'][$day] ?? ''));
            $closeTime = trim((string)($_POST['close_time'][$day] ?? ''));

            if (!$isClosed) {
                if (!preg_match('/^\d{2}:\d{2}$/', $openTime) || !preg_match('/^\d{2}:\d{2}$/', $closeTime)) {
                    $_SESSION['error'] = 'Open/close time is required for every open day.';
                    $this->redirect('/admin/restaurants/' . $restaurant['id'] . '/hours');
                    return;
                }
                if ($openTime >= $closeTime) {
                    $_SESSION['error'] = 'Close time must be after open time for each open day.';
                    $this->redirect('/admin/restaurants/' . $restaurant['id'] . '/hours');
                    return;
                }
            }

            $hoursPayload[] = [
                'day_of_week' => $day,
                'open_time' => $isClosed ? null : ($openTime . ':00'),
                'close_time' => $isClosed ? null : ($closeTime . ':00'),
                'is_closed' => $isClosed,
            ];
        }

        $this->restaurantRepo->replaceHours((int)$restaurant['id'], $hoursPayload);

        $_SESSION['success'] = 'Restaurant hours updated successfully.';
        $this->redirect('/admin/restaurants/' . $restaurant['id'] . '/hours');
    }
}
