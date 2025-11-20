<?php
/**
 * Helper functions
 */

/**
 * Generate UUID v4
 */
function generate_uuid() {
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

/**
 * Get database connection
 */
function get_db() {
    require_once __DIR__ . '/../includes/Database.php';
    return Database::getInstance()->getConnection();
}

/**
 * Generate URL with base path
 */
function url($path = '') {
    $basePath = defined('BASE_PATH') ? BASE_PATH : '';
    
    // Handle query string
    $queryString = '';
    if (strpos($path, '?') !== false) {
        list($path, $queryString) = explode('?', $path, 2);
        $queryString = '?' . $queryString;
    }
    
    $path = ltrim($path, '/');
    
    if ($path === '' || $path === '/') {
        $url = $basePath ? $basePath . '/' : '/';
    } else {
        $url = $basePath . '/' . $path;
    }
    
    return $url . $queryString;
}

/**
 * Sanitize string input
 */
function sanitize_input($input) {
    if (is_array($input)) {
        return array_map('sanitize_input', $input);
    }
    return htmlspecialchars(trim($input ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize for display (XSS protection)
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Validate and sanitize email
 */
function sanitize_email($email) {
    $email = filter_var(trim($email ?? ''), FILTER_SANITIZE_EMAIL);
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
}

/**
 * Validate and sanitize integer
 */
function sanitize_int($value, $min = null, $max = null) {
    $value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    $value = filter_var($value, FILTER_VALIDATE_INT);
    if ($value === false) {
        return null;
    }
    if ($min !== null && $value < $min) {
        return $min;
    }
    if ($max !== null && $value > $max) {
        return $max;
    }
    return $value;
}

/**
 * Validate and sanitize float
 */
function sanitize_float($value, $min = null, $max = null) {
    $value = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $value = filter_var($value, FILTER_VALIDATE_FLOAT);
    if ($value === false) {
        return null;
    }
    if ($min !== null && $value < $min) {
        return $min;
    }
    if ($max !== null && $value > $max) {
        return $max;
    }
    return $value;
}