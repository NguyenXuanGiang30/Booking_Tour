<?php
/**
 * Wishlist process
 */

// Disable error display for JSON responses
$oldErrorReporting = error_reporting(0);
$oldDisplayErrors = ini_get('display_errors');
ini_set('display_errors', 0);

// Start output buffering
while (ob_get_level() > 0) {
    ob_end_clean();
}
ob_start();

require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../functions/wishlist_function.php';
require_once __DIR__ . '/../functions/tour_function.php';

// Check login without setting no-cache headers
Auth::start();
if (!Auth::isLoggedIn()) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    header_remove();
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clear output buffer
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    header_remove();
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    
    try {
        $tourId = trim($_POST['tour_id'] ?? '');
        
        if (empty($tourId)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Tour ID is required']);
            exit;
        }
        
        // Verify tour exists
        $tour = get_tour_by_id($tourId);
        if (!$tour) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Tour not found']);
            exit;
        }
        
        if ($action === 'add') {
            // Check if already in wishlist
            if (is_in_wishlist(Auth::getUserId(), $tourId)) {
                echo json_encode(['success' => false, 'error' => 'Tour is already in your wishlist']);
                exit;
            }
            
            $result = add_to_wishlist(Auth::getUserId(), $tourId);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Added to wishlist']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to add to wishlist']);
            }
        } elseif ($action === 'remove') {
            $result = remove_from_wishlist(Auth::getUserId(), $tourId);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Removed from wishlist']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to remove from wishlist']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
    } catch (Throwable $e) {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header_remove();
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'An error occurred', 'details' => DEBUG ? $e->getMessage() : '']);
    } finally {
        error_reporting($oldErrorReporting);
        ini_set('display_errors', $oldDisplayErrors);
    }
    exit;
}
