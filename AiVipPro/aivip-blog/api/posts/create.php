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
    $metaTitle = isset($data['meta_title']) ? htmlspecialchars(strip_tags($data['meta_title'])) : $title;
    $metaDescription = isset($data['meta_description']) ? htmlspecialchars(strip_tags($data['meta_description'])) : '';
    $featuredImage = isset($data['featured_image']) ? htmlspecialchars(strip_tags($data['featured_image'])) : null;
    $authorId = $_SESSION['user_id'];
    $status = isset($data['status']) ? $data['status'] : 'draft';
    $categories = isset($data['categories']) ? array_map('intval', (array)$data['categories']) : [];

    // Database connection
    $db = new Database();
    $conn = $db->getConnection();

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert post
        $stmt = $conn->prepare("INSERT INTO posts (title, content, meta_title, meta_description, featured_image, author_id, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssssss", $title, $content, $metaTitle, $metaDescription, $featuredImage, $authorId, $status);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create post');
        }

        $postId = $conn->insert_id;

        // If no categories are selected, get the Uncategorized category ID
        if (empty($categories)) {
            $uncatStmt = $conn->prepare("SELECT id FROM categories WHERE slug = 'uncategorized' LIMIT 1");
            $uncatStmt->execute();
            $result = $uncatStmt->get_result();
            if ($uncatRow = $result->fetch_assoc()) {
                $categories = [$uncatRow['id']];
            }
        }

        // Insert categories
        if (!empty($categories)) {
            $values = array_fill(0, count($categories), "($postId, ?)");
            $sql = "INSERT INTO post_categories (post_id, category_id) VALUES " . implode(', ', $values);
            $stmt = $conn->prepare($sql);
            
            $types = str_repeat('i', count($categories));
            $stmt->bind_param($types, ...$categories);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to assign categories');
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
            'status' => $status,
            'categories' => $categories
        ];

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