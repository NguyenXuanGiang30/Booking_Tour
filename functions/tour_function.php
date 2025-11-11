<?php
/**
 * Tour functions - Handle tour data operations
 */

require_once __DIR__ . '/helper_function.php';

/**
 * Count tours with optional filters
 */
function count_tours($filters = []) {
    $db = get_db();
    $sql = "SELECT COUNT(*) as total FROM tours WHERE 1=1";
    $params = [];

    if (!empty($filters['location'])) {
        $sql .= " AND location LIKE :location";
        $params[':location'] = '%' . $filters['location'] . '%';
    }

    if (!empty($filters['minPrice'])) {
        $sql .= " AND price >= :minPrice";
        $params[':minPrice'] = $filters['minPrice'];
    }

    if (!empty($filters['maxPrice'])) {
        $sql .= " AND price <= :maxPrice";
        $params[':maxPrice'] = $filters['maxPrice'];
    }

    if (!empty($filters['duration'])) {
        $sql .= " AND duration <= :duration";
        $params[':duration'] = $filters['duration'];
    }

    if (!empty($filters['category'])) {
        $sql .= " AND category = :category";
        $params[':category'] = $filters['category'];
    }

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch();
    return intval($result['total'] ?? 0);
}

/**
 * Get all tours with optional filters and pagination
 */
function get_all_tours($filters = [], $limit = null, $offset = 0) {
    $db = get_db();
    $sql = "SELECT * FROM tours WHERE 1=1";
    $params = [];

    if (!empty($filters['location'])) {
        $sql .= " AND location LIKE :location";
        $params[':location'] = '%' . $filters['location'] . '%';
    }

    if (!empty($filters['minPrice'])) {
        $sql .= " AND price >= :minPrice";
        $params[':minPrice'] = $filters['minPrice'];
    }

    if (!empty($filters['maxPrice'])) {
        $sql .= " AND price <= :maxPrice";
        $params[':maxPrice'] = $filters['maxPrice'];
    }

    if (!empty($filters['duration'])) {
        $sql .= " AND duration <= :duration";
        $params[':duration'] = $filters['duration'];
    }

    if (!empty($filters['category'])) {
        $sql .= " AND category = :category";
        $params[':category'] = $filters['category'];
    }

    // Sorting
    $sortBy = $filters['sortBy'] ?? 'featured';
    switch ($sortBy) {
        case 'price-low':
            $sql .= " ORDER BY price ASC";
            break;
        case 'price-high':
            $sql .= " ORDER BY price DESC";
            break;
        case 'rating':
            $sql .= " ORDER BY rating DESC";
            break;
        case 'duration':
            $sql .= " ORDER BY duration ASC";
            break;
        default:
            $sql .= " ORDER BY featured DESC, created_at DESC";
    }

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
    $tours = $stmt->fetchAll();

    foreach ($tours as &$tour) {
        // Safely decode JSON fields
        $tour['images'] = json_decode($tour['images'] ?? '[]', true) ?: [];
        $tour['itinerary'] = json_decode($tour['itinerary'] ?? '[]', true) ?: [];
        
        // Handle included/excluded - might be JSON array or newline-separated string
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
        
        // Ensure images is always an array
        if (!is_array($tour['images'])) {
            $tour['images'] = [];
        }
    }

    return $tours;
}

/**
 * Get tour by ID
 */
function get_tour_by_id($id) {
    $db = get_db();
    $stmt = $db->prepare("SELECT * FROM tours WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $tour = $stmt->fetch();

    if ($tour) {
        // Safely decode JSON fields
        $tour['images'] = json_decode($tour['images'] ?? '[]', true) ?: [];
        $tour['itinerary'] = json_decode($tour['itinerary'] ?? '[]', true) ?: [];
        
        // Handle included/excluded - might be JSON array or newline-separated string
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
        
        // Ensure images is always an array
        if (!is_array($tour['images'])) {
            $tour['images'] = [];
        }
    }

    return $tour;
}

/**
 * Get featured tours
 */
function get_featured_tours($limit = 6) {
    $db = get_db();
    $stmt = $db->prepare("SELECT * FROM tours WHERE featured = 1 ORDER BY created_at DESC LIMIT :limit");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $tours = $stmt->fetchAll();

    foreach ($tours as &$tour) {
        // Safely decode JSON fields
        $tour['images'] = json_decode($tour['images'] ?? '[]', true) ?: [];
        $tour['itinerary'] = json_decode($tour['itinerary'] ?? '[]', true) ?: [];
        
        // Handle included/excluded - might be JSON array or newline-separated string
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
        
        // Ensure images is always an array
        if (!is_array($tour['images'])) {
            $tour['images'] = [];
        }
    }

    return $tours;
}
