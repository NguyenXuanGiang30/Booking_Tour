<?php
/**
 * Booking process
 */

// Start output buffering to catch any unwanted output
ob_start();

// Disable error display for JSON responses
$oldErrorReporting = error_reporting(E_ALL);
$oldDisplayErrors = ini_get('display_errors');
ini_set('display_errors', 0);

require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../functions/booking_function.php';
require_once __DIR__ . '/../functions/tour_function.php';

Auth::requireLogin();

$action = $_GET['action'] ?? 'create';

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clear any output buffer
    ob_clean();
    
    // Set JSON header
    header('Content-Type: application/json');

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
            http_response_code(400);
            echo json_encode(['error' => 'Invalid payment method']);
            exit;
        }
    }

    // Validation
    if (empty($tourId)) {
        http_response_code(400);
        echo json_encode(['error' => 'Tour ID is required']);
        exit;
    }

    if (empty($bookingDate)) {
        http_response_code(400);
        echo json_encode(['error' => 'Booking date is required']);
        exit;
    }

    // Validate booking date is in the future
    $bookingDateTime = strtotime($bookingDate);
    if ($bookingDateTime === false || $bookingDateTime < strtotime('today')) {
        http_response_code(400);
        echo json_encode(['error' => 'Booking date must be in the future']);
        exit;
    }

    if ($numberOfGuests < 1) {
        http_response_code(400);
        echo json_encode(['error' => 'Number of guests must be at least 1']);
        exit;
    }

    $tour = get_tour_by_id($tourId);

    if (!$tour) {
        http_response_code(404);
        echo json_encode(['error' => 'Tour not found']);
        exit;
    }

    // Check tour availability
    require_once __DIR__ . '/../functions/availability_function.php';
    $availability = check_tour_availability($tourId, $bookingDate, $numberOfGuests);
    
    if (!$availability['available']) {
        http_response_code(400);
        echo json_encode(['error' => $availability['message']]);
        exit;
    }
    
    // Check if number of guests exceeds available slots
    if ($numberOfGuests > $availability['available_slots']) {
        http_response_code(400);
        echo json_encode(['error' => 'Not enough slots available. Only ' . $availability['available_slots'] . ' slots remaining']);
        exit;
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
            http_response_code(400);
            echo json_encode(['error' => $validation['message']]);
            exit;
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
                require_once __DIR__ . '/../includes/Email.php';
                require_once __DIR__ . '/../functions/user_function.php';
                $user = get_user_by_id(Auth::getUserId());
                $booking = get_booking_by_id($result);
                if ($user && $booking) {
                    Email::sendBookingConfirmation($booking, $user, $tour);
                }
            } catch (Exception $emailException) {
                // Log email error but don't fail the booking
                error_log('Email sending failed: ' . $emailException->getMessage());
            }
            
            // Calculate final price for response
            $finalPrice = $totalPrice - $discountAmount;
            
            // Return booking ID for VNPay payment flow
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
            
            echo json_encode($response);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Failed to create booking']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'An error occurred while creating booking']);
    }
    
    // Restore error settings
    error_reporting($oldErrorReporting);
    ini_set('display_errors', $oldDisplayErrors);
    
    // End output buffering and send
    ob_end_flush();
    exit;
}
