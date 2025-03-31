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
$newsletter_sort_column = isset($_GET['newsletter_sort']) ? $_GET['newsletter_sort'] : 'created_at';
$newsletter_sort_direction = isset($_GET['newsletter_direction']) ? $_GET['newsletter_direction'] : 'desc';
$contact_sort_column = isset($_GET['contact_sort']) ? $_GET['contact_sort'] : 'created_at';
$contact_sort_direction = isset($_GET['contact_direction']) ? $_GET['contact_direction'] : 'desc';

// Validate sort columns
$newsletter_allowed_columns = ['id', 'nome_cognome', 'email', 'preferenza_invio', 'url_invio', 'privacy', 'created_at'];
$contact_allowed_columns = ['id', 'nome_cognome', 'email', 'telefono', 'ragione_sociale', 'messaggio', 'privacy', 'url_invio', 'created_at'];

if (!in_array($newsletter_sort_column, $newsletter_allowed_columns)) {
    $newsletter_sort_column = 'created_at';
}
if (!in_array($contact_sort_column, $contact_allowed_columns)) {
    $contact_sort_column = 'created_at';
}

// Validate sort directions
if (!in_array($newsletter_sort_direction, ['asc', 'desc'])) {
    $newsletter_sort_direction = 'desc';
}
if (!in_array($contact_sort_direction, ['asc', 'desc'])) {
    $contact_sort_direction = 'desc';
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
$contactQuery .= " ORDER BY $contact_sort_column $contact_sort_direction LIMIT ? OFFSET ?";
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
$newsletterQuery .= " ORDER BY $newsletter_sort_column $newsletter_sort_direction LIMIT ? OFFSET ?";
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
                <input type="hidden" name="newsletter_sort" value="<?php echo htmlspecialchars($newsletter_sort_column); ?>">
                <input type="hidden" name="newsletter_direction" value="<?php echo htmlspecialchars($newsletter_sort_direction); ?>">
                <input type="hidden" name="contact_sort" value="<?php echo htmlspecialchars($contact_sort_column); ?>">
                <input type="hidden" name="contact_direction" value="<?php echo htmlspecialchars($contact_sort_direction); ?>">
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
                            <th class="sortable" data-column="id" data-table="newsletter">
                                ID
                                <?php if ($newsletter_sort_column === 'id'): ?>
                                    <i class="bi bi-sort-<?php echo $newsletter_sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-column="nome_cognome" data-table="newsletter">
                                Name
                                <?php if ($newsletter_sort_column === 'nome_cognome'): ?>
                                    <i class="bi bi-sort-<?php echo $newsletter_sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-column="email" data-table="newsletter">
                                Email
                                <?php if ($newsletter_sort_column === 'email'): ?>
                                    <i class="bi bi-sort-<?php echo $newsletter_sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-column="preferenza_invio" data-table="newsletter">
                                Preference
                                <?php if ($newsletter_sort_column === 'preferenza_invio'): ?>
                                    <i class="bi bi-sort-<?php echo $newsletter_sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-column="url_invio" data-table="newsletter">
                                URL
                                <?php if ($newsletter_sort_column === 'url_invio'): ?>
                                    <i class="bi bi-sort-<?php echo $newsletter_sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-column="privacy" data-table="newsletter">
                                Privacy
                                <?php if ($newsletter_sort_column === 'privacy'): ?>
                                    <i class="bi bi-sort-<?php echo $newsletter_sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-column="created_at" data-table="newsletter">
                                Date
                                <?php if ($newsletter_sort_column === 'created_at'): ?>
                                    <i class="bi bi-sort-<?php echo $newsletter_sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
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
                            <a class="page-link" href="?page=subscribers&page_num=1&search=<?php echo urlencode($search); ?>&newsletter_sort=<?php echo urlencode($newsletter_sort_column); ?>&newsletter_direction=<?php echo urlencode($newsletter_sort_direction); ?>">
                                First
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=subscribers&page_num=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&newsletter_sort=<?php echo urlencode($newsletter_sort_column); ?>&newsletter_direction=<?php echo urlencode($newsletter_sort_direction); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=subscribers&page_num=<?php echo $totalPages; ?>&search=<?php echo urlencode($search); ?>&newsletter_sort=<?php echo urlencode($newsletter_sort_column); ?>&newsletter_direction=<?php echo urlencode($newsletter_sort_direction); ?>">
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
                            <th class="sortable" data-column="id" data-table="contact">
                                ID
                                <?php if ($contact_sort_column === 'id'): ?>
                                    <i class="bi bi-sort-<?php echo $contact_sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-column="nome_cognome" data-table="contact">
                                Name
                                <?php if ($contact_sort_column === 'nome_cognome'): ?>
                                    <i class="bi bi-sort-<?php echo $contact_sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-column="email" data-table="contact">
                                Email
                                <?php if ($contact_sort_column === 'email'): ?>
                                    <i class="bi bi-sort-<?php echo $contact_sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-column="telefono" data-table="contact">
                                Phone
                                <?php if ($contact_sort_column === 'telefono'): ?>
                                    <i class="bi bi-sort-<?php echo $contact_sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-column="ragione_sociale" data-table="contact">
                                Company
                                <?php if ($contact_sort_column === 'ragione_sociale'): ?>
                                    <i class="bi bi-sort-<?php echo $contact_sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-column="messaggio" data-table="contact">
                                Message
                                <?php if ($contact_sort_column === 'messaggio'): ?>
                                    <i class="bi bi-sort-<?php echo $contact_sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-column="privacy" data-table="contact">
                                Privacy
                                <?php if ($contact_sort_column === 'privacy'): ?>
                                    <i class="bi bi-sort-<?php echo $contact_sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-column="url_invio" data-table="contact">
                                URL
                                <?php if ($contact_sort_column === 'url_invio'): ?>
                                    <i class="bi bi-sort-<?php echo $contact_sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-column="created_at" data-table="contact">
                                Date
                                <?php if ($contact_sort_column === 'created_at'): ?>
                                    <i class="bi bi-sort-<?php echo $contact_sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
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
                            <a class="page-link" href="?page=subscribers&page_num=1&search=<?php echo urlencode($search); ?>&contact_sort=<?php echo urlencode($contact_sort_column); ?>&contact_direction=<?php echo urlencode($contact_sort_direction); ?>">
                                First
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=subscribers&page_num=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&contact_sort=<?php echo urlencode($contact_sort_column); ?>&contact_direction=<?php echo urlencode($contact_sort_direction); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=subscribers&page_num=<?php echo $totalPages; ?>&search=<?php echo urlencode($search); ?>&contact_sort=<?php echo urlencode($contact_sort_column); ?>&contact_direction=<?php echo urlencode($contact_sort_direction); ?>">
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
/* Fixed column widths for Newsletter Subscribers table */
.card:nth-of-type(2) .table {
    table-layout: fixed;
    width: 100%;
}
.card:nth-of-type(2) .table th:nth-child(1), 
.card:nth-of-type(2) .table td:nth-child(1) { width: 5%; }   /* ID column */
.card:nth-of-type(2) .table th:nth-child(2), 
.card:nth-of-type(2) .table td:nth-child(2) { width: 15%; }  /* Name column */
.card:nth-of-type(2) .table th:nth-child(3), 
.card:nth-of-type(2) .table td:nth-child(3) { width: 20%; }  /* Email column */
.card:nth-of-type(2) .table th:nth-child(4), 
.card:nth-of-type(2) .table td:nth-child(4) { width: 15%; }  /* Preference column */
.card:nth-of-type(2) .table th:nth-child(5), 
.card:nth-of-type(2) .table td:nth-child(5) { width: 15%; }  /* URL column */
.card:nth-of-type(2) .table th:nth-child(6), 
.card:nth-of-type(2) .table td:nth-child(6) { width: 10%; }  /* Privacy column */
.card:nth-of-type(2) .table th:nth-child(7), 
.card:nth-of-type(2) .table td:nth-child(7) { width: 20%; }  /* Date column */

/* Fixed column widths for Contact Form table */
.card:nth-of-type(3) .table {
    table-layout: fixed;
    width: 100%;
}
.card:nth-of-type(3) .table th:nth-child(1), 
.card:nth-of-type(3) .table td:nth-child(1) { width: 5%; }   /* ID column */
.card:nth-of-type(3) .table th:nth-child(2), 
.card:nth-of-type(3) .table td:nth-child(2) { width: 15%; }  /* Name column */
.card:nth-of-type(3) .table th:nth-child(3), 
.card:nth-of-type(3) .table td:nth-child(3) { width: 15%; }  /* Email column */
.card:nth-of-type(3) .table th:nth-child(4), 
.card:nth-of-type(3) .table td:nth-child(4) { width: 10%; }  /* Phone column */
.card:nth-of-type(3) .table th:nth-child(5), 
.card:nth-of-type(3) .table td:nth-child(5) { width: 10%; }  /* Company column */
.card:nth-of-type(3) .table th:nth-child(6), 
.card:nth-of-type(3) .table td:nth-child(6) { width: 20%; }  /* Message column */
.card:nth-of-type(3) .table th:nth-child(7), 
.card:nth-of-type(3) .table td:nth-child(7) { width: 5%; }   /* Privacy column */
.card:nth-of-type(3) .table th:nth-child(8), 
.card:nth-of-type(3) .table td:nth-child(8) { width: 10%; }  /* URL column */
.card:nth-of-type(3) .table th:nth-child(9), 
.card:nth-of-type(3) .table td:nth-child(9) { width: 10%; }  /* Date column */

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
            const table = this.getAttribute('data-table');
            const currentDirection = new URLSearchParams(window.location.search).get(`${table}_direction`) || 'desc';
            const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
            
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set(`${table}_sort`, column);
            urlParams.set(`${table}_direction`, newDirection);
            window.location.search = urlParams.toString();
        });
    });
});
</script> 