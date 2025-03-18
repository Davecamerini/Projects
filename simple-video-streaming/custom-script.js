jQuery(document).ready(function($) {
    // Media overlay functionality
    const overlay = $('#vsp-video-overlay');
    const overlayVideo = $('#vsp-overlay-video');
    const overlayImage = $('#vsp-overlay-image');
    const closeOverlay = $('.vsp-close-overlay');

    $('.vsp-video-link').on('click', function(e) {
        e.preventDefault();
        const videoUrl = $(this).data('video');
        overlayVideo.attr('src', videoUrl).show();
        overlayImage.hide();
        overlay.addClass('active');
    });

    $('.vsp-image-link').on('click', function(e) {
        e.preventDefault();
        const imageUrl = $(this).data('image');
        overlayImage.attr('src', imageUrl).show();
        overlayVideo.hide();
        overlay.addClass('active');
    });

    closeOverlay.on('click', function() {
        overlay.removeClass('active');
        overlayVideo.attr('src', '').hide();
        overlayImage.attr('src', '').hide();
    });

    overlay.on('click', function(e) {
        if (e.target === this) {
            overlay.removeClass('active');
            overlayVideo.attr('src', '').hide();
            overlayImage.attr('src', '').hide();
        }
    });

    // Handle delete video
    $('.vsp-delete-video').on('click', function() {
        const videoName = $(this).data('video-name');
        const currentFolder = new URLSearchParams(window.location.search).get('folder') || '';
        
        if (confirm('Are you sure you want to delete this file?')) {
            $.post(ajaxurl, {
                action: 'delete_video',
                video_name: videoName,
                folder: currentFolder
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
        const currentFolder = new URLSearchParams(window.location.search).get('folder') || '';
        const newName = prompt('Enter new name for the file:', oldName);
        
        if (newName && newName !== oldName) {
            $.post(ajaxurl, {
                action: 'rename_video',
                old_name: oldName,
                new_name: newName,
                folder: currentFolder
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

    // Handle folder item clicks
    $('.folder-item').on('click', function(e) {
        e.preventDefault();
        const path = $(this).data('path');
        window.location.href = '?folder=' + encodeURIComponent(path);
    });

    // Handle toggle icon clicks
    $('.toggle-icon').on('click', function(e) {
        e.stopPropagation();
        const subfolders = $(this).closest('li').find('.subfolders');
        const toggleIcon = $(this);
        
        if (subfolders.length) {
            subfolders.slideToggle(200);
            toggleIcon.toggleClass('open');
        }
    });

    // Open active folder's parent folders
    $('.folder-item.active').each(function() {
        $(this).closest('li').find('.subfolders').show();
        $(this).closest('li').find('.toggle-icon').addClass('open');
    });
});
