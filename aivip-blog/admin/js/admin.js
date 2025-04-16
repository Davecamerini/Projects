// Add notification banner to the page if it doesn't exist
if (!document.getElementById('notification')) {
    const notificationDiv = document.createElement('div');
    notificationDiv.id = 'notification';
    notificationDiv.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
    notificationDiv.style.cssText = 'display: none; z-index: 9999; min-width: 300px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.2);';
    notificationDiv.innerHTML = '<span id="notification-message"></span>';
    document.body.insertBefore(notificationDiv, document.body.firstChild);
}

// Add notification styles if they don't exist
if (!document.getElementById('notification-styles')) {
    const styleElement = document.createElement('style');
    styleElement.id = 'notification-styles';
    styleElement.textContent = `
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        .fade-out {
            animation: fadeOut 0.5s ease-out forwards;
        }
    `;
    document.head.appendChild(styleElement);
}

function showNotification(message, redirectUrl = null) {
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
            // Redirect or reload based on parameter
            if (redirectUrl) {
                window.location.replace(redirectUrl);
            } else {
                window.location.reload();
            }
        }, 500); // Wait for fade out animation to complete
    }, 2000);
}

// Initialize TinyMCE for post content
if (document.querySelector('#content')) {
    tinymce.init({
        selector: '#content',
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
        height: 500,
        // License configuration
        license_key: 'gpl',
        // Self-hosted configuration
        base_url: './js/tinymce',
        suffix: '.min',
        // Additional configuration
        skin: 'oxide',
        content_css: './js/tinymce/skins/content/default/content.min.css',
        language_url: './js/tinymce/langs/en.min.js',
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
                showNotification('Post saved successfully!');
            } else {
                showNotification('Error: ' + data.message);
            }
        } catch (error) {
            showNotification('Error saving post: ' + error.message);
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
            showNotification('Post deleted successfully!');
        } else {
            showNotification('Error: ' + data.message);
        }
    } catch (error) {
        showNotification('Error deleting post: ' + error.message);
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
            showNotification('Post status updated successfully');
        } else {
            showNotification('Error: ' + data.message);
        }
    } catch (error) {
        showNotification('Error updating post status: ' + error.message);
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
            showNotification('Media deleted successfully!');
        } else {
            showNotification('Error: ' + data.message);
        }
    } catch (error) {
        showNotification('Error deleting media: ' + error.message);
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