<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

// Initialize response array
$response = ['success' => false, 'message' => ''];

try {
    session_start();
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized access');
    }

    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (!isset($data['username']) || !isset($data['email'])) {
        throw new Exception('Username and email are required');
    }

    // Validate email format
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Database connection
    $db = new Database();
    $conn = $db->getConnection();

    // Check if username or email already exists for other users
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $checkStmt->bind_param("ssi", $data['username'], $data['email'], $_SESSION['user_id']);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows > 0) {
        throw new Exception('Username or email already exists');
    }

    // Update user profile
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("ssi", $data['username'], $data['email'], $_SESSION['user_id']);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update profile');
    }

    // Update session data
    $_SESSION['username'] = $data['username'];
    $_SESSION['email'] = $data['email'];

    // Prepare response
    $response['success'] = true;
    $response['message'] = 'Profile updated successfully';

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response); 