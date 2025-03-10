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
    $required_fields = ['id', 'username', 'email', 'first_name', 'last_name', 'role', 'status'];
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

    // Validate status
    if (!in_array($_POST['status'], ['active', 'inactive'])) {
        throw new Exception('Invalid status');
    }

    // Database connection
    $db = new Database();
    $conn = $db->getConnection();

    // Check if username or email already exists for other users
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $checkStmt->bind_param("ssi", $_POST['username'], $_POST['email'], $_POST['id']);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows > 0) {
        throw new Exception('Username or email already exists');
    }

    // Update user
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, first_name = ?, last_name = ?, role = ?, status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("ssssssi", 
        $_POST['username'],
        $_POST['email'],
        $_POST['first_name'],
        $_POST['last_name'],
        $_POST['role'],
        $_POST['status'],
        $_POST['id']
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