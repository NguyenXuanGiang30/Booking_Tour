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
    header('Content-Type: application/json');
    require_once 'includes/Auth.php';
    require_once 'functions/coupon_function.php';
    require_once 'functions/tour_function.php';
    
    Auth::start();
    
    $couponCode = trim($_POST['coupon_code'] ?? '');
    $tourId = trim($_POST['tour_id'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $userId = Auth::isLoggedIn() ? Auth::getUserId() : null;
    
    if (empty($couponCode)) {
        echo json_encode(['valid' => false, 'message' => 'Coupon code is required']);
        exit;
    }
    
    if (empty($tourId) || $amount <= 0) {
        echo json_encode(['valid' => false, 'message' => 'Tour ID and amount are required']);
        exit;
    }
    
    $validation = validate_coupon($couponCode, $userId, $amount, $tourId);
    echo json_encode($validation);
    exit;
});
$router->get('/api/tour/availability', function() {
    header('Content-Type: application/json');
    require_once 'functions/availability_function.php';
    
    $tourId = $_GET['tour_id'] ?? '';
    $startDate = $_GET['start_date'] ?? date('Y-m-d');
    $endDate = $_GET['end_date'] ?? date('Y-m-d', strtotime('+3 months'));
    
    if (empty($tourId)) {
        http_response_code(400);
        echo json_encode(['error' => 'Tour ID is required']);
        exit;
    }
    
    $calendar = get_tour_availability_calendar($tourId, $startDate, $endDate);
    echo json_encode($calendar);
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
$router->get('/admin/bookings', function() {
    $_GET['action'] = 'bookings';
    require_once 'handle/admin_process.php';
});
$router->post('/admin/bookings/status', function() {
    $_GET['action'] = 'update-booking-status';
    require_once 'handle/admin_process.php';
});

// VNPay Payment routes
$router->post('/payment/vnpay/create', function() {
    $_GET['action'] = 'create';
    require_once 'handle/vnpay_process.php';
});
$router->get('/payment/vnpay/return', function() {
    // Preserve all GET parameters from VNPay
    // Router already preserves $_GET, but we need to ensure action is set
    $_GET['action'] = 'return';
    
    // Debug: Log all parameters
    if (defined('VNPAY_DEBUG') && VNPAY_DEBUG) {
        error_log('VNPay Return - All GET params: ' . json_encode($_GET));
        error_log('VNPay Return - REQUEST_URI: ' . ($_SERVER['REQUEST_URI'] ?? 'N/A'));
        error_log('VNPay Return - QUERY_STRING: ' . ($_SERVER['QUERY_STRING'] ?? 'N/A'));
    }
    
    require_once 'handle/vnpay_process.php';
});
$router->get('/payment/vnpay/ipn', function() {
    $_GET['action'] = 'ipn';
    require_once 'handle/vnpay_process.php';
});
$router->get('/payment/vnpay/success', function() {
    require_once 'views/payment/vnpay-success.php';
});
$router->get('/payment/vnpay/error', function() {
    require_once 'views/payment/vnpay-error.php';
});

$router->dispatch();
