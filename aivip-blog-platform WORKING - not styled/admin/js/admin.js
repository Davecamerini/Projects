// Initialize TinyMCE for post content
if (document.querySelector('#post-content')) {
    tinymce.init({
        selector: '#post-content',
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
        height: 500,
        // License configuration
        license_key: 'gpl',
        // Self-hosted configuration
        base_url: '/admin/js/tinymce',
        suffix: '.min',
        // Additional configuration
        skin: 'oxide',
        content_css: '/admin/js/tinymce/skins/content/default/content.min.css',
        language_url: '/admin/js/tinymce/langs/en.min.js',
        language: 'en'
    });
}

// Handle post form submission
const postForm = document.querySelector('#post-form');
if (postForm) {
    postForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(postForm);
        const content = tinymce.get('post-content').getContent();
        formData.append('content', content);

        try {
            const response = await fetch('../api/posts/create.php', {
                method: 'POST',
                body: JSON.stringify(Object.fromEntries(formData)),
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                alert('Post saved successfully!');
                window.location.href = '?page=posts';
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            alert('Error saving post: ' + error.message);
        }
    });
}

// Handle media upload
const mediaForm = document.querySelector('#media-form');
if (mediaForm) {
    mediaForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(mediaForm);

        try {
            const response = await fetch('../api/media/upload.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            
            if (data.success) {
                alert('File uploaded successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            alert('Error uploading file: ' + error.message);
        }
    });
}

// Handle post deletion
async function deletePost(postId) {
    if (!confirm('Are you sure you want to delete this post?')) {
        return;
    }

    try {
        const response = await fetch('../api/posts/delete.php', {
            method: 'POST',
            body: JSON.stringify({ id: postId }),
            headers: {
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();
        
        if (data.success) {
            alert('Post deleted successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Error deleting post: ' + error.message);
    }
}

// Handle post status change
async function changePostStatus(postId, status) {
    try {
        const response = await fetch('../api/posts/update.php', {
            method: 'POST',
            body: JSON.stringify({
                id: postId,
                status: status
            }),
            headers: {
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();
        
        if (data.success) {
            alert('Post status updated successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Error updating post status: ' + error.message);
    }
}

// Handle media deletion
async function deleteMedia(mediaId) {
    if (!confirm('Are you sure you want to delete this media file?')) {
        return;
    }

    try {
        const response = await fetch('../api/media/delete.php', {
            method: 'POST',
            body: JSON.stringify({ id: mediaId }),
            headers: {
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();
        
        if (data.success) {
            alert('Media deleted successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Error deleting media: ' + error.message);
    }
}

// Handle search and pagination
function updateQueryString(params) {
    const searchParams = new URLSearchParams(window.location.search);
    
    for (const [key, value] of Object.entries(params)) {
        if (value) {
            searchParams.set(key, value);
        } else {
            searchParams.delete(key);
        }
    }

    window.location.search = searchParams.toString();
} 