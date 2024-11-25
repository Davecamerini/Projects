<?php
/**
 * Plugin Name: WP React Calendar
 * Description: A React-based calendar plugin with simple event management
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
        add_action('rest_api_init', array($this, 'calendar_register_styles_endpoint'));
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
        // Create a parent menu item without a rendering function
        add_menu_page(
            'WP React Calendar', // Page title
            'WP React Calendar', // Menu title
            'manage_options', // Capability
            'wp-react-calendar', // Menu slug
            '', // No function for the parent menu
            'dashicons-calendar' // Icon
        );

        // Add the Calendar Events submenu
        add_submenu_page(
            'wp-react-calendar', // Parent slug
            'Events', // Page title
            'Events', // Menu title
            'manage_options', // Capability
            'wp-react-calendar', // Menu slug
            array($this, 'render_events_page') // Function to display the events page
        );

        // Add the Calendar Settings submenu
        add_submenu_page(
            'wp-react-calendar', // Parent slug
            'Settings', // Page title
            'Settings', // Menu title
            'manage_options', // Capability
            'calendar-settings', // Menu slug
            'calendar_settings_page' // Function to display the settings page
        );
    }

    // Render the events page
    public function render_events_page() {
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
            plugins_url('build/static/js/main.5c8e998a.js', __FILE__),
            array(),
            '1.0.0',
            true
        );

        wp_enqueue_style(
            'wp-react-calendar',
            plugins_url('build/static/css/main.ccca85d9.css', __FILE__),
            array(),
            '1.0.0'
        );
    }

    public function render_calendar() {
        return '<div id="wp-react-calendar"></div>';
    }

    public function calendar_register_styles_endpoint() {
        register_rest_route('wp-react-calendar/v1', '/styles', array(
            'methods' => 'GET',
            'callback' => array($this, 'calendar_get_styles'),
        ));
    }

    public function calendar_get_styles() {
        return [
            'calendar_bg_color' => get_option('calendar_bg_color', '#F20000'),
            'button_bg_color' => get_option('button_bg_color', 'darkred'),
            'button_hover_bg_color' => get_option('button_hover_bg_color', '#FF4500'),
            'calendar_cell_bg_color' => get_option('calendar_cell_bg_color', '#FFFFFF'),
            'calendar_cell_hover_bg_color' => get_option('calendar_cell_hover_bg_color', '#F0F0F0'),
            'calendar_header_color' => get_option('calendar_header_color', '#0073aa'),
            'event_pill_bg_color' => get_option('event_pill_bg_color', '#FFD700'),
        ];
    }
}

new WP_React_Calendar();

// Add the settings page function outside the class
function calendar_settings_page() {
    ?>
    <div class="wrap">
        <h1>Calendar Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('calendar_options_group');
            do_settings_sections('calendar-settings');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Calendar Background Color</th>
                    <td><input type="text" name="calendar_bg_color" value="<?php echo esc_attr(get_option('calendar_bg_color', '#F20000')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Calendar Header Color</th>
                    <td><input type="text" name="calendar_header_color" value="<?php echo esc_attr(get_option('calendar_header_color', '#0073aa')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Calendar Cell Background Color</th>
                    <td><input type="text" name="calendar_cell_bg_color" value="<?php echo esc_attr(get_option('calendar_cell_bg_color', '#FFFFFF')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Calendar Cell Hover Background Color</th>
                    <td><input type="text" name="calendar_cell_hover_bg_color" value="<?php echo esc_attr(get_option('calendar_cell_hover_bg_color', '#F0F0F0')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Button Background Color</th>
                    <td><input type="text" name="button_bg_color" value="<?php echo esc_attr(get_option('button_bg_color', 'darkred')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Button Hover Background Color</th>
                    <td><input type="text" name="button_hover_bg_color" value="<?php echo esc_attr(get_option('button_hover_bg_color', '#FF4500')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Event Pill Background Color</th>
                    <td><input type="text" name="event_pill_bg_color" value="<?php echo esc_attr(get_option('event_pill_bg_color', '#FFD700')); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Register settings
function calendar_register_settings() {
    register_setting('calendar_options_group', 'calendar_bg_color');
    register_setting('calendar_options_group', 'button_bg_color');
    register_setting('calendar_options_group', 'button_hover_bg_color');
    register_setting('calendar_options_group', 'calendar_cell_bg_color');
    register_setting('calendar_options_group', 'calendar_cell_hover_bg_color');
    register_setting('calendar_options_group', 'calendar_header_color');
    register_setting('calendar_options_group', 'event_pill_bg_color');
}
add_action('admin_init', 'calendar_register_settings');
