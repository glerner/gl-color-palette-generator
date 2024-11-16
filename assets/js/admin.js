/**
 * Color Palette Generator Admin Interface
 *
 * Handles all admin-side functionality for the color palette generator.
 *
 * @namespace ColorPaletteAdmin
 */
(function($) {
    'use strict';

    const ColorPaletteAdmin = {
        /**
         * Initialize the admin interface
         * @memberof ColorPaletteAdmin
         */
        init: function() {
            this.bindEvents();
            this.initColorPickers();
            this.updatePreview();
        },

        /**
         * Bind all event listeners
         * @memberof ColorPaletteAdmin
         * @private
         */
        bindEvents: function() {
            $('.gl-add-color').on('click', this.addColor.bind(this));
            $('.gl-remove-color').on('click', this.removeColor.bind(this));
            $('.gl-color-picker, .gl-color-hex').on('change', this.updateColor.bind(this));
            $('.gl-analyze-palette').on('click', this.analyzePalette.bind(this));
            $('.gl-save-palette').on('click', this.savePalette.bind(this));
            $('#gl-generation-method').on('change', this.switchGenerationMethod.bind(this));
            $('#gl-image-upload').on('change', this.handleImageUpload.bind(this));
            $('#gl-harmony-type, #gl-base-color').on('change', this.generateHarmonyColors.bind(this));
        },

        /**
         * Handle color palette analysis
         * @memberof ColorPaletteAdmin
         * @private
         */
        analyzePalette: function() {
            const colors = this.getColors();

            $.ajax({
                url: glColorPalette.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'gl_color_palette_analyze',
                    nonce: glColorPalette.nonce,
                    colors: colors
                },
                beforeSend: () => {
                    $('.gl-analysis-results').html(
                        '<div class="gl-loading">Analyzing...</div>'
                    );
                },
                success: (response) => {
                    if (response.success) {
                        $('.gl-analysis-results').html(response.data.html);
                        this.initAnalysisFeatures(response.data.analysis);
                    }
                },
                error: () => {
                    $('.gl-analysis-results').html(
                        '<div class="gl-error">Analysis failed</div>'
                    );
                }
            });
        },

        /**
         * Initialize interactive analysis features
         * @memberof ColorPaletteAdmin
         * @param {Object} analysis Analysis results
         * @private
         */
        initAnalysisFeatures: function(analysis) {
            // Add color suggestions
            if (analysis.accessibility && analysis.accessibility.recommendations) {
                this.initColorSuggestions(analysis.accessibility.recommendations);
            }

            // Add interactive contrast checker
            if (analysis.contrast) {
                this.initContrastChecker();
            }
        },

        /**
         * Initialize color suggestions UI
         * @memberof ColorPaletteAdmin
         * @param {Array} recommendations Color recommendations
         * @private
         */
        initColorSuggestions: function(recommendations) {
            const $suggestions = $('<div>', {
                class: 'gl-color-suggestions'
            });

            recommendations.forEach(rec => {
                if (rec.suggestions) {
                    const $suggestion = this.createSuggestionElement(rec);
                    $suggestions.append($suggestion);
                }
            });

            $('.gl-analysis-results').append($suggestions);
        },

        /**
         * Create a suggestion UI element
         * @memberof ColorPaletteAdmin
         * @param {Object} recommendation Recommendation data
         * @returns {jQuery} Suggestion element
         * @private
         */
        createSuggestionElement: function(recommendation) {
            const $element = $('<div>', {
                class: `gl-suggestion ${recommendation.type}`
            });

            $element.append(
                $('<h4>', { text: recommendation.message }),
                this.createSuggestionSwatches(recommendation.suggestions)
            );

            return $element;
        },

        /**
         * Handle image-based color extraction
         * @memberof ColorPaletteAdmin
         * @param {Event} e Change event
         * @private
         */
        handleImageUpload: function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (event) => {
                const img = new Image();
                img.onload = () => {
                    const colors = this.extractColorsFromImage(img);
                    this.setColors(colors);
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        },

        /**
         * Extract dominant colors from an image
         * @memberof ColorPaletteAdmin
         * @param {HTMLImageElement} img Image element
         * @returns {Array<string>} Extracted colors
         * @private
         */
        extractColorsFromImage: function(img) {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const colorMap = new Map();

            canvas.width = img.width;
            canvas.height = img.height;
            ctx.drawImage(img, 0, 0);

            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height).data;

            // Sample pixels and count color occurrences
            for (let i = 0; i < imageData.length; i += 16) {
                const color = `#${this.rgbToHex(imageData[i], imageData[i+1], imageData[i+2])}`;
                colorMap.set(color, (colorMap.get(color) || 0) + 1);
            }

            // Sort by frequency and return top colors
            return Array.from(colorMap.entries())
                .sort((a, b) => b[1] - a[1])
                .slice(0, 5)
                .map(([color]) => color);
        },

        /**
         * Convert RGB values to hex color code
         * @memberof ColorPaletteAdmin
         * @param {number} r Red value
         * @param {number} g Green value
         * @param {number} b Blue value
         * @returns {string} Hex color code
         * @private
         */
        rgbToHex: function(r, g, b) {
            return ((1 << 24) + (r << 16) + (g << 8) + b)
                .toString(16)
                .slice(1);
        },

        initColorPickers: function() {
            $('.gl-color-picker').wpColorPicker({
                change: this.handleColorPickerChange.bind(this)
            });
        },

        updatePreview: function() {
            const colors = this.getColors();
            const $preview = $('.gl-preview-swatches').empty();

            colors.forEach(color => {
                $preview.append(
                    $('<div>', {
                        class: 'gl-preview-swatch',
                        style: `background-color: ${color}`
                    })
                );
            });
        },

        getColors: function() {
            return $('.gl-color-picker').map(function() {
                return $(this).val();
            }).get();
        },

        addColor: function() {
            const $lastInput = $('.gl-color-input').last();
            const $newInput = $lastInput.clone();
            const newIndex = $('.gl-color-input').length;

            $newInput.find('input').attr('data-index', newIndex);
            $newInput.insertAfter($lastInput);

            this.initColorPickers();
            this.updateRemoveButtons();
            this.updatePreview();
        },

        removeColor: function(e) {
            if ($('.gl-color-input').length > 1) {
                $(e.target).closest('.gl-color-input').remove();
                this.updateRemoveButtons();
                this.updatePreview();
            }
        },

        updateRemoveButtons: function() {
            const $buttons = $('.gl-remove-color');
            $buttons.prop('disabled', $buttons.length <= 1);
        },

        // ... (to be continued)
    }

    $(document).ready(function() {
        ColorPaletteAdmin.init();
    });
})(jQuery); 
(function($) {
    'use strict';

    const GLColorPaletteComponents = {
        init: function() {
            this.initVariationSlider();
            this.initHarmonyWheel();
            this.initSchemePreview();
            this.initToasts();
        },

        initVariationSlider: function() {
            const slider = $('.variation-slider');
            const handle = slider.find('.variation-handle');
            let isDragging = false;

            handle.on('mousedown', function(e) {
                isDragging = true;
                e.preventDefault();
            });

            $(document).on('mousemove', function(e) {
                if (!isDragging) return;

                const track = $('.variation-track');
                const trackRect = track[0].getBoundingClientRect();
                let position = (e.clientX - trackRect.left) / trackRect.width;
                position = Math.max(0, Math.min(1, position));

                handle.css('left', `${position * 100}%`);

                // Calculate and update color based on position
                const baseColor = track.css('--color-base');
                const newColor = this.interpolateColor(position, baseColor);
                this.updateColorPreview(newColor);
            }.bind(this));

            $(document).on('mouseup', function() {
                isDragging = false;
            });
        },

        initHarmonyWheel: function() {
            const wheel = $('.harmony-wheel');
            const markers = wheel.find('.harmony-marker');

            markers.each(function() {
                $(this).draggable({
                    containment: 'parent',
                    drag: function(event, ui) {
                        const wheelRect = wheel[0].getBoundingClientRect();
                        const centerX = wheelRect.width / 2;
                        const centerY = wheelRect.height / 2;

                        // Calculate angle and radius
                        const x = ui.position.left - centerX;
                        const y = ui.position.top - centerY;
                        const angle = Math.atan2(y, x);
                        const radius = Math.sqrt(x * x + y * y);

                        // Update color based on position
                        const hue = ((angle * 180 / Math.PI) + 360) % 360;
                        const saturation = Math.min(100, (radius / centerX) * 100);
                        this.updateHarmonyColors(hue, saturation);
                    }.bind(this)
                });
            }.bind(this));
        },

        initSchemePreview: function() {
            $('.scheme-color').on('click', function() {
                const color = $(this).css('background-color');
                this.copyToClipboard(this.rgbToHex(color));
                this.showToast('Color copied to clipboard!', 'success');
            }.bind(this));
        },

        initToasts: function() {
            if (!$('.toast-container').length) {
                $('body').append('<div class="toast-container"></div>');
            }
        },

        showToast: function(message, type = 'success') {
            const toast = $('<div></div>')
                .addClass(`toast ${type}`)
                .text(message);

            $('.toast-container').append(toast);

            setTimeout(() => {
                toast.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        },

        interpolateColor: function(position, baseColor) {
            // Color interpolation logic here
            return baseColor;
        },

        updateColorPreview: function(color) {
            $('.color-swatch').css('background-color', color);
            $('.color-hex').text(this.rgbToHex(color));
        },

        updateHarmonyColors: function(hue, saturation) {
            // Update harmony colors logic here
        },

        rgbToHex: function(rgb) {
            // Convert RGB to HEX
            const match = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
            if (!match) return rgb;

            const r = parseInt(match[1]);
            const g = parseInt(match[2]);
            const b = parseInt(match[3]);

            return '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
        },

        copyToClipboard: function(text) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
        }
    };

    $(document).ready(function() {
        GLColorPaletteComponents.init();
    });
})(jQuery); 
