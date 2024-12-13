<?php declare(strict_types=1);
/**
 * Compliance Checker Class
 *
 * Implements comprehensive accessibility compliance checking functionality
 * for WCAG 2.1 and Section 508 standards.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Accessibility
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Accessibility;

use GL_Color_Palette_Generator\Interfaces\Compliance_Checker_Interface;
use GL_Color_Palette_Generator\Types\Color_Types;
use GL_Color_Palette_Generator\Types\Compliance_Types;
use GL_Color_Palette_Generator\Color_Management\Color_Calculator;
use GL_Color_Palette_Generator\Color_Management\Color_Utility;

/**
 * Compliance Checker Class
 *
 * @since 1.0.0
 */
class Compliance_Checker implements Compliance_Checker_Interface {
    /**
     * WCAG compliance configurations
     */
    private const WCAG_REQUIREMENTS = [
        'contrast_ratios' => [
            'AA' => [
                'normal_text' => [
                    'minimum' => 4.5,
                    'recommended' => 5.0,
                    'font_size' => ['min' => 12, 'unit' => 'px'],
                ],
                'large_text' => [
                    'minimum' => 3.0,
                    'recommended' => 3.5,
                    'font_size' => ['min' => 18, 'unit' => 'px'],
                ],
            ],
            'AAA' => [
                'normal_text' => [
                    'minimum' => 7.0,
                    'recommended' => 7.5,
                    'font_size' => ['min' => 12, 'unit' => 'px'],
                ],
                'large_text' => [
                    'minimum' => 4.5,
                    'recommended' => 5.0,
                    'font_size' => ['min' => 18, 'unit' => 'px'],
                ],
            ],
        ],
    ];

    /**
     * Section 508 compliance configurations
     */
    private const SECTION_508_REQUIREMENTS = [
        'contrast_ratios' => [
            'minimum' => 4.5,
            'recommended' => 5.0,
        ],
        'color_blindness' => [
            'deuteranopia' => true,
            'protanopia' => true,
            'tritanopia' => true,
        ],
    ];

    /**
     * @var Color_Calculator
     */
    private Color_Calculator $color_calculator;

    /**
     * @var Color_Utility
     */
    private Color_Utility $color_utility;

    /**
     * Constructor
     */
    public function __construct() {
        $this->color_calculator = new Color_Calculator();
        $this->color_utility = new Color_Utility();
    }

    /**
     * Check WCAG compliance for a color palette
     *
     * @param array $colors Array of hex color codes
     * @return array Compliance status and details
     */
    public function check_wcag_compliance(array $colors): array {
        $results = [
            'status' => 'pass',
            'level' => null,
            'details' => [],
            'violations' => [],
        ];

        // Check all color combinations for contrast ratios
        foreach ($colors as $i => $color1) {
            foreach ($colors as $j => $color2) {
                if ($i === $j) continue;

                $ratio = $this->validate_contrast_ratio($color1, $color2);
                $level = $this->determine_wcag_level($ratio);

                if ($level === false) {
                    $results['status'] = 'fail';
                    $results['violations'][] = [
                        'colors' => [$color1, $color2],
                        'ratio' => $ratio,
                        'required' => self::WCAG_REQUIREMENTS['contrast_ratios']['AA']['normal_text']['minimum'],
                    ];
                } elseif ($results['level'] === null || $level === 'AAA') {
                    $results['level'] = $level;
                }

                $results['details'][] = [
                    'colors' => [$color1, $color2],
                    'ratio' => $ratio,
                    'level' => $level,
                ];
            }
        }

        return $results;
    }

    /**
     * Check Section 508 compliance for a color palette
     *
     * @param array $colors Array of hex color codes
     * @return array Compliance status and details
     */
    public function check_section508_compliance(array $colors): array {
        $results = [
            'status' => 'pass',
            'details' => [],
            'violations' => [],
        ];

        // Check contrast requirements
        foreach ($colors as $i => $color1) {
            foreach ($colors as $j => $color2) {
                if ($i === $j) continue;

                $ratio = $this->validate_contrast_ratio($color1, $color2);
                if ($ratio < self::SECTION_508_REQUIREMENTS['contrast_ratios']['minimum']) {
                    $results['status'] = 'fail';
                    $results['violations'][] = [
                        'type' => 'contrast',
                        'colors' => [$color1, $color2],
                        'ratio' => $ratio,
                        'required' => self::SECTION_508_REQUIREMENTS['contrast_ratios']['minimum'],
                    ];
                }
            }
        }

        // Check color blindness considerations
        foreach (self::SECTION_508_REQUIREMENTS['color_blindness'] as $type => $required) {
            if (!$required) continue;

            $simulation = $this->simulate_color_blindness($colors, $type);
            if (!$this->validate_color_blindness_distinction($simulation)) {
                $results['status'] = 'fail';
                $results['violations'][] = [
                    'type' => 'color_blindness',
                    'condition' => $type,
                    'colors' => $colors,
                ];
            }
        }

        return $results;
    }

    /**
     * Analyze detailed compliance status
     *
     * @param array $status Compliance check results
     * @return array Detailed analysis and recommendations
     */
    public function analyze_compliance_details(array $status): array {
        $analysis = [
            'summary' => [],
            'critical_issues' => [],
            'warnings' => [],
            'recommendations' => [],
        ];

        if (isset($status['violations']) && !empty($status['violations'])) {
            foreach ($status['violations'] as $violation) {
                if (isset($violation['type']) && $violation['type'] === 'contrast') {
                    $analysis['critical_issues'][] = sprintf(
                        'Insufficient contrast ratio (%f) between colors %s and %s. Minimum required: %f',
                        $violation['ratio'],
                        $violation['colors'][0],
                        $violation['colors'][1],
                        $violation['required']
                    );
                } elseif (isset($violation['type']) && $violation['type'] === 'color_blindness') {
                    $analysis['critical_issues'][] = sprintf(
                        'Colors may not be distinguishable for users with %s',
                        $violation['condition']
                    );
                }
            }
        }

        if (empty($analysis['critical_issues'])) {
            $analysis['summary'][] = 'Palette meets basic accessibility requirements';
        } else {
            $analysis['summary'][] = sprintf(
                'Palette has %d critical accessibility issues that need attention',
                count($analysis['critical_issues'])
            );
        }

        return $analysis;
    }

    /**
     * Generate compliance recommendations
     *
     * @return array List of recommendations for improving compliance
     */
    public function generate_compliance_recommendations(): array {
        return [
            'contrast' => [
                'Ensure text colors have a minimum contrast ratio of 4.5:1 with their background',
                'For large text (18pt or 14pt bold), maintain a minimum contrast ratio of 3:1',
                'Consider using darker shades for text colors to improve readability',
            ],
            'color_blindness' => [
                'Avoid relying solely on color to convey information',
                'Use patterns or icons in addition to color for important UI elements',
                'Test your palette with color blindness simulation tools',
            ],
            'general' => [
                'Provide sufficient color contrast between adjacent elements',
                'Consider using a color contrast checker tool during design',
                'Include focus indicators for interactive elements',
            ],
        ];
    }

    /**
     * Validate contrast ratio between two colors
     *
     * @param string $color1 First hex color code
     * @param string $color2 Second hex color code
     * @return float Contrast ratio value
     */
    public function validate_contrast_ratio(string $color1, string $color2): float {
        return $this->color_calculator->calculate_contrast_ratio($color1, $color2);
    }

    /**
     * Determine WCAG compliance level based on contrast ratio
     *
     * @param float $ratio Contrast ratio
     * @return string|false 'AA', 'AAA', or false if non-compliant
     */
    private function determine_wcag_level(float $ratio): string|false {
        if ($ratio >= self::WCAG_REQUIREMENTS['contrast_ratios']['AAA']['normal_text']['minimum']) {
            return 'AAA';
        } elseif ($ratio >= self::WCAG_REQUIREMENTS['contrast_ratios']['AA']['normal_text']['minimum']) {
            return 'AA';
        }
        return false;
    }

    /**
     * Simulate color blindness for a set of colors
     *
     * @param array  $colors Array of hex color codes
     * @param string $type   Type of color blindness
     * @return array Simulated colors
     */
    private function simulate_color_blindness(array $colors, string $type): array {
        // Implementation would use color vision deficiency simulation algorithms
        // This is a placeholder that would need actual implementation
        return $colors;
    }

    /**
     * Validate color distinction for color blindness
     *
     * @param array $colors Array of simulated colors
     * @return bool True if colors are sufficiently distinct
     */
    private function validate_color_blindness_distinction(array $colors): bool {
        // Implementation would check if simulated colors are distinguishable
        // This is a placeholder that would need actual implementation
        return true;
    }
}
