<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

// Initialize response array
$response = ['success' => false, 'message' => '', 'data' => null];

try {
    session_start();
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized access');
    }

    // Get and validate input
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['id']) || !is_numeric($input['id'])) {
        throw new Exception('Invalid media ID');
    }

    $mediaId = (int)$input['id'];

    // Database connection
    $db = new Database();
    $conn = $db->getConnection();

    // First, get the media info to check ownership and get file path
    $stmt = $conn->prepare("SELECT * FROM media WHERE id = ? AND (uploaded_by = ? OR ? = 'admin')");
    $stmt->bind_param("iis", $mediaId, $_SESSION['user_id'], $_SESSION['role']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Media not found or access denied');
    }

    $media = $result->fetch_assoc();
    
    // Get the physical file path
    $filePath = '../../' . ltrim($media['path'], '../');
    
    // Delete the database record first
    $deleteStmt = $conn->prepare("DELETE FROM media WHERE id = ?");
    $deleteStmt->bind_param("i", $mediaId);
    
    if (!$deleteStmt->execute()) {
        throw new Exception('Failed to delete media record');
    }

    // If database deletion successful, delete the physical file
    if (file_exists($filePath)) {
        if (!unlink($filePath)) {
            // Log error but don't throw exception as DB record is already deleted
            error_log("Failed to delete physical file: " . $filePath);
        }
    }

    $response['success'] = true;
    $response['message'] = 'Media deleted successfully';

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    if (isset($db)) {
        $db->closeConnection();
    }
    echo json_encode($response);
}
?>