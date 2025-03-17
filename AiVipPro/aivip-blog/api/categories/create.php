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

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

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

    // Check if slug exists
    $stmt = $conn->prepare("SELECT id FROM categories WHERE slug = ?");
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Slug already exists']);
        exit;
    }

    // Insert category
    $stmt = $conn->prepare("INSERT INTO categories (name, slug, description, parent_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $name, $slug, $description, $parent_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Category created successfully']);
    } else {
        throw new Exception('Failed to create category');
    }

    $db->closeConnection();
} catch (Exception $e) {
    error_log("Category creation error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while creating category']);
} 