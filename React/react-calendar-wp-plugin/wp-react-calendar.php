<?php
/**
 * Plugin Name: WP React Calendar
 * Description: A React-based calendar plugin with event management
 * Version: 1.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) exit;

class WP_React_Calendar {
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_shortcode('react_calendar', array($this, 'render_calendar'));
    }

    public function init() {
        // Register scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Register REST API endpoints
        add_action('rest_api_init', array($this, 'register_rest_routes'));
    }

    public function enqueue_scripts() {
        wp_enqueue_script(
            'wp-react-calendar',
            plugins_url('build/static/js/main.js', __FILE__),
            array(),
            '1.0.0',
            true
        );
    }

    public function render_calendar($atts) {
        return '<div id="wp-react-calendar"></div>';
    }

    public function register_rest_routes() {
        register_rest_route('wp-react-calendar/v1', '/events', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_events'),
            'permission_callback' => '__return_true'
        ));
    }
}

new WP_React_Calendar(); 