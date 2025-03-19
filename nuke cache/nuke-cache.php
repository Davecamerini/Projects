<?php
/*
Plugin Name: Nuke Cache
Description: Scans wp-content for cache folders and provides options to empty them.
Version: 1.0
Author: <a href="https://www.davecamerini.it">Davecamerini</a>
*/

// Hook to add a menu item in the admin dashboard
add_action('admin_menu', 'cache_folder_scanner_menu');

function cache_folder_scanner_menu() {
    $icon_url = plugins_url('Mon white trasp.png', __FILE__);
    add_menu_page('Nuke Cache', 'Cache Nuker', 'manage_options', 'cache-folder-scanner', 'cache_folder_scanner_page', $icon_url, 30);
}

function cache_folder_scanner_page() {
    // Define cache directories
    $cache_dir = WP_CONTENT_DIR . '/cache';
    $et_cache_dir = WP_CONTENT_DIR . '/et-cache';

    // Initialize cache sizes
    $cache_size = is_dir($cache_dir) ? folder_size($cache_dir) : 0;
    $et_cache_size = is_dir($et_cache_dir) ? folder_size($et_cache_dir) : 0;

    if (isset($_POST['empty_cache'])) {
        delete_folder($cache_dir);
        echo '<div class="updated"><p>Cache folder emptied.</p></div>';
        // Refresh the cache size after deletion
        $cache_size = is_dir($cache_dir) ? folder_size($cache_dir) : 0; // Re-query the cache size
    }

    if (isset($_POST['empty_et_cache'])) {
        delete_folder($et_cache_dir);
        echo '<div class="updated"><p>Et-cache folder emptied.</p></div>';
        // Refresh the et-cache size after deletion
        $et_cache_size = is_dir($et_cache_dir) ? folder_size($et_cache_dir) : 0; // Re-query the et-cache size
    }
    ?>
    <div class="wrap">
        <h1>Cache Nuker</h1>
        <?php if ($cache_size > 0): ?>
            <p>Cache folder found. Size: <strong><?php echo size_format($cache_size); ?></strong></p>
            <form method="post">
                <input type="submit" name="empty_cache" class="button button-primary" value="Empty Cache Folder" />
            </form>
        <?php else: ?>
            <p>No Cache folder found.</p>
        <?php endif; ?>

        <?php if ($et_cache_size > 0): ?>
            <p>Et-cache folder found. Size: <strong><?php echo size_format($et_cache_size); ?></strong></p>
            <form method="post">
                <input type="submit" name="empty_et_cache" class="button button-primary" value="Empty Et-cache Folder" />
            </form>
        <?php else: ?>
            <p>No Et-cache folder found.</p>
        <?php endif; ?>
    </div>
    <?php
}

function folder_size($dir) {
    $size = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $file) {
        $size += $file->getSize();
    }
    return $size;
}

function delete_folder($dir) {
    if (!is_dir($dir)) return;
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? delete_folder("$dir/$file") : unlink("$dir/$file");
    }
    rmdir($dir);
}
?>
