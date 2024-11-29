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

    // Start output buffering to prevent any output
    ob_start();

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
            // Prepare values for SQL insert
            $values = array_map(function($value) use ($wpdb) {
                return "'" . esc_sql($value) . "'"; // Escape values for SQL
            }, array_values($row));
            $values = implode(", ", $values);
            fwrite($handle, "INSERT INTO `$table` VALUES ($values);\n");
        }
        fwrite($handle, "\n\n");
    }

    fclose($handle);

    // Create gzipped version of the backup
    $gz_file = "{$backup_path}.gz";
    if (function_exists('gzencode') && function_exists('file_get_contents')) {
        // Read the contents of the SQL file
        $contents = file_get_contents($backup_path);
        if ($contents === false) {
            error_log("Failed to read the backup file: $backup_path");
            return false; // Handle error
        }

        // Compress the contents
        $gzipped = gzencode($contents, 9);
        if ($gzipped === false) {
            error_log("Failed to gzip the backup file: $backup_path");
            return false; // Handle error
        }

        // Write the gzipped contents to the new file
        if (file_put_contents($gz_file, $gzipped) === false) {
            error_log("Failed to write gzipped file: $gz_file");
            return false; // Handle error
        }

        unlink($backup_path); // Optionally delete the original file
    }

    // Store the most recent backup file in an option
    update_option('db_recent_backup', basename($gz_file)); // Store only the filename

    // End output buffering and clean
    ob_end_clean();

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
    $message = ''; // Initialize message variable

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['db_backup_action']) && $_POST['db_backup_action'] === 'create_backup') {
            $gz_file = db_create_backup(); // Create a backup
            if ($gz_file) {
                $download_link = esc_url(plugin_dir_url(__FILE__) . 'backups/' . basename($gz_file)); // Set the download link
                $message = '<div class="updated"><p>Backup created successfully!</p></div>'; // Success message without link
            } else {
                $message = '<div class="error"><p>Backup creation failed. Please try again.</p></div>';
            }
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
        <?php if ($message): ?>
            <?php echo $message; // Display the message ?>
        <?php endif; ?>
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
