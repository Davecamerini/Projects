jQuery(document).ready(function($) {
    // Store original z-index values
    const originalZIndexes = {
        mainArea: $('#et-main-area').css('z-index'),
        topNav: $('#et-top-navigation').css('z-index'),
        mainHeader: $('#et-main-header').css('z-index'),
        boc: $('#et-boc').css('z-index')
    };

    // Media overlay functionality
    const overlay = $('#vsp-video-overlay');
    const overlayVideo = $('#vsp-overlay-video');
    const overlayImage = $('#vsp-overlay-image');
    const closeOverlay = $('.vsp-close-overlay');

    function removeZIndexes() {
        $('#et-main-area, #et-top-navigation, #et-main-header, #et-boc').css('z-index', '');
    }

    function restoreZIndexes() {
        $('#et-main-area').css('z-index', originalZIndexes.mainArea);
        $('#et-top-navigation').css('z-index', originalZIndexes.topNav);
        $('#et-main-header').css('z-index', originalZIndexes.mainHeader);
        $('#et-boc').css('z-index', originalZIndexes.boc);
    }

    $('.vsp-video-link').on('click', function(e) {
        e.preventDefault();
        const videoUrl = $(this).data('video');
        overlayVideo.attr('src', videoUrl).show();
        overlayImage.hide();
        overlay.addClass('active');
        removeZIndexes();
    });

    $('.vsp-image-link').on('click', function(e) {
        e.preventDefault();
        const imageUrl = $(this).data('image');
        overlayImage.attr('src', imageUrl).show();
        overlayVideo.hide();
        overlay.addClass('active');
        removeZIndexes();
    });

    closeOverlay.on('click', function() {
        overlay.removeClass('active');
        overlayVideo.attr('src', '').hide();
        overlayImage.attr('src', '').hide();
        restoreZIndexes();
    });

    overlay.on('click', function(e) {
        if (e.target === this) {
            overlay.removeClass('active');
            overlayVideo.attr('src', '').hide();
            overlayImage.attr('src', '').hide();
            restoreZIndexes();
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

    // Mobile Tree View Collapse
    const treeView = $('.vsp-tree-view');
    if (treeView.length) {
        // Add collapsed class by default on mobile
        if (window.innerWidth <= 768) {
            treeView.addClass('collapsed');
        }

        // Add mobile collapse functionality
        treeView.on('click', function(e) {
            // Only trigger on mobile
            if (window.innerWidth <= 768) {
                // Don't trigger if clicking on folder items or their children
                if (!$(e.target).closest('.folder-item').length) {
                    $(this).toggleClass('collapsed');
                }
            }
        });
    }
});
