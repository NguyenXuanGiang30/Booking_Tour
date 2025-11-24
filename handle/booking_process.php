<?php
/**
 * Booking process
 */

// Disable error display for JSON responses
$oldErrorReporting = error_reporting(0);
$oldDisplayErrors = ini_get('display_errors');
ini_set('display_errors', 0);

// Start output buffering to catch any unwanted output
while (ob_get_level() > 0) {
    ob_end_clean();
}
ob_start();

require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../functions/booking_function.php';
require_once __DIR__ . '/../functions/tour_function.php';

// Check login without setting no-cache headers (we'll set JSON headers instead)
Auth::start();
if (!Auth::isLoggedIn()) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    header_remove();
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    echo json_encode(['error' => 'Authentication required']);
    exit;
}

$action = $_GET['action'] ?? 'create';

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clear any output buffer completely
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // Set JSON headers (override any previous headers)
    header_remove(); // Remove all previous headers
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');

    $tourId = trim($_POST['tour_id'] ?? '');
    $bookingDate = $_POST['booking_date'] ?? '';
    $numberOfGuests = intval($_POST['number_of_guests'] ?? 1);
    $travelerInfo = json_decode($_POST['traveler_info'] ?? '{}', true);
    $paymentMethod = trim($_POST['payment_method'] ?? '');
    $paymentMethodId = trim($_POST['payment_method_id'] ?? '');
    
    // Validate payment method if provided
    if (!empty($paymentMethodId)) {
        require_once __DIR__ . '/../functions/payment_method_function.php';
        $paymentMethods = get_payment_methods_by_user_id(Auth::getUserId());
        $validPaymentMethod = false;
        foreach ($paymentMethods as $pm) {
            if ($pm['id'] === $paymentMethodId) {
                $validPaymentMethod = true;
                break;
            }
        }
        if (!$validPaymentMethod) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            header_remove();
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-cache, must-revalidate');
            echo json_encode(['error' => 'Invalid payment method']);
            exit;
        }
    }

    // Helper function to send JSON error response
    function sendJsonError($message, $code = 400) {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header_remove();
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        echo json_encode(['error' => $message]);
        exit;
    }

    // Validation
    if (empty($tourId)) {
        sendJsonError('Tour ID is required');
    }

    if (empty($bookingDate)) {
        sendJsonError('Booking date is required');
    }

    // Validate booking date is in the future
    $bookingDateTime = strtotime($bookingDate);
    if ($bookingDateTime === false || $bookingDateTime < strtotime('today')) {
        sendJsonError('Booking date must be in the future');
    }

    if ($numberOfGuests < 1) {
        sendJsonError('Number of guests must be at least 1');
    }

    $tour = get_tour_by_id($tourId);

    if (!$tour) {
        sendJsonError('Tour not found', 404);
    }

    // Check tour availability
    require_once __DIR__ . '/../functions/availability_function.php';
    $availability = check_tour_availability($tourId, $bookingDate, $numberOfGuests);
    
    if (!$availability['available']) {
        sendJsonError($availability['message']);
    }
    
    // Check if number of guests exceeds available slots
    if ($numberOfGuests > $availability['available_slots']) {
        sendJsonError('Not enough slots available. Only ' . $availability['available_slots'] . ' slots remaining');
    }

    // Get price (use override if available)
    $pricePerPerson = $availability['price'] !== null ? $availability['price'] : $tour['price'];
    $totalPrice = $pricePerPerson * $numberOfGuests;
    
    // Handle coupon code
    $couponCode = trim($_POST['coupon_code'] ?? '');
    $discountAmount = 0;
    $couponData = null;
    
    if (!empty($couponCode)) {
        require_once __DIR__ . '/../functions/coupon_function.php';
        $validation = validate_coupon($couponCode, Auth::getUserId(), $totalPrice, $tourId);
        
        if ($validation['valid']) {
            $discountAmount = $validation['discount_amount'];
            $couponData = [
                'code' => $validation['coupon']['code'],
                'discount_amount' => $discountAmount,
                'message' => $validation['message']
            ];
        } else {
            sendJsonError($validation['message']);
        }
    }

    try {
        $result = create_booking(
            Auth::getUserId(),
            $tourId,
            $bookingDate,
            $numberOfGuests,
            $totalPrice,
            $travelerInfo,
            $paymentMethod,
            !empty($paymentMethodId) ? $paymentMethodId : null,
            $couponCode,
            $discountAmount
        );

        if ($result) {
            // Send booking confirmation email (suppress any output)
            try {
                // Start output buffering for email to prevent any output
                ob_start();
                require_once __DIR__ . '/../includes/Email.php';
                require_once __DIR__ . '/../functions/user_function.php';
                $user = get_user_by_id(Auth::getUserId());
                if ($user && function_exists('get_booking_by_id')) {
                    $booking = get_booking_by_id($result);
                    if ($booking) {
                        Email::sendBookingConfirmation($booking, $user, $tour);
                    }
                }
                // Discard any output from email sending
                ob_end_clean();
            } catch (Exception $emailException) {
                // Log email error but don't fail the booking
                if (ob_get_level() > 0) {
                    ob_end_clean();
                }
                error_log('Email sending failed: ' . $emailException->getMessage());
            } catch (Throwable $emailException) {
                // Catch any other errors
                if (ob_get_level() > 0) {
                    ob_end_clean();
                }
                error_log('Email sending failed: ' . $emailException->getMessage());
            }
            
            // Clear any output buffer before sending JSON
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            // Calculate final price for response
            $finalPrice = $totalPrice - $discountAmount;
            
            // Return booking ID
            $response = [
                'success' => true, 
                'message' => 'Booking created successfully',
                'booking_id' => $result,
                'total_price' => $totalPrice,
                'discount_amount' => $discountAmount,
                'final_price' => $finalPrice
            ];
            
            if ($couponData) {
                $response['coupon'] = $couponData;
            }
            
            // Ensure headers are set correctly
            header_remove();
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-cache, must-revalidate');
            echo json_encode($response);
        } else {
            sendJsonError('Failed to create booking');
        }
    } catch (Exception $e) {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header_remove();
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        echo json_encode(['error' => 'An error occurred while creating booking', 'details' => DEBUG ? $e->getMessage() : '']);
    }
    
    // Restore error settings
    error_reporting($oldErrorReporting);
    ini_set('display_errors', $oldDisplayErrors);
    
    // Clean output buffer and exit
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    exit;
}
