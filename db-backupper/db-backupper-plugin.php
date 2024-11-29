<?php
/**
 * Plugin Name: Database Backup Plugin
 * Description: A simple plugin to back up and download the entire database.
 * Version: 1.0
 * Author: <a href="https://www.davecamerini.it">Davecamerini</a>
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define the backup directory
define('DB_BACKUP_DIR', plugin_dir_path(__FILE__) . 'backups/');

// Create the backup directory if it doesn't exist
if (!file_exists(DB_BACKUP_DIR)) {
    mkdir(DB_BACKUP_DIR, 0755, true);
}

// Function to handle the database backup
function db_create_backup() {
    global $wpdb;

    // Set the filename for the backup
    $backup_file = 'db-backup-' . date('Y-m-d_H-i-s') . '.sql';
    $backup_path = DB_BACKUP_DIR . $backup_file;

    // Get all tables in the database
    $tables = $wpdb->get_col('SHOW TABLES');

    // Open a file for writing
    $handle = fopen($backup_path, 'w');

    // Loop through each table and write its structure and data
    foreach ($tables as $table) {
        // Get the table structure
        $create_table = $wpdb->get_row("SHOW CREATE TABLE `$table`", ARRAY_N);
        fwrite($handle, $create_table[1] . ";\n\n");

        // Get the table data
        $rows = $wpdb->get_results("SELECT * FROM `$table`", ARRAY_A);
        foreach ($rows as $row) {
            $values = array_map([$wpdb, 'prepare'], array_values($row));
            $values = implode(", ", $values);
            fwrite($handle, "INSERT INTO `$table` VALUES ($values);\n");
        }
        fwrite($handle, "\n\n");
    }

    fclose($handle);

    // Create gzipped version of the backup
    $gz_file = "{$backup_path}.gz";
    if (function_exists('gzencode') && function_exists('file_get_contents')) {
        $contents = file_get_contents($backup_path);
        $gzipped = gzencode($contents, 9);
        file_put_contents($gz_file, $gzipped);
        unlink($backup_path); // Optionally delete the original file
    }

    // Store the most recent backup file in an option
    update_option('db_recent_backup', basename($gz_file)); // Store only the filename

    return $gz_file; // Return the name of the gzipped backup file
}

// Function to create the admin menu
function db_backup_menu() {
    add_menu_page(
        'Database Backup',
        'DB Backup',
        'manage_options',
        'db-backup',
        'db_backup_page'
    );
}
add_action('admin_menu', 'db_backup_menu');

// Function to display the admin page
function db_backup_page() {
    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

    $download_link = ''; // Initialize download link variable

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['db_backup_action']) && $_POST['db_backup_action'] === 'create_backup') {
            db_create_backup(); // Create a backup
        }
    }

    // Retrieve the most recent backup filename
    $recent_backup = get_option('db_recent_backup');
    if ($recent_backup) {
        $download_link = esc_url(plugin_dir_url(__FILE__) . 'backups/' . $recent_backup); // Set the download link
    }

    ?>
    <div class="wrap">
        <h1>Database Backup</h1>
        <form method="post" action="">
            <input type="hidden" name="db_backup_action" value="create_backup">
            <p>
                <input type="submit" class="button button-primary" value="Create Database Backup">
            </p>
        </form>
        <?php if ($download_link): ?>
            <p>
                <a href="<?php echo $download_link; ?>" download class="button button-secondary">Download Most Recent Backup</a>
            </p>
        <?php endif; ?>
    </div>
    <?php
}
