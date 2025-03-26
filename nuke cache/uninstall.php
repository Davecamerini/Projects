<?php
// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Check if user has the required capabilities
if (!current_user_can('activate_plugins')) {
    return;
}

// Verify nonce if it exists
if (isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'bulk-plugins')) {
    // The plugin doesn't create any database tables or options,
    // so there's nothing to clean up here.
    // This file exists to follow WordPress best practices
    // and to ensure proper uninstallation through WordPress admin.
} 