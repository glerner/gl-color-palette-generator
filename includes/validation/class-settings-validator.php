<?php
namespace GLColorPalette;

/**
 * Settings Validator Class
 *
 * Validates and sanitizes plugin settings
 */
class SettingsValidator {
    /** @var array Validation rules and constraints */
    private $rules = [
        'naming_service' => [
            'type' => 'string',
            'required' => true,
            'allowed_values' => ['local', 'openai', 'color_pizza', 'custom'],
            'default' => 'local'
        ],
        'api_key' => [
            'type' => 'string',
            'required_if' => ['naming_service' => ['openai', 'custom']]
        ],
        'accessibility' => [
            'min_contrast_ratio' => [
                'type' => 'float',
                'min' => 3.0,
                'max' => 21.0,
                'default' => 4.5
            ]
        ],
        'cache_duration' => [
            'type' => 'int',
            'min' => 3600, // 1 hour
            'max' => 2592000, // 30 days
            'required' => true
        ],
        'max_variations' => [
            'type' => 'int',
            'min' => 1,
            'max' => 50,
            'required' => true
        ]
    ];

    private $errors = [];
    private $warnings = [];

    /**
     * Validate settings
     *
     * @param array $input Settings to validate
     * @return array Validation result
     */
    public function validate($input) {
        $this->errors = [];
        $this->warnings = [];

        try {
            // Basic structure validation
            if (!is_array($input)) {
                throw new \Exception(__('Invalid settings format', 'color-palette-generator'));
            }

            // Validate naming service and API key
            $this->validate_naming_service($input);

            // Validate accessibility settings
            $this->validate_accessibility_settings($input);

            // Validate export options
            $this->validate_export_options($input);

            // Validate default colors
            $this->validate_default_colors($input);

            // Validate cache and performance settings
            $this->validate_performance_settings($input);

            // Check for potential conflicts
            $this->check_settings_conflicts($input);

            // Validate custom API settings if applicable
            if (isset($input['naming_service']) && $input['naming_service'] === 'custom') {
                $this->validate_custom_api_settings($input);
            }

        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
        }

        return [
            'is_valid' => empty($this->errors),
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'sanitized_data' => $this->sanitize_input($input)
        ];
    }

    /**
     * Validate naming service configuration
     *
     * @param array $input Settings to validate
     */
    private function validate_naming_service($input) {
        if (!isset($input['naming_service'])) {
            $this->errors[] = __('Naming service must be specified', 'color-palette-generator');
            return;
        }

        if (!in_array($input['naming_service'], $this->rules['naming_service']['allowed_values'])) {
            $this->errors[] = __('Invalid naming service selected', 'color-palette-generator');
            return;
        }

        // API key validation for services that require it
        if (in_array($input['naming_service'], ['openai', 'custom', 'color_pizza'])) {
            if (empty($input['api_key'])) {
                $this->errors[] = __('API key is required for the selected naming service', 'color-palette-generator');
            } elseif (strlen($input['api_key']) < $this->rules['api_key']['min_length']) {
                $this->errors[] = __('API key is too short', 'color-palette-generator');
            }
        }
    }

    /**
     * Validate accessibility settings
     *
     * @param array $input Settings to validate
     */
    private function validate_accessibility_settings($input) {
        if (!isset($input['accessibility']) || !is_array($input['accessibility'])) {
            $this->errors[] = __('Invalid accessibility settings format', 'color-palette-generator');
            return;
        }

        $accessibility = $input['accessibility'];

        // Validate contrast ratio
        if (isset($accessibility['min_contrast_ratio'])) {
            $ratio = floatval($accessibility['min_contrast_ratio']);
            if ($ratio < $this->rules['accessibility']['min_contrast_ratio']['min'] ||
                $ratio > $this->rules['accessibility']['min_contrast_ratio']['max']) {
                $this->errors[] = __('Invalid contrast ratio value', 'color-palette-generator');
            }
        }

        // Check for potential accessibility conflicts
        if (isset($accessibility['enforce_wcag_aaa']) && $accessibility['enforce_wcag_aaa'] &&
            (!isset($accessibility['enforce_wcag_aa']) || !$accessibility['enforce_wcag_aa'])) {
            $this->warnings[] = __('WCAG AAA compliance requires WCAG AA compliance', 'color-palette-generator');
        }
    }

    /**
     * Validate default colors
     *
     * @param array $input Settings to validate
     */
    private function validate_default_colors($input) {
        if (!isset($input['default_colors']) || !is_array($input['default_colors'])) {
            $this->errors[] = __('Invalid default colors format', 'color-palette-generator');
            return;
        }

        $required_roles = ['primary', 'secondary', 'tertiary', 'accent'];
        foreach ($required_roles as $role) {
            if (!isset($input['default_colors'][$role])) {
                $this->errors[] = sprintf(
                    __('Missing default color for %s role', 'color-palette-generator'),
                    $role
                );
                continue;
            }

            $color = $input['default_colors'][$role];
            if (!$this->is_valid_hex_color($color)) {
                $this->errors[] = sprintf(
                    __('Invalid hex color for %s role: %s', 'color-palette-generator'),
                    $role,
                    $color
                );
            }
        }

        // Check color contrast for default combinations
        if ($this->should_check_contrast($input)) {
            $this->validate_default_color_contrast($input['default_colors']);
        }
    }

    /**
     * Validate export options
     *
     * @param array $input Settings to validate
     */
    private function validate_export_options($input) {
        if (!isset($input['export_options']) || !is_array($input['export_options'])) {
            $this->errors[] = __('Invalid export options format', 'color-palette-generator');
            return;
        }

        $required_options = [
            'include_theme_json',
            'include_variations',
            'include_css',
            'minify_output'
        ];

        foreach ($required_options as $option) {
            if (!isset($input['export_options'][$option])) {
                $this->warnings[] = sprintf(
                    __('Missing export option: %s', 'color-palette-generator'),
                    $option
                );
            }
        }

        // Ensure at least one export format is selected
        if (empty(array_filter($input['export_options']))) {
            $this->errors[] = __('At least one export format must be selected', 'color-palette-generator');
        }
    }

    /**
     * Validate performance settings
     *
     * @param array $input Settings to validate
     */
    private function validate_performance_settings($input) {
        // Validate cache duration
        if (isset($input['cache_duration'])) {
            $duration = intval($input['cache_duration']);
            if ($duration < $this->rules['cache_duration']['min'] ||
                $duration > $this->rules['cache_duration']['max']) {
                $this->errors[] = __('Invalid cache duration', 'color-palette-generator');
            }
        }

        // Validate max variations
        if (isset($input['max_variations'])) {
            $max = intval($input['max_variations']);
            if ($max < $this->rules['max_variations']['min'] ||
                $max > $this->rules['max_variations']['max']) {
                $this->errors[] = __('Invalid maximum variations value', 'color-palette-generator');
            }
        }
    }

    /**
     * Validate custom API settings
     *
     * @param array $input Settings to validate
     */
    private function validate_custom_api_settings($input) {
        if (!isset($input['custom_api_url'])) {
            $this->errors[] = __('Custom API URL is required', 'color-palette-generator');
            return;
        }

        if (!filter_var($input['custom_api_url'], FILTER_VALIDATE_URL)) {
            $this->errors[] = __('Invalid custom API URL', 'color-palette-generator');
        }

        // Validate API endpoint format
        if (!$this->is_valid_api_endpoint($input['custom_api_url'])) {
            $this->warnings[] = __('Custom API URL may not follow REST API conventions', 'color-palette-generator');
        }
    }

    /**
     * Check for potential conflicts between settings
     *
     * @param array $input Settings to validate
     */
    private function check_settings_conflicts($input) {
        // Check for performance vs quality conflicts
        if (isset($input['export_options']['minify_output']) &&
            $input['export_options']['minify_output'] &&
            isset($input['accessibility']['enforce_wcag_aaa']) &&
            $input['accessibility']['enforce_wcag_aaa']) {
            $this->warnings[] = __('Minification may affect WCAG AAA compliance validation', 'color-palette-generator');
        }

        // Check for cache duration vs API rate limits
        if (isset($input['naming_service']) &&
            $input['naming_service'] === 'openai' &&
            isset($input['cache_duration']) &&
            $input['cache_duration'] < 3600) {
            $this->warnings[] = __('Short cache duration may exceed API rate limits', 'color-palette-generator');
        }
    }

    /**
     * Utility function to validate hex colors
     *
     * @param string $color Color to validate
     * @return bool True if valid
     */
    private function is_valid_hex_color($color) {
        return preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color);
    }

    /**
     * Utility function to validate API endpoints
     *
     * @param string $url URL to validate
     * @return bool True if valid
     */
    private function is_valid_api_endpoint($url) {
        return preg_match('/^https?:\/\/[^\/]+\/[^\/]+\/v\d+\/', $url);
    }

    /**
     * Check if contrast validation should be performed
     *
     * @param array $input Settings to validate
     * @return bool True if contrast validation should be performed
     */
    private function should_check_contrast($input) {
        return isset($input['accessibility']['enforce_wcag_aa']) &&
               $input['accessibility']['enforce_wcag_aa'];
    }

    /**
     * Validate contrast ratios between default colors
     *
     * @param array $colors Colors to validate
     */
    private function validate_default_color_contrast($colors) {
        $combinations = [
            ['primary', 'secondary'],
            ['primary', 'tertiary'],
            ['secondary', 'tertiary'],
            ['accent', 'primary']
        ];

        foreach ($combinations as $pair) {
            $ratio = $this->calculate_contrast_ratio(
                $colors[$pair[0]],
                $colors[$pair[1]]
            );

            if ($ratio < 4.5) { // WCAG AA standard
                $this->warnings[] = sprintf(
                    __('Low contrast ratio (%s) between %s and %s colors', 'color-palette-generator'),
                    number_format($ratio, 2),
                    $pair[0],
                    $pair[1]
                );
            }
        }
    }

    /**
     * Calculate contrast ratio between two colors
     *
     * @param string $color1 First color
     * @param string $color2 Second color
     * @return float Contrast ratio
     */
    private function calculate_contrast_ratio($color1, $color2) {
        $l1 = $this->get_relative_luminance($color1);
        $l2 = $this->get_relative_luminance($color2);

        $lighter = max($l1, $l2);
        $darker = min($l1, $l2);

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    /**
     * Calculate relative luminance of a color
     *
     * @param string $hex Color to calculate luminance for
     * @return float Relative luminance
     */
    private function get_relative_luminance($hex) {
        $rgb = $this->hex_to_rgb($hex);

        $rgb = array_map(function($val) {
            $val = $val / 255;
            return $val <= 0.03928
                ? $val / 12.92
                : pow(($val + 0.055) / 1.055, 2.4);
        }, $rgb);

        return $rgb[0] * 0.2126 + $rgb[1] * 0.7152 + $rgb[2] * 0.0722;
    }

    /**
     * Convert hex color to RGB array
     *
     * @param string $hex Color to convert
     * @return array RGB values
     */
    private function hex_to_rgb($hex) {
        $hex = ltrim($hex, '#');
        return array_map('hexdec', str_split($hex, 2));
    }

    /**
     * Sanitize input data
     *
     * @param array $input Data to sanitize
     * @return array Sanitized data
     */
    private function sanitize_input($input) {
        $sanitized = [];

        // Sanitize basic fields
        $sanitized['naming_service'] = sanitize_text_field($input['naming_service']);
        $sanitized['api_key'] = sanitize_text_field($input['api_key']);

        // Sanitize accessibility settings
        $sanitized['accessibility'] = [
            'min_contrast_ratio' => floatval($input['accessibility']['min_contrast_ratio']),
            'check_color_blindness' => (bool) $input['accessibility']['check_color_blindness'],
            'enforce_wcag_aa' => (bool) $input['accessibility']['enforce_wcag_aa'],
            'enforce_wcag_aaa' => (bool) $input['accessibility']['enforce_wcag_aaa']
        ];

        // Sanitize export options
        $sanitized['export_options'] = array_map('boolval', $input['export_options']);

        // Sanitize default colors
        $sanitized['default_colors'] = array_map('sanitize_hex_color', $input['default_colors']);

        // Sanitize performance settings
        $sanitized['cache_duration'] = absint($input['cache_duration']);
        $sanitized['max_variations'] = absint($input['max_variations']);

        return $sanitized;
    }

    /**
     * Validate settings
     *
     * @param array $input Settings to validate
     * @return array Validated settings
     */
    public function validate_settings($input) {
        $validated = [];
        $errors = new WP_Error();

        // Validate General Settings
        $validated['default_palette_size'] = $this->validate_palette_size(
            $input['default_palette_size'] ?? 5
        );

        // Validate API Settings
        $validated['api_provider'] = $this->validate_api_provider(
            $input['api_provider'] ?? ''
        );

        $validated['api_key'] = $this->validate_api_key(
            $input['api_key'] ?? '',
            $validated['api_provider']
        );

        if ($errors->has_errors()) {
            add_settings_error(
                'color_palette_generator_settings',
                'validation_failed',
                $errors->get_error_message()
            );
            return get_option('color_palette_generator_options');
        }

        return $validated;
    }

    /**
     * Validate individual settings
     *
     * @param int $size Palette size to validate
     * @return int Validated palette size
     */
    private function validate_palette_size($size) {
        $size = absint($size);
        if ($size < 3 || $size > 10) {
            add_settings_error(
                'color_palette_generator_settings',
                'invalid_palette_size',
                __('Palette size must be between 3 and 10', 'color-palette-generator')
            );
            return 5;
        }
        return $size;
    }

    /**
     * Validate API credentials
     *
     * @param string $provider API provider to validate
     * @param string $key API key to validate
     * @return bool True if valid
     */
    private function validate_api_credentials($provider, $key) {
        $factory = new Providers\AiProviderFactory();
        try {
            $provider_instance = $factory->create_provider($provider);
            $validation_result = $provider_instance->validate_credentials($key);

            if (!$validation_result['valid']) {
                add_settings_error(
                    'color_palette_generator_settings',
                    'invalid_api_credentials',
                    $validation_result['message']
                );
                return false;
            }

            return true;
        } catch (Exception $e) {
            add_settings_error(
                'color_palette_generator_settings',
                'api_validation_error',
                $e->getMessage()
            );
            return false;
        }
    }
}
