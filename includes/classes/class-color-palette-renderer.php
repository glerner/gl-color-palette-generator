<?php

namespace GLColorPalette;

/**
 * Color Palette Renderer Class
 *
 * Handles rendering of color palettes in various formats and contexts.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
class ColorPaletteRenderer {
    /**
     * Default render options.
     *
     * @var array
     */
    private $default_options = [
        'format' => 'html',
        'template' => 'grid',
        'show_labels' => true,
        'show_values' => true,
        'class_prefix' => 'gl-palette',
        'container_class' => '',
        'swatch_size' => 'medium',
        'accessibility' => true
    ];

    /**
     * Renders a color palette.
     *
     * @param ColorPalette $palette Palette to render.
     * @param array $options {
     *     Optional. Render options.
     *     @type string $format          Output format (html|css|json).
     *     @type string $template        Layout template.
     *     @type bool   $show_labels     Show color labels.
     *     @type bool   $show_values     Show color values.
     *     @type string $class_prefix    CSS class prefix.
     *     @type string $container_class Additional container classes.
     *     @type string $swatch_size     Swatch size (small|medium|large).
     *     @type bool   $accessibility   Include accessibility attributes.
     * }
     * @return string Rendered output.
     * @throws \InvalidArgumentException If format is invalid.
     */
    public function render(ColorPalette $palette, array $options = []): string {
        $options = array_merge($this->default_options, $options);

        switch ($options['format']) {
            case 'html':
                return $this->render_html($palette, $options);
            case 'css':
                return $this->render_css($palette, $options);
            case 'json':
                return $this->render_json($palette, $options);
            default:
                throw new \InvalidArgumentException("Invalid render format: {$options['format']}");
        }
    }

    /**
     * Renders palette as HTML.
     *
     * @param ColorPalette $palette Palette to render.
     * @param array $options Render options.
     * @return string HTML output.
     */
    private function render_html(ColorPalette $palette, array $options): string {
        $container_class = trim("{$options['class_prefix']}-container {$options['container_class']}");
        $template_class = "{$options['class_prefix']}-{$options['template']}";
        $size_class = "{$options['class_prefix']}-{$options['swatch_size']}";

        $output = "<div class=\"{$container_class} {$template_class} {$size_class}\">\n";

        if ($options['show_labels']) {
            $output .= "  <h3 class=\"{$options['class_prefix']}-title\">" .
                      esc_html($palette->get_name()) . "</h3>\n";
        }

        $output .= "  <div class=\"{$options['class_prefix']}-swatches\">\n";

        foreach ($palette->get_colors() as $index => $color) {
            $output .= $this->render_swatch($color, $index, $options);
        }

        $output .= "  </div>\n</div>";

        return $output;
    }

    /**
     * Renders individual color swatch.
     *
     * @param string $color Color value.
     * @param int $index Color index.
     * @param array $options Render options.
     * @return string Swatch HTML.
     */
    private function render_swatch(string $color, int $index, array $options): string {
        $swatch_class = "{$options['class_prefix']}-swatch";
        $style = "background-color: " . esc_attr($color);
        $attrs = $options['accessibility'] ?
            "role=\"listitem\" aria-label=\"Color {$index}: {$color}\"" : '';

        $output = "    <div class=\"{$swatch_class}\" style=\"{$style}\" {$attrs}>\n";

        if ($options['show_values']) {
            $output .= "      <span class=\"{$options['class_prefix']}-value\">" .
                      esc_html($color) . "</span>\n";
        }

        $output .= "    </div>\n";

        return $output;
    }

    /**
     * Renders palette as CSS.
     *
     * @param ColorPalette $palette Palette to render.
     * @param array $options Render options.
     * @return string CSS output.
     */
    private function render_css(ColorPalette $palette, array $options): string {
        $prefix = sanitize_title($palette->get_name());
        $output = "/* {$palette->get_name()} */\n:root {\n";

        foreach ($palette->get_colors() as $index => $color) {
            $variable = "--{$prefix}-color-" . ($index + 1);
            $output .= "  {$variable}: {$color};\n";
        }

        $output .= "}\n";
        return $output;
    }

    /**
     * Renders palette as JSON.
     *
     * @param ColorPalette $palette Palette to render.
     * @param array $options Render options.
     * @return string JSON output.
     */
    private function render_json(ColorPalette $palette, array $options): string {
        $data = [
            'name' => $palette->get_name(),
            'colors' => $palette->get_colors(),
            'metadata' => $palette->get_metadata()
        ];

        return wp_json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Gets supported render formats.
     *
     * @return array Array of supported formats.
     */
    public function get_supported_formats(): array {
        return ['html', 'css', 'json'];
    }

    /**
     * Gets supported templates.
     *
     * @return array Array of supported templates.
     */
    public function get_supported_templates(): array {
        return ['grid', 'list', 'circle', 'compact'];
    }

    /**
     * Gets supported swatch sizes.
     *
     * @return array Array of supported sizes.
     */
    public function get_supported_sizes(): array {
        return ['small', 'medium', 'large'];
    }
} 
