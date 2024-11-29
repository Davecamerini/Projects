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

// Function to download the most recent backup
function db_download_backup() {
    $files = glob(DB_BACKUP_DIR . '*.sql'); // Get all SQL files in the backup directory
    if ($files) {
        // Sort files by modification time, newest first
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        $latest_file = $files[0]; // Get the most recent file

        // Clear any previous output
        if (ob_get_length()) {
            ob_end_clean(); // Clean the output buffer
        }

        // Set headers for download
        header('Content-Description: File Transfer');
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename=' . basename($latest_file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($latest_file));

        // Read the file and send it to the output
        readfile($latest_file);
        exit; // Terminate the script after sending the file
    } else {
        error_log('No backup files found in the backups directory.'); // Log if no files found
    }
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
            db_create_backup(); // Create a backup
            echo '<div class="updated"><p>Backup created successfully!</p></div>';
        } elseif (isset($_POST['db_backup_action']) && $_POST['db_backup_action'] === 'download_backup') {
            db_download_backup(); // Download the most recent backup
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
            <p>
                <input type="submit" class="button button-secondary" value="Download Most Recent Backup">
            </p>
        </form>
    </div>
    <?php
}
