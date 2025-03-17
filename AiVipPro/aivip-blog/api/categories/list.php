<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in and is admin for full details
$isAdmin = isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin';

try {
    require_once '../../config/database.php';
    $db = new Database();
    $conn = $db->getConnection();

    // Base query
    $query = "
        SELECT c.id, c.name, c.slug, c.description, c.parent_id,
               p.name as parent_name,
               COUNT(pc.post_id) as post_count
        FROM categories c
        LEFT JOIN categories p ON c.parent_id = p.id
        LEFT JOIN post_categories pc ON c.id = pc.category_id
    ";

    // Add grouping
    $query .= " GROUP BY c.id";

    // Execute query
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception('Failed to fetch categories');
    }

    $categories = [];
    while ($row = $result->fetch_assoc()) {
        // If not admin, only include essential fields
        if (!$isAdmin) {
            $categories[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'slug' => $row['slug'],
                'post_count' => $row['post_count']
            ];
        } else {
            $categories[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'slug' => $row['slug'],
                'description' => $row['description'],
                'parent_id' => $row['parent_id'],
                'parent_name' => $row['parent_name'],
                'post_count' => $row['post_count']
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'categories' => $categories
    ]);

    $db->closeConnection();
} catch (Exception $e) {
    error_log("Category list error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching categories'
    ]);
} 