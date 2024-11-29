<?php
/**
 * Plugin Name: Empty Comments
 * Description: A simple plugin to empty the wp_comments table.
 * Version: 1.0
 * Author: <a href="https://www.davecamerini.it">Davecamerini</a>
 */

// Hook to add a menu item in the admin dashboard
add_action('admin_menu', 'empty_comments_menu');

function empty_comments_menu() {
    add_menu_page('Empty Comments', 'Empty Comments', 'manage_options', 'empty-comments', 'empty_comments_page');
}

function empty_comments_page() {
    if (isset($_POST['empty_comments'])) {
        global $wpdb;
        $wpdb->query("TRUNCATE TABLE $wpdb->comments");
        echo '<div class="updated"><p>All comments have been deleted.</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>Empty Comments</h1>
        <form method="post">
            <input type="submit" name="empty_comments" class="button button-primary" value="Empty Comments" onclick="return confirm('Are you sure you want to delete all comments?');" />
        </form>
    </div>
    <?php
}
