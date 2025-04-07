<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

// Initialize response array
$response = ['success' => false, 'message' => ''];

try {
    session_start();
    
    // Debug: Log request method and headers
    error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
    error_log("Content-Type: " . $_SERVER['CONTENT_TYPE']);
    error_log("Raw POST data: " . print_r($_POST, true));
    error_log("Raw input: " . file_get_contents('php://input'));
    
    // Check if user is logged in and is admin
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        throw new Exception('Unauthorized access');
    }

    // Get input data from either POST or JSON
    $input = [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $json = file_get_contents('php://input');
            error_log("JSON data: " . $json);
            $input = json_decode($json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON data: ' . json_last_error_msg());
            }
        } else {
            $input = $_POST;
        }
    }
    
    error_log("Processed input data: " . print_r($input, true));

    // Validate required fields
    $required_fields = ['username', 'email', 'password', 'first_name', 'last_name', 'role'];
    foreach ($required_fields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Validate email format
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Validate role
    if (!in_array($input['role'], ['admin', 'author'])) {
        throw new Exception('Invalid role');
    }

    // Database connection
    $db = new Database();
    $conn = $db->getConnection();

    // Check if username or email already exists
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $checkStmt->bind_param("ss", $input['username'], $input['email']);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows > 0) {
        throw new Exception('Username or email already exists');
    }

    // Hash password
    $hashed_password = password_hash($input['password'], PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, first_name, last_name, role, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())");
    $stmt->bind_param("ssssss", 
        $input['username'],
        $input['email'],
        $hashed_password,
        $input['first_name'],
        $input['last_name'],
        $input['role']
    );

    if (!$stmt->execute()) {
        throw new Exception('Failed to create user');
    }

    // Prepare response
    $response['success'] = true;
    $response['message'] = 'User created successfully';

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response); 