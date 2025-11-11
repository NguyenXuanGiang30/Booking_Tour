<?php
/**
 * Wishlist functions - Handle wishlist data operations
 */

require_once __DIR__ . '/helper_function.php';

/**
 * Add tour to wishlist
 */
function add_to_wishlist($userId, $tourId) {
    $db = get_db();
    $id = generate_uuid();

    try {
        $stmt = $db->prepare("
            INSERT INTO wishlists (id, user_id, tour_id, created_at) 
            VALUES (:id, :user_id, :tour_id, NOW())
        ");
        return $stmt->execute([
            ':id' => $id,
            ':user_id' => $userId,
            ':tour_id' => $tourId
        ]);
    } catch (PDOException $e) {
        // Already exists
        return false;
    }
}

/**
 * Remove tour from wishlist
 */
function remove_from_wishlist($userId, $tourId) {
    $db = get_db();
    $stmt = $db->prepare("
        DELETE FROM wishlists 
        WHERE user_id = :user_id AND tour_id = :tour_id
    ");
    return $stmt->execute([
        ':user_id' => $userId,
        ':tour_id' => $tourId
    ]);
}

/**
 * Count wishlist items by user ID
 */
function count_wishlist_by_user_id($userId) {
    $db = get_db();
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM wishlists WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $userId]);
    $result = $stmt->fetch();
    return intval($result['total'] ?? 0);
}

/**
 * Get wishlist by user ID
 */
function get_wishlist_by_user_id($userId, $limit = null, $offset = 0) {
    $db = get_db();
    $sql = "
        SELECT w.*, t.* 
        FROM wishlists w 
        JOIN tours t ON t.id = w.tour_id 
        WHERE w.user_id = :user_id 
        ORDER BY w.created_at DESC
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
    $items = $stmt->fetchAll();

    foreach ($items as &$item) {
        $item['images'] = json_decode($item['images'] ?? '[]', true);
        $item['itinerary'] = json_decode($item['itinerary'] ?? '[]', true);
        $item['included'] = json_decode($item['included'] ?? '[]', true);
        $item['excluded'] = json_decode($item['excluded'] ?? '[]', true);
    }

    return $items;
}

/**
 * Check if tour is in wishlist
 */
function is_in_wishlist($userId, $tourId) {
    $db = get_db();
    $stmt = $db->prepare("
        SELECT COUNT(*) as count 
        FROM wishlists 
        WHERE user_id = :user_id AND tour_id = :tour_id
    ");
    $stmt->execute([
        ':user_id' => $userId,
        ':tour_id' => $tourId
    ]);
    $result = $stmt->fetch();
    return $result['count'] > 0;
}
