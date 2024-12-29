<?php

namespace GL_Color_Palette_Generator\Interfaces;

/**
 * Accessibility Checker Interface
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 * @since 1.0.0
 */

/**
 * Interface Accessibility_Checker
 *
 * Defines the contract for accessibility checking implementations.
 *
 * @since 1.0.0
 */
interface Accessibility_Checker {
    /**
     * Check contrast ratio between two colors
     *
     * @param string $foreground Foreground color in hex format
     * @param string $background Background color in hex format
     * @return array {
     *     Contrast analysis results
     *     @type float  $contrast_ratio   The calculated contrast ratio
     *     @type bool   $aa_small         Passes WCAG 2.1 AA for small text
     *     @type bool   $aa_large         Passes WCAG 2.1 AA for large text
     *     @type bool   $aaa_small        Passes WCAG 2.1 AAA for small text
     *     @type bool   $aaa_large        Passes WCAG 2.1 AAA for large text
     * }
     */
    public function check_combination(string $foreground, string $background): array;

    /**
     * Check accessibility of a color palette
     *
     * @param array $palette Array of hex color values
     * @return array {
     *     Palette analysis results
     *     @type array  $combinations     Array of contrast ratios between color pairs
     *     @type bool   $wcag_aa         Overall WCAG 2.1 AA compliance
     *     @type bool   $wcag_aaa        Overall WCAG 2.1 AAA compliance
     *     @type array  $recommendations Array of suggested improvements
     * }
     */
    public function check_palette(array $palette): array;

    /**
     * Calculate accessibility score for a color palette
     *
     * @param array $palette Array of hex color values
     * @return float Score between 0 and 1
     */
    public function calculate_score(array $palette): float;
} 
