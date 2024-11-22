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

// Function to handle the database backup and download
function db_backup_download() {
    error_log('Backup download function called'); // Debug statement
    global $wpdb;

    // Set the filename for the backup
    $backup_file = 'db-backup-' . date('Y-m-d_H-i-s') . '.sql';

    // Command to create a backup
    $command = "mysqldump --user={$wpdb->dbuser} --password={$wpdb->dbpassword} --host={$wpdb->dbhost} {$wpdb->dbname} > " . ABSPATH . $backup_file;

    // Execute the command
    $output = null;
    $return_var = null;
    exec($command, $output, $return_var);

    if ($return_var !== 0) {
        error_log('Backup failed: ' . implode("\n", $output)); // Log the error
    }

    // Check if the file exists and download it
    if (file_exists(ABSPATH . $backup_file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename=' . basename($backup_file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize(ABSPATH . $backup_file));
        readfile(ABSPATH . $backup_file);
        exit;
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
