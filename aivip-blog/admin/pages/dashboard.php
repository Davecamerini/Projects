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
$recentStmt = $conn->prepare("SELECT p.*, u.username as author_name,
    GROUP_CONCAT(c.name ORDER BY c.name ASC SEPARATOR ', ') as categories,
    GROUP_CONCAT(c.id ORDER BY c.name ASC SEPARATOR ',') as category_ids
    FROM posts p 
    JOIN users u ON p.author_id = u.id 
    LEFT JOIN post_categories pc ON p.id = pc.post_id
    LEFT JOIN categories c ON pc.category_id = c.id
    WHERE p.author_id = ? OR ? = 'admin'
    GROUP BY p.id
    ORDER BY p.created_at DESC LIMIT 5");
$recentStmt->bind_param("is", $_SESSION['user_id'], $_SESSION['role']);
$recentStmt->execute();
$recentPosts = $recentStmt->get_result();

$db->closeConnection();
?>

<!-- Add Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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
                            <th>Categories</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($post = $recentPosts->fetch_assoc()): ?>
                        <tr data-post-id="<?php echo $post['id']; ?>">
                            <td><?php echo htmlspecialchars($post['title']); ?></td>
                            <td><?php echo htmlspecialchars($post['author_name']); ?></td>
                            <td>
                                <?php if ($post['categories']): ?>
                                    <?php 
                                    $categories = explode(', ', $post['categories']);
                                    $categoryIds = explode(',', $post['category_ids'] ?? '');
                                    $visibleCategories = array_slice($categories, 0, 3);
                                    $hasMore = count($categories) > 3;
                                    
                                    foreach ($visibleCategories as $index => $category): 
                                        $categoryId = $categoryIds[$index] ?? '';
                                        echo generateCategoryBadge($category, $categoryId);
                                    endforeach;
                                    
                                    if ($hasMore):
                                    ?>
                                        <span class="badge bg-secondary" 
                                              data-bs-toggle="tooltip" 
                                              data-bs-placement="top" 
                                              title="<?php echo htmlspecialchars(implode(', ', array_slice($categories, 3))); ?>">
                                            +<?php echo count($categories) - 3; ?> more
                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">Uncategorized</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo match($post['status']) {
                                        'published' => 'success',
                                        'private' => 'secondary',
                                        'draft' => 'warning',
                                        default => 'secondary'
                                    }; ?>">
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

<style>
/* Category badge styles */
.category-badge {
    display: inline-block;
    margin: 0.1rem;
    padding: 0.3rem 0.6rem;
    font-size: 0.75rem;
    font-weight: 600;
    line-height: 1;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: 0.25rem;
    transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out;
    cursor: pointer;
    text-decoration: none;
}

.category-badge:hover {
    opacity: 0.9;
}

/* Category colors based on name hash */
<?php
$categoryQuery = "SELECT id, name FROM categories ORDER BY name ASC";
$categoryResult = $conn->query($categoryQuery);
$allCategories = $categoryResult->fetch_all(MYSQLI_ASSOC);

foreach ($allCategories as $cat) {
    $hash = substr(md5($cat['name']), 0, 6);
    $r = hexdec(substr($hash, 0, 2));
    $g = hexdec(substr($hash, 2, 2));
    $b = hexdec(substr($hash, 4, 2));
    
    // Ensure text is readable by adjusting background lightness
    $lightness = ($r * 299 + $g * 587 + $b * 114) / 1000;
    $textColor = $lightness > 128 ? '#000' : '#fff';
    
    echo ".category-{$cat['id']} { background-color: #{$hash}; color: {$textColor}; }\n";
}
?>

/* Add these styles to the existing style block */
.table td .dropdown {
    position: static;
}

.table td .dropdown-menu {
    position: absolute;
    z-index: 1060;
    min-width: 8rem;
}

.table td .dropdown button {
    min-width: 90px;
    display: inline-flex;
    align-items: center;
    justify-content: space-between;
    padding-right: 8px;
    padding-left: 8px;
}

.table td .dropdown button::after {
    margin-left: 6px;
}
</style>

<script>
// Initialize all tooltips
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize dropdowns
    var dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
    var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
    });
});

// Function to change post status
async function changePostStatus(postId, status) {
    try {
        const response = await fetch('../api/posts/update.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: postId,
                status: status
            })
        });

        const data = await response.json();
        
        if (data.success) {
            // Refresh the page to show updated status
            window.location.reload();
        } else {
            alert(data.error || 'Failed to update post status');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while updating the post status');
    }
}

// Function to filter by category (redirects to posts page with category filter)
function filterByCategory(categoryId) {
    window.location.href = `?page=posts&category=${categoryId}`;
}

<?php
// Helper function to generate category badge HTML
function generateCategoryBadge($categoryName, $categoryId) {
    return sprintf(
        '<a href="?page=posts&category=%d" class="category-badge category-%d" onclick="filterByCategory(%d); return false;">%s</a>',
        $categoryId,
        $categoryId,
        $categoryId,
        htmlspecialchars($categoryName)
    );
}
?>
</script> 