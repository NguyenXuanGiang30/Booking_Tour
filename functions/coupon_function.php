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

/**
 * Get coupon by ID
 */
function get_coupon_by_id($id) {
    $db = get_db();
    $stmt = $db->prepare("SELECT * FROM coupons WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $coupon = $stmt->fetch();
    
    if ($coupon && !empty($coupon['applicable_tours'])) {
        $coupon['applicable_tours'] = json_decode($coupon['applicable_tours'], true) ?: [];
    } else {
        $coupon['applicable_tours'] = [];
    }
    
    return $coupon;
}

/**
 * Get all coupons with filters and pagination
 */
function get_all_coupons($filters = [], $limit = null, $offset = 0) {
    $db = get_db();
    $sql = "SELECT * FROM coupons WHERE 1=1";
    $params = [];
    
    if (!empty($filters['search'])) {
        $sql .= " AND (code LIKE :search OR description LIKE :search)";
        $params[':search'] = '%' . $filters['search'] . '%';
    }
    
    if (!empty($filters['status'])) {
        $sql .= " AND status = :status";
        $params[':status'] = $filters['status'];
    }
    
    // Sorting
    $sortBy = $filters['sortBy'] ?? 'created_at';
    $sortOrder = $filters['sortOrder'] ?? 'DESC';
    $sql .= " ORDER BY $sortBy $sortOrder";
    
    // Pagination
    if ($limit !== null) {
        $sql .= " LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
    }
    
    $stmt = $db->prepare($sql);
    foreach ($params as $key => $value) {
        if ($key === ':limit' || $key === ':offset') {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($key, $value);
        }
    }
    $stmt->execute();
    $coupons = $stmt->fetchAll();
    
    foreach ($coupons as &$coupon) {
        if (!empty($coupon['applicable_tours'])) {
            $coupon['applicable_tours'] = json_decode($coupon['applicable_tours'], true) ?: [];
        } else {
            $coupon['applicable_tours'] = [];
        }
    }
    
    return $coupons;
}

/**
 * Count coupons with filters
 */
function count_coupons($filters = []) {
    $db = get_db();
    $sql = "SELECT COUNT(*) as total FROM coupons WHERE 1=1";
    $params = [];
    
    if (!empty($filters['search'])) {
        $sql .= " AND (code LIKE :search OR description LIKE :search)";
        $params[':search'] = '%' . $filters['search'] . '%';
    }
    
    if (!empty($filters['status'])) {
        $sql .= " AND status = :status";
        $params[':status'] = $filters['status'];
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch();
    return intval($result['total'] ?? 0);
}

/**
 * Create new coupon
 */
function create_coupon($data) {
    $db = get_db();
    $id = generate_uuid();
    
    $applicableTours = !empty($data['applicable_tours']) && is_array($data['applicable_tours']) 
        ? json_encode($data['applicable_tours']) 
        : null;
    
    $stmt = $db->prepare("
        INSERT INTO coupons (
            id, code, status, discount_type, discount_value, max_discount,
            min_amount, usage_limit, valid_from, valid_to, applicable_tours, description,
            created_at, updated_at
        ) VALUES (
            :id, :code, :status, :discount_type, :discount_value, :max_discount,
            :min_amount, :usage_limit, :valid_from, :valid_to, :applicable_tours, :description,
            NOW(), NOW()
        )
    ");
    
    $result = $stmt->execute([
        ':id' => $id,
        ':code' => strtoupper(trim($data['code'])),
        ':status' => $data['status'] ?? 'active',
        ':discount_type' => $data['discount_type'] ?? 'percentage',
        ':discount_value' => floatval($data['discount_value'] ?? 0),
        ':max_discount' => !empty($data['max_discount']) ? floatval($data['max_discount']) : null,
        ':min_amount' => floatval($data['min_amount'] ?? 0),
        ':usage_limit' => !empty($data['usage_limit']) ? intval($data['usage_limit']) : null,
        ':valid_from' => $data['valid_from'],
        ':valid_to' => $data['valid_to'],
        ':applicable_tours' => $applicableTours,
        ':description' => $data['description'] ?? null
    ]);
    
    return $result ? $id : false;
}

/**
 * Update coupon
 */
function update_coupon($id, $data) {
    $db = get_db();
    
    $applicableTours = !empty($data['applicable_tours']) && is_array($data['applicable_tours']) 
        ? json_encode($data['applicable_tours']) 
        : null;
    
    $stmt = $db->prepare("
        UPDATE coupons SET
            code = :code,
            status = :status,
            discount_type = :discount_type,
            discount_value = :discount_value,
            max_discount = :max_discount,
            min_amount = :min_amount,
            usage_limit = :usage_limit,
            valid_from = :valid_from,
            valid_to = :valid_to,
            applicable_tours = :applicable_tours,
            description = :description,
            updated_at = NOW()
        WHERE id = :id
    ");
    
    $result = $stmt->execute([
        ':id' => $id,
        ':code' => strtoupper(trim($data['code'])),
        ':status' => $data['status'] ?? 'active',
        ':discount_type' => $data['discount_type'] ?? 'percentage',
        ':discount_value' => floatval($data['discount_value'] ?? 0),
        ':max_discount' => !empty($data['max_discount']) ? floatval($data['max_discount']) : null,
        ':min_amount' => floatval($data['min_amount'] ?? 0),
        ':usage_limit' => !empty($data['usage_limit']) ? intval($data['usage_limit']) : null,
        ':valid_from' => $data['valid_from'],
        ':valid_to' => $data['valid_to'],
        ':applicable_tours' => $applicableTours,
        ':description' => $data['description'] ?? null
    ]);
    
    return $result;
}

/**
 * Delete coupon
 */
function delete_coupon($id) {
    $db = get_db();
    $stmt = $db->prepare("DELETE FROM coupons WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}

/**
 * Get coupon statistics
 */
function get_coupon_statistics() {
    $db = get_db();
    
    // Total coupons
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM coupons");
    $stmt->execute();
    $totalCoupons = intval($stmt->fetch()['total'] ?? 0);
    
    // Active coupons
    $now = date('Y-m-d H:i:s');
    $stmt = $db->prepare("
        SELECT COUNT(*) as total FROM coupons 
        WHERE status = 'active' 
        AND valid_from <= :now 
        AND valid_to >= :now
    ");
    $stmt->execute([':now' => $now]);
    $activeCoupons = intval($stmt->fetch()['total'] ?? 0);
    
    // Expired coupons
    $stmt = $db->prepare("
        SELECT COUNT(*) as total FROM coupons 
        WHERE valid_to < :now
    ");
    $stmt->execute([':now' => $now]);
    $expiredCoupons = intval($stmt->fetch()['total'] ?? 0);
    
    // Total usage count
    $stmt = $db->prepare("SELECT SUM(used_count) as total FROM coupons");
    $stmt->execute();
    $totalUsage = intval($stmt->fetch()['total'] ?? 0);
    
    // Total discount given
    $stmt = $db->prepare("
        SELECT COALESCE(SUM(discount_amount), 0) as total 
        FROM coupon_usage
    ");
    $stmt->execute();
    $totalDiscount = floatval($stmt->fetch()['total'] ?? 0);
    
    // Top used coupons
    $stmt = $db->prepare("
        SELECT c.*, 
               COALESCE(SUM(cu.discount_amount), 0) as total_discount_given
        FROM coupons c
        LEFT JOIN coupon_usage cu ON cu.coupon_id = c.id
        GROUP BY c.id
        ORDER BY c.used_count DESC
        LIMIT 10
    ");
    $stmt->execute();
    $topCoupons = $stmt->fetchAll();
    
    // Coupon usage frequency (by day/month)
    $stmt = $db->prepare("
        SELECT DATE(used_at) as date, COUNT(*) as count
        FROM coupon_usage
        WHERE used_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(used_at)
        ORDER BY date ASC
    ");
    $stmt->execute();
    $usageFrequency = $stmt->fetchAll();
    
    return [
        'total_coupons' => $totalCoupons,
        'active_coupons' => $activeCoupons,
        'expired_coupons' => $expiredCoupons,
        'total_usage' => $totalUsage,
        'total_discount' => $totalDiscount,
        'top_coupons' => $topCoupons,
        'usage_frequency' => $usageFrequency
    ];
}

