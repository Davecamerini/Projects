jQuery(document).ready(function($) {
    // Add nonce for AJAX requests
    const vspNonce = '<?php echo wp_create_nonce("vsp_nonce"); ?>';

    // Add notification banner
    $('body').append(`
        <div class="vsp-notification">
            <span class="vsp-notification-icon">✓</span>
            <span class="vsp-notification-message">File moved successfully</span>
        </div>
    `);

    // Function to show notification
    function showNotification(message) {
        const $notification = $('.vsp-notification');
        $notification.find('.vsp-notification-message').text(message);
        $notification.addClass('show');
        
        setTimeout(() => {
            $notification.removeClass('show');
            setTimeout(() => {
                location.reload();
            }, 300);
        }, 3000);
    }

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
                    showNotification('File deleted successfully');
                } else {
                    showNotification('Error: ' + response.data);
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
                    showNotification('File renamed successfully');
                } else {
                    showNotification('Error: ' + response.data);
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

    // Add move overlay HTML
    $('body').append(`
        <div id="vsp-move-overlay" class="vsp-video-overlay">
            <div class="vsp-video-overlay-content">
                <span class="vsp-close-overlay">&times;</span>
                <h3>Move File</h3>
                <div class="vsp-move-content">
                    <p>Select destination folder:</p>
                    <div class="vsp-folder-tree-move"></div>
                    <div class="vsp-move-actions">
                        <button class="button cancel-move">Cancel</button>
                        <button class="button button-primary confirm-move">Move</button>
                    </div>
                </div>
            </div>
        </div>
    `);

    // Handle move button click
    $('.vsp-move-video').on('click', function() {
        const videoName = $(this).data('video-name');
        const currentFolder = new URLSearchParams(window.location.search).get('folder') || '';
        
        // Store the file info for the move operation
        $('#vsp-move-overlay').data('file-info', {
            name: videoName,
            currentFolder: currentFolder
        });

        // Render folder tree for move
        const $folderTree = $('#vsp-move-overlay .vsp-folder-tree-move');
        $folderTree.html('');
        
        // Add Root folder option
        $folderTree.append(`
            <div class="folder-item" data-path="">
                <span class="toggle-icon" style="visibility: hidden;"></span>
                <span class="folder-icon"></span>
                <span class="folder-name">Root</span>
            </div>
        `);

        // Function to recursively add folders
        function addFolders($parent, folders, currentPath = '') {
            folders.forEach(folder => {
                const path = currentPath ? `${currentPath}/${folder.name}` : folder.name;
                
                // Skip if it's the current folder
                if (path === currentFolder) {
                    return;
                }

                const $folderItem = $(`
                    <div class="folder-item" data-path="${path}">
                        <span class="toggle-icon" style="visibility: hidden;"></span>
                        <span class="folder-icon"></span>
                        <span class="folder-name">${folder.name}</span>
                    </div>
                `);

                $parent.append($folderItem);

                // If folder has subfolders, add them with indentation
                if (folder.subfolders && folder.subfolders.length > 0) {
                    const $subfolders = $('<div class="subfolders"></div>');
                    $folderItem.append($subfolders);
                    addFolders($subfolders, folder.subfolders, path);
                }
            });
        }

        // Get all folders from the main tree view
        const folders = [];
        $('.vsp-tree-view .folder-item').each(function() {
            const path = $(this).data('path');
            if (path && path !== currentFolder) {
                const parts = path.split('/');
                let currentLevel = folders;
                
                parts.forEach((part, index) => {
                    let folder = currentLevel.find(f => f.name === part);
                    if (!folder) {
                        folder = { name: part, subfolders: [] };
                        currentLevel.push(folder);
                    }
                    currentLevel = folder.subfolders;
                });
            }
        });

        // Add all folders to the move tree
        addFolders($folderTree, folders);

        // Show overlay
        $('#vsp-move-overlay').addClass('active');
    });

    // Handle click outside overlay to close
    $('#vsp-move-overlay').on('click', function(e) {
        if ($(e.target).is('#vsp-move-overlay')) {
            $(this).removeClass('active');
        }
    });

    // Handle folder selection in move overlay
    $('#vsp-move-overlay').on('click', '.folder-item', function(e) {
        e.stopPropagation(); // Prevent click from bubbling to overlay
        $('#vsp-move-overlay .folder-item').removeClass('active');
        $(this).addClass('active');
    });

    // Handle move confirmation
    $('#vsp-move-overlay .confirm-move').on('click', function(e) {
        e.stopPropagation(); // Prevent click from bubbling to overlay
        const $overlay = $('#vsp-move-overlay');
        const fileInfo = $overlay.data('file-info');
        const selectedFolder = $overlay.find('.folder-item.active').data('path');
        
        if (selectedFolder === undefined) {
            alert('Please select a destination folder');
            return;
        }

        const $button = $(this);
        $button.prop('disabled', true).text('Moving...');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'move_video',
                video_name: fileInfo.name,
                current_folder: fileInfo.currentFolder,
                destination_folder: selectedFolder,
                nonce: vspNonce
            },
            success: function(response) {
                if (response.success) {
                    $overlay.removeClass('active');
                    showNotification(response.data);
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                alert('Error moving file: ' + error);
            },
            complete: function() {
                $button.prop('disabled', false).text('Move');
            }
        });
    });

    // Handle move overlay close
    $('#vsp-move-overlay .vsp-close-overlay, #vsp-move-overlay .cancel-move').on('click', function(e) {
        e.stopPropagation(); // Prevent click from bubbling to overlay
        $('#vsp-move-overlay').removeClass('active');
    });
});
