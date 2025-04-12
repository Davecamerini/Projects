<?php
/**
 * Plugin Name: Database Backup Downloader
 * Plugin URI: https://github.com/yourusername/db-backupper
 * Description: A WordPress plugin to download gzipped database backups
 * Version: 1.0.0
 * Author: Davecamerini
 * Author URI: https://www.davecamerini.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

// Add menu item to WordPress admin
function db_backup_downloader_menu() {
    add_menu_page(
        'Database Backup Downloader',
        'DB Backup',
        'manage_options',
        'db-backup-downloader',
        'db_backup_downloader_page',
        plugins_url('Mon.png', __FILE__), // Use the image as the icon
        30
    );
}
add_action('admin_menu', 'db_backup_downloader_menu');

// Handle the backup download
function handle_db_backup_download() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access');
    }
    
    if (
        isset($_POST['db_backup_download']) &&
        check_admin_referer('db_backup_download', 'db_backup_download_nonce')
    ) {
        try {
            error_log('Starting backup process...');
            global $wpdb;
            
            // Store the backup timestamp
            $backup_time = current_time('mysql');
            error_log('Backup time: ' . $backup_time);
            update_option('db_backup_last_generated', $backup_time);
            
            // Create a temporary file in the uploads directory
            $upload_dir = wp_upload_dir();
            $temp_file = $upload_dir['path'] . '/db-backup-' . date('Y-m-d-H-i-s') . '.sql.gz';
            error_log('Created temporary file: ' . $temp_file);
            
            // Open gzip stream to temporary file
            $gz = gzopen($temp_file, 'w9');
            if ($gz === false) {
                error_log('Failed to open gzip stream');
                throw new Exception('Failed to open gzip stream');
            }
            
            // Add SQL header and database info
            error_log('Writing SQL header...');
            gzwrite($gz, "-- WordPress Database Backup\n");
            gzwrite($gz, "-- Generated: " . $backup_time . "\n");
            gzwrite($gz, "-- Host: " . DB_HOST . "\n");
            gzwrite($gz, "-- Database: " . DB_NAME . "\n\n");
            
            // Create backup process
            error_log('Getting tables from database...');
            $tables = $wpdb->get_results('SHOW TABLES', ARRAY_N);
            if ($tables === null) {
                error_log('Error getting tables: ' . $wpdb->last_error);
                throw new Exception('Failed to get tables from database: ' . $wpdb->last_error);
            }
            error_log('Found ' . count($tables) . ' tables');
            
            // Process each table
            foreach ($tables as $table) {
                $table_name = $table[0];
                error_log('Processing table: ' . $table_name);
                
                // Get create table syntax
                $create_table = $wpdb->get_row("SHOW CREATE TABLE `$table_name`", ARRAY_N);
                if ($create_table === null) {
                    error_log('Error getting create table syntax: ' . $wpdb->last_error);
                    throw new Exception("Failed to get create table syntax for $table_name: " . $wpdb->last_error);
                }
                gzwrite($gz, "\n\n-- Table structure for table `$table_name`\n\n");
                gzwrite($gz, $create_table[1] . ";\n\n");
                
                // Get table data
                error_log('Getting data for table: ' . $table_name);
                $rows = $wpdb->get_results("SELECT * FROM `$table_name`", ARRAY_N);
                if ($rows === null) {
                    error_log('Error getting table data: ' . $wpdb->last_error);
                    throw new Exception("Failed to get data from table $table_name: " . $wpdb->last_error);
                }
                error_log('Found ' . count($rows) . ' rows in table ' . $table_name);
                
                if ($rows) {
                    gzwrite($gz, "-- Dumping data for table `$table_name`\n");
                    
                    foreach ($rows as $row) {
                        $sql = "INSERT INTO `$table_name` VALUES (";
                        for ($i = 0; $i < count($row); $i++) {
                            $sql .= ($i > 0) ? ',' : '';
                            $sql .= is_null($row[$i]) ? 'NULL' : "'" . $wpdb->_real_escape($row[$i]) . "'";
                        }
                        $sql .= ");\n";
                        if (gzwrite($gz, $sql) === false) {
                            error_log('Error writing data for table: ' . $table_name);
                            throw new Exception("Failed to write data for table $table_name");
                        }
                    }
                }
                error_log('Completed processing table: ' . $table_name);
            }
            
            // Close gzip stream
            error_log('Closing gzip stream...');
            gzclose($gz);
            
            // Set headers for download
            header('Content-Type: application/x-gzip');
            header('Content-Disposition: attachment; filename="' . basename($temp_file) . '"');
            header('Content-Length: ' . filesize($temp_file));
            header('Pragma: no-cache');
            header('Expires: 0');
            
            // Output the file
            readfile($temp_file);
            
            // Clean up
            unlink($temp_file);
            
            exit;
            
        } catch (Exception $e) {
            error_log('Database Backup Error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            
            // Clean up temp file if it exists
            if (isset($temp_file) && file_exists($temp_file)) {
                unlink($temp_file);
            }
            
            wp_die(
                'Error generating backup: ' . esc_html($e->getMessage()),
                'Backup Error',
                array('response' => 500, 'back_link' => true)
            );
        }
    }
}

// Create the admin page
function db_backup_downloader_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access');
    }
    
    // Get the last backup timestamp
    $last_backup = get_option('db_backup_last_generated', 'Never');
    if ($last_backup !== 'Never') {
        $last_backup = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_backup));
    }
    ?>
    <div class="wrap">
        <h1 style="margin-bottom: 30px; color: #1d2327; font-size: 24px;">Database Backup Downloader</h1>
        <div class="nuke-cache-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; margin-top: 20px;">
            <div class="nuke-cache-card" style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); overflow: hidden; transition: all 0.3s ease; border: 1px solid #e2e4e7;">
                <div class="nuke-card-header" style="background: #f8f9fa; padding: 16px 20px; border-bottom: 1px solid #e9ecef; display: flex; align-items: center; gap: 12px;">
                    <span class="dashicons dashicons-database" style="font-size: 24px; width: 24px; height: 24px; color: #2271b1; display: flex; align-items: center; justify-content: center;"></span>
                    <h2 style="margin: 0; font-size: 16px; font-weight: 600; color: #1d2327;">Download Database Backup</h2>
                </div>
                <div class="nuke-card-content" style="padding: 24px;">
                    <div style="margin-bottom: 20px; color: #1d2327;">
                        <p style="margin: 0 0 10px 0; font-size: 14px;">This will create a complete backup of your WordPress database in a compressed .sql.gz file.</p>
                        <p style="margin: 0; font-size: 13px; color: #646970;">The backup will include all your WordPress tables and data.</p>
                    </div>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <input type="hidden" name="action" value="db_backup_download">
                        <?php wp_nonce_field('db_backup_download', 'db_backup_download_nonce'); ?>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="submit" name="db_backup_download" class="button button-primary" value="Download Backup" style="width: 100%; text-align: center; margin-top: 16px; padding: 8px 16px; height: auto; line-height: 1.4;">
                        </div>
                    </form>
                </div>
                <div class="nuke-card-footer" style="background: #f8f9fa; padding: 12px 20px; border-top: 1px solid #e9ecef; font-size: 13px; color: #646970;">
                    <p style="margin: 0;">Last backup generated: <?php echo esc_html($last_backup); ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php
}

// Register the download handler
add_action('admin_post_db_backup_download', 'handle_db_backup_download');