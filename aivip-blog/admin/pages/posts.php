<?php
require_once '../config/database.php';

// Get query parameters
$page = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Database connection
$db = new Database();
$conn = $db->getConnection();

// Build query
$query = "SELECT p.*, u.username as author_name 
          FROM posts p 
          JOIN users u ON p.author_id = u.id 
          WHERE 1=1";
$countQuery = "SELECT COUNT(*) as total FROM posts p WHERE 1=1";
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
$query .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
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
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($post = $posts->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div class="fw-bold"><?php echo htmlspecialchars($post['title']); ?></div>
                                <div class="small text-muted"><?php echo htmlspecialchars($post['meta_title']); ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($post['author_name']); ?></td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-<?php echo $post['status'] === 'published' ? 'success' : 'warning'; ?> dropdown-toggle" 
                                            type="button" data-bs-toggle="dropdown">
                                        <?php echo ucfirst($post['status']); ?>
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
</style>

<script>
// Initialize all tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// ... existing code ...
</script> 