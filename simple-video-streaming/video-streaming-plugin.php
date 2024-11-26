<?php
/*
Plugin Name: Video Streaming Plugin
Description: Allows users to upload videos and stream them.
Version: 1.0
Author: Your Name
*/

// Define the upload directory
define('VIDEO_UPLOAD_DIR', wp_upload_dir()['basedir'] . '/videos');

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
    ?>
    <h2>Upload Video</h2>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="video_file" accept="video/*" required>
        <input type="submit" name="upload_video" value="Upload Video">
    </form>
    
    <?php
    // Handle video upload
    if (isset($_POST['upload_video'])) {
        if (!empty($_FILES['video_file']['name'])) {
            $uploaded_file = $_FILES['video_file'];
            $upload_path = VIDEO_UPLOAD_DIR . '/' . basename($uploaded_file['name']);
            move_uploaded_file($uploaded_file['tmp_name'], $upload_path);
            echo '<p>Video uploaded successfully!</p>';
        }
    }

    // List videos
    $videos = glob(VIDEO_UPLOAD_DIR . '/*.{mp4,webm,ogg}', GLOB_BRACE);
    if ($videos) {
        echo '<h2>Available Videos</h2><ul>';
        foreach ($videos as $video) {
            $video_url = wp_upload_dir()['baseurl'] . '/videos/' . basename($video);
            echo '<li><a href="' . esc_url($video_url) . '" target="_blank">' . esc_html(basename($video)) . '</a></li>';
        }
        echo '</ul>';
    } else {
        echo '<p>No videos found.</p>';
    }
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
