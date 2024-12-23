<?php
/**
 * Settings Validator Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Validation
 */

namespace GL_Color_Palette_Generator\Validation;

use WP_Error;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;

/**
 * Class Settings_Validator
 *
 * Validates and sanitizes plugin settings
 *
 * @since 1.0.0
 */
class Settings_Validator implements Color_Constants {
    /** @var array Validation rules and constraints */
    protected $rules;

    public function __construct() {
        $this->rules = [
            'naming_service' => [
                'type' => 'string',
                'enum' => ['local', 'openai', 'colorpizza']
            ],
            'accessibility_level' => [
                'type' => 'string',
                'enum' => ['AA', 'AAA']
            ],
            'color_scheme' => [
                'type' => 'string',
                'enum' => array_keys(self::COLOR_SCHEMES)
            ],
            'api_key' => [
                'type' => 'string',
                'required_if' => ['naming_service' => ['openai', 'colorpizza']]
            ],
            'cache_duration' => [
                'type' => 'int',
                'min' => 3600,    // 1 hour
                'max' => 2592000, // 30 days
                'default' => 86400
            ]
        ];
    }

    protected $errors = [];
    protected $warnings = [];
    protected $input = [];

    /**
     * Validate settings
     *
     * @param array $input Settings to validate
     * @return bool True if valid
     */
    public function validate($input) {
        $this->errors = [];
        $this->input = $input;

        try {
            // Validate basic settings
            $this->validate_naming_service($input);
            $this->validate_api_settings($input);
            $this->validate_cache_settings($input);
            $this->validate_color_scheme($input);

            // Validate export options
            if (isset($input['export_options'])) {
                $this->validate_export_options($input['export_options']);
            }

        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }

        return empty($this->errors);
    }

    /**
     * Validate color scheme setting
     */
    protected function validate_color_scheme($input) {
        if (!isset($input['color_scheme'])) {
            return;
        }

        if (!in_array($input['color_scheme'], array_keys(self::COLOR_SCHEMES))) {
            $this->errors[] = __('Invalid color scheme selected', 'gl-color-palette-generator');
        }
    }

    /**
     * Validate naming service settings
     */
    protected function validate_naming_service($input) {
        if (!isset($input['naming_service'])) {
            return;
        }

        if (!in_array($input['naming_service'], $this->rules['naming_service']['enum'])) {
            $this->errors[] = __('Invalid naming service selected', 'gl-color-palette-generator');
        }
    }

    /**
     * Validate API settings
     */
    protected function validate_api_settings($input) {
        if (isset($input['naming_service']) &&
            in_array($input['naming_service'], ['openai', 'colorpizza'])) {
            if (empty($input['api_key'])) {
                $this->errors[] = __('API key is required for the selected naming service', 'gl-color-palette-generator');
            }
        }
    }

    /**
     * Validate cache settings
     */
    protected function validate_cache_settings($input) {
        if (!isset($input['cache_duration'])) {
            return;
        }

        $duration = intval($input['cache_duration']);
        if ($duration < $this->rules['cache_duration']['min'] ||
            $duration > $this->rules['cache_duration']['max']) {
            $this->errors[] = __('Invalid cache duration', 'gl-color-palette-generator');
        }
    }

    /**
     * Validate export options
     */
    protected function validate_export_options($options) {
        if (!is_array($options)) {
            $this->errors[] = __('Invalid export options format', 'gl-color-palette-generator');
            return;
        }

        // Add any specific export option validation here
    }
}
