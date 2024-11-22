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

    if (isset($_POST['edit_event'])) {
        $wpdb->update(
            $table_name,
            array(
                'name' => sanitize_text_field($_POST['event_name']),
                'date' => sanitize_text_field($_POST['event_date']),
                'link' => esc_url_raw($_POST['event_link'])
            ),
            array('id' => intval($_POST['event_id'])),
            array('%s', '%s', '%s'),
            array('%d')
        );
    }
}

// Get all events
$events = $wpdb->get_results("SELECT * FROM $table_name ORDER BY date ASC");
?>

<div class="wrap">
    <h1>Calendar Events</h1>

    <!-- Add/Edit Event Form -->
    <h2><?php echo isset($_GET['edit_id']) ? 'Edit Event' : 'Add New Event'; ?></h2>
    <form method="post" action="">
        <input type="hidden" name="event_id" value="<?php echo isset($_GET['edit_id']) ? intval($_GET['edit_id']) : ''; ?>">
        <table class="form-table">
            <tr>
                <th><label for="event_name">Event Name</label></th>
                <td><input type="text" name="event_name" id="event_name" class="regular-text" value="<?php echo isset($event) ? esc_attr($event->name) : ''; ?>" required></td>
            </tr>
            <tr>
                <th><label for="event_date">Date</label></th>
                <td><input type="datetime-local" name="event_date" id="event_date" value="<?php echo isset($event) ? esc_attr($event->date) : ''; ?>" required></td>
            </tr>
            <tr>
                <th><label for="event_link">Link</label></th>
                <td><input type="url" name="event_link" id="event_link" class="regular-text" value="<?php echo isset($event) ? esc_url($event->link) : ''; ?>" required></td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="<?php echo isset($_GET['edit_id']) ? 'edit_event' : 'add_event'; ?>" class="button button-primary" value="<?php echo isset($_GET['edit_id']) ? 'Update Event' : 'Add Event'; ?>">
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
            <tr id="event-<?php echo esc_attr($event->id); ?>">
                <td class="event-name"><?php echo esc_html($event->name); ?></td>
                <td class="event-date"><?php echo esc_html($event->date); ?></td>
                <td class="event-link"><a href="<?php echo esc_url($event->link); ?>" target="_blank">View</a></td>
                <td>
                    <button class="button button-small edit-button" data-id="<?php echo esc_attr($event->id); ?>">Edit</button>
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

<script>
document.querySelectorAll('.edit-button').forEach(button => {
    button.addEventListener('click', function() {
        const row = this.closest('tr');
        const eventId = this.getAttribute('data-id');
        const nameCell = row.querySelector('.event-name');
        const dateCell = row.querySelector('.event-date');
        const linkCell = row.querySelector('.event-link');

        // Create input fields
        const nameInput = document.createElement('input');
        nameInput.type = 'text';
        nameInput.value = nameCell.textContent;
        nameInput.className = 'regular-text';

        const dateInput = document.createElement('input');
        dateInput.type = 'datetime-local';
        dateInput.value = dateCell.textContent;
        
        const linkInput = document.createElement('input');
        linkInput.type = 'url';
        linkInput.value = linkCell.querySelector('a').href;
        linkInput.className = 'regular-text';

        // Replace cells with input fields
        nameCell.innerHTML = '';
        nameCell.appendChild(nameInput);
        dateCell.innerHTML = '';
        dateCell.appendChild(dateInput);
        linkCell.innerHTML = '';
        linkCell.appendChild(linkInput);

        // Change the button to save
        this.textContent = 'Save';
        this.classList.remove('edit-button');
        this.classList.add('save-button');

        // Add save functionality
        this.addEventListener('click', function() {
            const formData = new FormData();
            formData.append('edit_event', true);
            formData.append('event_id', eventId);
            formData.append('event_name', nameInput.value);
            formData.append('event_date', dateInput.value);
            formData.append('event_link', linkInput.value);

            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                // Reload the page or update the row with new data
                location.reload(); // Reloading the page to see the changes
            });
        });
    });
});
</script> 