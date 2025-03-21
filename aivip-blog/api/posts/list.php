<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
require_once '../../config/database.php';

// Initialize response array
$response = ['success' => false, 'message' => '', 'data' => null];

try {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized access - Please login first');
    }

    // Get and validate query parameters
    $params = [
        'page' => isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1,
        'limit' => isset($_GET['limit']) ? min(50, max(1, (int)$_GET['limit'])) : 10,
        'status' => isset($_GET['status']) ? $_GET['status'] : null,
        'search' => isset($_GET['search']) ? trim($_GET['search']) : null,
        'category' => isset($_GET['category']) ? (int)$_GET['category'] : null,
        'author' => isset($_GET['author']) ? (int)$_GET['author'] : null,
        'sort' => isset($_GET['sort']) ? $_GET['sort'] : 'created_at',
        'order' => isset($_GET['order']) ? strtoupper($_GET['order']) : 'DESC'
    ];

    // Validate status if provided
    if ($params['status'] && !in_array($params['status'], ['draft', 'published', 'archived'])) {
        throw new Exception('Invalid status value. Must be draft, published, or archived');
    }

    // Validate sort field
    $allowedSortFields = ['created_at', 'updated_at', 'published_at', 'title'];
    if (!in_array($params['sort'], $allowedSortFields)) {
        $params['sort'] = 'created_at';
    }

    // Validate order
    if (!in_array($params['order'], ['ASC', 'DESC'])) {
        $params['order'] = 'DESC';
    }

    // Calculate offset
    $offset = ($params['page'] - 1) * $params['limit'];

    // Database connection
    $db = new Database();
    $conn = $db->getConnection();

    // Build base query with all necessary joins
    $baseQuery = "FROM posts p 
                  JOIN users u ON p.author_id = u.id 
                  LEFT JOIN post_categories pc ON p.id = pc.post_id 
                  LEFT JOIN categories c ON pc.category_id = c.id 
                  WHERE 1=1";

    // Build WHERE conditions
    $conditions = [];
    $queryParams = [];
    $types = "";

    // Status filter
    if ($params['status']) {
        $conditions[] = "p.status = ?";
        $queryParams[] = $params['status'];
        $types .= "s";
    }

    // Search filter
    if ($params['search']) {
        $searchTerm = "%{$params['search']}%";
        $conditions[] = "(p.title LIKE ? OR p.content LIKE ? OR p.excerpt LIKE ?)";
        $queryParams = array_merge($queryParams, [$searchTerm, $searchTerm, $searchTerm]);
        $types .= "sss";
    }

    // Category filter
    if ($params['category']) {
        $conditions[] = "c.id = ?";
        $queryParams[] = $params['category'];
        $types .= "i";
    }

    // Author filter
    if ($params['author']) {
        $conditions[] = "p.author_id = ?";
        $queryParams[] = $params['author'];
        $types .= "i";
    }

    // Non-admin users can only see their own posts
    if ($_SESSION['role'] !== 'admin') {
        $conditions[] = "p.author_id = ?";
        $queryParams[] = $_SESSION['user_id'];
        $types .= "i";
    }

    // Add conditions to base query
    if (!empty($conditions)) {
        $baseQuery .= " AND " . implode(" AND ", $conditions);
    }

    // Get total count
    $countQuery = "SELECT COUNT(DISTINCT p.id) as total " . $baseQuery;
    $countStmt = $conn->prepare($countQuery);
    if (!empty($queryParams)) {
        $countStmt->bind_param($types, ...$queryParams);
    }
    $countStmt->execute();
    $total = $countStmt->get_result()->fetch_assoc()['total'];

    // Get posts with categories
    $query = "SELECT DISTINCT 
                     p.id, p.title, p.content, p.featured_image,
                     p.status, p.created_at, p.updated_at,
                     u.id as author_id, u.username as author_name, u.first_name, u.last_name,
                     GROUP_CONCAT(DISTINCT CONCAT(c.id, ':', c.name, ':', c.slug) ORDER BY c.name) as categories
              " . $baseQuery . "
              GROUP BY p.id, p.title, p.content, p.featured_image,
                       p.status, p.created_at, p.updated_at,
                       u.id, u.username, u.first_name, u.last_name
              ORDER BY p.{$params['sort']} {$params['order']}
              LIMIT ? OFFSET ?";

    $queryParams[] = $params['limit'];
    $queryParams[] = $offset;
    $types .= "ii";

    $stmt = $conn->prepare($query);
    if (!empty($queryParams)) {
        $stmt->bind_param($types, ...$queryParams);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $posts = [];
    while ($row = $result->fetch_assoc()) {
        // Process categories
        $categories = [];
        if ($row['categories']) {
            foreach (explode(',', $row['categories']) as $cat) {
                list($id, $name, $slug) = explode(':', $cat);
                $categories[] = [
                    'id' => (int)$id,
                    'name' => $name,
                    'slug' => $slug
                ];
            }
        }

        $posts[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'content' => $row['content'],
            'featured_image' => $row['featured_image'],
            'status' => $row['status'],
            'author' => [
                'id' => $row['author_id'],
                'username' => $row['author_name'],
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name']
            ],
            'categories' => $categories,
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
            'page' => $params['page'],
            'limit' => $params['limit'],
            'total_pages' => ceil($total / $params['limit'])
        ],
        'filters' => [
            'status' => $params['status'],
            'category' => $params['category'],
            'author' => $params['author'],
            'search' => $params['search']
        ],
        'sort' => [
            'field' => $params['sort'],
            'order' => $params['order']
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