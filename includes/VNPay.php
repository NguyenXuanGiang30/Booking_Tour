<?php
/**
 * VNPay Payment Gateway Integration
 * Based on VNPay Payment Gateway API
 */

class VNPay {
    private $vnp_TmnCode;
    private $vnp_HashSecret;
    private $vnp_Url;
    private $vnp_ReturnUrl;
    
    public function __construct() {
        // VNPay Configuration - Update these with your VNPay credentials
        $this->vnp_TmnCode = defined('VNPAY_TMN_CODE') ? VNPAY_TMN_CODE : '';
        $this->vnp_HashSecret = defined('VNPAY_HASH_SECRET') ? VNPAY_HASH_SECRET : '';
        
        // VNPay URLs
        // Sandbox: https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
        // Production: https://www.vnpay.vn/paymentv2/vpcpay.html
        $this->vnp_Url = defined('VNPAY_URL') ? VNPAY_URL : 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html';
        
        // Return URL - will be set dynamically
        $baseUrl = defined('SITE_URL') ? SITE_URL : 'http://localhost';
        $basePath = defined('BASE_PATH') ? BASE_PATH : '';
        // Ensure proper URL format
        $baseUrl = rtrim($baseUrl, '/');
        $basePath = $basePath ? '/' . ltrim($basePath, '/') : '';
        $this->vnp_ReturnUrl = $baseUrl . $basePath . '/payment/vnpay/return';
    }
    
    /**
     * Create payment URL
     * @param array $params Payment parameters
     * @return string Payment URL
     */
    public function createPaymentUrl($params) {
        // Validate required configuration
        if (empty($this->vnp_TmnCode) || empty($this->vnp_HashSecret)) {
            throw new Exception('VNPay TmnCode and HashSecret must be configured');
        }
        
        $vnp_TxnRef = $params['vnp_TxnRef'] ?? '';
        $vnp_OrderInfo = $params['vnp_OrderInfo'] ?? '';
        $vnp_OrderType = $params['vnp_OrderType'] ?? 'other';
        $vnp_Amount = $params['vnp_Amount'] ?? 0;
        $vnp_Locale = $params['vnp_Locale'] ?? 'vn';
        $vnp_BankCode = $params['vnp_BankCode'] ?? '';
        $vnp_IpAddr = $params['vnp_IpAddr'] ?? $this->getIpAddress();
        
        // If IP is localhost, use empty string (VNPay will handle it)
        if (empty($vnp_IpAddr) || $vnp_IpAddr === '127.0.0.1' || $vnp_IpAddr === '::1') {
            $vnp_IpAddr = '';
        }
        
        // Validate required parameters
        if (empty($vnp_TxnRef) || empty($vnp_OrderInfo) || $vnp_Amount <= 0) {
            throw new Exception('Missing required VNPay parameters');
        }
        
        // Convert amount to VNPay format (multiply by 100) - according to VNPay standard
        $vnp_Amount = intval($vnp_Amount * 100);
        
        // Calculate expire date (15 minutes from now)
        $startTime = date("YmdHis");
        $expire = date('YmdHis', strtotime('+15 minutes', strtotime($startTime)));
        
        // Build input data array - exactly like VNPay sample
        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $this->vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount, // Already multiplied by 100
            "vnp_Command" => "pay",
            "vnp_CreateDate" => $startTime,
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $this->vnp_ReturnUrl,
            "vnp_TxnRef" => $vnp_TxnRef,
            "vnp_ExpireDate" => $expire
        );
        
        // Add bank code if provided
        if (!empty($vnp_BankCode)) {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }
        
        // Sort by key (alphabetically) - VNPay requires this
        ksort($inputData);
        
        // Build query string and hash data - exactly like VNPay sample
        $query = "";
        $hashdata = "";
        $i = 0;
        
        foreach ($inputData as $key => $value) {
            // URL encode both key and value
            $encodedKey = urlencode($key);
            $encodedValue = urlencode($value);
            
            if ($i == 1) {
                $hashdata .= '&' . $encodedKey . "=" . $encodedValue;
            } else {
                $hashdata .= $encodedKey . "=" . $encodedValue;
                $i = 1;
            }
            $query .= $encodedKey . "=" . $encodedValue . '&';
        }
        
        // Create secure hash using HMAC SHA512
        $vnpSecureHash = hash_hmac('sha512', $hashdata, $this->vnp_HashSecret);
        
        // Build final URL - exactly like VNPay sample (no & before vnp_SecureHash)
        $vnp_Url = $this->vnp_Url . "?" . $query . "vnp_SecureHash=" . $vnpSecureHash;
        
        return $vnp_Url;
    }
    
    /**
     * Verify payment response from VNPay
     * @param array $data Response data from VNPay
     * @return array Verification result
     */
    public function verifyPayment($data) {
        // Filter only VNPay parameters (start with vnp_) - exactly like VNPay sample
        $inputData = array();
        foreach ($data as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }
        
        $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
        
        // Remove vnp_SecureHash from data for verification
        unset($inputData['vnp_SecureHash']);
        
        // Sort by key (alphabetically) - VNPay requires this
        ksort($inputData);
        
        // Build hash data string - exactly like VNPay sample
        $hashData = "";
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }
        
        // Create secure hash
        $secureHash = hash_hmac('sha512', $hashData, $this->vnp_HashSecret);
        
        $result = [
            'success' => false,
            'message' => '',
            'data' => $inputData
        ];
        
        // Compare hashes - exactly like VNPay sample (direct comparison, not case-insensitive)
        if ($secureHash == $vnp_SecureHash) {
            $vnp_ResponseCode = $inputData['vnp_ResponseCode'] ?? '';
            $vnp_TransactionStatus = $inputData['vnp_TransactionStatus'] ?? '';
            
            // Check response code - exactly like VNPay sample (only check ResponseCode == '00')
            if ($vnp_ResponseCode == '00') {
                $result['success'] = true;
                $result['message'] = 'Giao dịch thành công';
            } else {
                $result['message'] = $this->getResponseMessage($vnp_ResponseCode);
            }
        } else {
            $result['message'] = 'Chữ ký không hợp lệ';
            // Debug info (remove in production)
            if (defined('VNPAY_DEBUG') && VNPAY_DEBUG) {
                $result['debug'] = [
                    'expected' => $secureHash,
                    'received' => $vnp_SecureHash,
                    'hashData' => $hashData
                ];
            }
        }
        
        return $result;
    }
    
    /**
     * Get response message by code
     * @param string $code Response code
     * @return string Message
     */
    private function getResponseMessage($code) {
        $messages = [
            '00' => 'Giao dịch thành công',
            '07' => 'Trừ tiền thành công. Giao dịch bị nghi ngờ (liên quan tới lừa đảo, giao dịch bất thường).',
            '09' => 'Thẻ/Tài khoản chưa đăng ký dịch vụ InternetBanking',
            '10' => 'Xác thực thông tin thẻ/tài khoản không đúng. Quá 3 lần',
            '11' => 'Đã hết hạn chờ thanh toán. Xin vui lòng thực hiện lại giao dịch.',
            '12' => 'Thẻ/Tài khoản bị khóa.',
            '13' => 'Nhập sai mật khẩu xác thực giao dịch (OTP). Quá 3 lần',
            '51' => 'Tài khoản không đủ số dư để thực hiện giao dịch.',
            '65' => 'Tài khoản đã vượt quá hạn mức giao dịch trong ngày.',
            '75' => 'Ngân hàng thanh toán đang bảo trì.',
            '79' => 'Nhập sai mật khẩu thanh toán quá số lần quy định.',
            '99' => 'Lỗi không xác định',
        ];
        
        return $messages[$code] ?? 'Lỗi không xác định (Mã: ' . $code . ')';
    }
    
    /**
     * Get IP address
     * @return string IP address
     */
    public static function getIpAddress() {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    // Skip localhost addresses (127.0.0.1, ::1)
                    if ($ip === '127.0.0.1' || $ip === '::1' || $ip === 'localhost') {
                        continue;
                    }
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        // If we get here, try REMOTE_ADDR but avoid localhost
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if ($ip === '127.0.0.1' || $ip === '::1' || $ip === 'localhost') {
            // For local development, use a public IP or let VNPay handle it
            // Return empty string so VNPay can detect it
            return '';
        }
        return $ip;
    }
}

