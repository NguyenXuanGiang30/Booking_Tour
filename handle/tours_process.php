<?php
/**
 * Tours page process
 */

require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../functions/tour_function.php';
require_once __DIR__ . '/../functions/review_function.php';
require_once __DIR__ . '/../functions/payment_method_function.php';

$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? '';

if ($action === 'show' && $id) {
    // Show tour detail
    $tour = get_tour_by_id($id);
    
    if (!$tour) {
        http_response_code(404);
        require_once __DIR__ . '/../views/404.php';
        exit;
    }

    $reviews = get_reviews_by_tour_id($id);
    
    // Load payment methods if user is logged in
    $paymentMethods = [];
    $defaultPaymentMethod = null;
    if (Auth::isLoggedIn()) {
        $paymentMethods = get_payment_methods_by_user_id(Auth::getUserId());
        $defaultPaymentMethod = get_default_payment_method(Auth::getUserId());
    }
    
    require_once __DIR__ . '/../views/tour-detail.php';
} else {
    // List tours
    require_once __DIR__ . '/../functions/pagination_function.php';
    
    $filters = [
        'location' => $_GET['location'] ?? '',
        'minPrice' => $_GET['minPrice'] ?? '',
        'maxPrice' => $_GET['maxPrice'] ?? '',
        'duration' => $_GET['duration'] ?? '',
        'category' => $_GET['category'] ?? '',
        'sortBy' => $_GET['sortBy'] ?? 'featured'
    ];
    
    $itemsPerPage = 12;
    $currentPage = max(1, intval($_GET['page'] ?? 1));
    
    $totalTours = count_tours($filters);
    $pagination = get_pagination_info($totalTours, $currentPage, $itemsPerPage);
    
    $tours = get_all_tours($filters, $pagination['items_per_page'], $pagination['offset']);
    require_once __DIR__ . '/../views/tours.php';
}
