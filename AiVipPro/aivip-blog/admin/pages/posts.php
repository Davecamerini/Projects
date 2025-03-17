<?php
require_once '../config/database.php';

// Get query parameters
$page = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Database connection
$db = new Database();
$conn = $db->getConnection();

// Get all categories for filter dropdown
$categoryQuery = "SELECT id, name FROM categories ORDER BY name ASC";
$categoryResult = $conn->query($categoryQuery);
$allCategories = $categoryResult->fetch_all(MYSQLI_ASSOC);

// Build query
$query = "SELECT p.*, u.username as author_name,
          GROUP_CONCAT(c.name ORDER BY c.name ASC SEPARATOR ', ') as categories,
          GROUP_CONCAT(c.id ORDER BY c.name ASC SEPARATOR ',') as category_ids
          FROM posts p 
          JOIN users u ON p.author_id = u.id 
          LEFT JOIN post_categories pc ON p.id = pc.post_id
          LEFT JOIN categories c ON pc.category_id = c.id
          WHERE 1=1";
$countQuery = "SELECT COUNT(DISTINCT p.id) as total FROM posts p WHERE 1=1";
$params = [];
$types = "";

// Add search filter
if ($search) {
    $searchTerm = "%$search%";
    $query .= " AND (p.title LIKE ? OR p.content LIKE ?)";
    $countQuery .= " AND (p.title LIKE ? OR p.content LIKE ?)";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

// Add status filter
if ($status) {
    $query .= " AND p.status = ?";
    $countQuery .= " AND p.status = ?";
    $params[] = $status;
    $types .= "s";
}

// Add author filter for non-admin users
if ($_SESSION['role'] !== 'admin') {
    $query .= " AND p.author_id = ?";
    $countQuery .= " AND p.author_id = ?";
    $params[] = $_SESSION['user_id'];
    $types .= "i";
}

// Add category filter
if ($category) {
    $query .= " AND EXISTS (SELECT 1 FROM post_categories pc2 WHERE pc2.post_id = p.id AND pc2.category_id = ?)";
    $countQuery .= " AND EXISTS (SELECT 1 FROM post_categories pc2 WHERE pc2.post_id = p.id AND pc2.category_id = ?)";
    $params[] = $category;
    $types .= "i";
}

// Get total count
$countStmt = $conn->prepare($countQuery);
if (!empty($params)) {
    $countParams = $params;
    $countStmt->bind_param($types, ...$countParams);
}
$countStmt->execute();
$totalResult = $countStmt->get_result()->fetch_assoc();
$total = $totalResult['total'];

// Add pagination
$offset = ($page - 1) * $limit;
$query .= " GROUP BY p.id ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

// Get posts
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$posts = $stmt->get_result();

$db->closeConnection();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Posts</h1>
        <a href="?page=new-post" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>New Post
        </a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <input type="hidden" name="page" value="posts">
                
                <div class="col-md-4">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Search posts..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>

                <div class="col-md-3">
                    <select class="form-select" name="category" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php foreach ($allCategories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <select class="form-select" name="status" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>Draft</option>
                        <option value="published" <?php echo $status === 'published' ? 'selected' : ''; ?>>Published</option>
                        <option value="archived" <?php echo $status === 'archived' ? 'selected' : ''; ?>>Archived</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <select class="form-select" name="limit" onchange="this.form.submit()">
                        <option value="10" <?php echo $limit === 10 ? 'selected' : ''; ?>>10 per page</option>
                        <option value="25" <?php echo $limit === 25 ? 'selected' : ''; ?>>25 per page</option>
                        <option value="50" <?php echo $limit === 50 ? 'selected' : ''; ?>>50 per page</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Posts List -->
    <div class="card">
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
                        <?php while ($post = $posts->fetch_assoc()): ?>
                        <tr data-post-id="<?php echo $post['id']; ?>" data-content="<?php echo htmlspecialchars($post['content']); ?>">
                            <td>
                                <div class="fw-bold"><?php echo htmlspecialchars($post['title']); ?></div>
                                <div class="small text-muted"><?php echo htmlspecialchars($post['meta_title']); ?></div>
                            </td>
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
                                <div class="dropdown">
                                    <?php 
                                    $currentStatus = $post['status'];
                                    if (empty($currentStatus) || !in_array($currentStatus, ['draft', 'published', 'archived'])) {
                                        $currentStatus = 'draft';
                                    }
                                    ?>
                                    <button class="btn btn-sm btn-outline-<?php 
                                        echo match($currentStatus) {
                                            'published' => 'success',
                                            'archived' => 'secondary',
                                            'draft' => 'warning',
                                            default => 'warning'
                                        }; ?> dropdown-toggle" 
                                            type="button" 
                                            data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                        <?php echo ucfirst($currentStatus); ?>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="changePostStatus(<?php echo $post['id']; ?>, 'draft')">Draft</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="changePostStatus(<?php echo $post['id']; ?>, 'published')">Published</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="changePostStatus(<?php echo $post['id']; ?>, 'archived')">Archived</a></li>
                                    </ul>
                                </div>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($post['created_at'])); ?></td>
                            <td>
                                <a href="?page=new-post&action=edit&id=<?php echo $post['id']; ?>" 
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

            <!-- Pagination -->
            <?php if ($total > $limit): ?>
            <div class="d-flex justify-content-center mt-4">
                <nav>
                    <ul class="pagination">
                        <?php
                        $totalPages = ceil($total / $limit);
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);
                        ?>
                        
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=posts&page_num=1&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&limit=<?php echo $limit; ?>">
                                First
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=posts&page_num=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&limit=<?php echo $limit; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=posts&page_num=<?php echo $totalPages; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&limit=<?php echo $limit; ?>">
                                Last
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Fix dropdown menu visibility */
.card {
    position: relative;
    z-index: 1;
    overflow: visible;
}

.card-body {
    position: relative;
    z-index: 1;
    overflow: visible;
}

.table-responsive {
    position: relative;
    z-index: 1;
    overflow: visible;
}

/* Status dropdown specific styles */
td .dropdown {
    position: static;
}

td .dropdown-menu {
    position: absolute;
    z-index: 1060;
    min-width: 8rem;
}

td .dropdown button {
    display: inline-flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.25rem 0.5rem;
}

td .dropdown button::after {
    margin-left: 6px;
}

/* Action buttons styles */
td .btn-group,
td .btn {
    position: relative;
    z-index: 1050;
}

/* General dropdown styles */
.dropdown-menu {
    padding: 0.5rem 0;
    margin: 0;
    font-size: 1rem;
    color: #212529;
    text-align: left;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid rgba(0,0,0,.15);
    border-radius: 0.25rem;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15);
}

.dropdown-item {
    display: block;
    width: 100%;
    padding: 0.25rem 1rem;
    clear: both;
    font-weight: 400;
    color: #212529;
    text-align: inherit;
    text-decoration: none;
    white-space: nowrap;
    background-color: transparent;
    border: 0;
    cursor: pointer;
}

.dropdown-item:hover {
    color: #1e2125;
    background-color: #f8f9fa;
}

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

/* Ensure text is visible for all status types */
.btn-outline-secondary {
    color: #6c757d;
}

.btn-outline-success {
    color: #198754;
}

.btn-outline-warning {
    color: #ffc107;
}
</style>

<script>
// Initialize all tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Function to filter by category
function filterByCategory(categoryId) {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('page', 'posts');
    urlParams.set('category', categoryId);
    window.location.search = urlParams.toString();
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips for category badges
    var categoryBadges = document.querySelectorAll('[data-categories]');
    categoryBadges.forEach(function(badge) {
        var categories = badge.getAttribute('data-categories').split(', ');
        if (categories.length > 2) {
            var visibleCategories = categories.slice(0, 2).join(', ');
            var hiddenCategories = categories.slice(2).join(', ');
            var tooltip = `${visibleCategories} +${categories.length - 2} more:\n${hiddenCategories}`;
            new bootstrap.Tooltip(badge, {
                title: tooltip,
                placement: 'top',
                html: true
            });
        }
    });
});

// Function to change post status
function changePostStatus(postId, newStatus) {
    // Validate status
    const validStatuses = ['draft', 'published', 'archived'];
    if (!validStatuses.includes(newStatus)) {
        console.error('Invalid status:', newStatus);
        alert('Invalid status value');
        return;
    }

    // Log the request data
    console.log('Sending status update:', { id: postId, status: newStatus });

    // Send the update request
    fetch('/api/posts/update.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id: postId,
            status: newStatus
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Server response:', data);
        
        if (data.success) {
            // Verify the returned status matches what we sent
            if (data.data.status !== newStatus) {
                console.error('Status mismatch:', { sent: newStatus, received: data.data.status });
                alert('Warning: The returned status does not match the requested status');
            }
            
            // Refresh the page to show updated status
            location.reload();
        } else {
            alert('Error updating status: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating status: ' + error.message);
    });
}
</script>

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