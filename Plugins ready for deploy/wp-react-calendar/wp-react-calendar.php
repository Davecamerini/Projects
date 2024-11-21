<?php
/**
 * Plugin Name: WP React Calendar
 * Description: A React-based calendar plugin with event management
 * Version: 1.0
 * Author: <a href="https://www.davecamerini.com">Davecamerini</a>
 */

if (!defined('ABSPATH')) exit;

class WP_React_Calendar {
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_shortcode('react_calendar', array($this, 'render_calendar'));
        register_activation_hook(__FILE__, array($this, 'create_events_table'));
    }

    public function init() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('rest_api_init', array($this, 'register_rest_routes'));
    }

    public function create_events_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'calendar_events';
        
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            date datetime NOT NULL,
            link varchar(255) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function add_admin_menu() {
        add_menu_page(
            'Calendar Events',
            'Calendar Events',
            'manage_options',
            'calendar-events',
            array($this, 'render_admin_page'),
            'dashicons-calendar'
        );
    }

    public function render_admin_page() {
        include plugin_dir_path(__FILE__) . 'admin/admin-page.php';
    }

    public function register_rest_routes() {
        register_rest_route('wp-react-calendar/v1', '/events', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_events'),
            'permission_callback' => '__return_true'
        ));
    }

    public function get_events() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'calendar_events';
        $events = $wpdb->get_results("SELECT * FROM $table_name ORDER BY date ASC");
        return $events;
    }

    public function enqueue_scripts() {
        wp_enqueue_script(
            'wp-react-calendar',
            plugins_url('build/static/js/main.09b38780.js', __FILE__),
            array(),
            '1.0.0',
            true
        );

        wp_enqueue_style(
            'wp-react-calendar',
            plugins_url('build/static/css/main.eb7dc0a6.css', __FILE__),
            array(),
            '1.0.0'
        );
    }

    public function render_calendar() {
        return '<div id="wp-react-calendar"></div>';
    }
}

new WP_React_Calendar();
