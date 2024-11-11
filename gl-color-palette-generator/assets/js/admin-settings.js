jQuery(document).ready(function($) {
    const clearCacheBtn = $('#clear-cache');
    const statusContainer = $('<div class="cache-status-message"></div>')
        .insertAfter(clearCacheBtn);

    function showMessage(message, type = 'info') {
        statusContainer
            .removeClass('notice-success notice-error notice-info')
            .addClass(`notice notice-${type} inline`)
            .html(`<p>${message}</p>`)
            .show();
    }

    function clearMessage() {
        statusContainer.hide().empty();
    }

    clearCacheBtn.on('click', function(e) {
        e.preventDefault();
        const button = $(this);

        // Confirm before clearing
        if (!confirm('Are you sure you want to clear the color name cache?')) {
            return;
        }

        // Disable button and show loading state
        button.prop('disabled', true);
        button.addClass('updating-message');
        showMessage(colorPaletteSettings.clearingCache, 'info');

        $.ajax({
            url: colorPaletteSettings.ajaxUrl,
            type: 'POST',
            data: {
                action: 'clear_color_cache',
                nonce: colorPaletteSettings.nonce
            },
            success: function(response) {
                if (response.success) {
                    showMessage(colorPaletteSettings.cacheCleared, 'success');

                    // Update cache count in status table
                    $('.cache-count').text('0');

                    // Optionally reload after a delay
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showMessage(
                        `${colorPaletteSettings.errorClearing}: ${response.data.message}`,
                        'error'
                    );
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                showMessage(
                    `${colorPaletteSettings.errorClearing}: ${textStatus}`,
                    'error'
                );
            },
            complete: function() {
                button.prop('disabled', false);
                button.removeClass('updating-message');
            }
        });
    });
});
