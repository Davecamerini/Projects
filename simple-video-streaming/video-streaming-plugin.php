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

// Create a shortcode to display the video upload form and list
function vsp_video_page() {
    ob_start();
    
    // Get the current folder from the URL
    $current_dir = isset($_GET['folder']) ? sanitize_text_field($_GET['folder']) : '';
    $parent_dir = dirname($current_dir); // Get the parent directory
    $video_dir = rtrim(VIDEO_UPLOAD_DIR . '/' . $current_dir, '/');

    // List folders and videos
    $items = scandir($video_dir);
    $folders = [];
    $videos = [];

    // Separate folders and videos
    foreach ($items as $item) {
        if ($item !== '.' && $item !== '..') {
            $item_path = $video_dir . '/' . $item;
            if (is_dir($item_path)) {
                $folders[] = ucfirst($item); // Capitalize the first letter and add to folders array
            } elseif (preg_match('/\.(mp4|m4v|webm|ogg|flv)$/i', $item)) {
                $videos[] = ucfirst($item); // Capitalize the first letter and add to videos array
            }
        }
    }

    // Sort folders and videos alphabetically
    sort($folders);
    sort($videos);

    // Sort videos naturally
    natsort($videos); // Sorts the array in natural order

    // Array to hold videos with their sizes
    $video_sizes = [];
    
    // Get sizes for each video
    foreach ($videos as $video) {
        $video_path = $video_dir . '/' . basename($video); // Get the full path to the video
        $file_size = filesize($video_path); // Get the file size
        $video_sizes[$video] = $file_size; // Store video name and size
    }

    // Start the layout with a larger container
    echo '<div class="vsp-container" style="width: 100%;">'; // Increased width to 90%
    
    // Folder navigation as a dropdown
    echo '<div class="vsp-folder-navigation" style="margin-bottom: 20px;">';
    echo '<h3>Folders:</h3>';
    echo '<select onchange="if (this.value) { window.location.href = this.value; }" style="padding: 5px; font-size: 16px; margin-right: 10px;">'; // Styled dropdown
    echo '<option value="">Select a folder</option>'; // Default option
    foreach ($folders as $folder) {
        echo '<option value="?folder=' . esc_attr($folder) . '">' . esc_html($folder) . '</option>';
    }
    echo '</select>';

    // Back navigation link
    if ($parent_dir) {
        echo '<a href="?folder=' . esc_attr($parent_dir) . '" class="vsp-back-button" style="padding: 8px 12px; background-color: #0073aa; color: white; text-decoration: none; border-radius: 4px; font-size: 16px;">Back</a>'; // Styled back button
    }
    echo '</div>';

    // Add table headers for sorting
    echo '<table class="vsp-video-table">';
    echo '<thead>';
    echo '<tr>';
    echo '<th onclick="sortTable(0)">Video</th>'; // Video column
    echo '<th onclick="sortTable(1)">Size</th>'; // Size column
    echo '<th>Rename</th>'; // Rename column
    echo '<th>Delete</th>'; // Delete column
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    // Video list
    if ($video_sizes) {
        foreach ($video_sizes as $video => $file_size) {
            $file_size_human_readable = size_format($file_size); // Convert to human-readable format
            $video_url = site_url('wp-content/uploads/videos/' . ($current_dir ? $current_dir . '/' : '') . basename($video));
            echo '<tr class="vsp-video">
                    <td><a href="' . esc_url($video_url) . '" target="_blank"><i class="fas fa-video"></i> ' . esc_html($video) . '</a></td>
                    <td>' . esc_html($file_size_human_readable) . '</td>
                    <td><button class="vsp-rename-video" data-video-name="' . esc_attr($video) . '">Rename</button></td>
                    <td><button class="vsp-delete-video" data-video-name="' . esc_attr($video) . '">Delete</button></td>
                  </tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        // Ensure the "No videos found" message is clearly separated
        echo '</tbody></table>'; // Close the table before the message
        echo '<p class="vsp-no-items">No videos found.</p>'; // Display message
    }

    // End the layout
    echo '</div>';

    return ob_get_clean();
}
add_shortcode('video_streaming', 'vsp_video_page');

// Add a function to handle video deletion
function vsp_delete_video() {
    if (isset($_POST['video_name'])) {
        $video_name = sanitize_text_field($_POST['video_name']);
        $video_path = VIDEO_UPLOAD_DIR . '/' . $video_name;

        if (file_exists($video_path)) {
            unlink($video_path); // Delete the video file
            wp_send_json_success('Video deleted successfully.');
        } else {
            wp_send_json_error('Video not found.');
        }
    }
    wp_die(); // Required to terminate immediately and return a proper response
}
add_action('wp_ajax_delete_video', 'vsp_delete_video');

// Add a function to handle video renaming
function vsp_rename_video() {
    if (isset($_POST['old_name']) && isset($_POST['new_name'])) {
        $old_name = sanitize_text_field($_POST['old_name']);
        $new_name = sanitize_text_field($_POST['new_name']);
        $old_path = VIDEO_UPLOAD_DIR . '/' . $old_name;
        $new_path = VIDEO_UPLOAD_DIR . '/' . $new_name;

        if (file_exists($old_path)) {
            rename($old_path, $new_path); // Rename the video file
            wp_send_json_success('Video renamed successfully.');
        } else {
            wp_send_json_error('Video not found.');
        }
    }
    wp_die(); // Required to terminate immediately and return a proper response
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

                if (columnIndex === 1) { // Size column
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