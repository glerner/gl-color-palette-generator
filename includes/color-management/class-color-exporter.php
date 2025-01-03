<?php
/**
 * Color Exporter Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Color_Management
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Color_Management;

use GL_Color_Palette_Generator\Core\Abstract_Component;

/**
 * Class Color_Exporter
 * 
 * Exports color palettes in various formats
 */
class Color_Exporter extends Abstract_Component {
    /**
     * Constructor
     */
    public function __construct() {
        $this->set_name('color_exporter');
        $this->set_version('1.0.0');
    }

    /**
     * Initialize the component
     *
     * @return bool True if initialization was successful
     */
    public function init(): bool {
        add_action('wp_ajax_gl_export_palette', [$this, 'ajax_export']);
        return true;
    }

    /**
     * AJAX handler for palette export
     */
    public function ajax_export(): void {
        check_ajax_referer('gl_color_palette_generator', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $colors = filter_input(INPUT_POST, 'colors', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $format = filter_input(INPUT_POST, 'format', FILTER_SANITIZE_STRING);

        if (!$colors || !$format) {
            wp_send_json_error('Missing required parameters');
        }

        $exported = $this->export_palette($colors, $format);
        wp_send_json_success(['content' => $exported]);
    }

    /**
     * Export a color palette
     *
     * @param array  $colors Array of color values
     * @param string $format Export format (css, scss, json)
     * @return string Exported content
     */
    public function export_palette(array $colors, string $format): string {
        switch ($format) {
            case 'css':
                return $this->export_as_css($colors);
            case 'scss':
                return $this->export_as_scss($colors);
            case 'json':
                return $this->export_as_json($colors);
            default:
                return '';
        }
    }

    /**
     * Export palette as CSS variables
     *
     * @param array $colors Array of color values
     * @return string CSS content
     */
    private function export_as_css(array $colors): string {
        $css = ":root {\n";
        foreach ($colors as $i => $color) {
            $css .= sprintf("  --color-%d: %s;\n", $i + 1, $color);
        }
        $css .= "}\n";
        return $css;
    }

    /**
     * Export palette as SCSS variables
     *
     * @param array $colors Array of color values
     * @return string SCSS content
     */
    private function export_as_scss(array $colors): string {
        $scss = "";
        foreach ($colors as $i => $color) {
            $scss .= sprintf("\$color-%d: %s;\n", $i + 1, $color);
        }
        return $scss;
    }

    /**
     * Export palette as JSON
     *
     * @param array $colors Array of color values
     * @return string JSON content
     */
    private function export_as_json(array $colors): string {
        $palette = [];
        foreach ($colors as $i => $color) {
            $palette["color-" . ($i + 1)] = $color;
        }
        return wp_json_encode($palette, JSON_PRETTY_PRINT);
    }
}
