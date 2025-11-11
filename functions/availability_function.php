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
    $db = get_db();
    
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
    $stmt->execute([
        ':tour_id' => $tourId,
        ':start_date' => $startDate,
        ':end_date' => $endDate
    ]);
    
    $availabilities = $stmt->fetchAll();
    
    // Get tour default info
    require_once __DIR__ . '/tour_function.php';
    $tour = get_tour_by_id($tourId);
    
    // Build calendar array
    $calendar = [];
    $currentDate = strtotime($startDate);
    $endTimestamp = strtotime($endDate);
    
    while ($currentDate <= $endTimestamp) {
        $dateStr = date('Y-m-d', $currentDate);
        
        // Find matching availability record
        $availability = null;
        foreach ($availabilities as $avail) {
            if ($avail['available_date'] === $dateStr) {
                $availability = $avail;
                break;
            }
        }
        
        if ($availability) {
            $availableSlots = $availability['available_slots'] - $availability['booked_slots'];
            $calendar[$dateStr] = [
                'date' => $dateStr,
                'available_slots' => max(0, $availableSlots),
                'status' => $availability['status'],
                'price' => $availability['price_override'] !== null ? $availability['price_override'] : $tour['price'],
                'notes' => $availability['notes']
            ];
        } else {
            // Default availability
            $isPast = $currentDate < strtotime('today');
            $calendar[$dateStr] = [
                'date' => $dateStr,
                'available_slots' => $isPast ? 0 : $tour['max_guests'],
                'status' => $isPast ? 'unavailable' : 'available',
                'price' => $tour['price'],
                'notes' => null
            ];
        }
        
        $currentDate = strtotime('+1 day', $currentDate);
    }
    
    return $calendar;
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

