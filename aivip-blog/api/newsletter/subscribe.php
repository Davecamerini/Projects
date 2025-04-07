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
    $preferenza_invio = isset($data['preferenza_invio']) ? (bool)$data['preferenza_invio'] : false;

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
    $stmt->bind_param("ssssi", $nome_cognome, $email, $privacy, $url_invio, $preferenza_invio);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to save subscription');
    }

    // Send welcome email
    $mail = new Mail();
    $subject = "Benvenuto nella Newsletter di AIVIP Blog";
    
    $body = "
    <html>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2 style='color: #efc064;'>Benvenuto nella Newsletter di AIVIP Blog!</h2>
            <p>Ciao {$nome_cognome},</p>
            <p>Grazie per esserti iscritto alla nostra newsletter. Siamo felici di averti con noi!</p>
            
            <div style='background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;'>
                <h3 style='color: #efc064; margin-top: 0;'>Dettagli della tua iscrizione:</h3>
                <p><strong>Nome:</strong> {$nome_cognome}</p>
                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Data di iscrizione:</strong> " . date('j F Y') . "</p>
            </div>
            
            <p>Riceverai i nostri ultimi aggiornamenti, notizie e contenuti esclusivi direttamente nella tua casella di posta.</p>
            
            <p>Se hai domande o hai bisogno di assistenza, non esitare a contattarci.</p>
            
            <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
            <p style='color: #666; font-size: 12px;'>
                Questa è un'email automatica da AIVIP Blog. Si prega di non rispondere.
            </p>
        </div>
    </body>
    </html>";

    $mail->send($email, $subject, $body);

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