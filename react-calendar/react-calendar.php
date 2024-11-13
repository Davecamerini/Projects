<?php
/*
Plugin Name: React Calendar
Description: A custom calendar built with React
Version: 1.0
Author: Your Name
*/

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('RC_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('RC_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include API functionality
require_once RC_PLUGIN_PATH . 'includes/api.php';

// Register scripts and styles
function rc_enqueue_scripts() {
    wp_enqueue_style(
        'react-calendar-style',
        RC_PLUGIN_URL . 'assets/css/calendar.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_script(
        'react-calendar-script',
        RC_PLUGIN_URL . 'assets/js/calendar.js',
        array(),
        '1.0.0',
        true
    );

    // Pass WordPress data to JavaScript
    wp_localize_script('react-calendar-script', 'rcData', array(
        'root_url' => rest_url(),
        'nonce' => wp_create_nonce('wp_rest')
    ));
}
add_action('wp_enqueue_scripts', 'rc_enqueue_scripts');

// Register shortcode
function rc_calendar_shortcode() {
    return '<div id="react-calendar-root"></div>';
}
add_shortcode('react_calendar', 'rc_calendar_shortcode');