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
$required_fields = ['nome_cognome', 'email', 'telefono', 'ragione_sociale', 'messaggio', 'privacy', 'url_invio'];
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

try {
    // Prepare the SQL statement
    $stmt = $pdo->prepare("
        INSERT INTO contact_form (
            nome_cognome, 
            email, 
            telefono, 
            ragione_sociale, 
            messaggio, 
            privacy, 
            url_invio
        ) VALUES (
            :nome_cognome,
            :email,
            :telefono,
            :ragione_sociale,
            :messaggio,
            :privacy,
            :url_invio
        )
    ");

    // Bind parameters and execute
    $stmt->execute([
        ':nome_cognome' => sanitizeInput($data['nome_cognome']),
        ':email' => sanitizeInput($data['email']),
        ':telefono' => sanitizeInput($data['telefono']),
        ':ragione_sociale' => sanitizeInput($data['ragione_sociale']),
        ':messaggio' => sanitizeInput($data['messaggio']),
        ':privacy' => $data['privacy'],
        ':url_invio' => sanitizeInput($data['url_invio'])
    ]);

    // Get the ID of the inserted record
    $id = $pdo->lastInsertId();

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Contact form submitted successfully',
        'id' => $id
    ]);

} catch (PDOException $e) {
    // Log the error (in a production environment, you'd want to log this properly)
    error_log("Database error: " . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'error' => 'An error occurred while submitting the form'
    ]);
} 