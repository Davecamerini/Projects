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
    
    // Get the current directory from the query parameter, default to the base directory
    $current_dir = isset($_GET['dir']) ? sanitize_text_field($_GET['dir']) : '';
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
            } elseif (preg_match('/\.(mp4|m4v|webm|ogg)$/i', $item)) {
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

    // Start the layout
    echo '<div class="vsp-container">';

    // Display "Go Up" link if in a subfolder
    if ($current_dir && $current_dir !== '/') {
        $parent_dir = dirname($current_dir);
        $parent_url = add_query_arg('dir', $parent_dir);
        echo '<div class="vsp-parent-dir"><a href="' . esc_url($parent_url) . '"><i class="fas fa-arrow-left"></i> Go Up</a></div>';
    }

    // Dropdown for folders
    echo '<div class="vsp-folder-dropdown">';
    echo '<label for="folder-select">Select Folder:</label>';
    echo '<select id="folder-select" onchange="location = this.value;">';
    echo '<option value="">-- Select a folder --</option>';
    foreach ($folders as $folder) {
        $folder_url = add_query_arg('dir', $current_dir . '/' . $folder);
        echo '<option value="' . esc_url($folder_url) . '">' . esc_html($folder) . '</option>';
    }
    echo '</select>';
    echo '</div>';

    // Video list
    echo '<h2 class="vsp-title">Available Videos</h2>';
    if ($video_sizes) {
        echo '<ul class="vsp-item-list">';
        foreach ($video_sizes as $video => $file_size) {
            $file_size_human_readable = size_format($file_size); // Convert to human-readable format
            $video_url = site_url('wp-content/uploads/videos/' . ($current_dir ? $current_dir . '/' : '') . basename($video));
            echo '<li class="vsp-video">
                    <a href="' . esc_url($video_url) . '" target="_blank"><i class="fas fa-video"></i> ' . esc_html($video) . ' (' . esc_html($file_size_human_readable) . ')</a>
                    <button class="vsp-delete-video" data-video-name="' . esc_attr($video) . '">Delete</button>
                    <button class="vsp-rename-video" data-video-name="' . esc_attr($video) . '">Rename</button>
                  </li>';
        }
        echo '</ul>';
    } else {
        echo '<p class="vsp-no-items">No videos found.</p>';
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