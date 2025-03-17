<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

// Initialize response array
$response = ['success' => false, 'message' => '', 'data' => null];

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['username']) || !isset($data['password'])) {
        throw new Exception('Username and password are required');
    }

    $username = $data['username'];
    $password = $data['password'];

    // Database connection
    $db = new Database();
    $conn = $db->getConnection();

    // Prepare statement
    $stmt = $conn->prepare("SELECT id, username, role, password FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Invalid credentials');
    }

    $user = $result->fetch_assoc();

    // Verify password
    if (!password_verify($password, $user['password'])) {
        throw new Exception('Invalid credentials');
    }

    // Start session
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];

    // Prepare response
    $response['success'] = true;
    $response['message'] = 'Login successful';
    $response['data'] = [
        'id' => $user['id'],
        'username' => $user['username'],
        'role' => $user['role']
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