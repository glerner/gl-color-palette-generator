/**
 * Settings page functionality
 */
(function($) {
    'use strict';

    const GLColorPaletteSettings = {
        init: function() {
            this.providerSelect = $('#gl_color_palette_ai_provider');
            this.modelSelect = $('#gl_color_palette_model');
            this.apiKeyField = $('#gl_color_palette_api_key');
            this.testButton = $('#gl_test_api_connection');

            this.bindEvents();
            this.updateModelOptions();
        },

        bindEvents: function() {
            this.providerSelect.on('change', this.updateModelOptions.bind(this));
            this.testButton.on('click', this.testApiConnection.bind(this));
            this.apiKeyField.on('change', this.validateApiKey.bind(this));
        },

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
        GLColorPaletteSettings.init();
    });
})(jQuery); 
