<?php

namespace GLColorPalette;

/**
 * Color Palette Validator Class
 *
 * Handles validation of color palettes and their properties.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
class ColorPaletteValidator {
    /**
     * Default validation rules.
     *
     * @var array
     */
    private $default_rules = [
        'name' => [
            'required' => true,
            'type' => 'string',
            'min_length' => 1,
            'max_length' => 100
        ],
        'colors' => [
            'required' => true,
            'type' => 'array',
            'min_items' => 1,
            'max_items' => 20,
            'unique_items' => true
        ],
        'metadata' => [
            'required' => false,
            'type' => 'array'
        ]
    ];

    /**
     * Color format validator instance.
     *
     * @var ColorPaletteFormatter
     */
    private $formatter;

    /**
     * Constructor.
     *
     * @param ColorPaletteFormatter $formatter Color formatter instance.
     */
    public function __construct(ColorPaletteFormatter $formatter) {
        $this->formatter = $formatter;
    }

    /**
     * Validates a color palette.
     *
     * @param ColorPalette $palette Palette to validate.
     * @param array $rules Optional. Custom validation rules.
     * @return array {
     *     Validation results.
     *     @type bool   $valid    Whether palette is valid.
     *     @type array  $errors   Validation errors.
     *     @type array  $warnings Validation warnings.
     * }
     */
    public function validate_palette(ColorPalette $palette, array $rules = []): array {
        $rules = array_merge($this->default_rules, $rules);
        $errors = [];
        $warnings = [];

        // Validate name
        $name_validation = $this->validate_field(
            'name',
            $palette->get_name(),
            $rules['name']
        );
        if (!empty($name_validation['errors'])) {
            $errors['name'] = $name_validation['errors'];
        }

        // Validate colors
        $colors_validation = $this->validate_colors(
            $palette->get_colors(),
            $rules['colors']
        );
        if (!empty($colors_validation['errors'])) {
            $errors['colors'] = $colors_validation['errors'];
        }
        if (!empty($colors_validation['warnings'])) {
            $warnings['colors'] = $colors_validation['warnings'];
        }

        // Validate metadata
        if (isset($rules['metadata'])) {
            $metadata_validation = $this->validate_field(
                'metadata',
                $palette->get_metadata(),
                $rules['metadata']
            );
            if (!empty($metadata_validation['errors'])) {
                $errors['metadata'] = $metadata_validation['errors'];
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }

    /**
     * Validates individual field.
     *
     * @param string $field Field name.
     * @param mixed $value Field value.
     * @param array $rules Validation rules.
     * @return array Validation results.
     */
    private function validate_field(string $field, $value, array $rules): array {
        $errors = [];

        // Required check
        if ($rules['required'] && empty($value)) {
            $errors[] = "{$field} is required";
            return ['errors' => $errors];
        }

        // Type check
        if (isset($rules['type'])) {
            $type_valid = match($rules['type']) {
                'string' => is_string($value),
                'array' => is_array($value),
                'number' => is_numeric($value),
                default => true
            };
            if (!$type_valid) {
                $errors[] = "{$field} must be of type {$rules['type']}";
            }
        }

        // Length checks for strings
        if (is_string($value)) {
            if (isset($rules['min_length']) && strlen($value) < $rules['min_length']) {
                $errors[] = "{$field} must be at least {$rules['min_length']} characters";
            }
            if (isset($rules['max_length']) && strlen($value) > $rules['max_length']) {
                $errors[] = "{$field} must not exceed {$rules['max_length']} characters";
            }
        }

        // Array checks
        if (is_array($value)) {
            if (isset($rules['min_items']) && count($value) < $rules['min_items']) {
                $errors[] = "{$field} must contain at least {$rules['min_items']} items";
            }
            if (isset($rules['max_items']) && count($value) > $rules['max_items']) {
                $errors[] = "{$field} must not exceed {$rules['max_items']} items";
            }
        }

        return ['errors' => $errors];
    }

    /**
     * Validates color array.
     *
     * @param array $colors Colors to validate.
     * @param array $rules Validation rules.
     * @return array Validation results.
     */
    private function validate_colors(array $colors, array $rules): array {
        $errors = [];
        $warnings = [];

        // Basic array validation
        $array_validation = $this->validate_field('colors', $colors, $rules);
        if (!empty($array_validation['errors'])) {
            return $array_validation;
        }

        // Validate individual colors
        foreach ($colors as $index => $color) {
            if (!is_string($color)) {
                $errors[] = "Color at index {$index} must be a string";
                continue;
            }

            // Validate color format
            if (!$this->formatter->detect_format($color)) {
                $errors[] = "Invalid color format at index {$index}: {$color}";
                continue;
            }
        }

        // Check for uniqueness if required
        if (!empty($rules['unique_items']) && count($colors) !== count(array_unique($colors))) {
            $warnings[] = "Palette contains duplicate colors";
        }

        // Check for contrast if specified
        if (!empty($rules['min_contrast'])) {
            $contrast_issues = $this->check_color_contrast($colors, $rules['min_contrast']);
            if (!empty($contrast_issues)) {
                $warnings = array_merge($warnings, $contrast_issues);
            }
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }

    /**
     * Checks color contrast ratios.
     *
     * @param array $colors Colors to check.
     * @param float $min_ratio Minimum contrast ratio.
     * @return array Contrast warnings.
     */
    private function check_color_contrast(array $colors, float $min_ratio): array {
        $warnings = [];
        $count = count($colors);

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $ratio = $this->calculate_contrast_ratio($colors[$i], $colors[$j]);
                if ($ratio < $min_ratio) {
                    $warnings[] = sprintf(
                        "Low contrast ratio (%0.2f) between colors %s and %s",
                        $ratio,
                        $colors[$i],
                        $colors[$j]
                    );
                }
            }
        }

        return $warnings;
    }

    /**
     * Calculates contrast ratio between two colors.
     *
     * @param string $color1 First color.
     * @param string $color2 Second color.
     * @return float Contrast ratio.
     */
    private function calculate_contrast_ratio(string $color1, string $color2): float {
        // Convert colors to RGB format
        $rgb1 = $this->formatter->format_color($color1, 'rgb');
        $rgb2 = $this->formatter->format_color($color2, 'rgb');

        // Extract RGB values
        preg_match('/rgb\((\d+),\s*(\d+),\s*(\d+)\)/', $rgb1, $matches1);
        preg_match('/rgb\((\d+),\s*(\d+),\s*(\d+)\)/', $rgb2, $matches2);

        // Calculate relative luminance
        $l1 = $this->calculate_relative_luminance(
            (int)$matches1[1],
            (int)$matches1[2],
            (int)$matches1[3]
        );
        $l2 = $this->calculate_relative_luminance(
            (int)$matches2[1],
            (int)$matches2[2],
            (int)$matches2[3]
        );

        // Calculate contrast ratio
        $lighter = max($l1, $l2);
        $darker = min($l1, $l2);
        return ($lighter + 0.05) / ($darker + 0.05);
    }

    /**
     * Calculates relative luminance.
     *
     * @param int $r Red value.
     * @param int $g Green value.
     * @param int $b Blue value.
     * @return float Relative luminance.
     */
    private function calculate_relative_luminance(int $r, int $g, int $b): float {
        $r = $r / 255;
        $g = $g / 255;
        $b = $b / 255;

        $r = $r <= 0.03928 ? $r / 12.92 : pow(($r + 0.055) / 1.055, 2.4);
        $g = $g <= 0.03928 ? $g / 12.92 : pow(($g + 0.055) / 1.055, 2.4);
        $b = $b <= 0.03928 ? $b / 12.92 : pow(($b + 0.055) / 1.055, 2.4);

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }
} 
