<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'travel_quest');
define('DB_USER', 'root');
define('DB_PASS', 'giang2005');
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_URL', 'https://ilana-possessory-zariah.ngrok-free.dev'); // URL từ Ngrok
define('SITE_NAME', 'TravelQuest');

// Base path (automatically detected, but can be overridden)
// Get the directory of the script relative to document root
if (isset($_SERVER['SCRIPT_NAME'])) {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    // If script is in root, BASE_PATH is empty, otherwise it's the directory path
    define('BASE_PATH', ($scriptDir === '/' || $scriptDir === '.') ? '' : rtrim($scriptDir, '/'));
} else {
    // Fallback if SCRIPT_NAME is not available
    define('BASE_PATH', '');
}

// Session Configuration
define('SESSION_LIFETIME', 3600); // 1 hour

// Timezone - VNPay requires Asia/Ho_Chi_Minh
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// VNPay Configuration
// Get these from VNPay merchant portal: https://sandbox.vnpayment.vn/
define('VNPAY_TMN_CODE', 'WL32PUHB'); // Your VNPay Terminal Code (TmnCode)
define('VNPAY_HASH_SECRET', 'LMBW1WQN2RHA4I738D09SREYE1RSB55F'); // Your VNPay Hash Secret
// Sandbox URL for testing
define('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
// Production URL (uncomment when going live)
// define('VNPAY_URL', 'https://www.vnpay.vn/paymentv2/vpcpay.html');

// VNPay Debug Mode (set to false in production)
define('VNPAY_DEBUG', false);

// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com'); // SMTP server
define('SMTP_PORT', 587); // SMTP port (587 for TLS, 465 for SSL)
define('SMTP_USER', ''); // SMTP username (your email)
define('SMTP_PASS', ''); // SMTP password (app password for Gmail)
define('SMTP_FROM_EMAIL', 'noreply@travelquest.com'); // From email address
define('SMTP_FROM_NAME', 'TravelQuest'); // From name
define('SMTP_SECURE', 'tls'); // 'tls' or 'ssl'

// Email Settings
define('EMAIL_ENABLED', true); // Set to false to disable email sending
define('EMAIL_ADMIN', 'admin@travelquest.com'); // Admin email for notifications