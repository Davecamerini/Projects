<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

// Initialize response array
$response = ['success' => false, 'message' => '', 'data' => null];

try {
    session_start();
    
    // Check if user is logged in and is admin
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        throw new Exception('Unauthorized access');
    }

    // Get query parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
    $order = isset($_GET['order']) ? strtoupper($_GET['order']) : 'DESC';

    // Validate parameters
    if ($page < 1) $page = 1;
    if ($limit < 1) $limit = 1;
    if ($limit > 50) $limit = 50;
    if (!in_array($sort, ['nome_cognome', 'email', 'created_at'])) $sort = 'created_at';
    if (!in_array($order, ['ASC', 'DESC'])) $order = 'DESC';

    // Calculate offset
    $offset = ($page - 1) * $limit;

    // Database connection
    $db = new Database();
    $conn = $db->getConnection();

    // Build query
    $where = '';
    $params = [];
    $types = '';

    if (!empty($search)) {
        $where = "WHERE nome_cognome LIKE ? OR email LIKE ?";
        $searchTerm = "%$search%";
        $params = [$searchTerm, $searchTerm];
        $types = 'ss';
    }

    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM newsletter $where";
    $stmt = $conn->prepare($countQuery);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];

    // Get subscriptions
    $query = "SELECT * FROM newsletter $where ORDER BY $sort $order LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $types .= 'ii';
        $params[] = $limit;
        $params[] = $offset;
        $stmt->bind_param($types, ...$params);
    } else {
        $stmt->bind_param('ii', $limit, $offset);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $subscriptions = [];
    while ($row = $result->fetch_assoc()) {
        // Convert preferenza_invio to boolean
        $row['preferenza_invio'] = (bool)$row['preferenza_invio'];
        $subscriptions[] = $row;
    }

    // Prepare response
    $response['success'] = true;
    $response['data'] = [
        'subscriptions' => $subscriptions,
        'total' => $total,
        'page' => $page,
        'limit' => $limit,
        'total_pages' => ceil($total / $limit)
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