/**
 * AFCGlide Settings Upload Handler
 * Handles the WordPress Media Library for Plugin Settings
 */
jQuery(document).ready(function($) {
    // When the 'Upload' button is clicked
    $('.afcglide-upload-button').on('click', function(e) {
        e.preventDefault();

        var button = $(this);
        var targetField = $('#' + button.data('target'));
        var previewContainer = $('#' + button.data('preview'));

        // Create the media frame
        var frame = wp.media({
            title: 'Select or Upload Media',
            button: {
                text: 'Use this media'
            },
            multiple: false
        });

        // When a file is selected, run a callback
        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            
            // Send the attachment URL to our hidden input
            targetField.val(attachment.url);

            // Update the preview image if it exists
            if (previewContainer.length) {
                previewContainer.html('<img src="' + attachment.url + '" style="max-width:150px; height:auto; display:block; margin-top:10px;">');
            }
        });

        // Open the modal
        frame.open();
    });
});