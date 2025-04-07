<?php
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
require_once '../../includes/Mail.php';

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

    // Send email notification
    $mail = new Mail();
    $subject = "New Contact Form Submission - AIVIP Blog";
    
    $body = "
    <html>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2 style='color: #007bff;'>New Contact Form Submission</h2>
            <p>A new contact form has been submitted with the following details:</p>
            
            <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                <tr>
                    <td style='padding: 8px; border: 1px solid #ddd; background: #f8f9fa; width: 30%;'><strong>Name:</strong></td>
                    <td style='padding: 8px; border: 1px solid #ddd;'>{$nome_cognome}</td>
                </tr>
                <tr>
                    <td style='padding: 8px; border: 1px solid #ddd; background: #f8f9fa;'><strong>Email:</strong></td>
                    <td style='padding: 8px; border: 1px solid #ddd;'>{$email}</td>
                </tr>
                <tr>
                    <td style='padding: 8px; border: 1px solid #ddd; background: #f8f9fa;'><strong>Phone:</strong></td>
                    <td style='padding: 8px; border: 1px solid #ddd;'>{$telefono}</td>
                </tr>
                <tr>
                    <td style='padding: 8px; border: 1px solid #ddd; background: #f8f9fa;'><strong>Company:</strong></td>
                    <td style='padding: 8px; border: 1px solid #ddd;'>{$ragione_sociale}</td>
                </tr>
                <tr>
                    <td style='padding: 8px; border: 1px solid #ddd; background: #f8f9fa;'><strong>Message:</strong></td>
                    <td style='padding: 8px; border: 1px solid #ddd;'>{$messaggio}</td>
                </tr>
                <tr>
                    <td style='padding: 8px; border: 1px solid #ddd; background: #f8f9fa;'><strong>Submitted from:</strong></td>
                    <td style='padding: 8px; border: 1px solid #ddd;'>{$url_invio}</td>
                </tr>
            </table>
            
            <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
            <p style='color: #666; font-size: 12px;'>
                This is an automated email from AIVIP Blog. Please do not reply.
            </p>
        </div>
    </body>
    </html>";

    $mail->send('supportotecnico@aivippro.it', $subject, $body);

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