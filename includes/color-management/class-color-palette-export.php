<?php
/**
 * Color Palette Export
 *
 * Handles exporting color palettes in various formats (CSS, SCSS, JSON, etc.)
 *
 * @package GL_Color_Palette_Generator
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Color_Management;

/**
 * Class Color_Palette_Export
 */
class Color_Palette_Export {
    /**
     * Supported export formats
     * @var array
     */
    private const SUPPORTED_FORMATS = [
        'css', 'scss', 'json', 'svg',
        'tailwind', 'bootstrap'
    ];

    /**
     * Export a palette to specified format
     *
     * @param Color_Palette $palette Palette to export.
     * @param string       $format  Export format.
     * @param array        $options Export options.
     * @return string|array Exported content.
     * @throws \InvalidArgumentException If format is not supported.
     */
    public function export(
        Color_Palette $palette,
        string $format,
        array $options = []
    ): string|array {
        if (!in_array($format, self::SUPPORTED_FORMATS)) {
            throw new \InvalidArgumentException(
                sprintf(
                    __('Unsupported export format: %s', 'gl-color-palette-generator'),
                    $format
                )
            );
        }

        $method = "to_{$format}";
        return $this->$method($palette, $options);
    }

    /**
     * Export to CSS
     *
     * @param Color_Palette $palette Palette to export.
     * @param array        $options Export options.
     * @return string CSS variables.
     */
    private function to_css(Color_Palette $palette, array $options = []): string {
        $prefix = $options['prefix'] ?? 'color';
        $scope = $options['scope'] ?? ':root';
        $colors = $palette->get_colors();

        $css = "$scope {\n";
        foreach ($colors as $index => $color) {
            $name = $palette->get_metadata("name_$index") ?? $index + 1;
            $css .= "    --$prefix-$name: $color;\n";
        }
        $css .= "}\n";

        return $css;
    }

    /**
     * Export to SCSS
     *
     * @param Color_Palette $palette Palette to export.
     * @param array        $options Export options.
     * @return string SCSS variables.
     */
    private function to_scss(Color_Palette $palette, array $options = []): string {
        $prefix = $options['prefix'] ?? 'color';
        $colors = $palette->get_colors();
        $scss = "";

        foreach ($colors as $index => $color) {
            $name = $palette->get_metadata("name_$index") ?? $index + 1;
            $scss .= "\$$prefix-$name: $color;\n";
        }

        if (!empty($options['generate_map'])) {
            $scss .= "\n\$colors: (\n";
            foreach ($colors as $index => $color) {
                $name = $palette->get_metadata("name_$index") ?? $index + 1;
                $scss .= "    '$name': \$$prefix-$name,\n";
            }
            $scss .= ");\n";
        }

        return $scss;
    }

    /**
     * Export to JSON
     *
     * @param Color_Palette $palette Palette to export.
     * @param array        $options Export options.
     * @return string JSON representation.
     */
    private function to_json(Color_Palette $palette, array $options = []): string {
        $data = [
            'colors' => $palette->get_colors(),
            'metadata' => $palette->get_metadata()
        ];

        if (!empty($options['named_colors'])) {
            $data['named_colors'] = [];
            foreach ($palette->get_colors() as $index => $color) {
                $name = $palette->get_metadata("name_$index") ?? $index + 1;
                $data['named_colors'][$name] = $color;
            }
        }

        $flags = JSON_PRETTY_PRINT;
        if (!empty($options['minimize'])) {
            $flags = 0;
        }

        return json_encode($data, $flags);
    }

    /**
     * Export to SVG
     *
     * @param Color_Palette $palette Palette to export.
     * @param array        $options Export options.
     * @return string SVG representation.
     */
    private function to_svg(Color_Palette $palette, array $options = []): string {
        $width = $options['width'] ?? 300;
        $height = $options['height'] ?? 50;
        $colors = $palette->get_colors();
        $count = count($colors);
        $segment_width = $width / $count;

        $svg = "<svg width=\"$width\" height=\"$height\" xmlns=\"http://www.w3.org/2000/svg\">\n";
        foreach ($colors as $index => $color) {
            $x = $index * $segment_width;
            $svg .= "    <rect x=\"$x\" y=\"0\" width=\"$segment_width\" " .
                   "height=\"$height\" fill=\"$color\" />\n";
        }
        $svg .= "</svg>";

        return $svg;
    }

    /**
     * Export to Tailwind config
     *
     * @param Color_Palette $palette Palette to export.
     * @param array        $options Export options.
     * @return string Tailwind configuration.
     */
    private function to_tailwind(Color_Palette $palette, array $options = []): string {
        $colors = $palette->get_colors();
        $js = "module.exports = {\n  theme: {\n    extend: {\n      colors: {\n";

        foreach ($colors as $index => $color) {
            $name = $palette->get_metadata("name_$index") ?? $index + 1;
            $js .= "        '$name': '$color',\n";
        }

        $js .= "      }\n    }\n  }\n};\n";

        return $js;
    }

    /**
     * Export to Bootstrap SCSS variables
     *
     * @param Color_Palette $palette Palette to export.
     * @param array        $options Export options.
     * @return string Bootstrap SCSS variables.
     */
    private function to_bootstrap(Color_Palette $palette, array $options = []): string {
        $colors = $palette->get_colors();
        $scss = "/ Custom color variables\n";

        foreach ($colors as $index => $color) {
            $name = $palette->get_metadata("name_$index") ?? $index + 1;
            $scss .= "\$color-$name: $color;\n";
        }

        if (!empty($options['generate_theme'])) {
            $scss .= "\n/ Theme color variables\n";
            $scss .= "\$theme-colors: (\n";
            foreach ($colors as $index => $color) {
                $name = $palette->get_metadata("name_$index") ?? $index + 1;
                $scss .= "    '$name': \$color-$name,\n";
            }
            $scss .= ");\n";
        }

        if (!empty($options['generate_utilities'])) {
            $scss .= "\n/ Utility classes\n";
            foreach ($colors as $index => $color) {
                $name = $palette->get_metadata("name_$index") ?? $index + 1;
                $scss .= ".bg-$name { background-color: \$color-$name !important; }\n";
                $scss .= ".text-$name { color: \$color-$name !important; }\n";
            }
        }

        return $scss;
    }

    /**
     * Export to CSS-in-JS object
     *
     * @param Color_Palette $palette Palette to export.
     * @param array        $options Export options.
     * @return string JavaScript object.
     */
    private function to_js(Color_Palette $palette, array $options = []): string {
        $colors = $palette->get_colors();
        $js = "export const colors = {\n";

        foreach ($colors as $index => $color) {
            $name = $palette->get_metadata("name_$index") ?? $index + 1;
            $js .= "  $name: '$color',\n";
        }

        $js .= "};\n";

        if (!empty($options['generate_theme'])) {
            $js .= "\nexport const theme = {\n  colors,\n};\n";
        }

        return $js;
    }

    /**
     * Export to Adobe Swatch Exchange (ASE)
     *
     * @param Color_Palette $palette Palette to export.
     * @param array        $options Export options.
     * @return string Binary ASE file content.
     */
    private function to_ase(Color_Palette $palette, array $options = []): string {
        $colors = $palette->get_colors();
        $binary = "ASEF\x00\x01\x00\x00";
        $binary .= pack("n", count($colors)); / Number of colors

        foreach ($colors as $index => $color) {
            $name = $palette->get_metadata("name_$index") ?? "Color " . ($index + 1);
            $rgb = $this->hex_to_rgb($color);

            / Block start
            $binary .= "\x00\x01"; / Color entry

            / Color name
            $binary .= pack("n", strlen($name) + 1);
            $binary .= $name . "\x00";

            / Color model (RGB)
            $binary .= "RGB ";

            / Color values
            $binary .= pack("f*",
                $rgb[0] / 255,
                $rgb[1] / 255,
                $rgb[2] / 255
            );

            / Color type (global)
            $binary .= "\x00\x00";
        }

        return $binary;
    }

    /**
     * Convert hex to RGB
     *
     * @param string $hex Hex color code.
     * @return array RGB values [r, g, b].
     */
    private function hex_to_rgb(string $hex): array {
        $hex = ltrim($hex, '#');
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        ];
    }
} 
