<?php
if (!defined('ABSPATH')) {
    die('Direct access not permitted');
}

global $wpdb;
$supplier = null;

if (isset($_GET['id'])) {
    $id = absint($_GET['id']);
    $supplier = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}suppliers WHERE id = %d",
        $id
    ));
}

$is_edit = !empty($supplier);
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php echo $is_edit ? __('Edit Supplier', 'wp-suppliers') : __('Add New Supplier', 'wp-suppliers'); ?>
    </h1>
    
    <hr class="wp-header-end">
    
    <form method="post" id="supplier-form">
        <?php wp_nonce_field('save_supplier', 'supplier_nonce'); ?>
        
        <?php if ($is_edit): ?>
            <input type="hidden" name="id" value="<?php echo $supplier->id; ?>">
        <?php endif; ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="company_name"><?php _e('Company Name', 'wp-suppliers'); ?> *</label>
                </th>
                <td>
                    <input type="text" 
                           name="company_name" 
                           id="company_name" 
                           value="<?php echo $is_edit ? esc_attr($supplier->company_name) : ''; ?>" 
                           class="regular-text" 
                           required>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="description"><?php _e('Description', 'wp-suppliers'); ?></label>
                </th>
                <td>
                    <?php 
                    wp_editor(
                        $is_edit ? $supplier->description : '',
                        'description',
                        [
                            'textarea_name' => 'description',
                            'textarea_rows' => 10,
                            'media_buttons' => true
                        ]
                    ); 
                    ?>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="email"><?php _e('Email', 'wp-suppliers'); ?></label>
                </th>
                <td>
                    <input type="email" 
                           name="email" 
                           id="email" 
                           value="<?php echo $is_edit ? esc_attr($supplier->email) : ''; ?>" 
                           class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="phone"><?php _e('Phone', 'wp-suppliers'); ?></label>
                </th>
                <td>
                    <input type="tel" 
                           name="phone" 
                           id="phone" 
                           value="<?php echo $is_edit ? esc_attr($supplier->phone) : ''; ?>" 
                           class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="website"><?php _e('Website', 'wp-suppliers'); ?></label>
                </th>
                <td>
                    <input type="url" 
                           name="website" 
                           id="website" 
                           value="<?php echo $is_edit ? esc_attr($supplier->website) : ''; ?>" 
                           class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="address"><?php _e('Address', 'wp-suppliers'); ?></label>
                </th>
                <td>
                    <input type="text" 
                           name="address" 
                           id="address" 
                           value="<?php echo $is_edit ? esc_attr($supplier->address) : ''; ?>" 
                           class="regular-text">
                    <p>
                        <button type="button" class="button" id="get-coordinates">
                            <?php _e('Get Coordinates', 'wp-suppliers'); ?>
                        </button>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label><?php _e('Coordinates', 'wp-suppliers'); ?></label>
                </th>
                <td>
                    <input type="text" 
                           name="latitude" 
                           id="latitude" 
                           value="<?php echo $is_edit ? esc_attr($supplier->latitude) : ''; ?>" 
                           class="small-text" 
                           placeholder="<?php _e('Latitude', 'wp-suppliers'); ?>">
                    
                    <input type="text" 
                           name="longitude" 
                           id="longitude" 
                           value="<?php echo $is_edit ? esc_attr($supplier->longitude) : ''; ?>" 
                           class="small-text" 
                           placeholder="<?php _e('Longitude', 'wp-suppliers'); ?>">
                </td>
            </tr>
        </table>
        
        <h2><?php _e('Gallery', 'wp-suppliers'); ?></h2>
        <div id="supplier-gallery" class="supplier-gallery">
            <?php
            if ($is_edit) {
                $gallery_items = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}supplier_gallery WHERE supplier_id = %d ORDER BY sort_order ASC",
                    $supplier->id
                ));
            }
            ?>
            
            <div class="gallery-items" style="display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 20px;">
                <?php if ($is_edit && $gallery_items): ?>
                    <?php foreach ($gallery_items as $item): ?>
                        <div class="gallery-item" data-id="<?php echo $item->id; ?>" style="position: relative;">
                            <img src="<?php echo esc_url($item->image_url); ?>" 
                                 alt="<?php echo esc_attr($item->title); ?>"
                                 style="width: 150px; height: 150px; object-fit: cover;">
                            <button type="button" class="remove-image button" style="position: absolute; top: 5px; right: 5px;">
                                <span class="dashicons dashicons-no"></span>
                            </button>
                            <input type="hidden" name="gallery_items[]" value="<?php echo $item->id; ?>">
                            <input type="text" 
                                   name="gallery_titles[]" 
                                   value="<?php echo esc_attr($item->title); ?>" 
                                   placeholder="<?php _e('Image title', 'wp-suppliers'); ?>"
                                   style="width: 150px; margin-top: 5px;">
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="add-images">
                <button type="button" class="button button-secondary" id="add-images">
                    <?php _e('Add Images', 'wp-suppliers'); ?>
                </button>
            </div>
        </div>
        
        <p class="submit">
            <input type="submit" 
                   name="submit" 
                   id="submit" 
                   class="button button-primary" 
                   value="<?php echo $is_edit ? __('Update Supplier', 'wp-suppliers') : __('Add Supplier', 'wp-suppliers'); ?>">
        </p>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Get coordinates from address
    $('#get-coordinates').on('click', function() {
        var address = $('#address').val();
        if (!address) {
            alert('<?php _e('Please enter an address first.', 'wp-suppliers'); ?>');
            return;
        }
        
        var apiKey = '<?php echo esc_js(get_option('wp_suppliers_google_maps_api_key')); ?>';
        if (!apiKey) {
            alert('<?php _e('Please set your Google Maps API Key in the settings.', 'wp-suppliers'); ?>');
            return;
        }
        
        $.get('https://maps.googleapis.com/maps/api/geocode/json', {
            address: address,
            key: apiKey
        }, function(response) {
            if (response.status === 'OK') {
                var location = response.results[0].geometry.location;
                $('#latitude').val(location.lat);
                $('#longitude').val(location.lng);
            } else {
                alert('<?php _e('Could not find coordinates for this address.', 'wp-suppliers'); ?>');
            }
        });
    });
    
    // Gallery management
    var mediaUploader;
    
    $('#add-images').on('click', function(e) {
        e.preventDefault();
        
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        
        mediaUploader = wp.media({
            title: '<?php _e('Select Images', 'wp-suppliers'); ?>',
            button: {
                text: '<?php _e('Add to Gallery', 'wp-suppliers'); ?>'
            },
            multiple: true,
            library: {
                type: 'image'
            }
        });
        
        mediaUploader.on('select', function() {
            var attachments = mediaUploader.state().get('selection').toJSON();
            var galleryContainer = $('.gallery-items');
            
            attachments.forEach(function(attachment) {
                var item = $('<div class="gallery-item" style="position: relative;">' +
                    '<img src="' + attachment.url + '" style="width: 150px; height: 150px; object-fit: cover;">' +
                    '<button type="button" class="remove-image button" style="position: absolute; top: 5px; right: 5px;">' +
                    '<span class="dashicons dashicons-no"></span></button>' +
                    '<input type="hidden" name="new_gallery_images[]" value="' + attachment.url + '">' +
                    '<input type="text" name="new_gallery_titles[]" placeholder="<?php _e('Image title', 'wp-suppliers'); ?>" ' +
                    'style="width: 150px; margin-top: 5px;"></div>');
                
                galleryContainer.append(item);
            });
        });
        
        mediaUploader.open();
    });
    
    // Remove gallery image
    $(document).on('click', '.remove-image', function() {
        $(this).closest('.gallery-item').remove();
    });
    
    // Make gallery sortable
    $('.gallery-items').sortable({
        items: '.gallery-item',
        cursor: 'move',
        opacity: 0.6
    });
    
    // Modify form submission to handle gallery
    var originalFormSubmit = $('#supplier-form').data('events').submit[0].handler;
    $('#supplier-form').off('submit').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var submitButton = $('#submit');
        
        submitButton.prop('disabled', true);
        
        // Get form data including gallery items
        var formData = new FormData(this);
        
        // Add existing gallery items order
        $('.gallery-item').each(function(index) {
            formData.append('gallery_order[]', $(this).data('id') || '');
        });
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                submitButton.prop('disabled', false);
                
                if (response.success) {
                    window.location.href = '<?php echo admin_url('admin.php?page=wp-suppliers&message=1'); ?>';
                } else {
                    alert(response.data.message || '<?php _e('Error saving supplier.', 'wp-suppliers'); ?>');
                }
            },
            error: function() {
                submitButton.prop('disabled', false);
                alert('<?php _e('Error saving supplier.', 'wp-suppliers'); ?>');
            }
        });
    });
});
</script> 