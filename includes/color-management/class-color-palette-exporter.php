<?php
/**
 * Color Palette Exporter Class
 *
 * Extends the Color_Palette_Formatter to provide additional export capabilities.
 * Supports all formats from Color_Palette_Formatter plus these additional formats:
 * - svg: SVG color swatches
 * - ase: Adobe Swatch Exchange format
 * - bootstrap: Bootstrap-specific SCSS variables
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\Color_Management;

use GL_Color_Palette_Generator\Color_Management\Color_Shade_Generator;
use GL_Color_Palette_Generator\Interfaces\Accessibility_Checker;
use GL_Color_Palette_Generator\Interfaces\Color_Palette_Exporter_Interface;
use GL_Color_Palette_Generator\Traits\Color_Shade_Generator_Trait;

/**
 * Class Color_Palette_Exporter
 * 
 * Exports color palettes to various file formats. Inherits basic formatting capabilities
 * from Color_Palette_Formatter and adds support for additional export-specific formats.
 */
class Color_Palette_Exporter extends Color_Palette_Formatter implements Color_Palette_Exporter_Interface {
    use Color_Shade_Generator_Trait;

    /**
     * Additional export-specific formats beyond those provided by Color_Palette_Formatter
     *
     * @see Color_Palette_Formatter::SUPPORTED_FORMATS for the base formatting options
     */
    protected const EXPORT_FORMATS = [
        'svg',      // SVG color swatches
        'ase',      // Adobe Swatch Exchange
        'bootstrap' // Bootstrap SCSS
    ];

    private $accessibility_checker;
    private $shade_generator;
    private $color_utility;

    /**
     * Constructor
     *
     * @param Accessibility_Checker $accessibility_checker Accessibility checker instance
     */
    public function __construct(Accessibility_Checker $accessibility_checker) {
        $this->accessibility_checker = $accessibility_checker;
        $this->shade_generator = new Color_Shade_Generator();
        $this->color_utility = new Color_Utility();
    }

    /**
     * Get all supported formats
     *
     * @return array List of supported formats
     */
    public function get_supported_formats(): array {
        return array_merge(parent::get_supported_formats(), self::EXPORT_FORMATS);
    }

    /**
     * Export color palette to specified format
     *
     * @param array  $palette Color palette data
     * @param string $format  Export format (css, scss, json, etc.)
     * @param array  $options Export options
     * @return string|array Exported palette in specified format
     * @throws \InvalidArgumentException If format is not supported
     */
    public function export_palette(array $palette, string $format, array $options = []): string|array {
        // First try parent formatter
        try {
            return $this->format_palette($palette, $format, $options);
        } catch (\Exception $e) {
            // If not a basic format, try export-specific formats
            if (!in_array($format, self::EXPORT_FORMATS, true)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        __('Unsupported export format: %s', 'gl-color-palette-generator'),
                        $format
                    )
                );
            }

            $method = 'to_' . $format;
            return $this->$method($palette, $options);
        }
    }

    /**
     * Export to SVG
     *
     * @param array $palette Color palette.
     * @param array $options Export options.
     * @return string SVG markup.
     */
    protected function to_svg(array $palette, array $options): string {
        $size = $options['size'] ?? 100;
        $gap = $options['gap'] ?? 10;
        $output = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d">',
            $size * count($palette),
            $size
        );

        $x = 0;
        foreach ($palette as $name => $color) {
            if ($color === null || $color === '') {
                continue;
            }

            $output .= sprintf(
                '<rect x="%d" y="0" width="%d" height="%d" fill="%s"><title>%s</title></rect>',
                $x,
                $size - $gap,
                $size - $gap,
                esc_attr($color),
                esc_attr($name)
            );
            $x += $size;
        }

        $output .= '</svg>';
        return $output;
    }

    /**
     * Export to Adobe Swatch Exchange (ASE)
     *
     * @param array $palette Color palette.
     * @param array $options Export options.
     * @return string Binary ASE file content.
     */
    protected function to_ase(array $palette, array $options): string {
        // ASE file header
        $output = pack('n*', 0xAEFF, 0x0001, 0x0000, count($palette));

        foreach ($palette as $name => $color) {
            if ($color === null || $color === '') {
                continue;
            }

            $rgb = $this->color_utility->hex_to_rgb($color);
            $name = substr($name, 0, 32); // ASE name length limit

            // Color entry
            $output .= pack('n', 0x0001); // Entry start
            $output .= pack('n', strlen($name) + 1); // Name length + null
            $output .= $name . "\0"; // Name + null terminator
            $output .= pack('CCCCCCCCCCCC',
                0x52, 0x47, 0x42, 0x20, // "RGB "
                $rgb['r'], 0, // R
                $rgb['g'], 0, // G
                $rgb['b'], 0, // B
                0, 0  // Mode
            );
        }

        return $output;
    }

    /**
     * Export to Bootstrap SCSS variables
     *
     * @param array $palette Color palette.
     * @param array $options Export options.
     * @return string Bootstrap SCSS variables.
     */
    protected function to_bootstrap(array $palette, array $options): string {
        $output = "// Custom color variables\n";

        foreach ($palette as $name => $color) {
            if ($color === null || $color === '') {
                continue;
            }

            $varName = sanitize_title($name);
            $output .= "\$theme-colors: (\n";
            $output .= "  \"{$varName}\": {$color},\n";
            $output .= ");\n";
        }

        return $output;
    }

    /**
     * Export palettes to JSON
     *
     * @param array $palettes Array of palettes to export.
     * @return string JSON string.
     */
    public function export_to_json($palettes): string {
        $export_data = [
            'version' => GL_CPG_VERSION,
            'exported_at' => current_time('mysql'),
            'palettes' => $palettes
        ];

        return wp_json_encode($export_data, JSON_PRETTY_PRINT);
    }

    /**
     * Export palettes to CSV
     *
     * @param array $palettes Array of palettes to export.
     * @return string CSV string.
     */
    public function export_to_csv($palettes): string {
        if (!function_exists('esc_csv')) {
            /**
             * Escape CSV field
             *
             * @param string $field Field to escape
             * @return string Escaped field
             */
            function esc_csv($field) {
                $field = str_replace('"', '""', $field);
                return '"' . $field . '"';
            }
        }

        $csv = "Name,Colors,Created At\n";

        foreach ($palettes as $palette) {
            $csv .= sprintf(
                '%s,%s,%s' . "\n",
                esc_csv($palette['name']),
                esc_csv(implode(', ', $palette['colors'])),
                esc_csv($palette['created_at'])
            );
        }

        return $csv;
    }

    /**
     * Import palettes from JSON
     *
     * @param string $json JSON string to import.
     * @return array Imported palettes.
     * @throws \Exception If import fails.
     */
    public function import_from_json($json): array {
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception(__('Invalid JSON format', 'gl-color-palette-generator'));
        }

        if (!isset($data['palettes']) || !is_array($data['palettes'])) {
            throw new \Exception(__('Invalid palette data format', 'gl-color-palette-generator'));
        }

        return $data['palettes'];
    }
}
