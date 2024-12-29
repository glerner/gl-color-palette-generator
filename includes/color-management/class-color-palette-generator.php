<?php
/**
 * Color Palette Generator Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Color_Management
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GL_Color_Palette_Generator\Color_Management;

use GL_Color_Palette_Generator\Interfaces\Color_Palette_Generator_Interface;
use GL_Color_Palette_Generator\Models\Color_Palette;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;
use WP_Error;

/**
 * Class Color_Palette_Generator
 *
 * Generates color palettes using various color theory algorithms.
 */
class Color_Palette_Generator implements Color_Palette_Generator_Interface {
    /**
     * Color utility instance
     *
     * @var Color_Utility
     */
    private Color_Utility $color_utility;

    /**
     * Constructor
     *
     * @param Color_Utility $color_utility Color utility instance.
     */
    public function __construct(Color_Utility $color_utility) {
        $this->color_utility = $color_utility;
    }

    /**
     * Generate a new color palette
     *
     * @param array $options Generation options.
     * @return Color_Palette|WP_Error Generated palette or error.
     */
    public function generate_palette(array $options = []): Color_Palette|WP_Error {
        try {
            $base_color = $options['base_color'] ?? $this->generate_random_color();
            $algorithm = $options['algorithm'] ?? 'monochromatic';

            if (!$this->is_valid_hex_color($base_color)) {
                return new WP_Error('invalid_color', __('Invalid base color provided', 'gl-color-palette-generator'));
            }

            switch ($algorithm) {
                case 'complementary':
                    return $this->generate_complementary($base_color, $options);
                case 'analogous':
                    return $this->generate_analogous($base_color, $options);
                case 'triadic':
                    return $this->generate_triadic($base_color, $options);
                case 'monochromatic':
                    return $this->generate_monochromatic($base_color, $options);
                default:
                    return new WP_Error('invalid_algorithm', __('Invalid algorithm specified', 'gl-color-palette-generator'));
            }
        } catch (\Exception $e) {
            return new WP_Error('generation_failed', $e->getMessage());
        }
    }

    /**
     * Generate complementary color palette
     *
     * @param string $base_color Base color to build from.
     * @param array  $options    Generation options.
     * @return Color_Palette|WP_Error Generated palette or error.
     */
    public function generate_complementary(string $base_color, array $options = []): Color_Palette|WP_Error {
        try {
            $hsl = $this->color_utility->hex_to_hsl($base_color);
            $complement_hsl = $hsl;
            $complement_hsl['h'] = ($hsl['h'] + 180) % 360;

            $colors = [
                'base' => $base_color,
                'complement' => $this->color_utility->hsl_to_hex($complement_hsl)
            ];

            return new Color_Palette($colors);
        } catch (\Exception $e) {
            return new WP_Error('complementary_generation_failed', $e->getMessage());
        }
    }

    /**
     * Generate analogous color palette
     *
     * @param string $base_color Base color to build from.
     * @param array  $options    Generation options.
     * @return Color_Palette|WP_Error Generated palette or error.
     */
    public function generate_analogous(string $base_color, array $options = []): Color_Palette|WP_Error {
        try {
            $hsl = $this->color_utility->hex_to_hsl($base_color);
            $angle = $options['angle'] ?? 30;

            $colors = [
                'base' => $base_color,
                'analogous1' => $this->color_utility->hsl_to_hex([
                    'h' => ($hsl['h'] + $angle) % 360,
                    's' => $hsl['s'],
                    'l' => $hsl['l']
                ]),
                'analogous2' => $this->color_utility->hsl_to_hex([
                    'h' => ($hsl['h'] - $angle + 360) % 360,
                    's' => $hsl['s'],
                    'l' => $hsl['l']
                ])
            ];

            return new Color_Palette($colors);
        } catch (\Exception $e) {
            return new WP_Error('analogous_generation_failed', $e->getMessage());
        }
    }

    /**
     * Generate triadic color palette
     *
     * @param string $base_color Base color to build from.
     * @param array  $options    Generation options.
     * @return Color_Palette|WP_Error Generated palette or error.
     */
    public function generate_triadic(string $base_color, array $options = []): Color_Palette|WP_Error {
        try {
            $hsl = $this->color_utility->hex_to_hsl($base_color);

            $colors = [
                'base' => $base_color,
                'triad1' => $this->color_utility->hsl_to_hex([
                    'h' => ($hsl['h'] + 120) % 360,
                    's' => $hsl['s'],
                    'l' => $hsl['l']
                ]),
                'triad2' => $this->color_utility->hsl_to_hex([
                    'h' => ($hsl['h'] + 240) % 360,
                    's' => $hsl['s'],
                    'l' => $hsl['l']
                ])
            ];

            return new Color_Palette($colors);
        } catch (\Exception $e) {
            return new WP_Error('triadic_generation_failed', $e->getMessage());
        }
    }

    /**
     * Generate monochromatic color palette
     *
     * @param string $base_color Base color to build from.
     * @param array  $options    Generation options.
     * @return Color_Palette|WP_Error Generated palette or error.
     */
    public function generate_monochromatic(string $base_color, array $options = []): Color_Palette|WP_Error {
        try {
            $hsl = $this->color_utility->hex_to_hsl($base_color);
            $count = $options['count'] ?? 5;
            $step = 100 / ($count + 1);

            $colors = ['base' => $base_color];
            for ($i = 1; $i < $count; $i++) {
                $new_hsl = $hsl;
                $new_hsl['l'] = min(100, max(0, $step * $i));
                $colors["shade$i"] = $this->color_utility->hsl_to_hex($new_hsl);
            }

            return new Color_Palette($colors);
        } catch (\Exception $e) {
            return new WP_Error('monochromatic_generation_failed', $e->getMessage());
        }
    }

    /**
     * Get available generation algorithms
     *
     * @return array List of available algorithms.
     */
    public function get_available_algorithms(): array {
        return [
            'monochromatic' => __('Monochromatic', 'gl-color-palette-generator'),
            'complementary' => __('Complementary', 'gl-color-palette-generator'),
            'analogous' => __('Analogous', 'gl-color-palette-generator'),
            'triadic' => __('Triadic', 'gl-color-palette-generator')
        ];
    }

    /**
     * Get default generation options
     *
     * @return array Default options.
     */
    public function get_default_options(): array {
        return [
            'algorithm' => 'monochromatic',
            'count' => 5,
            'angle' => 30,
            'contrast_ratio' => Color_Constants::WCAG_CONTRAST_AA
        ];
    }

    /**
     * Generate a random color
     *
     * @return string Random color in hex format
     */
    private function generate_random_color(): string {
        return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
    }

    /**
     * Check if a color is in valid hex format
     *
     * @param string $color Color to check
     * @return bool True if valid hex color
     */
    private function is_valid_hex_color(string $color): bool {
        return (bool) preg_match('/^#[a-f0-9]{6}$/i', $color);
    }
}
