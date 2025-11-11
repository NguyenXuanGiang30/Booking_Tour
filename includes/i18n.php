<?php
/**
 * Internationalization (i18n) class
 * Handles language translation and switching
 */

class i18n {
    private static $currentLang = 'en';
    private static $translations = [];
    private static $availableLangs = ['en', 'vi'];
    private static $langDir = __DIR__ . '/../locales';

    /**
     * Initialize i18n system
     */
    public static function init() {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Get language from session or default to 'en'
        if (isset($_SESSION['lang']) && in_array($_SESSION['lang'], self::$availableLangs)) {
            self::$currentLang = $_SESSION['lang'];
        } elseif (isset($_GET['lang']) && in_array($_GET['lang'], self::$availableLangs)) {
            self::$currentLang = $_GET['lang'];
            $_SESSION['lang'] = self::$currentLang;
        } else {
            // Try to detect browser language
            $browserLang = self::detectBrowserLanguage();
            if ($browserLang && in_array($browserLang, self::$availableLangs)) {
                self::$currentLang = $browserLang;
                $_SESSION['lang'] = self::$currentLang;
            }
        }

        // Load translations
        self::loadTranslations();
    }

    /**
     * Detect browser language
     */
    private static function detectBrowserLanguage() {
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return null;
        }

        $langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        foreach ($langs as $lang) {
            $lang = strtolower(trim(explode(';', $lang)[0]));
            if (in_array($lang, self::$availableLangs)) {
                return $lang;
            }
            // Check for language prefix (e.g., vi-VN -> vi)
            $langPrefix = explode('-', $lang)[0];
            if (in_array($langPrefix, self::$availableLangs)) {
                return $langPrefix;
            }
        }

        return null;
    }

    /**
     * Load translations for current language
     */
    private static function loadTranslations() {
        $langFile = self::$langDir . '/' . self::$currentLang . '.php';
        
        if (file_exists($langFile)) {
            self::$translations = require $langFile;
        } else {
            // Fallback to English if language file doesn't exist
            $enFile = self::$langDir . '/en.php';
            if (file_exists($enFile)) {
                self::$translations = require $enFile;
            }
        }
    }

    /**
     * Translate a key
     * @param string $key Translation key (supports dot notation like 'nav.home')
     * @param array $params Parameters to replace in translation
     * @return string Translated text
     */
    public static function t($key, $params = []) {
        // Support dot notation (e.g., 'nav.home')
        $keys = explode('.', $key);
        $value = self::$translations;

        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                // Key not found, return the key itself
                return $key;
            }
        }

        // Replace parameters if provided
        if (!empty($params) && is_string($value)) {
            foreach ($params as $paramKey => $paramValue) {
                $value = str_replace(':' . $paramKey, $paramValue, $value);
            }
        }

        return $value;
    }

    /**
     * Get current language
     */
    public static function getCurrentLang() {
        return self::$currentLang;
    }

    /**
     * Set language
     */
    public static function setLang($lang) {
        if (in_array($lang, self::$availableLangs)) {
            self::$currentLang = $lang;
            if (session_status() !== PHP_SESSION_NONE) {
                $_SESSION['lang'] = $lang;
            }
            self::loadTranslations();
        }
    }

    /**
     * Get available languages
     */
    public static function getAvailableLangs() {
        return self::$availableLangs;
    }

    /**
     * Get language name
     */
    public static function getLangName($lang = null) {
        $lang = $lang ?? self::$currentLang;
        $names = [
            'en' => 'English',
            'vi' => 'Tiếng Việt'
        ];
        return $names[$lang] ?? $lang;
    }
}

// Alias function for easier use
function __($key, $params = []) {
    return i18n::t($key, $params);
}

