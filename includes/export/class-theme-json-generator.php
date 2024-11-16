<?php
/**
 * Theme.json Generator Class
 *
 * @package ColorPaletteGenerator
 */
namespace GLColorPalette;

if (!defined('ABSPATH')) {
    exit;
}

class ThemeJsonGenerator {
    private $contrast_checker;
    private $color_processor;
    private $base_theme_json;

    /**
     * Constructor
     *
     * @param ContrastChecker $contrast_checker Contrast checker instance
     */
    public function __construct(ContrastChecker $contrast_checker) {
        $this->contrast_checker = $contrast_checker;
        $this->color_processor = new ColorProcessor();
        $this->base_theme_json = $this->get_base_theme_json();
    }

    /**
     * Get base theme.json structure
     */
    private function get_base_theme_json() {
        return [
            '$schema' => 'https://schemas.wp.org/trunk/theme.json',
            'version' => 2,
            'settings' => [
                'color' => [
                    'palette' => []
                ]
            ]
        ];
    }

    /**
     * Create monochromatic variation
     */
    private function create_monochromatic_variation($base_color) {
        return [
            ['name' => 'Primary', 'hex' => $base_color],
            ['name' => 'Light', 'hex' => $this->color_processor->lighten($base_color, 20)],
            ['name' => 'Lighter', 'hex' => $this->color_processor->lighten($base_color, 40)],
            ['name' => 'Dark', 'hex' => $this->color_processor->darken($base_color, 20)],
            ['name' => 'Darker', 'hex' => $this->color_processor->darken($base_color, 40)]
        ];
    }

    /**
     * Create analogous variation
     */
    private function create_analogous_variation($base_color) {
        return [
            ['name' => 'Primary', 'hex' => $base_color],
            ['name' => 'Secondary', 'hex' => $this->color_processor->adjust_hue($base_color, 30)],
            ['name' => 'Secondary Light', 'hex' => $this->color_processor->lighten(
                $this->color_processor->adjust_hue($base_color, 30),
                15
            )],
            ['name' => 'Tertiary', 'hex' => $this->color_processor->adjust_hue($base_color, -30)],
            ['name' => 'Tertiary Light', 'hex' => $this->color_processor->lighten(
                $this->color_processor->adjust_hue($base_color, -30),
                15
            )]
        ];
    }

    /**
     * Create complementary variation
     */
    private function create_complementary_variation($base_color) {
        $complement = $this->color_processor->adjust_hue($base_color, 180);

        return [
            ['name' => 'Primary', 'hex' => $base_color],
            ['name' => 'Primary Light', 'hex' => $this->color_processor->lighten($base_color, 15)],
            ['name' => 'Primary Dark', 'hex' => $this->color_processor->darken($base_color, 15)],
            ['name' => 'Complementary', 'hex' => $complement],
            ['name' => 'Complementary Light', 'hex' => $this->color_processor->lighten($complement, 15)],
            ['name' => 'Complementary Dark', 'hex' => $this->color_processor->darken($complement, 15)]
        ];
    }

    /**
     * Generate theme.json content with color palette
     */
    public function generate_theme_json($colors) {
        $theme_json = $this->base_theme_json;
        $palette = [];

        foreach ($colors as $color) {
            $palette[] = [
                'slug'  => sanitize_title($color['name']),
                'name'  => $color['name'],
                'color' => $color['hex']
            ];
        }

        $theme_json['settings']['color']['palette'] = $palette;
        return $theme_json;
    }

    /**
     * Generate variation of theme.json
     */
    public function generate_variation($colors, $variation_name) {
        return [
            'title' => $variation_name,
            'settings' => [
                'color' => [
                    'palette' => array_map(function($color) {
                        return [
                            'slug'  => sanitize_title($color['name']),
                            'name'  => $color['name'],
                            'color' => $color['hex']
                        ];
                    }, $colors)
                ]
            ]
        ];
    }

    /**
     * Generate variations for different color schemes
     */
}
