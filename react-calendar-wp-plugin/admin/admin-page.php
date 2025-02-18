<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$table_name = $wpdb->prefix . 'calendar_events';

// Define a base URL
define('BASE_URL', 'https://artisnotdead.it/'); // Change this to your desired base URL

// Initialize an error message variable
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_event'])) {
        $link = sanitize_text_field($_POST['event_link']);
        
        // Ensure the link is an anchor link
        if (preg_match('/^#/', $link)) {
            // Store the link directly as it is an anchor link
            $link = ltrim($link, '/'); // Optional: Remove leading slash if present
        } else {
            // Set an error message if the link is not an anchor link
            $error_message = 'Please enter a valid anchor link (e.g., #section).';
            $link = ''; // Optionally reset the link
        }

        // Only insert if there is no error
        if (empty($error_message)) {
            $wpdb->insert(
                $table_name,
                array(
                    'name' => sanitize_text_field($_POST['event_name']),
                    'date' => sanitize_text_field($_POST['event_date']),
                    'link' => esc_url_raw($link) // Store the anchor link directly
                ),
                array('%s', '%s', '%s')
            );
        }
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

    <?php if (!empty($error_message)): ?>
        <div class="notice notice-error">
            <p><?php echo esc_html($error_message); ?></p>
        </div>
    <?php endif; ?>

    <style>
        /* Style for input fields */
        .regular-text {
            width: 100%; /* Full width */
            padding: 8px; /* Padding for comfort */
            border: 1px solid #ccc; /* Light border */
            border-radius: 4px; /* Rounded corners */
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1); /* Subtle shadow */
            font-size: 14px; /* Font size */
            transition: border-color 0.2s; /* Smooth transition for border color */
        }

        .regular-text:focus {
            border-color: #0073aa; /* Change border color on focus */
            outline: none; /* Remove default outline */
            box-shadow: 0 0 5px rgba(0, 115, 170, 0.5); /* Shadow on focus */
        }

        /* Style for buttons */
        .button {
            background-color: #0073aa; /* WordPress primary color */
            color: white; /* Text color */
            padding: 10px 15px; /* Padding */
            border: none; /* No border */
            border-radius: 4px; /* Rounded corners */
            cursor: pointer; /* Pointer cursor */
            transition: background-color 0.2s; /* Smooth transition */
        }

        .button:hover {
            background-color: #005177; /* Darker shade on hover */
        }
    </style>

    <!-- Add/Edit Event Form -->
    <h2><?php echo isset($_GET['edit_id']) ? 'Edit Event' : 'Add New Event'; ?></h2>
    <form method="post" action="">
        <input type="hidden" name="event_id" value="<?php echo isset($_GET['edit_id']) ? intval($_GET['edit_id']) : ''; ?>">
        <table class="form-table">
            <tr>
                <th><label for="event_name">Event Name</label></th>
                <td><input type="text" name="event_name" id="event_name" value="<?php echo isset($event) ? esc_attr($event->name) : ''; ?>" required></td>
            </tr>
            <tr>
                <th><label for="event_date">Date</label></th>
                <td><input type="datetime-local" name="event_date" id="event_date" value="<?php echo isset($event) ? esc_attr($event->date) : ''; ?>" required></td>
            </tr>
            <tr>
                <th><label for="event_link">Link (Anchor Link Only)</label></th>
                <td>
                    <input type="text" name="event_link" id="event_link" value="<?php echo isset($event) ? esc_url($event->link) : ''; ?>" required>
                    <p class="description">Please enter an anchor link (e.g., #section). The base URL will not be added.</p>
                </td>
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
                <td class="event-link">
                    <a href="<?php echo esc_url(home_url($event->link)); ?>">View</a>
                </td>
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
        linkInput.value = linkCell.querySelector('a').href.replace(BASE_URL, ''); // Remove base URL for editing
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
            formData.append('event_link', linkInput.value.startsWith('#') ? linkInput.value : BASE_URL + linkInput.value); // Ensure it starts with #
            
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