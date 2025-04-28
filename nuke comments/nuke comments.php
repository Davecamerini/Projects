<?php
/**
 * Plugin Name: Nuke Comments
 * Description: A simple plugin to nuke the wp_comments table.
 * Version: 1.0.0
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * Author: Davecamerini
 * Author URI: https://www.davecamerini.com
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: nuke-comments
 * Domain Path: /languages
 */

// Hook to add a menu item in the admin dashboard
add_action('admin_menu', 'empty_comments_menu');
add_action('admin_enqueue_scripts', 'nuke_comments_admin_styles');

function nuke_comments_admin_styles($hook) {
    if ('toplevel_page_nuke-comments' !== $hook) {
        return;
    }
    ?>
    <style>
        .wrap {
            margin: 20px;
        }
        .wrap h1 {
            margin-bottom: 30px;
            color: #1d2327;
            font-size: 24px;
        }
        .nuke-comments-grid {
            display: grid;
            grid-template-columns: 600px;
            gap: 24px;
            margin-top: 20px;
        }
        .nuke-comments-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .nuke-comments-container h1 {
            margin-bottom: 30px;
            color: #1d2327;
            font-size: 24px;
        }
        .nuke-comments-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid #e2e4e7;
        }
        .nuke-comments-card:hover {
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
        .nuke-comments-count {
            font-size: 32px;
            font-weight: 600;
            color: #2271b1;
            line-height: 1.2;
            margin-bottom: 8px;
            text-align: center;
        }
        .nuke-comments-label {
            font-size: 14px;
            color: #646970;
            font-weight: 500;
            text-align: center;
            margin-bottom: 20px;
        }
        .nuke-comments-status {
            padding: 12px;
            border-radius: 6px;
            margin: 16px 0;
            text-align: center;
            font-size: 14px;
            font-weight: 500;
        }
        .nuke-comments-status.found {
            background: #f0f6fc;
            color: #2271b1;
            border: 1px solid #c5d9ed;
        }
        .nuke-comments-status.empty {
            background: #f0f6fc;
            color: #646970;
            border: 1px solid #e2e4e7;
        }
        .nuke-comments-card .button {
            width: 100%;
            text-align: center;
            margin-top: 16px;
            padding: 8px 16px;
            height: auto;
            line-height: 1.4;
        }
        @media screen and (max-width: 782px) {
            .nuke-comments-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            .nuke-comments-card {
                margin-bottom: 0;
            }
            .nuke-comments-count {
                font-size: 28px;
            }
        }
    </style>
    <?php
}

function empty_comments_menu() {
    $icon_url = plugins_url('Mon white trasp.png', __FILE__);
    add_menu_page('Nuke Comments', 'Nuke Comments', 'manage_options', 'nuke-comments', 'nuke_comments_page', $icon_url, 30);
}

function nuke_comments_page() {
    global $wpdb;

    // Get the total number of comments
    $total_comments = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments");

    if (isset($_POST['nuke_comments']) && isset($_POST['nuke_comments_nonce']) && wp_verify_nonce($_POST['nuke_comments_nonce'], 'nuke_comments_action')) {
        $wpdb->query("TRUNCATE TABLE $wpdb->comments");
        echo '<div class="updated"><p>' . esc_html__('All comments have been nuked.', 'nuke-comments') . '</p></div>';
        // Refresh the total number of comments after nuking
        $total_comments = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments");
    }
    ?>
    <div class="wrap nuke-comments-container">
        <h1><?php echo esc_html__('Nuke Comments', 'nuke-comments'); ?></h1>
        
        <div class="nuke-comments-grid">
            <div class="nuke-comments-card">
                <div class="nuke-card-header">
                    <span class="dashicons dashicons-admin-comments"></span>
                    <h2><?php echo esc_html__('Comments Management', 'nuke-comments'); ?></h2>
                </div>
                <div class="nuke-card-content">
                    <div class="nuke-comments-count">
                        <?php echo esc_html($total_comments); ?>
                    </div>
                    <div class="nuke-comments-label">
                        <?php echo esc_html__('Total Comments', 'nuke-comments'); ?>
                    </div>
                    <?php if ($total_comments > 0): ?>
                        <div class="nuke-comments-status found">
                            <?php echo esc_html__('Comments found and ready to be cleared.', 'nuke-comments'); ?>
                        </div>
                        <form method="post">
                            <?php wp_nonce_field('nuke_comments_action', 'nuke_comments_nonce'); ?>
                            <input type="submit" name="nuke_comments" class="button button-primary" value="<?php echo esc_attr__('Nuke Comments', 'nuke-comments'); ?>" onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete all comments?', 'nuke-comments')); ?>');" />
                        </form>
                    <?php else: ?>
                        <div class="nuke-comments-status empty">
                            <?php echo esc_html__('No comments found.', 'nuke-comments'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}
