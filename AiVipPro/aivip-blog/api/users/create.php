<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

// Initialize response array
$response = ['success' => false, 'message' => ''];

try {
    session_start();
    
    // Check if user is logged in and is admin
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        throw new Exception('Unauthorized access');
    }

    // Validate required fields
    $required_fields = ['username', 'email', 'password', 'first_name', 'last_name', 'role'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Validate email format
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Validate role
    if (!in_array($_POST['role'], ['admin', 'author'])) {
        throw new Exception('Invalid role');
    }

    // Database connection
    $db = new Database();
    $conn = $db->getConnection();

    // Check if username or email already exists
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $checkStmt->bind_param("ss", $_POST['username'], $_POST['email']);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows > 0) {
        throw new Exception('Username or email already exists');
    }

    // Hash password
    $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, first_name, last_name, role, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())");
    $stmt->bind_param("ssssss", 
        $_POST['username'],
        $_POST['email'],
        $hashed_password,
        $_POST['first_name'],
        $_POST['last_name'],
        $_POST['role']
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