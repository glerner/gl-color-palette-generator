<?php

namespace GL_Color_Palette_Generator\Color_Management;

use GL_Color_Palette_Generator\Interfaces\Color_Shade_Generator_Interface;
use GL_Color_Palette_Generator\Interfaces\AccessibilityChecker;

/**
 * Color Shade Generator Class
 *
 * Generates accessible tints and shades from a base color.
 * This is distinct from WordPress theme style variations - it focuses on
 * creating lighter and darker versions of a single color that meet WCAG standards.
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
class Color_Shade_Generator implements Color_Shade_Generator_Interface {
    /**
     * @var AccessibilityChecker
     */
    private $accessibility_checker;

    /**
     * Constructor
     *
     * @param AccessibilityChecker $accessibility_checker Accessibility checker instance
     */
    public function __construct(AccessibilityChecker $accessibility_checker) {
        $this->accessibility_checker = $accessibility_checker;
    }

    /**
     * Generate accessible tints and shades
     *
     * @param string $color Base color in hex format
     * @param array  $options Generation options
     * @return array Array of accessible tints and shades
     */
    public function generate_tints_and_shades(string $color, array $options = []): array {
        // Set default options
        $options = array_merge([
            'include_base' => true,
            'contrast_level' => 'AA',
            'small_text' => true,
            'custom_steps' => []
        ], $options);

        // Convert hex to RGB
        $hex = ltrim($color, '#');
        list($r, $g, $b) = sscanf($hex, "%02x%02x%02x");

        // Generate variations with increasing contrast until requirements are met
        $variations = [];

        // Define brightness steps
        $steps = $options['custom_steps'] ?: [
            'lighter' => [40, 80, 5],  // [start, end, step]
            'light'   => [20, 60, 5],
            'dark'    => [-20, -60, -5],
            'darker'  => [-40, -80, -5]
        ];

        // Generate each variation
        foreach ($steps as $variant => list($start, $end, $step)) {
            for ($i = $start; $step > 0 ? $i <= $end : $i >= $end; $i += $step) {
                $color = $this->adjust_brightness([$r, $g, $b], $i);
                if ($this->meets_contrast_requirements($color, $options)) {
                    $variations[$variant] = $color;
                    break;
                }
            }
        }

        // Only include base color if it meets contrast requirements and is requested
        if ($options['include_base'] && $this->meets_contrast_requirements($color, $options)) {
            $variations['base'] = $color;
        }

        return [
            'original' => $color,
            'variations' => $variations
        ];
    }

    /**
     * Check if a color meets contrast requirements
     *
     * @param string $color Color to check
     * @param array  $options Check options
     * @return bool True if color meets requirements
     */
    public function meets_contrast_requirements(string $color, array $options = []): bool {
        $options = array_merge([
            'contrast_level' => 'AA',
            'small_text' => true
        ], $options);

        $white_contrast = $this->accessibility_checker->calculate_contrast_ratio($color, '#FFFFFF');
        $black_contrast = $this->accessibility_checker->calculate_contrast_ratio($color, '#000000');

        $min_contrast = $options['contrast_level'] === 'AAA'
            ? ($options['small_text'] ? 7.0 : 4.5)
            : ($options['small_text'] ? 4.5 : 3.0);

        return ($white_contrast >= $min_contrast || $black_contrast >= $min_contrast);
    }

    /**
     * Adjust brightness of an RGB color
     *
     * @param array $rgb RGB color values
     * @param int   $steps Steps to adjust (-100 to 100)
     * @return string Hex color code
     */
    private function adjust_brightness(array $rgb, int $steps): string {
        $steps = max(-100, min(100, $steps));

        if ($steps > 0) {
            $rgb = array_map(function($value) use ($steps) {
                return $value + ((255 - $value) * ($steps / 100));
            }, $rgb);
        } else {
            $rgb = array_map(function($value) use ($steps) {
                return $value * (1 + ($steps / 100));
            }, $rgb);
        }

        $rgb = array_map('round', $rgb);
        return sprintf('#%02x%02x%02x', ...$rgb);
    }
}
