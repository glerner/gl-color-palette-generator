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
     *
     * @param array $base_colors Array of base colors to generate variations from
     * @return array Array of style variations
     */
    public function generate_style_variations(array $base_colors): array {
        $variations = [];
        $color_names = ['Primary', 'Secondary', 'Tertiary', 'Accent'];
        
        // Generate all possible combinations of the base colors
        $combinations = $this->generate_color_combinations($base_colors, count($color_names));
        
        foreach ($combinations as $index => $combo) {
            $colors = [];
            foreach ($combo as $i => $color) {
                // Create variations for each color
                $colors = array_merge($colors, [
                    [
                        'name' => $color_names[$i],
                        'hex' => $color
                    ],
                    [
                        'name' => $color_names[$i] . ' Lighter',
                        'hex' => $this->color_processor->lighten($color, 40)
                    ],
                    [
                        'name' => $color_names[$i] . ' Light',
                        'hex' => $this->color_processor->lighten($color, 20)
                    ],
                    [
                        'name' => $color_names[$i] . ' Dark',
                        'hex' => $this->color_processor->darken($color, 20)
                    ],
                    [
                        'name' => $color_names[$i] . ' Darker',
                        'hex' => $this->color_processor->darken($color, 40)
                    ]
                ]);
            }

            // Add black and white
            $colors[] = ['name' => 'Black', 'hex' => '#000000'];
            $colors[] = ['name' => 'White', 'hex' => '#FFFFFF'];

            // Generate variation name based on first color of each role
            $variation_name = implode('-', array_map(function($c) {
                return str_replace(' ', '', $c['name']);
            }, array_filter($colors, function($c) use ($color_names) {
                return in_array($c['name'], $color_names);
            })));

            $variations[$variation_name] = $this->generate_variation($colors, $variation_name);
        }

        return $variations;
    }

    /**
     * Generate all possible combinations of colors
     *
     * @param array $colors Array of colors to combine
     * @param int $length Length of each combination
     * @return array Array of color combinations
     */
    private function generate_color_combinations(array $colors, int $length): array {
        if ($length === 1) {
            return array_map(function($color) {
                return [$color];
            }, $colors);
        }

        $combinations = [];
        for ($i = 0; $i < count($colors); $i++) {
            $current = $colors[$i];
            $remaining = array_slice($colors, 0);
            unset($remaining[$i]);
            $subCombinations = $this->generate_color_combinations($remaining, $length - 1);
            
            foreach ($subCombinations as $subCombo) {
                array_unshift($subCombo, $current);
                $combinations[] = $subCombo;
            }
        }

        return $combinations;
    }

    /**
     * Save style variations to theme directory
     *
     * @param array $variations Style variations to save
     * @param string $theme_dir Theme directory path
     * @return bool True on success, false on failure
     */
    public function save_style_variations(array $variations, string $theme_dir): bool {
        $styles_dir = trailingslashit($theme_dir) . 'styles';
        
        // Create styles directory if it doesn't exist
        if (!file_exists($styles_dir)) {
            if (!mkdir($styles_dir, 0755, true)) {
                return false;
            }
        }

        // Save each variation
        foreach ($variations as $name => $variation) {
            $filename = $styles_dir . '/' . sanitize_file_name($name) . '.json';
            if (file_put_contents($filename, wp_json_encode($variation, JSON_PRETTY_PRINT)) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Create ZIP archive of style variations
     *
     * @param array $variations Style variations to archive
     * @param string $theme_dir Theme directory path
     * @return string|bool Path to ZIP file on success, false on failure
     */
    public function create_variations_archive(array $variations, string $theme_dir): string|bool {
        $styles_dir = trailingslashit($theme_dir) . 'styles';
        $zip_path = $styles_dir . '/variations.zip';

        $zip = new \ZipArchive();
        if ($zip->open($zip_path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return false;
        }

        foreach ($variations as $name => $variation) {
            $filename = sanitize_file_name($name) . '.json';
            $zip->addFromString($filename, wp_json_encode($variation, JSON_PRETTY_PRINT));
        }

        $zip->close();
        return $zip_path;
    }
}
