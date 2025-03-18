<?php
/*
Plugin Name: Video Streaming Plugin
Description: Allows users to upload videos and stream them.
Version: 1.0
Author: <a href="https://www.davecamerini.com">Davecamerini</a>
*/

// Define the upload directory
define('VIDEO_UPLOAD_DIR', ABSPATH . 'wp-content/uploads/videos');

// Add thumbnail cache directory constant
define('THUMBNAIL_CACHE_DIR', VIDEO_UPLOAD_DIR . '/thumbnails');

// Create the upload directory on plugin activation
function vsp_create_upload_dir() {
    if (!file_exists(VIDEO_UPLOAD_DIR)) {
        mkdir(VIDEO_UPLOAD_DIR, 0755, true);
    }
    if (!file_exists(THUMBNAIL_CACHE_DIR)) {
        mkdir(THUMBNAIL_CACHE_DIR, 0755, true);
    }
}
register_activation_hook(__FILE__, 'vsp_create_upload_dir');

// Function to recursively get folder structure
function vsp_get_folder_structure($base_dir, $current_dir = '') {
    $structure = [];
    $full_path = rtrim($base_dir . '/' . $current_dir, '/');
    
    if (!is_dir($full_path)) {
        return [];
    }

    $items = scandir($full_path);
    foreach ($items as $item) {
        if ($item !== '.' && $item !== '..') {
            $item_path = $full_path . '/' . $item;
            if (is_dir($item_path)) {
                $relative_path = $current_dir ? $current_dir . '/' . $item : $item;
                $subfolders = vsp_get_folder_structure($base_dir, $relative_path);
                $structure[] = [
                    'name' => $item,
                    'path' => $relative_path,
                    'subfolders' => $subfolders
                ];
            }
        }
    }
    
    return $structure;
}

// Function to render folder tree
function vsp_render_folder_tree($folders, $current_dir = '') {
    if (empty($folders)) {
        return '';
    }

    $output = '<ul>';
    foreach ($folders as $folder) {
        $is_active = $folder['path'] === $current_dir;
        $has_subfolders = !empty($folder['subfolders']);
        $output .= '<li>';
        $output .= '<div class="folder-item' . ($is_active ? ' active' : '') . '" data-path="' . esc_attr($folder['path']) . '">';
        if ($has_subfolders) {
            $output .= '<span class="toggle-icon"></span>';
        } else {
            $output .= '<span class="toggle-icon" style="visibility: hidden;"></span>';
        }
        $output .= '<span class="folder-icon' . ($is_active ? ' open' : '') . '"></span>';
        $output .= '<span class="folder-name">' . esc_html($folder['name']) . '</span>';
        $output .= '</div>';
        
        if ($has_subfolders) {
            $output .= '<div class="subfolders' . ($is_active ? ' open' : '') . '">';
            $output .= vsp_render_folder_tree($folder['subfolders'], $current_dir);
            $output .= '</div>';
        }
        
        $output .= '</li>';
    }
    $output .= '</ul>';
    return $output;
}

// Function to get cached thumbnail path
function vsp_get_cached_thumbnail_path($source_path) {
    $filename = basename($source_path);
    $hash = md5($source_path . filemtime($source_path)); // Include file modification time in hash
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    return THUMBNAIL_CACHE_DIR . '/' . $hash . '.' . $ext;
}

// Create a shortcode to display the video upload form and list
function vsp_video_page() {
    ob_start();
    
    // Get the current folder from the URL
    $current_dir = isset($_GET['folder']) ? sanitize_text_field($_GET['folder']) : '';
    $video_dir = rtrim(VIDEO_UPLOAD_DIR . '/' . $current_dir, '/');

    // Get folder structure
    $folder_structure = vsp_get_folder_structure(VIDEO_UPLOAD_DIR);

    // List videos and images in current directory
    $items = scandir($video_dir);
    $media_files = [];

    foreach ($items as $item) {
        if ($item !== '.' && $item !== '..') {
            if (preg_match('/\.(mp4|m4v|webm|ogg|flv)$/i', $item)) {
                $media_files[] = ['type' => 'video', 'name' => $item];
            } elseif (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $item)) {
                $media_files[] = ['type' => 'image', 'name' => $item];
            }
        }
    }

    // Sort media files naturally by name
    usort($media_files, function($a, $b) {
        return strnatcmp($a['name'], $b['name']);
    });

    // Start output
    echo '<div class="vsp-container">';
    
    // Tree View Sidebar
    echo '<div class="vsp-tree-view">';
    echo '<h3>Folders</h3>';
    // Add Root folder option
    echo '<div class="folder-item' . (empty($current_dir) ? ' active' : '') . '" data-path="">';
    echo '<span class="toggle-icon" style="visibility: hidden;"></span>';
    echo '<span class="folder-icon"></span>';
    echo '<span class="folder-name">Root</span>';
    echo '</div>';
    echo vsp_render_folder_tree($folder_structure, $current_dir);
    echo '</div>';

    // Main Content Area
    echo '<div class="vsp-content">';
    // Show current folder path as title
    if ($current_dir) {
        $path_parts = explode('/', $current_dir);
        $folder_name = end($path_parts);
        $parent_folder = count($path_parts) > 1 ? $path_parts[count($path_parts) - 2] : '';
        echo '<h2 class="vsp-title">' . esc_html($parent_folder ? $parent_folder . ' / ' . $folder_name : $folder_name) . '</h2>';
    } else {
        echo '<h2 class="vsp-title">Root</h2>';
    }

    // Media List
    if (!empty($media_files)) {
        echo '<table class="vsp-video-table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th onclick="sortTable(0)">Name <span class="sort-icon">↕</span></th>';
        echo '<th onclick="sortTable(1)">Size <span class="sort-icon">↕</span></th>';
        echo '<th>Actions</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        foreach ($media_files as $file) {
            $file_path = $current_dir ? $current_dir . '/' . $file['name'] : $file['name'];
            $full_path = VIDEO_UPLOAD_DIR . '/' . $file_path;
            $size = filesize($full_path);
            $formatted_size = vsp_format_file_size($size);
            $media_url = wp_upload_dir()['baseurl'] . '/videos/' . $file_path;
            
            echo '<tr>';
            echo '<td>';
            if ($file['type'] === 'video') {
                echo '<a href="#" class="vsp-video-link" data-video="' . esc_url($media_url) . '">' . esc_html($file['name']) . '</a>';
            } else {
                $thumbnail_url = '';
                $thumbnail_file = vsp_create_thumbnail($full_path);
                if ($thumbnail_file) {
                    $thumbnail_url = wp_upload_dir()['baseurl'] . '/videos/thumbnails/' . basename($thumbnail_file);
                }
                
                echo '<div class="vsp-media-item">';
                if ($thumbnail_url) {
                    echo '<img src="' . esc_url($thumbnail_url) . '" class="vsp-thumbnail" alt="' . esc_attr($file['name']) . '" loading="lazy">';
                }
                echo '<a href="#" class="vsp-image-link" data-image="' . esc_url($media_url) . '">' . esc_html($file['name']) . '</a>';
                echo '</div>';
            }
            echo '</td>';
            echo '<td>' . esc_html($formatted_size) . '</td>';
            echo '<td class="vsp-video-actions">';
            echo '<button class="vsp-rename-video" data-video-name="' . esc_attr($file['name']) . '">Rename</button>';
            echo '<button class="vsp-delete-video" data-video-name="' . esc_attr($file['name']) . '">Delete</button>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p class="vsp-no-items">No media files found in this folder.</p>';
    }
    
    // Media Overlay
    echo '<div id="vsp-video-overlay" class="vsp-video-overlay">';
    echo '<div class="vsp-video-overlay-content">';
    echo '<span class="vsp-close-overlay">&times;</span>';
    echo '<video id="vsp-overlay-video" controls style="display: none;"></video>';
    echo '<img id="vsp-overlay-image" style="display: none; max-width: 100%; max-height: 90vh;">';
    echo '</div>';
    echo '</div>';
    
    echo '</div>'; // End vsp-content
    echo '</div>'; // End vsp-container

    return ob_get_clean();
}
add_shortcode('video_streaming', 'vsp_video_page');

// Helper function to format file size
function vsp_format_file_size($bytes) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}

// Add a function to handle video deletion
function vsp_delete_video() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied.');
        return;
    }

    $video_name = sanitize_text_field($_POST['video_name']);
    $folder = isset($_POST['folder']) ? sanitize_text_field($_POST['folder']) : '';
    
    // Construct the full path including the folder
    $video_path = $folder ? $folder . '/' . $video_name : $video_name;
    $full_path = VIDEO_UPLOAD_DIR . '/' . $video_path;

    if (file_exists($full_path)) {
        if (unlink($full_path)) {
            wp_send_json_success('Video deleted successfully.');
        } else {
            wp_send_json_error('Error deleting video.');
        }
    } else {
        wp_send_json_error('Video not found.');
    }
}
add_action('wp_ajax_delete_video', 'vsp_delete_video');

// Add a function to handle video renaming
function vsp_rename_video() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied.');
        return;
    }

    $old_name = sanitize_text_field($_POST['old_name']);
    $new_name = sanitize_text_field($_POST['new_name']);
    $folder = isset($_POST['folder']) ? sanitize_text_field($_POST['folder']) : '';
    
    // Construct the full paths including the folder
    $old_path = $folder ? $folder . '/' . $old_name : $old_name;
    $new_path = $folder ? $folder . '/' . $new_name : $new_name;
    $old_full_path = VIDEO_UPLOAD_DIR . '/' . $old_path;
    $new_full_path = VIDEO_UPLOAD_DIR . '/' . $new_path;

    if (file_exists($old_full_path)) {
        if (rename($old_full_path, $new_full_path)) {
            wp_send_json_success('Video renamed successfully.');
        } else {
            wp_send_json_error('Error renaming video.');
        }
    } else {
        wp_send_json_error('Video not found.');
    }
}
add_action('wp_ajax_rename_video', 'vsp_rename_video');

// Enqueue JavaScript for handling delete and rename actions
function vsp_enqueue_scripts() {
    wp_enqueue_script('videojs', 'https://vjs.zencdn.net/7.11.4/video.min.js', array(), null, true);
    wp_enqueue_style('videojs-css', 'https://vjs.zencdn.net/7.11.4/video-js.min.css');
    wp_enqueue_style('custom-style', plugin_dir_url(__FILE__) . 'custom-style.css');
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');

    // Enqueue custom script for handling delete and rename
    wp_enqueue_script('vsp-custom-script', plugin_dir_url(__FILE__) . 'custom-script.js', array('jquery'), null, true);
    
    // Localize script to make ajaxurl available
    wp_localize_script('vsp-custom-script', 'ajaxurl', admin_url('admin-ajax.php'));
}
add_action('wp_enqueue_scripts', 'vsp_enqueue_scripts');

// Add JavaScript for sorting functionality
function vsp_sorting_script() {
    ?>
    <script>
        function convertToBytes(size) {
            const units = {
                'B': 1,
                'KB': 1024,
                'MB': 1024 * 1024,
                'GB': 1024 * 1024 * 1024,
                'TB': 1024 * 1024 * 1024 * 1024
            };
            const match = size.match(/(\d+(\.\d+)?)\s*(B|KB|MB|GB|TB)/i);
            if (match) {
                const value = parseFloat(match[1]);
                const unit = match[3].toUpperCase();
                return value * units[unit];
            }
            return 0; // Default to 0 if no match
        }

        function sortTable(columnIndex) {
            const table = document.querySelector('.vsp-video-table tbody');
            const rows = Array.from(table.rows);
            const isAscending = table.dataset.sortOrder === 'asc';
            const direction = isAscending ? 1 : -1;

            rows.sort((a, b) => {
                const aText = a.cells[columnIndex].innerText;
                const bText = b.cells[columnIndex].innerText;

                if (columnIndex === 0) { // Video column
                    // Custom comparison for video names
                    return aText.localeCompare(bText, undefined, { numeric: true, sensitivity: 'base' }) * direction;
                } else if (columnIndex === 1) { // Size column
                    const aSizeInBytes = convertToBytes(aText);
                    const bSizeInBytes = convertToBytes(bText);
                    return (aSizeInBytes - bSizeInBytes) * direction;
                }
                return aText.localeCompare(bText) * direction;
            });

            // Clear the table and append sorted rows
            table.innerHTML = '';
            rows.forEach(row => table.appendChild(row));

            // Toggle sort order
            table.dataset.sortOrder = isAscending ? 'desc' : 'asc';
        }
    </script>
    <?php
}
add_action('wp_footer', 'vsp_sorting_script');

// Modified thumbnail creation function with caching
function vsp_create_thumbnail($source_path, $width = 40, $height = 40) {
    // Check if file exists and is readable
    if (!file_exists($source_path) || !is_readable($source_path)) {
        return false;
    }

    $cached_path = vsp_get_cached_thumbnail_path($source_path);
    
    // Return cached thumbnail if it exists and is newer than source
    if (file_exists($cached_path) && filemtime($cached_path) >= filemtime($source_path)) {
        return $cached_path;
    }

    $image_info = getimagesize($source_path);
    if (!$image_info) return false;

    $source_image = null;
    switch ($image_info[2]) {
        case IMAGETYPE_JPEG:
            $source_image = imagecreatefromjpeg($source_path);
            break;
        case IMAGETYPE_PNG:
            $source_image = imagecreatefrompng($source_path);
            break;
        case IMAGETYPE_GIF:
            $source_image = imagecreatefromgif($source_path);
            break;
        case IMAGETYPE_WEBP:
            $source_image = imagecreatefromwebp($source_path);
            break;
        default:
            return false;
    }

    if (!$source_image) return false;

    // Create thumbnail
    $thumbnail = imagecreatetruecolor($width, $height);
    
    // Preserve transparency for PNG
    if ($image_info[2] === IMAGETYPE_PNG) {
        imagealphablending($thumbnail, false);
        imagesavealpha($thumbnail, true);
        $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
        imagefilledrectangle($thumbnail, 0, 0, $width, $height, $transparent);
    }

    // Resize image
    imagecopyresampled($thumbnail, $source_image, 0, 0, 0, 0, $width, $height, imagesx($source_image), imagesy($source_image));

    // Save to cache directory
    switch ($image_info[2]) {
        case IMAGETYPE_JPEG:
            imagejpeg($thumbnail, $cached_path, 30);
            break;
        case IMAGETYPE_PNG:
            imagepng($thumbnail, $cached_path, 6);
            break;
        case IMAGETYPE_GIF:
            imagegif($thumbnail, $cached_path);
            break;
        case IMAGETYPE_WEBP:
            imagewebp($thumbnail, $cached_path, 30);
            break;
    }

    // Clean up
    imagedestroy($source_image);
    imagedestroy($thumbnail);

    return $cached_path;
}