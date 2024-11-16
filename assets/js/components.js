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
