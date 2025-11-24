<?php
require_once 'config.php';
require_once 'functions/helper_function.php';
require_once 'includes/Auth.php';
require_once 'includes/i18n.php';
require_once 'includes/Router.php';

Auth::start();
i18n::init();

$router = new Router();

// Public routes
$router->get('/', function() {
    $_GET['process'] = 'home';
    require_once 'handle/home_process.php';
});
$router->get('/tours', function() {
    $_GET['process'] = 'tours';
    require_once 'handle/tours_process.php';
});
$router->get('/tour/{id}', function($id) {
    $_GET['process'] = 'tours';
    $_GET['action'] = 'show';
    $_GET['id'] = $id;
    require_once 'handle/tours_process.php';
});
$router->get('/about', function() { require_once 'views/about.php'; });
$router->get('/contact', function() { require_once 'views/contact.php'; });

// Language switching
$router->get('/lang/{lang}', function($lang) {
    require_once 'includes/i18n.php';
    i18n::setLang($lang);
    $referer = $_SERVER['HTTP_REFERER'] ?? url('/');
    header('Location: ' . $referer);
    exit;
});

// Auth routes
$router->get('/login', function() {
    $_GET['action'] = 'login';
    require_once 'handle/auth_process.php';
});
$router->post('/login', function() {
    $_GET['action'] = 'login';
    require_once 'handle/auth_process.php';
});
$router->get('/register', function() {
    $_GET['action'] = 'register';
    require_once 'handle/auth_process.php';
});
$router->post('/register', function() {
    $_GET['action'] = 'register';
    require_once 'handle/auth_process.php';
});
$router->get('/logout', function() {
    $_GET['action'] = 'logout';
    require_once 'handle/auth_process.php';
});

// Protected routes
$router->get('/dashboard', function() {
    require_once 'handle/dashboard_process.php';
});
$router->post('/dashboard/profile', function() {
    $_GET['action'] = 'update-profile';
    require_once 'handle/dashboard_process.php';
});
$router->post('/dashboard/password', function() {
    $_GET['action'] = 'change-password';
    require_once 'handle/dashboard_process.php';
});
$router->post('/dashboard/review', function() {
    $_GET['action'] = 'create-review';
    require_once 'handle/dashboard_process.php';
});
$router->post('/dashboard/payment-method/add', function() {
    $_GET['action'] = 'add-payment-method';
    require_once 'handle/dashboard_process.php';
});
$router->post('/dashboard/payment-method/update', function() {
    $_GET['action'] = 'update-payment-method';
    require_once 'handle/dashboard_process.php';
});
$router->post('/dashboard/payment-method/delete', function() {
    $_GET['action'] = 'delete-payment-method';
    require_once 'handle/dashboard_process.php';
});
$router->post('/dashboard/payment-method/set-default', function() {
    $_GET['action'] = 'set-default-payment-method';
    require_once 'handle/dashboard_process.php';
});

// API routes
$router->post('/api/booking', function() {
    $_GET['action'] = 'create';
    require_once 'handle/booking_process.php';
});
$router->post('/api/wishlist/add', function() {
    $_GET['action'] = 'add';
    require_once 'handle/wishlist_process.php';
});
$router->post('/api/wishlist/remove', function() {
    $_GET['action'] = 'remove';
    require_once 'handle/wishlist_process.php';
});
$router->post('/api/coupon/validate', function() {
    // Disable error display
    $oldErrorReporting = error_reporting(0);
    $oldDisplayErrors = ini_get('display_errors');
    ini_set('display_errors', 0);
    
    // Start output buffering
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    ob_start();
    
    try {
        require_once 'includes/Auth.php';
        require_once 'functions/coupon_function.php';
        require_once 'functions/tour_function.php';
        
        Auth::start();
        
        $couponCode = trim($_POST['coupon_code'] ?? '');
        $tourId = trim($_POST['tour_id'] ?? '');
        $amount = floatval($_POST['amount'] ?? 0);
        $userId = Auth::isLoggedIn() ? Auth::getUserId() : null;
        
        if (empty($couponCode)) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            header_remove();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['valid' => false, 'message' => 'Coupon code is required']);
            exit;
        }
        
        if (empty($tourId) || $amount <= 0) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            header_remove();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['valid' => false, 'message' => 'Tour ID and amount are required']);
            exit;
        }
        
        $validation = validate_coupon($couponCode, $userId, $amount, $tourId);
        
        // Clear output buffer
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        header_remove();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($validation);
    } catch (Throwable $e) {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header_remove();
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['valid' => false, 'message' => 'Failed to validate coupon', 'details' => DEBUG ? $e->getMessage() : '']);
    } finally {
        error_reporting($oldErrorReporting);
        ini_set('display_errors', $oldDisplayErrors);
    }
    exit;
});
$router->get('/api/tour/availability', function() {
    // Disable error display
    $oldErrorReporting = error_reporting(0);
    $oldDisplayErrors = ini_get('display_errors');
    ini_set('display_errors', 0);
    
    // Start output buffering
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    ob_start();
    
    try {
        require_once 'functions/availability_function.php';
        
        $tourId = $_GET['tour_id'] ?? '';
        $startDate = $_GET['start_date'] ?? date('Y-m-d');
        $endDate = $_GET['end_date'] ?? date('Y-m-d', strtotime('+3 months'));
        
        // Validate tour ID
        if (empty($tourId)) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            header_remove();
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Tour ID is required']);
            exit;
        }
        
        // Validate date format (YYYY-MM-DD)
        $datePattern = '/^\d{4}-\d{2}-\d{2}$/';
        if (!preg_match($datePattern, $startDate) || !preg_match($datePattern, $endDate)) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            header_remove();
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Invalid date format. Expected YYYY-MM-DD']);
            exit;
        }
        
        // Validate dates are valid
        $startTimestamp = strtotime($startDate);
        $endTimestamp = strtotime($endDate);
        
        if ($startTimestamp === false || $endTimestamp === false) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            header_remove();
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Invalid date values']);
            exit;
        }
        
        // Validate end date is after start date
        if ($endTimestamp < $startTimestamp) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            header_remove();
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'End date must be after start date']);
            exit;
        }
        
        // Validate date range (max 6 months)
        $maxDays = 180; // 6 months
        $daysDiff = ($endTimestamp - $startTimestamp) / (60 * 60 * 24);
        
        if ($daysDiff > $maxDays) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            header_remove();
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => "Date range cannot exceed $maxDays days (6 months)"]);
            exit;
        }
        
        $calendar = get_tour_availability_calendar($tourId, $startDate, $endDate);
        
        // Check if calendar has error
        if (isset($calendar['error'])) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            header_remove();
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($calendar);
            exit;
        }
        
        // Clear output buffer
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        header_remove();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($calendar);
    } catch (Throwable $e) {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header_remove();
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        $errorMsg = 'Failed to load availability calendar';
        if (defined('DEBUG') && DEBUG) {
            $errorMsg .= ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
        }
        error_log('Availability API Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        echo json_encode(['error' => $errorMsg]);
    } finally {
        error_reporting($oldErrorReporting);
        ini_set('display_errors', $oldDisplayErrors);
    }
    exit;
});

// Admin routes
$router->get('/admin/login', function() {
    $_GET['action'] = 'login';
    require_once 'handle/admin_process.php';
});
$router->post('/admin/login', function() {
    $_GET['action'] = 'login';
    require_once 'handle/admin_process.php';
});
$router->get('/admin/logout', function() {
    $_GET['action'] = 'logout';
    require_once 'handle/admin_process.php';
});
$router->get('/admin/dashboard', function() {
    $_GET['action'] = 'dashboard';
    require_once 'handle/admin_process.php';
});
$router->get('/admin/tours', function() {
    $_GET['action'] = 'tours';
    require_once 'handle/admin_process.php';
});
$router->get('/admin/tours/create', function() {
    $_GET['action'] = 'tour-create';
    require_once 'handle/admin_process.php';
});
$router->post('/admin/tours/create', function() {
    $_GET['action'] = 'tour-create';
    require_once 'handle/admin_process.php';
});
$router->get('/admin/tours/edit/{id}', function($id) {
    $_GET['action'] = 'tour-edit';
    $_GET['id'] = $id;
    require_once 'handle/admin_process.php';
});
$router->post('/admin/tours/edit/{id}', function($id) {
    $_GET['action'] = 'tour-edit';
    $_GET['id'] = $id;
    require_once 'handle/admin_process.php';
});
$router->get('/admin/tours/delete/{id}', function($id) {
    $_GET['action'] = 'tour-delete';
    $_GET['id'] = $id;
    require_once 'handle/admin_process.php';
});
$router->get('/admin/users', function() {
    $_GET['action'] = 'users';
    require_once 'handle/admin_process.php';
});
$router->get('/admin/users/create', function() {
    $_GET['action'] = 'user-create';
    require_once 'handle/admin_process.php';
});
$router->post('/admin/users/create', function() {
    $_GET['action'] = 'user-create';
    require_once 'handle/admin_process.php';
});
$router->get('/admin/users/edit/{id}', function($id) {
    $_GET['action'] = 'user-edit';
    $_GET['id'] = $id;
    require_once 'handle/admin_process.php';
});
$router->post('/admin/users/edit/{id}', function($id) {
    $_GET['action'] = 'user-edit';
    $_GET['id'] = $id;
    require_once 'handle/admin_process.php';
});
$router->get('/admin/users/delete/{id}', function($id) {
    $_GET['action'] = 'user-delete';
    $_GET['id'] = $id;
    require_once 'handle/admin_process.php';
});
$router->get('/admin/bookings', function() {
    $_GET['action'] = 'bookings';
    require_once 'handle/admin_process.php';
});
$router->post('/admin/bookings/status', function() {
    $_GET['action'] = 'update-booking-status';
    require_once 'handle/admin_process.php';
});
$router->get('/admin/coupons', function() {
    $_GET['action'] = 'coupons';
    require_once 'handle/admin_process.php';
});
$router->get('/admin/coupons/create', function() {
    $_GET['action'] = 'coupon-create';
    require_once 'handle/admin_process.php';
});
$router->post('/admin/coupons/create', function() {
    $_GET['action'] = 'coupon-create';
    require_once 'handle/admin_process.php';
});
$router->get('/admin/coupons/edit/{id}', function($id) {
    $_GET['action'] = 'coupon-edit';
    $_GET['id'] = $id;
    require_once 'handle/admin_process.php';
});
$router->post('/admin/coupons/edit/{id}', function($id) {
    $_GET['action'] = 'coupon-edit';
    $_GET['id'] = $id;
    require_once 'handle/admin_process.php';
});
$router->get('/admin/coupons/delete/{id}', function($id) {
    $_GET['action'] = 'coupon-delete';
    $_GET['id'] = $id;
    require_once 'handle/admin_process.php';
});
$router->get('/admin/coupons/statistics', function() {
    $_GET['action'] = 'coupon-statistics';
    require_once 'handle/admin_process.php';
});
$router->post('/admin/coupons/import', function() {
    $_GET['action'] = 'coupon-import';
    require_once 'handle/admin_process.php';
});

$router->dispatch();
