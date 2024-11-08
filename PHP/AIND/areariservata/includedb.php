<?php
/**
 * Database Connection Configuration
 * 
 * This file handles the database connection and configuration
 * for the Art Is Not Dead reserved area.
 */

// Prevent direct access to this file
if (!defined('SECURE_ACCESS')) {
    die('Direct access not permitted');
}

// Database configuration
$db_config = [
    'host'    => 'artit2-eribum83.db.tb-hosting.com',
    'user'    => 'artit2_eribum83',
    'pass'    => 'UHG1NwOcRvsjzo4W',
    'dbname'  => 'artit2_eribum83',
    'charset' => 'utf8mb4'  // Better UTF-8 support
];

/**
 * Error Reporting Configuration
 * Note: Remove or modify for production environment
 */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/**
 * Database Connection
 */
try {
    // Establish database connection
    $conn = mysqli_connect(
        $db_config['host'],
        $db_config['user'],
        $db_config['pass'],
        $db_config['dbname']
    );
    
    // Set character encoding
    mysqli_set_charset($conn, $db_config['charset']);
    
} catch (Exception $e) {
    // Log error and display user-friendly message
    error_log(sprintf(
        'Database Connection Error [%s]: %s', 
        date('Y-m-d H:i:s'),
        $e->getMessage()
    ));
    die('Database connection failed. Please try again later.');
}

// Set timezone for date/time operations
date_default_timezone_set('Europe/Rome');
?>