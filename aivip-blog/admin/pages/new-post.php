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
                        <button type="button" class="btn btn-outline-secondary" onclick="window.open('?page=media', '_blank')">
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

                <div class="d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i><?php echo $isEditing ? 'Update Post' : 'Save Post'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('post-form');
    
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(form);
        const content = tinymce.get('content').getContent();
        formData.set('content', content);
        
        try {
            const response = await fetch('../api/posts/<?php echo $isEditing ? 'update' : 'create'; ?>.php', {
                method: 'POST',
                body: JSON.stringify(Object.fromEntries(formData)),
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                alert('Post <?php echo $isEditing ? 'updated' : 'created'; ?> successfully!');
                window.location.href = '?page=posts';
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            alert('Error saving post: ' + error.message);
        }
    });
});
</script> 