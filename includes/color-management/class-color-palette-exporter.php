<?php
/**
 * Color Palette Exporter Class
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\Color_Management;

/**
 * Class Color_Palette_Exporter
 */
class Color_Palette_Exporter implements \GL_Color_Palette_Generator\Interfaces\Color_Palette_Exporter {
    /**
     * Export palettes to JSON
     *
     * @param array $palettes Array of palettes to export.
     * @return string JSON string.
     */
    public function export_to_json($palettes) {
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
    public function export_to_csv($palettes) {
        $csv = "Name,Colors,Created At\n";

        foreach ($palettes as $palette) {
            $csv .= sprintf(
                '"%s","%s","%s"' . "\n",
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
    public function import_from_json($json) {
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception(__('Invalid JSON format', 'gl-color-palette-generator'));
        }

        if (!isset($data['version']) || !isset($data['palettes'])) {
            throw new \Exception(__('Invalid export file format', 'gl-color-palette-generator'));
        }

        return $this->validate_imported_palettes($data['palettes']);
    }

    /**
     * Import palettes from CSV
     *
     * @param string $csv CSV string to import.
     * @return array Imported palettes.
     * @throws \Exception If import fails.
     */
    public function import_from_csv($csv) {
        $lines = array_map('str_getcsv', explode("\n", $csv));
        $headers = array_shift($lines);

        if ($headers !== ['Name', 'Colors', 'Created At']) {
            throw new \Exception(__('Invalid CSV format', 'gl-color-palette-generator'));
        }

        $palettes = [];
        foreach ($lines as $line) {
            if (count($line) !== 3) continue;

            $palettes[] = [
                'name' => $line[0],
                'colors' => array_map('trim', explode(',', $line[1])),
                'created_at' => $line[2]
            ];
        }

        return $this->validate_imported_palettes($palettes);
    }

    /**
     * Validate imported palettes
     *
     * @param array $palettes Palettes to validate.
     * @return array Validated palettes.
     * @throws \Exception If validation fails.
     */
    private function validate_imported_palettes($palettes) {
        $color_utility = new Color_Utility();

        foreach ($palettes as $palette) {
            if (!isset($palette['name']) || !isset($palette['colors'])) {
                throw new \Exception(__('Invalid palette format', 'gl-color-palette-generator'));
            }

            if (!is_array($palette['colors']) || count($palette['colors']) !== 5) {
                throw new \Exception(__('Invalid number of colors', 'gl-color-palette-generator'));
            }

            foreach ($palette['colors'] as $color) {
                if (!preg_match('/^#[0-9a-f]{6}$/i', $color)) {
                    throw new \Exception(__('Invalid color format', 'gl-color-palette-generator'));
                }
            }

            if (!$color_utility->are_colors_distinct($palette['colors'])) {
                throw new \Exception(__('Colors are not visually distinct', 'gl-color-palette-generator'));
            }
        }

        return $palettes;
    }
}
