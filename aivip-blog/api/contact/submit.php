<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

// Initialize response array
$response = ['success' => false, 'message' => '', 'data' => null];

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (!isset($data['nome_cognome']) || !isset($data['email']) || !isset($data['telefono']) || 
        !isset($data['ragione_sociale']) || !isset($data['messaggio']) || !isset($data['privacy']) || 
        !isset($data['url_invio'])) {
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
    $telefono = htmlspecialchars(strip_tags($data['telefono']));
    $ragione_sociale = htmlspecialchars(strip_tags($data['ragione_sociale']));
    $messaggio = htmlspecialchars(strip_tags($data['messaggio']));
    $privacy = (bool)$data['privacy'];
    $url_invio = htmlspecialchars(strip_tags($data['url_invio']));

    // Database connection
    $db = new Database();
    $conn = $db->getConnection();

    // Insert new submission
    $stmt = $conn->prepare("INSERT INTO contact_form (nome_cognome, email, telefono, ragione_sociale, messaggio, privacy, url_invio) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $nome_cognome, $email, $telefono, $ragione_sociale, $messaggio, $privacy, $url_invio);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to save submission');
    }

    // Prepare response
    $response['success'] = true;
    $response['message'] = 'Successfully submitted contact form';
    $response['data'] = [
        'id' => $conn->insert_id,
        'email' => $email,
        'nome_cognome' => $nome_cognome,
        'ragione_sociale' => $ragione_sociale
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