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

// Validate sort columns
$allowed_columns = ['id', 'nome_cognome', 'email', 'telefono', 'ragione_sociale', 'messaggio', 'privacy', 'url_invio', 'created_at'];

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
$query = "SELECT * FROM contact_form WHERE 1=1";
$countQuery = "SELECT COUNT(*) as total FROM contact_form WHERE 1=1";
$params = [];
$types = "";

// Add search filter
if ($search) {
    $searchTerm = "%$search%";
    $query .= " AND (nome_cognome LIKE ? OR email LIKE ? OR ragione_sociale LIKE ?)";
    $countQuery .= " AND (nome_cognome LIKE ? OR email LIKE ? OR ragione_sociale LIKE ?)";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
    $types .= "sss";
}

// Get total count
$countStmt = $conn->prepare($countQuery);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$total = $countStmt->get_result()->fetch_assoc()['total'];

// Add pagination and sorting
$offset = ($page - 1) * $limit;
$query .= " ORDER BY $sort_column $sort_direction LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

// Get contact form submissions
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$submissions = $stmt->get_result();

$db->closeConnection();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Contact Form Submissions</h1>
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
                <input type="hidden" name="page" value="contact-form">
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort_column); ?>">
                <input type="hidden" name="direction" value="<?php echo htmlspecialchars($sort_direction); ?>">
            </form>
        </div>
    </div>

    <!-- Contact Form Submissions -->
    <div class="card">
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
                            <th class="sortable" data-column="telefono">
                                Phone
                                <?php if ($sort_column === 'telefono'): ?>
                                    <i class="bi bi-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-column="ragione_sociale">
                                Company
                                <?php if ($sort_column === 'ragione_sociale'): ?>
                                    <i class="bi bi-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-column="messaggio">
                                Message
                                <?php if ($sort_column === 'messaggio'): ?>
                                    <i class="bi bi-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-column="privacy">
                                Privacy
                                <?php if ($sort_column === 'privacy'): ?>
                                    <i class="bi bi-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-column="url_invio">
                                URL
                                <?php if ($sort_column === 'url_invio'): ?>
                                    <i class="bi bi-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-column="created_at">
                                Date
                                <?php if ($sort_column === 'created_at'): ?>
                                    <i class="bi bi-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($submission = $submissions->fetch_assoc()): ?>
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
                            <a class="page-link" href="?page=contact-form&page_num=1&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort_column); ?>&direction=<?php echo urlencode($sort_direction); ?>">
                                First
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=contact-form&page_num=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort_column); ?>&direction=<?php echo urlencode($sort_direction); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=contact-form&page_num=<?php echo $totalPages; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort_column); ?>&direction=<?php echo urlencode($sort_direction); ?>">
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
/* Fixed column widths */
.table {
    table-layout: fixed;
    width: 100%;
}
.table th:nth-child(1), .table td:nth-child(1) { width: 5%; }   /* ID column */
.table th:nth-child(2), .table td:nth-child(2) { width: 15%; }  /* Name column */
.table th:nth-child(3), .table td:nth-child(3) { width: 15%; }  /* Email column */
.table th:nth-child(4), .table td:nth-child(4) { width: 10%; }  /* Phone column */
.table th:nth-child(5), .table td:nth-child(5) { width: 10%; }  /* Company column */
.table th:nth-child(6), .table td:nth-child(6) { width: 20%; }  /* Message column */
.table th:nth-child(7), .table td:nth-child(7) { width: 5%; }   /* Privacy column */
.table th:nth-child(8), .table td:nth-child(8) { width: 10%; }  /* URL column */
.table th:nth-child(9), .table td:nth-child(9) { width: 10%; }  /* Date column */

/* Ensure table cells don't wrap */
.table td, .table th {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Sortable column styles */
.sortable {
    cursor: pointer;
    user-select: none;
}
.sortable:hover {
    background-color: rgba(0,0,0,.05);
}
.sortable i {
    margin-left: 5px;
}

/* Override table-responsive behavior */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
.table-responsive .table {
    margin-bottom: 0;
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