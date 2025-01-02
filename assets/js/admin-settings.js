jQuery(document).ready(function($) {
    const clearCacheBtn = $('#clear-cache');
    const statusContainer = $('<div class="cache-status-message"></div>')
        .insertAfter(clearCacheBtn);

    /**
     * Display a message in the status container.
     * @param {string} message - The message to display.
     * @param {string} [type='info'] - The type of message (info, success, error).
     */
    function showMessage(message, type = 'info') {
        statusContainer
            .removeClass('notice-success notice-error notice-info')
            .addClass(`notice notice-${type} inline`)
            .html(`<p>${message}</p>`)
            .show();
    }

    /**
     * Clear the message from the status container.
     */
    function clearMessage() {
        statusContainer.hide().empty();
    }

    /**
     * Handle the click event for the clear cache button.
     * @param {Event} e - The event object.
     */
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
/**
 * Settings page functionality
 */
(function($) {
    'use strict';

    const GL_Color_Palette_GeneratorSettings = {
        /**
         * Initialize the settings page functionality.
         */
        init: function() {
            this.providerSelect = $('#gl_color_palette_ai_provider');
            this.modelSelect = $('#gl_color_palette_model');
            this.apiKeyField = $('#gl_color_palette_api_key');
            this.testButton = $('#gl_test_api_connection');

            this.bindEvents();
            this.updateModelOptions();
        },

        /**
         * Bind event handlers to UI elements.
         */
        bindEvents: function() {
            this.providerSelect.on('change', this.updateModelOptions.bind(this));
            this.testButton.on('click', this.testApiConnection.bind(this));
            this.apiKeyField.on('change', this.validateApiKey.bind(this));
        },

        /**
         * Update the model options based on the selected provider.
         */
        updateModelOptions: function() {
            const provider = this.providerSelect.val();
            const models = this.getModelsForProvider(provider);

            this.modelSelect.empty();

            Object.entries(models).forEach(([value, label]) => {
                this.modelSelect.append(
                    $('<option></option>')
                        .attr('value', value)
                        .text(label)
                );
            });
        },

        /**
         * Get available models for the selected provider.
         * @param {string} provider - The selected provider.
         * @return {Object} An object containing model options.
         */
        getModelsForProvider: function(provider) {
            const models = {
                openai: {
                    'gpt-4': 'GPT-4 (Recommended)',
                    'gpt-3.5-turbo': 'GPT-3.5 Turbo (Faster)'
                },
                anthropic: {
                    'claude-3-sonnet': 'Claude 3 Sonnet (Recommended)',
                    'claude-3-opus': 'Claude 3 Opus (Most Capable)',
                    'claude-3-haiku': 'Claude 3 Haiku (Fastest)'
                }
            };

            return models[provider] || {};
        },

        /**
         * Validate the API key format based on the selected provider.
         */
        validateApiKey: function() {
            const provider = this.providerSelect.val();
            const apiKey = this.apiKeyField.val();
            let isValid = true;

            // Basic format validation
            if (provider === 'openai' && !apiKey.match(/^sk-[a-zA-Z0-9]{32,}$/)) {
                isValid = false;
            } else if (provider === 'anthropic' && !apiKey.match(/^sk-ant-[a-zA-Z0-9]{32,}$/)) {
                isValid = false;
            }

            this.apiKeyField.toggleClass('invalid', !isValid);
            this.testButton.prop('disabled', !isValid);
        },

        /**
         * Test the API connection with the provided credentials.
         * @param {Event} e - The event object.
         */
        testApiConnection: function(e) {
            e.preventDefault();
            const self = this;
            const data = {
                action: 'gl_test_api_connection',
                provider: this.providerSelect.val(),
                api_key: this.apiKeyField.val(),
                nonce: glColorPalette.nonce
            };

            this.testButton.prop('disabled', true).text(glColorPalette.i18n.testing);

            $.post(ajaxurl, data, function(response) {
                if (response.success) {
                    self.showNotice('success', glColorPalette.i18n.connectionSuccess);
                } else {
                    self.showNotice('error', response.data.message);
                }
            }).always(function() {
                self.testButton.prop('disabled', false).text(glColorPalette.i18n.testConnection);
            });
        },

        /**
         * Display a notice message on the settings page.
         * @param {string} type - The type of notice (success, error).
         * @param {string} message - The message to display.
         */
        showNotice: function(type, message) {
            const notice = $('<div></div>')
                .addClass(`notice notice-${type} is-dismissible`)
                .append($('<p></p>').text(message));

            $('.wrap h1').after(notice);

            // Add dismiss button functionality
            notice.append(
                $('<button></button>')
                    .attr('type', 'button')
                    .addClass('notice-dismiss')
                    .append($('<span></span>').addClass('screen-reader-text').text('Dismiss this notice.'))
            );

            notice.find('.notice-dismiss').on('click', function() {
                notice.fadeOut(300, function() { $(this).remove(); });
            });
        }
    };

    $(document).ready(function() {
        GL_Color_Palette_GeneratorSettings.init();
    });
})(jQuery);
