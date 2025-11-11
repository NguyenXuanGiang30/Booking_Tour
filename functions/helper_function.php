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