<?php
if (!defined('ABSPATH')) {
    die('Direct access not permitted');
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Suppliers List', 'wp-suppliers'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=wp-suppliers-new'); ?>" class="page-title-action">
        <?php _e('Add New', 'wp-suppliers'); ?>
    </a>
    
    <hr class="wp-header-end">
    
    <?php
    global $wpdb;
    $suppliers = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}suppliers ORDER BY company_name ASC");
    
    if ($suppliers): ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col"><?php _e('Company Name', 'wp-suppliers'); ?></th>
                    <th scope="col"><?php _e('Email', 'wp-suppliers'); ?></th>
                    <th scope="col"><?php _e('Phone', 'wp-suppliers'); ?></th>
                    <th scope="col"><?php _e('Address', 'wp-suppliers'); ?></th>
                    <th scope="col"><?php _e('Added', 'wp-suppliers'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($suppliers as $supplier): ?>
                    <tr>
                        <td>
                            <strong>
                                <a href="<?php echo admin_url('admin.php?page=wp-suppliers-new&id=' . $supplier->id); ?>" class="row-title">
                                    <?php echo esc_html($supplier->company_name); ?>
                                </a>
                            </strong>
                            <div class="row-actions">
                                <span class="edit">
                                    <a href="<?php echo admin_url('admin.php?page=wp-suppliers-new&id=' . $supplier->id); ?>">
                                        <?php _e('Edit', 'wp-suppliers'); ?>
                                    </a> |
                                </span>
                                <span class="delete">
                                    <a href="#" class="delete-supplier" data-id="<?php echo $supplier->id; ?>">
                                        <?php _e('Delete', 'wp-suppliers'); ?>
                                    </a>
                                </span>
                            </div>
                        </td>
                        <td><?php echo esc_html($supplier->email); ?></td>
                        <td><?php echo esc_html($supplier->phone); ?></td>
                        <td><?php echo esc_html($supplier->address); ?></td>
                        <td><?php echo mysql2date(get_option('date_format'), $supplier->created_at); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="no-items">
            <p><?php _e('No suppliers found.', 'wp-suppliers'); ?></p>
        </div>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    $('.delete-supplier').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm('<?php _e('Are you sure you want to delete this supplier?', 'wp-suppliers'); ?>')) {
            return;
        }
        
        var id = $(this).data('id');
        
        $.post(ajaxurl, {
            action: 'delete_supplier',
            id: id,
            nonce: '<?php echo wp_create_nonce('delete_supplier'); ?>'
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.data.message);
            }
        });
    });
});
</script> 