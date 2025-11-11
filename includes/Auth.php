<?php
class Auth {
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
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

    public static function requireLogin() {
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
}
