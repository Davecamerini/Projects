<?php
/*
Plugin Name: Nuke Cache
Description: Scans wp-content for cache folders and provides options to empty them.
Version: 1.0
Author: <a href="https://www.davecamerini.it">Davecamerini</a>
*/

function cache_folder_scanner_menu() {
    $icon_url = plugins_url('lotus.png', __FILE__);
    add_menu_page('Nuke Cache', 'Cache Nuker', 'manage_options', 'cache-folder-scanner', 'cache_folder_scanner_page', $icon_url);
}
add_action('admin_menu', 'cache_folder_scanner_menu');

function cache_folder_scanner_page() {
    echo '<h1>Cache Nuker</h1>';

    $cache_dir = WP_CONTENT_DIR . '/cache';
    $et_cache_dir = WP_CONTENT_DIR . '/et-cache';
    $cache_size = 0;
    $et_cache_size = 0;

    if (is_dir($cache_dir)) {
        $cache_size = folder_size($cache_dir);
        echo "<p>Cache folder found. Size: " . size_format($cache_size) . "</p>";
        echo '<form method="post"><button name="empty_cache">Empty Cache Folder</button></form>';
    }

    if (is_dir($et_cache_dir)) {
        $et_cache_size = folder_size($et_cache_dir);
        echo "<p>Et-cache folder found. Size: " . size_format($et_cache_size) . "</p>";
        echo '<form method="post"><button name="empty_et_cache">Empty Et-cache Folder</button></form>';
    }

    if (isset($_POST['empty_cache'])) {
        delete_folder($cache_dir);
        echo "<p>Cache folder emptied.</p>";
    }

    if (isset($_POST['empty_et_cache'])) {
        delete_folder($et_cache_dir);
        echo "<p>Et-cache folder emptied.</p>";
    }
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
