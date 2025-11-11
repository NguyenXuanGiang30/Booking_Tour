<?php
/**
 * Coupon functions - Handle coupon operations
 */

require_once __DIR__ . '/helper_function.php';

/**
 * Get coupon by code
 */
function get_coupon_by_code($code) {
    $db = get_db();
    $stmt = $db->prepare("SELECT * FROM coupons WHERE code = :code");
    $stmt->execute([':code' => strtoupper(trim($code))]);
    return $stmt->fetch();
}

/**
 * Validate coupon
 * @param string $code Coupon code
 * @param string $userId User ID (optional, for per-user limits)
 * @param float $amount Order amount
 * @param string $tourId Tour ID (optional, for tour-specific coupons)
 * @return array Validation result
 */
function validate_coupon($code, $userId = null, $amount = 0, $tourId = null) {
    $result = [
        'valid' => false,
        'message' => '',
        'coupon' => null,
        'discount_amount' => 0
    ];
    
    if (empty($code)) {
        $result['message'] = 'Coupon code is required';
        return $result;
    }
    
    $coupon = get_coupon_by_code($code);
    
    if (!$coupon) {
        $result['message'] = 'Invalid coupon code';
        return $result;
    }
    
    // Check status
    if ($coupon['status'] !== 'active') {
        $result['message'] = 'This coupon is not active';
        return $result;
    }
    
    // Check validity dates
    $now = date('Y-m-d H:i:s');
    if ($now < $coupon['valid_from']) {
        $result['message'] = 'This coupon is not yet valid';
        return $result;
    }
    
    if ($now > $coupon['valid_to']) {
        $result['message'] = 'This coupon has expired';
        return $result;
    }
    
    // Check minimum amount
    if ($amount < $coupon['min_amount']) {
        $result['message'] = 'Minimum order amount is ' . number_format($coupon['min_amount'], 0) . ' VND';
        return $result;
    }
    
    // Check usage limit
    if ($coupon['usage_limit'] !== null && $coupon['used_count'] >= $coupon['usage_limit']) {
        $result['message'] = 'This coupon has reached its usage limit';
        return $result;
    }
    
    // Check if coupon is applicable to this tour
    if ($tourId !== null) {
        $applicableTours = json_decode($coupon['applicable_tours'] ?? '[]', true);
        if ($applicableTours !== null && !empty($applicableTours) && !in_array($tourId, $applicableTours)) {
            $result['message'] = 'This coupon is not applicable to this tour';
            return $result;
        }
    }
    
    // Calculate discount amount
    $discountAmount = 0;
    if ($coupon['discount_type'] === 'percentage') {
        $discountAmount = ($amount * $coupon['discount_value']) / 100;
        // Apply max discount if set
        if ($coupon['max_discount'] !== null && $discountAmount > $coupon['max_discount']) {
            $discountAmount = $coupon['max_discount'];
        }
    } else {
        // Fixed amount
        $discountAmount = $coupon['discount_value'];
    }
    
    // Ensure discount doesn't exceed order amount
    if ($discountAmount > $amount) {
        $discountAmount = $amount;
    }
    
    $result['valid'] = true;
    $result['message'] = 'Coupon applied successfully';
    $result['coupon'] = $coupon;
    $result['discount_amount'] = round($discountAmount, 2);
    
    return $result;
}

/**
 * Apply coupon to booking
 */
function apply_coupon_to_booking($bookingId, $couponCode) {
    $db = get_db();
    
    // Get booking
    require_once __DIR__ . '/booking_function.php';
    $booking = get_booking_by_id($bookingId);
    
    if (!$booking) {
        return false;
    }
    
    // Validate coupon
    $validation = validate_coupon($couponCode, $booking['user_id'], $booking['total_price'], $booking['tour_id']);
    
    if (!$validation['valid']) {
        return false;
    }
    
    $coupon = $validation['coupon'];
    $discountAmount = $validation['discount_amount'];
    $finalPrice = $booking['total_price'] - $discountAmount;
    
    // Update booking
    $stmt = $db->prepare("
        UPDATE bookings 
        SET coupon_code = :coupon_code,
            coupon_id = :coupon_id,
            discount_amount = :discount_amount,
            final_price = :final_price,
            updated_at = NOW()
        WHERE id = :id
    ");
    
    $result = $stmt->execute([
        ':coupon_code' => $coupon['code'],
        ':coupon_id' => $coupon['id'],
        ':discount_amount' => $discountAmount,
        ':final_price' => $finalPrice,
        ':id' => $bookingId
    ]);
    
    if ($result) {
        // Increment used count
        $stmt = $db->prepare("
            UPDATE coupons 
            SET used_count = used_count + 1,
                updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([':id' => $coupon['id']]);
        
        // Record usage
        $usageId = generate_uuid();
        $stmt = $db->prepare("
            INSERT INTO coupon_usage (id, coupon_id, booking_id, user_id, discount_amount, used_at)
            VALUES (:id, :coupon_id, :booking_id, :user_id, :discount_amount, NOW())
        ");
        $stmt->execute([
            ':id' => $usageId,
            ':coupon_id' => $coupon['id'],
            ':booking_id' => $bookingId,
            ':user_id' => $booking['user_id'],
            ':discount_amount' => $discountAmount
        ]);
    }
    
    return $result;
}

/**
 * Get all active coupons
 */
function get_active_coupons($limit = 10) {
    $db = get_db();
    $now = date('Y-m-d H:i:s');
    
    $stmt = $db->prepare("
        SELECT * FROM coupons 
        WHERE status = 'active' 
        AND valid_from <= :now 
        AND valid_to >= :now
        AND (usage_limit IS NULL OR used_count < usage_limit)
        ORDER BY created_at DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':now', $now, PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll();
}

