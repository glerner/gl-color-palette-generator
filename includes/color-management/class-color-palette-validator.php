<?php
/**
 * Color Palette Validator Class
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\Color_Management;

use GL_Color_Palette_Generator\Interfaces\Color_Constants;

/**
 * Class Color_Palette_Validator
 * Validates color palettes for various criteria including contrast, accessibility, and harmony
 */
class Color_Palette_Validator implements \GL_Color_Palette_Generator\Interfaces\Color_Palette_Validator {
    /**
     * Color Utility instance
     *
     * @var Color_Utility
     */
    private $color_utility;

    /**
     * Constructor
     */
    public function __construct() {
        $this->color_utility = new Color_Utility();
    }

    /**
     * Validate entire palette
     *
     * @param array $palette Array of hex colors.
     * @param array $options Validation options.
     * @return array Validation results.
     */
    public function validate_palette($palette, $options = []) {
        $default_options = [
            'check_contrast' => true,
            'check_accessibility' => true,
            'check_harmony' => true,
            'check_distinctness' => true,
            'check_format' => true,
            'required_roles' => [], // Allow specifying which roles are required for this palette
            'scheme_type' => null   // Allow specifying the scheme type for appropriate validation
        ];

        $options = array_merge($default_options, $options);
        $results = [
            'is_valid' => true,
            'errors' => [],
            'warnings' => [],
            'details' => []
        ];

        // Basic format validation
        if ($options['check_format']) {
            $format_results = $this->validate_format($palette);
            if (!$format_results['is_valid']) {
                $results['is_valid'] = false;
                $results['errors'] = array_merge($results['errors'], $format_results['errors']);
            }
        }

        // Skip other checks if format is invalid
        if (!$results['is_valid']) {
            return $results;
        }

        // Validate required roles if specified
        if (!empty($options['required_roles'])) {
            foreach ($options['required_roles'] as $role) {
                if (!isset($palette[$role])) {
                    $results['errors'][] = sprintf(
                        __('Missing required color role: %s', 'gl-color-palette-generator'),
                        $role
                    );
                    $results['is_valid'] = false;
                }
            }
        }

        // Color distinctness
        if ($options['check_distinctness']) {
            $distinctness_results = $this->validate_distinctness($palette);
            $results['details']['distinctness'] = $distinctness_results;
            if (!$distinctness_results['is_valid']) {
                $results['warnings'][] = __('Some colors are too similar', 'gl-color-palette-generator');
            }
        }

        // Contrast validation
        if ($options['check_contrast']) {
            $contrast_results = $this->validate_contrast($palette);
            $results['details']['contrast'] = $contrast_results;
            if (!$contrast_results['is_valid']) {
                $results['warnings'][] = __('Some color combinations have contrast issues', 'gl-color-palette-generator');
            }
        }

        return $results;
    }

    /**
     * Validate color format
     *
     * @param array $palette Array of colors.
     * @return array Validation results.
     */
    private function validate_format($palette) {
        $results = [
            'is_valid' => true,
            'errors' => []
        ];

        if (!is_array($palette)) {
            $results['is_valid'] = false;
            $results['errors'][] = __('Palette must be an array', 'gl-color-palette-generator');
            return $results;
        }

        foreach ($palette as $role => $color) {
            if (!preg_match('/^#[0-9a-f]{6}$/i', $color)) {
                $results['is_valid'] = false;
                $results['errors'][] = sprintf(
                    __('Invalid color format for role %s: %s', 'gl-color-palette-generator'),
                    $role,
                    $color
                );
            }
        }

        return $results;
    }

    /**
     * Validate color distinctness
     *
     * @param array $palette Array of colors.
     * @return array Validation results.
     */
    private function validate_distinctness($palette) {
        $results = [
            'is_valid' => true,
            'similar_pairs' => []
        ];

        for ($i = 0; $i < count($palette); $i++) {
            for ($j = $i + 1; $j < count($palette); $j++) {
                $difference = $this->color_utility->calculate_color_difference(
                    $palette[array_keys($palette)[$i]],
                    $palette[array_keys($palette)[$j]]
                );

                if ($difference < Color_Constants::MIN_COLOR_DIFFERENCE) {
                    $results['is_valid'] = false;
                    $results['similar_pairs'][] = [
                        'colors' => [$palette[array_keys($palette)[$i]], $palette[array_keys($palette)[$j]]],
                        'difference' => $difference
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * Validate contrast ratios
     *
     * @param array $palette Array of colors.
     * @return array Validation results.
     */
    private function validate_contrast($palette) {
        $results = [
            'is_valid' => true,
            'contrast_pairs' => []
        ];

        for ($i = 0; $i < count($palette); $i++) {
            for ($j = $i + 1; $j < count($palette); $j++) {
                $contrast = $this->color_utility->get_contrast_ratio(
                    $palette[array_keys($palette)[$i]],
                    $palette[array_keys($palette)[$j]]
                );

                // Check if contrast is within acceptable range
                if ($contrast < Color_Constants::WCAG_CONTRAST_MIN ||
                    $contrast > Color_Constants::CONTRAST_MAX) {
                    $results['is_valid'] = false;
                    $results['contrast_pairs'][] = [
                        'colors' => [$palette[array_keys($palette)[$i]], $palette[array_keys($palette)[$j]]],
                        'contrast' => $contrast,
                        'issue' => $contrast < Color_Constants::WCAG_CONTRAST_MIN ? 'insufficient' : 'excessive'
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * Validate accessibility
     *
     * @param array $palette Array of colors.
     * @return array Validation results.
     */
    private function validate_accessibility($palette) {
        $accessibility_checker = new Accessibility_Checker();
        $results = [
            'is_valid' => true,
            'checks' => []
        ];

        foreach ($palette as $role => $color) {
            // Check against white and black backgrounds
            $results['checks'][] = [
                'color' => $color,
                'on_white' => $accessibility_checker->check_combination($color, '#FFFFFF'),
                'on_black' => $accessibility_checker->check_combination($color, '#000000')
            ];
        }

        // Check if at least one color is suitable for text
        $has_text_color = false;
        foreach ($results['checks'] as $check) {
            if ($check['on_white']['aa_small'] || $check['on_black']['aa_small']) {
                $has_text_color = true;
                break;
            }
        }

        if (!$has_text_color) {
            $results['is_valid'] = false;
        }

        return $results;
    }

    /**
     * Validate color harmony
     *
     * @param array $palette Array of colors.
     * @return array Validation results.
     */
    private function validate_harmony($palette) {
        $results = [
            'is_valid' => true,
            'harmony_score' => 0,
            'details' => []
        ];

        // Convert colors to HSL for harmony analysis
        $hsl_colors = array_map(
            [$this->color_utility, 'hex_to_hsl'],
            $palette
        );

        // Check hue spacing
        $hues = array_column($hsl_colors, 'h');
        sort($hues);
        $hue_spacing = $this->analyze_hue_spacing($hues);
        $results['details']['hue_spacing'] = $hue_spacing;

        // Check saturation and lightness distribution
        $saturations = array_column($hsl_colors, 's');
        $lightnesses = array_column($hsl_colors, 'l');

        $results['details']['saturation_range'] = [
            'min' => min($saturations),
            'max' => max($saturations),
            'spread' => max($saturations) - min($saturations)
        ];

        $results['details']['lightness_range'] = [
            'min' => min($lightnesses),
            'max' => max($lightnesses),
            'spread' => max($lightnesses) - min($lightnesses)
        ];

        // Calculate overall harmony score
        $results['harmony_score'] = $this->calculate_harmony_score(
            $hue_spacing,
            $results['details']['saturation_range'],
            $results['details']['lightness_range']
        );

        $results['is_valid'] = $results['harmony_score'] >= 70;

        return $results;
    }

    /**
     * Analyze hue spacing
     *
     * @param array $hues Array of hue values.
     * @return array Spacing analysis.
     */
    private function analyze_hue_spacing($hues) {
        $spacing = [];
        for ($i = 0; $i < count($hues); $i++) {
            $next = ($i + 1) % count($hues);
            $diff = $hues[$next] - $hues[$i];
            if ($diff < 0) {
                $diff += 360;
            }
            $spacing[] = $diff;
        }

        return [
            'min_spacing' => min($spacing),
            'max_spacing' => max($spacing),
            'average_spacing' => array_sum($spacing) / count($spacing)
        ];
    }

    /**
     * Calculate harmony score
     *
     * @param array $hue_spacing Hue spacing analysis.
     * @param array $saturation_range Saturation range.
     * @param array $lightness_range Lightness range.
     * @return int Harmony score (0-100).
     */
    private function calculate_harmony_score($hue_spacing, $saturation_range, $lightness_range) {
        $hue_score = min(100, ($hue_spacing['min_spacing'] / 30) * 100);

        $saturation_score = 100 - min(100, abs($saturation_range['spread'] - 40));

        $lightness_score = 100 - min(100, abs($lightness_range['spread'] - 50));

        return (int) (($hue_score + $saturation_score + $lightness_score) / 3);
    }
}
