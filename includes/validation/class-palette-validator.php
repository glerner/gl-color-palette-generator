<?php
namespace GLColorPalette\Validation;

/**
 * Palette Validator
 *
 * Validates color palettes for harmony, contrast, and accessibility.
 */
class Palette_Validator {
    /** @var array Validation rules and thresholds */
    protected $rules = [
        'min_colors' => 2,
        'max_colors' => 10,
        'min_contrast' => 4.5,
        'min_distinction' => 20,
        'min_hue_variance' => 30,
        'min_saturation_range' => 20,
        'min_lightness_range' => 30
    ];

    protected $errors = [];
    protected $warnings = [];
    protected $suggestions = [];

    /**
     * Validate a color palette
     *
     * @param array $colors Array of hex color codes
     * @param array $context Additional context for validation
     * @return bool True if valid
     */
    public function validate($colors, $context = []) {
        $this->errors = [];
        $this->warnings = [];
        $this->suggestions = [];

        try {
            // Check basic requirements
            if (!$this->validate_basic_requirements($colors)) {
                return false;
            }

            // Check color relationships
            $this->validate_relationships($colors);

            // Check accessibility
            $this->validate_accessibility($colors, $context);

            // Generate suggestions if needed
            if (!empty($this->warnings)) {
                $this->generate_suggestions($colors);
            }

            return empty($this->errors);

        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    /**
     * Validate basic palette requirements
     *
     * @param array $colors Colors to validate
     * @return bool True if valid
     */
    private function validate_basic_requirements($colors) {
        // Check count
        $count = count($colors);
        if ($count < $this->rules['min_colors']) {
            $this->errors[] = sprintf(
                'Not enough colors (minimum %d)',
                $this->rules['min_colors']
            );
            return false;
        }

        if ($count > $this->rules['max_colors']) {
            $this->errors[] = sprintf(
                'Too many colors (maximum %d)',
                $this->rules['max_colors']
            );
            return false;
        }

        // Validate hex codes
        foreach ($colors as $color) {
            if (!preg_match('/^#[0-9A-F]{6}$/i', $color)) {
                $this->errors[] = sprintf(
                    'Invalid hex code: %s',
                    $color
                );
                return false;
            }
        }

        return true;
    }

    /**
     * Validate color relationships
     *
     * @param array $colors Colors to validate
     */
    private function validate_relationships($colors) {
        $hsv_colors = array_map([$this, 'hex_to_hsv'], $colors);

        // Check hue variance
        $hues = array_column($hsv_colors, 'h');
        $hue_variance = $this->calculate_variance($hues);
        if ($hue_variance < $this->rules['min_hue_variance']) {
            $this->warnings[] = 'Low hue variance';
        }

        // Check saturation range
        $saturations = array_column($hsv_colors, 's');
        $saturation_range = max($saturations) - min($saturations);
        if ($saturation_range < $this->rules['min_saturation_range']) {
            $this->warnings[] = 'Limited saturation range';
        }

        // Check lightness range
        $values = array_column($hsv_colors, 'v');
        $value_range = max($values) - min($values);
        if ($value_range < $this->rules['min_lightness_range']) {
            $this->warnings[] = 'Limited lightness range';
        }

        // Check distinctions between colors
        for ($i = 0; $i < count($colors); $i++) {
            for ($j = $i + 1; $j < count($colors); $j++) {
                $distinction = $this->calculate_color_distinction(
                    $hsv_colors[$i],
                    $hsv_colors[$j]
                );
                if ($distinction < $this->rules['min_distinction']) {
                    $this->warnings[] = sprintf(
                        'Similar colors: %s and %s',
                        $colors[$i],
                        $colors[$j]
                    );
                }
            }
        }
    }

    /**
     * Validate accessibility requirements
     *
     * @param array $colors Colors to validate
     * @param array $context Validation context
     */
    private function validate_accessibility($colors, $context) {
        if (!isset($context['accessibility_level'])) {
            return;
        }

        $level = $context['accessibility_level'];
        foreach ($colors as $i => $color1) {
            foreach ($colors as $j => $color2) {
                if ($i === $j) continue;

                $contrast = $this->calculate_contrast_ratio($color1, $color2);
                if ($contrast < $this->rules['min_contrast']) {
                    $this->warnings[] = sprintf(
                        'Low contrast between %s and %s (%.1f:1)',
                        $color1,
                        $color2,
                        $contrast
                    );
                }
            }
        }
    }

    /**
     * Generate improvement suggestions
     *
     * @param array $colors Colors to analyze
     */
    private function generate_suggestions($colors) {
        // Contrast improvements
        if ($this->has_warning_type('contrast')) {
            $this->suggestions[] = 'Consider increasing contrast by adjusting lightness values';
        }

        // Distinction improvements
        if ($this->has_warning_type('similar')) {
            $this->suggestions[] = 'Try varying hue or saturation to create more distinct colors';
        }

        // Harmony improvements
        if ($this->has_warning_type('hue')) {
            $this->suggestions[] = 'Consider using complementary or analogous color relationships';
        }
    }

    /**
     * Convert hex color to HSV
     *
     * @param string $hex Hex color code
     * @return array HSV values
     */
    private function hex_to_hsv($hex) {
        // Convert hex to RGB
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        // Convert RGB to HSV
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $delta = $max - $min;

        // Calculate hue
        if ($delta == 0) {
            $h = 0;
        } elseif ($max == $r) {
            $h = 60 * fmod((($g - $b) / $delta), 6);
        } elseif ($max == $g) {
            $h = 60 * ((($b - $r) / $delta) + 2);
        } else {
            $h = 60 * ((($r - $g) / $delta) + 4);
        }

        if ($h < 0) {
            $h += 360;
        }

        // Calculate saturation and value
        $s = ($max == 0) ? 0 : ($delta / $max) * 100;
        $v = $max * 100;

        return [
            'h' => $h,
            's' => $s,
            'v' => $v
        ];
    }

    /**
     * Calculate variance of an array of numbers
     *
     * @param array $numbers Numbers to analyze
     * @return float Variance
     */
    private function calculate_variance($numbers) {
        $count = count($numbers);
        if ($count < 2) return 0;

        $mean = array_sum($numbers) / $count;
        $variance = 0;

        foreach ($numbers as $number) {
            $variance += pow($number - $mean, 2);
        }

        return sqrt($variance / ($count - 1));
    }

    /**
     * Calculate distinction between two colors
     *
     * @param array $color1 First color in HSV
     * @param array $color2 Second color in HSV
     * @return float Color distinction value
     */
    private function calculate_color_distinction($color1, $color2) {
        $hue_diff = min(
            abs($color1['h'] - $color2['h']),
            360 - abs($color1['h'] - $color2['h'])
        );
        $sat_diff = abs($color1['s'] - $color2['s']);
        $val_diff = abs($color1['v'] - $color2['v']);

        return sqrt(
            pow($hue_diff / 360, 2) +
            pow($sat_diff / 100, 2) +
            pow($val_diff / 100, 2)
        ) * 100;
    }

    /**
     * Calculate contrast ratio between two colors
     *
     * @param string $color1 First color hex code
     * @param string $color2 Second color hex code
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
     * Get relative luminance of a color
     *
     * @param string $hex Color hex code
     * @return float Relative luminance
     */
    private function get_relative_luminance($hex) {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        $r = ($r <= 0.03928) ? $r / 12.92 : pow(($r + 0.055) / 1.055, 2.4);
        $g = ($g <= 0.03928) ? $g / 12.92 : pow(($g + 0.055) / 1.055, 2.4);
        $b = ($b <= 0.03928) ? $b / 12.92 : pow(($b + 0.055) / 1.055, 2.4);

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    /**
     * Check if warnings contain a specific type
     *
     * @param string $type Warning type to check for
     * @return bool True if warning type exists
     */
    private function has_warning_type($type) {
        foreach ($this->warnings as $warning) {
            if (stripos($warning, $type) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get validation errors
     *
     * @return array Validation errors
     */
    public function get_errors() {
        return $this->errors;
    }

    /**
     * Get validation warnings
     *
     * @return array Validation warnings
     */
    public function get_warnings() {
        return $this->warnings;
    }

    /**
     * Get improvement suggestions
     *
     * @return array Improvement suggestions
     */
    public function get_suggestions() {
        return $this->suggestions;
    }
}
