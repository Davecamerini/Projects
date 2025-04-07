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
    $required_fields = ['id', 'username', 'email', 'first_name', 'last_name', 'role', 'status'];
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

    // Validate status
    if (!in_array($input['status'], ['active', 'inactive'])) {
        throw new Exception('Invalid status');
    }

    // Database connection
    $db = new Database();
    $conn = $db->getConnection();

    // Check if username or email already exists for other users
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $checkStmt->bind_param("ssi", $input['username'], $input['email'], $input['id']);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows > 0) {
        throw new Exception('Username or email already exists');
    }

    // Update user
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, first_name = ?, last_name = ?, role = ?, status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("ssssssi", 
        $input['username'],
        $input['email'],
        $input['first_name'],
        $input['last_name'],
        $input['role'],
        $input['status'],
        $input['id']
    );

    if (!$stmt->execute()) {
        throw new Exception('Failed to update user');
    }

    // Prepare response
    $response['success'] = true;
    $response['message'] = 'User updated successfully';

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response); 