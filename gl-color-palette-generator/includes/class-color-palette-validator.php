<?php
/**
 * Color Palette Validator
 *
 * @package GLColorPalette
 * @since 1.0.0
 */

namespace GLColorPalette;

/**
 * Class Color_Palette_Validator
 *
 * Validates colors and color palettes according to defined rules.
 *
 * @since 1.0.0
 */
class Color_Palette_Validator {
    /**
     * Validate a hex color code
     *
     * @param string $color Color code to validate
     * @return bool Whether the color is valid
     */
    public function is_valid_hex_color(string $color): bool {
        return (bool) preg_match('/^#[a-fA-F0-9]{6}$/', $color);
    }

    /**
     * Validate a color palette
     *
     * @param Color_Palette $palette Palette to validate
     * @param array $requirements Optional validation requirements
     * @return array|WP_Error Array of validation messages or WP_Error on failure
     */
    public function validate_palette($palette, array $requirements = []) {
        $default_requirements = [
            'min_colors' => 1,
            'max_colors' => 10,
            'require_metadata' => ['name', 'theme'],
            'max_name_length' => 100,
            'max_description_length' => 500
        ];

        $requirements = wp_parse_args($requirements, $default_requirements);
        $errors = [];

        // Validate colors
        $colors = $palette->get_colors();
        if (count($colors) < $requirements['min_colors']) {
            $errors[] = sprintf(
                __('Palette must have at least %d colors', 'gl-color-palette-generator'),
                $requirements['min_colors']
            );
        }

        if (count($colors) > $requirements['max_colors']) {
            $errors[] = sprintf(
                __('Palette cannot have more than %d colors', 'gl-color-palette-generator'),
                $requirements['max_colors']
            );
        }

        foreach ($colors as $color) {
            if (!$this->is_valid_hex_color($color)) {
                $errors[] = sprintf(
                    __('Invalid color code: %s', 'gl-color-palette-generator'),
                    $color
                );
            }
        }

        // Validate metadata
        $metadata = $palette->get_metadata();
        foreach ($requirements['require_metadata'] as $field) {
            if (empty($metadata[$field])) {
                $errors[] = sprintf(
                    __('Missing required metadata: %s', 'gl-color-palette-generator'),
                    $field
                );
            }
        }

        if (isset($metadata['name']) && strlen($metadata['name']) > $requirements['max_name_length']) {
            $errors[] = sprintf(
                __('Name cannot be longer than %d characters', 'gl-color-palette-generator'),
                $requirements['max_name_length']
            );
        }

        if (isset($metadata['description']) && strlen($metadata['description']) > $requirements['max_description_length']) {
            $errors[] = sprintf(
                __('Description cannot be longer than %d characters', 'gl-color-palette-generator'),
                $requirements['max_description_length']
            );
        }

        return empty($errors) ? true : new \WP_Error('invalid_palette', __('Invalid palette', 'gl-color-palette-generator'), $errors);
    }

    /**
     * Validate generation parameters
     *
     * @param array $params Parameters to validate
     * @return bool|WP_Error True if valid, WP_Error if invalid
     */
    public function validate_generation_params(array $params) {
        $required_params = ['theme', 'count'];
        $errors = [];

        foreach ($required_params as $param) {
            if (!isset($params[$param])) {
                $errors[] = sprintf(
                    __('Missing required parameter: %s', 'gl-color-palette-generator'),
                    $param
                );
            }
        }

        if (isset($params['count'])) {
            if (!is_numeric($params['count']) || $params['count'] < 1 || $params['count'] > 10) {
                $errors[] = __('Count must be between 1 and 10', 'gl-color-palette-generator');
            }
        }

        if (isset($params['theme']) && empty(trim($params['theme']))) {
            $errors[] = __('Theme cannot be empty', 'gl-color-palette-generator');
        }

        return empty($errors) ? true : new \WP_Error('invalid_params', __('Invalid parameters', 'gl-color-palette-generator'), $errors);
    }
} 
