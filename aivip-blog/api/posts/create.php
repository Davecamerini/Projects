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
    $metaTitle = isset($data['meta_title']) ? htmlspecialchars(strip_tags($data['meta_title'])) : null;
    $metaDescription = isset($data['meta_description']) ? htmlspecialchars(strip_tags($data['meta_description'])) : null;
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

    // Check if slug is unique, if not append a number
    $originalSlug = $slug;
    $counter = 1;
    while (true) {
        $stmt = $conn->prepare("SELECT id FROM posts WHERE slug = ?");
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            break;
        }
        
        $slug = $originalSlug . '-' . $counter;
        $counter++;
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert post
        $stmt = $conn->prepare("INSERT INTO posts (title, slug, content, excerpt, meta_title, meta_description, featured_image, author_id, status, published_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        // Set published_at to NOW() if status is published, otherwise NULL
        $publishedAt = $status === 'published' ? date('Y-m-d H:i:s') : null;
        
        $stmt->bind_param("ssssssssss", $title, $slug, $content, $excerpt, $metaTitle, $metaDescription, $featuredImage, $authorId, $status, $publishedAt);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create post');
        }

        $postId = $conn->insert_id;

        // Insert categories if any, otherwise use Uncategorized
        if (!empty($categories)) {
            $stmt = $conn->prepare("INSERT INTO post_categories (post_id, category_id) VALUES (?, ?)");
            foreach ($categories as $categoryId) {
                $stmt->bind_param("ii", $postId, $categoryId);
                if (!$stmt->execute()) {
                    throw new Exception('Failed to add categories');
                }
            }
        } else {
            // Get the Uncategorized category ID
            $stmt = $conn->prepare("SELECT id FROM categories WHERE slug = 'uncategorized'");
            $stmt->execute();
            $result = $stmt->get_result();
            $uncategorizedCategory = $result->fetch_assoc();
            
            if (!$uncategorizedCategory) {
                throw new Exception('Uncategorized category not found');
            }

            // Assign to Uncategorized if no categories specified
            $stmt = $conn->prepare("INSERT INTO post_categories (post_id, category_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $postId, $uncategorizedCategory['id']);
            if (!$stmt->execute()) {
                throw new Exception('Failed to add Uncategorized category');
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