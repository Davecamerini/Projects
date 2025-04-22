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
$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

try {
    // Get JSON data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data');
    }

    // Get data from JSON
    $website = isset($data['website']) ? trim($data['website']) : '';
    $email = isset($data['email']) ? trim($data['email']) : '';
    $privacy = isset($data['privacy']) ? (bool)$data['privacy'] : false;

    // Get the referrer URL
    $url_invio = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

    // Validate input
    if (empty($website)) {
        throw new Exception('Website URL is required');
    }

    if (empty($email)) {
        throw new Exception('Email is required');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    if (!$privacy) {
        throw new Exception('Privacy consent is required');
    }

    // Database connection
    $db = new Database();
    $conn = $db->getConnection();

    // Prepare the query
    $query = "INSERT INTO digital_analysis (website, email, privacy, url_invio) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssis', $website, $email, $privacy, $url_invio);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to save analysis request: ' . $stmt->error);
    }

    // Get the inserted record
    $analysisId = $stmt->insert_id;
    $query = "SELECT * FROM digital_analysis WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $analysisId);
    $stmt->execute();
    $result = $stmt->get_result();
    $analysis = $result->fetch_assoc();

    // Send email notification
    $mail = new Mail();
    $subject = "New Digital Analysis Request - AIVIP Blog";
    
    $body = "
    <html>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2 style='color: #007bff;'>New Digital Analysis Request</h2>
            <p>A new digital analysis has been requested with the following details:</p>
            
            <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                <tr>
                    <td style='padding: 8px; border: 1px solid #ddd; background: #f8f9fa; width: 30%;'><strong>Website:</strong></td>
                    <td style='padding: 8px; border: 1px solid #ddd;'>{$website}</td>
                </tr>
                <tr>
                    <td style='padding: 8px; border: 1px solid #ddd; background: #f8f9fa;'><strong>Email:</strong></td>
                    <td style='padding: 8px; border: 1px solid #ddd;'>{$email}</td>
                </tr>
                <tr>
                    <td style='padding: 8px; border: 1px solid #ddd; background: #f8f9fa;'><strong>Privacy Accepted:</strong></td>
                    <td style='padding: 8px; border: 1px solid #ddd;'>" . ($privacy ? 'Yes' : 'No') . "</td>
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

    $mail->send('seo@aivippro.it', $subject, $body);

    // Format the response
    $response['success'] = true;
    $response['message'] = 'Analysis request submitted successfully';
    $response['data'] = [
        'id' => $analysis['id'],
        'website' => $analysis['website'],
        'email' => $analysis['email'],
        'privacy' => (bool)$analysis['privacy'],
        'url_invio' => $analysis['url_invio'],
        'timestamp' => $analysis['timestamp']
    ];

    // Close the database connection
    $db->closeConnection();

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Output the response
echo json_encode($response); 