<?php
/**
 * VNPay Payment Process
 */

// Start output buffering to catch any unwanted output
ob_start();

// Disable error display for JSON responses
$oldErrorReporting = error_reporting(E_ALL);
$oldDisplayErrors = ini_get('display_errors');
ini_set('display_errors', 0);

require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/VNPay.php';
require_once __DIR__ . '/../functions/booking_function.php';
require_once __DIR__ . '/../functions/helper_function.php';
require_once __DIR__ . '/../functions/tour_function.php';

Auth::start();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'create':
        // Create payment URL for booking
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Clear any output buffer
            ob_clean();
            
            // Set JSON header
            header('Content-Type: application/json');
            
            if (!Auth::isLoggedIn()) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit;
            }
            
            $bookingId = trim($_POST['booking_id'] ?? '');
            
            if (empty($bookingId)) {
                http_response_code(400);
                echo json_encode(['error' => 'Booking ID is required']);
                exit;
            }
            
            // Get booking details
            require_once __DIR__ . '/../functions/booking_function.php';
            $booking = get_booking_by_id($bookingId);
            
            if (!$booking || $booking['user_id'] !== Auth::getUserId()) {
                http_response_code(404);
                echo json_encode(['error' => 'Booking not found']);
                exit;
            }
            
            // Check if booking is already paid
            if ($booking['status'] === 'paid' || $booking['status'] === 'confirmed') {
                http_response_code(400);
                echo json_encode(['error' => 'Booking is already paid']);
                exit;
            }
            
            // Get tour info
            $tour = get_tour_by_id($booking['tour_id']);
            
            // Create VNPay payment URL
            $vnpay = new VNPay();
            
            $vnp_TxnRef = $bookingId . '_' . time(); // Unique transaction reference
            $vnp_OrderInfo = 'Thanh toan don dat tour: ' . $tour['title'];
            // Use final_price if available (after discount), otherwise use total_price
            $vnp_Amount = floatval($booking['final_price'] ?? $booking['total_price']);
            
            // Get user locale for VNPay
            require_once __DIR__ . '/../includes/i18n.php';
            $currentLang = i18n::getCurrentLang();
            $vnp_Locale = ($currentLang === 'vi') ? 'vn' : 'en';
            
            $paymentUrl = $vnpay->createPaymentUrl([
                'vnp_TxnRef' => $vnp_TxnRef,
                'vnp_OrderInfo' => $vnp_OrderInfo,
                'vnp_OrderType' => 'other',
                'vnp_Amount' => $vnp_Amount,
                'vnp_Locale' => $vnp_Locale,
                'vnp_IpAddr' => VNPay::getIpAddress()
            ]);
            
            // Update booking with transaction reference
            update_booking_vnpay_ref($bookingId, $vnp_TxnRef);
            
            echo json_encode([
                'success' => true,
                'payment_url' => $paymentUrl,
                'vnp_TxnRef' => $vnp_TxnRef
            ]);
            
            // Restore error settings
            error_reporting($oldErrorReporting);
            ini_set('display_errors', $oldDisplayErrors);
            
            // End output buffering and send
            ob_end_flush();
        }
        exit;
        break;
        
    case 'return':
        // Handle VNPay return callback
        // Filter only VNPay parameters (start with vnp_) - exactly like VNPay sample
        $vnpayParams = [];
        foreach ($_GET as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $vnpayParams[$key] = $value;
            }
        }
        
        // Debug mode - log parameters
        if (defined('VNPAY_DEBUG') && VNPAY_DEBUG) {
            error_log('VNPay Return Params: ' . json_encode($vnpayParams));
        }
        
        $vnpay = new VNPay();
        $result = $vnpay->verifyPayment($_GET); // Pass all GET params, VNPay class will filter
        
        $vnp_TxnRef = $vnpayParams['vnp_TxnRef'] ?? '';
        $vnp_TransactionNo = $vnpayParams['vnp_TransactionNo'] ?? '';
        $vnp_ResponseCode = $vnpayParams['vnp_ResponseCode'] ?? '';
        $vnp_TransactionStatus = $vnpayParams['vnp_TransactionStatus'] ?? '';
        
        // Extract booking ID from transaction reference (format: bookingId_timestamp)
        $bookingId = '';
        if (!empty($vnp_TxnRef)) {
            $parts = explode('_', $vnp_TxnRef);
            $bookingId = $parts[0] ?? '';
        }
        
        if ($result['success']) {
            // Payment successful - vnp_ResponseCode == '00'
            if (!empty($bookingId)) {
                // Get old status before update
                $oldBooking = get_booking_by_id($bookingId);
                $oldStatus = $oldBooking['status'] ?? 'pending';
                
                // Only update if not already paid
                if ($oldStatus !== 'paid' && $oldStatus !== 'confirmed') {
                    // Update booking status
                    update_booking_payment_status(
                        $bookingId,
                        'paid',
                        $vnp_TxnRef,
                        $vnp_TransactionNo,
                        $vnp_ResponseCode,
                        $vnp_TransactionStatus,
                        $vnpayParams['vnp_SecureHash'] ?? ''
                    );
                    
                    // Send payment success email
                    require_once __DIR__ . '/../includes/Email.php';
                    require_once __DIR__ . '/../functions/user_function.php';
                    $booking = get_booking_by_id($bookingId);
                    if ($booking) {
                        $user = get_user_by_id($booking['user_id']);
                        $tour = get_tour_by_id($booking['tour_id']);
                        if ($user && $tour) {
                            Email::sendPaymentSuccess($booking, $user, $tour);
                            Email::sendBookingStatusUpdate($booking, $user, $tour, $oldStatus, 'paid');
                        }
                    }
                }
            }
            
            // Redirect to success page
            header('Location: ' . url('/payment/vnpay/success?booking_id=' . $bookingId));
            exit;
        } else {
            // Payment failed or signature invalid
            if (!empty($bookingId)) {
                // Only update if not already failed
                $oldBooking = get_booking_by_id($bookingId);
                if ($oldBooking && $oldBooking['status'] !== 'failed') {
                    // Update booking with failed status
                    update_booking_payment_status(
                        $bookingId,
                        'failed',
                        $vnp_TxnRef,
                        $vnp_TransactionNo,
                        $vnp_ResponseCode,
                        $vnp_TransactionStatus,
                        $vnpayParams['vnp_SecureHash'] ?? ''
                    );
                }
            }
            
            // Redirect to error page
            header('Location: ' . url('/payment/vnpay/error?message=' . urlencode($result['message']) . '&booking_id=' . $bookingId));
            exit;
        }
        break;
        
    case 'ipn':
        // Handle VNPay IPN (Instant Payment Notification)
        // This is called by VNPay server to notify payment status
        // Exactly like VNPay sample code
        
        // Clear any output buffer
        ob_clean();
        
        // Set JSON header
        header('Content-Type: application/json');
        
        $vnpay = new VNPay();
        $result = $vnpay->verifyPayment($_GET);
        
        $returnData = array();
        
        if ($result['success']) {
            // Signature valid and ResponseCode == '00'
            $inputData = $result['data'];
            $vnp_TxnRef = $inputData['vnp_TxnRef'] ?? '';
            $vnp_TransactionNo = $inputData['vnp_TransactionNo'] ?? '';
            $vnp_ResponseCode = $inputData['vnp_ResponseCode'] ?? '';
            $vnp_TransactionStatus = $inputData['vnp_TransactionStatus'] ?? '';
            $vnp_Amount = isset($inputData['vnp_Amount']) ? intval($inputData['vnp_Amount']) / 100 : 0; // Divide by 100
            
            // Extract booking ID
            $bookingId = '';
            if (!empty($vnp_TxnRef)) {
                $parts = explode('_', $vnp_TxnRef);
                $bookingId = $parts[0] ?? '';
            }
            
            if (!empty($bookingId)) {
                // Get booking to check amount and status
                $booking = get_booking_by_id($bookingId);
                
                if ($booking) {
                    // Check amount matches
                    if (abs($booking['total_price'] - $vnp_Amount) < 0.01) {
                        // Check if booking is not already paid
                        if ($booking['status'] == 'pending' || $booking['status'] == 'failed') {
                            // Update booking status
                            update_booking_payment_status(
                                $bookingId,
                                'paid',
                                $vnp_TxnRef,
                                $vnp_TransactionNo,
                                $vnp_ResponseCode,
                                $vnp_TransactionStatus,
                                $inputData['vnp_SecureHash'] ?? ''
                            );
                            
                            // Send email notification
                            require_once __DIR__ . '/../includes/Email.php';
                            require_once __DIR__ . '/../functions/user_function.php';
                            require_once __DIR__ . '/../functions/tour_function.php';
                            
                            $updatedBooking = get_booking_by_id($bookingId);
                            if ($updatedBooking) {
                                $user = get_user_by_id($updatedBooking['user_id']);
                                $tour = get_tour_by_id($updatedBooking['tour_id']);
                                if ($user && $tour) {
                                    Email::sendPaymentSuccess($updatedBooking, $user, $tour);
                                    Email::sendBookingStatusUpdate($updatedBooking, $user, $tour, $booking['status'], 'paid');
                                }
                            }
                            
                            $returnData['RspCode'] = '00';
                            $returnData['Message'] = 'Confirm Success';
                        } else {
                            $returnData['RspCode'] = '02';
                            $returnData['Message'] = 'Order already confirmed';
                        }
                    } else {
                        $returnData['RspCode'] = '04';
                        $returnData['Message'] = 'Invalid amount';
                    }
                } else {
                    $returnData['RspCode'] = '01';
                    $returnData['Message'] = 'Order not found';
                }
            } else {
                $returnData['RspCode'] = '01';
                $returnData['Message'] = 'Order not found';
            }
        } else {
            // Signature invalid or payment failed
            $returnData['RspCode'] = '97';
            $returnData['Message'] = 'Invalid signature';
        }
        
        // Return JSON response - exactly like VNPay sample
        echo json_encode($returnData);
        
        // Restore error settings
        error_reporting($oldErrorReporting);
        ini_set('display_errors', $oldDisplayErrors);
        
        // End output buffering and send
        ob_end_flush();
        exit;
        
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Action not found']);
        break;
}

