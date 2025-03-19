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
$name = trim($data['name'] ?? '');
$slug = trim($data['slug'] ?? '');
$description = trim($data['description'] ?? '');
$parent_id = $data['parent_id'] ? (int)$data['parent_id'] : null;

// Validate input
if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Name is required']);
    exit;
}

// Generate slug if not provided
if (empty($slug)) {
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
    $slug = trim($slug, '-');
}

try {
    require_once '../../config/database.php';
    $db = new Database();
    $conn = $db->getConnection();

    // Check if slug exists for other categories
    $stmt = $conn->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
    $stmt->bind_param("si", $slug, $id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Slug already exists']);
        exit;
    }

    // Check if parent_id creates a cycle
    if ($parent_id) {
        $current_parent = $parent_id;
        while ($current_parent) {
            if ($current_parent == $id) {
                echo json_encode(['success' => false, 'message' => 'Invalid parent category - would create a cycle']);
                exit;
            }
            $stmt = $conn->prepare("SELECT parent_id FROM categories WHERE id = ?");
            $stmt->bind_param("i", $current_parent);
            $stmt->execute();
            $result = $stmt->get_result();
            $parent = $result->fetch_assoc();
            $current_parent = $parent ? $parent['parent_id'] : null;
        }
    }

    // Update category
    $stmt = $conn->prepare("UPDATE categories SET name = ?, slug = ?, description = ?, parent_id = ? WHERE id = ?");
    $stmt->bind_param("sssii", $name, $slug, $description, $parent_id, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Category updated successfully']);
    } else {
        throw new Exception('Failed to update category');
    }

    $db->closeConnection();
} catch (Exception $e) {
    error_log("Category update error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while updating category']);
} 