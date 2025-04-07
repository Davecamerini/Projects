<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Allow access from all origins
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header('Content-Type: application/json');
require_once '../../config/database.php';

// Initialize response array
$response = ['success' => false, 'message' => '', 'data' => null];

try {
    // Get query parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;
    $category = isset($_GET['category']) ? $_GET['category'] : null;
    $search = isset($_GET['search']) ? $_GET['search'] : '';

    // Validate parameters
    if ($page < 1) $page = 1;
    if ($limit < 1) $limit = 1;
    if ($limit > 50) $limit = 50;

    // Calculate offset
    $offset = ($page - 1) * $limit;

    // Database connection
    $db = new Database();
    $conn = $db->getConnection();

    // Build base query
    $query = "SELECT 
                p.id,
                p.slug,
                p.featured_image,
                p.published_at,
                p.title,
                p.excerpt,
                GROUP_CONCAT(c.name) as categories
            FROM posts p
            LEFT JOIN post_categories pc ON p.id = pc.post_id
            LEFT JOIN categories c ON pc.category_id = c.id
            WHERE p.status = 'published'";

    $countQuery = "SELECT COUNT(DISTINCT p.id) as total 
                   FROM posts p
                   LEFT JOIN post_categories pc ON p.id = pc.post_id
                   LEFT JOIN categories c ON pc.category_id = c.id
                   WHERE p.status = 'published'";

    $params = [];
    $types = "";

    // Add category filter
    if ($category) {
        $query .= " AND c.slug = ?";
        $countQuery .= " AND c.slug = ?";
        $params[] = $category;
        $types .= "s";
    }

    // Add search filter
    if ($search) {
        $searchTerm = "%$search%";
        $query .= " AND (p.title LIKE ? OR p.excerpt LIKE ?)";
        $countQuery .= " AND (p.title LIKE ? OR p.excerpt LIKE ?)";
        $params = array_merge($params, [$searchTerm, $searchTerm]);
        $types .= "ss";
    }

    // Group by post
    $query .= " GROUP BY p.id";

    // Add sorting
    $query .= " ORDER BY p.published_at DESC";

    // Add pagination
    $query .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    // Debug information
    $debug = [
        'query' => $query,
        'countQuery' => $countQuery,
        'params' => $params,
        'types' => $types
    ];

    // Get total count
    $countParams = array_slice($params, 0, -2); // Remove pagination params
    $countTypes = substr($types, 0, -2); // Remove pagination types
    
    $stmt = $conn->prepare($countQuery);
    if (!empty($countParams)) {
        $stmt->bind_param($countTypes, ...$countParams);
    }
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];

    // Get posts
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $posts = [];
    while ($row = $result->fetch_assoc()) {
        // Format categories
        $row['categories'] = $row['categories'] ? explode(',', $row['categories']) : [];
        
        // Format date
        $row['published_at'] = date('Y-m-d', strtotime($row['published_at']));
        
        $posts[] = $row;
    }

    // Prepare response
    $response['success'] = true;
    $response['data'] = [
        'posts' => $posts,
        'total' => $total,
        'page' => $page,
        'limit' => $limit,
        'total_pages' => ceil($total / $limit)
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    $response['debug'] = isset($debug) ? $debug : null;
    error_log("Grid API Error: " . $e->getMessage());
} finally {
    if (isset($db)) {
        $db->closeConnection();
    }
    echo json_encode($response);
}
?> 