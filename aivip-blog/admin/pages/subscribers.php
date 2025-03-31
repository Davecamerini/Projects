<?php
// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ?page=dashboard');
    exit;
}

require_once '../config/database.php';
$db = new Database();
$conn = $db->getConnection();

// Get contact form submissions
$stmt = $conn->prepare("SELECT * FROM contact_form ORDER BY created_at DESC");
$stmt->execute();
$contactSubmissions = $stmt->get_result();

// Get newsletter subscribers
$stmt = $conn->prepare("SELECT * FROM newsletter ORDER BY created_at DESC");
$stmt->execute();
$newsletterSubscribers = $stmt->get_result();

$db->closeConnection();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Subscribers & Contact Form</h1>
    </div>

    <!-- Newsletter Subscribers -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Newsletter Subscribers</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="newsletterTable">
                    <thead>
                        <tr>
                            <th class="sortable" data-sort="id">ID <i class="bi bi-arrow-down-up"></i></th>
                            <th class="sortable" data-sort="nome_cognome">Name <i class="bi bi-arrow-down-up"></i></th>
                            <th class="sortable" data-sort="email">Email <i class="bi bi-arrow-down-up"></i></th>
                            <th class="sortable" data-sort="preferenza_invio">Preference <i class="bi bi-arrow-down-up"></i></th>
                            <th class="sortable" data-sort="url_invio">URL <i class="bi bi-arrow-down-up"></i></th>
                            <th class="sortable" data-sort="privacy">Privacy <i class="bi bi-arrow-down-up"></i></th>
                            <th class="sortable" data-sort="created_at">Date <i class="bi bi-arrow-down-up"></i></th>
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
        </div>
    </div>

    <!-- Contact Form Submissions -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Contact Form Submissions</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="contactTable">
                    <thead>
                        <tr>
                            <th class="sortable" data-sort="id">ID <i class="bi bi-arrow-down-up"></i></th>
                            <th class="sortable" data-sort="nome_cognome">Name <i class="bi bi-arrow-down-up"></i></th>
                            <th class="sortable" data-sort="email">Email <i class="bi bi-arrow-down-up"></i></th>
                            <th class="sortable" data-sort="telefono">Phone <i class="bi bi-arrow-down-up"></i></th>
                            <th class="sortable" data-sort="ragione_sociale">Company <i class="bi bi-arrow-down-up"></i></th>
                            <th class="sortable" data-sort="messaggio">Message <i class="bi bi-arrow-down-up"></i></th>
                            <th class="sortable" data-sort="privacy">Privacy <i class="bi bi-arrow-down-up"></i></th>
                            <th class="sortable" data-sort="url_invio">URL <i class="bi bi-arrow-down-up"></i></th>
                            <th class="sortable" data-sort="created_at">Date <i class="bi bi-arrow-down-up"></i></th>
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
        </div>
    </div>
</div>

<style>
.sortable {
    cursor: pointer;
    user-select: none;
    position: relative;
}

.sortable:hover {
    background-color: rgba(0,0,0,.05);
}

.sortable i {
    margin-left: 5px;
    opacity: 0.3;
}

.sortable.asc i {
    opacity: 1;
}

.sortable.desc i {
    opacity: 1;
    transform: rotate(180deg);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to sort table
    function sortTable(table, column, type = 'text') {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const direction = table.dataset.sortDirection === 'asc' ? -1 : 1;
        
        rows.sort((a, b) => {
            let aValue = a.cells[column].textContent.trim();
            let bValue = b.cells[column].textContent.trim();
            
            if (type === 'number') {
                aValue = parseInt(aValue);
                bValue = parseInt(bValue);
            } else if (type === 'date') {
                aValue = new Date(aValue);
                bValue = new Date(bValue);
            }
            
            if (aValue < bValue) return -1 * direction;
            if (aValue > bValue) return 1 * direction;
            return 0;
        });
        
        // Clear and re-append sorted rows
        tbody.innerHTML = '';
        rows.forEach(row => tbody.appendChild(row));
        
        // Update sort direction
        table.dataset.sortDirection = direction === 1 ? 'asc' : 'desc';
    }
    
    // Add click handlers to all sortable tables
    document.querySelectorAll('table').forEach(table => {
        const headers = table.querySelectorAll('th.sortable');
        
        headers.forEach((header, index) => {
            header.addEventListener('click', () => {
                // Remove sort classes from all headers
                headers.forEach(h => {
                    h.classList.remove('asc', 'desc');
                });
                
                // Add sort class to clicked header
                header.classList.add(table.dataset.sortDirection === 'asc' ? 'asc' : 'desc');
                
                // Determine column type
                let type = 'text';
                if (header.dataset.sort === 'id') type = 'number';
                if (header.dataset.sort === 'created_at') type = 'date';
                
                // Sort the table
                sortTable(table, index, type);
            });
        });
    });
});
</script> 