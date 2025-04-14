<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

// Initialize response array
$response = ['success' => false, 'message' => ''];

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (!isset($data['id']) || !isset($data['action'])) {
        throw new Exception('ID and action are required');
    }

    // Validate action
    if (!in_array($data['action'], ['check', 'reset'])) {
        throw new Exception('Invalid action');
    }

    // Database connection
    $db = new Database();
    $conn = $db->getConnection();

    // Update the check status
    $checkValue = $data['action'] === 'check' ? 1 : 0;
    
    // If we're checking (setting to true), update the last_update timestamp
    if ($data['action'] === 'check') {
        $stmt = $conn->prepare("UPDATE digital_analysis SET `check` = ?, last_update = NOW() WHERE id = ?");
    } else {
        // When resetting, clear the last_update field
        $stmt = $conn->prepare("UPDATE digital_analysis SET `check` = ?, last_update = NULL WHERE id = ?");
    }
    
    $stmt->bind_param("ii", $checkValue, $data['id']);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update status');
    }

    $response['success'] = true;
    $response['message'] = 'Status updated successfully';

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    if (isset($db)) {
        $db->closeConnection();
    }
    echo json_encode($response);
}
?> 