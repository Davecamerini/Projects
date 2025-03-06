<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

// Initialize response array
$response = ['success' => false, 'message' => '', 'data' => null];

try {
    session_start();
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized access');
    }

    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (!isset($data['id']) || !isset($data['title']) || !isset($data['content'])) {
        throw new Exception('ID, title and content are required');
    }

    // Database connection
    $db = new Database();
    $conn = $db->getConnection();

    // Check if post exists and user has permission to edit it
    $checkStmt = $conn->prepare("SELECT author_id FROM posts WHERE id = ?");
    $checkStmt->bind_param("i", $data['id']);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Post not found');
    }

    $post = $result->fetch_assoc();

    // Check if user has permission to edit this post
    if ($_SESSION['role'] !== 'admin' && $post['author_id'] !== $_SESSION['user_id']) {
        throw new Exception('You do not have permission to edit this post');
    }

    // Sanitize inputs
    $id = (int)$data['id'];
    $title = htmlspecialchars(strip_tags($data['title']));
    $content = $data['content']; // Allow HTML in content
    $metaTitle = isset($data['meta_title']) ? htmlspecialchars(strip_tags($data['meta_title'])) : $title;
    $metaDescription = isset($data['meta_description']) ? htmlspecialchars(strip_tags($data['meta_description'])) : '';
    $featuredImage = isset($data['featured_image']) ? htmlspecialchars(strip_tags($data['featured_image'])) : null;
    $status = isset($data['status']) ? $data['status'] : 'draft';

    // Validate status
    $allowedStatuses = ['draft', 'published', 'archived'];
    if (!in_array($status, $allowedStatuses)) {
        throw new Exception('Invalid status');
    }

    // Prepare statement
    $stmt = $conn->prepare("UPDATE posts SET 
        title = ?, 
        content = ?, 
        meta_title = ?, 
        meta_description = ?, 
        featured_image = ?, 
        status = ?,
        updated_at = NOW()
        WHERE id = ?");
    
    $stmt->bind_param("ssssssi", 
        $title, 
        $content, 
        $metaTitle, 
        $metaDescription, 
        $featuredImage, 
        $status,
        $id
    );
    
    if ($stmt->execute()) {
        // Prepare response
        $response['success'] = true;
        $response['message'] = 'Post updated successfully';
        $response['data'] = [
            'id' => $id,
            'title' => $title,
            'status' => $status
        ];
    } else {
        throw new Exception('Failed to update post');
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