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
    if (!isset($data['id'])) {
        throw new Exception('Post ID is required');
    }

    $postId = (int)$data['id'];

    // Database connection
    $db = new Database();
    $conn = $db->getConnection();

    // Check if post exists and user has permission to delete it
    $checkStmt = $conn->prepare("SELECT author_id FROM posts WHERE id = ?");
    $checkStmt->bind_param("i", $postId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Post not found');
    }

    $post = $result->fetch_assoc();

    // Check if user has permission to delete this post
    if ($_SESSION['role'] !== 'admin' && $post['author_id'] !== $_SESSION['user_id']) {
        throw new Exception('You do not have permission to delete this post');
    }

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Delete the post
        $deleteStmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
        $deleteStmt->bind_param("i", $postId);
        
        if (!$deleteStmt->execute()) {
            throw new Exception('Failed to delete post');
        }

        // Commit transaction
        $conn->commit();
        
        // Prepare response
        $response['success'] = true;
        $response['message'] = 'Post deleted successfully';

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    if (isset($db)) {
        $db->closeConnection();
    }
    echo json_encode($response);
}
?> 