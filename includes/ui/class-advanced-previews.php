<?php
namespace GLColorPalette;
class AdvancedPreviews {
    private $svg_generator;
    private $css_generator;
    private $settings;

    /**
     * New preview categories
     */
    private const PREVIEW_CATEGORIES = [
        'typography' => [
            'font_stack',
            'text_hierarchy',
            'link_states',
            'text_decoration',
            'text_selection'
        ],
        'interactive' => [
            'hover_states',
            'focus_states',
            'active_states',
            'loading_states',
            'disabled_states'
        ],
        'components' => [
            'navigation',
            'tabs',
            'modals',
            'tooltips',
            'accordions'
        ],
        'data_viz' => [
            'charts',
            'graphs',
            'tables',
            'infographics',
            'maps'
        ],
        'marketing' => [
            'cta_buttons',
            'banners',
            'cards',
            'pricing_tables',
            'testimonials'
        ],
        'mobile' => [
            'buttons',
            'inputs',
            'switches',
            'notifications',
            'bottom_sheets'
        ],
        'dark_mode' => [
            'inverted_text',
            'dark_ui',
            'system_icons',
            'code_blocks',
            'media_player'
        ],
        'print' => [
            'documents',
            'business_cards',
            'letterheads',
            'invoices',
            'reports'
        ]
    ];

    public function __construct() {
        $this->svg_generator = new SVGGenerator();
        $this->css_generator = new CSSGenerator();
        $this->settings = new SettingsManager();
    }

    /**
     * Generate advanced previews
     */
    public function generate_advanced_previews($palette) {
        return [
            'light_mode' => $this->generate_light_mode_preview($palette),
            'dark_mode' => $this->generate_dark_mode_preview($palette),
            'color_blindness' => $this->generate_color_blindness_previews($palette),
            'device_previews' => $this->generate_device_specific_previews($palette),
            'context_previews' => $this->generate_context_specific_previews($palette)
        ];
    }

    /**
     * Generate interactive elements
     */
    public function generate_interactive_elements($palette) {
        return [
            'buttons' => $this->generate_button_states($palette),
            'forms' => $this->generate_form_elements($palette),
            'navigation' => $this->generate_navigation_elements($palette),
            'cards' => $this->generate_card_variations($palette),
            'modals' => $this->generate_modal_variations($palette)
        ];
    }

    /**
     * Generate accessibility previews
     */
    public function generate_accessibility_previews($palette) {
        $accessibility = new AccessibilityChecker();

        return [
            'contrast_ratios' => $accessibility->generate_contrast_matrix($palette),
            'color_blindness_simulations' => [
                'protanopia' => $this->simulate_protanopia($palette),
                'deuteranopia' => $this->simulate_deuteranopia($palette),
                'tritanopia' => $this->simulate_tritanopia($palette)
            ],
            'text_readability' => $this->generate_text_readability_preview($palette),
            'interface_elements' => $this->generate_interface_preview($palette)
        ];
    }

    /**
     * Utility methods
     */
    private function should_generate_category($category, $options) {
        if (isset($options['categories'])) {
            return in_array($category, $options['categories']);
        }
        return true;
    }

    private function adjust_color($color, $adjustments) {
        $color_adjuster = new ColorAdjuster();
        return $color_adjuster->adjust($color, $adjustments);
    }

    private function invert_color($color) {
        $rgb = $this->hex_to_rgb($color);
        return sprintf(
            '#%02x%02x%02x',
            255 - $rgb['r'],
            255 - $rgb['g'],
            255 - $rgb['b']
        );
    }

    private function hex_to_rgb($hex) {
        $hex = ltrim($hex, '#');
        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2))
        ];
    }
}
