<?php
// Ensure no output before headers
ob_start();

// Log the current script path and server variables
error_log("Script path: " . __FILE__);
error_log("Server variables: " . print_r($_SERVER, true));

session_start();

// Log session data for debugging
error_log("Session data: " . print_r($_SESSION, true));

// Adjust the include path based on server configuration
$basePath = dirname(dirname(__FILE__));
$configPath = $basePath . '/config/database.php';

error_log("Attempting to include config file from: " . $configPath);

if (!file_exists($configPath)) {
    error_log("Config file not found at: " . $configPath);
    // Try alternative path
    $configPath = dirname(dirname(dirname(__FILE__))) . '/config/database.php';
    error_log("Trying alternative config path: " . $configPath);
}

require_once $configPath;

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    error_log('Session data missing: user_id or role not set');
    ob_clean();
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized - Session data missing']);
    exit;
}

// Get query parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;

error_log("Media list request - User ID: {$_SESSION['user_id']}, Role: {$_SESSION['role']}, Page: {$page}, Limit: {$limit}");

// Validate parameters
if ($page < 1) $page = 1;
if ($limit < 1 || $limit > 50) $limit = 12;

// Calculate offset
$offset = ($page - 1) * $limit;

try {
    // Database connection
    $db = new Database();
    $conn = $db->getConnection();
    
    error_log("Database connection established successfully");

    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM media WHERE uploaded_by = ? OR ? = 'admin'";
    error_log("Count query: " . $countQuery);
    error_log("Parameters: user_id={$_SESSION['user_id']}, role={$_SESSION['role']}");
    
    $countStmt = $conn->prepare($countQuery);
    if (!$countStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $countStmt->bind_param("is", $_SESSION['user_id'], $_SESSION['role']);
    $countStmt->execute();
    $total = $countStmt->get_result()->fetch_assoc()['total'];
    
    error_log("Total media items found: {$total}");

    // Get media items
    $query = "SELECT m.*, u.username as uploader_name 
              FROM media m 
              JOIN users u ON m.uploaded_by = u.id 
              WHERE m.uploaded_by = ? OR ? = 'admin'
              ORDER BY m.created_at DESC 
              LIMIT ? OFFSET ?";
    error_log("Media query: " . $query);
    error_log("Parameters: user_id={$_SESSION['user_id']}, role={$_SESSION['role']}, limit={$limit}, offset={$offset}");
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("isii", $_SESSION['user_id'], $_SESSION['role'], $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    $media = [];
    while ($row = $result->fetch_assoc()) {
        // Adjust file paths for the server and ensure HTTPS
        $filepath = $row['filepath'];
        
        // Convert HTTP to HTTPS if needed
        if (strpos($filepath, 'http://') === 0) {
            $filepath = 'https://' . substr($filepath, 7);
        }
        
        // Ensure the path starts with /backend/ if it's a relative path
        if (strpos($filepath, 'http') !== 0 && strpos($filepath, '/backend/') === false) {
            $filepath = '/backend' . $filepath;
        }
        
        $media[] = [
            'id' => $row['id'],
            'filename' => $row['filename'],
            'filepath' => $filepath,
            'filetype' => $row['filetype'],
            'filesize' => $row['filesize'],
            'uploaded_by' => $row['uploader_name'],
            'created_at' => $row['created_at']
        ];
    }
    
    error_log("Retrieved " . count($media) . " media items");

    $db->closeConnection();

    // Clear any previous output
    ob_clean();

    // Return response
    $response = [
        'success' => true,
        'data' => $media,
        'total' => $total,
        'page' => $page,
        'limit' => $limit
    ];
    
    error_log("Sending response: " . json_encode($response));
    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error in media list API: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Clear any previous output
    ob_clean();
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving media: ' . $e->getMessage()
    ]);
}

// End output buffering
ob_end_flush(); 