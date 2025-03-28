<?php
require_once '../config/database.php';

// Function to ensure correct image path
function getImagePath($path) {
    // If path starts with '../', it's already correct
    if (strpos($path, '../') === 0) {
        return $path;
    }
    // If path starts with '/', remove it and add '../'
    if (strpos($path, '/') === 0) {
        return '../' . ltrim($path, '/');
    }
    // If no leading slash or ../, add '../'
    return '../' . $path;
}

// Get query parameters
$page = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
$limit = 12; // Show 12 items per page in grid view

// Database connection
$db = new Database();
$conn = $db->getConnection();

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM media WHERE uploaded_by = ? OR ? = 'admin'";
$countStmt = $conn->prepare($countQuery);
$countStmt->bind_param("is", $_SESSION['user_id'], $_SESSION['role']);
$countStmt->execute();
$total = $countStmt->get_result()->fetch_assoc()['total'];

// Calculate offset
$offset = ($page - 1) * $limit;

// Get media items
$query = "SELECT m.*, u.username as uploader_name 
          FROM media m 
          JOIN users u ON m.uploaded_by = u.id 
          WHERE m.uploaded_by = ? OR ? = 'admin'
          ORDER BY m.upload_date DESC 
          LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("isii", $_SESSION['user_id'], $_SESSION['role'], $limit, $offset);
$stmt->execute();
$media = $stmt->get_result();

$db->closeConnection();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Media Library</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
            <i class="bi bi-cloud-upload me-2"></i>Upload Media
        </button>
    </div>

    <!-- Image Overlay -->
    <div id="imageOverlay" class="image-overlay">
        <span class="close-overlay">&times;</span>
        <img id="overlayImage" class="overlay-content">
    </div>

    <!-- Media Grid -->
    <div class="card">
        <div class="card-body">
            <div class="row g-4">
                <?php while ($item = $media->fetch_assoc()): ?>
                <div class="col-md-3">
                    <div class="card h-100">
                        <img src="<?php echo htmlspecialchars(getImagePath($item['path'])); ?>" 
                             class="card-img-top media-image" 
                             alt="<?php echo htmlspecialchars($item['filename']); ?>"
                             data-full-src="<?php echo htmlspecialchars(getImagePath($item['path'])); ?>"
                             style="height: 200px; object-fit: cover; cursor: pointer;">
                        <div class="card-body">
                            <h6 class="card-title text-truncate"><?php echo htmlspecialchars($item['filename']); ?></h6>
                            <p class="card-text small text-muted">
                                Uploaded by: <?php echo htmlspecialchars($item['uploader_name']); ?><br>
                                Date: <?php echo date('M j, Y', strtotime($item['upload_date'])); ?>
                            </p>
                            <div class="btn-group w-100">
                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                        onclick="copyToClipboard('<?php echo htmlspecialchars($item['path']); ?>')">
                                    <i class="bi bi-link-45deg"></i> Copy URL
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        onclick="deleteMedia(<?php echo $item['id']; ?>)">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
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
                            <a class="page-link" href="?page=media&page_num=1">First</a>
                        </li>
                        <?php endif; ?>

                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=media&page_num=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=media&page_num=<?php echo $totalPages; ?>">Last</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Media</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="media-form">
                    <div class="mb-3">
                        <label for="image" class="form-label">Choose Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                        <div class="form-text">
                            Supported formats: JPG, JPEG, PNG, GIF<br>
                            Maximum size: 5MB
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-cloud-upload me-2"></i>Upload
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.image-overlay {
    display: none;
    position: fixed;
    z-index: 9999;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.9);
    overflow: auto;
}

.overlay-content {
    margin: auto;
    display: block;
    max-width: 90%;
    max-height: 90vh;
    margin-top: 5vh;
}

.close-overlay {
    position: absolute;
    right: 35px;
    top: 15px;
    color: #f1f1f1;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
}

.close-overlay:hover {
    color: #bbb;
}

.media-image {
    transition: transform 0.2s;
}

.media-image:hover {
    transform: scale(1.05);
}
</style>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('URL copied to clipboard!');
    }).catch(err => {
        console.error('Failed to copy URL:', err);
    });
}

// Image overlay functionality
document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('imageOverlay');
    const overlayImg = document.getElementById('overlayImage');
    const closeBtn = document.querySelector('.close-overlay');
    const mediaImages = document.querySelectorAll('.media-image');

    // Open overlay when clicking on an image
    mediaImages.forEach(img => {
        img.addEventListener('click', function() {
            overlay.style.display = 'block';
            overlayImg.src = this.getAttribute('data-full-src');
        });
    });

    // Close overlay when clicking the close button
    closeBtn.addEventListener('click', function() {
        overlay.style.display = 'none';
    });

    // Close overlay when clicking outside the image
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            overlay.style.display = 'none';
        }
    });

    // Close overlay with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && overlay.style.display === 'block') {
            overlay.style.display = 'none';
        }
    });
});
</script> 