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
    if (!isset($data['id'])) {
        throw new Exception('Post ID is required');
    }

    // Check if this is a status-only update
    $isStatusUpdate = isset($data['status']) && count($data) === 2;

    if (!$isStatusUpdate && (!isset($data['title']) || !isset($data['content']))) {
        throw new Exception('Title and content are required');
    }

    // Validate status value
    if (isset($data['status']) && !in_array($data['status'], ['draft', 'published', 'archived'])) {
        throw new Exception('Invalid status value. Must be draft, published, or archived');
    }

    // Sanitize inputs
    $id = (int)$data['id'];
    
    // Database connection
    $db = new Database();
    $conn = $db->getConnection();

    // Check if user has permission to edit this post
    $stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Post not found');
    }
    
    $post = $result->fetch_assoc();
    if ($post['author_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
        throw new Exception('Unauthorized to edit this post');
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        if ($isStatusUpdate) {
            // Only update the status
            $status = $data['status'];
            error_log("Attempting status update - ID: " . $id . ", Status: " . $status);
            
            // Validate status value
            $validStatuses = ['draft', 'published', 'archived'];
            if (!in_array($status, $validStatuses)) {
                throw new Exception("Invalid status value: " . $status);
            }
            
            // First verify the post exists and get current status
            $stmt = $conn->prepare("SELECT status FROM posts WHERE id = ?");
            if (!$stmt) {
                error_log("Prepare failed for select: " . $conn->error);
                throw new Exception("Database error during status check");
            }
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                error_log("Execute failed for select: " . $stmt->error);
                throw new Exception("Failed to check current status");
            }
            $result = $stmt->get_result();
            $currentPost = $result->fetch_assoc();
            error_log("Current post status: " . ($currentPost['status'] ?? 'null'));
            
            // Now update the status
            $updateQuery = "UPDATE posts SET status = ?, updated_at = NOW() WHERE id = ?";
            error_log("Update query: " . $updateQuery);
            
            $stmt = $conn->prepare($updateQuery);
            if (!$stmt) {
                error_log("Prepare failed for update: " . $conn->error);
                throw new Exception("Database error during update preparation");
            }
            
            $stmt->bind_param("si", $status, $id);
            if (!$stmt->execute()) {
                error_log("Execute failed for update: " . $stmt->error);
                throw new Exception("Failed to update status: " . $stmt->error);
            }
            
            error_log("Rows affected: " . $stmt->affected_rows);
            
            if ($stmt->affected_rows === 0 && $currentPost['status'] !== $status) {
                error_log("No rows were updated but status was different");
                throw new Exception("Failed to update status");
            }
        } else {
            // Full post update
            $title = htmlspecialchars(strip_tags($data['title']));
            $content = $data['content'];
            $metaTitle = isset($data['meta_title']) ? htmlspecialchars(strip_tags($data['meta_title'])) : $title;
            $metaDescription = isset($data['meta_description']) ? htmlspecialchars(strip_tags($data['meta_description'])) : '';
            $featuredImage = isset($data['featured_image']) ? htmlspecialchars(strip_tags($data['featured_image'])) : null;
            $status = isset($data['status']) ? $data['status'] : $post['status'];

            $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ?, meta_title = ?, meta_description = ?, featured_image = ?, status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("ssssssi", $title, $content, $metaTitle, $metaDescription, $featuredImage, $status, $id);
        }
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update post: ' . $stmt->error);
        }

        // Only handle categories for full updates
        if (!$isStatusUpdate && isset($data['categories'])) {
            $categories = array_map('intval', (array)$data['categories']);

            // Delete existing category relationships
            $stmt = $conn->prepare("DELETE FROM post_categories WHERE post_id = ?");
            $stmt->bind_param("i", $id);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to update categories');
            }

            // Insert new categories
            if (!empty($categories)) {
                $values = array_fill(0, count($categories), "($id, ?)");
                $sql = "INSERT INTO post_categories (post_id, category_id) VALUES " . implode(', ', $values);
                $stmt = $conn->prepare($sql);
                
                $types = str_repeat('i', count($categories));
                $stmt->bind_param($types, ...$categories);
                
                if (!$stmt->execute()) {
                    throw new Exception('Failed to assign categories');
                }
            }
        }

        // Commit transaction
        $conn->commit();
        
        // Get the updated post data
        $stmt = $conn->prepare("SELECT status FROM posts WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $updatedPost = $result->fetch_assoc();
        
        // Prepare response
        $response['success'] = true;
        $response['message'] = 'Post updated successfully';
        $response['data'] = [
            'id' => $id,
            'status' => $updatedPost['status']
        ];

        if (!$isStatusUpdate) {
            $response['data']['title'] = $title;
            if (isset($categories)) {
                $response['data']['categories'] = $categories;
            }
        }

    } catch (Exception $e) {
        // Rollback on error
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