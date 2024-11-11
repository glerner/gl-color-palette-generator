(function($) {
    'use strict';

    const ColorPaletteAdmin = {
        form: null,
        previewContainer: null,
        downloadContainer: null,
        colorPickers: [],
        isGenerating: false,

        init: function() {
            this.form = $('#palette-generator-form');
            this.previewContainer = $('#preview-container');
            this.downloadContainer = $('#download-container');

            this.initColorPickers();
            this.bindEvents();
            this.initTooltips();
        },

        initColorPickers: function() {
            $('.color-picker').each((index, element) => {
                const $picker = $(element);

                $picker.wpColorPicker({
                    defaultColor: '#000000',
                    change: (event, ui) => {
                        this.handleColorChange(index, ui.color.toString());
                    },
                    clear: () => {
                        this.handleColorChange(index, '');
                    }
                });

                this.colorPickers.push($picker);
            });
        },

        bindEvents: function() {
            this.form.on('submit', (e) => {
                e.preventDefault();
                this.generatePalette();
            });

            // Handle real-time preview updates
            this.form.on('change', 'select[name="naming_preference"]', () => {
                if (this.hasValidColors()) {
                    this.updatePreview();
                }
            });

            // Handle download button clicks
            this.downloadContainer.on('click', '.download-button', (e) => {
                e.preventDefault();
                this.handleDownload($(e.currentTarget).data('url'));
            });

            // Handle variation preview tabs
            this.previewContainer.on('click', '.variation-tab', (e) => {
                e.preventDefault();
                this.switchVariationTab($(e.currentTarget));
            });
        },

        initTooltips: function() {
            $('.color-input-group').each(function() {
                const $group = $(this);
                const role = $group.find('label').text();

                $group.attr('title', `Choose a base color for ${role}`);

                // Initialize WordPress tooltips if available
                if (typeof wp.tooltips !== 'undefined') {
                    wp.tooltips.add($group);
                }
            });
        },

        hasValidColors: function() {
            return this.colorPickers.every($picker =>
                $picker.val() && $picker.val().match(/^#[0-9A-F]{6}$/i)
            );
        },

        handleColorChange: function(index, color) {
            if (this.hasValidColors()) {
                this.updatePreview();
            }
        },

        generatePalette: function() {
            if (this.isGenerating || !this.hasValidColors()) {
                return;
            }

            this.isGenerating = true;
            this.showGeneratingState();

            const formData = new FormData(this.form[0]);
            formData.append('action', 'generate_palette');
            formData.append('nonce', colorPaletteAdmin.nonce);

            $.ajax({
                url: colorPaletteAdmin.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => {
                    if (response.success) {
                        this.handleSuccess(response.data);
                    } else {
                        this.handleError(response.data.message);
                    }
                },
                error: (xhr, status, error) => {
                    this.handleError(error);
                },
                complete: () => {
                    this.isGenerating = false;
                    this.hideGeneratingState();
                }
            });
        },

        updatePreview: function() {
            const formData = new FormData(this.form[0]);
            formData.append('action', 'preview_palette');
            formData.append('nonce', colorPaletteAdmin.nonce);

            $.ajax({
                url: colorPaletteAdmin.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => {
                    if (response.success) {
                        this.renderPreview(response.data);
                    }
                }
            });
        },

        renderPreview: function(data) {
            // Render palette preview
            const previewHtml = `
                <div class="palette-preview">
                    <h2>Color Palette Preview</h2>
                    <div class="preview-tabs">
                        <button class="variation-tab active" data-variation="palette">
                            Palette
                        </button>
                        <button class="variation-tab" data-variation="variations">
                            Style Variations
                        </button>
                    </div>
                    <div class="preview-content">
                        <div class="preview-panel active" id="palette-preview">
                            ${data.palettePreview}
                        </div>
                        <div class="preview-panel" id="variations-preview">
                            ${data.variationsPreview}
                        </div>
                    </div>
                </div>
            `;

            this.previewContainer.html(previewHtml);
            this.initPreviewInteractions();
        },

        initPreviewInteractions: function() {
            // Initialize preview interactions (hover states, etc.)
            $('.color-swatch').hover(
                function() { $(this).addClass('hover'); },
                function() { $(this).removeClass('hover'); }
            );

            // Copy color values on click
            $('.color-swatch').on('click', function() {
                const colorValue = $(this).data('color');
                this.copyToClipboard(colorValue);
                this.showCopyNotification($(this));
            }.bind(this));
        },

        copyToClipboard: function(text) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
        },

        showCopyNotification: function($element) {
            const $notification = $('<div class="copy-notification">Copied!</div>');
            $element.append($notification);
            setTimeout(() => $notification.remove(), 1500);
        },

        switchVariationTab: function($tab) {
            const variation = $tab.data('variation');

            $('.variation-tab').removeClass('active');
            $tab.addClass('active');

            $('.preview-panel').removeClass('active');
            $(`#${variation}-preview`).addClass('active');
        },

        handleSuccess: function(data) {
            this.renderPreview(data);

            // Show download options
            this.downloadContainer.html(`
                <div class="download-options">
                    <h3>Download Files</h3>
                    <p>Your color palette has been generated successfully!</p>
                    <button class="button button-primary download-button"
                            data-url="${data.downloadUrl}">
                        Download ZIP
                    </button>
                </div>
            `).addClass('active');

            // Show success message
            this.showNotice(colorPaletteAdmin.strings.success, 'success');
        },

        handleError: function(message) {
            this.showNotice(
                `${colorPaletteAdmin.strings.error}: ${message}`,
                'error'
            );
        },

        showGeneratingState: function() {
            this.form.addClass('generating');
            this.form.find('button[type="submit"]')
                .text(colorPaletteAdmin.strings.generating);
        },

        hideGeneratingState: function() {
            this.form.removeClass('generating');
            this.form.find('button[type="submit"]')
                .text('Generate Palette');
        },

        showNotice: function(message, type = 'success') {
            const $notice = $(`
                <div class="notice notice-${type} is-dismissible">
                    <p>${message}</p>
                </div>
            `);

            $('.wrap > h1').after($notice);

            // Initialize WordPress dismissible notices
            if (typeof wp.notices !== 'undefined') {
                wp.notices.initialize($notice);
            }
        },

        handleDownload: function(url) {
            window.location.href = url;
        },

        accessibilityTools: {
            init: function() {
                this.addAccessibilityPanel();
                this.initContrastChecker();
                this.bindAccessibilityEvents();
                this.initSimulators();
            },

            addAccessibilityPanel: function() {
                const panelHtml = `
                    <div class="accessibility-tools">
                        <h3>Accessibility Tools</h3>

                        <!-- Contrast Checker -->
                        <div class="contrast-checker">
                            <h4>Contrast Checker</h4>
                            <div class="contrast-inputs">
                                <div class="color-select">
                                    <label>Foreground Color</label>
                                    <select class="foreground-select">
                                        ${this.generateColorOptions()}
                                    </select>
                                    <input type="text" class="custom-foreground" placeholder="#000000">
                                </div>
                                <div class="color-select">
                                    <label>Background Color</label>
                                    <select class="background-select">
                                        ${this.generateColorOptions()}
                                    </select>
                                    <input type="text" class="custom-background" placeholder="#FFFFFF">
                                </div>
                            </div>
                            <div class="contrast-preview">
                                <p class="sample-text">Sample Text</p>
                                <div class="contrast-ratio"></div>
                                <div class="wcag-results"></div>
                            </div>
                        </div>

                        <!-- Color Vision Simulator -->
                        <div class="vision-simulator">
                            <h4>Color Vision Simulator</h4>
                            <div class="simulator-controls">
                                <button type="button" class="button vision-type" data-type="normal">
                                    Normal Vision
                                </button>
                                <button type="button" class="button vision-type" data-type="protanopia">
                                    Protanopia
                                </button>
                                <button type="button" class="button vision-type" data-type="deuteranopia">
                                    Deuteranopia
                                </button>
                                <button type="button" class="button vision-type" data-type="tritanopia">
                                    Tritanopia
                                </button>
                                <button type="button" class="button vision-type" data-type="achromatopsia">
                                    Grayscale
                                </button>
                            </div>
                            <div class="simulator-preview"></div>
                        </div>

                        <!-- Accessibility Warnings -->
                        <div class="accessibility-warnings">
                            <h4>Accessibility Checks</h4>
                            <div class="warning-list"></div>
                        </div>
                    </div>
                `;

                this.previewContainer.after(panelHtml);
            },

            generateColorOptions: function() {
                const variations = ['lighter', 'light', 'dark', 'darker'];
                const roles = ['primary', 'secondary', 'tertiary', 'accent'];
                let options = '<option value="custom">Custom Color</option>';

                roles.forEach(role => {
                    variations.forEach(variation => {
                        const value = `var(--wp--preset--color--${role}-${variation})`;
                        options += `<option value="${value}">${role}-${variation}</option>`;
                    });
                });

                return options;
            },

            initContrastChecker: function() {
                const updateContrast = () => {
                    const fg = this.getColorValue('.foreground-select', '.custom-foreground');
                    const bg = this.getColorValue('.background-select', '.custom-background');

                    if (fg && bg) {
                        const ratio = this.calculateContrastRatio(fg, bg);
                        this.updateContrastDisplay(ratio);
                        this.updateSampleText(fg, bg);
                    }
                };

                $('.contrast-inputs select, .contrast-inputs input').on('change input', updateContrast);
            },

            getColorValue: function(selectSelector, inputSelector) {
                const $select = $(selectSelector);
                const $input = $(inputSelector);

                return $select.val() === 'custom' ? $input.val() : this.getCSSVariableColor($select.val());
            },

            getCSSVariableColor: function(variable) {
                if (!variable.startsWith('var(')) return variable;

                const computed = getComputedStyle(document.documentElement)
                    .getPropertyValue(variable.slice(4, -1));
                return computed || '#000000';
            },

            calculateContrastRatio: function(color1, color2) {
                const l1 = this.getRelativeLuminance(color1);
                const l2 = this.getRelativeLuminance(color2);

                const lighter = Math.max(l1, l2);
                const darker = Math.min(l1, l2);

                return (lighter + 0.05) / (darker + 0.05);
            },

            getRelativeLuminance: function(hex) {
                const rgb = this.hexToRgb(hex);
                const [r, g, b] = [rgb.r, rgb.g, rgb.b].map(c => {
                    c = c / 255;
                    return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
                });
                return 0.2126 * r + 0.7152 * g + 0.0722 * b;
            },

            updateContrastDisplay: function(ratio) {
                const $results = $('.wcag-results');
                const roundedRatio = Math.round(ratio * 100) / 100;

                $('.contrast-ratio').text(`Contrast Ratio: ${roundedRatio}:1`);

                const wcagResults = this.getWCAGResults(ratio);
                $results.html(`
                    <div class="wcag-level">
                        <span class="wcag-label">WCAG AA:</span>
                        <span class="wcag-result ${wcagResults.aa ? 'pass' : 'fail'}">
                            ${wcagResults.aa ? 'Pass' : 'Fail'}
                        </span>
                    </div>
                    <div class="wcag-level">
                        <span class="wcag-label">WCAG AAA:</span>
                        <span class="wcag-result ${wcagResults.aaa ? 'pass' : 'fail'}">
                            ${wcagResults.aaa ? 'Pass' : 'Fail'}
                        </span>
                    </div>
                `);
            },

            getWCAGResults: function(ratio) {
                return {
                    aa: ratio >= 4.5,
                    aaa: ratio >= 7
                };
            },

            updateSampleText: function(fg, bg) {
                $('.sample-text').css({
                    color: fg,
                    backgroundColor: bg,
                    padding: '15px',
                    borderRadius: '4px'
                });
            },

            initSimulators: function() {
                $('.vision-type').on('click', (e) => {
                    const type = $(e.target).data('type');
                    this.updateSimulatorPreview(type);
                });
            },

            updateSimulatorPreview: function(type) {
                const $preview = $('.simulator-preview');
                const colors = this.getAllPaletteColors();

                let previewHtml = '<div class="simulation-grid">';
                colors.forEach(color => {
                    const simulatedColor = this.simulateColorVision(color, type);
                    previewHtml += `
                        <div class="simulation-swatch">
                            <div class="original-color" style="background-color: ${color}"></div>
                            <div class="simulated-color" style="background-color: ${simulatedColor}"></div>
                            <div class="color-info">
                                <span class="color-hex">${color}</span>
                                <span class="simulation-type">${type}</span>
                            </div>
                        </div>
                    `;
                });
                previewHtml += '</div>';

                $preview.html(previewHtml);
            },

            simulateColorVision: function(hex, type) {
                const rgb = this.hexToRgb(hex);
                let simulated;

                switch(type) {
                    case 'protanopia':
                        simulated = this.simulateProtanopia(rgb);
                        break;
                    case 'deuteranopia':
                        simulated = this.simulateDeuteranopia(rgb);
                        break;
                    case 'tritanopia':
                        simulated = this.simulateTritanopia(rgb);
                        break;
                    case 'achromatopsia':
                        simulated = this.simulateAchromatopsia(rgb);
                        break;
                    default:
                        return hex;
                }

                return this.rgbToHex(simulated);
            },

            // Color vision simulation algorithms
            simulateProtanopia: function(rgb) {
                // Simulation matrix for protanopia
                return {
                    r: 0.567 * rgb.r + 0.433 * rgb.g + 0 * rgb.b,
                    g: 0.558 * rgb.r + 0.442 * rgb.g + 0 * rgb.b,
                    b: 0 * rgb.r + 0.242 * rgb.g + 0.758 * rgb.b
                };
            },

            simulateDeuteranopia: function(rgb) {
                // Simulation matrix for deuteranopia
                return {
                    r: 0.625 * rgb.r + 0.375 * rgb.g + 0 * rgb.b,
                    g: 0.7 * rgb.r + 0.3 * rgb.g + 0 * rgb.b,
                    b: 0 * rgb.r + 0.3 * rgb.g + 0.7 * rgb.b
                };
            },

            simulateTritanopia: function(rgb) {
                // Simulation matrix for tritanopia
                return {
                    r: 0.95 * rgb.r + 0.05 * rgb.g + 0 * rgb.b,
                    g: 0 * rgb.r + 0.433 * rgb.g + 0.567 * rgb.b,
                    b: 0 * rgb.r + 0.475 * rgb.g + 0.525 * rgb.b
                };
            },

            simulateAchromatopsia: function(rgb) {
                // Convert to grayscale
                const gray = 0.299 * rgb.r + 0.587 * rgb.g + 0.114 * rgb.b;
                return { r: gray, g: gray, b: gray };
            },

            // Utility functions
            hexToRgb: function(hex) {
                const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
                return result ? {
                    r: parseInt(result[1], 16),
                    g: parseInt(result[2], 16),
                    b: parseInt(result[3], 16)
                } : null;
            },

            rgbToHex: function(rgb) {
                const toHex = c => {
                    const hex = Math.round(c).toString(16);
                    return hex.length === 1 ? '0' + hex : hex;
                };
                return `#${toHex(rgb.r)}${toHex(rgb.g)}${toHex(rgb.b)}`;
            },

            getAllPaletteColors: function() {
                return $('.color-picker').map(function() {
                    return $(this).val();
                }).get();
            }
        }
    };

    // Initialize on document ready
    $(document).ready(() => {
        ColorPaletteAdmin.init();
    });

})(jQuery);
