<?php
// Database configuration
define('DB_HOST', 'db16.webme.it');
define('DB_USER', 'sitidi_759');
define('DB_PASS', 'c2F1K5cd08442336');
define('DB_NAME', 'sitidi_759');

// Site configuration
define('SITE_URL', 'https://www.lovenozze.it');
define('UPLOAD_PATH', __DIR__ . '/admin/process/uploads/');
define('UPLOAD_URL', SITE_URL . '/fornitori/admin/process/uploads/');

// Google Maps configuration
define('GOOGLE_MAPS_API_KEY', 'AIzaSyDyi2qjyYB4_WUBAW-2KXVgPL8zhRvAFOI');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection function
function getDBConnection() {
    static $conn = null;
    
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            die("Connessione al database fallita: " . $conn->connect_error);
        }
        
        $conn->set_charset("utf8mb4");
    }
    
    return $conn;
}

// Security functions
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Image optimization function
function optimizeImage($source, $destination, $quality = 80) {
    $info = getimagesize($source);
    
    if ($info['mime'] == 'image/jpeg') {
        $image = imagecreatefromjpeg($source);
    } elseif ($info['mime'] == 'image/png') {
        $image = imagecreatefrompng($source);
    }
    
    if (isset($image)) {
        imagejpeg($image, $destination, $quality);
        imagedestroy($image);
        return true;
    }
    
    return false;
}

// Cache functions
function getCache($key) {
    $cacheFile = __DIR__ . '/cache/' . md5($key) . '.cache';
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 3600)) {
        return unserialize(file_get_contents($cacheFile));
    }
    return false;
}

function setCache($key, $data) {
    $cacheDir = __DIR__ . '/cache';
    if (!file_exists($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    $cacheFile = $cacheDir . '/' . md5($key) . '.cache';
    file_put_contents($cacheFile, serialize($data));
}

// WordPress integration
function getWordPressHeader() {
    if (function_exists('get_header')) {
        get_header();
    } else {
        require_once('../wp-load.php');
        get_header();
    }
}

function getWordPressFooter() {
    if (function_exists('get_footer')) {
        get_footer();
    } else {
        get_footer();
    }
} 