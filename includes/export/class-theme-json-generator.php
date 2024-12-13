<?php
/**
 * Theme.json Generator Class
 *
 * @package ColorPaletteGenerator
 */
namespace GLColorPalette;

use GLColorPalette\Color_Management\Color_Variation_Generator;
use GLColorPalette\Interfaces\AccessibilityChecker;

if (!defined('ABSPATH')) {
    exit;
}

class ThemeJsonGenerator {
    protected $contrast_checker;
    protected $color_processor;
    protected $base_theme_json;
    protected $variation_generator;

    /**
     * Constructor
     *
     * @param AccessibilityChecker $accessibility_checker Accessibility checker instance
     */
    public function __construct(AccessibilityChecker $accessibility_checker) {
        $this->contrast_checker = $accessibility_checker;
        $this->color_processor = new ColorProcessor();
        $this->base_theme_json = $this->get_base_theme_json();
        $this->variation_generator = new Color_Variation_Generator($accessibility_checker);
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
     * Generate color variations that meet accessibility requirements
     *
     * @param string $base_color Base color in hex format
     * @return array Array of color variations that meet contrast requirements
     */
    private function generate_color_variations($base_color) {
        $result = $this->variation_generator->generate_variations($base_color, [
            'contrast_level' => 'AA',
            'small_text' => true,
            'include_base' => true
        ]);

        return $result['variations'];
    }

    /**
     * Check if a color meets WCAG contrast requirements against black or white
     *
     * @param string $color Color in hex format
     * @return bool True if meets requirements
     */
    private function meets_contrast_requirements($color) {
        $hex = ltrim($color, '#');
        list($r, $g, $b) = sscanf($hex, "%02x%02x%02x");

        // Calculate relative luminance
        $r = $r / 255;
        $g = $g / 255;
        $b = $b / 255;

        $r = $r <= 0.03928 ? $r / 12.92 : pow(($r + 0.055) / 1.055, 2.4);
        $g = $g <= 0.03928 ? $g / 12.92 : pow(($g + 0.055) / 1.055, 2.4);
        $b = $b <= 0.03928 ? $b / 12.92 : pow(($b + 0.055) / 1.055, 2.4);

        $L = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;

        // Calculate contrast with black (0) and white (1)
        $contrast_black = ($L + 0.05) / (0 + 0.05);
        $contrast_white = (1 + 0.05) / ($L + 0.05);

        // WCAG 2.0 level AA requires contrast of at least 4.5:1 for normal text
        return max($contrast_black, $contrast_white) >= 4.5;
    }

    /**
     * Adjust brightness of RGB color
     *
     * @param array $rgb RGB color values
     * @param int $percent Percentage to adjust (-100 to 100)
     * @return string Hex color code
     */
    private function adjust_brightness($rgb, $percent) {
        $rgb = array_map(function($color) use ($percent) {
            return max(0, min(255, $color + ($color * ($percent / 100))));
        }, $rgb);

        return sprintf("#%02x%02x%02x", ...$rgb);
    }

    /**
     * Creative color names from meodai/color-names (MIT License)
     * A subset focused on web-friendly, evocative names
     */
    private $creative_colors = [
        // Reds
        '#FF0000' => 'Firecracker',
        '#DC143C' => 'Dragon Heart',
        '#B22222' => 'Autumn Maple',
        '#CD5C5C' => 'Desert Rose',
        '#FF4500' => 'Sunset Blaze',
        
        // Oranges
        '#FFA500' => 'Harvest Moon',
        '#FF8C00' => 'Amber Waves',
        '#FF7F50' => 'Desert Dawn',
        '#FFD700' => 'Golden Hour',
        
        // Yellows
        '#FFD700' => 'Morning Light',
        '#F0E68C' => 'Summer Wheat',
        '#BDB76B' => 'Prairie Grass',
        
        // Greens
        '#008000' => 'Forest Heart',
        '#228B22' => 'Mountain Pine',
        '#32CD32' => 'Spring Leaves',
        '#90EE90' => 'Morning Dew',
        '#98FB98' => 'Whisper Mint',
        
        // Blues
        '#0000FF' => 'Ocean Deep',
        '#1E90FF' => 'Sky Spirit',
        '#87CEEB' => 'Cloud Dancer',
        '#4169E1' => 'Twilight Blue',
        
        // Purples
        '#800080' => 'Mystic Night',
        '#8B008B' => 'Dream Weaver',
        '#9932CC' => 'Evening Storm',
        '#9400D3' => 'Midnight Bloom',
        
        // Browns
        '#8B4513' => 'Earth Song',
        '#A0522D' => 'Canyon Dust',
        '#CD853F' => 'Desert Wind',
        
        // Grays
        '#808080' => 'Mountain Mist',
        '#A9A9A9' => 'Storm Cloud',
        '#D3D3D3' => 'Morning Fog',
        
        // Special colors for specific contexts
        '#FFB6C1' => 'Dawn Blossom',
        '#98FF98' => 'Spring Whisper',
        '#E6E6FA' => 'Mountain Air',
        '#F0FFF0' => 'Morning Frost'
    ];

    /**
     * Get artistic name for a color
     * 
     * @param string $hex_color Hex color code
     * @return string Color name
     */
    private function get_artistic_name($hex_color) {
        // First try exact match
        if (isset($this->creative_colors[$hex_color])) {
            return $this->creative_colors[$hex_color];
        }

        // If no exact match, find closest color
        $closest = null;
        $closest_distance = PHP_FLOAT_MAX;
        
        list($r1, $g1, $b1) = sscanf(ltrim($hex_color, '#'), "%02x%02x%02x");
        
        foreach (array_keys($this->creative_colors) as $key) {
            list($r2, $g2, $b2) = sscanf(ltrim($key, '#'), "%02x%02x%02x");
            
            // Calculate color distance using CIEDE2000 formula
            $distance = $this->calculate_color_distance(
                [$r1, $g1, $b1],
                [$r2, $g2, $b2]
            );
            
            if ($distance < $closest_distance) {
                $closest = $key;
                $closest_distance = $distance;
            }
        }
        
        // Fallback to descriptive name if no good match found
        if ($closest_distance > 50) {
            return $this->get_descriptive_name([$r1, $g1, $b1]);
        }
        
        return $this->creative_colors[$closest];
    }

    /**
     * Get a descriptive name for a color based on its RGB values
     * 
     * @param array $rgb RGB color values
     * @return string Descriptive name
     */
    private function get_descriptive_name($rgb) {
        list($r, $g, $b) = $rgb;
        
        // Convert to HSL for better naming
        $max = max($r, $g, $b) / 255;
        $min = min($r, $g, $b) / 255;
        $l = ($max + $min) / 2;
        
        if ($max == $min) {
            $h = $s = 0;
        } else {
            $d = $max - $min;
            $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
            
            switch ($max) {
                case $r / 255:
                    $h = ($g / 255 - $b / 255) / $d + ($g < $b ? 6 : 0);
                    break;
                case $g / 255:
                    $h = ($b / 255 - $r / 255) / $d + 2;
                    break;
                case $b / 255:
                    $h = ($r / 255 - $g / 255) / $d + 4;
                    break;
            }
            $h /= 6;
        }
        
        // Generate descriptive name
        $intensity = $l < 0.25 ? 'Deep ' : ($l > 0.75 ? 'Bright ' : '');
        $saturation = $s < 0.25 ? 'Muted ' : ($s > 0.75 ? 'Vibrant ' : '');
        
        $hue = match(true) {
            $h < 0.025 || $h >= 0.958 => 'Crimson',
            $h < 0.042 => 'Sunset',
            $h < 0.075 => 'Amber',
            $h < 0.108 => 'Honey',
            $h < 0.142 => 'Sunlight',
            $h < 0.208 => 'Spring',
            $h < 0.292 => 'Forest',
            $h < 0.375 => 'Ocean',
            $h < 0.458 => 'Sky',
            $h < 0.542 => 'Azure',
            $h < 0.625 => 'Twilight',
            $h < 0.708 => 'Storm',
            $h < 0.792 => 'Dusk',
            $h < 0.875 => 'Evening',
            $h < 0.958 => 'Dawn',
            default => 'Crimson'
        };
        
        return trim($intensity . $saturation . $hue);
    }

    /**
     * Calculate color distance using a simplified CIEDE2000 formula
     * 
     * @param array $rgb1 First RGB color
     * @param array $rgb2 Second RGB color
     * @return float Distance between colors
     */
    private function calculate_color_distance($rgb1, $rgb2) {
        // Simple weighted Euclidean distance that approximates human perception
        list($r1, $g1, $b1) = $rgb1;
        list($r2, $g2, $b2) = $rgb2;
        
        $r_mean = ($r1 + $r2) / 2;
        $r = $r1 - $r2;
        $g = $g1 - $g2;
        $b = $b1 - $b2;
        
        return sqrt(
            (2 + $r_mean / 256) * $r * $r +
            4 * $g * $g +
            (2 + (255 - $r_mean) / 256) * $b * $b
        );
    }

    /**
     * Generate WordPress theme style variations from base colors
     * 
     * @param array $base_colors Array of 4 hex color codes
     * @return array Array of WordPress theme style variations
     * 
     * @todo Add support for different WordPress theme naming conventions:
     *       - TwentyTwentyFour: primary/secondary/tertiary/accent with variations
     *       - TwentyTwentyFive: accent-1 through accent-6, base/contrast system
     * @todo Integrate with Codeium API to generate artistic color names
     */
    public function generate_theme_style_variations($base_colors) {
        if (count($base_colors) !== 4) {
            throw new \InvalidArgumentException('Exactly 4 base colors must be provided');
        }

        // Get artistic names for all base colors at once
        $artistic_names = [];
        foreach ($base_colors as $hex) {
            $artistic_names[$hex] = $this->get_artistic_name($hex);
        }
        $variations = [];
        $roles = ['primary', 'secondary', 'tertiary', 'accent'];

        // Generate all role combinations
        $combinations = [];
        for ($i = 0; $i < 4; $i++) {
            for ($j = 0; $j < 4; $j++) {
                if ($j !== $i) {
                    for ($k = 0; $k < 4; $k++) {
                        if ($k !== $i && $k !== $j) {
                            $l = 6 - ($i + $j + $k); // Last remaining index
                            $combinations[] = [$i, $j, $k, $l];
                        }
                    }
                }
            }
        }

        // Create variations for each combination
        foreach ($combinations as $combo) {
            $palette = [];
            $variation_colors = [];

            // Add base colors with variations
            for ($i = 0; $i < 4; $i++) {
                $color = $base_colors[$combo[$i]];
                $role = $roles[$i];
                $color_variations = $this->generate_color_variations($color);
                
                // Store base color for naming the variation
                if ($role === 'primary' && isset($color_variations['base'])) {
                    $primary_base = $color_variations['base'];
                }
                
                foreach ($color_variations as $variant => $hex) {
                    $palette[] = $this->generate_palette_entry($role, $variant, $hex);
                }
            }

            // Add standard colors
            $palette[] = [
                'name' => 'White',
                'slug' => 'white',
                'color' => '#FFFFFF'
            ];
            $palette[] = [
                'name' => 'Black',
                'slug' => 'black',
                'color' => '#000000'
            ];
            $palette[] = [
                'name' => 'Transparent',
                'slug' => 'transparent',
                'color' => 'transparent'
            ];

            // Generate gradients
            $gradients = $this->generate_gradients($palette);

            // Create variation name based on primary color's artistic name
            $primary_color = isset($primary_base) ? $primary_base : $base_colors[0];
            $name = $artistic_names[$primary_color];

            $variations[$name] = [
                'title' => $name,
                'settings' => [
                    'color' => [
                        'palette' => $palette,
                        'gradients' => $gradients
                    ]
                ]
            ];
        }

        return $variations;
    }

    /**
     * Generate palette entry for a color variation
     *
     * @param string $role Color role (primary, secondary, etc.)
     * @param string $variant Variation type (lighter, light, etc.)
     * @param string $color Color in hex format
     * @return array Palette entry
     */
    private function generate_palette_entry($role, $variant, $color) {
        // Generate role-based name and slug
        $role_name = ucfirst($role);
        if ($variant !== 'base') {
            $role_name .= ' ' . ucfirst($variant);
        }

        $slug = $role;
        if ($variant !== 'base') {
            $slug .= '-' . $variant;
        }

        // Generate artistic name
        $artistic_name = $this->get_artistic_name($color);
        if ($variant !== 'base') {
            $artistic_name .= ' ' . ucfirst($variant);
        }

        return [
            'name' => $role_name, // Display name shows the role
            'artisticName' => $artistic_name, // Custom property for artistic name
            'slug' => $slug,
            'color' => $color
        ];
    }

    /**
     * Generate gradient combinations from palette colors
     *
     * @param array $palette Color palette
     * @return array Array of gradient definitions
     */
    private function generate_gradients($palette) {
        $gradients = [];
        $base_colors = array_filter($palette, function($color) {
            return !str_contains($color['name'], 'lighter') &&
                   !str_contains($color['name'], 'light') &&
                   !str_contains($color['name'], 'dark') &&
                   !str_contains($color['name'], 'darker');
        });

        // Linear gradients
        foreach ($base_colors as $color1) {
            foreach ($base_colors as $color2) {
                if ($color1['slug'] !== $color2['slug']) {
                    $gradients[] = [
                        'name' => "{$color1['name']} to {$color2['name']}",
                        'slug' => "{$color1['slug']}-to-{$color2['slug']}",
                        'gradient' => "linear-gradient(135deg, {$color1['color']} 0%, {$color2['color']} 100%)"
                    ];
                }
            }
        }

        // Radial gradients for accent colors
        $accent = array_filter($base_colors, function($color) {
            return str_contains($color['name'], 'Accent');
        });

        foreach ($accent as $color) {
            $gradients[] = [
                'name' => "Radial {$color['name']}",
                'slug' => "radial-{$color['slug']}",
                'gradient' => "radial-gradient(circle, {$color['color']} 0%, transparent 100%)"
            ];
        }

        return $gradients;
    }

    /**
     * Get all permutations of an array
     *
     * @param array $items Array to get permutations of
     * @return array Array of all possible permutations
     */
    private function get_permutations($items) {
        if (count($items) <= 1) {
            return [$items];
        }

        $permutations = [];
        $item = array_shift($items);

        foreach ($this->get_permutations($items) as $permutation) {
            for ($i = 0; $i <= count($permutation); $i++) {
                $new = array_merge(
                    array_slice($permutation, 0, $i),
                    [$item],
                    array_slice($permutation, $i)
                );
                $permutations[] = $new;
            }
        }

        return $permutations;
    }

    /**
     * Get a human-readable name for a color
     *
     * @param string $hex_color Hex color code
     * @return string Color name
     */
    private function get_color_name($hex_color) {
        $colors = [
            '#FF0000' => 'Red',
            '#00FF00' => 'Green',
            '#0000FF' => 'Blue',
            '#800080' => 'Purple',
            // Add more mappings as needed
        ];

        return $colors[$hex_color] ?? 'Custom';
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
