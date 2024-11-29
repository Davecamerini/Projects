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
    return $backup_file; // Return the name of the backup file
}

// Function to handle backup download
function handle_backup_download() {
    // Set the backup directory to the "backups" folder within the plugin's directory
    $backup_dir = plugin_dir_path(__FILE__) . 'backups/'; // Adjust the path as needed

    // Get all SQL backup files in the directory
    $backup_files = glob($backup_dir . '*.sql');

    // Check if there are any backup files
    if (empty($backup_files)) {
        error_log("No backup files found in the directory.");
        return false; // No backup files found
    }

    // Sort files by modification time, newest first
    usort($backup_files, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });

    // Get the most recent backup file
    $most_recent_backup = $backup_files[0];
    $gz_diskfile = "{$most_recent_backup}.gz";

    // Check if the gzipped file exists, if not, create it
    if (file_exists($most_recent_backup) && !file_exists($gz_diskfile)) {
        if (function_exists('gzencode') && function_exists('file_get_contents')) {
            $contents = file_get_contents($most_recent_backup);
            $gzipped = gzencode($contents, 9);
            file_put_contents($gz_diskfile, $gzipped);
            unlink($most_recent_backup); // Optionally delete the original file
        }
    }

    // Determine which file to deliver
    $file_to_deliver = file_exists($gz_diskfile) ? $gz_diskfile : $most_recent_backup;

    // Check if the file exists for download
    if (!file_exists($file_to_deliver)) {
        error_log("File not found: $file_to_deliver");
        return false; // File not found
    }

    // Clear any previous output
    ob_clean(); // Clear the output buffer
    flush(); // Flush the system output buffer

    // Set headers for download
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Length: ' . filesize($file_to_deliver));
    header("Content-Disposition: attachment; filename=" . basename($file_to_deliver));
    header('Pragma: public');
    header('Expires: 0');

    // Read the file and send it to the output
    readfile($file_to_deliver);
    exit; // Terminate the script after sending the file
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

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['db_backup_action']) && $_POST['db_backup_action'] === 'create_backup') {
            $backup_file = db_create_backup(); // Create a backup
            echo '<div class="updated"><p>Backup created successfully!</p></div>';
        } elseif (isset($_POST['db_backup_action']) && $_POST['db_backup_action'] === 'download_backup') {
            handle_backup_download(); // Download the most recent backup
        }
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
        <form method="post" action="">
            <input type="hidden" name="db_backup_action" value="download_backup">
            <input type="hidden" name="backup_file" value="<?php echo esc_attr($backup_file); ?>">
            <p>
                <input type="submit" class="button button-secondary" value="Download Most Recent Backup">
            </p>
        </form>
    </div>
    <?php
}
