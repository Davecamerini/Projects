<?php
/*
Plugin Name: Simple Calendar Plugin
Plugin URI: https://yourwebsite.com/simple-calendar-plugin
Description: A simple calendar plugin with anchor links
Version: 1.0.0
Author: Your Name
License: GPL v2 or later
*/

if (!defined('ABSPATH')) {
    exit;
}

// Register Custom Post Type for Events
function scp_register_event_post_type() {
    $args = array(
        'public' => true,
        'label'  => 'Events',
        'supports' => array('title', 'editor', 'thumbnail'),
        'menu_icon' => 'dashicons-calendar-alt',
        'labels' => array(
            'name' => 'Events',
            'singular_name' => 'Event',
            'add_new' => 'Add New Event',
            'add_new_item' => 'Add New Event',
            'edit_item' => 'Edit Event',
            'new_item' => 'New Event',
            'view_item' => 'View Event',
            'search_items' => 'Search Events',
            'not_found' => 'No events found',
            'not_found_in_trash' => 'No events found in Trash'
        )
    );
    register_post_type('event', $args);
}
add_action('init', 'scp_register_event_post_type');

// Add Meta Box for Event Details
function scp_add_event_meta_boxes() {
    add_meta_box(
        'event_details',
        'Event Details',
        'scp_event_details_callback',
        'event',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'scp_add_event_meta_boxes');

// Meta Box Callback
function scp_event_details_callback($post) {
    wp_nonce_field('scp_event_details', 'scp_event_details_nonce');
    $event_date = get_post_meta($post->ID, '_event_date', true);
    ?>
    <p>
        <label for="event_date">Event Date:</label>
        <input type="date" id="event_date" name="event_date" value="<?php echo esc_attr($event_date); ?>" required>
    </p>
    <?php
}

// Save Meta Box Data
function scp_save_event_meta($post_id) {
    if (!isset($_POST['scp_event_details_nonce'])) {
        return;
    }
    if (!wp_verify_nonce($_POST['scp_event_details_nonce'], 'scp_event_details')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (isset($_POST['event_date'])) {
        update_post_meta($post_id, '_event_date', sanitize_text_field($_POST['event_date']));
    }
}
add_action('save_post', 'scp_save_event_meta');

// Create Shortcode for Calendar Display
function scp_calendar_shortcode($atts) {
    $events = get_posts(array(
        'post_type' => 'event',
        'posts_per_page' => -1,
        'orderby' => 'meta_value',
        'meta_key' => '_event_date',
        'order' => 'ASC'
    ));

    if (empty($events)) {
        return '<p>No events found.</p>';
    }

    // Calendar Links Section
    $output = '<div class="scp-calendar-links">';
    foreach ($events as $event) {
        $event_date = get_post_meta($event->ID, '_event_date', true);
        $event_slug = 'event-' . $event->ID;
        
        $formatted_date = date('F j, Y', strtotime($event_date));
        
        $output .= sprintf(
            '<div class="calendar-link">
                <span class="event-date">%s</span>
                <a href="#%s">%s</a>
            </div>',
            esc_html($formatted_date),
            esc_attr($event_slug),
            esc_html($event->post_title)
        );
    }
    $output .= '</div>';

    // Event Details Section
    $output .= '<div class="scp-calendar-details">';
    foreach ($events as $event) {
        $event_date = get_post_meta($event->ID, '_event_date', true);
        $event_slug = 'event-' . $event->ID;
        $formatted_date = date('F j, Y', strtotime($event_date));
        
        $output .= sprintf(
            '<div id="%s" class="event-detail">
                <h3>%s</h3>
                <p class="event-date">Date: %s</p>
                <div class="event-content">%s</div>
                <a href="#top" class="back-to-top">Back to top</a>
            </div>',
            esc_attr($event_slug),
            esc_html($event->post_title),
            esc_html($formatted_date),
            wp_kses_post($event->post_content)
        );
    }
    $output .= '</div>';
    
    return $output;
}
add_shortcode('simple_calendar', 'scp_calendar_shortcode');

// Add Styles
function scp_add_styles() {
    ?>
    <style>
        .scp-calendar-links {
            max-width: 800px;
            margin: 20px auto;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 4px;
            background: #fff;
        }

        .calendar-link {
            margin-bottom: 10px;
            padding: 5px 0;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #eee;
        }

        .calendar-link:last-child {
            border-bottom: none;
        }

        .calendar-link .event-date {
            min-width: 150px;
            margin-right: 15px;
            color: #666;
            font-weight: 500;
        }

        .calendar-link a {
            text-decoration: none;
            color: #0073aa;
            font-weight: 500;
        }

        .calendar-link a:hover {
            text-decoration: underline;
            color: #004d73;
        }

        .scp-calendar-details {
            max-width: 800px;
            margin: 20px auto;
        }

        .event-detail {
            border: 1px solid #ddd;
            padding: 25px;
            margin-bottom: 20px;
            border-radius: 4px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .event-detail h3 {
            margin-top: 0;
            color: #333;
            font-size: 1.5em;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }

        .event-date {
            color: #666;
            font-style: italic;
            margin: 10px 0;
        }

        .event-content {
            margin: 15px 0;
            line-height: 1.6;
        }

        .back-to-top {
            display: inline-block;
            margin-top: 15px;
            font-size: 0.9em;
            text-decoration: none;
            color: #666;
            background: #f5f5f5;
            padding: 5px 10px;
            border-radius: 3px;
        }

        .back-to-top:hover {
            background: #eee;
            color: #333;
        }
    </style>
    <?php
}
add_action('wp_head', 'scp_add_styles');