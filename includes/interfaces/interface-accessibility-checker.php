<?php

namespace GLColorPalette\Interfaces;

/**
 * Accessibility Checker Interface
 *
 * Defines the contract for WCAG compliance and accessibility checking.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface AccessibilityChecker {
    /**
     * Checks contrast ratio between two colors.
     *
     * @param string $foreground Foreground color in hex format.
     * @param string $background Background color in hex format.
     * @return array {
     *     Contrast analysis results.
     *     @type float  $ratio           The calculated contrast ratio.
     *     @type bool   $wcag_aa_normal  Passes WCAG 2.1 AA for normal text.
     *     @type bool   $wcag_aa_large   Passes WCAG 2.1 AA for large text.
     *     @type bool   $wcag_aaa_normal Passes WCAG 2.1 AAA for normal text.
     *     @type bool   $wcag_aaa_large  Passes WCAG 2.1 AAA for large text.
     * }
     * @throws \InvalidArgument
     */
    public function check_contrast_ratio(string $foreground, string $background): array;
    public function validate_wcag_compliance(array $palette): array;
    public function get_accessibility_score(array $palette): float;
} 
