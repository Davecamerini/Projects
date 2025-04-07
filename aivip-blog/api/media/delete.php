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

    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (!isset($data['id'])) {
        throw new Exception('Media ID is required');
    }

    $mediaId = (int)$data['id'];

    // Database connection
    $db = new Database();
    $conn = $db->getConnection();

    // Get file information
    $stmt = $conn->prepare("SELECT filepath FROM media WHERE id = ?");
    $stmt->bind_param("i", $mediaId);
    $stmt->execute();
    $result = $stmt->get_result();
    $media = $result->fetch_assoc();

    if (!$media) {
        throw new Exception('Media not found');
    }

    // Extract filename from full URL
    $filepath = $media['filepath'];
    $filename = basename($filepath);
    $uploadPath = '../../uploads/images/' . $filename;

    // Delete file from server
    if (file_exists($uploadPath)) {
        if (!unlink($uploadPath)) {
            throw new Exception('Failed to delete file from server');
        }
    }

    // Delete from database
    $stmt = $conn->prepare("DELETE FROM media WHERE id = ?");
    $stmt->bind_param("i", $mediaId);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Media deleted successfully';
    } else {
        throw new Exception('Failed to delete media from database');
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    if (isset($db)) {
        $db->closeConnection();
    }
    echo json_encode($response);
}
?>