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
            
            // Update status and published_at
            $publishedAt = $status === 'published' ? date('Y-m-d H:i:s') : null;
            $updateQuery = "UPDATE posts SET status = ?, published_at = ?, updated_at = NOW() WHERE id = ?";
            
            $stmt = $conn->prepare($updateQuery);
            if (!$stmt) {
                throw new Exception("Database error during update preparation");
            }
            
            $stmt->bind_param("ssi", $status, $publishedAt, $id);
            if (!$stmt->execute()) {
                throw new Exception("Failed to update status: " . $stmt->error);
            }
        } else {
            // Full post update
            $title = htmlspecialchars(strip_tags($data['title']));
            $content = $data['content'];
            $slug = isset($data['slug']) ? htmlspecialchars(strip_tags($data['slug'])) : strtolower(str_replace(' ', '-', $title));
            $excerpt = isset($data['excerpt']) ? htmlspecialchars(strip_tags($data['excerpt'])) : substr(strip_tags($content), 0, 200);
            $metaTitle = isset($data['meta_title']) ? htmlspecialchars(strip_tags($data['meta_title'])) : null;
            $metaDescription = isset($data['meta_description']) ? htmlspecialchars(strip_tags($data['meta_description'])) : null;
            $featuredImage = isset($data['featured_image']) ? htmlspecialchars(strip_tags($data['featured_image'])) : null;
            $status = isset($data['status']) ? $data['status'] : $post['status'];
            
            // Check if slug is unique (excluding current post)
            $originalSlug = $slug;
            $counter = 1;
            while (true) {
                $stmt = $conn->prepare("SELECT id FROM posts WHERE slug = ? AND id != ?");
                $stmt->bind_param("si", $slug, $id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 0) {
                    break;
                }
                
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            
            // Update published_at if status changes to published
            $publishedAt = $post['published_at'];
            if ($status === 'published' && $post['status'] !== 'published') {
                $publishedAt = date('Y-m-d H:i:s');
            } elseif ($status !== 'published' && $post['status'] === 'published') {
                $publishedAt = null;
            }

            $stmt = $conn->prepare("UPDATE posts SET title = ?, slug = ?, content = ?, excerpt = ?, meta_title = ?, meta_description = ?, featured_image = ?, status = ?, published_at = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("sssssssssi", $title, $slug, $content, $excerpt, $metaTitle, $metaDescription, $featuredImage, $status, $publishedAt, $id);
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
        $stmt = $conn->prepare("SELECT status, published_at FROM posts WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $updatedPost = $result->fetch_assoc();
        
        // Prepare response
        $response['success'] = true;
        $response['message'] = 'Post updated successfully';
        $response['data'] = [
            'id' => $id,
            'status' => $updatedPost['status'],
            'published_at' => $updatedPost['published_at']
        ];

        if (!$isStatusUpdate) {
            $response['data']['title'] = $title;
            $response['data']['slug'] = $slug;
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