<?php
/*
Plugin Name: Simple Calendar Plugin
Plugin URI: https://yourwebsite.com/simple-calendar-plugin
Description: A simple calendar plugin with monthly view and anchor links
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
    $event_time = get_post_meta($post->ID, '_event_time', true);
    $event_location = get_post_meta($post->ID, '_event_location', true);
    ?>
    <div class="event-meta-fields">
        <p>
            <label for="event_date">Event Date:</label>
            <input type="date" id="event_date" name="event_date" value="<?php echo esc_attr($event_date); ?>" required>
        </p>
        <p>
            <label for="event_time">Event Time:</label>
            <input type="time" id="event_time" name="event_time" value="<?php echo esc_attr($event_time); ?>">
        </p>
        <p>
            <label for="event_location">Location:</label>
            <input type="text" id="event_location" name="event_location" value="<?php echo esc_attr($event_location); ?>" style="width: 100%;">
        </p>
    </div>
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
    
    $fields = array('event_date', 'event_time', 'event_location');
    
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
        }
    }
}
add_action('save_post', 'scp_save_event_meta');

// Create Shortcode for Calendar Display
function scp_calendar_shortcode($atts) {
    // Get current month/year or from URL parameters
    $month = isset($_GET['month']) ? intval($_GET['month']) : current_time('n');
    $year = isset($_GET['year']) ? intval($_GET['year']) : current_time('Y');
    
    // Get all events for the current month
    $events = get_posts(array(
        'post_type' => 'event',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => '_event_date',
                'value' => array($year . '-' . sprintf('%02d', $month) . '-01', 
                               $year . '-' . sprintf('%02d', $month) . '-31'),
                'type' => 'DATE',
                'compare' => 'BETWEEN'
            )
        ),
        'orderby' => 'meta_value',
        'meta_key' => '_event_date',
        'order' => 'ASC'
    ));

    // Create events lookup array
    $events_lookup = array();
    foreach ($events as $event) {
        $event_date = get_post_meta($event->ID, '_event_date', true);
        $day = date('j', strtotime($event_date));
        if (!isset($events_lookup[$day])) {
            $events_lookup[$day] = array();
        }
        $events_lookup[$day][] = $event;
    }

    // Calendar navigation
    $output = '<div class="scp-calendar-nav">';
    $prev_month = $month - 1;
    $prev_year = $year;
    if ($prev_month < 1) {
        $prev_month = 12;
        $prev_year--;
    }
    
    $next_month = $month + 1;
    $next_year = $year;
    if ($next_month > 12) {
        $next_month = 1;
        $next_year++;
    }
    
    $output .= sprintf(
        '<a href="?month=%d&year=%d" class="calendar-nav">&laquo; %s</a>',
        $prev_month,
        $prev_year,
        date('F', mktime(0, 0, 0, $prev_month, 1))
    );
    
    $output .= '<span class="current-month">' . date('F Y', mktime(0, 0, 0, $month, 1, $year)) . '</span>';
    
    $output .= sprintf(
        '<a href="?month=%d&year=%d" class="calendar-nav">%s &raquo;</a>',
        $next_month,
        $next_year,
        date('F', mktime(0, 0, 0, $next_month, 1))
    );
    $output .= '</div>';

    // Calendar table
    $output .= '<table class="scp-calendar">';
    $output .= '<tr><th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th></tr>';

    $first_day = date('w', mktime(0, 0, 0, $month, 1, $year));
    $days_in_month = date('t', mktime(0, 0, 0, $month, 1, $year));
    
    $output .= '<tr>';
    for ($i = 0; $i < $first_day; $i++) {
        $output .= '<td class="empty"></td>';
    }

    $current_day = 1;
    $current_position = $first_day;

    while ($current_day <= $days_in_month) {
        if ($current_position % 7 == 0 && $current_day != 1) {
            $output .= '</tr><tr>';
        }

        $output .= '<td class="calendar-day">';
        $output .= '<span class="day-number">' . $current_day . '</span>';
        
        if (isset($events_lookup[$current_day])) {
            $output .= '<div class="day-events">';
            foreach ($events_lookup[$current_day] as $event) {
                $event_slug = 'event-' . $event->ID;
                $event_time = get_post_meta($event->ID, '_event_time', true);
                $time_display = $event_time ? date('g:i A', strtotime($event_time)) : '';
                
                $output .= sprintf(
                    '<a href="#%s" class="event-link" title="%s">%s%s</a>',
                    esc_attr($event_slug),
                    esc_attr($event->post_title . ($time_display ? ' at ' . $time_display : '')),
                    esc_html($event->post_title),
                    $time_display ? '<span class="event-time">' . esc_html($time_display) . '</span>' : ''
                );
            }
            $output .= '</div>';
        }
        
        $output .= '</td>';

        $current_day++;
        $current_position++;
    }

    while ($current_position % 7 != 0) {
        $output .= '<td class="empty"></td>';
        $current_position++;
    }

    $output .= '</tr></table>';

    // Event Details Section
    $output .= '<div class="scp-calendar-details">';
    foreach ($events as $event) {
        $event_date = get_post_meta($event->ID, '_event_date', true);
        $event_time = get_post_meta($event->ID, '_event_time', true);
        $event_location = get_post_meta($event->ID, '_event_location', true);
        $event_slug = 'event-' . $event->ID;
        
        $formatted_date = date('F j, Y', strtotime($event_date));
        $formatted_time = $event_time ? date('g:i A', strtotime($event_time)) : '';
        
        $output .= sprintf(
            '<div id="%s" class="event-detail">
                <h3>%s</h3>
                <div class="event-meta">
                    <p class="event-date">Date: %s</p>
                    %s
                    %s
                </div>
                <div class="event-content">%s</div>
                <a href="#top" class="back-to-top">Back to top</a>
            </div>',
            esc_attr($event_slug),
            esc_html($event->post_title),
            esc_html($formatted_date),
            $formatted_time ? '<p class="event-time">Time: ' . esc_html($formatted_time) . '</p>' : '',
            $event_location ? '<p class="event-location">Location: ' . esc_html($event_location) . '</p>' : '',
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
        .scp-calendar-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 4px;
            max-width: 800px;
            margin: 20px auto;
        }

        .calendar-nav {
            text-decoration: none;
            color: #0073aa;
            padding: 5px 10px;
            transition: all 0.3s ease;
        }

        .calendar-nav:hover {
            background: #e0e0e0;
            border-radius: 3px;
        }

        .current-month {
            font-weight: bold;
            font-size: 1.2em;
            color: #333;
        }

        .scp-calendar {
            width: 100%;
            max-width: 800px;
            margin: 0 auto 30px;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .scp-calendar th {
            background: #f8f9fa;
            padding: 12px;
            text-align: center;
            border: 1px solid #dee2e6;
            font-weight: 600;
            color: #495057;
        }

        .scp-calendar td {
            border: 1px solid #dee2e6;
            padding: 10px;
            height: 120px;
            vertical-align: top;
        }

        .calendar-day {
            position: relative;
        }

        .day-number {
            position: absolute;
            top: 5px;
            right: 5px;
            color: #666;
            font-size: 0.9em;
        }

        .day-events {
            margin-top: 25px;
        }

        .event-link {
            display: block;
            font-size: 0.85em;
            color: #0073aa;
            text-decoration: none;
            margin: 2px 0;
            padding: 4px 6px;
            background: #f0f7ff;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .event-link:hover {
            background: #e1f0ff;
            transform: translateX(2px);
        }

        .event-time {
            display: block;
            font-size: 0.8em;
            color: #666;
            margin-top: 2px;
        }

        .empty {
            background: #f9f9f9;
        }

        .scp-calendar-details {
            max-width: 800px;
            margin: 20px auto;
        }

        .event-detail {
            border: 1px solid #dee2e6;
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

        .event-meta {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }

        .event-meta p {
            margin: 5px 0;
            color: #666;
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
            transition: all 0.2s ease;
        }

        .back-to-top:hover {
            background: #e0e0e0;
            color: #333;
        }

        @media (max-width: 768px) {
            .scp-calendar td {
                height: auto;
                min-height: 80px;
            }

            .event-link {
                font-size: 0.8em;
            }

            .current-month {
                font-size: 1em;
            }
        }
    </style>
    <?php
}
add_action('wp_head', 'scp_add_styles');