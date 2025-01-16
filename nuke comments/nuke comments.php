<?php
/**
 * Plugin Name: Nuke Comments
 * Description: A simple plugin to nuke the wp_comments table.
 * Version: 1.0
 * Author: <a href="https://www.davecamerini.it">Davecamerini</a>
 */

// Hook to add a menu item in the admin dashboard
add_action('admin_menu', 'empty_comments_menu');

function empty_comments_menu() {
    $icon_url = plugins_url('lotus.png', __FILE__);
    add_menu_page('Nuke Comments', 'Nuke Comments', 'manage_options', 'nuke-comments', 'nuke_comments_page', $icon_url);
}

function nuke_comments_page() {
    if (isset($_POST['nuke_comments'])) {
        global $wpdb;
        $wpdb->query("TRUNCATE TABLE $wpdb->comments");
        echo '<div class="updated"><p>All comments have been nuked.</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>Nuke Comments</h1>
        <form method="post" style="margin-top: 20px;">
            <input type="submit" name="nuke_comments" class="button button-primary" value="Nuke Comments" onclick="return confirm('Are you sure you want to delete all comments?');" />
        </form>
    </div>
    <?php
}
