<?php
/**
 * Tour availability functions - Handle tour availability calendar
 */

require_once __DIR__ . '/helper_function.php';

/**
 * Check tour availability for a date
 */
function check_tour_availability($tourId, $date, $numberOfGuests = 1) {
    $db = get_db();
    
    $stmt = $db->prepare("
        SELECT * FROM tour_availability 
        WHERE tour_id = :tour_id 
        AND available_date = :date
    ");
    $stmt->execute([
        ':tour_id' => $tourId,
        ':date' => $date
    ]);
    
    $availability = $stmt->fetch();
    
    if (!$availability) {
        // No specific availability record, check tour max_guests
        require_once __DIR__ . '/tour_function.php';
        $tour = get_tour_by_id($tourId);
        
        if (!$tour) {
            return [
                'available' => false,
                'message' => 'Tour not found',
                'available_slots' => 0
            ];
        }
        
        // Check if date is in the past
        if (strtotime($date) < strtotime('today')) {
            return [
                'available' => false,
                'message' => 'Date is in the past',
                'available_slots' => 0
            ];
        }
        
        // Default: available with tour max_guests
        return [
            'available' => true,
            'message' => 'Available',
            'available_slots' => $tour['max_guests'],
            'price' => $tour['price']
        ];
    }
    
    // Check status
    if ($availability['status'] === 'unavailable' || $availability['status'] === 'sold_out') {
        return [
            'available' => false,
            'message' => ucfirst(str_replace('_', ' ', $availability['status'])),
            'available_slots' => 0
        ];
    }
    
    // Check available slots
    $availableSlots = $availability['available_slots'] - $availability['booked_slots'];
    
    if ($availableSlots < $numberOfGuests) {
        return [
            'available' => false,
            'message' => 'Not enough slots available',
            'available_slots' => max(0, $availableSlots)
        ];
    }
    
    // Use price override if available
    $price = $availability['price_override'] !== null ? $availability['price_override'] : null;
    
    return [
        'available' => true,
        'message' => 'Available',
        'available_slots' => $availableSlots,
        'price' => $price
    ];
}

/**
 * Get availability calendar for a tour
 */
function get_tour_availability_calendar($tourId, $startDate = null, $endDate = null) {
    try {
        $db = get_db();
        if (!$db) {
            error_log("Database connection failed for tour availability: $tourId");
            return ['error' => 'Database connection failed'];
        }
        
        if ($startDate === null) {
            $startDate = date('Y-m-d');
        }
        
        if ($endDate === null) {
            $endDate = date('Y-m-d', strtotime('+3 months'));
        }
        
        $stmt = $db->prepare("
            SELECT * FROM tour_availability 
            WHERE tour_id = :tour_id 
            AND available_date >= :start_date 
            AND available_date <= :end_date
            ORDER BY available_date ASC
        ");
        
        if (!$stmt) {
            $errorInfo = $db->errorInfo();
            error_log("Database query preparation failed for tour $tourId: " . print_r($errorInfo, true));
            return ['error' => 'Failed to prepare database query', 'details' => DEBUG ? $errorInfo[2] ?? 'Unknown error' : ''];
        }
        
        $result = $stmt->execute([
            ':tour_id' => $tourId,
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);
        
        if (!$result) {
            $errorInfo = $stmt->errorInfo();
            error_log("Database query execution failed for tour $tourId: " . print_r($errorInfo, true));
            return ['error' => 'Failed to execute database query', 'details' => DEBUG ? $errorInfo[2] ?? 'Unknown error' : ''];
        }
        
        $availabilities = $stmt->fetchAll();
        
        // Get tour default info
        require_once __DIR__ . '/tour_function.php';
        $tour = get_tour_by_id($tourId);
        
        if (!$tour) {
            error_log("Tour not found: $tourId");
            return ['error' => 'Tour not found'];
        }
        
        // Build calendar array
        $calendar = [];
        $currentDate = strtotime($startDate);
        $endTimestamp = strtotime($endDate);
        $todayTimestamp = strtotime('today');
        
        if ($currentDate === false || $endTimestamp === false) {
            return ['error' => 'Invalid date range'];
        }
        
        // Create a map of availabilities for faster lookup
        $availabilityMap = [];
        foreach ($availabilities as $avail) {
            $availabilityMap[$avail['available_date']] = $avail;
        }
        
        // Limit processing to prevent timeout (max 180 days)
        $maxDays = 180;
        $daysDiff = ($endTimestamp - $currentDate) / (60 * 60 * 24);
        if ($daysDiff > $maxDays) {
            $endTimestamp = strtotime("+$maxDays days", $currentDate);
        }
        
        $dayCount = 0;
        while ($currentDate <= $endTimestamp && $dayCount < $maxDays) {
            $dateStr = date('Y-m-d', $currentDate);
            
            // Check if we have availability record for this date
            if (isset($availabilityMap[$dateStr])) {
                $availability = $availabilityMap[$dateStr];
                $availableSlots = ($availability['available_slots'] ?? 0) - ($availability['booked_slots'] ?? 0);
                $calendar[$dateStr] = [
                    'date' => $dateStr,
                    'available_slots' => max(0, $availableSlots),
                    'status' => $availability['status'] ?? 'available',
                    'price' => $availability['price_override'] !== null ? $availability['price_override'] : ($tour['price'] ?? 0),
                    'notes' => $availability['notes'] ?? null
                ];
            } else {
                // Default availability
                $isPast = $currentDate < $todayTimestamp;
                $calendar[$dateStr] = [
                    'date' => $dateStr,
                    'available_slots' => $isPast ? 0 : ($tour['max_guests'] ?? 0),
                    'status' => $isPast ? 'unavailable' : 'available',
                    'price' => $tour['price'] ?? 0,
                    'notes' => null
                ];
            }
            
            $currentDate = strtotime('+1 day', $currentDate);
            $dayCount++;
            
            if ($currentDate === false) {
                break; // Prevent infinite loop
            }
        }
        
        return $calendar;
    } catch (Exception $e) {
        error_log('Availability calendar error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        return ['error' => 'Failed to load availability data'];
    } catch (Throwable $e) {
        error_log('Availability calendar fatal error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        return ['error' => 'Failed to load availability data'];
    }
}

/**
 * Update tour availability after booking
 */
function update_tour_availability($tourId, $date, $numberOfGuests, $action = 'book') {
    $db = get_db();
    
    // Check if record exists
    $stmt = $db->prepare("
        SELECT * FROM tour_availability 
        WHERE tour_id = :tour_id 
        AND available_date = :date
    ");
    $stmt->execute([
        ':tour_id' => $tourId,
        ':date' => $date
    ]);
    
    $availability = $stmt->fetch();
    
    if ($availability) {
        // Update existing record
        if ($action === 'book') {
            $newBookedSlots = $availability['booked_slots'] + $numberOfGuests;
            $newStatus = ($newBookedSlots >= $availability['available_slots']) ? 'sold_out' : 'available';
            
            $stmt = $db->prepare("
                UPDATE tour_availability 
                SET booked_slots = :booked_slots,
                    status = :status,
                    updated_at = NOW()
                WHERE id = :id
            ");
            $stmt->execute([
                ':booked_slots' => $newBookedSlots,
                ':status' => $newStatus,
                ':id' => $availability['id']
            ]);
        } elseif ($action === 'cancel') {
            $newBookedSlots = max(0, $availability['booked_slots'] - $numberOfGuests);
            $newStatus = ($newBookedSlots >= $availability['available_slots']) ? 'sold_out' : 'available';
            
            $stmt = $db->prepare("
                UPDATE tour_availability 
                SET booked_slots = :booked_slots,
                    status = :status,
                    updated_at = NOW()
                WHERE id = :id
            ");
            $stmt->execute([
                ':booked_slots' => $newBookedSlots,
                ':status' => $newStatus,
                ':id' => $availability['id']
            ]);
        }
    } else {
        // Create new record (for tracking)
        require_once __DIR__ . '/tour_function.php';
        $tour = get_tour_by_id($tourId);
        
        if ($tour) {
            $id = generate_uuid();
            $availableSlots = $tour['max_guests'];
            $bookedSlots = ($action === 'book') ? $numberOfGuests : 0;
            $status = ($bookedSlots >= $availableSlots) ? 'sold_out' : 'available';
            
            $stmt = $db->prepare("
                INSERT INTO tour_availability (id, tour_id, available_date, available_slots, booked_slots, status, created_at, updated_at)
                VALUES (:id, :tour_id, :date, :available_slots, :booked_slots, :status, NOW(), NOW())
            ");
            $stmt->execute([
                ':id' => $id,
                ':tour_id' => $tourId,
                ':date' => $date,
                ':available_slots' => $availableSlots,
                ':booked_slots' => $bookedSlots,
                ':status' => $status
            ]);
        }
    }
}

