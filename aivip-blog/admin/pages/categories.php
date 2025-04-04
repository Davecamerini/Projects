<?php
require_once '../config/database.php';

// Get categories
$db = new Database();
$conn = $db->getConnection();

// Get all categories with post count
$stmt = $conn->prepare("
    SELECT c.*, 
           COUNT(DISTINCT pc.post_id) as post_count,
           parent.name as parent_name
    FROM categories c
    LEFT JOIN post_categories pc ON c.id = pc.category_id
    LEFT JOIN categories parent ON c.parent_id = parent.id
    GROUP BY c.id
    ORDER BY c.name ASC
");
$stmt->execute();
$categories = $stmt->get_result();

// Get parent categories for dropdown
$parentQuery = "SELECT id, name FROM categories WHERE parent_id IS NULL";
$parentResult = $conn->query($parentQuery);
$parentCategories = $parentResult->fetch_all(MYSQLI_ASSOC);

$db->closeConnection();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Categories</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
            <i class="bi bi-plus-circle me-2"></i>New Category
        </button>
    </div>

    <!-- Categories List -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Parent</th>
                            <th>Posts</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($category = $categories->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div class="fw-bold"><?php echo htmlspecialchars($category['name']); ?></div>
                                <?php if ($category['description']): ?>
                                <div class="small text-muted"><?php echo htmlspecialchars($category['description']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($category['slug']); ?></td>
                            <td><?php echo $category['parent_name'] ? htmlspecialchars($category['parent_name']) : '-'; ?></td>
                            <td><?php echo $category['post_count']; ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary" 
                                        onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="Edit Category">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <?php if ($category['post_count'] == 0): ?>
                                <button class="btn btn-sm btn-danger" 
                                        onclick="deleteCategory(<?php echo $category['id']; ?>)"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="Delete Category">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="categoryForm">
                    <input type="hidden" id="categoryId" name="id">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug</label>
                        <input type="text" class="form-control" id="slug" name="slug">
                        <div class="form-text">Leave empty to auto-generate from name</div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="parent_id" class="form-label">Parent Category</label>
                        <select class="form-select" id="parent_id" name="parent_id">
                            <option value="">None</option>
                            <?php 
                            // Reset categories result pointer
                            $categories->data_seek(0);
                            while ($cat = $categories->fetch_assoc()): 
                            ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveCategory()">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Auto-generate slug from name
    document.getElementById('name').addEventListener('input', function() {
        const slug = document.getElementById('slug');
        if (!slug.value) {
            slug.value = this.value
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/(^-|-$)/g, '');
        }
    });

    // Reset form when modal is closed
    document.getElementById('categoryModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('categoryForm').reset();
        document.getElementById('categoryId').value = '';
        document.querySelector('#categoryModal .modal-title').textContent = 'Add Category';
    });
});

// Edit category
function editCategory(category) {
    document.getElementById('categoryId').value = category.id;
    document.getElementById('name').value = category.name;
    document.getElementById('slug').value = category.slug;
    document.getElementById('description').value = category.description || '';
    document.getElementById('parent_id').value = category.parent_id || '';
    
    // Disable selecting self or children as parent
    const parentSelect = document.getElementById('parent_id');
    Array.from(parentSelect.options).forEach(option => {
        option.disabled = option.value === category.id;
    });
    
    document.querySelector('#categoryModal .modal-title').textContent = 'Edit Category';
    new bootstrap.Modal(document.getElementById('categoryModal')).show();
}

// Save category
async function saveCategory() {
    const form = document.getElementById('categoryForm');
    const formData = new FormData(form);
    const id = formData.get('id');
    const data = Object.fromEntries(formData);
    
    // Clean up empty values
    if (!data.parent_id) delete data.parent_id;
    if (!data.description) delete data.description;
    if (!data.slug) delete data.slug;
    
    try {
        const response = await fetch('../api/categories/' + (id ? 'update.php' : 'create.php'), {
            method: 'POST',
            body: JSON.stringify(data),
            headers: {
                'Content-Type': 'application/json'
            }
        });

        const result = await response.json();
        
        if (result.success) {
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error saving category:', error);
        alert('Error saving category. Please try again.');
    }
}

// Delete category
async function deleteCategory(id) {
    if (!confirm('Are you sure you want to delete this category? Child categories will be moved to this category\'s parent.')) {
        return;
    }
    
    try {
        const response = await fetch('../api/categories/delete.php', {
            method: 'POST',
            body: JSON.stringify({ id: id }),
            headers: {
                'Content-Type': 'application/json'
            }
        });

        const result = await response.json();
        
        if (result.success) {
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error deleting category:', error);
        alert('Error deleting category. Please try again.');
    }
}
</script> 