<?php
/*
Plugin Name: Video Streaming Plugin
Description: Allows users to upload videos and stream them.
Version: 1.0
Author: <a href="https://www.davecamerini.com">Davecamerini</a>
*/

// Define the upload directory
define('VIDEO_UPLOAD_DIR', ABSPATH . 'wp-content/uploads/videos');

// Create the upload directory on plugin activation
function vsp_create_upload_dir() {
    if (!file_exists(VIDEO_UPLOAD_DIR)) {
        mkdir(VIDEO_UPLOAD_DIR, 0755, true);
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
    $folders = [];
    
    // First, collect all folders
    foreach ($items as $item) {
        if ($item !== '.' && $item !== '..' && $item !== 'thumbnails' && $item !== 'duration') {
            $item_path = $full_path . '/' . $item;
            if (is_dir($item_path)) {
                $folders[] = $item;
            }
        }
    }
    
    // Sort folders case-insensitively
    usort($folders, function($a, $b) {
        return strcasecmp($a, $b);
    });
    
    // Now process the sorted folders
    foreach ($folders as $item) {
        $item_path = $full_path . '/' . $item;
        $relative_path = $current_dir ? $current_dir . '/' . $item : $item;
        $subfolders = vsp_get_folder_structure($base_dir, $relative_path);
        $structure[] = [
            'name' => $item,
            'path' => $relative_path,
            'subfolders' => $subfolders
        ];
    }
    
    return $structure;
}

// Function to render folder tree
function vsp_render_folder_tree($folders, $current_dir = '', $is_settings_page = false) {
    if (empty($folders)) {
        return '';
    }

    $output = '<ul>';
    foreach ($folders as $folder) {
        $is_active = $folder['path'] === $current_dir;
        $has_subfolders = !empty($folder['subfolders']);
        $output .= '<li>';
        $output .= '<div class="folder-item' . ($is_active ? ' active' : '') . '" data-path="' . esc_attr($folder['path']) . '">';
        if (!$is_settings_page && $has_subfolders) {
            $output .= '<span class="toggle-icon"></span>';
        } else {
            $output .= '<span class="toggle-icon" style="visibility: hidden;"></span>';
        }
        $output .= '<span class="folder-icon' . ($is_active ? ' open' : '') . '"></span>';
        $output .= '<span class="folder-name">' . esc_html($folder['name']) . '</span>';
        if ($is_settings_page) {
            $output .= '<div class="folder-actions">';
            $output .= '<button class="folder-action-button add" data-action="add" title="Add subfolder"><span class="dashicons dashicons-plus-alt2"></span></button>';
            $output .= '<button class="folder-action-button rename" data-action="rename" title="Rename folder"><span class="dashicons dashicons-edit"></span></button>';
            $output .= '<button class="folder-action-button delete" data-action="delete" title="Delete folder"><span class="dashicons dashicons-trash"></span></button>';
            $output .= '</div>';
        }
        $output .= '</div>';
        
        if ($has_subfolders) {
            $output .= '<div class="subfolders' . ($is_active ? ' open' : '') . '">';
            $output .= vsp_render_folder_tree($folder['subfolders'], $current_dir, $is_settings_page);
            $output .= '</div>';
        }
        
        $output .= '</li>';
    }
    $output .= '</ul>';
    return $output;
}

// Function to get thumbnail directory path
function vsp_get_thumbnail_dir($source_path) {
    $dir = dirname($source_path);
    $thumb_dir = $dir . '/thumbnails';
    if (!file_exists($thumb_dir)) {
        mkdir($thumb_dir, 0755, true);
    }
    return $thumb_dir;
}

// Function to get cached thumbnail path
function vsp_get_cached_thumbnail_path($source_path) {
    $filename = basename($source_path);
    $hash = md5($source_path . filemtime($source_path));
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $thumb_dir = vsp_get_thumbnail_dir($source_path);
    return $thumb_dir . '/' . $hash . '.' . $ext;
}

// Function to convert seconds to HH:MM:SS format
function vsp_format_duration($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $seconds = floor($seconds % 60);
    
    return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
}

// Function to get cached duration
function vsp_get_cached_duration($file_path) {
    $duration_dir = dirname($file_path) . '/duration';
    if (!file_exists($duration_dir)) {
        mkdir($duration_dir, 0755, true);
    }
    $cache_file = $duration_dir . '/' . basename($file_path) . '.duration';
    if (file_exists($cache_file)) {
        return file_get_contents($cache_file);
    }
    return false;
}

// Function to cache duration
function vsp_cache_duration($file_path, $duration) {
    $duration_dir = dirname($file_path) . '/duration';
    if (!file_exists($duration_dir)) {
        mkdir($duration_dir, 0755, true);
    }
    $cache_file = $duration_dir . '/' . basename($file_path) . '.duration';
    file_put_contents($cache_file, $duration);
}

// Function to get video duration
function vsp_get_video_duration($file_path) {
    // Check cache first
    $cached_duration = vsp_get_cached_duration($file_path);
    if ($cached_duration !== false) {
        return $cached_duration;
    }

    // Check if FFmpeg is available
    if (!function_exists('exec')) {
        return 'N/A';
    }

    // Try to get duration using FFmpeg
    $command = "ffmpeg -i " . escapeshellarg($file_path) . " 2>&1";
    exec($command, $output, $return_var);

    if ($return_var === 0 && !empty($output)) {
        // Look for duration in the output
        foreach ($output as $line) {
            if (preg_match('/Duration: (\d{2}):(\d{2}):(\d{2})\.(\d{2})/', $line, $matches)) {
                $hours = intval($matches[1]);
                $minutes = intval($matches[2]);
                $seconds = intval($matches[3]);
                $total_seconds = $hours * 3600 + $minutes * 60 + $seconds;
                $duration = vsp_format_duration($total_seconds);
                vsp_cache_duration($file_path, $duration);
                return $duration;
            }
        }
    }

    // If FFmpeg failed or couldn't find duration, try alternative method
    if (function_exists('shell_exec')) {
        $command = "ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($file_path);
        $duration = shell_exec($command);
        
        if ($duration !== null) {
            $total_seconds = floatval($duration);
            $formatted_duration = vsp_format_duration($total_seconds);
            vsp_cache_duration($file_path, $formatted_duration);
            return $formatted_duration;
        }
    }

    return 'N/A';
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
    echo vsp_render_folder_tree($folder_structure, $current_dir, false);
    echo '</div>';

    // Main Content Area
    echo '<div class="vsp-content">';
    // Show current folder path as title
    if ($current_dir) {
        $path_parts = explode('/', $current_dir);
        $folder_name = end($path_parts);
        $parent_folder = count($path_parts) > 1 ? $path_parts[count($path_parts) - 2] : '';
        $media_count = vsp_count_media_files($video_dir);
        echo '<h2 class="vsp-title">' . esc_html($parent_folder ? $parent_folder . ' / ' . $folder_name : $folder_name) . ' - ' . $media_count . ' ' . ($media_count === 1 ? 'item' : 'items') . '</h2>';
    } else {
        $media_count = vsp_count_media_files(VIDEO_UPLOAD_DIR);
        echo '<h2 class="vsp-title">Root - ' . $media_count . ' ' . ($media_count === 1 ? 'item' : 'items') . '</h2>';
    }

    // Media List
    if (!empty($media_files)) {
        echo '<table class="vsp-video-table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th onclick="sortTable(0)">Name <span class="sort-icon">↕</span></th>';
        echo '<th onclick="sortTable(1)">Duration <span class="sort-icon">↕</span></th>';
        echo '<th onclick="sortTable(2)">Size <span class="sort-icon">↕</span></th>';
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
                $cached_path = vsp_get_cached_thumbnail_path($full_path);
                if (file_exists($cached_path)) {
                    $relative_path = str_replace(ABSPATH, '', $cached_path);
                    $thumbnail_url = site_url($relative_path);
                }
                
                echo '<div class="vsp-media-item">';
                if ($thumbnail_url) {
                    echo '<img src="' . esc_url($thumbnail_url) . '" class="vsp-thumbnail" alt="' . esc_attr($file['name']) . '" loading="lazy">';
                }
                echo '<a href="#" class="vsp-image-link" data-image="' . esc_url($media_url) . '">' . esc_html($file['name']) . '</a>';
                echo '</div>';
            }
            echo '</td>';
            echo '<td>' . ($file['type'] === 'video' ? esc_html(vsp_get_cached_duration($full_path) ?: 'N/A') : '-') . '</td>';
            echo '<td>' . esc_html($formatted_size) . '</td>';
            echo '<td class="vsp-video-actions">';
            echo '<button class="vsp-rename-video" data-video-name="' . esc_attr($file['name']) . '" title="Rename video"><span class="dashicons dashicons-edit"></span></button>';
            echo '<button class="vsp-move-video" data-video-name="' . esc_attr($file['name']) . '" title="Move video"><span class="dashicons dashicons-move"></span></button>';
            echo '<button class="vsp-delete-video" data-video-name="' . esc_attr($file['name']) . '" title="Delete video"><span class="dashicons dashicons-trash"></span></button>';
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
    echo '<img id="vsp-overlay-image" style="display: none;">';
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

    // Get raw input and decode it, then strip any slashes that might have been added
    $video_name = stripslashes(rawurldecode($_POST['video_name']));
    $folder = isset($_POST['folder']) ? stripslashes(rawurldecode($_POST['folder'])) : '';
    
    // Construct the full path including the folder
    $video_path = $folder ? $folder . '/' . $video_name : $video_name;
    $full_path = VIDEO_UPLOAD_DIR . '/' . $video_path;

    // Debug information
    error_log("Attempting to delete video:");
    error_log("Video name: " . $video_name);
    error_log("Folder: " . $folder);
    error_log("Full path: " . $full_path);
    error_log("File exists: " . (file_exists($full_path) ? 'yes' : 'no'));

    if (file_exists($full_path)) {
        // Delete the main file
        if (unlink($full_path)) {
            // Delete associated thumbnail if it exists
            $thumbnail_dir = dirname($full_path) . '/thumbnails';
            $thumbnail_path = $thumbnail_dir . '/' . md5($full_path . filemtime($full_path)) . '.' . pathinfo($video_name, PATHINFO_EXTENSION);
            if (file_exists($thumbnail_path)) {
                unlink($thumbnail_path);
            }

            // Delete associated duration file if it exists
            $duration_dir = dirname($full_path) . '/duration';
            $duration_path = $duration_dir . '/' . $video_name . '.duration';
            if (file_exists($duration_path)) {
                unlink($duration_path);
            }

            wp_send_json_success('Video and associated files deleted successfully.');
        } else {
            wp_send_json_error('Error deleting video. Please check file permissions.');
        }
    } else {
        wp_send_json_error('Video not found at path: ' . $full_path);
    }
}
add_action('wp_ajax_delete_video', 'vsp_delete_video');

// Add a function to handle video renaming
function vsp_rename_video() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied.');
        return;
    }

    // Get raw input and decode it, then strip any slashes that might have been added
    $old_name = stripslashes(rawurldecode($_POST['old_name']));
    $new_name = stripslashes(rawurldecode($_POST['new_name']));
    $folder = isset($_POST['folder']) ? stripslashes(rawurldecode($_POST['folder'])) : '';
    
    // Construct the full paths including the folder
    $old_path = $folder ? $folder . '/' . $old_name : $old_name;
    $new_path = $folder ? $folder . '/' . $new_name : $new_name;
    $old_full_path = VIDEO_UPLOAD_DIR . '/' . $old_path;
    $new_full_path = VIDEO_UPLOAD_DIR . '/' . $new_path;

    // Debug information
    error_log("Attempting to rename video:");
    error_log("Old name: " . $old_name);
    error_log("New name: " . $new_name);
    error_log("Folder: " . $folder);
    error_log("Old path: " . $old_full_path);
    error_log("New path: " . $new_full_path);
    error_log("File exists: " . (file_exists($old_full_path) ? 'yes' : 'no'));

    if (file_exists($old_full_path)) {
        if (rename($old_full_path, $new_full_path)) {
            // Handle duration file
            $old_duration_dir = dirname($old_full_path) . '/duration';
            $new_duration_dir = dirname($new_full_path) . '/duration';
            
            // Create duration directory if it doesn't exist
            if (!file_exists($new_duration_dir)) {
                mkdir($new_duration_dir, 0755, true);
            }
            
            // Move duration file if it exists
            $old_duration_file = $old_duration_dir . '/' . $old_name . '.duration';
            $new_duration_file = $new_duration_dir . '/' . $new_name . '.duration';
            
            if (file_exists($old_duration_file)) {
                rename($old_duration_file, $new_duration_file);
            }
            
            wp_send_json_success('Video renamed successfully.');
        } else {
            wp_send_json_error('Error renaming video. Please check file permissions.');
        }
    } else {
        wp_send_json_error('Video not found at path: ' . $old_full_path);
    }
}
add_action('wp_ajax_rename_video', 'vsp_rename_video');

// Enqueue JavaScript for handling delete and rename actions
function vsp_enqueue_scripts() {
    wp_enqueue_script('videojs', 'https://vjs.zencdn.net/7.11.4/video.min.js', array(), null, true);
    wp_enqueue_style('videojs-css', 'https://vjs.zencdn.net/7.11.4/video-js.min.css');
    wp_enqueue_style('custom-style', plugin_dir_url(__FILE__) . 'custom-style.css');
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
    wp_enqueue_style('dashicons');

    // Enqueue custom script for handling delete and rename
    wp_enqueue_script('vsp-custom-script', plugin_dir_url(__FILE__) . 'custom-script.js', array('jquery'), null, true);
    
    // Localize script to make ajaxurl available
    wp_localize_script('vsp-custom-script', 'ajaxurl', admin_url('admin-ajax.php'));
    wp_localize_script('vsp-custom-script', 'vspAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('vsp_nonce')
    ));
}

// Add admin styles
function vsp_admin_enqueue_scripts($hook) {
    // Only load on our plugin's settings page
    if ('toplevel_page_video-streaming-settings' !== $hook) {
        return;
    }
    
    wp_enqueue_style('custom-style', plugin_dir_url(__FILE__) . 'custom-style.css');
    wp_enqueue_style('dashicons');
}

add_action('wp_enqueue_scripts', 'vsp_enqueue_scripts');
add_action('admin_enqueue_scripts', 'vsp_admin_enqueue_scripts');

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

        function convertToSeconds(duration) {
            if (duration === 'N/A') return 0;
            const parts = duration.split(':');
            if (parts.length === 3) {
                const hours = parseInt(parts[0]);
                const minutes = parseInt(parts[1]);
                const seconds = parseInt(parts[2]);
                return hours * 3600 + minutes * 60 + seconds;
            }
            return 0;
        }

        function sortTable(columnIndex) {
            const table = document.querySelector('.vsp-video-table tbody');
            const rows = Array.from(table.rows);
            const isAscending = table.dataset.sortOrder === 'asc';
            const direction = isAscending ? 1 : -1;

            rows.sort((a, b) => {
                const aText = a.cells[columnIndex].innerText;
                const bText = b.cells[columnIndex].innerText;

                if (columnIndex === 0) { // Name column
                    return aText.localeCompare(bText, undefined, { numeric: true, sensitivity: 'base' }) * direction;
                } else if (columnIndex === 1) { // Duration column
                    const aSeconds = convertToSeconds(aText);
                    const bSeconds = convertToSeconds(bText);
                    return (aSeconds - bSeconds) * direction;
                } else if (columnIndex === 2) { // Size column
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

// Modified thumbnail creation function
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

    // Save to thumbnail directory
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

// Add admin menu
function vsp_add_admin_menu() {
    $icon_url = plugin_dir_url(__FILE__) . 'Mon white trasp.png';
    add_menu_page(
        'Video Streaming Settings',
        'Video Streaming',
        'manage_options',
        'video-streaming-settings',
        'vsp_settings_page',
        $icon_url,
        30
    );
}
add_action('admin_menu', 'vsp_add_admin_menu');

// Function to get total folder size
function vsp_get_folder_size($path) {
    $total_size = 0;
    $files = scandir($path);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $file_path = $path . '/' . $file;
        if (is_dir($file_path)) {
            $total_size += vsp_get_folder_size($file_path);
        } else {
            $total_size += filesize($file_path);
        }
    }
    
    return $total_size;
}

// Function to count images recursively
function vsp_count_images($path) {
    $count = 0;
    $files = scandir($path);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $file_path = $path . '/' . $file;
        if (is_dir($file_path)) {
            // Skip the thumbnails directory
            if ($file !== 'thumbnails') {
                $count += vsp_count_images($file_path);
            }
        } elseif (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file)) {
            $count++;
        }
    }
    
    return $count;
}

// Modified function to count thumbnails
function vsp_count_thumbnails($path) {
    $count = 0;
    $files = scandir($path);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $file_path = $path . '/' . $file;
        if (is_dir($file_path)) {
            if ($file === 'thumbnails') {
                $thumb_files = scandir($file_path);
                foreach ($thumb_files as $thumb_file) {
                    if ($thumb_file !== '.' && $thumb_file !== '..' && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $thumb_file)) {
                        $count++;
                    }
                }
            } else {
                $count += vsp_count_thumbnails($file_path);
            }
        }
    }
    
    return $count;
}

// Function to get all image files recursively
function vsp_get_all_images($path) {
    $images = [];
    $files = scandir($path);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $file_path = $path . '/' . $file;
        if (is_dir($file_path)) {
            // Skip the thumbnails directory
            if ($file !== 'thumbnails') {
                $images = array_merge($images, vsp_get_all_images($file_path));
            }
        } elseif (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file)) {
            $images[] = $file_path;
        }
    }
    
    return $images;
}

// AJAX handler for thumbnail generation
function vsp_generate_thumbnails() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied.');
        return;
    }

    $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
    $batch_size = 20;
    $images = vsp_get_all_images(VIDEO_UPLOAD_DIR);
    $total = count($images);
    $processed = 0;
    $skipped = 0;
    
    for ($i = $offset; $i < min($offset + $batch_size, $total); $i++) {
        $source_path = $images[$i];
        $cached_path = vsp_get_cached_thumbnail_path($source_path);
        
        // Check if thumbnail exists and is up to date
        if (file_exists($cached_path) && filemtime($cached_path) >= filemtime($source_path)) {
            $skipped++;
        } else {
            vsp_create_thumbnail($source_path);
            $processed++;
        }
    }
    
    $next_offset = $offset + $batch_size;
    $is_complete = $next_offset >= $total;
    
    wp_send_json_success([
        'processed' => $processed,
        'skipped' => $skipped,
        'total' => $total,
        'next_offset' => $next_offset,
        'is_complete' => $is_complete
    ]);
}
add_action('wp_ajax_generate_thumbnails', 'vsp_generate_thumbnails');

// Function to count videos recursively
function vsp_count_videos($path) {
    $count = 0;
    $files = scandir($path);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $file_path = $path . '/' . $file;
        if (is_dir($file_path)) {
            // Skip the thumbnails directory
            if ($file !== 'thumbnails') {
                $count += vsp_count_videos($file_path);
            }
        } elseif (preg_match('/\.(mp4|m4v|webm|ogg|flv)$/i', $file)) {
            $count++;
        }
    }
    
    return $count;
}

// Function to get all video files recursively
function vsp_get_all_videos($path) {
    $videos = [];
    $files = scandir($path);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $file_path = $path . '/' . $file;
        if (is_dir($file_path)) {
            // Skip the thumbnails directory
            if ($file !== 'thumbnails') {
                $videos = array_merge($videos, vsp_get_all_videos($file_path));
            }
        } elseif (preg_match('/\.(mp4|m4v|webm|ogg|flv)$/i', $file)) {
            $videos[] = $file_path;
        }
    }
    
    return $videos;
}

// AJAX handler for bulk duration calculation
function vsp_calculate_durations() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied.');
        return;
    }

    $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
    $batch_size = 10;
    $videos = vsp_get_all_videos(VIDEO_UPLOAD_DIR);
    $total = count($videos);
    $processed = 0;
    $skipped = 0;
    
    for ($i = $offset; $i < min($offset + $batch_size, $total); $i++) {
        $source_path = $videos[$i];
        $duration_dir = dirname($source_path) . '/duration';
        $cached_path = $duration_dir . '/' . basename($source_path) . '.duration';
        
        // Check if duration exists and is up to date
        if (file_exists($cached_path) && filemtime($cached_path) >= filemtime($source_path)) {
            $skipped++;
        } else {
            vsp_get_video_duration($source_path);
            $processed++;
        }
    }
    
    $next_offset = $offset + $batch_size;
    $is_complete = $next_offset >= $total;
    
    wp_send_json_success([
        'processed' => $processed,
        'skipped' => $skipped,
        'total' => $total,
        'next_offset' => $next_offset,
        'is_complete' => $is_complete
    ]);
}
add_action('wp_ajax_calculate_durations', 'vsp_calculate_durations');

// Add function to clear duration cache
function vsp_clear_duration_cache() {
    check_ajax_referer('vsp_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }
    
    $videos = vsp_get_all_videos(VIDEO_UPLOAD_DIR);
    $cleared = 0;
    
    foreach ($videos as $video_path) {
        $duration_dir = dirname($video_path) . '/duration';
        if (file_exists($duration_dir)) {
            $duration_file = $duration_dir . '/' . basename($video_path) . '.duration';
            if (file_exists($duration_file)) {
                if (unlink($duration_file)) {
                    $cleared++;
                }
            }
        }
    }
    
    wp_send_json_success([
        'cleared' => $cleared,
        'total' => count($videos)
    ]);
}
add_action('wp_ajax_clear_duration_cache', 'vsp_clear_duration_cache');

// Add function to count videos without durations
function vsp_count_videos_without_duration() {
    $videos = vsp_get_all_videos(VIDEO_UPLOAD_DIR);
    $count = 0;
    
    foreach ($videos as $video_path) {
        if (!vsp_get_cached_duration($video_path)) {
            $count++;
        }
    }
    
    return $count;
}

// Settings page HTML
function vsp_settings_page() {
    $total_size = vsp_get_folder_size(VIDEO_UPLOAD_DIR);
    $total_images = vsp_count_images(VIDEO_UPLOAD_DIR);
    $total_thumbnails = vsp_count_thumbnails(VIDEO_UPLOAD_DIR);
    $total_videos = vsp_count_videos(VIDEO_UPLOAD_DIR);
    ?>
    <div class="wrap vsp-settings-page">
        <h1>Video Streaming Settings</h1>
        
        <div class="vsp-settings-grid">
            <!-- Storage Card -->
            <div class="vsp-settings-card">
                <div class="vsp-card-header">
                    <span class="dashicons dashicons-database"></span>
                    <h2>Storage Usage</h2>
                </div>
                <div class="vsp-card-content">
                    <div class="vsp-stats-row">
                        <div class="vsp-stat-group">
                            <div class="vsp-stat-value"><?php echo vsp_format_file_size($total_size); ?></div>
                            <div class="vsp-stat-label">Storage Used</div>
                        </div>
                        <div class="vsp-stat-group">
                            <div class="vsp-stat-value"><?php echo $total_videos; ?></div>
                            <div class="vsp-stat-label">Total Videos</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Images Card -->
            <div class="vsp-settings-card">
                <div class="vsp-card-header">
                    <span class="dashicons dashicons-format-image"></span>
                    <h2>Images</h2>
                </div>
                <div class="vsp-card-content">
                    <div class="vsp-stat-value"><?php echo $total_images; ?></div>
                    <div class="vsp-stat-label">Total Images</div>
                </div>
            </div>

            <!-- Thumbnails Card -->
            <div class="vsp-settings-card">
                <div class="vsp-card-header">
                    <span class="dashicons dashicons-camera"></span>
                    <h2>Thumbnails</h2>
                </div>
                <div class="vsp-card-content">
                    <div class="vsp-stat-value"><?php echo $total_thumbnails; ?></div>
                    <div class="vsp-stat-label">Generated Thumbnails</div>
                </div>
            </div>

            <!-- Thumbnail Generation Card -->
            <div class="vsp-settings-card">
                <div class="vsp-card-header">
                    <span class="dashicons dashicons-update"></span>
                    <h2>Thumbnail Generation</h2>
                </div>
                <div class="vsp-card-content">
                    <p>Generate thumbnails for all images in the videos folder.</p>
                    <div class="vsp-thumbnail-actions">
                        <button id="vsp-generate-thumbnails" class="button button-primary">Generate All Thumbnails</button>
                        <button id="vsp-clear-thumbnails" class="button button-secondary">Clear All Thumbnails</button>
                    </div>
                    <div id="vsp-progress-container" style="display: none;">
                        <div class="vsp-progress-bar">
                            <div class="vsp-progress-fill"></div>
                        </div>
                        <p id="vsp-progress-text">Processing: 0/0</p>
                    </div>
                </div>
            </div>

            <!-- Duration Calculation Card -->
            <div class="vsp-settings-card">
                <div class="vsp-card-header">
                    <span class="dashicons dashicons-clock"></span>
                    <h2>Video Durations</h2>
                </div>
                <div class="vsp-card-content">
                    <?php
                    $videos_without_duration = vsp_count_videos_without_duration();
                    if ($videos_without_duration > 0) {
                        echo '<div class="vsp-notice vsp-notice-warning">';
                        echo '<p><strong>' . $videos_without_duration . ' video' . ($videos_without_duration === 1 ? '' : 's') . ' missing duration</strong></p>';
                        echo '</div>';
                    }
                    ?>
                    <p>Calculate durations for all videos in the videos folder.</p>
                    <div class="vsp-button-group">
                        <button id="vsp-calculate-durations" class="button button-primary">Calculate All Durations</button>
                        <button id="vsp-clear-durations" class="button button-secondary">Clear Duration Cache</button>
                    </div>
                    <div id="vsp-duration-progress" style="display: none;">
                        <div class="vsp-progress-bar">
                            <div class="vsp-progress-fill"></div>
                        </div>
                        <p id="vsp-duration-progress-text">Processing: 0/0</p>
                    </div>
                    <div id="vsp-clear-duration-progress" style="display: none;">
                        <div class="vsp-progress-bar">
                            <div class="vsp-progress-fill"></div>
                        </div>
                        <p id="vsp-clear-duration-progress-text">Processing: 0/0</p>
                    </div>
                </div>
            </div>

            <!-- Folder Management Card -->
            <div class="vsp-settings-card vsp-settings-card-full">
                <div class="vsp-card-header">
                    <span class="dashicons dashicons-category"></span>
                    <h2>Folder Management</h2>
                </div>
                <div class="vsp-card-content">
                    <div class="vsp-folder-management">
                        <div class="vsp-folder-actions">
                            <button id="vsp-create-folder" class="button button-primary">
                                <span class="dashicons dashicons-plus-alt2"></span> Create New Folder
                            </button>
                        </div>
                        <div class="vsp-folder-tree">
                            <?php
                            $folder_structure = vsp_get_folder_structure(VIDEO_UPLOAD_DIR);
                            echo vsp_render_folder_tree($folder_structure, '', true);
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        // Add nonce for AJAX requests
        const vspNonce = '<?php echo wp_create_nonce("vsp_nonce"); ?>';

        // Define showNotification function
        function showNotification(message) {
            var $notification = $('<div class="vsp-notification"><span class="vsp-notification-message">' + message + '</span></div>');
            $('body').append($notification);
            setTimeout(function() {
                $notification.addClass('show');
            }, 100);
            setTimeout(function() {
                $notification.removeClass('show');
                setTimeout(function() {
                    $notification.remove();
                }, 2000);
            }, 2000);
        }

        // Duration calculation
        var isCalculatingDurations = false;
        var durationOffset = 0;
        var totalVideos = 0;
        var totalProcessedDurations = 0;
        var totalSkippedDurations = 0;

        // Check if there's a duration calculation in progress
        var savedDurationProgress = localStorage.getItem('vsp_duration_progress');
        if (savedDurationProgress) {
            var progress = JSON.parse(savedDurationProgress);
            if (!progress.is_complete) {
                if (confirm('There is a duration calculation process in progress. Would you like to resume?')) {
                    durationOffset = progress.next_offset;
                    totalVideos = progress.total;
                    totalProcessedDurations = progress.processed;
                    totalSkippedDurations = progress.skipped;
                    startDurationCalculation();
                } else {
                    localStorage.removeItem('vsp_duration_progress');
                }
            }
        }

        $('#vsp-calculate-durations').on('click', function() {
            if (!isCalculatingDurations) {
                startDurationCalculation();
            }
        });

        function startDurationCalculation() {
            isCalculatingDurations = true;
            var $button = $('#vsp-calculate-durations');
            var $progressContainer = $('#vsp-duration-progress');
            var $progressBar = $progressContainer.find('.vsp-progress-fill');
            var $progressText = $('#vsp-duration-progress-text');
            
            $button.prop('disabled', true).text('Calculating Durations...');
            $progressContainer.show();
            
            if (!savedDurationProgress) {
                durationOffset = 0;
                totalProcessedDurations = 0;
                totalSkippedDurations = 0;
            }
            
            $(window).on('beforeunload', function() {
                if (isCalculatingDurations) {
                    return 'Duration calculation is in progress. Are you sure you want to leave?';
                }
            });
            
            processDurationBatch(durationOffset, totalProcessedDurations, totalSkippedDurations);
        }

        function processDurationBatch(offset, processed, skipped) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'calculate_durations',
                    offset: offset,
                    nonce: vspNonce
                },
                success: function(response) {
                    if (response.success) {
                        processed += response.data.processed;
                        skipped += response.data.skipped;
                        totalVideos = response.data.total;
                        var progress = ((processed + skipped) / totalVideos) * 100;
                        $('#vsp-duration-progress .vsp-progress-fill').css('width', progress + '%');
                        $('#vsp-duration-progress-text').text('Processing: ' + (processed + skipped) + '/' + totalVideos + ' (Skipped: ' + skipped + ')');
                        
                        localStorage.setItem('vsp_duration_progress', JSON.stringify({
                            processed: processed,
                            skipped: skipped,
                            total: totalVideos,
                            next_offset: response.data.next_offset,
                            is_complete: response.data.is_complete
                        }));
                        
                        if (!response.data.is_complete) {
                            processDurationBatch(response.data.next_offset, processed, skipped);
                        } else {
                            completeDurationCalculation();
                        }
                    }
                },
                error: function() {
                    alert('An error occurred while calculating durations. The process will resume from where it left off when you refresh the page.');
                }
            });
        }

        function completeDurationCalculation() {
            isCalculatingDurations = false;
            localStorage.removeItem('vsp_duration_progress');
            $('#vsp-calculate-durations').prop('disabled', false).text('Calculate All Durations');
            setTimeout(function() {
                location.reload();
            }, 1000);
        }

        // Add cancel button for duration calculation
        if (!$('#vsp-cancel-durations').length) {
            $('#vsp-calculate-durations').after(' <button id="vsp-cancel-durations" class="button" style="display: none;">Cancel</button>');
        }

        $('#vsp-cancel-durations').on('click', function() {
            if (confirm('Are you sure you want to cancel the duration calculation process?')) {
                isCalculatingDurations = false;
                localStorage.removeItem('vsp_duration_progress');
                $('#vsp-calculate-durations').prop('disabled', false).text('Calculate All Durations');
                $('#vsp-cancel-durations').hide();
                $('#vsp-duration-progress').hide();
            }
        });

        // Clear duration cache
        $('#vsp-clear-durations').on('click', function() {
            if (confirm('Are you sure you want to clear all cached durations? This will require recalculating durations for all videos.')) {
                var $button = $(this);
                var $progressContainer = $('#vsp-clear-duration-progress');
                var $progressBar = $progressContainer.find('.vsp-progress-fill');
                var $progressText = $('#vsp-clear-duration-progress-text');
                
                $button.prop('disabled', true).text('Clearing Cache...');
                $progressContainer.show();
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'clear_duration_cache',
                        nonce: vspNonce
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update progress bar to 100%
                            $progressBar.css('width', '100%');
                            $progressText.text('Processing: ' + response.data.cleared + '/' + response.data.total);
                            
                            // Show admin notice
                            var notice = $('<div class="notice notice-success is-dismissible"><p>Successfully cleared ' + response.data.cleared + ' cached durations.</p></div>');
                            $('.wrap').prepend(notice);
                            
                            setTimeout(function() {
                                location.reload();
                            }, 3000);
                        } else {
                            alert('Error clearing duration cache: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('Error clearing duration cache. Please try again.');
                    },
                    complete: function() {
                        $button.prop('disabled', false).text('Clear Duration Cache');
                    }
                });
            }
        });

        // Folder Management
        function initFolderManagement() {
            // Toggle folder expansion
            $('.vsp-folder-tree .toggle-icon').on('click', function(e) {
                e.stopPropagation();
                $(this).toggleClass('open');
                $(this).closest('li').find('.subfolders').toggleClass('open');
            });

            // Create new folder
            $('#vsp-create-folder').on('click', function() {
                showFolderDialog('create');
            });

            // Folder action buttons
            $(document).on('click', '.folder-action-button', function(e) {
                e.stopPropagation();
                const action = $(this).data('action');
                const folderPath = $(this).closest('.folder-item').data('path');
                const folderName = $(this).closest('.folder-item').find('.folder-name').text();

                if (action === 'add') {
                    showFolderDialog('add', folderPath);
                } else if (action === 'rename') {
                    showFolderDialog('rename', folderPath, folderName);
                } else if (action === 'delete') {
                    if (confirm('Are you sure you want to delete this folder and all its contents?')) {
                        deleteFolder(folderPath);
                    }
                }
            });
        }

        function showFolderDialog(type, folderPath = '', folderName = '') {
            const dialog = $('<div class="vsp-folder-dialog"><div class="vsp-folder-dialog-content">' +
                '<div class="vsp-folder-dialog-header">' +
                '<h3>' + (type === 'add' ? 'Add Subfolder' : (type === 'create' ? 'Create New Folder' : 'Rename Folder')) + '</h3>' +
                '<button class="vsp-folder-dialog-close">&times;</button>' +
                '</div>' +
                '<div class="vsp-folder-dialog-body">' +
                '<input type="text" class="folder-name-input" value="' + folderName + '" placeholder="Enter folder name">' +
                '</div>' +
                '<div class="vsp-folder-dialog-footer">' +
                '<button class="button cancel-folder-dialog">Cancel</button>' +
                '<button class="button button-primary save-folder-dialog">' + (type === 'add' ? 'Add' : (type === 'create' ? 'Create' : 'Rename')) + '</button>' +
                '</div>' +
                '</div></div>');

            $('body').append(dialog);
            dialog.addClass('active');
            dialog.find('.folder-name-input').focus();

            // Close dialog
            dialog.find('.vsp-folder-dialog-close, .cancel-folder-dialog').on('click', function() {
                dialog.remove();
            });

            // Save folder
            dialog.find('.save-folder-dialog').on('click', function() {
                const newName = dialog.find('.folder-name-input').val().trim();
                if (newName) {
                    if (type === 'add') {
                        createSubfolder(folderPath, newName);
                    } else if (type === 'create') {
                        createFolder(newName);
                    } else {
                        renameFolder(folderPath, newName);
                    }
                    dialog.remove();
                }
            });
        }

        function createFolder(name) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'create_folder',
                    folder_name: name,
                    nonce: vspNonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error creating folder: ' + response.data);
                    }
                }
            });
        }

        function renameFolder(oldPath, newName) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'rename_folder',
                    old_path: oldPath,
                    new_name: newName,
                    nonce: vspNonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error renaming folder: ' + response.data);
                    }
                }
            });
        }

        function deleteFolder(path) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'delete_folder',
                    folder_path: path,
                    nonce: vspNonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error deleting folder: ' + response.data);
                    }
                }
            });
        }

        function createSubfolder(parentPath, name) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'create_subfolder',
                    parent_path: parentPath,
                    folder_name: name,
                    nonce: vspNonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error creating subfolder: ' + response.data);
                    }
                }
            });
        }

        // Initialize folder management
        initFolderManagement();

        // Add click handlers for images
        $(document).on('click', '.vsp-image-link', function(e) {
            e.preventDefault();
            const imgSrc = $(this).attr('href');
            const img = $('<img id="vsp-overlay-image" src="' + imgSrc + '">');
            $('.vsp-video-overlay-content').html(img);
            $('.vsp-video-overlay').addClass('active');
        });

        // Thumbnail generation
        var isGeneratingThumbnails = false;
        var thumbnailOffset = 0;
        var totalImages = 0;
        var totalProcessedThumbnails = 0;
        var totalSkippedThumbnails = 0;

        // Check if there's a thumbnail generation in progress
        var savedThumbnailProgress = localStorage.getItem('vsp_thumbnail_progress');
        if (savedThumbnailProgress) {
            var progress = JSON.parse(savedThumbnailProgress);
            if (!progress.is_complete) {
                if (confirm('There is a thumbnail generation process in progress. Would you like to resume?')) {
                    thumbnailOffset = progress.next_offset;
                    totalImages = progress.total;
                    totalProcessedThumbnails = progress.processed;
                    totalSkippedThumbnails = progress.skipped;
                    startThumbnailGeneration();
                } else {
                    localStorage.removeItem('vsp_thumbnail_progress');
                }
            }
        }

        $('#vsp-generate-thumbnails').on('click', function() {
            if (!isGeneratingThumbnails) {
                startThumbnailGeneration();
            }
        });

        function startThumbnailGeneration() {
            isGeneratingThumbnails = true;
            var $button = $('#vsp-generate-thumbnails');
            var $progressContainer = $('#vsp-progress-container');
            var $progressBar = $progressContainer.find('.vsp-progress-fill');
            var $progressText = $('#vsp-progress-text');
            
            $button.prop('disabled', true).text('Generating Thumbnails...');
            $progressContainer.show();
            
            if (!savedThumbnailProgress) {
                thumbnailOffset = 0;
                totalProcessedThumbnails = 0;
                totalSkippedThumbnails = 0;
            }
            
            $(window).on('beforeunload', function() {
                if (isGeneratingThumbnails) {
                    return 'Thumbnail generation is in progress. Are you sure you want to leave?';
                }
            });
            
            processThumbnailBatch(thumbnailOffset, totalProcessedThumbnails, totalSkippedThumbnails);
        }

        function processThumbnailBatch(offset, processed, skipped) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'generate_thumbnails',
                    offset: offset,
                    nonce: vspNonce
                },
                success: function(response) {
                    if (response.success) {
                        processed += response.data.processed;
                        skipped += response.data.skipped;
                        totalImages = response.data.total;
                        var progress = ((processed + skipped) / totalImages) * 100;
                        $('#vsp-progress-container .vsp-progress-fill').css('width', progress + '%');
                        $('#vsp-progress-text').text('Processing: ' + (processed + skipped) + '/' + totalImages + ' (Skipped: ' + skipped + ')');
                        
                        localStorage.setItem('vsp_thumbnail_progress', JSON.stringify({
                            processed: processed,
                            skipped: skipped,
                            total: totalImages,
                            next_offset: response.data.next_offset,
                            is_complete: response.data.is_complete
                        }));
                        
                        if (!response.data.is_complete) {
                            processThumbnailBatch(response.data.next_offset, processed, skipped);
                        } else {
                            completeThumbnailGeneration();
                        }
                    }
                },
                error: function() {
                    alert('An error occurred while generating thumbnails. The process will resume from where it left off when you refresh the page.');
                }
            });
        }

        function completeThumbnailGeneration() {
            isGeneratingThumbnails = false;
            localStorage.removeItem('vsp_thumbnail_progress');
            $('#vsp-generate-thumbnails').prop('disabled', false).text('Generate All Thumbnails');
            setTimeout(function() {
                location.reload();
            }, 1000);
        }

        // Add cancel button for thumbnail generation
        if (!$('#vsp-cancel-thumbnails').length) {
            $('#vsp-generate-thumbnails').after(' <button id="vsp-cancel-thumbnails" class="button" style="display: none;">Cancel</button>');
        }

        $('#vsp-cancel-thumbnails').on('click', function() {
            if (confirm('Are you sure you want to cancel the thumbnail generation process?')) {
                isGeneratingThumbnails = false;
                localStorage.removeItem('vsp_thumbnail_progress');
                $('#vsp-generate-thumbnails').prop('disabled', false).text('Generate All Thumbnails');
                $('#vsp-cancel-thumbnails').hide();
                $('#vsp-progress-container').hide();
            }
        });

        // Add AJAX handler for clearing thumbnails
        $('#vsp-clear-thumbnails').on('click', function() {
            if (confirm('Are you sure you want to clear all cached thumbnails? This will require regenerating thumbnails for all images.')) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'clear_thumbnails',
                        nonce: vspNonce
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotification('Successfully cleared all cached thumbnails.');
                            setTimeout(function() {
                                location.reload();
                            }, 3000);
                        } else {
                            alert('Error clearing thumbnails: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('Error clearing thumbnails. Please try again.');
                    }
                });
            }
        });
    });
    </script>
    <?php
}

// Add AJAX handlers for folder management
add_action('wp_ajax_create_folder', 'vsp_ajax_create_folder');
add_action('wp_ajax_rename_folder', 'vsp_ajax_rename_folder');
add_action('wp_ajax_delete_folder', 'vsp_ajax_delete_folder');
add_action('wp_ajax_create_subfolder', 'vsp_ajax_create_subfolder');

function vsp_ajax_create_folder() {
    check_ajax_referer('vsp_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }

    $folder_name = sanitize_text_field($_POST['folder_name']);
    $new_folder = VIDEO_UPLOAD_DIR . '/' . $folder_name;

    if (file_exists($new_folder)) {
        wp_send_json_error('Folder already exists');
    }

    if (wp_mkdir_p($new_folder)) {
        wp_send_json_success('Folder created successfully');
    } else {
        wp_send_json_error('Failed to create folder');
    }
}

function vsp_ajax_rename_folder() {
    check_ajax_referer('vsp_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }

    $old_path = sanitize_text_field($_POST['old_path']);
    $new_name = sanitize_text_field($_POST['new_name']);
    
    $old_folder = VIDEO_UPLOAD_DIR . '/' . $old_path;
    $new_folder = dirname($old_folder) . '/' . $new_name;

    if (!file_exists($old_folder)) {
        wp_send_json_error('Folder does not exist');
    }

    if (file_exists($new_folder)) {
        wp_send_json_error('Folder with new name already exists');
    }

    if (rename($old_folder, $new_folder)) {
        wp_send_json_success('Folder renamed successfully');
    } else {
        wp_send_json_error('Failed to rename folder');
    }
}

function vsp_ajax_delete_folder() {
    check_ajax_referer('vsp_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }

    $folder_path = sanitize_text_field($_POST['folder_path']);
    $folder_to_delete = VIDEO_UPLOAD_DIR . '/' . $folder_path;

    if (!file_exists($folder_to_delete)) {
        wp_send_json_error('Folder does not exist');
    }

    if (vsp_delete_directory($folder_to_delete)) {
        wp_send_json_success('Folder deleted successfully');
    } else {
        wp_send_json_error('Failed to delete folder');
    }
}

function vsp_delete_directory($dir) {
    if (!file_exists($dir)) {
        return true;
    }

    if (!is_dir($dir)) {
        return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        if (!vsp_delete_directory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }

    return rmdir($dir);
}

function vsp_ajax_create_subfolder() {
    check_ajax_referer('vsp_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }

    $parent_path = sanitize_text_field($_POST['parent_path']);
    $folder_name = sanitize_text_field($_POST['folder_name']);
    $new_folder = VIDEO_UPLOAD_DIR . '/' . ($parent_path ? $parent_path . '/' : '') . $folder_name;

    if (file_exists($new_folder)) {
        wp_send_json_error('Folder already exists');
    }

    if (wp_mkdir_p($new_folder)) {
        wp_send_json_success('Subfolder created successfully');
    } else {
        wp_send_json_error('Failed to create subfolder');
    }
}

// Function to count media files in a directory
function vsp_count_media_files($dir) {
    $count = 0;
    $items = scandir($dir);
    
    foreach ($items as $item) {
        if ($item !== '.' && $item !== '..' && $item !== 'thumbnails') {
            if (preg_match('/\.(mp4|m4v|webm|ogg|flv|jpg|jpeg|png|gif|webp)$/i', $item)) {
                $count++;
            }
        }
    }
    
    return $count;
}

// Add function to handle video moving
function vsp_move_video() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied.');
        return;
    }

    // Get raw input and decode it, then strip any slashes that might have been added
    $video_name = stripslashes(rawurldecode($_POST['video_name']));
    $current_folder = isset($_POST['current_folder']) ? stripslashes(rawurldecode($_POST['current_folder'])) : '';
    $destination_folder = isset($_POST['destination_folder']) ? stripslashes(rawurldecode($_POST['destination_folder'])) : '';
    
    // Construct the full paths
    $current_path = $current_folder ? $current_folder . '/' . $video_name : $video_name;
    $destination_path = $destination_folder ? $destination_folder . '/' . $video_name : $video_name;
    
    $current_full_path = VIDEO_UPLOAD_DIR . '/' . $current_path;
    $destination_full_path = VIDEO_UPLOAD_DIR . '/' . $destination_path;

    // Debug information
    error_log("Attempting to move video:");
    error_log("Video name: " . $video_name);
    error_log("Current folder: " . $current_folder);
    error_log("Destination folder: " . $destination_folder);
    error_log("Current path: " . $current_full_path);
    error_log("Destination path: " . $destination_full_path);
    error_log("File exists: " . (file_exists($current_full_path) ? 'yes' : 'no'));

    if (!file_exists($current_full_path)) {
        wp_send_json_error('File not found at path: ' . $current_full_path);
        return;
    }

    // Create destination directory if it doesn't exist
    $destination_dir = dirname($destination_full_path);
    if (!file_exists($destination_dir)) {
        wp_mkdir_p($destination_dir);
    }

    // Move the main file
    if (rename($current_full_path, $destination_full_path)) {
        // Move associated thumbnail if it exists
        $current_thumb_dir = dirname($current_full_path) . '/thumbnails';
        $current_thumb_path = $current_thumb_dir . '/' . md5($current_full_path . filemtime($current_full_path)) . '.' . pathinfo($video_name, PATHINFO_EXTENSION);
        
        if (file_exists($current_thumb_path)) {
            $destination_thumb_dir = dirname($destination_full_path) . '/thumbnails';
            if (!file_exists($destination_thumb_dir)) {
                wp_mkdir_p($destination_thumb_dir);
            }
            $destination_thumb_path = $destination_thumb_dir . '/' . md5($destination_full_path . filemtime($destination_full_path)) . '.' . pathinfo($video_name, PATHINFO_EXTENSION);
            rename($current_thumb_path, $destination_thumb_path);
        }

        // Move associated duration file if it exists
        $current_duration_dir = dirname($current_full_path) . '/duration';
        $current_duration_path = $current_duration_dir . '/' . $video_name . '.duration';
        
        if (file_exists($current_duration_path)) {
            $destination_duration_dir = dirname($destination_full_path) . '/duration';
            if (!file_exists($destination_duration_dir)) {
                wp_mkdir_p($destination_duration_dir);
            }
            $destination_duration_path = $destination_duration_dir . '/' . $video_name . '.duration';
            rename($current_duration_path, $destination_duration_path);
        }

        wp_send_json_success('File moved successfully.');
    } else {
        wp_send_json_error('Error moving file. Please check file permissions.');
    }
}
add_action('wp_ajax_move_video', 'vsp_move_video');

// Add AJAX handler for clearing thumbnails
add_action('wp_ajax_clear_thumbnails', 'vsp_clear_thumbnails');

function vsp_clear_thumbnails() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied.');
        return;
    }

    check_ajax_referer('vsp_nonce', 'nonce');

    $total_deleted = 0;
    $total_errors = 0;

    // Function to recursively delete thumbnails
    function delete_thumbnails_recursive($path) {
        global $total_deleted, $total_errors;
        
        if (!is_dir($path)) return;
        
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $file_path = $path . '/' . $file;
            if (is_dir($file_path)) {
                if ($file === 'thumbnails') {
                    // Delete all files in the thumbnails directory
                    $thumb_files = scandir($file_path);
                    foreach ($thumb_files as $thumb_file) {
                        if ($thumb_file === '.' || $thumb_file === '..') continue;
                        if (unlink($file_path . '/' . $thumb_file)) {
                            $total_deleted++;
                        } else {
                            $total_errors++;
                        }
                    }
                    // Remove the thumbnails directory
                    if (rmdir($file_path)) {
                        $total_deleted++;
                    } else {
                        $total_errors++;
                    }
                } else {
                    delete_thumbnails_recursive($file_path);
                }
            }
        }
    }

    delete_thumbnails_recursive(VIDEO_UPLOAD_DIR);

    wp_send_json_success([
        'deleted' => $total_deleted,
        'errors' => $total_errors
    ]);
}