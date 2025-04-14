<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';

// Initialize database connection
$db = new Database();
$conn = $db->getConnection();

// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'timestamp';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Validate parameters
$page = max(1, $page);
$limit = max(1, min(50, $limit));
$offset = ($page - 1) * $limit;

// Allowed sort columns
$allowedSorts = ['website', 'email', 'timestamp', 'privacy'];
$sort = in_array($sort, $allowedSorts) ? $sort : 'timestamp';
$order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

// Build the query
$query = "SELECT * FROM digital_analysis";
$countQuery = "SELECT COUNT(*) as total FROM digital_analysis";
$where = [];

if ($search) {
    $where[] = "(website LIKE ? OR email LIKE ?)";
    $searchParam = "%$search%";
}

if (!empty($where)) {
    $whereClause = " WHERE " . implode(" AND ", $where);
    $query .= $whereClause;
    $countQuery .= $whereClause;
}

// Add sorting
$query .= " ORDER BY $sort $order LIMIT ? OFFSET ?";

// Get total count
$stmt = $conn->prepare($countQuery);
if ($search) {
    $stmt->bind_param('ss', $searchParam, $searchParam);
}
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($total / $limit);

// Get analysis data
$stmt = $conn->prepare($query);
if ($search) {
    $stmt->bind_param('ssii', $searchParam, $searchParam, $limit, $offset);
} else {
    $stmt->bind_param('ii', $limit, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
$analysisData = $result->fetch_all(MYSQLI_ASSOC);

// Close the database connection
$db->closeConnection();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Digital Analysis Requests</h1>
    </div>

    <!-- Search and Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" id="search" placeholder="Search by website or email..." value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <select class="form-select d-inline-block w-auto" id="limit">
                        <option value="10" <?php echo $limit === 10 ? 'selected' : ''; ?>>10 per page</option>
                        <option value="25" <?php echo $limit === 25 ? 'selected' : ''; ?>>25 per page</option>
                        <option value="50" <?php echo $limit === 50 ? 'selected' : ''; ?>>50 per page</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Analysis Requests Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th class="sortable" data-sort="website">
                                Website
                                <i class="bi bi-sort-up"></i>
                            </th>
                            <th class="sortable" data-sort="email">
                                Email
                                <i class="bi bi-sort-up"></i>
                            </th>
                            <th class="sortable" data-sort="privacy">
                                Privacy
                                <i class="bi bi-sort-up"></i>
                            </th>
                            <th class="sortable" data-sort="timestamp">
                                Submitted
                                <i class="bi bi-sort-up"></i>
                            </th>
                            <th class="sortable" data-sort="last_update">
                                Last Update
                                <i class="bi bi-sort-up"></i>
                            </th>
                            <th>Check</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($analysisData)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-file-earmark-text text-muted" style="font-size: 3rem;"></i>
                                    <p class="mt-3 text-muted">No digital analysis requests found</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($analysisData as $analysis): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($analysis['website']); ?></td>
                                    <td><?php echo htmlspecialchars($analysis['email']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $analysis['privacy'] ? 'success' : 'danger'; ?>">
                                            <?php echo $analysis['privacy'] ? 'Accepted' : 'Not Accepted'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($analysis['timestamp'])); ?></td>
                                    <td><?php echo $analysis['last_update'] ? date('M j, Y', strtotime($analysis['last_update'])) : '-'; ?></td>
                                    <td>
                                        <i class="bi bi-<?php echo $analysis['check'] ? 'check-circle-fill text-success' : 'x-circle-fill text-danger'; ?>" style="font-size: 1.5rem;"></i>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-success check-btn" data-id="<?php echo $analysis['id']; ?>" <?php echo $analysis['check'] ? 'disabled' : ''; ?>>
                                                <i class="bi bi-check-lg"></i> Inviato
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger reset-btn" data-id="<?php echo $analysis['id']; ?>" <?php echo !$analysis['check'] ? 'disabled' : ''; ?>>
                                                <i class="bi bi-x-lg"></i> Reset
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page-1; ?>&limit=<?php echo $limit; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&limit=<?php echo $limit; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page+1; ?>&limit=<?php echo $limit; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.sortable {
    cursor: pointer;
    position: relative;
    white-space: nowrap;
}
.sortable:hover {
    background-color: rgba(0,0,0,.075);
}
.sortable i {
    margin-left: 5px;
    display: none;
    vertical-align: middle;
}
/* Fixed width percentages for columns */
.table th:nth-child(1),
.table td:nth-child(1) { width: 40%; }
.table th:nth-child(2),
.table td:nth-child(2) { width: 20%; }
.table th:nth-child(3),
.table td:nth-child(3) { width: 10%; }
.table th:nth-child(4),
.table td:nth-child(4) { width: 10%; }
.table th:nth-child(5),
.table td:nth-child(5) { width: 10%; }
.table th:nth-child(6),
.table td:nth-child(6) { width: 5%; }
.table th:nth-child(7),
.table td:nth-child(7) { width: 5%; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    document.getElementById('searchBtn').addEventListener('click', function() {
        const search = document.getElementById('search').value;
        const limit = document.getElementById('limit').value;
        const url = new URL(window.location.href);
        url.searchParams.set('search', search);
        url.searchParams.set('limit', limit);
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    });

    // Limit change functionality
    document.getElementById('limit').addEventListener('change', function() {
        const limit = this.value;
        const url = new URL(window.location.href);
        url.searchParams.set('limit', limit);
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    });

    // Sort functionality
    document.querySelectorAll('.sortable').forEach(header => {
        header.addEventListener('click', function() {
            const sort = this.dataset.sort;
            const currentOrder = new URLSearchParams(window.location.search).get('order');
            const newOrder = currentOrder === 'ASC' ? 'DESC' : 'ASC';
            
            const url = new URL(window.location.href);
            url.searchParams.set('sort', sort);
            url.searchParams.set('order', newOrder);
            window.location.href = url.toString();
        });
    });

    // Update sort icons
    const currentSort = '<?php echo $sort; ?>';
    const currentOrder = '<?php echo $order; ?>';
    document.querySelectorAll('.sortable').forEach(th => {
        const sort = th.dataset.sort;
        const icon = th.querySelector('i');
        if (sort === currentSort) {
            icon.style.display = 'inline-block';
            icon.className = `bi bi-sort-${currentOrder === 'ASC' ? 'up' : 'down'}`;
        } else {
            icon.style.display = 'none';
        }
    });

    // Check/Reset functionality
    document.querySelectorAll('.check-btn, .reset-btn').forEach(button => {
        button.addEventListener('click', async function() {
            const id = this.dataset.id;
            const action = this.classList.contains('check-btn') ? 'check' : 'reset';
            
            try {
                const response = await fetch('../api/digital_analysis/update.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: id,
                        action: action
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    // Reload the page to show updated status
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while updating the status');
            }
        });
    });
});
</script> 