<?php
/*
Plugin Name: Video Streaming Plugin
Description: Allows users to upload videos and stream them.
Version: 1.0
Author: Your Name
*/

// Define the custom video directory
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

    // Handle video upload
    if (isset($_POST['upload_video'])) {
        if (!empty($_FILES['video_file']['name'])) {
            $uploaded_file = $_FILES['video_file'];
            $upload_path = $video_dir . '/' . basename($uploaded_file['name']);
            move_uploaded_file($uploaded_file['tmp_name'], $upload_path);
            echo '<p>Video uploaded successfully!</p>';
        }
    }

    // List folders and videos
    $items = scandir($video_dir);
    if ($items) {
        echo '<h2>Available Videos and Folders</h2><ul>';
        
        // Link to go back to the parent directory
        if ($current_dir) {
            $parent_dir = dirname($current_dir);
            $parent_url = add_query_arg('dir', $parent_dir);
            echo '<li><a href="' . esc_url($parent_url) . '">.. (Go Up)</a></li>';
        }

        foreach ($items as $item) {
            if ($item !== '.' && $item !== '..') {
                $item_path = $video_dir . '/' . $item;
                if (is_dir($item_path)) {
                    // Create a link to navigate into the folder
                    $folder_url = add_query_arg('dir', $current_dir . '/' . $item);
                    echo '<li><a href="' . esc_url($folder_url) . '">' . esc_html($item) . '</a></li>';
                } elseif (preg_match('/\.(mp4|webm|ogg)$/i', $item)) {
                    // Construct the video URL
                    $video_url = site_url('wp-content/uploads/videos/' . ($current_dir ? $current_dir . '/' : '') . basename($item));
                    echo '<li><a href="' . esc_url($video_url) . '" target="_blank">' . esc_html($item) . '</a></li>';
                }
            }
        }
        echo '</ul>';
    } else {
        echo '<p>No videos or folders found.</p>';
    }

    // Upload form
    echo '<h2>Upload Video</h2>';
    echo '<form method="post" enctype="multipart/form-data">';
    echo '<input type="file" name="video_file" accept="video/*" required>';
    echo '<input type="submit" name="upload_video" value="Upload Video">';
    echo '</form>';

    return ob_get_clean();
}
add_shortcode('video_streaming', 'vsp_video_page');

// Enqueue video player script
function vsp_enqueue_scripts() {
    if (is_page('video-streaming')) {
        wp_enqueue_script('videojs', 'https://vjs.zencdn.net/7.11.4/video.min.js', array(), null, true);
        wp_enqueue_style('videojs-css', 'https://vjs.zencdn.net/7.11.4/video-js.min.css');
    }
}
add_action('wp_enqueue_scripts', 'vsp_enqueue_scripts'); 