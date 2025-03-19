<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$id = (int)$data['id'];

try {
    require_once '../../config/database.php';
    $db = new Database();
    $conn = $db->getConnection();

    // Begin transaction
    $conn->begin_transaction();

    // Check if category has posts
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM post_categories WHERE category_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete category with associated posts']);
        exit;
    }

    // Update child categories to point to parent
    $stmt = $conn->prepare("UPDATE categories SET parent_id = (SELECT parent_id FROM (SELECT parent_id FROM categories WHERE id = ?) as temp) WHERE parent_id = ?");
    $stmt->bind_param("ii", $id, $id);
    $stmt->execute();

    // Delete category
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
    } else {
        throw new Exception('Failed to delete category');
    }

    $db->closeConnection();
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    error_log("Category delete error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while deleting category']);
} 