<?php
// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ?page=dashboard');
    exit;
}

require_once '../config/database.php';

// Get query parameters
$page = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
$limit = 10;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$sort_direction = isset($_GET['direction']) ? $_GET['direction'] : 'desc';

// Validate sort column
$allowed_columns = ['id', 'nome_cognome', 'email', 'created_at'];
if (!in_array($sort_column, $allowed_columns)) {
    $sort_column = 'created_at';
}

// Validate sort direction
if (!in_array($sort_direction, ['asc', 'desc'])) {
    $sort_direction = 'desc';
}

$db = new Database();
$conn = $db->getConnection();

// Build query for contact form submissions
$contactQuery = "SELECT * FROM contact_form WHERE 1=1";
$contactCountQuery = "SELECT COUNT(*) as total FROM contact_form WHERE 1=1";
$contactParams = [];
$contactTypes = "";

// Add search filter for contact form
if ($search) {
    $searchTerm = "%$search%";
    $contactQuery .= " AND (nome_cognome LIKE ? OR email LIKE ? OR ragione_sociale LIKE ?)";
    $contactCountQuery .= " AND (nome_cognome LIKE ? OR email LIKE ? OR ragione_sociale LIKE ?)";
    $contactParams = array_merge($contactParams, [$searchTerm, $searchTerm, $searchTerm]);
    $contactTypes .= "sss";
}

// Get total count for contact form
$countStmt = $conn->prepare($contactCountQuery);
if (!empty($contactParams)) {
    $countStmt->bind_param($contactTypes, ...$contactParams);
}
$countStmt->execute();
$contactTotal = $countStmt->get_result()->fetch_assoc()['total'];

// Add pagination and sorting for contact form
$offset = ($page - 1) * $limit;
$contactQuery .= " ORDER BY $sort_column $sort_direction LIMIT ? OFFSET ?";
$contactParams[] = $limit;
$contactParams[] = $offset;
$contactTypes .= "ii";

// Get contact form submissions
$stmt = $conn->prepare($contactQuery);
if (!empty($contactParams)) {
    $stmt->bind_param($contactTypes, ...$contactParams);
}
$stmt->execute();
$contactSubmissions = $stmt->get_result();

// Build query for newsletter subscribers
$newsletterQuery = "SELECT * FROM newsletter WHERE 1=1";
$newsletterCountQuery = "SELECT COUNT(*) as total FROM newsletter WHERE 1=1";
$newsletterParams = [];
$newsletterTypes = "";

// Add search filter for newsletter
if ($search) {
    $searchTerm = "%$search%";
    $newsletterQuery .= " AND (nome_cognome LIKE ? OR email LIKE ?)";
    $newsletterCountQuery .= " AND (nome_cognome LIKE ? OR email LIKE ?)";
    $newsletterParams = array_merge($newsletterParams, [$searchTerm, $searchTerm]);
    $newsletterTypes .= "ss";
}

// Get total count for newsletter
$countStmt = $conn->prepare($newsletterCountQuery);
if (!empty($newsletterParams)) {
    $countStmt->bind_param($newsletterTypes, ...$newsletterParams);
}
$countStmt->execute();
$newsletterTotal = $countStmt->get_result()->fetch_assoc()['total'];

// Add pagination and sorting for newsletter
$newsletterQuery .= " ORDER BY $sort_column $sort_direction LIMIT ? OFFSET ?";
$newsletterParams[] = $limit;
$newsletterParams[] = $offset;
$newsletterTypes .= "ii";

// Get newsletter subscribers
$stmt = $conn->prepare($newsletterQuery);
if (!empty($newsletterParams)) {
    $stmt->bind_param($newsletterTypes, ...$newsletterParams);
}
$stmt->execute();
$newsletterSubscribers = $stmt->get_result();

$db->closeConnection();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Subscribers & Contact Form</h1>
    </div>

    <!-- Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Search..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
                <input type="hidden" name="page" value="subscribers">
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort_column); ?>">
                <input type="hidden" name="direction" value="<?php echo htmlspecialchars($sort_direction); ?>">
            </form>
        </div>
    </div>

    <!-- Newsletter Subscribers -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Newsletter Subscribers</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th class="sortable" data-column="id">
                                ID
                                <?php if ($sort_column === 'id'): ?>
                                    <i class="bi bi-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-column="nome_cognome">
                                Name
                                <?php if ($sort_column === 'nome_cognome'): ?>
                                    <i class="bi bi-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-column="email">
                                Email
                                <?php if ($sort_column === 'email'): ?>
                                    <i class="bi bi-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                            <th>Preference</th>
                            <th>URL</th>
                            <th>Privacy</th>
                            <th class="sortable" data-column="created_at">
                                Date
                                <?php if ($sort_column === 'created_at'): ?>
                                    <i class="bi bi-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($subscriber = $newsletterSubscribers->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($subscriber['id']); ?></td>
                            <td><?php echo htmlspecialchars($subscriber['nome_cognome']); ?></td>
                            <td><?php echo htmlspecialchars($subscriber['email']); ?></td>
                            <td><?php echo htmlspecialchars($subscriber['preferenza_invio']); ?></td>
                            <td><?php echo htmlspecialchars($subscriber['url_invio']); ?></td>
                            <td>
                                <?php if ($subscriber['privacy']): ?>
                                    <span class="badge bg-success">Accepted</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Not Accepted</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('Y-m-d H:i:s', strtotime($subscriber['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Newsletter Pagination -->
            <?php if ($newsletterTotal > $limit): ?>
            <div class="d-flex justify-content-center mt-4">
                <nav>
                    <ul class="pagination">
                        <?php
                        $totalPages = ceil($newsletterTotal / $limit);
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);
                        ?>
                        
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=subscribers&page_num=1&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort_column); ?>&direction=<?php echo urlencode($sort_direction); ?>">
                                First
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=subscribers&page_num=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort_column); ?>&direction=<?php echo urlencode($sort_direction); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=subscribers&page_num=<?php echo $totalPages; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort_column); ?>&direction=<?php echo urlencode($sort_direction); ?>">
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

    <!-- Contact Form Submissions -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Contact Form Submissions</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th class="sortable" data-column="id">
                                ID
                                <?php if ($sort_column === 'id'): ?>
                                    <i class="bi bi-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-column="nome_cognome">
                                Name
                                <?php if ($sort_column === 'nome_cognome'): ?>
                                    <i class="bi bi-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-column="email">
                                Email
                                <?php if ($sort_column === 'email'): ?>
                                    <i class="bi bi-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                            <th>Phone</th>
                            <th>Company</th>
                            <th>Message</th>
                            <th>Privacy</th>
                            <th>URL</th>
                            <th class="sortable" data-column="created_at">
                                Date
                                <?php if ($sort_column === 'created_at'): ?>
                                    <i class="bi bi-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($submission = $contactSubmissions->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($submission['id']); ?></td>
                            <td><?php echo htmlspecialchars($submission['nome_cognome']); ?></td>
                            <td><?php echo htmlspecialchars($submission['email']); ?></td>
                            <td><?php echo htmlspecialchars($submission['telefono']); ?></td>
                            <td><?php echo htmlspecialchars($submission['ragione_sociale']); ?></td>
                            <td><?php echo htmlspecialchars($submission['messaggio']); ?></td>
                            <td>
                                <?php if ($submission['privacy']): ?>
                                    <span class="badge bg-success">Accepted</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Not Accepted</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($submission['url_invio']); ?></td>
                            <td><?php echo date('Y-m-d H:i:s', strtotime($submission['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Contact Form Pagination -->
            <?php if ($contactTotal > $limit): ?>
            <div class="d-flex justify-content-center mt-4">
                <nav>
                    <ul class="pagination">
                        <?php
                        $totalPages = ceil($contactTotal / $limit);
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);
                        ?>
                        
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=subscribers&page_num=1&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort_column); ?>&direction=<?php echo urlencode($sort_direction); ?>">
                                First
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=subscribers&page_num=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort_column); ?>&direction=<?php echo urlencode($sort_direction); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=subscribers&page_num=<?php echo $totalPages; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort_column); ?>&direction=<?php echo urlencode($sort_direction); ?>">
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
/* Add hover effect for sortable columns */
.sortable {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.sortable:hover {
    background-color: rgba(0, 0, 0, 0.05);
}

.sortable i {
    margin-left: 4px;
    opacity: 0.5;
}

.sortable:hover i {
    opacity: 1;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add click handlers for sortable columns
    document.querySelectorAll('.sortable').forEach(function(header) {
        header.style.cursor = 'pointer';
        header.addEventListener('click', function() {
            const column = this.getAttribute('data-column');
            const currentDirection = new URLSearchParams(window.location.search).get('direction') || 'desc';
            const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
            
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('sort', column);
            urlParams.set('direction', newDirection);
            window.location.search = urlParams.toString();
        });
    });
});
</script> 