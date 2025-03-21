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
    if (!isset($data['title']) || !isset($data['content'])) {
        throw new Exception('Title and content are required');
    }

    // Sanitize inputs
    $title = htmlspecialchars(strip_tags($data['title']));
    $content = $data['content']; // Allow HTML in content
    $slug = isset($data['slug']) ? htmlspecialchars(strip_tags($data['slug'])) : strtolower(str_replace(' ', '-', $title));
    $excerpt = isset($data['excerpt']) ? htmlspecialchars(strip_tags($data['excerpt'])) : substr(strip_tags($content), 0, 200);
    $featuredImage = isset($data['featured_image']) ? htmlspecialchars(strip_tags($data['featured_image'])) : null;
    $authorId = $_SESSION['user_id'];
    $status = isset($data['status']) ? $data['status'] : 'draft';
    $categories = isset($data['categories']) ? $data['categories'] : [];

    // Validate status
    if (!in_array($status, ['draft', 'published', 'archived'])) {
        throw new Exception('Invalid status value');
    }

    // Database connection
    $db = new Database();
    $conn = $db->getConnection();

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert post
        $stmt = $conn->prepare("INSERT INTO posts (title, slug, content, excerpt, featured_image, author_id, status, published_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        // Set published_at to NOW() if status is published, otherwise NULL
        $publishedAt = $status === 'published' ? date('Y-m-d H:i:s') : null;
        
        $stmt->bind_param("ssssssss", $title, $slug, $content, $excerpt, $featuredImage, $authorId, $status, $publishedAt);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create post');
        }

        $postId = $conn->insert_id;

        // Insert categories if any
        if (!empty($categories)) {
            $stmt = $conn->prepare("INSERT INTO post_categories (post_id, category_id) VALUES (?, ?)");
            foreach ($categories as $categoryId) {
                $stmt->bind_param("ii", $postId, $categoryId);
                if (!$stmt->execute()) {
                    throw new Exception('Failed to add categories');
                }
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        // Prepare response
        $response['success'] = true;
        $response['message'] = 'Post created successfully';
        $response['data'] = [
            'id' => $postId,
            'title' => $title,
            'slug' => $slug,
            'status' => $status,
            'published_at' => $publishedAt
        ];
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