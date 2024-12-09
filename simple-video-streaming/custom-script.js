jQuery(document).ready(function($) {
    // Handle delete video
    $('.vsp-delete-video').on('click', function() {
        const videoName = $(this).data('video-name');
        if (confirm('Are you sure you want to delete this video?')) {
            $.post(ajaxurl, {
                action: 'delete_video',
                video_name: videoName
            }, function(response) {
                if (response.success) {
                    alert(response.data);
                    location.reload(); // Reload the page to see changes
                } else {
                    alert(response.data);
                }
            });
        }
    });

    // Handle rename video
    $('.vsp-rename-video').on('click', function() {
        const oldName = $(this).data('video-name');
        const newName = prompt('Enter new name for the video:', oldName);
        if (newName && newName !== oldName) {
            $.post(ajaxurl, {
                action: 'rename_video',
                old_name: oldName,
                new_name: newName
            }, function(response) {
                if (response.success) {
                    alert(response.data);
                    location.reload(); // Reload the page to see changes
                } else {
                    alert(response.data);
                }
            });
        }
    });
});
