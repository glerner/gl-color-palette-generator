<?php
/**
 * Color Palette Validator Class
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\Color_Management;

use GL_Color_Palette_Generator\Interfaces\Color_Palette_Validator_Interface;
use GL_Color_Palette_Generator\Models\Color_Palette;
use GL_Color_Palette_Generator\Color_Management\Color_Accessibility;
use GL_Color_Palette_Generator\Color_Management\Color_Wheel;
use GL_Color_Palette_Generator\Color_Management\Color_Utility;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;
use WP_Error;

/**
 * Class Color_Palette_Validator
 * Validates color palettes for various criteria including contrast, accessibility, and harmony
 */
class Color_Palette_Validator implements Color_Palette_Validator_Interface {
    /**
     * Color accessibility checker
     * @var Color_Accessibility
     */
    private Color_Accessibility $accessibility;

    /**
     * Color wheel
     * @var Color_Wheel
     */
    private Color_Wheel $color_wheel;

    /**
     * Color utility
     * @var Color_Utility
     */
    private Color_Utility $color_utility;

    /**
     * Last validation errors
     * @var array
     */
    private array $last_errors = [];

    /**
     * Constructor
     */
    public function __construct(
        Color_Accessibility $accessibility,
        Color_Wheel $color_wheel,
        Color_Utility $color_utility
    ) {
        $this->accessibility = $accessibility;
        $this->color_wheel = $color_wheel;
        $this->color_utility = $color_utility;
    }

    /**
     * Validate a color palette
     *
     * @param Color_Palette $palette Palette to validate
     * @param array        $options Validation options
     * @return bool|WP_Error True if valid, WP_Error on failure
     */
    public function validate_palette(Color_Palette $palette, array $options = []): bool|WP_Error {
        $this->last_errors = [];

        $default_options = [
            'check_contrast' => true,
            'check_accessibility' => true,
            'check_harmony' => true,
            'level' => 'AA',
            'scheme_type' => Color_Constants::SCHEME_MONOCHROMATIC
        ];

        $options = array_merge($default_options, $options);
        $colors = $palette->get_colors();

        if (count($colors) === 0) {
            return new WP_Error('empty_palette', 'Palette contains no colors');
        }

        // Validate color formats
        foreach ($colors as $color) {
            if (!$this->validate_color_format($color)) {
                $this->last_errors[] = "Invalid color format: {$color}";
                return new WP_Error('invalid_format', 'One or more colors have invalid format');
            }
        }

        // Check accessibility if requested
        if ($options['check_accessibility']) {
            $accessibility_result = $this->validate_accessibility($colors, $options['level']);
            if (is_wp_error($accessibility_result)) {
                $this->last_errors[] = $accessibility_result->get_error_message();
                return $accessibility_result;
            }
        }

        // Check harmony if requested
        if ($options['check_harmony']) {
            $harmony_result = $this->validate_harmony($colors, $options['scheme_type']);
            if (is_wp_error($harmony_result)) {
                $this->last_errors[] = $harmony_result->get_error_message();
                return $harmony_result;
            }
        }

        return true;
    }

    /**
     * Validate color format
     *
     * @param string $color Color to validate
     * @param string $format Expected format (hex, rgb, rgba, hsl, hsla)
     * @return bool True if valid
     */
    public function validate_color_format(string $color, string $format = 'hex'): bool {
        return $this->color_utility->is_valid_hex_color($color);
    }

    /**
     * Validate color accessibility
     *
     * @param array  $colors Colors to validate
     * @param string $level  WCAG level ('AA', or 'AAA')
     * @return bool|WP_Error True if valid, WP_Error on failure
     */
    public function validate_accessibility(array $colors, string $level = 'AA'): bool|WP_Error {
        if (count($colors) < 2) {
            return new WP_Error('insufficient_colors', 'Need at least 2 colors to check accessibility');
        }

        // Get target and minimum contrast based on WCAG level
        $target_contrast = Color_Constants::WCAG_CONTRAST_TARGET;  // Our target (AAA or better)
        $min_contrast = match ($level) {
            'AAA' => Color_Constants::WCAG_CONTRAST_AAA,
            'AA' => Color_Constants::WCAG_CONTRAST_AA,
            'A' => Color_Constants::WCAG_CONTRAST_AA_LARGE,
            default => Color_Constants::WCAG_CONTRAST_MIN,   // Our minimum requirement
        };

        foreach ($colors as $fg_color) {
            foreach ($colors as $bg_color) {
                if ($fg_color === $bg_color) {
                    continue;
                }

                $contrast = $this->accessibility->check_contrast($fg_color, $bg_color);

                // First check if we meet our target contrast
                if ($contrast >= $target_contrast) {
                    continue;  // Exceeds our target, no need to check minimum
                }

                // If we don't meet target, ensure we at least meet minimum for the specified level
                if ($contrast < $min_contrast) {
                    return new WP_Error(
                        'insufficient_contrast',
                        sprintf(
                            'Contrast ratio %.2f between %s and %s is below %s level requirement (%.2f). Target contrast is %.2f',
                            $contrast,
                            $fg_color,
                            $bg_color,
                            $level,
                            $min_contrast,
                            $target_contrast
                        )
                    );
                }
            }
        }

        return true;
    }

    /**
     * Validate color harmony
     *
     * @param array  $colors Colors to validate
     * @param string $scheme_type Type of color scheme to validate against
     * @return bool|WP_Error True if valid, WP_Error on failure
     */
    public function validate_harmony(
        array $colors,
        string $scheme_type = Color_Constants::SCHEME_MONOCHROMATIC
    ): bool|WP_Error {
        if (count($colors) < 2) {
            return new WP_Error('insufficient_colors', 'Need at least 2 colors to check harmony');
        }

        // Get required roles for scheme type
        $required_roles = Color_Constants::REQUIRED_ROLES[$scheme_type] ?? null;
        if ($required_roles === null) {
            return new WP_Error('invalid_scheme', 'Invalid color scheme type');
        }

        if (count($colors) < count($required_roles)) {
            return new WP_Error(
                'insufficient_colors',
                sprintf('Scheme type %s requires at least %d colors', $scheme_type, count($required_roles))
            );
        }

        // Calculate harmony scores
        $harmony_score = $this->color_wheel->calculate_harmony_score($colors);
        $balance_score = $this->color_wheel->calculate_color_balance($colors);
        $vibrance_score = $this->color_wheel->calculate_vibrance_score($colors);
        
        // Calculate overall score (weighted average)
        $overall_score = (
            $harmony_score * 0.5 +    // Harmony is most important
            $balance_score * 0.3 +    // Balance is second
            $vibrance_score * 0.2     // Vibrance is third
        );
        
        $thresholds = Color_Constants::HARMONY_THRESHOLDS;
        $failed_aspects = [];
        
        if ($harmony_score < $thresholds['harmony']) {
            $failed_aspects[] = sprintf('Harmony (%.2f < %.2f)', $harmony_score, $thresholds['harmony']);
        }
        if ($balance_score < $thresholds['balance']) {
            $failed_aspects[] = sprintf('Balance (%.2f < %.2f)', $balance_score, $thresholds['balance']);
        }
        if ($vibrance_score < $thresholds['vibrance']) {
            $failed_aspects[] = sprintf('Vibrance (%.2f < %.2f)', $vibrance_score, $thresholds['vibrance']);
        }
        if ($overall_score < $thresholds['overall']) {
            $failed_aspects[] = sprintf('Overall (%.2f < %.2f)', $overall_score, $thresholds['overall']);
        }
        
        if ($failed_aspects === []) {
            return true;
        }
        
        return new WP_Error(
            'invalid_harmony',
            sprintf(
                'Colors do not meet harmony requirements for scheme type %s:
                Failed aspects: %s
                
                Scores:
                - Harmony: %.2f
                - Balance: %.2f
                - Vibrance: %.2f
                - Overall: %.2f',
                $scheme_type,
                implode(', ', $failed_aspects),
                $harmony_score,
                $balance_score,
                $vibrance_score,
                $overall_score
            )
        );
    }

    /**
     * Get last validation errors
     *
     * @return array List of validation errors with context
     */
    public function get_last_errors(): array {
        return $this->last_errors;
    }
}
