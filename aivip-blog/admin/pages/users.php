<?php
require_once '../config/database.php';

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Get query parameters
$page = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
$limit = 10;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$sort_direction = isset($_GET['direction']) ? $_GET['direction'] : 'desc';

// Validate sort column
$allowed_columns = ['id', 'username', 'email', 'first_name', 'last_name', 'role', 'status', 'created_at'];
if (!in_array($sort_column, $allowed_columns)) {
    $sort_column = 'created_at';
}

// Validate sort direction
if (!in_array($sort_direction, ['asc', 'desc'])) {
    $sort_direction = 'desc';
}

// Database connection
$db = new Database();
$conn = $db->getConnection();

// Build query
$query = "SELECT * FROM users WHERE 1=1";
$countQuery = "SELECT COUNT(*) as total FROM users WHERE 1=1";
$params = [];
$types = "";

// Add search filter
if ($search) {
    $searchTerm = "%$search%";
    $query .= " AND (username LIKE ? OR email LIKE ? OR first_name LIKE ? OR last_name LIKE ?)";
    $countQuery .= " AND (username LIKE ? OR email LIKE ? OR first_name LIKE ? OR last_name LIKE ?)";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $types .= "ssss";
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

// Get users
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$users = $stmt->get_result();

$db->closeConnection();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">User Management</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-person-plus me-2"></i>Add New User
        </button>
    </div>

    <!-- Search and Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Search users..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
                <input type="hidden" name="page" value="users">
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort_column); ?>">
                <input type="hidden" name="direction" value="<?php echo htmlspecialchars($sort_direction); ?>">
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th class="sortable" data-column="id">
                                ID
                                <?php if ($sort_column === 'id'): ?>
                                    <i class="bi bi-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-column="username">
                                Username
                                <?php if ($sort_column === 'username'): ?>
                                    <i class="bi bi-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-column="first_name">
                                Name
                                <?php if ($sort_column === 'first_name'): ?>
                                    <i class="bi bi-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-column="email">
                                Email
                                <?php if ($sort_column === 'email'): ?>
                                    <i class="bi bi-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-column="role">
                                Role
                                <?php if ($sort_column === 'role'): ?>
                                    <i class="bi bi-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-column="status">
                                Status
                                <?php if ($sort_column === 'status'): ?>
                                    <i class="bi bi-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-column="created_at">
                                Created
                                <?php if ($sort_column === 'created_at'): ?>
                                    <i class="bi bi-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                <?php endif; ?>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'primary'; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($user['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary edit-user" data-user='<?php echo json_encode($user); ?>'>
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-warning reset-password" data-id="<?php echo $user['id']; ?>">
                                    <i class="bi bi-key"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-user" data-id="<?php echo $user['id']; ?>">
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
                            <a class="page-link" href="?page=users&page_num=1&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort_column); ?>&direction=<?php echo urlencode($sort_direction); ?>">
                                First
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=users&page_num=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort_column); ?>&direction=<?php echo urlencode($sort_direction); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=users&page_num=<?php echo $totalPages; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort_column); ?>&direction=<?php echo urlencode($sort_direction); ?>">
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

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="author">Author</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveUser">Save User</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" id="edit_user_id" name="id">
                    <div class="mb-3">
                        <label for="edit_username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="edit_username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_role" class="form-label">Role</label>
                        <select class="form-select" id="edit_role" name="role" required>
                            <option value="author">Author</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <select class="form-select" id="edit_status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="updateUser">Update User</button>
            </div>
        </div>
    </div>
</div>

<style>
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

/* Fixed column widths */
.table th:nth-child(1), .table td:nth-child(1) { width: 5%; }  /* ID column */
.table th:nth-child(2), .table td:nth-child(2) { width: 15%; } /* Username column */
.table th:nth-child(3), .table td:nth-child(3) { width: 20%; } /* Name column */
.table th:nth-child(4), .table td:nth-child(4) { width: 25%; } /* Email column */
.table th:nth-child(5), .table td:nth-child(5) { width: 10%; } /* Role column */
.table th:nth-child(6), .table td:nth-child(6) { width: 10%; } /* Status column */
.table th:nth-child(7), .table td:nth-child(7) { width: 10%; } /* Created column */
.table th:nth-child(8), .table td:nth-child(8) { width: 5%; }  /* Actions column */

/* Ensure table cells don't wrap */
.table td, .table th {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Table styles */
.table-responsive {
    overflow: visible;
}

.table td {
    position: relative;
    overflow: visible;
    vertical-align: middle;
}

.table th {
    white-space: nowrap;
}

/* Column widths */
.table th:nth-child(1), .table td:nth-child(1) { width: 5%; } /* ID */
.table th:nth-child(2), .table td:nth-child(2) { width: 15%; } /* Username */
.table th:nth-child(3), .table td:nth-child(3) { width: 20%; } /* Name */
.table th:nth-child(4), .table td:nth-child(4) { width: 20%; } /* Email */
.table th:nth-child(5), .table td:nth-child(5) { width: 10%; } /* Role */
.table th:nth-child(6), .table td:nth-child(6) { width: 10%; } /* Status */
.table th:nth-child(7), .table td:nth-child(7) { width: 10%; } /* Created */
.table th:nth-child(8), .table td:nth-child(8) { width: 10%; } /* Actions */

/* Text overflow handling */
.table td:nth-child(2),
.table td:nth-child(3),
.table td:nth-child(4) { 
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Dropdown specific styles */
.dropdown {
    position: relative;
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    z-index: 1000;
    min-width: 8rem;
}
</style>

<script>
// Local showNotification function for users page
function showNotification(message) {
    const notification = document.getElementById('notification');
    const messageSpan = document.getElementById('notification-message');
    
    // Set message and show notification
    messageSpan.textContent = message;
    notification.style.display = 'block';
    
    // Hide after 2 seconds
    setTimeout(() => {
        notification.classList.add('fade-out');
        setTimeout(() => {
            notification.style.display = 'none';
            notification.classList.remove('fade-out');
            // Reload the current page
            window.location.reload();
        }, 500); // Wait for fade out animation to complete
    }, 2000);
}

document.addEventListener('DOMContentLoaded', function() {
    // Handle sortable columns
    document.querySelectorAll('.sortable').forEach(header => {
        header.addEventListener('click', function() {
            const column = this.dataset.column;
            const currentUrl = new URL(window.location.href);
            const currentSort = currentUrl.searchParams.get('sort');
            const currentDirection = currentUrl.searchParams.get('direction');
            
            // Determine new sort direction
            let newDirection = 'asc';
            if (currentSort === column) {
                newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
            }
            
            // Update URL parameters
            currentUrl.searchParams.set('sort', column);
            currentUrl.searchParams.set('direction', newDirection);
            currentUrl.searchParams.set('page_num', '1'); // Reset to first page when sorting changes
            
            // Navigate to new URL
            window.location.href = currentUrl.toString();
        });
    });

    // Add User
    document.getElementById('saveUser').addEventListener('click', function() {
        const form = document.getElementById('addUserForm');
        const formData = new FormData(form);
        
        fetch('../api/users/create.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close the modal
                bootstrap.Modal.getInstance(document.getElementById('addUserModal')).hide();
                // Show success notification
                showNotification('User created successfully!');
            } else {
                showNotification('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error creating user');
        });
    });

    // Edit User
    document.querySelectorAll('.edit-user').forEach(button => {
        button.addEventListener('click', function() {
            const user = JSON.parse(this.dataset.user);
            const form = document.getElementById('editUserForm');
            
            form.querySelector('#edit_user_id').value = user.id;
            form.querySelector('#edit_username').value = user.username;
            form.querySelector('#edit_email').value = user.email;
            form.querySelector('#edit_first_name').value = user.first_name;
            form.querySelector('#edit_last_name').value = user.last_name;
            form.querySelector('#edit_role').value = user.role;
            form.querySelector('#edit_status').value = user.status;
            
            new bootstrap.Modal(document.getElementById('editUserModal')).show();
        });
    });

    // Update User
    document.getElementById('updateUser').addEventListener('click', function() {
        const form = document.getElementById('editUserForm');
        const formData = new FormData(form);
        
        fetch('../api/users/update.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close the modal
                bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide();
                // Show success notification
                showNotification('User updated successfully!');
            } else {
                showNotification('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error updating user');
        });
    });

    // Delete User
    document.querySelectorAll('.delete-user').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this user?')) {
                const userId = this.dataset.id;
                
                fetch('../api/users/delete.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: userId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('User deleted successfully!');
                    } else {
                        showNotification('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error deleting user');
                });
            }
        });
    });

    // Handle reset password button click
    document.querySelectorAll('.reset-password').forEach(button => {
        button.addEventListener('click', async function() {
            const userId = this.dataset.id;
            const button = this;
            
            // Show confirmation dialog
            if (confirm('Are you sure you want to reset this user\'s password? A new random password will be generated.')) {
                // Disable button and show loading state
                button.disabled = true;
                button.innerHTML = '<i class="bi bi-hourglass-split"></i>';
                
                try {
                    const response = await fetch('../api/users/reset-password.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ id: userId })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        showNotification('Password reset successful! New password: ' + data.password);
                    } else {
                        showNotification('Error: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showNotification('Error resetting password');
                } finally {
                    // Re-enable button and restore icon
                    button.disabled = false;
                    button.innerHTML = '<i class="bi bi-key"></i>';
                }
            }
        });
    });
});
</script> 