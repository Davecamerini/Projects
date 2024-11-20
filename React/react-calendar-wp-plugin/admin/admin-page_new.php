<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$table_name = $wpdb->prefix . 'calendar_events';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_event'])) {
        $wpdb->insert(
            $table_name,
            array(
                'name' => sanitize_text_field($_POST['event_name']),
                'date' => sanitize_text_field($_POST['event_date']),
                'link' => esc_url_raw($_POST['event_link'])
            ),
            array('%s', '%s', '%s')
        );
    }

    if (isset($_POST['delete_event'])) {
        $wpdb->delete(
            $table_name,
            array('id' => intval($_POST['event_id'])),
            array('%d')
        );
    }
}

// Get all events
$events = $wpdb->get_results("SELECT * FROM $table_name ORDER BY date ASC");
?>

<div class="wrap">
    <h1>Calendar Events</h1>

    <!-- Add Event Form -->
    <h2>Add New Event</h2>
    <form method="post" action="">
        <table class="form-table">
            <tr>
                <th><label for="event_name">Event Name</label></th>
                <td><input type="text" name="event_name" id="event_name" class="regular-text" required></td>
            </tr>
            <tr>
                <th><label for="event_date">Date</label></th>
                <td><input type="datetime-local" name="event_date" id="event_date" required></td>
            </tr>
            <tr>
                <th><label for="event_link">Link</label></th>
                <td><input type="url" name="event_link" id="event_link" class="regular-text" required></td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="add_event" class="button button-primary" value="Add Event">
        </p>
    </form>

    <!-- Events List -->
    <h2>Current Events</h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Event Name</th>
                <th>Date</th>
                <th>Link</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($events as $event): ?>
            <tr>
                <td><?php echo esc_html($event->name); ?></td>
                <td><?php echo esc_html($event->date); ?></td>
                <td><a href="<?php echo esc_url($event->link); ?>" target="_blank">View</a></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="event_id" value="<?php echo esc_attr($event->id); ?>">
                        <input type="submit" name="delete_event" class="button button-small" value="Delete" 
                               onclick="return confirm('Are you sure you want to delete this event?');">
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div> 