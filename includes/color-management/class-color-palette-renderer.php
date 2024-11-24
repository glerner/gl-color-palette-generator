<?php
/**
 * Color Palette Renderer
 *
 * @package GLColorPalette
 * @since 1.0.0
 */

namespace GLColorPalette;

/**
 * Class Color_Palette_Renderer
 *
 * Renders color palettes in various visual formats:
 * - HTML swatches
 * - Grid layout
 * - Preview cards
 * - Color information displays
 *
 * @since 1.0.0
 */
class Color_Palette_Renderer {
    /**
     * Render a palette as HTML
     *
     * @param Color_Palette $palette Palette to render
     * @param array        $options Rendering options
     * @return string HTML output
     */
    public function render(Color_Palette $palette, array $options = []): string {
        $default_options = [
            'layout' => 'swatches', / swatches, grid, cards
            'show_info' => true,
            'show_names' => true,
            'show_values' => true,
            'size' => 'medium', / small, medium, large
            'class' => '',
        ];

        $options = wp_parse_args($options, $default_options);
        $method = "render_{$options['layout']}";

        if (!method_exists($this, $method)) {
            return '';
        }

        $wrapper_class = 'gl-color-palette';
        $wrapper_class .= ' gl-color-palette--' . $options['layout'];
        $wrapper_class .= ' gl-color-palette--' . $options['size'];
        $wrapper_class .= ' ' . $options['class'];

        $html = sprintf(
            '<div class="%s" data-palette-id="%s">',
            esc_attr($wrapper_class),
            esc_attr($palette->get_metadata('id') ?? '')
        );

        if ($options['show_info']) {
            $html .= $this->render_palette_info($palette);
        }

        $html .= $this->$method($palette, $options);
        $html .= '</div>';

        return $html;
    }

    /**
     * Render palette as swatches
     *
     * @param Color_Palette $palette Palette to render
     * @param array        $options Rendering options
     * @return string HTML output
     */
    private function render_swatches(Color_Palette $palette, array $options): string {
        $html = '<div class="gl-color-palette__swatches">';

        foreach ($palette->get_colors() as $index => $color) {
            $html .= $this->render_swatch($color, $index, $palette, $options);
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Render palette as grid
     *
     * @param Color_Palette $palette Palette to render
     * @param array        $options Rendering options
     * @return string HTML output
     */
    private function render_grid(Color_Palette $palette, array $options): string {
        $html = '<div class="gl-color-palette__grid">';

        foreach ($palette->get_colors() as $index => $color) {
            $html .= sprintf(
                '<div class="gl-color-palette__grid-item" style="background-color: %s;">',
                esc_attr($color)
            );

            if ($options['show_info']) {
                $html .= $this->render_color_info($color, $index, $palette, $options);
            }

            $html .= '</div>';
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Render palette as cards
     *
     * @param Color_Palette $palette Palette to render
     * @param array        $options Rendering options
     * @return string HTML output
     */
    private function render_cards(Color_Palette $palette, array $options): string {
        $html = '<div class="gl-color-palette__cards">';

        foreach ($palette->get_colors() as $index => $color) {
            $html .= '<div class="gl-color-palette__card">';
            $html .= $this->render_swatch($color, $index, $palette, $options);
            $html .= $this->render_color_details($color, $index, $palette, $options);
            $html .= '</div>';
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Render individual swatch
     *
     * @param string        $color   Color hex value
     * @param int          $index   Color index
     * @param Color_Palette $palette Palette object
     * @param array        $options Rendering options
     * @return string HTML output
     */
    private function render_swatch(string $color, int $index, Color_Palette $palette, array $options): string {
        $html = sprintf(
            '<div class="gl-color-palette__swatch" style="background-color: %s;" data-color="%s">',
            esc_attr($color),
            esc_attr($color)
        );

        if ($options['show_info']) {
            $html .= $this->render_color_info($color, $index, $palette, $options);
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Render palette information
     *
     * @param Color_Palette $palette Palette to render info for
     * @return string HTML output
     */
    private function render_palette_info(Color_Palette $palette): string {
        $metadata = $palette->get_metadata();

        $html = '<div class="gl-color-palette__info">';

        if (!empty($metadata['name'])) {
            $html .= sprintf(
                '<h3 class="gl-color-palette__name">%s</h3>',
                esc_html($metadata['name'])
            );
        }

        if (!empty($metadata['description'])) {
            $html .= sprintf(
                '<p class="gl-color-palette__description">%s</p>',
                esc_html($metadata['description'])
            );
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Render color information
     *
     * @param string        $color   Color hex value
     * @param int          $index   Color index
     * @param Color_Palette $palette Palette object
     * @param array        $options Rendering options
     * @return string HTML output
     */
    private function render_color_info(string $color, int $index, Color_Palette $palette, array $options): string {
        $html = '<div class="gl-color-palette__color-info">';

        if ($options['show_names']) {
            $html .= sprintf(
                '<span class="gl-color-palette__color-name">%s</span>',
                esc_html($this->generate_color_name($color, $index, $palette))
            );
        }

        if ($options['show_values']) {
            $html .= sprintf(
                '<span class="gl-color-palette__color-value">%s</span>',
                esc_html($color)
            );
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Render detailed color information
     *
     * @param string        $color   Color hex value
     * @param int          $index   Color index
     * @param Color_Palette $palette Palette object
     * @param array        $options Rendering options
     * @return string HTML output
     */
    private function render_color_details(string $color, int $index, Color_Palette $palette, array $options): string {
        $rgb = $this->hex_to_rgb($color);

        $html = '<div class="gl-color-palette__color-details">';

        $html .= sprintf(
            '<div class="gl-color-palette__color-values">
                <div>HEX: %s</div>
                <div>RGB: %d, %d, %d</div>
            </div>',
            esc_html($color),
            $rgb['r'],
            $rgb['g'],
            $rgb['b']
        );

        $html .= '</div>';
        return $html;
    }

    /**
     * Generate color name
     *
     * @param string        $color   Color hex value
     * @param int          $index   Color index
     * @param Color_Palette $palette Palette object
     * @return string Generated name
     */
    private function generate_color_name(string $color, int $index, Color_Palette $palette): string {
        $metadata = $palette->get_metadata();
        $theme = sanitize_title($metadata['theme'] ?? '');

        if (empty($theme)) {
            return "Color " . ($index + 1);
        }

        return ucfirst($theme) . " " . ($index + 1);
    }

    /**
     * Convert hex to RGB
     *
     * @param string $color Hex color
     * @return array RGB values
     */
    private function hex_to_rgb(string $color): array {
        $color = ltrim($color, '#');
        return [
            'r' => hexdec(substr($color, 0, 2)),
            'g' => hexdec(substr($color, 2, 2)),
            'b' => hexdec(substr($color, 4, 2))
        ];
    }
}
