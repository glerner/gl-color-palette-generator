<?php

class AdvancedPreviews {
    private $svg_generator;
    private $css_generator;
    private $settings;

    // New preview categories
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
    public function generate_advanced_previews($foreground, $background, $options = []) {
        $previews = [];

        foreach (self::PREVIEW_CATEGORIES as $category => $types) {
            if ($this->should_generate_category($category, $options)) {
                $previews[$category] = $this->generate_category_previews(
                    $category,
                    $types,
                    $foreground,
                    $background,
                    $options
                );
            }
        }

        return $previews;
    }

    /**
     * Generate typography previews
     */
    private function generate_typography_previews($foreground, $background, $options) {
        return [
            'font_stack' => $this->generate_font_stack_preview($foreground, $background, [
                'system' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica',
                'serif' => 'Georgia, "Times New Roman", serif',
                'monospace' => 'Monaco, Consolas, "Courier New", monospace'
            ]),
            'text_hierarchy' => $this->generate_text_hierarchy_preview($foreground, $background, [
                'h1' => ['size' => '2.5em', 'weight' => '700'],
                'h2' => ['size' => '2em', 'weight' => '600'],
                'h3' => ['size' => '1.75em', 'weight' => '500'],
                'body' => ['size' => '1em', 'weight' => '400'],
                'small' => ['size' => '0.875em', 'weight' => '400']
            ]),
            'link_states' => $this->generate_link_states_preview($foreground, $background, [
                'normal' => $foreground,
                'hover' => $this->adjust_color($foreground, ['lightness' => 10]),
                'active' => $this->adjust_color($foreground, ['lightness' => -10]),
                'visited' => $this->adjust_color($foreground, ['saturation' => -20])
            ]),
            'text_decoration' => $this->generate_text_decoration_preview($foreground, $background),
            'text_selection' => $this->generate_text_selection_preview($foreground, $background)
        ];
    }

    /**
     * Generate interactive previews
     */
    private function generate_interactive_previews($foreground, $background, $options) {
        return [
            'hover_states' => $this->generate_hover_states_preview($foreground, $background, [
                'scale' => 1.05,
                'transition' => '0.3s ease',
                'shadow' => '0 4px 6px rgba(0,0,0,0.1)'
            ]),
            'focus_states' => $this->generate_focus_states_preview($foreground, $background, [
                'outline_width' => '3px',
                'outline_offset' => '2px',
                'ring_color' => $this->adjust_color($foreground, ['alpha' => 0.5])
            ]),
            'active_states' => $this->generate_active_states_preview($foreground, $background, [
                'scale' => 0.98,
                'brightness' => 0.95
            ]),
            'loading_states' => $this->generate_loading_states_preview($foreground, $background, [
                'spinner_color' => $foreground,
                'background_opacity' => 0.8,
                'pulse_animation' => true
            ]),
            'disabled_states' => $this->generate_disabled_states_preview($foreground, $background, [
                'opacity' => 0.6,
                'cursor' => 'not-allowed',
                'grayscale' => true
            ])
        ];
    }

    /**
     * Generate data visualization previews
     */
    private function generate_data_viz_previews($foreground, $background, $options) {
        return [
            'charts' => $this->generate_chart_preview($foreground, $background, [
                'type' => 'bar',
                'data' => [10, 45, 30, 80, 60],
                'labels' => ['A', 'B', 'C', 'D', 'E'],
                'grid' => true
            ]),
            'graphs' => $this->generate_graph_preview($foreground, $background, [
                'type' => 'line',
                'points' => [[0,0], [25,30], [50,20], [75,60], [100,40]],
                'smooth' => true
            ]),
            'tables' => $this->generate_table_preview($foreground, $background, [
                'striped' => true,
                'hover' => true,
                'borders' => 'horizontal'
            ]),
            'infographics' => $this->generate_infographic_preview($foreground, $background, [
                'icons' => true,
                'data_points' => 4,
                'connector_style' => 'curved'
            ]),
            'maps' => $this->generate_map_preview($foreground, $background, [
                'region' => 'world',
                'highlight_color' => $this->adjust_color($foreground, ['lightness' => 20]),
                'marker_color' => $this->adjust_color($foreground, ['saturation' => 20])
            ])
        ];
    }

    /**
     * Generate dark mode previews
     */
    private function generate_dark_mode_previews($foreground, $background, $options) {
        // Invert colors for dark mode
        $dark_foreground = $this->invert_color($foreground);
        $dark_background = $this->invert_color($background);

        return [
            'inverted_text' => $this->generate_inverted_text_preview($dark_foreground, $dark_background, [
                'content' => 'Dark Mode Text Sample',
                'contrast_check' => true
            ]),
            'dark_ui' => $this->generate_dark_ui_preview($dark_foreground, $dark_background, [
                'components' => ['card', 'button', 'input'],
                'elevation' => true
            ]),
            'system_icons' => $this->generate_system_icons_preview($dark_foreground, $dark_background, [
                'icon_set' => ['home', 'search', 'menu', 'settings'],
                'stroke_width' => 1.5
            ]),
            'code_blocks' => $this->generate_code_block_preview($dark_foreground, $dark_background, [
                'language' => 'javascript',
                'line_numbers' => true,
                'syntax_highlighting' => true
            ]),
            'media_player' => $this->generate_media_player_preview($dark_foreground, $dark_background, [
                'controls' => true,
                'progress_bar' => true,
                'volume_slider' => true
            ])
        ];
    }

    /**
     * Generate print previews
     */
    private function generate_print_previews($foreground, $background, $options) {
        return [
            'documents' => $this->generate_document_preview($foreground, $background, [
                'page_size' => 'a4',
                'margins' => '2cm',
                'header_footer' => true
            ]),
            'business_cards' => $this->generate_business_card_preview($foreground, $background, [
                'orientation' => 'landscape',
                'size' => ['width' => '85mm', 'height' => '55mm'],
                'bleed' => '3mm'
            ]),
            'letterheads' => $this->generate_letterhead_preview($foreground, $background, [
                'logo' => true,
                'contact_info' => true,
                'watermark' => true
            ]),
            'invoices' => $this->generate_invoice_preview($foreground, $background, [
                'company_details' => true,
                'line_items' => true,
                'totals' => true
            ]),
            'reports' => $this->generate_report_preview($foreground, $background, [
                'cover_page' => true,
                'table_of_contents' => true,
                'sections' => true
            ])
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
