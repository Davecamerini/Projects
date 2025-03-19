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

    // Check if file was uploaded
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error occurred');
    }

    $file = $_FILES['image'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    $fileType = $file['type'];

    // Get file extension
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Allowed extensions
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    
    // Validate file extension
    if (!in_array($fileExt, $allowed)) {
        throw new Exception('File type not allowed');
    }

    // Validate file size (5MB max)
    if ($fileSize > 5000000) {
        throw new Exception('File too large');
    }

    // Generate unique filename
    $newFileName = uniqid('', true) . '.' . $fileExt;
    $uploadPath = '../../uploads/images/' . $newFileName;

    // Move file to upload directory
    if (!move_uploaded_file($fileTmpName, $uploadPath)) {
        throw new Exception('Failed to move uploaded file');
    }

    // Database connection
    $db = new Database();
    $conn = $db->getConnection();

    // Save file info to database
    $stmt = $conn->prepare("INSERT INTO media (filename, path, type, uploaded_by, upload_date) VALUES (?, ?, ?, ?, NOW())");
    $relativePath = '../uploads/images/' . $newFileName;
    $stmt->bind_param("sssi", $fileName, $relativePath, $fileType, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $mediaId = $conn->insert_id;
        
        // Prepare response
        $response['success'] = true;
        $response['message'] = 'File uploaded successfully';
        $response['data'] = [
            'id' => $mediaId,
            'filename' => $fileName,
            'path' => $relativePath
        ];
    } else {
        // Remove uploaded file if database insert fails
        unlink($uploadPath);
        throw new Exception('Failed to save file information');
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