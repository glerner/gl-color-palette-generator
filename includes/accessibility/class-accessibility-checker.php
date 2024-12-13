<?php
/**
 * Accessibility Checker Class
 *
 * Checks color combinations for WCAG 2.1 compliance and accessibility requirements.
 * Provides comprehensive accessibility testing including contrast ratios,
 * color blindness simulation, and readability analysis.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Accessibility
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Accessibility;

/**
 * Class AccessibilityChecker
 *
 * Handles accessibility testing and validation for color combinations.
 * Implements WCAG 2.1 guidelines and provides color blindness simulation.
 *
 * @since 1.0.0
 */
class AccessibilityChecker {
    /**
     * WCAG 2.1 contrast ratios
     *
     * @var array Minimum contrast ratios for AA and AAA compliance
     */
    private $contrast_ratios = [
        'AA' => ['normal' => 4.5, 'large' => 3.0],
        'AAA' => ['normal' => 7.0, 'large' => 4.5]
    ];

    /**
     * Color blindness types
     *
     * @var array Supported color blindness simulation types
     */
    private $colorblind_types = [
        'protanopia',
        'deuteranopia',
        'tritanopia',
        'achromatopsia'
    ];

    /**
     * Check accessibility compliance
     *
     * @param string $foreground Foreground color hex code
     * @param string $background Background color hex code
     * @param array $options Check options
     * @return array Results and recommendations
     */
    public function check_accessibility($foreground, $background, $options = []) {
        $results = [
            'passes_aa' => false,
            'passes_aaa' => false,
            'contrast_ratio' => 0,
            'colorblind_safe' => false,
            'readable' => false,
            'recommendations' => []
        ];

        try {
            // Calculate contrast ratio
            $ratio = $this->calculate_contrast_ratio($foreground, $background);
            $results['contrast_ratio'] = round($ratio, 2);

            // Check WCAG levels
            $text_size = $options['large_text'] ?? false;
            $required_aa = $text_size ? 
                $this->contrast_ratios['AA']['large'] : 
                $this->contrast_ratios['AA']['normal'];
            $required_aaa = $text_size ? 
                $this->contrast_ratios['AAA']['large'] : 
                $this->contrast_ratios['AAA']['normal'];

            $results['passes_aa'] = $ratio >= $required_aa;
            $results['passes_aaa'] = $ratio >= $required_aaa;

            // Add recommendations if needed
            if (!$results['passes_aa']) {
                $results['recommendations'][] = $this->suggest_contrast_improvement(
                    $foreground,
                    $background,
                    $required_aa
                );
            }

            // Check colorblind accessibility
            $results['colorblind_safe'] = $this->check_colorblind_safety(
                $foreground,
                $background
            );

            // Check readability
            $results['readable'] = $this->check_readability(
                $foreground,
                $background
            );

        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Calculate contrast ratio between two colors
     *
     * @param string $color1 First color hex code
     * @param string $color2 Second color hex code
     * @return float Contrast ratio
     */
    public function calculate_contrast_ratio($color1, $color2) {
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
        // Convert to RGB
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        // Calculate luminance
        $r = ($r <= 0.03928) ? $r / 12.92 : pow(($r + 0.055) / 1.055, 2.4);
        $g = ($g <= 0.03928) ? $g / 12.92 : pow(($g + 0.055) / 1.055, 2.4);
        $b = ($b <= 0.03928) ? $b / 12.92 : pow(($b + 0.055) / 1.055, 2.4);

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    /**
     * Check if colors are safe for colorblind users
     *
     * @param string $color1 First color hex code
     * @param string $color2 Second color hex code
     * @return bool True if colorblind safe
     */
    public function check_colorblind_safety($color1, $color2) {
        foreach ($this->colorblind_types as $type) {
            $simulated1 = $this->simulate_colorblind_vision($color1, $type);
            $simulated2 = $this->simulate_colorblind_vision($color2, $type);

            $contrast = $this->calculate_contrast_ratio($simulated1, $simulated2);
            if ($contrast < $this->contrast_ratios['AA']['normal']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if text is readable on background
     *
     * @param string $text_color Text color hex code
     * @param string $bg_color Background color hex code
     * @return bool True if readable
     */
    public function check_readability($text_color, $bg_color) {
        // Check contrast ratio
        $contrast = $this->calculate_contrast_ratio($text_color, $bg_color);
        if ($contrast < $this->contrast_ratios['AA']['normal']) {
            return false;
        }

        // Check color blindness issues
        if (!$this->check_colorblind_safety($text_color, $bg_color)) {
            return false;
        }

        // Check readability
        $bg_luminance = $this->get_relative_luminance($bg_color);
        $text_luminance = $this->get_relative_luminance($text_color);

        return abs($bg_luminance - $text_luminance) >= 0.3;
    }

    /**
     * Suggest improvements for better contrast
     *
     * @param string $foreground Foreground color hex code
     * @param string $background Background color hex code
     * @param float $target_ratio Target contrast ratio
     * @return array Improvement suggestions
     */
    private function suggest_contrast_improvement($foreground, $background, $target_ratio) {
        $current_ratio = $this->calculate_contrast_ratio($foreground, $background);
        $fg_luminance = $this->get_relative_luminance($foreground);
        $bg_luminance = $this->get_relative_luminance($background);

        if ($fg_luminance > $bg_luminance) {
            return [
                'message' => 'Try increasing the lightness of the foreground color',
                'target_ratio' => $target_ratio,
                'current_ratio' => $current_ratio
            ];
        } else {
            return [
                'message' => 'Try decreasing the lightness of the foreground color',
                'target_ratio' => $target_ratio,
                'current_ratio' => $current_ratio
            ];
        }
    }

    /**
     * Simulate color blindness vision
     *
     * @param string $color Color hex code
     * @param string $type Type of color blindness
     * @return string Simulated color hex code
     */
    private function simulate_colorblind_vision($color, $type) {
        // Basic simulation - in reality, would use proper matrices
        $hex = ltrim($color, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        switch ($type) {
            case 'protanopia':
                $r = ($r * 0.567) + ($g * 0.433);
                break;
            case 'deuteranopia':
                $g = ($r * 0.558) + ($g * 0.442);
                break;
            case 'tritanopia':
                $b = ($g * 0.375) + ($b * 0.625);
                break;
            case 'achromatopsia':
                $gray = ($r * 0.299) + ($g * 0.587) + ($b * 0.114);
                $r = $g = $b = $gray;
                break;
        }

        return sprintf('#%02x%02x%02x', 
            min(255, max(0, round($r))),
            min(255, max(0, round($g))),
            min(255, max(0, round($b)))
        );
    }

    /**
     * Generate accessibility report
     */
    public function generate_accessibility_report($palette) {
        $compliance = $this->check_wcag_compliance($palette);
        $contrast_matrix = $this->generate_contrast_matrix($palette);

        return [
            'compliance_status' => $compliance,
            'contrast_analysis' => [
                'matrix' => $contrast_matrix,
                'failing_combinations' => $this->identify_failing_combinations($contrast_matrix),
                'recommendations' => $this->generate_contrast_recommendations($contrast_matrix)
            ],
            'color_blindness_analysis' => [
                'protanopia' => $this->analyze_protanopia_impact($palette),
                'deuteranopia' => $this->analyze_deuteranopia_impact($palette),
                'tritanopia' => $this->analyze_tritanopia_impact($palette)
            ],
            'readability_analysis' => $this->analyze_text_readability($palette)
        ];
    }

    /**
     * Optimize for accessibility
     */
    public function optimize_for_accessibility($palette, $target_level = 'AA') {
        $original_compliance = $this->check_wcag_compliance($palette);
        $optimized_palette = $this->optimize_palette($palette, $target_level);

        return [
            'original_palette' => $palette,
            'optimized_palette' => $optimized_palette,
            'compliance_improvement' => [
                'before' => $original_compliance,
                'after' => $this->check_wcag_compliance($optimized_palette)
            ],
            'optimization_steps' => $this->get_optimization_steps(),
            'implementation_guide' => $this->generate_implementation_guide($optimized_palette)
        ];
    }
}
