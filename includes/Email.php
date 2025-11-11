<?php
/**
 * Email Service Class
 * Handles sending emails using PHP mail() or SMTP
 */

class Email {
    private static $enabled = true;
    
    /**
     * Send email
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @param string $altBody Plain text alternative
     * @return bool Success status
     */
    public static function send($to, $subject, $body, $altBody = '') {
        if (!self::$enabled || !defined('EMAIL_ENABLED') || !EMAIL_ENABLED) {
            // Log email instead of sending in development
            error_log("Email would be sent to: $to, Subject: $subject");
            return true;
        }
        
        $fromEmail = defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : 'noreply@travelquest.com';
        $fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'TravelQuest';
        
        // Headers
        $headers = [];
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: text/html; charset=UTF-8";
        $headers[] = "From: " . $fromName . " <" . $fromEmail . ">";
        $headers[] = "Reply-To: " . $fromEmail;
        $headers[] = "X-Mailer: PHP/" . phpversion();
        
        // Send email
        return mail($to, $subject, $body, implode("\r\n", $headers));
    }
    
    /**
     * Send booking confirmation email
     */
    public static function sendBookingConfirmation($booking, $user, $tour) {
        require_once __DIR__ . '/i18n.php';
        $lang = i18n::getCurrentLang();
        $locale = $lang === 'vi' ? 'vi' : 'en';
        
        $subject = $locale === 'vi' 
            ? "Xác nhận đặt tour - TravelQuest" 
            : "Booking Confirmation - TravelQuest";
        
        $body = self::getBookingConfirmationTemplate($booking, $user, $tour, $locale);
        
        return self::send($user['email'], $subject, $body);
    }
    
    /**
     * Send booking status update email
     */
    public static function sendBookingStatusUpdate($booking, $user, $tour, $oldStatus, $newStatus) {
        require_once __DIR__ . '/i18n.php';
        $lang = i18n::getCurrentLang();
        $locale = $lang === 'vi' ? 'vi' : 'en';
        
        $statusMessages = [
            'vi' => [
                'paid' => 'đã thanh toán thành công',
                'confirmed' => 'đã được xác nhận',
                'cancelled' => 'đã bị hủy',
                'completed' => 'đã hoàn thành'
            ],
            'en' => [
                'paid' => 'has been paid successfully',
                'confirmed' => 'has been confirmed',
                'cancelled' => 'has been cancelled',
                'completed' => 'has been completed'
            ]
        ];
        
        $statusText = $statusMessages[$locale][$newStatus] ?? $newStatus;
        
        $subject = $locale === 'vi'
            ? "Cập nhật trạng thái đặt tour - TravelQuest"
            : "Booking Status Update - TravelQuest";
        
        $body = self::getBookingStatusUpdateTemplate($booking, $user, $tour, $statusText, $locale);
        
        return self::send($user['email'], $subject, $body);
    }
    
    /**
     * Send payment success email
     */
    public static function sendPaymentSuccess($booking, $user, $tour) {
        require_once __DIR__ . '/i18n.php';
        $lang = i18n::getCurrentLang();
        $locale = $lang === 'vi' ? 'vi' : 'en';
        
        $subject = $locale === 'vi'
            ? "Thanh toán thành công - TravelQuest"
            : "Payment Successful - TravelQuest";
        
        $body = self::getPaymentSuccessTemplate($booking, $user, $tour, $locale);
        
        return self::send($user['email'], $subject, $body);
    }
    
    /**
     * Get booking confirmation email template
     */
    private static function getBookingConfirmationTemplate($booking, $user, $tour, $locale) {
        $siteUrl = defined('SITE_URL') ? SITE_URL : 'http://localhost';
        $siteName = defined('SITE_NAME') ? SITE_NAME : 'TravelQuest';
        
        $bookingDate = date('d/m/Y', strtotime($booking['booking_date']));
        $totalPrice = number_format($booking['total_price'], 0, ',', '.');
        
        if ($locale === 'vi') {
            return "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #007bff; color: white; padding: 20px; text-align: center; }
                    .content { background: #f9f9f9; padding: 20px; }
                    .booking-info { background: white; padding: 15px; margin: 15px 0; border-left: 4px solid #007bff; }
                    .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Xác nhận đặt tour</h1>
                    </div>
                    <div class='content'>
                        <p>Xin chào <strong>{$user['full_name']}</strong>,</p>
                        <p>Cảm ơn bạn đã đặt tour với chúng tôi!</p>
                        
                        <div class='booking-info'>
                            <h3>Thông tin đặt tour:</h3>
                            <p><strong>Mã đặt tour:</strong> {$booking['id']}</p>
                            <p><strong>Tour:</strong> {$tour['title']}</p>
                            <p><strong>Địa điểm:</strong> {$tour['location']}</p>
                            <p><strong>Ngày khởi hành:</strong> {$bookingDate}</p>
                            <p><strong>Số khách:</strong> {$booking['number_of_guests']}</p>
                            <p><strong>Tổng tiền:</strong> {$totalPrice} VNĐ</p>
                            <p><strong>Trạng thái:</strong> Chờ thanh toán</p>
                        </div>
                        
                        <p>Vui lòng hoàn tất thanh toán để xác nhận đặt tour của bạn.</p>
                        <p><a href='{$siteUrl}/dashboard' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Xem chi tiết đặt tour</a></p>
                    </div>
                    <div class='footer'>
                        <p>&copy; {$siteName}. Tất cả quyền được bảo lưu.</p>
                    </div>
                </div>
            </body>
            </html>";
        } else {
            return "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #007bff; color: white; padding: 20px; text-align: center; }
                    .content { background: #f9f9f9; padding: 20px; }
                    .booking-info { background: white; padding: 15px; margin: 15px 0; border-left: 4px solid #007bff; }
                    .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Booking Confirmation</h1>
                    </div>
                    <div class='content'>
                        <p>Hello <strong>{$user['full_name']}</strong>,</p>
                        <p>Thank you for booking with us!</p>
                        
                        <div class='booking-info'>
                            <h3>Booking Information:</h3>
                            <p><strong>Booking ID:</strong> {$booking['id']}</p>
                            <p><strong>Tour:</strong> {$tour['title']}</p>
                            <p><strong>Location:</strong> {$tour['location']}</p>
                            <p><strong>Departure Date:</strong> {$bookingDate}</p>
                            <p><strong>Number of Guests:</strong> {$booking['number_of_guests']}</p>
                            <p><strong>Total Price:</strong> {$totalPrice} VND</p>
                            <p><strong>Status:</strong> Pending Payment</p>
                        </div>
                        
                        <p>Please complete the payment to confirm your booking.</p>
                        <p><a href='{$siteUrl}/dashboard' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>View Booking Details</a></p>
                    </div>
                    <div class='footer'>
                        <p>&copy; {$siteName}. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>";
        }
    }
    
    /**
     * Get booking status update email template
     */
    private static function getBookingStatusUpdateTemplate($booking, $user, $tour, $statusText, $locale) {
        $siteUrl = defined('SITE_URL') ? SITE_URL : 'http://localhost';
        $siteName = defined('SITE_NAME') ? SITE_NAME : 'TravelQuest';
        
        $bookingDate = date('d/m/Y', strtotime($booking['booking_date']));
        
        if ($locale === 'vi') {
            return "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #28a745; color: white; padding: 20px; text-align: center; }
                    .content { background: #f9f9f9; padding: 20px; }
                    .booking-info { background: white; padding: 15px; margin: 15px 0; border-left: 4px solid #28a745; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Cập nhật trạng thái đặt tour</h1>
                    </div>
                    <div class='content'>
                        <p>Xin chào <strong>{$user['full_name']}</strong>,</p>
                        <p>Đặt tour của bạn <strong>{$statusText}</strong>.</p>
                        
                        <div class='booking-info'>
                            <h3>Thông tin đặt tour:</h3>
                            <p><strong>Mã đặt tour:</strong> {$booking['id']}</p>
                            <p><strong>Tour:</strong> {$tour['title']}</p>
                            <p><strong>Ngày khởi hành:</strong> {$bookingDate}</p>
                        </div>
                        
                        <p><a href='{$siteUrl}/dashboard' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Xem chi tiết</a></p>
                    </div>
                </div>
            </body>
            </html>";
        } else {
            return "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #28a745; color: white; padding: 20px; text-align: center; }
                    .content { background: #f9f9f9; padding: 20px; }
                    .booking-info { background: white; padding: 15px; margin: 15px 0; border-left: 4px solid #28a745; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Booking Status Update</h1>
                    </div>
                    <div class='content'>
                        <p>Hello <strong>{$user['full_name']}</strong>,</p>
                        <p>Your booking <strong>{$statusText}</strong>.</p>
                        
                        <div class='booking-info'>
                            <h3>Booking Information:</h3>
                            <p><strong>Booking ID:</strong> {$booking['id']}</p>
                            <p><strong>Tour:</strong> {$tour['title']}</p>
                            <p><strong>Departure Date:</strong> {$bookingDate}</p>
                        </div>
                        
                        <p><a href='{$siteUrl}/dashboard' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>View Details</a></p>
                    </div>
                </div>
            </body>
            </html>";
        }
    }
    
    /**
     * Get payment success email template
     */
    private static function getPaymentSuccessTemplate($booking, $user, $tour, $locale) {
        $siteUrl = defined('SITE_URL') ? SITE_URL : 'http://localhost';
        $siteName = defined('SITE_NAME') ? SITE_NAME : 'TravelQuest';
        
        $bookingDate = date('d/m/Y', strtotime($booking['booking_date']));
        $totalPrice = number_format($booking['total_price'], 0, ',', '.');
        
        if ($locale === 'vi') {
            return "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #28a745; color: white; padding: 20px; text-align: center; }
                    .content { background: #f9f9f9; padding: 20px; }
                    .booking-info { background: white; padding: 15px; margin: 15px 0; border-left: 4px solid #28a745; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Thanh toán thành công!</h1>
                    </div>
                    <div class='content'>
                        <p>Xin chào <strong>{$user['full_name']}</strong>,</p>
                        <p>Thanh toán của bạn đã được xử lý thành công!</p>
                        
                        <div class='booking-info'>
                            <h3>Thông tin thanh toán:</h3>
                            <p><strong>Mã đặt tour:</strong> {$booking['id']}</p>
                            <p><strong>Tour:</strong> {$tour['title']}</p>
                            <p><strong>Ngày khởi hành:</strong> {$bookingDate}</p>
                            <p><strong>Số khách:</strong> {$booking['number_of_guests']}</p>
                            <p><strong>Số tiền đã thanh toán:</strong> {$totalPrice} VNĐ</p>
                        </div>
                        
                        <p>Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi!</p>
                        <p><a href='{$siteUrl}/dashboard' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Xem voucher</a></p>
                    </div>
                </div>
            </body>
            </html>";
        } else {
            return "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #28a745; color: white; padding: 20px; text-align: center; }
                    .content { background: #f9f9f9; padding: 20px; }
                    .booking-info { background: white; padding: 15px; margin: 15px 0; border-left: 4px solid #28a745; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Payment Successful!</h1>
                    </div>
                    <div class='content'>
                        <p>Hello <strong>{$user['full_name']}</strong>,</p>
                        <p>Your payment has been processed successfully!</p>
                        
                        <div class='booking-info'>
                            <h3>Payment Information:</h3>
                            <p><strong>Booking ID:</strong> {$booking['id']}</p>
                            <p><strong>Tour:</strong> {$tour['title']}</p>
                            <p><strong>Departure Date:</strong> {$bookingDate}</p>
                            <p><strong>Number of Guests:</strong> {$booking['number_of_guests']}</p>
                            <p><strong>Amount Paid:</strong> {$totalPrice} VND</p>
                        </div>
                        
                        <p>Thank you for using our service!</p>
                        <p><a href='{$siteUrl}/dashboard' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>View Voucher</a></p>
                    </div>
                </div>
            </body>
            </html>";
        }
    }
}

