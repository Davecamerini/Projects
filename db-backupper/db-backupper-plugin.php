<?php
ob_start(); // Start output buffering

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

// Function to handle the database backup and download
function db_backup_download() {
    global $wpdb;

    // Set the filename for the backup
    $backup_file = 'db-backup-' . date('Y-m-d_H-i-s') . '.sql';
    $backup_path = ABSPATH . $backup_file;

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

    // Check if the file exists and download it
    if (file_exists($backup_path)) {
        // Set headers for download
        header('Content-Description: File Transfer');
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename=' . basename($backup_file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($backup_path));

        // Clear the output buffer
        ob_end_clean(); // Clean the output buffer and turn off output buffering

        // Read the file and send it to the output
        readfile($backup_path);
        exit;
    } else {
        error_log('Backup file does not exist: ' . $backup_path); // Log file absence
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

    ?>
    <div class="wrap">
        <h1>Database Backup</h1>
        <form method="post" action="">
            <input type="hidden" name="db_backup_action" value="backup_db">
            <p>
                <input type="submit" class="button button-primary" value="Download Database Backup">
            </p>
        </form>
    </div>
    <?php

    // Debugging: Check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        error_log('Form submitted'); // Log form submission
        if (isset($_POST['db_backup_action']) && $_POST['db_backup_action'] === 'backup_db') {
            error_log('Backup action triggered'); // Log backup action
            db_backup_download();
        } else {
            error_log('Backup action not set'); // Log if action is not set
        }
    }
}

// At the end of your plugin, you can flush the buffer if needed
ob_end_flush();
