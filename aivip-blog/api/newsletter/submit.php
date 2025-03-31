<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include database configuration
require_once __DIR__ . '/../../config/database.php';

// Function to validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required_fields = ['nome_cognome', 'email', 'privacy', 'url_invio', 'preferenza_invio'];
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing required field: $field"]);
        exit;
    }
}

// Validate email
if (!isValidEmail($data['email'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email format']);
    exit;
}

// Validate privacy checkbox
if ($data['privacy'] !== true) {
    http_response_code(400);
    echo json_encode(['error' => 'Privacy policy must be accepted']);
    exit;
}

// Validate preferenza_invio
$valid_preferences = ['email', 'sms', 'both']; // Add or modify these based on your needs
if (!in_array($data['preferenza_invio'], $valid_preferences)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid preference for sending']);
    exit;
}

try {
    // Check if email already exists
    $check_stmt = $pdo->prepare("SELECT id FROM newsletter WHERE email = :email");
    $check_stmt->execute([':email' => sanitizeInput($data['email'])]);
    
    if ($check_stmt->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'This email is already subscribed to the newsletter']);
        exit;
    }

    // Prepare the SQL statement
    $stmt = $pdo->prepare("
        INSERT INTO newsletter (
            nome_cognome, 
            email, 
            privacy, 
            url_invio,
            preferenza_invio
        ) VALUES (
            :nome_cognome,
            :email,
            :privacy,
            :url_invio,
            :preferenza_invio
        )
    ");

    // Bind parameters and execute
    $stmt->execute([
        ':nome_cognome' => sanitizeInput($data['nome_cognome']),
        ':email' => sanitizeInput($data['email']),
        ':privacy' => $data['privacy'],
        ':url_invio' => sanitizeInput($data['url_invio']),
        ':preferenza_invio' => sanitizeInput($data['preferenza_invio'])
    ]);

    // Get the ID of the inserted record
    $id = $pdo->lastInsertId();

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Newsletter subscription successful',
        'id' => $id
    ]);

} catch (PDOException $e) {
    // Log the error (in a production environment, you'd want to log this properly)
    error_log("Database error: " . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'error' => 'An error occurred while subscribing to the newsletter'
    ]);
} 