<?php

class VisualizationHelper {
    private $svg_generator;
    private $css_generator;
    private $image_processor;
    private $settings;

    // Preview types
    private const PREVIEW_TYPES = [
        'text' => ['paragraph', 'heading', 'button'],
        'ui' => ['card', 'alert', 'form'],
        'pattern' => ['dots', 'stripes', 'grid'],
        'simulation' => ['normal', 'protanopia', 'deuteranopia', 'tritanopia'],
        'contrast' => ['side-by-side', 'overlay', 'gradient']
    ];

    public function __construct() {
        $this->svg_generator = new SVGGenerator();
        $this->css_generator = new CSSGenerator();
        $this->image_processor = new ImageProcessor();
        $this->settings = new SettingsManager();
    }

    /**
     * Generate visual previews
     */
    public function generate_previews($foreground, $background, $options = []) {
        $previews = [
            'text_samples' => $this->generate_text_previews($foreground, $background),
            'ui_elements' => $this->generate_ui_previews($foreground, $background),
            'patterns' => $this->generate_pattern_previews($foreground, $background),
            'simulations' => $this->generate_vision_simulations($foreground, $background),
            'contrast_demos' => $this->generate_contrast_demonstrations($foreground, $background)
        ];

        if (!empty($options['custom_previews'])) {
            $previews['custom'] = $this->generate_custom_previews(
                $foreground,
                $background,
                $options['custom_previews']
            );
        }

        return $previews;
    }

    /**
     * Generate text previews
     */
    private function generate_text_previews($foreground, $background) {
        $html = '';

        // Heading preview
        $html .= $this->generate_text_sample(
            $foreground,
            $background,
            'h1',
            __('Sample Heading Text', 'color-palette-generator'),
            ['font-size' => '32px', 'font-weight' => 'bold']
        );

        // Paragraph preview
        $html .= $this->generate_text_sample(
            $foreground,
            $background,
            'p',
            __('This is a sample paragraph demonstrating how the text appears against the background. It includes multiple lines to show text flow and readability.', 'color-palette-generator'),
            ['font-size' => '16px', 'line-height' => '1.5']
        );

        // Button preview
        $html .= $this->generate_text_sample(
            $foreground,
            $background,
            'button',
            __('Click Me', 'color-palette-generator'),
            ['padding' => '10px 20px', 'border-radius' => '4px']
        );

        return [
            'html' => $html,
            'css' => $this->css_generator->generate_text_styles($foreground, $background),
            'svg' => $this->svg_generator->generate_text_preview($foreground, $background)
        ];
    }

    /**
     * Generate UI element previews
     */
    private function generate_ui_previews($foreground, $background) {
        $previews = [];

        // Card preview
        $previews['card'] = $this->generate_ui_component(
            'card',
            $foreground,
            $background,
            [
                'width' => '300px',
                'height' => '200px',
                'border-radius' => '8px',
                'shadow' => true
            ]
        );

        // Alert preview
        $previews['alert'] = $this->generate_ui_component(
            'alert',
            $foreground,
            $background,
            [
                'width' => '100%',
                'padding' => '15px',
                'border-left' => '4px solid ' . $foreground
            ]
        );

        // Form elements preview
        $previews['form'] = $this->generate_ui_component(
            'form',
            $foreground,
            $background,
            [
                'input-height' => '40px',
                'border-color' => $foreground,
                'focus-ring' => true
            ]
        );

        return $previews;
    }

    /**
     * Generate pattern previews
     */
    private function generate_pattern_previews($foreground, $background) {
        return [
            'dots' => $this->svg_generator->generate_pattern(
                'dots',
                $foreground,
                $background,
                ['size' => 20, 'spacing' => 10]
            ),
            'stripes' => $this->svg_generator->generate_pattern(
                'stripes',
                $foreground,
                $background,
                ['width' => 10, 'angle' => 45]
            ),
            'grid' => $this->svg_generator->generate_pattern(
                'grid',
                $foreground,
                $background,
                ['size' => 30, 'thickness' => 2]
            )
        ];
    }

    /**
     * Generate color vision simulations
     */
    private function generate_vision_simulations($foreground, $background) {
        $simulations = [];

        foreach (self::PREVIEW_TYPES['simulation'] as $type) {
            $simulations[$type] = [
                'foreground' => $this->image_processor->simulate_color_vision(
                    $foreground,
                    $type
                ),
                'background' => $this->image_processor->simulate_color_vision(
                    $background,
                    $type
                ),
                'preview' => $this->generate_simulation_preview(
                    $foreground,
                    $background,
                    $type
                )
            ];
        }

        return $simulations;
    }

    /**
     * Generate contrast demonstrations
     */
    private function generate_contrast_demonstrations($foreground, $background) {
        return [
            'side_by_side' => $this->svg_generator->generate_contrast_demo(
                'side-by-side',
                $foreground,
                $background
            ),
            'overlay' => $this->svg_generator->generate_contrast_demo(
                'overlay',
                $foreground,
                $background,
                ['opacity' => 0.8]
            ),
            'gradient' => $this->svg_generator->generate_contrast_demo(
                'gradient',
                $foreground,
                $background,
                ['steps' => 5]
            )
        ];
    }

    /**
     * Generate custom previews
     */
    private function generate_custom_previews($foreground, $background, $custom_options) {
        $previews = [];

        foreach ($custom_options as $option) {
            $previews[$option['name']] = $this->generate_custom_preview(
                $foreground,
                $background,
                $option
            );
        }

        return $previews;
    }

    /**
     * Helper methods for preview generation
     */
    private function generate_text_sample($foreground, $background, $tag, $text, $styles) {
        $style_string = $this->css_generator->generate_inline_styles(array_merge(
            $styles,
            [
                'color' => $foreground,
                'background-color' => $background
            ]
        ));

        return sprintf(
            '<%1$s style="%2$s">%3$s</%1$s>',
            $tag,
            $style_string,
            esc_html($text)
        );
    }

    private function generate_ui_component($type, $foreground, $background, $options) {
        $template = $this->get_ui_template($type);
        $styles = $this->css_generator->generate_component_styles(
            $type,
            $foreground,
            $background,
            $options
        );

        return [
            'html' => sprintf($template, $styles['inline']),
            'css' => $styles['external'],
            'svg' => $this->svg_generator->generate_component_preview(
                $type,
                $foreground,
                $background,
                $options
            )
        ];
    }

    private function generate_simulation_preview($foreground, $background, $type) {
        return $this->svg_generator->generate_simulation_preview(
            $foreground,
            $background,
            [
                'type' => $type,
                'width' => 200,
                'height' => 100,
                'content' => __('Sample Text', 'color-palette-generator')
            ]
        );
    }

    private function get_ui_template($type) {
        $templates = [
            'card' => '<div class="preview-card" style="%s">
                        <h3>Card Title</h3>
                        <p>Card content goes here.</p>
                      </div>',
            'alert' => '<div class="preview-alert" style="%s">
                         <strong>Alert!</strong> This is an alert message.
                       </div>',
            'form' => '<form class="preview-form" style="%s">
                        <input type="text" placeholder="Input field">
                        <button type="submit">Submit</button>
                      </form>'
        ];

        return $templates[$type] ?? '';
    }
} 
