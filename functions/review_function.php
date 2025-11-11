<?php
/**
 * Review functions - Handle review data operations
 */

require_once __DIR__ . '/helper_function.php';

/**
 * Get reviews by tour ID
 */
function get_reviews_by_tour_id($tourId, $limit = 10) {
    $db = get_db();
    $stmt = $db->prepare("
        SELECT r.*, p.full_name, p.avatar_url 
        FROM reviews r 
        JOIN profiles p ON p.id = r.user_id 
        WHERE r.tour_id = :tour_id 
        ORDER BY r.created_at DESC 
        LIMIT :limit
    ");
    $stmt->bindValue(':tour_id', $tourId);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Create new review
 */
function create_review($userId, $tourId, $rating, $comment) {
    // Validate rating
    $rating = intval($rating);
    if ($rating < 1 || $rating > 5) {
        return false;
    }
    
    // Validate comment
    $comment = trim($comment);
    if (empty($comment)) {
        return false;
    }
    
    $db = get_db();
    $id = generate_uuid();

    $stmt = $db->prepare("
        INSERT INTO reviews (id, user_id, tour_id, rating, comment, created_at, updated_at) 
        VALUES (:id, :user_id, :tour_id, :rating, :comment, NOW(), NOW())
    ");
    
    $result = $stmt->execute([
        ':id' => $id,
        ':user_id' => $userId,
        ':tour_id' => $tourId,
        ':rating' => $rating,
        ':comment' => $comment
    ]);

    // Update tour rating
    if ($result) {
        update_tour_rating($tourId);
    }

    return $result;
}

/**
 * Update tour rating based on reviews
 */
function update_tour_rating($tourId) {
    $db = get_db();
    $stmt = $db->prepare("
        UPDATE tours SET 
            rating = (SELECT COALESCE(AVG(rating), 0) FROM reviews WHERE tour_id = :tour_id),
            total_reviews = (SELECT COUNT(*) FROM reviews WHERE tour_id = :tour_id)
        WHERE id = :tour_id
    ");
    $stmt->execute([':tour_id' => $tourId]);
}

/**
 * Count reviews by user ID
 */
function count_reviews_by_user_id($userId) {
    $db = get_db();
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM reviews WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $userId]);
    $result = $stmt->fetch();
    return intval($result['total'] ?? 0);
}

/**
 * Get reviews by user ID
 */
function get_reviews_by_user_id($userId, $limit = null, $offset = 0) {
    $db = get_db();
    $sql = "
        SELECT r.*, t.title, t.location, t.images 
        FROM reviews r 
        JOIN tours t ON t.id = r.tour_id 
        WHERE r.user_id = :user_id 
        ORDER BY r.created_at DESC
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
    $reviews = $stmt->fetchAll();

    foreach ($reviews as &$review) {
        $review['images'] = json_decode($review['images'] ?? '[]', true);
    }

    return $reviews;
}

/**
 * Get tours that user can review (booked and paid, not yet reviewed)
 */
function get_tours_for_review($userId) {
    $db = get_db();
    // Get tours that user has paid bookings for but hasn't reviewed yet
    // Use different parameter names for main query and subquery
    $stmt = $db->prepare("
        SELECT t.*, 
               MIN(b.booking_date) as booking_date,
               MAX(b.created_at) as last_booking_date
        FROM bookings b
        INNER JOIN tours t ON t.id = b.tour_id
        WHERE b.user_id = :user_id
        AND b.status = 'paid'
        AND t.id NOT IN (
            SELECT DISTINCT tour_id 
            FROM reviews 
            WHERE user_id = :user_id_sub
        )
        GROUP BY t.id
        ORDER BY last_booking_date DESC
    ");
    $stmt->execute([
        ':user_id' => $userId,
        ':user_id_sub' => $userId
    ]);
    $tours = $stmt->fetchAll();

    foreach ($tours as &$tour) {
        $tour['images'] = json_decode($tour['images'] ?? '[]', true) ?: [];
        $tour['itinerary'] = json_decode($tour['itinerary'] ?? '[]', true) ?: [];
        
        $included = json_decode($tour['included'] ?? '[]', true);
        if (is_array($included)) {
            $tour['included'] = array_filter(array_map('trim', $included));
        } else {
            $tour['included'] = [];
        }
        
        $excluded = json_decode($tour['excluded'] ?? '[]', true);
        if (is_array($excluded)) {
            $tour['excluded'] = array_filter(array_map('trim', $excluded));
        } else {
            $tour['excluded'] = [];
        }
    }

    return $tours;
}

/**
 * Check if user has already reviewed a tour
 */
function has_user_reviewed($userId, $tourId) {
    $db = get_db();
    $stmt = $db->prepare("
        SELECT COUNT(*) as count 
        FROM reviews 
        WHERE user_id = :user_id AND tour_id = :tour_id
    ");
    $stmt->execute([
        ':user_id' => $userId,
        ':tour_id' => $tourId
    ]);
    $result = $stmt->fetch();
    return $result['count'] > 0;
}
