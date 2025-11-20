<?php
/**
 * Dashboard process
 */

require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../functions/booking_function.php';
require_once __DIR__ . '/../functions/wishlist_function.php';
require_once __DIR__ . '/../functions/user_function.php';
require_once __DIR__ . '/../functions/review_function.php';
require_once __DIR__ . '/../functions/tour_function.php';
require_once __DIR__ . '/../functions/payment_method_function.php';
require_once __DIR__ . '/../functions/helper_function.php';

// Set no-cache headers to prevent browser caching
Auth::setNoCacheHeaders();
Auth::requireLogin();

$action = $_GET['action'] ?? 'index';
$view = $_GET['view'] ?? 'profile';

// Validate view parameter
$allowedViews = ['profile', 'bookings', 'wishlist', 'payments', 'reviews', 'settings'];
if (!in_array($view, $allowedViews)) {
    $view = 'profile';
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token for all POST requests
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!Auth::verifyCsrfToken($csrfToken)) {
        $error = 'Invalid security token. Please try again.';
        $view = 'profile';
    } elseif ($action === 'update-profile') {
        $birthday = trim($_POST['birthday'] ?? '');
        
        $data = [
            'full_name' => trim($_POST['full_name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'birthday' => $birthday // Will be handled in update_user function
        ];

        // Validate full name
        if (empty($data['full_name'])) {
            $error = 'Full name is required';
        } else {
            // Validate birthday format if provided
            if (!empty($birthday)) {
                $date = DateTime::createFromFormat('Y-m-d', $birthday);
                if ($date === false || $date->format('Y-m-d') !== $birthday) {
                    $error = 'Invalid birthday format';
                }
            }
            
            if (!isset($error)) {
                $result = update_user(Auth::getUserId(), $data);
                if ($result) {
                    // Update session user data
                    $user = get_user_by_id(Auth::getUserId());
                    Auth::login(Auth::getUserId(), $user);
                    header('Location: ' . url('/dashboard?view=profile&success=Profile updated successfully'));
                    exit;
                } else {
                    $error = 'Failed to update profile';
                }
            }
        }
    } elseif ($action === 'change-password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'All password fields are required';
        } elseif (strlen($newPassword) < 6) {
            $error = 'New password must be at least 6 characters';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New passwords do not match';
        } else {
            $result = update_user_password(Auth::getUserId(), $currentPassword, $newPassword);
            if ($result) {
                header('Location: ' . url('/dashboard?view=settings&success=Password changed successfully'));
                exit;
            } else {
                $error = 'Current password is incorrect';
            }
        }
    } elseif ($action === 'create-review') {
        $tourId = $_POST['tour_id'] ?? '';
        $rating = intval($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');
        
        if (empty($tourId) || $rating < 1 || $rating > 5 || empty($comment)) {
            $error = 'Please provide a valid rating and comment';
        } elseif (has_user_reviewed(Auth::getUserId(), $tourId)) {
            $error = 'You have already reviewed this tour';
        } else {
            $result = create_review(Auth::getUserId(), $tourId, $rating, $comment);
            if ($result) {
                header('Location: ' . url('/dashboard?view=reviews&success=Review submitted successfully'));
                exit;
            } else {
                $error = 'Failed to submit review';
            }
        }
    } elseif ($action === 'add-payment-method') {
        $type = trim($_POST['type'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $lastFour = trim($_POST['last_four'] ?? '');
        $expiryDate = trim($_POST['expiry_date'] ?? '');
        $isDefault = isset($_POST['is_default']);
        
        if (empty($type) || empty($name)) {
            $error = 'Payment method type and name are required';
        } elseif ($type === 'card' && (empty($lastFour) || empty($expiryDate))) {
            $error = 'Card number (last 4 digits) and expiry date are required for cards';
        } else {
            $result = create_payment_method(Auth::getUserId(), $type, $name, $lastFour, $expiryDate, $isDefault);
            if ($result) {
                header('Location: ' . url('/dashboard?view=payments&success=Payment method added successfully'));
                exit;
            } else {
                $error = 'Failed to add payment method';
            }
        }
    } elseif ($action === 'update-payment-method') {
        $id = $_POST['id'] ?? '';
        $type = trim($_POST['type'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $lastFour = trim($_POST['last_four'] ?? '');
        $expiryDate = trim($_POST['expiry_date'] ?? '');
        $isDefault = isset($_POST['is_default']);
        
        if (empty($id) || empty($type) || empty($name)) {
            $error = 'Payment method ID, type and name are required';
        } elseif ($type === 'card' && (empty($lastFour) || empty($expiryDate))) {
            $error = 'Card number (last 4 digits) and expiry date are required for cards';
        } else {
            $data = [
                'type' => $type,
                'name' => $name,
                'last_four' => $lastFour,
                'expiry_date' => $expiryDate,
                'is_default' => $isDefault
            ];
            $result = update_payment_method($id, Auth::getUserId(), $data);
            if ($result) {
                header('Location: ' . url('/dashboard?view=payments&success=Payment method updated successfully'));
                exit;
            } else {
                $error = 'Failed to update payment method';
            }
        }
    } elseif ($action === 'delete-payment-method') {
        $id = $_POST['id'] ?? '';
        if (empty($id)) {
            $error = 'Payment method ID is required';
        } else {
            $result = delete_payment_method($id, Auth::getUserId());
            if ($result) {
                header('Location: ' . url('/dashboard?view=payments&success=Payment method deleted successfully'));
                exit;
            } else {
                $error = 'Failed to delete payment method';
            }
        }
    } elseif ($action === 'set-default-payment-method') {
        $id = $_POST['id'] ?? '';
        if (empty($id)) {
            $error = 'Payment method ID is required';
        } else {
            $result = set_default_payment_method($id, Auth::getUserId());
            if ($result) {
                header('Location: ' . url('/dashboard?view=payments&success=Default payment method updated'));
                exit;
            } else {
                $error = 'Failed to set default payment method';
            }
        }
    }
}

$user = Auth::getUser();
$bookings = [];
$wishlist = [];
$payments = [];
$paymentStats = [];
$paymentMethods = [];
$reviews = [];
$toursForReview = [];

// Load data based on view
require_once __DIR__ . '/../functions/pagination_function.php';

$itemsPerPage = 10;
$currentPage = max(1, intval($_GET['page'] ?? 1));
$pagination = null;

if ($view === 'bookings') {
    $totalItems = count_bookings_by_user_id(Auth::getUserId());
    $pagination = get_pagination_info($totalItems, $currentPage, $itemsPerPage);
    $bookings = get_bookings_by_user_id(Auth::getUserId(), $pagination['items_per_page'], $pagination['offset']);
} elseif ($view === 'wishlist') {
    $totalItems = count_wishlist_by_user_id(Auth::getUserId());
    $pagination = get_pagination_info($totalItems, $currentPage, $itemsPerPage);
    $wishlist = get_wishlist_by_user_id(Auth::getUserId(), $pagination['items_per_page'], $pagination['offset']);
} elseif ($view === 'payments') {
    $totalItems = count_payments_by_user_id(Auth::getUserId());
    $pagination = get_pagination_info($totalItems, $currentPage, $itemsPerPage);
    $payments = get_payments_by_user_id(Auth::getUserId(), $pagination['items_per_page'], $pagination['offset']);
    $paymentStats = get_user_payment_stats(Auth::getUserId());
    $paymentMethods = get_payment_methods_by_user_id(Auth::getUserId());
} elseif ($view === 'reviews') {
    $totalItems = count_reviews_by_user_id(Auth::getUserId());
    $pagination = get_pagination_info($totalItems, $currentPage, $itemsPerPage);
    $reviews = get_reviews_by_user_id(Auth::getUserId(), $pagination['items_per_page'], $pagination['offset']);
    $toursForReview = get_tours_for_review(Auth::getUserId());
}

require_once __DIR__ . '/../views/dashboard.php';
