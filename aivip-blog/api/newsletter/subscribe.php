<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

// Initialize response array
$response = ['success' => false, 'message' => '', 'data' => null];

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (!isset($data['nome_cognome']) || !isset($data['email']) || !isset($data['privacy']) || !isset($data['url_invio'])) {
        throw new Exception('Missing required fields');
    }

    // Validate email format
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Validate privacy checkbox
    if (!$data['privacy']) {
        throw new Exception('Privacy policy must be accepted');
    }

    // Sanitize inputs
    $nome_cognome = htmlspecialchars(strip_tags($data['nome_cognome']));
    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
    $privacy = (bool)$data['privacy'];
    $url_invio = htmlspecialchars(strip_tags($data['url_invio']));
    $preferenza_invio = isset($data['preferenza_invio']) ? htmlspecialchars(strip_tags($data['preferenza_invio'])) : '';

    // Database connection
    $db = new Database();
    $conn = $db->getConnection();

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM newsletter WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        throw new Exception('Email already subscribed');
    }

    // Insert new subscription
    $stmt = $conn->prepare("INSERT INTO newsletter (nome_cognome, email, privacy, url_invio, preferenza_invio) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $nome_cognome, $email, $privacy, $url_invio, $preferenza_invio);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to save subscription');
    }

    // Prepare response
    $response['success'] = true;
    $response['message'] = 'Successfully subscribed to newsletter';
    $response['data'] = [
        'id' => $conn->insert_id,
        'email' => $email,
        'nome_cognome' => $nome_cognome
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