<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

try {
    // Get post slug from query parameter
    $postSlug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
    
    if (empty($postSlug)) {
        throw new Exception('Post slug is required');
    }

    // Database connection
    $db = new Database();
    $conn = $db->getConnection();

    // Prepare the query to get post data with author name and categories
    $query = "SELECT 
                p.id,
                p.title,
                p.slug,
                p.content,
                p.featured_image,
                p.published_at,
                u.username as author_name,
                GROUP_CONCAT(c.name) as categories
              FROM posts p
              LEFT JOIN users u ON p.author_id = u.id
              LEFT JOIN post_categories pc ON p.id = pc.post_id
              LEFT JOIN categories c ON pc.category_id = c.id
              WHERE p.slug = ?
              GROUP BY p.id";

    // Prepare and execute the query
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $postSlug);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Post not found');
    }

    // Fetch the post data
    $post = $result->fetch_assoc();

    // Format the categories into an array
    $post['categories'] = $post['categories'] ? explode(',', $post['categories']) : [];

    // Format the response
    $response['success'] = true;
    $response['data'] = [
        'id' => $post['id'],
        'title' => $post['title'],
        'slug' => $post['slug'],
        'content' => $post['content'],
        'featured_image' => $post['featured_image'],
        'author_name' => $post['author_name'],
        'categories' => $post['categories'],
        'published_at' => $post['published_at']
    ];

    // Close the database connection
    $db->closeConnection();

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Output the response
echo json_encode($response); 