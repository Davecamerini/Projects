<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

// Initialize response array
$response = ['success' => false, 'message' => '', 'password' => ''];

try {
    session_start();
    
    // Check if user is logged in and is admin
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        throw new Exception('Unauthorized access');
    }

    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (!isset($data['id'])) {
        throw new Exception('User ID is required');
    }

    $userId = (int)$data['id'];

    // Prevent resetting own password
    if ($userId === $_SESSION['user_id']) {
        throw new Exception('Cannot reset your own password');
    }

    // Database connection
    $db = new Database();
    $conn = $db->getConnection();

    // Check if user exists
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $checkStmt->bind_param("i", $userId);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows === 0) {
        throw new Exception('User not found');
    }

    // Generate new random password
    $newPassword = bin2hex(random_bytes(8)); // 16 characters
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update user's password
    $stmt = $conn->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("si", $hashedPassword, $userId);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to reset password');
    }

    // Delete all remember tokens for this user
    $tokenStmt = $conn->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
    $tokenStmt->bind_param("i", $userId);
    $tokenStmt->execute();

    // Prepare response
    $response['success'] = true;
    $response['message'] = 'Password reset successfully';
    $response['password'] = $newPassword;

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response); 