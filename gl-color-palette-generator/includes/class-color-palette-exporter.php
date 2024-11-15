<?php
/**
 * Color Palette Exporter
 *
 * @package GLColorPalette
 * @since 1.0.0
 */

namespace GLColorPalette;

/**
 * Class Color_Palette_Exporter
 *
 * Exports color palettes in various formats:
 * - CSS Variables
 * - SCSS Variables
 * - JSON
 * - Adobe ASE
 * - Sketch Palette
 * - SVG Swatches
 *
 * @since 1.0.0
 */
class Color_Palette_Exporter {
    /**
     * Export formats supported by the exporter
     * @var array
     */
    private $supported_formats = [
        'css',
        'scss',
        'json',
        'ase',
        'sketch',
        'svg'
    ];

    /**
     * Export a palette to a specific format
     *
     * @param Color_Palette $palette Palette to export
     * @param string       $format  Format to export to
     * @param array        $options Optional export options
     * @return string|array|WP_Error Exported palette or error
     */
    public function export(Color_Palette $palette, string $format, array $options = []) {
        if (!in_array($format, $this->supported_formats)) {
            return new \WP_Error(
                'invalid_format',
                sprintf(
                    __('Unsupported format: %s. Supported formats: %s', 'gl-color-palette-generator'),
                    $format,
                    implode(', ', $this->supported_formats)
                )
            );
        }

        $method = "to_{$format}";
        return $this->$method($palette, $options);
    }

    /**
     * Export to CSS variables
     *
     * @param Color_Palette $palette Palette to export
     * @param array        $options Export options
     * @return string CSS variables
     */
    private function to_css(Color_Palette $palette, array $options = []): string {
        $prefix = $options['prefix'] ?? 'color';
        $colors = $palette->get_colors();
        $css = ":root {\n";

        foreach ($colors as $index => $color) {
            $name = $this->generate_color_name($color, $index, $palette);
            $css .= "    --{$prefix}-{$name}: {$color};\n";
        }

        $css .= "}\n";
        return $css;
    }

    /**
     * Export to SCSS variables
     *
     * @param Color_Palette $palette Palette to export
     * @param array        $options Export options
     * @return string SCSS variables
     */
    private function to_scss(Color_Palette $palette, array $options = []): string {
        $prefix = $options['prefix'] ?? 'color';
        $colors = $palette->get_colors();
        $scss = "";

        foreach ($colors as $index => $color) {
            $name = $this->generate_color_name($color, $index, $palette);
            $scss .= "\${$prefix}-{$name}: {$color};\n";
        }

        return $scss;
    }

    /**
     * Export to JSON format
     *
     * @param Color_Palette $palette Palette to export
     * @param array        $options Export options
     * @return string JSON representation
     */
    private function to_json(Color_Palette $palette, array $options = []): string {
        $data = [
            'name' => $palette->get_metadata('name'),
            'colors' => array_map(function($color, $index) use ($palette) {
                return [
                    'name' => $this->generate_color_name($color, $index, $palette),
                    'hex' => $color,
                    'rgb' => $this->hex_to_rgb($color)
                ];
            }, $palette->get_colors(), array_keys($palette->get_colors())),
            'metadata' => $palette->get_metadata()
        ];

        return wp_json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Export to Adobe ASE format
     *
     * @param Color_Palette $palette Palette to export
     * @param array        $options Export options
     * @return string Binary ASE file content
     */
    private function to_ase(Color_Palette $palette, array $options = []): string {
        // ASE file header
        $content = "ASEF"; // Signature
        $content .= pack("n", 1); // Version
        $content .= pack("N", count($palette->get_colors())); // Blocks count

        foreach ($palette->get_colors() as $index => $color) {
            $name = $this->generate_color_name($color, $index, $palette);
            $rgb = $this->hex_to_rgb($color);

            // Block header
            $content .= pack("n", 0x0001); // Color entry
            $content .= pack("N", 24 + strlen($name)); // Block length

            // Color name (Unicode)
            $content .= pack("n", strlen($name)); // String length
            $content .= mb_convert_encoding($name, 'UTF-16BE', 'UTF-8');
            $content .= pack("n", 0); // Null terminator

            // Color model (RGB)
            $content .= "RGB ";

            // Color values (32-bit float)
            $content .= pack("f", $rgb['r'] / 255);
            $content .= pack("f", $rgb['g'] / 255);
            $content .= pack("f", $rgb['b'] / 255);

            // Color type (global)
            $content .= pack("n", 0);
        }

        return $content;
    }

    /**
     * Export to Sketch palette format
     *
     * @param Color_Palette $palette Palette to export
     * @param array        $options Export options
     * @return string JSON for Sketch
     */
    private function to_sketch(Color_Palette $palette, array $options = []): string {
        $data = [
            'compatibleVersion' => '2.0',
            'pluginVersion' => '2.29',
            'colors' => array_map(function($color) {
                $rgb = $this->hex_to_rgb($color);
                return [
                    'red' => $rgb['r'] / 255,
                    'green' => $rgb['g'] / 255,
                    'blue' => $rgb['b'] / 255,
                    'alpha' => 1
                ];
            }, $palette->get_colors()),
            'gradients' => [],
            'images' => []
        ];

        return wp_json_encode($data);
    }

    /**
     * Export to SVG swatches
     *
     * @param Color_Palette $palette Palette to export
     * @param array        $options Export options
     * @return string SVG content
     */
    private function to_svg(Color_Palette $palette, array $options = []): string {
        $width = $options['width'] ?? 100;
        $height = $options['height'] ?? 100;
        $spacing = $options['spacing'] ?? 10;
        $colors = $palette->get_colors();
        $total_width = count($colors) * ($width + $spacing) - $spacing;

        $svg = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $svg .= '<svg xmlns="http://www.w3.org/2000/svg" ';
        $svg .= 'width="' . $total_width . '" height="' . $height . '">' . "\n";

        foreach ($colors as $index => $color) {
            $x = $index * ($width + $spacing);
            $name = $this->generate_color_name($color, $index, $palette);

            $svg .= '  <rect x="' . $x . '" y="0" ';
            $svg .= 'width="' . $width . '" height="' . $height . '" ';
            $svg .= 'fill="' . $color . '">';
            $svg .= '<title>' . esc_html($name) . '</title>';
            $svg .= '</rect>' . "\n";
        }

        $svg .= '</svg>';
        return $svg;
    }

    /**
     * Generate a semantic color name
     *
     * @param string        $color   Hex color
     * @param int          $index   Color index
     * @param Color_Palette $palette Palette object
     * @return string Generated name
     */
    private function generate_color_name(string $color, int $index, Color_Palette $palette): string {
        $metadata = $palette->get_metadata();
        $theme = sanitize_title($metadata['theme'] ?? '');

        if (empty($theme)) {
            return "color-" . ($index + 1);
        }

        return $theme . "-" . ($index + 1);
    }

    /**
     * Convert hex color to RGB values
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
