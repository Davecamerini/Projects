<?php
// Check if we're editing an existing post
$isEditing = isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id']);
$post = null;

if ($isEditing) {
    require_once '../config/database.php';
    $db = new Database();
    $conn = $db->getConnection();

    // Get post data
    $stmt = $conn->prepare("SELECT * FROM posts WHERE id = ? AND (author_id = ? OR ? = 'admin')");
    $postId = (int)$_GET['id'];
    $stmt->bind_param("iis", $postId, $_SESSION['user_id'], $_SESSION['role']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $post = $result->fetch_assoc();
    } else {
        header('Location: ?page=posts');
        exit;
    }

    $db->closeConnection();
}
?>

<!-- TinyMCE -->
<script src="../js/tinymce/tinymce.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        tinymce.init({
            selector: '#content',
            height: 600,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount', 'emoticons'
            ],
            toolbar: 'undo redo | formatselect | ' +
                    'bold italic backcolor | alignleft aligncenter ' +
                    'alignright alignjustify | bullist numlist outdent indent | ' +
                    'removeformat | help | fullscreen',
            menubar: 'file edit view insert format tools table help',
            content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; }',
            branding: false,
            promotion: false,
            // License configuration
            license_key: 'gpl',
            // Self-hosted configuration
            base_url: '../js/tinymce',
            suffix: '.min',
            // Additional configuration
            skin: 'oxide',
            content_css: '../js/tinymce/skins/content/default/content.min.css',
            language_url: '../js/tinymce/langs/en.min.js',
            language: 'en',
            // Image upload configuration
            images_upload_url: '../api/media/upload.php',
            images_upload_handler: function (blobInfo, success, failure) {
                var xhr, formData;
                xhr = new XMLHttpRequest();
                xhr.withCredentials = false;
                xhr.open('POST', '../api/media/upload.php');
                xhr.onload = function() {
                    var json;
                    if (xhr.status != 200) {
                        failure('HTTP Error: ' + xhr.status);
                        return;
                    }
                    json = JSON.parse(xhr.responseText);
                    if (!json || typeof json.location != 'string') {
                        failure('Invalid JSON: ' + xhr.responseText);
                        return;
                    }
                    success(json.location);
                };
                formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());
                xhr.send(formData);
            },
            // Additional features
            paste_data_images: true,
            image_advtab: true,
            link_list: [
                { title: 'My page 1', value: 'https://www.example.com' },
                { title: 'My page 2', value: 'https://www.example.com' }
            ],
            image_list: [
                { title: 'My image 1', value: 'https://www.example.com/image1.jpg' },
                { title: 'My image 2', value: 'https://www.example.com/image2.jpg' }
            ],
            templates: [
                { 
                    title: 'New Table',
                    description: 'creates a new table',
                    content: '<div class="mceTmpl"><table width="98%%"  border="0" cellspacing="0" cellpadding="0"><tr><th scope="col"> </th><th scope="col"> </th></tr><tr><td> </td><td> </td></tr></table></div>'
                }
            ],
            template_cdate_format: '[Date Created (CDATE): %m/%d/%Y : %H:%M:%S]',
            template_mdate_format: '[Date Modified (MDATE): %m/%d/%Y : %H:%M:%S]',
            image_caption: true,
            quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage quicktable',
            contextmenu: 'link image table configurepermanentpen',
            powerpaste_word_import: 'clean',
            powerpaste_html_import: 'clean',
            setup: function(editor) {
                editor.on('init', function() {
                    console.log('Editor initialized successfully');
                });
                editor.on('error', function(e) {
                    console.error('Editor error:', e);
                });
            }
        });
    });
</script>

<div class="container-fluid">
    <!-- Notification Banner -->
    <div id="notification" class="alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" style="display: none; z-index: 9999;">
        <span id="notification-message"></span>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?php echo $isEditing ? 'Edit Post' : 'New Post'; ?></h1>
        <a href="?page=posts" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Posts
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form id="post-form">
                <?php if ($isEditing): ?>
                <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" class="form-control" id="title" name="title" required
                           value="<?php echo $isEditing ? htmlspecialchars($post['title']) : ''; ?>">
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label">Slug</label>
                    <input type="text" class="form-control" id="slug" name="slug"
                           value="<?php echo $isEditing ? htmlspecialchars($post['slug']) : ''; ?>">
                    <div class="form-text">The URL-friendly version of the title. Will be auto-generated from the title if left empty.</div>
                </div>

                <div class="mb-3">
                    <label for="meta_title" class="form-label">Meta Title</label>
                    <input type="text" class="form-control" id="meta_title" name="meta_title"
                           value="<?php echo $isEditing ? htmlspecialchars($post['meta_title']) : ''; ?>">
                    <div class="form-text">Leave empty to use the post title</div>
                </div>

                <div class="mb-3">
                    <label for="meta_description" class="form-label">Meta Description</label>
                    <textarea class="form-control" id="meta_description" name="meta_description" rows="2"><?php echo $isEditing ? htmlspecialchars($post['meta_description']) : ''; ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="featured_image" class="form-label">Featured Image</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="featured_image" name="featured_image"
                               value="<?php echo $isEditing ? htmlspecialchars($post['featured_image']) : ''; ?>">
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#mediaSelectionModal">
                            Browse Media
                        </button>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label">Content</label>
                    <div id="content" class="form-control" style="min-height: 500px;"><?php echo $isEditing ? $post['content'] : ''; ?></div>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="draft" <?php echo $isEditing && $post['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                        <option value="published" <?php echo $isEditing && $post['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                        <option value="archived" <?php echo $isEditing && $post['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="categories" class="form-label">Categories</label>
                    <select class="form-select" id="categories" name="categories[]" multiple>
                        <?php
                        // Get categories
                        require_once '../config/database.php';
                        $db = new Database();
                        $conn = $db->getConnection();
                        
                        // Get all categories
                        $stmt = $conn->prepare("SELECT id, name FROM categories ORDER BY name ASC");
                        $stmt->execute();
                        $categories = $stmt->get_result();

                        // Get post categories if editing
                        $selectedCategories = [];
                        if ($isEditing) {
                            $stmt = $conn->prepare("SELECT category_id FROM post_categories WHERE post_id = ?");
                            $stmt->bind_param("i", $post['id']);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while ($row = $result->fetch_assoc()) {
                                $selectedCategories[] = $row['category_id'];
                            }
                        }

                        // Output category options
                        while ($category = $categories->fetch_assoc()): 
                            $isSelected = in_array($category['id'], $selectedCategories);
                        ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo $isSelected ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endwhile; 
                        
                        $db->closeConnection();
                        ?>
                    </select>
                    <div class="form-text">Hold Ctrl/Cmd to select multiple categories</div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i><?php echo $isEditing ? 'Update Post' : 'Save Post'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Media Selection Modal -->
<div class="modal fade" id="mediaSelectionModal" tabindex="-1" aria-labelledby="mediaSelectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mediaSelectionModalLabel">Select Media</h5>
                <button type="button" class="btn btn-primary ms-3" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    <i class="bi bi-cloud-upload me-2"></i>Upload Media
                </button>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row" id="mediaGrid">
                    <!-- Media items will be loaded here -->
                </div>
                <div class="d-flex justify-content-center mt-3">
                    <nav>
                        <ul class="pagination" id="mediaPagination">
                            <!-- Pagination will be loaded here -->
                        </ul>
                    </nav>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
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
                <form id="media-upload-form">
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
/* Add to existing styles */
#notification {
    min-width: 300px;
    text-align: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

/* Add animation for fade out */
@keyframes fadeOut {
    from { opacity: 1; }
    to { opacity: 0; }
}

.fade-out {
    animation: fadeOut 0.5s ease-out forwards;
}

.media-grid-item {
    cursor: pointer;
    transition: transform 0.2s;
    margin-bottom: 1rem;
}

.media-grid-item:hover {
    transform: scale(1.05);
}

.media-grid-item.selected {
    border: 3px solid #0d6efd;
}

.media-grid-item img {
    width: 100%;
    height: 150px;
    object-fit: cover;
}
</style>

<script>
function showNotification(message, redirect) {
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
            // Redirect to posts page after notification disappears
            window.location.replace(redirect);
        }, 500);
    }, 2000);
}

// Function to generate slug from title
function generateSlug(title) {
    // Split the title into words, take first 4, and join with hyphens
    const words = title
        .toLowerCase()
        .trim()
        .split(/\s+/)
        .slice(0, 4);
    
    // Join words with hyphens and clean up
    return words
        .join('-')
        .replace(/[^a-z0-9-]/g, '') // Remove non-alphanumeric characters
        .replace(/-+/g, '-') // Replace multiple hyphens with single hyphen
        .replace(/^-|-$/g, ''); // Remove leading/trailing hyphens
}

// Initialize slug field on page load
document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    
    // Set initial slug value if title exists and slug is empty
    if (titleInput.value && !slugInput.value) {
        slugInput.value = generateSlug(titleInput.value);
    }
    
    // Add event listener for title input
    titleInput.addEventListener('input', function(e) {
        console.log('Input event triggered');
        console.log('Current title value:', this.value);
        const newSlug = generateSlug(this.value);
        console.log('Generated slug:', newSlug);
        slugInput.value = newSlug;
    });

    // Add event listener for title change
    titleInput.addEventListener('change', function(e) {
        console.log('Change event triggered');
        console.log('Current title value:', this.value);
        const newSlug = generateSlug(this.value);
        console.log('Generated slug:', newSlug);
        slugInput.value = newSlug;
    });
});

// Add event listener for form submission
document.getElementById('post-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Get form data
    const formData = new FormData(this);
    const data = {};
    
    // Convert FormData to object
    for (let [key, value] of formData.entries()) {
        if (key === 'categories[]') {
            // Handle multiple categories
            if (!data.categories) {
                data.categories = [];
            }
            data.categories.push(value);
        } else {
            data[key] = value;
        }
    }
    
    // Add content from TinyMCE
    data.content = tinymce.get('content').getContent();
    
    // Send data to API
    fetch('../api/posts/<?php echo $isEditing ? 'update.php' : 'create.php'; ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, '?page=posts');
        } else {
            alert(data.message || 'An error occurred');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while saving the post');
    });
});

// Function to load media items
function loadMediaItems(page = 1) {
    console.log('Loading media items, page:', page);
    fetch(`../api/media/list.php?page=${page}&limit=12`)
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Media data:', data);
            if (data.success) {
                const mediaGrid = document.getElementById('mediaGrid');
                mediaGrid.innerHTML = '';
                
                if (data.data && data.data.length > 0) {
                    data.data.forEach(item => {
                        console.log('Processing media item:', item);
                        const col = document.createElement('div');
                        col.className = 'col-md-3';
                        col.innerHTML = `
                            <div class="media-grid-item" data-path="${item.filepath}">
                                <img src="${item.filepath}" alt="${item.filename}" class="img-fluid">
                                <div class="text-center mt-2">
                                    <small class="text-muted">${item.filename}</small>
                                </div>
                            </div>
                        `;
                        mediaGrid.appendChild(col);
                    });

                    // Add click handlers
                    document.querySelectorAll('.media-grid-item').forEach(item => {
                        item.addEventListener('click', function() {
                            const path = this.dataset.path;
                            document.getElementById('featured_image').value = path;
                            const modal = bootstrap.Modal.getInstance(document.getElementById('mediaSelectionModal'));
                            modal.hide();
                        });
                    });

                    // Update pagination
                    updatePagination(data.total, page);
                } else {
                    console.log('No media items found');
                    mediaGrid.innerHTML = '<div class="col-12 text-center"><p>No media items found</p></div>';
                }
            } else {
                console.error('API returned error:', data.message);
                mediaGrid.innerHTML = `<div class="col-12 text-center"><p class="text-danger">Error: ${data.message}</p></div>`;
            }
        })
        .catch(error => {
            console.error('Error loading media:', error);
            const mediaGrid = document.getElementById('mediaGrid');
            mediaGrid.innerHTML = `<div class="col-12 text-center"><p class="text-danger">Error loading media: ${error.message}</p></div>`;
        });
}

// Function to update pagination
function updatePagination(total, currentPage) {
    const pagination = document.getElementById('mediaPagination');
    pagination.innerHTML = '';
    
    const totalPages = Math.ceil(total / 12);
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);

    // Previous button
    if (currentPage > 1) {
        const li = document.createElement('li');
        li.className = 'page-item';
        li.innerHTML = `<a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>`;
        pagination.appendChild(li);
    }

    // Page numbers
    for (let i = startPage; i <= endPage; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${i === currentPage ? 'active' : ''}`;
        li.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
        pagination.appendChild(li);
    }

    // Next button
    if (currentPage < totalPages) {
        const li = document.createElement('li');
        li.className = 'page-item';
        li.innerHTML = `<a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>`;
        pagination.appendChild(li);
    }

    // Add click handlers
    pagination.querySelectorAll('.page-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const page = parseInt(this.dataset.page);
            loadMediaItems(page);
        });
    });
}

// Load media items when modal is shown
document.getElementById('mediaSelectionModal').addEventListener('shown.bs.modal', function () {
    loadMediaItems();
});

// Handle media upload
document.getElementById('media-upload-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('../api/media/upload.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        console.log('Upload response:', data); // Debug log
        
        if (data.success) {
            // Get the filepath from the response data
            let filepath = data.data?.filepath;
            console.log('Original filepath:', filepath); // Debug log
            
            if (!filepath) {
                throw new Error('No filepath returned from server');
            }
            
            // Get current protocol (http or https)
            const currentProtocol = window.location.protocol;
            console.log('Current protocol:', currentProtocol); // Debug log
            
            // Update the filepath to use the current protocol
            if (filepath.startsWith('http://') || filepath.startsWith('https://')) {
                filepath = filepath.replace(/^https?:\/\//, currentProtocol + '//');
            }
            console.log('Updated filepath:', filepath); // Debug log
            
            // Close the upload modal
            const uploadModal = bootstrap.Modal.getInstance(document.getElementById('uploadModal'));
            uploadModal.hide();
            
            // Set the featured image field with the new image path
            const featuredImageInput = document.getElementById('featured_image');
            featuredImageInput.value = filepath;
            console.log('Featured image value set to:', featuredImageInput.value); // Debug log
            
            // Reload the media grid
            loadMediaItems();
            
            // Show success notification
            showNotification('Media uploaded successfully!');
            
            // Close the media selection modal
            const mediaModal = bootstrap.Modal.getInstance(document.getElementById('mediaSelectionModal'));
            mediaModal.hide();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error uploading file:', error);
        alert('Error uploading file: ' + error.message);
    }
});

// Function to show notification
function showNotification(message) {
    const notification = document.getElementById('notification');
    const messageSpan = document.getElementById('notification-message');
    
    messageSpan.textContent = message;
    notification.classList.remove('d-none');
    
    setTimeout(() => {
        notification.classList.add('d-none');
    }, 3000);
}
</script> 