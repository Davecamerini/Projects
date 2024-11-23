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
            plugins_url('build/static/js/main.92ea504d.js', __FILE__),
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
            'calendar_cell_bg_color' => get_option('calendar_cell_bg_color', '#FFFFFF'),
            'calendar_header_color' => get_option('calendar_header_color', '#0073aa'),
            'event_pill_bg_color' => get_option('event_pill_bg_color', '#FFD700'),
        ];
    }
}

new WP_React_Calendar();

// Add a menu item for the settings page
function calendar_settings_menu() {
    add_menu_page(
        'Calendar Settings',
        'Calendar Settings',
        'manage_options',
        'calendar-settings',
        'calendar_settings_page'
    );
}
add_action('admin_menu', 'calendar_settings_menu');

// Display the settings page
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
                    <th scope="row">Button Background Color</th>
                    <td><input type="text" name="button_bg_color" value="<?php echo esc_attr(get_option('button_bg_color', 'darkred')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Calendar Cell Background Color</th>
                    <td><input type="text" name="calendar_cell_bg_color" value="<?php echo esc_attr(get_option('calendar_cell_bg_color', '#FFFFFF')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Calendar Header Color</th>
                    <td><input type="text" name="calendar_header_color" value="<?php echo esc_attr(get_option('calendar_header_color', '#0073aa')); ?>" /></td>
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
    register_setting('calendar_options_group', 'calendar_cell_bg_color');
    register_setting('calendar_options_group', 'calendar_header_color');
    register_setting('calendar_options_group', 'event_pill_bg_color');
}
add_action('admin_init', 'calendar_register_settings');
