<?php
namespace App\Services;

use App\Repositories\ReservationRepository;
use App\Repositories\RestaurantRepository;
use Exception;

class ReservationService
{
    private $reservationRepo;
    private $restaurantRepo;
    private $config;
    
    public function __construct()
    {
        $this->reservationRepo = new ReservationRepository();
        $this->restaurantRepo = new RestaurantRepository();
        $this->config = require CONFIG_PATH . '/config.php';
    }
    
    public function checkAvailability($restaurantId, $date, $time, $partySize)
    {
        // Validate restaurant exists
        $restaurant = $this->restaurantRepo->findById($restaurantId);
        if (!$restaurant) {
            throw new Exception('Restaurant not found');
        }
        
        // Validate date is not in the past
        if (strtotime($date) < strtotime('today')) {
            throw new Exception('Cannot book in the past');
        }

        $this->validateWithinRestaurantHours((int)$restaurantId, (string)$date, (string)$time);
        
        // Get available tables
        $duration = $this->config['app']['reservation_duration'];
        $buffer = $this->config['app']['reservation_buffer'];
        
        $availableTables = $this->reservationRepo->checkAvailability(
            $restaurantId,
            $date,
            $time,
            $duration,
            $buffer
        );
        
        // Filter by capacity
        $suitableTables = array_filter($availableTables, function($table) use ($partySize) {
            return $table['capacity'] >= $partySize;
        });
        
        return array_values($suitableTables);
    }
    
    public function createReservation($data)
    {
        // Validate availability
        $availableTables = $this->checkAvailability(
            $data['restaurant_id'],
            $data['reservation_date'],
            $data['reservation_time'],
            $data['party_size']
        );
        
        // Check if selected table is available
        $tableAvailable = false;
        foreach ($availableTables as $table) {
            if ($table['id'] == $data['table_id']) {
                $tableAvailable = true;
                break;
            }
        }
        
        if (!$tableAvailable) {
            throw new Exception('Selected table is not available for this time slot');
        }
        
        // Generate reservation code
        $data['reservation_code'] = $this->reservationRepo->generateReservationCode();
        
        // Set status
        $data['status'] = 'pending';
        
        // Create reservation
        $reservationId = $this->reservationRepo->create($data);
        
        return $this->reservationRepo->findById($reservationId);
    }
    
    public function canCancel($reservationId)
    {
        $reservation = $this->reservationRepo->findById($reservationId);
        if (!$reservation) {
            return false;
        }
        
        // Check if already cancelled or completed
        if (in_array($reservation['status'], ['cancelled', 'completed', 'no_show'])) {
            return false;
        }
        
        // Check cancellation cutoff
        $cutoffHours = $this->config['app']['cancellation_cutoff'];
        $reservationDateTime = strtotime($reservation['reservation_date'] . ' ' . $reservation['reservation_time']);
        $cutoffTime = $reservationDateTime - ($cutoffHours * 3600);
        
        return time() < $cutoffTime;
    }
    
    public function cancelReservation($reservationId)
    {
        if (!$this->canCancel($reservationId)) {
            throw new Exception('Cannot cancel this reservation');
        }
        
        return $this->reservationRepo->cancel($reservationId);
    }

    private function validateWithinRestaurantHours(int $restaurantId, string $date, string $time): void
    {
        $hours = $this->restaurantRepo->getHours($restaurantId);
        if (empty($hours)) {
            throw new Exception('Restaurant hours are not configured yet.');
        }

        $dayOfWeek = (int)date('w', strtotime($date));
        $dayHours = null;
        foreach ($hours as $row) {
            if ((int)$row['day_of_week'] === $dayOfWeek) {
                $dayHours = $row;
                break;
            }
        }

        if (!$dayHours) {
            throw new Exception('Restaurant is not available for the selected date.');
        }

        if (!empty($dayHours['is_closed'])) {
            throw new Exception('Restaurant is closed on the selected day.');
        }

        $openTime = (string)($dayHours['open_time'] ?? '');
        $closeTime = (string)($dayHours['close_time'] ?? '');
        if ($openTime === '' || $closeTime === '') {
            throw new Exception('Restaurant hours are incomplete for the selected day.');
        }

        $reservationStart = strtotime($date . ' ' . $time);
        $openDateTime = strtotime($date . ' ' . $openTime);
        $closeDateTime = strtotime($date . ' ' . $closeTime);

        if ($reservationStart < $openDateTime || $reservationStart >= $closeDateTime) {
            throw new Exception('Selected time is outside restaurant operating hours.');
        }

        $durationMinutes = (int)($this->config['app']['reservation_duration'] ?? 90);
        $reservationEnd = $reservationStart + ($durationMinutes * 60);
        if ($reservationEnd > $closeDateTime) {
            throw new Exception('Reservation end time exceeds restaurant closing time.');
        }
    }
}
