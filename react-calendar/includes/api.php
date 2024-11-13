<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Register REST API endpoints
function rc_register_routes() {
    register_rest_route('calendar/v1', '/events', array(
        'methods' => 'GET',
        'callback' => 'rc_get_events',
        'permission_callback' => '__return_true'
    ));
}
add_action('rest_api_init', 'rc_register_routes');

// Get events callback
function rc_get_events() {
    // Example: Query events from a custom post type
    $args = array(
        'post_type' => 'event', // You'll need to register this custom post type
        'posts_per_page' => -1,
        'orderby' => 'meta_value',
        'meta_key' => 'event_date'
    );
    
    $events = array();
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $events[] = array(
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'date' => get_post_meta(get_the_ID(), 'event_date', true),
                'description' => get_the_excerpt()
            );
        }
        wp_reset_postdata();
    }
    
    return new WP_REST_Response($events, 200);
}

// Register custom post type for events
function rc_register_post_type() {
    register_post_type('event', array(
        'labels' => array(
            'name' => 'Events',
            'singular_name' => 'Event'
        ),
        'public' => true,
        'show_in_rest' => true,
        'supports' => array('title', 'editor', 'excerpt'),
        'menu_icon' => 'dashicons-calendar'
    ));
}
add_action('init', 'rc_register_post_type');

// Add meta box for event date
function rc_add_event_meta_box() {
    add_meta_box(
        'event_date',
        'Event Date',
        'rc_event_date_callback',
        'event',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'rc_add_event_meta_box');

// Meta box callback
function rc_event_date_callback($post) {
    $value = get_post_meta($post->ID, 'event_date', true);
    ?>
    <label for="event_date">Date:</label>
    <input type="date" id="event_date" name="event_date" value="<?php echo esc_attr($value); ?>">
    <?php
}

// Save meta box data
function rc_save_event_date($post_id) {
    if (array_key_exists('event_date', $_POST)) {
        update_post_meta(
            $post_id,
            'event_date',
            sanitize_text_field($_POST['event_date'])
        );
    }
}
add_action('save_post', 'rc_save_event_date');