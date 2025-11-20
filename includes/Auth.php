<?php
class Auth {
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            // Configure secure session settings
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 1 : 0);
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_samesite', 'Strict');
            
            session_start();
            
            // Regenerate session ID periodically to prevent session fixation
            if (!isset($_SESSION['created'])) {
                $_SESSION['created'] = time();
            } else if (time() - $_SESSION['created'] > 1800) { // 30 minutes
                session_regenerate_id(true);
                $_SESSION['created'] = time();
            }
        }
    }

    public static function login($userId, $userData) {
        self::start();
        $_SESSION['user_id'] = $userId;
        $_SESSION['user'] = $userData;
    }

    public static function logout() {
        self::start();
        session_unset();
        session_destroy();
    }

    public static function isLoggedIn() {
        self::start();
        return isset($_SESSION['user_id']);
    }

    public static function getUserId() {
        self::start();
        return $_SESSION['user_id'] ?? null;
    }

    public static function getUser() {
        self::start();
        return $_SESSION['user'] ?? null;
    }

    /**
     * Set no-cache headers to prevent browser caching
     */
    public static function setNoCacheHeaders() {
        header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
    }

    public static function requireLogin() {
        // Set no-cache headers to prevent browser back button issues
        self::setNoCacheHeaders();
        
        if (!self::isLoggedIn()) {
            require_once __DIR__ . '/../functions/helper_function.php';
            header('Location: ' . url('/login'));
            exit;
        }
    }

    public static function isAdmin() {
        self::start();
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
    }

    public static function requireAdmin() {
        // Set no-cache headers to prevent browser back button issues
        self::setNoCacheHeaders();
        
        if (!self::isAdmin()) {
            require_once __DIR__ . '/../functions/helper_function.php';
            header('Location: ' . url('/admin/login'));
            exit;
        }
    }

    public static function adminLogin($adminId, $adminData) {
        self::start();
        $_SESSION['admin_id'] = $adminId;
        $_SESSION['admin'] = $adminData;
        $_SESSION['is_admin'] = true;
    }

    public static function getAdminId() {
        self::start();
        return $_SESSION['admin_id'] ?? null;
    }

    public static function getAdmin() {
        self::start();
        return $_SESSION['admin'] ?? null;
    }

    /**
     * Generate CSRF token
     */
    public static function generateCsrfToken() {
        self::start();
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCsrfToken($token) {
        self::start();
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
