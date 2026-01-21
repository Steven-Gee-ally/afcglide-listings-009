/**
 * AFCGlide Admin JavaScript
 * Version 4.1.0 - A+ Grade Production Ready
 * Handles all admin interface interactions with enhanced error handling
 */

jQuery(document).ready(function ($) {

    // ==========================================
    // 0. VERIFY DEPENDENCIES
    // ==========================================
    if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
        console.error('AFCGlide Error: WordPress Media Library not loaded');
        alert('⚠️ SYSTEM ERROR\n\nWordPress Media Library failed to load.\n\nPlease refresh the page and try again.');
        return;
    }

    // ==========================================
    // 0.1 CONSTANTS
    // ==========================================
    const MIN_AGENT_WIDTH = 800;
    const MIN_LISTING_WIDTH = 1200;
    const MAX_GALLERY = 16;

    // ==========================================
    // 1. DATA LOSS PREVENTION
    // ==========================================
    let formChanged = false;
    let lastRemovedImage = null; // For undo functionality

    $('input, textarea, select').on('change input', function () {
        formChanged = true;
    });

    $(window).on('beforeunload', function (e) {
        if (formChanged) {
            return 'You have unsaved changes. Are you sure you want to leave?';
        }
    });

    $('form#post').on('submit', function () {
        formChanged = false;
    });

    // ==========================================
    // 2. AGENT PHOTO UPLOAD (Single Select)
    // ==========================================
    $(document).on('click', '.afcglide-upload-image-btn', function (e) {
        e.preventDefault();

        const frame = wp.media({
            title: 'Select Professional Agent Photo',
            multiple: false,
            library: { type: 'image' }
        });

        frame.on('select', function () {
            const attachment = frame.state().get('selection').first().toJSON();

            // Enhanced validation with better messaging
            if (attachment.width < MIN_AGENT_WIDTH) {
                alert(
                    '⚠️ IMAGE TOO SMALL\n\n' +
                    'Agent photos require ' + MIN_AGENT_WIDTH + 'px width minimum for professional quality.\n\n' +
                    'Selected image: ' + attachment.width + 'px × ' + attachment.height + 'px\n\n' +
                    'Please choose a higher resolution image.'
                );
                return;
            }

            // Update preview and hidden field
            $('#agent_photo_id').val(attachment.id);
            $('#agent-photo-img').attr('src', attachment.url);

            formChanged = true;
            showSuccessNotice('✅ Agent photo updated successfully');
        });

        frame.on('close', function () {
            // Optional: Track if user canceled
            console.log('Agent photo selector closed');
        });

        frame.open();
    });

    // ==========================================
    // 2.2 PDF UPLOAD (Asset Intelligence)
    // ==========================================
    $(document).on('click', '.afc-pdf-upload-btn', function (e) {
        e.preventDefault();

        const frame = wp.media({
            title: 'Select Property Fact Sheet (PDF)',
            multiple: false,
            library: { type: 'application/pdf' }
        });

        frame.on('select', function () {
            const attachment = frame.state().get('selection').first().toJSON();

            if (attachment.mime !== 'application/pdf') {
                alert('⚠️ INVALID FILE TYPE\n\nPlease select a PDF document only.\n\nSelected: ' + attachment.mime);
                return;
            }

            // Check file size (10MB limit)
            const maxSize = 10 * 1024 * 1024; // 10MB in bytes
            if (attachment.filesizeInBytes > maxSize) {
                alert('⚠️ FILE TOO LARGE\n\nMaximum PDF size: 10MB\n\nYour file: ' + (attachment.filesizeInBytes / 1024 / 1024).toFixed(1) + 'MB');
                return;
            }

            $('#afc_pdf_id').val(attachment.id);
            $('#pdf-filename').text(attachment.filename);
            formChanged = true;
            showSuccessNotice('✅ PDF uploaded: ' + attachment.filename);
        });

        frame.open();
    });

    // ==========================================
    // 3. HERO IMAGE UPLOAD (Single Select)
    // ==========================================
    $(document).on('click', '.afc-upload-zone[data-type="hero"] .afc-upload-btn', function (e) {
        e.preventDefault();

        const $zone = $(this).closest('.afc-upload-zone');
        const $input = $zone.find('input[type="hidden"]');
        const $preview = $zone.find('.afc-preview-grid');

        const frame = wp.media({
            title: 'Select Hero Image (Minimum ' + MIN_LISTING_WIDTH + 'px)',
            multiple: false,
            library: { type: 'image' }
        });

        frame.on('select', function () {
            const attachment = frame.state().get('selection').first().toJSON();

            // Validate dimensions with detailed feedback
            if (attachment.width < MIN_LISTING_WIDTH) {
                alert(
                    '⚠️ IMAGE TOO SMALL\n\n' +
                    'Luxury listings require ' + MIN_LISTING_WIDTH + 'px width minimum.\n\n' +
                    'Selected image: ' + attachment.width + 'px × ' + attachment.height + 'px\n\n' +
                    'Recommended: At least 1920px × 1080px for best quality.'
                );
                return;
            }

            // Update hidden field
            $input.val(attachment.id);

            // Update preview with smooth transition
            $preview.fadeOut(200, function () {
                $preview.html(
                    '<div class="afc-preview-item" data-id="' + attachment.id + '">' +
                    '<img src="' + attachment.url + '" alt="Hero Image">' +
                    '<span class="afc-remove-img">×</span>' +
                    '</div>'
                ).fadeIn(200);
            });

            formChanged = true;
            showSuccessNotice('✅ Hero image set (' + attachment.width + 'px × ' + attachment.height + 'px)');
        });

        frame.open();
    });

    // ==========================================
    // 4. GALLERY UPLOAD (Multi-Select - 16 Photo Limit)
    // ==========================================
    $(document).on('click', '.afc-upload-zone[data-type="gallery"] .afc-upload-btn', function (e) {
        e.preventDefault();

        const $zone = $(this).closest('.afc-upload-zone');
        const limit = parseInt($zone.data('limit')) || MAX_GALLERY;
        const $input = $zone.find('input[name="_listing_gallery_ids"]');
        const $preview = $zone.find('.afc-preview-grid');

        // Get current IDs
        let currentIds = $input.val() ? $input.val().split(',').filter(Boolean) : [];
        const remaining = limit - currentIds.length;

        // Check if gallery is full
        if (remaining <= 0) {
            alert(
                '⚠️ GALLERY FULL\n\n' +
                'You have reached the maximum of ' + limit + ' photos.\n\n' +
                'Current photos: ' + currentIds.length + '\n\n' +
                'Remove some photos before adding more.'
            );
            return;
        }

        const frame = wp.media({
            title: 'Select Gallery Photos (' + remaining + ' slots remaining)',
            multiple: true,
            button: { text: 'Add to Gallery (' + remaining + ' available)' },
            library: { type: 'image' }
        });

        frame.on('select', function () {
            const selection = frame.state().get('selection');
            let addedCount = 0;
            let rejectedCount = 0;
            let rejectedFiles = [];

            selection.each(function (attachment) {
                attachment = attachment.toJSON();

                // Check limit
                if (currentIds.length >= limit) {
                    rejectedCount++;
                    rejectedFiles.push(attachment.filename + ' (limit reached)');
                    return;
                }

                // Check if already added
                if (currentIds.includes(attachment.id.toString())) {
                    rejectedCount++;
                    rejectedFiles.push(attachment.filename + ' (duplicate)');
                    return;
                }

                // Validate dimensions (The 1200px Quality Gatekeeper)
                if (attachment.width < MIN_LISTING_WIDTH) {
                    rejectedCount++;
                    rejectedFiles.push(attachment.filename + ' (' + attachment.width + 'px - too small)');
                    return;
                }

                // Add to array
                currentIds.push(attachment.id);
                addedCount++;

                // Add to preview grid with fade in
                const $newItem = $(
                    '<div class="afc-preview-item" data-id="' + attachment.id + '" style="display:none;">' +
                    '<img src="' + attachment.url + '" alt="Gallery Image">' +
                    '<span class="afc-remove-img">×</span>' +
                    '</div>'
                );

                $preview.append($newItem);
                $newItem.fadeIn(300);
            });

            // Update hidden field with comma-separated IDs
            $input.val(currentIds.join(','));

            // Refresh Sortable so agent can move new photos
            initSortable();

            formChanged = true;

            // Show summary message
            let message = '✅ ' + addedCount + ' photo(s) added to gallery';

            if (rejectedCount > 0) {
                message += '\n\n⚠️ ' + rejectedCount + ' photo(s) rejected:\n\n';
                message += rejectedFiles.slice(0, 5).join('\n');
                if (rejectedFiles.length > 5) {
                    message += '\n...and ' + (rejectedFiles.length - 5) + ' more';
                }
            }

            if (addedCount > 0 || rejectedCount > 0) {
                alert(message);
            }

            // Update button text with new count
            updateGalleryCount($zone, currentIds.length, limit);
        });

        frame.open();
    });

    // ==========================================
    // 4.1 UPDATE GALLERY COUNT DISPLAY
    // ==========================================
    function updateGalleryCount($zone, current, max) {
        const $btn = $zone.find('.afc-upload-btn');
        const remaining = max - current;

        if (remaining > 0) {
            $btn.text('Manage Luxury Gallery (' + remaining + ' slots available)');
        } else {
            $btn.text('Gallery Full (' + current + '/' + max + ')').css('opacity', '0.6');
        }
    }

    // ==========================================
    // 5. REMOVE IMAGE (With Undo Support)
    // ==========================================
    $(document).on('click', '.afc-remove-img', function (e) {
        e.preventDefault();

        const $item = $(this).closest('.afc-preview-item');
        const $zone = $item.closest('.afc-upload-zone');
        const $input = $zone.find('input[type="hidden"]');
        const imageId = $item.data('id').toString();

        // Store for potential undo
        lastRemovedImage = {
            id: imageId,
            zone: $zone,
            item: $item.clone(),
            timestamp: Date.now()
        };

        // Remove from hidden field
        let ids = $input.val().split(',').filter(Boolean);
        ids = ids.filter(id => id !== imageId);
        $input.val(ids.join(','));

        // Remove from DOM with animation
        $item.fadeOut(300, function () {
            $(this).remove();

            // Update gallery count if this is a gallery
            if ($zone.data('type') === 'gallery') {
                const limit = parseInt($zone.data('limit')) || MAX_GALLERY;
                updateGalleryCount($zone, ids.length, limit);
            }
        });

        formChanged = true;

        // Show undo option for 5 seconds
        showUndoNotice('Photo removed', function () {
            undoRemoveImage();
        });
    });

    // ==========================================
    // 5.1 UNDO REMOVE IMAGE
    // ==========================================
    function undoRemoveImage() {
        if (!lastRemovedImage) return;

        const age = Date.now() - lastRemovedImage.timestamp;
        if (age > 5000) {
            alert('⚠️ Undo expired. The undo window is 5 seconds.');
            return;
        }

        const $zone = lastRemovedImage.zone;
        const $input = $zone.find('input[type="hidden"]');
        const $preview = $zone.find('.afc-preview-grid');

        // Add back to hidden field
        let ids = $input.val() ? $input.val().split(',').filter(Boolean) : [];
        ids.push(lastRemovedImage.id);
        $input.val(ids.join(','));

        // Add back to DOM
        const $item = lastRemovedImage.item;
        $item.hide();
        $preview.append($item);
        $item.fadeIn(300);

        lastRemovedImage = null;
        showSuccessNotice('✅ Photo restored');
    }

    // ==========================================
    // 6. AGENT AUTO-FILL
    // ==========================================
    $(document).on('change', '#afc_agent_selector', function () {
        const $selected = $(this).find(':selected');

        if (!$selected.val()) return;

        // Confirm before overwriting
        const currentName = $('#afc_agent_name').val();
        if (currentName && currentName !== '') {
            if (!confirm('⚠️ OVERWRITE WARNING\n\nThis will replace the current agent information.\n\nCurrent: ' + currentName + '\nNew: ' + $selected.data('name') + '\n\nContinue?')) {
                $(this).val(''); // Reset selector
                return;
            }
        }

        // Fill in fields
        $('#afc_agent_name').val($selected.data('name'));
        $('#afc_agent_phone').val($selected.data('phone'));
        $('#agent_photo_id').val($selected.data('photo-id'));

        // Update photo preview
        const photoUrl = $selected.data('photo-url');
        if (photoUrl) {
            $('#agent-photo-img').fadeOut(200, function () {
                $(this).attr('src', photoUrl).fadeIn(200);
            });
        }

        formChanged = true;
        showSuccessNotice('✅ Agent information loaded: ' + $selected.data('name'));
    });

    // ==========================================
    // 7. DRAG & DROP SORTING (Gallery)
    // ==========================================
    function initSortable() {
        if (typeof $.fn.sortable === 'undefined') {
            console.warn('jQuery UI Sortable not loaded - drag & drop disabled');
            return;
        }

        $('.afc-preview-grid').sortable({
            items: '.afc-preview-item',
            cursor: 'grabbing',
            placeholder: 'afc-sortable-placeholder',
            tolerance: 'pointer',
            update: function (event, ui) {
                const $zone = $(this).closest('.afc-upload-zone');
                const $input = $zone.find('input[type="hidden"]');

                // Recalculate order
                const newIds = [];
                $(this).find('.afc-preview-item').each(function () {
                    newIds.push($(this).data('id'));
                });

                $input.val(newIds.join(','));

                console.log('Gallery reordered: ' + newIds.length + ' photos');
                formChanged = true;

                showSuccessNotice('✅ Gallery order updated');
            }
        });
    }

    // Initialize on page load
    initSortable();

    // ==========================================
    // 8. ENFORCE METABOX ORDER
    // ==========================================
    function enforceLayoutOrder() {
        const $container = $('#normal-sortables');
        if (!$container.length) return;

        const order = [
            'afc_intro',
            'afc_description',
            'afc_details',
            'afc_media_hub',
            'afc_slider',
            'afc_location_v2',
            'afc_amenities',
            'afc_agent',
            'afc_intelligence',
            'afc_publish_box'
        ];

        order.forEach(function (id) {
            const $box = $('#' + id);
            if ($box.length) {
                $container.append($box);
            }
        });
    }

    enforceLayoutOrder();

    // ==========================================
    // 9. PUBLISH BUTTON ENHANCEMENT
    // ==========================================
    $(document).on('click', '#publish', function (e) {
        const $title = $('#title');

        // Title validation
        if ($title.val().trim() === '') {
            e.preventDefault();
            alert('⚠️ MISSING TITLE\n\nPlease enter a property title before publishing.\n\nThis field is required.');
            $title.focus();
            return false;
        }

        // Hero image validation
        const heroId = $('input[name="_listing_hero_id"]').val();

        if (!heroId) {
            const confirmed = confirm(
                '⚠️ NO HERO IMAGE SET\n\n' +
                'This listing does not have a hero image.\n\n' +
                'Publishing without a hero image will result in:\n' +
                '• Poor visual presentation\n' +
                '• Lower engagement rates\n' +
                '• Unprofessional appearance\n\n' +
                'Do you want to continue anyway?'
            );

            if (!confirmed) {
                e.preventDefault();
                return false;
            }
        }

        // Gallery recommendation
        const galleryIds = $('input[name="_listing_gallery_ids"]').val();
        const photoCount = galleryIds ? galleryIds.split(',').filter(Boolean).length : 0;

        if (photoCount < 5) {
            const confirmed = confirm(
                '⚠️ LOW PHOTO COUNT\n\n' +
                'Current gallery: ' + photoCount + ' photos\n' +
                'Recommended: At least 5-8 photos\n\n' +
                'Luxury listings perform better with comprehensive photo galleries.\n\n' +
                'Publish anyway?'
            );

            if (!confirmed) {
                e.preventDefault();
                return false;
            }
        }

        // Show loading state
        $(this).prop('disabled', true)
            .val('Publishing...')
            .css({
                'opacity': '0.7',
                'cursor': 'not-allowed'
            });
    });

    // ==========================================
    // 10. NOTIFICATION HELPERS
    // ==========================================
    function showSuccessNotice(message) {
        // Remove any existing notices
        $('.afc-temp-notice').remove();

        const $notice = $('<div class="notice notice-success is-dismissible afc-temp-notice"><p>' + message + '</p></div>');
        $('.wrap h1').after($notice);

        setTimeout(function () {
            $notice.fadeOut(400, function () { $(this).remove(); });
        }, 3000);
    }

    function showUndoNotice(message, undoCallback) {
        $('.afc-temp-notice').remove();

        const $notice = $(
            '<div class="notice notice-warning afc-temp-notice" style="display:flex; justify-content:space-between; align-items:center;">' +
            '<p>' + message + '</p>' +
            '<button type="button" class="button afc-undo-btn">Undo</button>' +
            '</div>'
        );

        $('.wrap h1').after($notice);

        $notice.find('.afc-undo-btn').on('click', function () {
            undoCallback();
            $notice.remove();
        });

        setTimeout(function () {
            $notice.fadeOut(400, function () { $(this).remove(); });
        }, 5000);
    }

    // ==========================================
    // 11. KEYBOARD SHORTCUTS
    // ==========================================
    $(document).on('keydown', function (e) {
        // Ctrl/Cmd + S to save
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            $('#publish').click();
        }

        // Ctrl/Cmd + Z to undo (if within 5 seconds)
        if ((e.ctrlKey || e.metaKey) && e.key === 'z' && lastRemovedImage) {
            e.preventDefault();
            undoRemoveImage();
        }
    });

    // ==========================================
    // 12. CONSOLE WELCOME MESSAGE
    // ==========================================
    console.log('%c🚀 AFCGlide Admin v4.1.0 Loaded', 'color: #10b981; font-weight: bold; font-size: 14px;');
    console.log('%cKeyboard Shortcuts:', 'color: #64748b; font-size: 12px; font-weight: bold;');
    console.log('%c  Ctrl/Cmd + S: Save/Publish', 'color: #64748b; font-size: 11px;');
    console.log('%c  Ctrl/Cmd + Z: Undo last deletion', 'color: #64748b; font-size: 11px;');
    console.log('%cFeatures: Drag to reorder | Click × to remove | Auto-save protection', 'color: #64748b; font-size: 11px;');

});