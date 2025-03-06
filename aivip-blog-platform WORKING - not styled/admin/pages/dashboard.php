<?php
require_once '../config/database.php';

// Get statistics
$db = new Database();
$conn = $db->getConnection();

// Get total posts
$postStmt = $conn->prepare("SELECT 
    COUNT(*) as total_posts,
    COUNT(CASE WHEN status = 'published' THEN 1 END) as published_posts,
    COUNT(CASE WHEN status = 'draft' THEN 1 END) as draft_posts
    FROM posts WHERE author_id = ? OR ? = 'admin'");
$postStmt->bind_param("is", $_SESSION['user_id'], $_SESSION['role']);
$postStmt->execute();
$stats = $postStmt->get_result()->fetch_assoc();

// Get recent posts
$recentStmt = $conn->prepare("SELECT p.*, u.username as author_name 
    FROM posts p 
    JOIN users u ON p.author_id = u.id 
    WHERE p.author_id = ? OR ? = 'admin'
    ORDER BY p.created_at DESC LIMIT 5");
$recentStmt->bind_param("is", $_SESSION['user_id'], $_SESSION['role']);
$recentStmt->execute();
$recentPosts = $recentStmt->get_result();

$db->closeConnection();
?>

<div class="container-fluid">
    <h1 class="h3 mb-4">Dashboard</h1>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Posts</h5>
                    <h2 class="card-text"><?php echo $stats['total_posts']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Published Posts</h5>
                    <h2 class="card-text"><?php echo $stats['published_posts']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Draft Posts</h5>
                    <h2 class="card-text"><?php echo $stats['draft_posts']; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Posts -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Recent Posts</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($post = $recentPosts->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($post['title']); ?></td>
                            <td><?php echo htmlspecialchars($post['author_name']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $post['status'] === 'published' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($post['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($post['created_at'])); ?></td>
                            <td>
                                <a href="?page=posts&action=edit&id=<?php echo $post['id']; ?>" 
                                   class="btn btn-sm btn-primary"
                                   data-bs-toggle="tooltip"
                                   data-bs-placement="top"
                                   title="Edit Post">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="/post.php?id=<?php echo $post['id']; ?>" 
                                   target="_blank" 
                                   class="btn btn-sm btn-info"
                                   data-bs-toggle="tooltip"
                                   data-bs-placement="top"
                                   title="Preview Post">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button onclick="deletePost(<?php echo $post['id']; ?>)" 
                                        class="btn btn-sm btn-danger"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="Delete Post">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <a href="?page=new-post" class="btn btn-primary me-2">
                        <i class="bi bi-plus-circle me-2"></i>New Post
                    </a>
                    <a href="?page=media" class="btn btn-secondary me-2">
                        <i class="bi bi-images me-2"></i>Upload Media
                    </a>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="?page=users" class="btn btn-info">
                        <i class="bi bi-people me-2"></i>Manage Users
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize all tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script> 