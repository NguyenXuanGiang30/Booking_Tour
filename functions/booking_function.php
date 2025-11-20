<?php
/**
 * Booking functions - Handle booking data operations
 */

require_once __DIR__ . '/helper_function.php';

/**
 * Create new booking
 */
function create_booking($userId, $tourId, $bookingDate, $numberOfGuests, $totalPrice, $travelerInfo = [], $paymentMethod = '', $paymentMethodId = null, $couponCode = null, $discountAmount = 0) {
    $db = get_db();
    $id = generate_uuid();

    // If payment_method_id is provided, get payment method details
    $paymentMethodName = $paymentMethod;
    if (!empty($paymentMethodId)) {
        require_once __DIR__ . '/payment_method_function.php';
        $paymentMethods = get_payment_methods_by_user_id($userId);
        foreach ($paymentMethods as $pm) {
            if ($pm['id'] === $paymentMethodId) {
                $paymentMethodName = ucfirst($pm['type']);
                if (!empty($pm['last_four'])) {
                    $paymentMethodName .= ' ****' . $pm['last_four'];
                } elseif (!empty($pm['name'])) {
                    $paymentMethodName .= ' - ' . $pm['name'];
                }
                break;
            }
        }
    }
    
    // Calculate final price
    $finalPrice = $totalPrice - $discountAmount;
    if ($finalPrice < 0) {
        $finalPrice = 0;
    }
    
    // Get coupon ID if coupon code is provided
    $couponId = null;
    if (!empty($couponCode)) {
        require_once __DIR__ . '/coupon_function.php';
        $coupon = get_coupon_by_code($couponCode);
        if ($coupon) {
            $couponId = $coupon['id'];
        }
    }

    $stmt = $db->prepare("
        INSERT INTO bookings (id, user_id, tour_id, booking_date, number_of_guests, total_price, status, traveler_info, payment_method, payment_method_id, coupon_code, coupon_id, discount_amount, final_price, created_at, updated_at) 
        VALUES (:id, :user_id, :tour_id, :booking_date, :number_of_guests, :total_price, 'pending', :traveler_info, :payment_method, :payment_method_id, :coupon_code, :coupon_id, :discount_amount, :final_price, NOW(), NOW())
    ");
    
    $result = $stmt->execute([
        ':id' => $id,
        ':user_id' => $userId,
        ':tour_id' => $tourId,
        ':booking_date' => $bookingDate,
        ':number_of_guests' => $numberOfGuests,
        ':total_price' => $totalPrice,
        ':traveler_info' => json_encode($travelerInfo),
        ':payment_method' => $paymentMethodName,
        ':payment_method_id' => $paymentMethodId ?: null,
        ':coupon_code' => $couponCode ?: null,
        ':coupon_id' => $couponId,
        ':discount_amount' => $discountAmount,
        ':final_price' => $finalPrice
    ]);
    
    if ($result) {
        // Update tour availability
        require_once __DIR__ . '/availability_function.php';
        update_tour_availability($tourId, $bookingDate, $numberOfGuests, 'book');
        
        // Record coupon usage if applicable
        if (!empty($couponCode) && $couponId) {
            require_once __DIR__ . '/coupon_function.php';
            apply_coupon_to_booking($id, $couponCode);
        }
    }
    
    return $result ? $id : false;
}

/**
 * Get bookings by user ID
 */
function count_bookings_by_user_id($userId) {
    $db = get_db();
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM bookings WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $userId]);
    $result = $stmt->fetch();
    return intval($result['total'] ?? 0);
}

function get_bookings_by_user_id($userId, $limit = null, $offset = 0) {
    $db = get_db();
    $sql = "
        SELECT b.*, t.title, t.location, t.images,
               pm.type as payment_method_type, pm.name as payment_method_name, pm.last_four as payment_method_last_four
        FROM bookings b 
        JOIN tours t ON t.id = b.tour_id 
        LEFT JOIN payment_methods pm ON pm.id = b.payment_method_id
        WHERE b.user_id = :user_id 
        ORDER BY b.created_at DESC
    ";
    
    if ($limit !== null) {
        $sql .= " LIMIT :limit OFFSET :offset";
    }
    
    $stmt = $db->prepare($sql);
    $params = [':user_id' => $userId];
    if ($limit !== null) {
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
    }
    
    foreach ($params as $key => $value) {
        if ($key === ':limit' || $key === ':offset') {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($key, $value);
        }
    }
    $stmt->execute();
    $bookings = $stmt->fetchAll();

    foreach ($bookings as &$booking) {
        $booking['traveler_info'] = json_decode($booking['traveler_info'] ?? '{}', true);
        $booking['images'] = json_decode($booking['images'] ?? '[]', true);
    }

    return $bookings;
}

/**
 * Count payments by user ID
 */
function count_payments_by_user_id($userId) {
    $db = get_db();
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM bookings WHERE user_id = :user_id AND status = 'paid'");
    $stmt->execute([':user_id' => $userId]);
    $result = $stmt->fetch();
    return intval($result['total'] ?? 0);
}

/**
 * Get payments by user ID (bookings with paid status)
 */
function get_payments_by_user_id($userId, $limit = null, $offset = 0) {
    $db = get_db();
    $sql = "
        SELECT b.*, t.title, t.location, t.images,
               pm.type as payment_method_type, pm.name as payment_method_name, pm.last_four as payment_method_last_four
        FROM bookings b 
        JOIN tours t ON t.id = b.tour_id 
        LEFT JOIN payment_methods pm ON pm.id = b.payment_method_id
        WHERE b.user_id = :user_id 
        AND b.status = 'paid'
        ORDER BY b.created_at DESC
    ";
    
    if ($limit !== null) {
        $sql .= " LIMIT :limit OFFSET :offset";
    }
    
    $stmt = $db->prepare($sql);
    $params = [':user_id' => $userId];
    if ($limit !== null) {
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
    }
    
    foreach ($params as $key => $value) {
        if ($key === ':limit' || $key === ':offset') {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($key, $value);
        }
    }
    $stmt->execute();
    $payments = $stmt->fetchAll();

    foreach ($payments as &$payment) {
        $payment['traveler_info'] = json_decode($payment['traveler_info'] ?? '{}', true);
        $payment['images'] = json_decode($payment['images'] ?? '[]', true);
    }

    return $payments;
}

/**
 * Get booking by ID
 */
function get_booking_by_id($bookingId) {
    $db = get_db();
    $stmt = $db->prepare("
        SELECT b.*, t.title, t.location, t.images, t.price as tour_price
        FROM bookings b 
        JOIN tours t ON t.id = b.tour_id 
        WHERE b.id = :id
    ");
    $stmt->execute([':id' => $bookingId]);
    $booking = $stmt->fetch();
    
    if ($booking) {
        $booking['traveler_info'] = json_decode($booking['traveler_info'] ?? '{}', true);
        $booking['images'] = json_decode($booking['images'] ?? '[]', true);
    }
    
    return $booking;
}
