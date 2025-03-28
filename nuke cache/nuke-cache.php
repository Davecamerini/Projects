<?php
/**
 * Plugin Name: Nuke Cache
 * Plugin URI: https://www.davecamerini.it/nuke-cache
 * Description: Scans wp-content for cache folders and provides options to empty them.
 * Version: 1.0.0
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * Author: Davecamerini
 * Author URI: https://www.davecamerini.it
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: nuke cache
 * Domain Path: /languages
 */

// Hook to add a menu item in the admin dashboard
add_action('admin_menu', 'cache_folder_scanner_menu');
add_action('admin_enqueue_scripts', 'nuke_cache_admin_styles');

function nuke_cache_admin_styles($hook) {
    if ('toplevel_page_cache-folder-scanner' !== $hook) {
        return;
    }
    ?>
    <style>
        .nuke-cache-container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .nuke-cache-container h1 {
            margin-bottom: 30px;
            color: #1d2327;
            font-size: 24px;
        }
        .nuke-cache-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            margin-top: 20px;
        }
        .nuke-cache-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid #e2e4e7;
        }
        .nuke-cache-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }
        .nuke-card-header {
            background: #f8f9fa;
            padding: 16px 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .nuke-card-header .dashicons {
            font-size: 24px;
            width: 24px;
            height: 24px;
            color: #2271b1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .nuke-card-header h2 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #1d2327;
        }
        .nuke-card-content {
            padding: 24px;
        }
        .nuke-cache-size {
            font-size: 32px;
            font-weight: 600;
            color: #2271b1;
            line-height: 1.2;
            margin-bottom: 8px;
            text-align: center;
        }
        .nuke-cache-label {
            font-size: 14px;
            color: #646970;
            font-weight: 500;
            text-align: center;
            margin-bottom: 20px;
        }
        .nuke-cache-status {
            padding: 12px;
            border-radius: 6px;
            margin: 16px 0;
            text-align: center;
            font-size: 14px;
            font-weight: 500;
        }
        .nuke-cache-status.found {
            background: #f0f6fc;
            color: #2271b1;
            border: 1px solid #c5d9ed;
        }
        .nuke-cache-status.empty {
            background: #f0f6fc;
            color: #646970;
            border: 1px solid #e2e4e7;
        }
        .nuke-cache-card .button {
            width: 100%;
            text-align: center;
            margin-top: 16px;
            padding: 8px 16px;
            height: auto;
            line-height: 1.4;
        }
        @media screen and (max-width: 782px) {
            .nuke-cache-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            .nuke-cache-card {
                margin-bottom: 0;
            }
            .nuke-cache-size {
                font-size: 28px;
            }
        }
    </style>
    <?php
}

function cache_folder_scanner_menu() {
    $icon_url = plugins_url('Mon.png', __FILE__);
    add_menu_page('Nuke Cache', 'Cache Nuker', 'manage_options', 'cache-folder-scanner', 'cache_folder_scanner_page', $icon_url, 30);
}

function cache_folder_scanner_page() {
    // Define cache directories
    $cache_dir = WP_CONTENT_DIR . '/cache';
    $et_cache_dir = WP_CONTENT_DIR . '/et-cache';

    // Initialize cache sizes
    $cache_size = is_dir($cache_dir) ? folder_size($cache_dir) : 0;
    $et_cache_size = is_dir($et_cache_dir) ? folder_size($et_cache_dir) : 0;

    // Handle form submissions with nonce verification
    if (isset($_POST['empty_cache']) && isset($_POST['nuke_cache_nonce']) && wp_verify_nonce($_POST['nuke_cache_nonce'], 'empty_cache_action')) {
        delete_folder($cache_dir);
        echo '<div class="updated"><p>' . esc_html__('Cache folder emptied.', 'nuke cache') . '</p></div>';
        // Refresh the cache size after deletion
        $cache_size = is_dir($cache_dir) ? folder_size($cache_dir) : 0;
    }

    if (isset($_POST['empty_et_cache']) && isset($_POST['nuke_cache_nonce']) && wp_verify_nonce($_POST['nuke_cache_nonce'], 'empty_et_cache_action')) {
        delete_folder($et_cache_dir);
        echo '<div class="updated"><p>' . esc_html__('Et-cache folder emptied.', 'nuke cache') . '</p></div>';
        // Refresh the et-cache size after deletion
        $et_cache_size = is_dir($et_cache_dir) ? folder_size($et_cache_dir) : 0;
    }
    ?>
    <div class="wrap nuke-cache-container">
        <h1><?php echo esc_html__('Cache Nuker', 'nuke cache'); ?></h1>
        
        <div class="nuke-cache-grid">
            <!-- WordPress Cache Card -->
            <div class="nuke-cache-card">
                <div class="nuke-card-header">
                    <span class="dashicons dashicons-performance"></span>
                    <h2><?php echo esc_html__('WordPress Cache', 'nuke cache'); ?></h2>
                </div>
                <div class="nuke-card-content">
                    <?php if ($cache_size > 0): ?>
                        <div class="nuke-cache-size">
                            <?php echo esc_html(size_format($cache_size)); ?>
                        </div>
                        <div class="nuke-cache-label">
                            <?php echo esc_html__('Total Cache Size', 'nuke cache'); ?>
                        </div>
                        <div class="nuke-cache-status found">
                            <?php echo esc_html__('Cache folder found and ready to be cleared.', 'nuke cache'); ?>
                        </div>
                        <form method="post">
                            <?php wp_nonce_field('empty_cache_action', 'nuke_cache_nonce'); ?>
                            <input type="submit" name="empty_cache" class="button button-primary" value="<?php echo esc_attr__('Empty Cache Folder', 'nuke cache'); ?>" />
                        </form>
                    <?php else: ?>
                        <div class="nuke-cache-size">0 MB</div>
                        <div class="nuke-cache-label">
                            <?php echo esc_html__('Total Cache Size', 'nuke cache'); ?>
                        </div>
                        <div class="nuke-cache-status empty">
                            <?php echo esc_html__('No Cache folder found.', 'nuke cache'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Divi Cache Card -->
            <div class="nuke-cache-card">
                <div class="nuke-card-header">
                    <span class="dashicons dashicons-layout"></span>
                    <h2><?php echo esc_html__('Divi Cache', 'nuke cache'); ?></h2>
                </div>
                <div class="nuke-card-content">
                    <?php if ($et_cache_size > 0): ?>
                        <div class="nuke-cache-size">
                            <?php echo esc_html(size_format($et_cache_size)); ?>
                        </div>
                        <div class="nuke-cache-label">
                            <?php echo esc_html__('Total Cache Size', 'nuke cache'); ?>
                        </div>
                        <div class="nuke-cache-status found">
                            <?php echo esc_html__('Divi cache folder found and ready to be cleared.', 'nuke cache'); ?>
                        </div>
                        <form method="post">
                            <?php wp_nonce_field('empty_et_cache_action', 'nuke_cache_nonce'); ?>
                            <input type="submit" name="empty_et_cache" class="button button-primary" value="<?php echo esc_attr__('Empty Et-cache Folder', 'nuke cache'); ?>" />
                        </form>
                    <?php else: ?>
                        <div class="nuke-cache-size">0 MB</div>
                        <div class="nuke-cache-label">
                            <?php echo esc_html__('Total Cache Size', 'nuke cache'); ?>
                        </div>
                        <div class="nuke-cache-status empty">
                            <?php echo esc_html__('No Divi cache folder found.', 'nuke cache'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
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
