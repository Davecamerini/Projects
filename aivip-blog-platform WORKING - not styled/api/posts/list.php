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

    // Get query parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $status = isset($_GET['status']) ? $_GET['status'] : null;
    $search = isset($_GET['search']) ? $_GET['search'] : null;

    // Validate page and limit
    if ($page < 1) $page = 1;
    if ($limit < 1) $limit = 10;
    if ($limit > 50) $limit = 50;

    $offset = ($page - 1) * $limit;

    // Database connection
    $db = new Database();
    $conn = $db->getConnection();

    // Build query
    $query = "SELECT p.*, u.username as author_name 
              FROM posts p 
              JOIN users u ON p.author_id = u.id 
              WHERE 1=1";
    $countQuery = "SELECT COUNT(*) as total FROM posts p WHERE 1=1";
    $params = [];
    $types = "";

    // Add status filter if provided
    if ($status) {
        $query .= " AND p.status = ?";
        $countQuery .= " AND p.status = ?";
        $params[] = $status;
        $types .= "s";
    }

    // Add search filter if provided
    if ($search) {
        $searchTerm = "%$search%";
        $query .= " AND (p.title LIKE ? OR p.content LIKE ?)";
        $countQuery .= " AND (p.title LIKE ? OR p.content LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "ss";
    }

    // If user is not admin, show only their posts
    if ($_SESSION['role'] !== 'admin') {
        $query .= " AND p.author_id = ?";
        $countQuery .= " AND p.author_id = ?";
        $params[] = $_SESSION['user_id'];
        $types .= "i";
    }

    // Add ordering
    $query .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    // Get total count
    $countStmt = $conn->prepare($countQuery);
    if (!empty($params)) {
        $countParams = array_slice($params, 0, -2);
        $countTypes = substr($types, 0, -2);
        $countStmt->bind_param($countTypes, ...$countParams);
    }
    $countStmt->execute();
    $totalResult = $countStmt->get_result()->fetch_assoc();
    $total = $totalResult['total'];

    // Get posts
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $posts = [];
    while ($row = $result->fetch_assoc()) {
        $posts[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'meta_title' => $row['meta_title'],
            'meta_description' => $row['meta_description'],
            'featured_image' => $row['featured_image'],
            'status' => $row['status'],
            'author_name' => $row['author_name'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        ];
    }

    // Prepare response
    $response['success'] = true;
    $response['data'] = [
        'posts' => $posts,
        'pagination' => [
            'total' => (int)$total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ]
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    if (isset($db)) {
        $db->closeConnection();
    }
    echo json_encode($response);
}
?> 