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

    // Handle video upload
    if (isset($_POST['upload_video'])) {
        if (!empty($_FILES['video_file']['name'])) {
            $uploaded_file = $_FILES['video_file'];
            $upload_path = $video_dir . '/' . basename($uploaded_file['name']);
            move_uploaded_file($uploaded_file['tmp_name'], $upload_path);
            echo '<p class="vsp-upload-success">Video uploaded successfully!</p>';
        }
    }

    // Upload form
    echo '<h2 class="vsp-upload-title">Upload Video</h2>';
    echo '<form method="post" enctype="multipart/form-data" class="vsp-upload-form">';
    echo '<input type="file" name="video_file" accept="video/*" required class="vsp-file-input">';
    echo '<input type="submit" name="upload_video" value="Upload Video" class="vsp-upload-button">';
    echo '</form>';

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
            } elseif (preg_match('/\.(mp4|webm|ogg)$/i', $item)) {
                $videos[] = ucfirst($item); // Capitalize the first letter and add to videos array
            }
        }
    }

    // Sort folders and videos alphabetically
    sort($folders);
    sort($videos);

    // Start the layout
    echo '<div class="vsp-container">';

    // Display "Go Up" link if in a subfolder
    if ($current_dir && $current_dir !== '/') {
        $parent_dir = dirname($current_dir);
        $parent_url = add_query_arg('dir', $parent_dir);
        echo '<div class="vsp-parent-dir"><a href="' . esc_url($parent_url) . '"><i class="fas fa-arrow-left"></i> Go Up</a></div>';
    }

    // Display folders
    if ($folders) {
        echo '<div class="vsp-column vsp-folders"><h2 class="vsp-title">Available Folders</h2><ul class="vsp-item-list">';
        foreach ($folders as $folder) {
            $folder_url = add_query_arg('dir', $current_dir . '/' . $folder);
            echo '<li class="vsp-folder"><a href="' . esc_url($folder_url) . '"><i class="fas fa-folder"></i> ' . esc_html($folder) . '</a></li>';
        }
        echo '</ul></div>';
    }

    // Display videos
    if ($videos) {
        // If there are no folders, display videos in a single column
        if (empty($folders)) {
            echo '<div class="vsp-column"><h2 class="vsp-title">Available Videos</h2><ul class="vsp-item-list">';
            foreach ($videos as $video) {
                $video_url = site_url('wp-content/uploads/videos/' . ($current_dir ? $current_dir . '/' : '') . basename($video));
                echo '<li class="vsp-video"><a href="' . esc_url($video_url) . '" target="_blank"><i class="fas fa-video"></i> ' . esc_html($video) . '</a></li>';
            }
            echo '</ul></div>'; // Wrap both heading and list in the same column
        } else {
            // If there are folders, display videos in a separate column
            echo '<div class="vsp-column vsp-videos"><h2 class="vsp-title">Available Videos</h2><ul class="vsp-item-list">';
            foreach ($videos as $video) {
                $video_url = site_url('wp-content/uploads/videos/' . ($current_dir ? $current_dir . '/' : '') . basename($video));
                echo '<li class="vsp-video"><a href="' . esc_url($video_url) . '" target="_blank"><i class="fas fa-video"></i> ' . esc_html($video) . '</a></li>';
            }
            echo '</ul></div>';
        }
    } else {
        echo '<p class="vsp-no-items">No videos found.</p>';
    }

    // End the layout
    echo '</div>';

    return ob_get_clean();
}
add_shortcode('video_streaming', 'vsp_video_page');

// Enqueue video player script and Font Awesome
function vsp_enqueue_scripts() {
    if (is_page('video-streaming')) {
        wp_enqueue_script('videojs', 'https://vjs.zencdn.net/7.11.4/video.min.js', array(), null, true);
        wp_enqueue_style('videojs-css', 'https://vjs.zencdn.net/7.11.4/video-js.min.css');
        wp_enqueue_style('custom-style', plugin_dir_url(__FILE__) . 'custom-style.css'); // Enqueue custom styles
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css'); // Enqueue Font Awesome
    }
}
add_action('wp_enqueue_scripts', 'vsp_enqueue_scripts'); 