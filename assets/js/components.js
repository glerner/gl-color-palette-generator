(function($) {
    'use strict';

    const GL_Color_Palette_GeneratorComponents = {
        /**
         * Initialize all component functionalities.
         */
        init: function() {
            this.initVariationSlider();
            this.initHarmonyWheel();
            this.initSchemePreview();
            this.initToasts();
        },

        /**
         * Initialize the variation slider functionality.
         */
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

        /**
         * Initialize the harmony wheel functionality.
         */
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

        /**
         * Initialize the scheme preview functionality.
         */
        initSchemePreview: function() {
            $('.scheme-color').on('click', function() {
                const color = $(this).css('background-color');
                this.copyToClipboard(this.rgbToHex(color));
                this.showToast('Color copied to clipboard!', 'success');
            }.bind(this));
        },

        /**
         * Initialize toast notifications.
         */
        initToasts: function() {
            if (!$('.toast-container').length) {
                $('body').append('<div class="toast-container"></div>');
            }
        },

        /**
         * Display a toast notification.
         * @param {string} message - The message to display.
         * @param {string} [type='success'] - The type of toast (success, error, etc.).
         */
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

        /**
         * Interpolate color based on position.
         * @param {number} position - The position on the slider (0 to 1).
         * @param {string} baseColor - The base color in CSS format.
         * @return {string} The interpolated color.
         */
        interpolateColor: function(position, baseColor) {
            const targetColor = [255, 255, 255]; // Interpolating towards white
            const baseRgb = this.extractRgbValues(baseColor);

            const interpolatedRgb = baseRgb.map((baseValue, index) => {
                return Math.round(baseValue + (targetColor[index] - baseValue) * position);
            });

            return `rgb(${interpolatedRgb.join(', ')})`;
        },

        /**
         * Update the color preview display.
         * @param {string} color - The color to display.
         */
        updateColorPreview: function(color) {
            $('.color-swatch').css('background-color', color);
            $('.color-hex').text(this.rgbToHex(color));
        },

        /**
         * Update harmony colors based on hue and saturation.
         * @param {number} hue - The hue value (0 to 360).
         * @param {number} saturation - The saturation percentage (0 to 100).
         */
        updateHarmonyColors: function(hue, saturation) {
            const baseLightness = 50; // Assuming a fixed lightness for simplicity
            const rgbColor = this.hslToRgb(hue, saturation, baseLightness);

            $('.harmony-color-preview').css('background-color', rgbColor);
        },

        /**
         * Convert HSL to RGB color format.
         * @param {number} h - The hue (0 to 360).
         * @param {number} s - The saturation (0 to 100).
         * @param {number} l - The lightness (0 to 100).
         * @return {string} The RGB color string.
         */
        hslToRgb: function(h, s, l) {
            s /= 100;
            l /= 100;

            const c = (1 - Math.abs(2 * l - 1)) * s;
            const x = c * (1 - Math.abs((h / 60) % 2 - 1));
            const m = l - c / 2;
            let r = 0, g = 0, b = 0;

            if (0 <= h && h < 60) {
                r = c; g = x; b = 0;
            } else if (60 <= h && h < 120) {
                r = x; g = c; b = 0;
            } else if (120 <= h && h < 180) {
                r = 0; g = c; b = x;
            } else if (180 <= h && h < 240) {
                r = 0; g = x; b = c;
            } else if (240 <= h && h < 300) {
                r = x; g = 0; b = c;
            } else if (300 <= h && h < 360) {
                r = c; g = 0; b = x;
            }

            r = Math.round((r + m) * 255);
            g = Math.round((g + m) * 255);
            b = Math.round((b + m) * 255);

            return `rgb(${r}, ${g}, ${b})`;
        },

        /**
         * Convert an RGB color to HEX format.
         * @param {string} rgb - The RGB color string.
         * @return {string} The HEX color string.
         */
        rgbToHex: function(rgb) {
            // Convert RGB to HEX
            const match = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
            if (!match) return rgb;

            const r = parseInt(match[1]);
            const g = parseInt(match[2]);
            const b = parseInt(match[3]);

            return '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
        },

        /**
         * Copy text to the clipboard.
         * @param {string} text - The text to copy.
         */
        copyToClipboard: function(text) {
            try {
                const textarea = document.createElement('textarea');
                textarea.value = text;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                this.showToast('Text copied to clipboard!', 'success');
            } catch (error) {
                this.showToast('Failed to copy text.', 'error');
            }
        }
    };

    $(document).ready(function() {
        GL_Color_Palette_GeneratorComponents.init();
    });
})(jQuery);
