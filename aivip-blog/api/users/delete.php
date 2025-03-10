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

    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (!isset($data['id'])) {
        throw new Exception('User ID is required');
    }

    $userId = (int)$data['id'];

    // Prevent deleting self
    if ($userId === $_SESSION['user_id']) {
        throw new Exception('Cannot delete your own account');
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

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Delete user's remember tokens
        $tokenStmt = $conn->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
        $tokenStmt->bind_param("i", $userId);
        $tokenStmt->execute();

        // Delete user's posts
        $postStmt = $conn->prepare("DELETE FROM posts WHERE author_id = ?");
        $postStmt->bind_param("i", $userId);
        $postStmt->execute();

        // Delete user's media
        $mediaStmt = $conn->prepare("DELETE FROM media WHERE uploaded_by = ?");
        $mediaStmt->bind_param("i", $userId);
        $mediaStmt->execute();

        // Finally, delete the user
        $deleteStmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $deleteStmt->bind_param("i", $userId);
        
        if (!$deleteStmt->execute()) {
            throw new Exception('Failed to delete user');
        }

        // Commit transaction
        $conn->commit();
        
        // Prepare response
        $response['success'] = true;
        $response['message'] = 'User deleted successfully';

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response); 