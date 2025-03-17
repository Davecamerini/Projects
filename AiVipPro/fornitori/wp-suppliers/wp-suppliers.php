<?php
/**
 * Plugin Name: WP Suppliers
 * Description: A WordPress plugin for managing suppliers
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: wp-suppliers
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    die('Direct access not permitted');
}

class WP_Suppliers {
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Add admin menu
        add_action('admin_menu', [$this, 'add_admin_menu']);
        
        // Register settings
        add_action('admin_init', [$this, 'register_settings']);
        
        // Add AJAX handlers
        add_action('wp_ajax_save_supplier', [$this, 'ajax_save_supplier']);
        add_action('wp_ajax_delete_supplier', [$this, 'ajax_delete_supplier']);
        
        // Add admin scripts and styles
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }
    
    /**
     * Add admin menu items
     */
    public function add_admin_menu() {
        // Add main menu
        add_menu_page(
            __('Suppliers', 'wp-suppliers'),
            __('Suppliers', 'wp-suppliers'),
            'manage_options',
            'wp-suppliers',
            [$this, 'render_suppliers_list'],
            'dashicons-store',
            30
        );
        
        // Add submenu items
        add_submenu_page(
            'wp-suppliers',
            __('Suppliers List', 'wp-suppliers'),
            __('Suppliers List', 'wp-suppliers'),
            'manage_options',
            'wp-suppliers',
            [$this, 'render_suppliers_list']
        );
        
        add_submenu_page(
            'wp-suppliers',
            __('Add New Supplier', 'wp-suppliers'),
            __('Add New', 'wp-suppliers'),
            'manage_options',
            'wp-suppliers-new',
            [$this, 'render_add_supplier']
        );
        
        add_submenu_page(
            'wp-suppliers',
            __('Supplier Settings', 'wp-suppliers'),
            __('Settings', 'wp-suppliers'),
            'manage_options',
            'wp-suppliers-settings',
            [$this, 'render_settings']
        );
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('wp_suppliers_settings', 'wp_suppliers_google_maps_api_key');
        
        add_settings_section(
            'wp_suppliers_main_section',
            __('Google Maps Integration', 'wp-suppliers'),
            null,
            'wp-suppliers-settings'
        );
        
        add_settings_field(
            'wp_suppliers_google_maps_api_key',
            __('Google Maps API Key', 'wp-suppliers'),
            [$this, 'render_api_key_field'],
            'wp-suppliers-settings',
            'wp_suppliers_main_section'
        );
    }
    
    /**
     * Render suppliers list page
     */
    public function render_suppliers_list() {
        include plugin_dir_path(__FILE__) . 'templates/admin/suppliers-list.php';
    }
    
    /**
     * Render add/edit supplier page
     */
    public function render_add_supplier() {
        include plugin_dir_path(__FILE__) . 'templates/admin/supplier-form.php';
    }
    
    /**
     * Render settings page
     */
    public function render_settings() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('wp_suppliers_settings');
                do_settings_sections('wp-suppliers-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Render Google Maps API Key field
     */
    public function render_api_key_field() {
        $value = get_option('wp_suppliers_google_maps_api_key');
        ?>
        <input type="text" 
               name="wp_suppliers_google_maps_api_key" 
               value="<?php echo esc_attr($value); ?>" 
               class="regular-text">
        <p class="description">
            <?php _e('Enter your Google Maps API Key to enable maps functionality.', 'wp-suppliers'); ?>
        </p>
        <?php
    }
    
    /**
     * Plugin activation
     */
    public static function activate() {
        // Create database tables
        self::create_tables();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Create database tables
     */
    private static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}suppliers (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            company_name varchar(255) NOT NULL,
            description text,
            address varchar(255),
            email varchar(255),
            phone varchar(50),
            website varchar(255),
            latitude decimal(10,8),
            longitude decimal(11,8),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;
        
        CREATE TABLE IF NOT EXISTS {$wpdb->prefix}supplier_gallery (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            supplier_id bigint(20) NOT NULL,
            image_url varchar(255) NOT NULL,
            title varchar(255),
            sort_order int DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY supplier_id (supplier_id),
            CONSTRAINT fk_supplier_gallery FOREIGN KEY (supplier_id) 
            REFERENCES {$wpdb->prefix}suppliers (id) ON DELETE CASCADE
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * AJAX handler for saving supplier
     */
    public function ajax_save_supplier() {
        check_ajax_referer('save_supplier', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'wp-suppliers')]);
        }
        
        global $wpdb;
        
        // Get and validate form data
        $company_name = isset($_POST['company_name']) ? sanitize_text_field($_POST['company_name']) : '';
        if (empty($company_name)) {
            wp_send_json_error(['message' => __('Company name is required.', 'wp-suppliers')]);
        }
        
        // Prepare supplier data
        $supplier_data = [
            'company_name' => $company_name,
            'description' => isset($_POST['description']) ? wp_kses_post($_POST['description']) : '',
            'email' => isset($_POST['email']) ? sanitize_email($_POST['email']) : '',
            'phone' => isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '',
            'website' => isset($_POST['website']) ? esc_url_raw($_POST['website']) : '',
            'address' => isset($_POST['address']) ? sanitize_text_field($_POST['address']) : '',
            'latitude' => isset($_POST['latitude']) ? floatval($_POST['latitude']) : null,
            'longitude' => isset($_POST['longitude']) ? floatval($_POST['longitude']) : null
        ];
        
        $supplier_id = isset($_POST['id']) ? absint($_POST['id']) : 0;
        
        // Start transaction
        $wpdb->query('START TRANSACTION');
        
        try {
            if ($supplier_id > 0) {
                // Update existing supplier
                $result = $wpdb->update(
                    $wpdb->prefix . 'suppliers',
                    $supplier_data,
                    ['id' => $supplier_id],
                    array_fill(0, count($supplier_data), '%s'),
                    ['%d']
                );
            } else {
                // Insert new supplier
                $result = $wpdb->insert(
                    $wpdb->prefix . 'suppliers',
                    $supplier_data,
                    array_fill(0, count($supplier_data), '%s')
                );
                $supplier_id = $wpdb->insert_id;
            }
            
            if ($result === false) {
                throw new Exception(__('Error saving supplier data.', 'wp-suppliers'));
            }
            
            // Handle gallery images
            if ($supplier_id) {
                // Update existing gallery items
                if (isset($_POST['gallery_items']) && is_array($_POST['gallery_items'])) {
                    $gallery_items = array_map('absint', $_POST['gallery_items']);
                    $gallery_titles = isset($_POST['gallery_titles']) ? array_map('sanitize_text_field', $_POST['gallery_titles']) : [];
                    
                    foreach ($gallery_items as $index => $item_id) {
                        $wpdb->update(
                            $wpdb->prefix . 'supplier_gallery',
                            [
                                'title' => isset($gallery_titles[$index]) ? $gallery_titles[$index] : '',
                                'sort_order' => $index
                            ],
                            ['id' => $item_id],
                            ['%s', '%d'],
                            ['%d']
                        );
                    }
                }
                
                // Add new gallery images
                if (isset($_POST['new_gallery_images']) && is_array($_POST['new_gallery_images'])) {
                    $new_images = array_map('esc_url_raw', $_POST['new_gallery_images']);
                    $new_titles = isset($_POST['new_gallery_titles']) ? array_map('sanitize_text_field', $_POST['new_gallery_titles']) : [];
                    
                    foreach ($new_images as $index => $image_url) {
                        $wpdb->insert(
                            $wpdb->prefix . 'supplier_gallery',
                            [
                                'supplier_id' => $supplier_id,
                                'image_url' => $image_url,
                                'title' => isset($new_titles[$index]) ? $new_titles[$index] : '',
                                'sort_order' => $index + count($gallery_items ?? [])
                            ],
                            ['%d', '%s', '%s', '%d']
                        );
                    }
                }
                
                // Delete removed gallery items
                if (isset($_POST['gallery_items'])) {
                    $gallery_items = array_map('absint', $_POST['gallery_items']);
                    $wpdb->query($wpdb->prepare(
                        "DELETE FROM {$wpdb->prefix}supplier_gallery 
                        WHERE supplier_id = %d AND id NOT IN (" . implode(',', array_fill(0, count($gallery_items), '%d')) . ")",
                        array_merge([$supplier_id], $gallery_items)
                    ));
                } else {
                    // If no gallery items are present, delete all
                    $wpdb->delete(
                        $wpdb->prefix . 'supplier_gallery',
                        ['supplier_id' => $supplier_id],
                        ['%d']
                    );
                }
            }
            
            $wpdb->query('COMMIT');
            
            wp_send_json_success([
                'message' => __('Supplier saved successfully.', 'wp-suppliers'),
                'id' => $supplier_id
            ]);
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * AJAX handler for deleting supplier
     */
    public function ajax_delete_supplier() {
        check_ajax_referer('delete_supplier', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'wp-suppliers')]);
        }
        
        if (empty($_POST['id'])) {
            wp_send_json_error(['message' => __('Invalid supplier ID.', 'wp-suppliers')]);
        }
        
        global $wpdb;
        
        $result = $wpdb->delete(
            $wpdb->prefix . 'suppliers',
            ['id' => absint($_POST['id'])],
            ['%d']
        );
        
        if ($result !== false) {
            wp_send_json_success(['message' => __('Supplier deleted successfully.', 'wp-suppliers')]);
        } else {
            wp_send_json_error(['message' => __('Error deleting supplier.', 'wp-suppliers')]);
        }
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if (!in_array($hook, ['suppliers_page_wp-suppliers-new', 'toplevel_page_wp-suppliers'])) {
            return;
        }
        
        // Enqueue WordPress media scripts
        wp_enqueue_media();
        
        // Enqueue jQuery UI sortable
        wp_enqueue_script('jquery-ui-sortable');
        
        // Add dashicons
        wp_enqueue_style('dashicons');
        
        // Add custom styles
        wp_add_inline_style('dashicons', '
            .supplier-gallery .gallery-items {
                display: flex;
                flex-wrap: wrap;
                gap: 20px;
                margin: 20px 0;
            }
            .supplier-gallery .gallery-item {
                position: relative;
                width: 150px;
            }
            .supplier-gallery .gallery-item img {
                width: 150px;
                height: 150px;
                object-fit: cover;
                display: block;
            }
            .supplier-gallery .remove-image {
                position: absolute;
                top: 5px;
                right: 5px;
                background: rgba(255,255,255,0.8);
                border-radius: 50%;
                padding: 0;
                width: 24px;
                height: 24px;
                cursor: pointer;
            }
            .supplier-gallery .remove-image .dashicons {
                font-size: 16px;
                width: 16px;
                height: 16px;
                line-height: 24px;
            }
            .supplier-gallery .gallery-item input[type="text"] {
                width: 100%;
                margin-top: 5px;
            }
        ');
    }
}

// Initialize plugin
add_action('plugins_loaded', ['WP_Suppliers', 'get_instance']);

// Register activation hook
register_activation_hook(__FILE__, ['WP_Suppliers', 'activate']); 