/**
 * Admin JavaScript for GL Color Palette Generator
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

(function($) {
    'use strict';

    const ColorPaletteAdmin = {
        /**
         * Current palette colors
         * @type {Array}
         */
        currentPalette: [],

        /**
         * Initialize the admin functionality
         */
        init: function() {
            this.bindEvents();
            this.initColorPicker();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            $('#gl-cpg-generate-form').on('submit', this.handleGenerate.bind(this));
            $('#gl-cpg-save-form').on('submit', this.handleSave.bind(this));
            $('.gl-cpg-delete').on('click', this.handleDelete.bind(this));
            $('.gl-cpg-color').on('click', this.handleColorClick.bind(this));
        },

        /**
         * Initialize color picker
         */
        initColorPicker: function() {
            $('.gl-cpg-color-picker').wpColorPicker({
                change: this.handleColorChange.bind(this)
            });
        },

        /**
         * Handle palette generation
         * @param {Event} e Submit event
         */
        handleGenerate: async function(e) {
            e.preventDefault();

            const $form = $(e.currentTarget);
            const $submit = $form.find('button[type="submit"]');
            const prompt = $('#gl-cpg-prompt').val();

            if (!prompt) {
                this.showNotice('error', glCpgAdmin.i18n.promptRequired);
                return;
            }

            $submit.prop('disabled', true);

            try {
                const response = await $.ajax({
                    url: glCpgAdmin.ajaxUrl,
                    method: 'POST',
                    data: {
                        action: 'gl_cpg_generate_palette',
                        nonce: glCpgAdmin.nonce,
                        prompt: prompt
                    }
                });

                if (response.success) {
                    this.currentPalette = response.data.palette;
                    this.displayPalette(response.data.palette);
                    this.showNotice('success', response.data.message);
                } else {
                    this.showNotice('error', response.data);
                }
            } catch (error) {
                this.showNotice('error', glCpgAdmin.i18n.generateError);
            } finally {
                $submit.prop('disabled', false);
            }
        },

        /**
         * Handle palette saving
         * @param {Event} e Submit event
         */
        handleSave: async function(e) {
            e.preventDefault();

            const $form = $(e.currentTarget);
            const $submit = $form.find('button[type="submit"]');
            const name = $form.find('input[name="palette_name"]').val();

            if (!name || !this.currentPalette.length) {
                this.showNotice('error', glCpgAdmin.i18n.invalidData);
                return;
            }

            $submit.prop('disabled', true);

            try {
                const response = await $.ajax({
                    url: glCpgAdmin.ajaxUrl,
                    method: 'POST',
                    data: {
                        action: 'gl_cpg_save_palette',
                        nonce: glCpgAdmin.nonce,
                        name: name,
                        colors: this.currentPalette
                    }
                });

                if (response.success) {
                    this.showNotice('success', glCpgAdmin.i18n.saveSuccess);
                    window.location.reload();
                } else {
                    this.showNotice('error', response.data);
                }
            } catch (error) {
                this.showNotice('error', glCpgAdmin.i18n.saveError);
            } finally {
                $submit.prop('disabled', false);
            }
        },

        /**
         * Handle palette deletion
         * @param {Event} e Click event
         */
        handleDelete: async function(e) {
            e.preventDefault();

            if (!confirm(glCpgAdmin.i18n.deleteConfirm)) {
                return;
            }

            const $button = $(e.currentTarget);
            const paletteId = $button.closest('.gl-cpg-palette').data('id');

            $button.prop('disabled', true);

            try {
                const response = await $.ajax({
                    url: glCpgAdmin.ajaxUrl,
                    method: 'POST',
                    data: {
                        action: 'gl_cpg_delete_palette',
                        nonce: glCpgAdmin.nonce,
                        id: paletteId
                    }
                });

                if (response.success) {
                    $button.closest('.gl-cpg-palette').fadeOut(400, function() {
                        $(this).remove();
                    });
                } else {
                    this.showNotice('error', response.data);
                }
            } catch (error) {
                this.showNotice('error', glCpgAdmin.i18n.deleteError);
            } finally {
                $button.prop('disabled', false);
            }
        },

        /**
         * Handle color click (copy to clipboard)
         * @param {Event} e Click event
         */
        handleColorClick: function(e) {
            const $color = $(e.currentTarget);
            const hex = $color.find('.gl-cpg-color-hex').text();

            navigator.clipboard.writeText(hex).then(() => {
                this.showNotice('success', glCpgAdmin.i18n.colorCopied);
            });
        },

        /**
         * Handle color picker change
         * @param {Event} e Change event
         * @param {Object} ui Color picker UI object
         */
        handleColorChange: function(e, ui) {
            const index = $(e.target).data('index');
            this.currentPalette[index] = ui.color.toString();
        },

        /**
         * Display generated palette
         * @param {Array} colors Array of color hex codes
         */
        displayPalette: function(colors) {
            const $preview = $('#gl-cpg-preview');
            const $colors = $preview.find('.gl-cpg-colors');

            $colors.empty();

            colors.forEach(color => {
                $colors.append(`
                    <div class="gl-cpg-color" style="background-color: ${color}">
                        <span class="gl-cpg-color-hex">${color}</span>
                    </div>
                `);
            });

            $preview.removeClass('hidden');
        },

        /**
         * Show admin notice
         * @param {string} type Notice type (success/error)
         * @param {string} message Notice message
         */
        showNotice: function(type, message) {
            const $notice = $(`
                <div class="notice notice-${type} is-dismissible">
                    <p>${message}</p>
                </div>
            `);

            $('.wrap > h1').after($notice);

            // Initialize WordPress dismissible notices
            if (window.wp && window.wp.notices) {
                window.wp.notices.initializeNotices();
            }

            // Auto-dismiss after 3 seconds
            setTimeout(() => {
                $notice.fadeOut(400, function() {
                    $(this).remove();
                });
            }, 3000);
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        ColorPaletteAdmin.init();
    });

})(jQuery);
