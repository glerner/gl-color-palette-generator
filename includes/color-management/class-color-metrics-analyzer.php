<?php
/**
 * Color Metrics Analyzer Class
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\Color_Management;

/**
 * Error handler trait and logging setup
 */
use \GL_Color_Palette_Generator\Traits\Error_Handler;
use \GL_Color_Palette_Generator\Traits\Logger;

/**
 * Class Color_Metrics_Analyzer
 * Analyzes detailed color metrics and relationships
 */
class Color_Metrics_Analyzer implements \GL_Color_Palette_Generator\Interfaces\Color_Metrics_Analyzer {
    /**
     * Color Utility instance
     *
     * @var Color_Utility
     */
    private $color_utility;

    /**
     * Class properties for error handling and logging
     */
    private $error_log = [];
    private $debug_mode = false;
    private $last_error = null;

    /**
     * Caching mechanism for color calculations
     */
    private $calculation_cache = [];
    private $cache_ttl = 3600; // 1 hour default TTL

    /**
     * Constructor
     */
    public function __construct() {
        $this->color_utility = new Color_Utility();
    }

    /**
     * Analyze color metrics
     *
     * @param string $color Hex color code.
     * @return array Metrics analysis.
     */
    public function analyze_color($color) {
        return [
            'basic_metrics' => $this->get_basic_metrics($color),
            'perceptual_metrics' => $this->get_perceptual_metrics($color),
            'color_space_values' => $this->get_color_space_values($color),
            'psychological_metrics' => $this->get_psychological_metrics($color),
            'accessibility_metrics' => $this->get_accessibility_metrics($color),
            'complementary_colors' => $this->get_complementary_colors($color),
            'semantic_analysis' => $this->get_semantic_analysis($color)
        ];
    }

    /**
     * Get basic color metrics
     *
     * @param string $color Hex color code.
     * @return array Basic metrics.
     */
    private function get_basic_metrics($color) {
        $rgb = $this->handle_color_conversion($color, 'rgb');
        $hsl = $this->handle_color_conversion($color, 'hsl');

        return [
            'brightness' => $this->calculate_brightness($rgb),
            'relative_luminance' => $this->color_utility->calculate_relative_luminance($color),
            'saturation' => $hsl['s'],
            'lightness' => $hsl['l'],
            'intensity' => ($rgb['r'] + $rgb['g'] + $rgb['b']) / (3 * 255),
            'purity' => $this->calculate_color_purity($rgb)
        ];
    }

    /**
     * Get perceptual color metrics
     *
     * @param string $color Hex color code.
     * @return array Perceptual metrics.
     */
    private function get_perceptual_metrics($color) {
        $lab = $this->handle_color_conversion($color, 'lab');

        return [
            'perceived_brightness' => $lab['l'],
            'perceived_colorfulness' => sqrt(pow($lab['a'], 2) + pow($lab['b'], 2)),
            'perceived_hue' => atan2($lab['b'], $lab['a']) * (180 / M_PI),
            'perceived_warmth' => $this->calculate_perceived_warmth($color),
            'visual_weight' => $this->calculate_visual_weight($color)
        ];
    }

    /**
     * Get color space values
     *
     * @param string $color Hex color code.
     * @return array Color space values.
     */
    private function get_color_space_values($color) {
        return [
            'rgb' => $this->handle_color_conversion($color, 'rgb'),
            'hsl' => $this->handle_color_conversion($color, 'hsl'),
            'lab' => $this->handle_color_conversion($color, 'lab'),
            'cmyk' => $this->handle_color_conversion($color, 'cmyk'),
            'xyz' => $this->handle_color_conversion($color, 'xyz')
        ];
    }

    /**
     * Get psychological color metrics
     *
     * @param string $color Hex color code.
     * @return array Psychological metrics.
     */
    private function get_psychological_metrics($color) {
        $hsl = $this->handle_color_conversion($color, 'hsl');

        return [
            'temperature' => $this->calculate_psychological_temperature($hsl),
            'weight' => $this->calculate_psychological_weight($hsl),
            'activity' => $this->calculate_psychological_activity($hsl),
            'emotional_response' => $this->get_emotional_response($hsl),
            'cultural_associations' => $this->get_cultural_associations($hsl)
        ];
    }

    /**
     * Get accessibility metrics
     *
     * @param string $color Hex color code.
     * @return array Accessibility metrics.
     */
    private function get_accessibility_metrics($color) {
        return [
            'contrast_white' => $this->color_utility->calculate_contrast_ratio($color, '#FFFFFF'),
            'contrast_black' => $this->color_utility->calculate_contrast_ratio($color, '#000000'),
            'legibility_score' => $this->calculate_legibility_score($color),
            'wcag_compliance' => $this->check_wcag_compliance($color),
            'color_blindness_simulation' => $this->simulate_color_blindness($color)
        ];
    }

    /**
     * Get complementary colors
     *
     * @param string $color Hex color code.
     * @return array Complementary colors.
     */
    private function get_complementary_colors($color) {
        try {
            $this->validate_color($color);
            $hsl = $this->color_utility->hex_to_hsl($color);

            // Calculate complementary hue (opposite on color wheel)
            $complementary_hue = ($hsl['h'] + 180) % 360;

            return [
                'primary' => $color,
                'complementary' => $this->color_utility->hsl_to_hex([
                    'h' => $complementary_hue,
                    's' => $hsl['s'],
                    'l' => $hsl['l']
                ]),
                'variations' => $this->generate_complementary_variations($hsl)
            ];
        } catch (\Exception $e) {
            $this->log_error("Failed to generate complementary colors", $e);
            throw new \RuntimeException("Complementary color generation failed: " . $e->getMessage());
        }
    }

    /**
     * Generate variations of complementary colors
     *
     * @param array $hsl HSL values.
     * @return array Color variations.
     */
    private function generate_complementary_variations($hsl) {
        return $this->get_cached_calculation(
            "complementary_variations_{$hsl['h']}_{$hsl['s']}_{$hsl['l']}",
            function() use ($hsl) {
                $variations = [];
                $lightness_steps = [-20, -10, 10, 20];

                foreach ($lightness_steps as $step) {
                    $new_lightness = max(0, min(100, $hsl['l'] + $step));
                    $variations[] = $this->color_utility->hsl_to_hex([
                        'h' => $hsl['h'],
                        's' => $hsl['s'],
                        'l' => $new_lightness
                    ]);
                }

                return $variations;
            }
        );
    }

    /**
     * Get semantic color analysis
     *
     * @param string $color Hex color code.
     * @return array Semantic analysis.
     */
    private function get_semantic_analysis($color) {
        $hsl = $this->handle_color_conversion($color, 'hsl');

        return [
            'color_name' => $this->get_closest_color_name($color),
            'color_category' => $this->get_color_category($hsl),
            'semantic_associations' => $this->get_semantic_associations($hsl),
            'industry_usage' => $this->get_industry_usage($hsl),
            'brand_alignment' => $this->analyze_brand_alignment($hsl)
        ];
    }

    /**
     * Calculate color purity
     *
     * @param array $rgb RGB values.
     * @return float Purity value.
     */
    private function calculate_color_purity($rgb) {
        $max = max($rgb['r'], $rgb['g'], $rgb['b']);
        $min = min($rgb['r'], $rgb['g'], $rgb['b']);

        if ($max === 0) {
            return 0;
        }

        return ($max - $min) / $max;
    }

    /**
     * Calculate perceived warmth
     *
     * @param string $color Hex color code.
     * @return float Warmth value.
     */
    private function calculate_perceived_warmth($color) {
        $rgb = $this->handle_color_conversion($color, 'rgb');
        return ($rgb['r'] * 2 + $rgb['g'] + $rgb['b'] * 0.5) / (3.5 * 255);
    }

    /**
     * Calculate visual weight
     *
     * @param string $color Hex color code.
     * @return float Visual weight value.
     */
    private function calculate_visual_weight($color) {
        $lab = $this->handle_color_conversion($color, 'lab');
        return (100 - $lab['l']) / 100;
    }

    /**
     * Calculate psychological temperature
     *
     * @param array $hsl HSL values.
     * @return array Temperature metrics.
     */
    private function calculate_psychological_temperature($hsl) {
        $temp_factors = [
            'warmth' => $this->calculate_warmth_factor($hsl),
            'coolness' => $this->calculate_coolness_factor($hsl),
            'neutrality' => $this->calculate_neutrality_factor($hsl)
        ];

        $dominant = array_keys($temp_factors, max($temp_factors))[0];

        return [
            'factors' => $temp_factors,
            'dominant' => $dominant,
            'intensity' => max($temp_factors)
        ];
    }

    /**
     * Calculate warmth factor
     *
     * @param array $hsl HSL values.
     * @return float Warmth factor.
     */
    private function calculate_warmth_factor($hsl) {
        // Warm colors: red (0°) through yellow (60°)
        $hue = $hsl['h'];
        if ($hue <= 60 || $hue >= 300) {
            return 1 - (min(abs($hue), abs(360 - $hue)) / 60);
        }
        return 0;
    }

    /**
     * Calculate coolness factor
     *
     * @param array $hsl HSL values.
     * @return float Coolness factor.
     */
    private function calculate_coolness_factor($hsl) {
        // Cool colors: green (120°) through blue (240°)
        $hue = $hsl['h'];
        if ($hue >= 120 && $hue <= 240) {
            return 1 - (abs($hue - 180) / 60);
        }
        return 0;
    }

    /**
     * Calculate neutrality factor
     *
     * @param array $hsl HSL values.
     * @return float Neutrality factor.
     */
    private function calculate_neutrality_factor($hsl) {
        return 1 - ($hsl['s'] / 100);
    }

    /**
     * Calculate brightness
     *
     * @param array $rgb RGB values.
     * @return float Brightness value.
     */
    private function calculate_brightness($rgb) {
        return sqrt(
            $rgb['r'] * $rgb['r'] * 0.241 +
            $rgb['g'] * $rgb['g'] * 0.691 +
            $rgb['b'] * $rgb['b'] * 0.068
        ) / 255;
    }

    /**
     * Calculate legibility score
     *
     * @param string $color Hex color code.
     * @return float Legibility score.
     */
    private function calculate_legibility_score($color) {
        $contrast_white = $this->color_utility->calculate_contrast_ratio($color, '#FFFFFF');
        $contrast_black = $this->color_utility->calculate_contrast_ratio($color, '#000000');

        return max($contrast_white, $contrast_black) / 21 * 100;
    }

    /**
     * Check WCAG compliance
     *
     * @param string $color Hex color code.
     * @return array Compliance results.
     */
    private function check_wcag_compliance($color) {
        $contrast_white = $this->color_utility->calculate_contrast_ratio($color, '#FFFFFF');
        $contrast_black = $this->color_utility->calculate_contrast_ratio($color, '#000000');

        return [
            'AA_large_text' => [
                'on_white' => $contrast_white >= 3,
                'on_black' => $contrast_black >= 3
            ],
            'AA_small_text' => [
                'on_white' => $contrast_white >= 4.5,
                'on_black' => $contrast_black >= 4.5
            ],
            'AAA_large_text' => [
                'on_white' => $contrast_white >= 4.5,
                'on_black' => $contrast_black >= 4.5
            ],
            'AAA_small_text' => [
                'on_white' => $contrast_white >= 7,
                'on_black' => $contrast_black >= 7
            ]
        ];
    }

    /**
     * Simulate color blindness
     *
     * @param string $color Hex color code.
     * @return array Simulated colors.
     */
    private function simulate_color_blindness($color) {
        return [
            'protanopia' => $this->simulate_protanopia($color),
            'deuteranopia' => $this->simulate_deuteranopia($color),
            'tritanopia' => $this->simulate_tritanopia($color),
            'achromatopsia' => $this->simulate_achromatopsia($color)
        ];
    }

    /**
     * Simulate protanopia (red-blind)
     *
     * @param string $color Hex color code.
     * @return string Simulated color.
     */
    private function simulate_protanopia($color) {
        $rgb = $this->handle_color_conversion($color, 'rgb');

        // Protanopia simulation matrix
        $matrix = [
            [0.567, 0.433, 0],
            [0.558, 0.442, 0],
            [0, 0.242, 0.758]
        ];

        return $this->apply_color_matrix($rgb, $matrix);
    }

    /**
     * Simulate deuteranopia (green-blind)
     *
     * @param string $color Hex color code.
     * @return string Simulated color.
     */
    private function simulate_deuteranopia($color) {
        $rgb = $this->handle_color_conversion($color, 'rgb');

        // Deuteranopia simulation matrix
        $matrix = [
            [0.625, 0.375, 0],
            [0.7, 0.3, 0],
            [0, 0.3, 0.7]
        ];

        return $this->apply_color_matrix($rgb, $matrix);
    }

    /**
     * Simulate tritanopia (blue-blind)
     *
     * @param string $color Hex color code.
     * @return string Simulated color.
     */
    private function simulate_tritanopia($color) {
        $rgb = $this->handle_color_conversion($color, 'rgb');

        // Tritanopia simulation matrix
        $matrix = [
            [0.95, 0.05, 0],
            [0, 0.433, 0.567],
            [0, 0.475, 0.525]
        ];

        return $this->apply_color_matrix($rgb, $matrix);
    }

    /**
     * Simulate achromatopsia (complete color blindness)
     *
     * @param string $color Hex color code.
     * @return string Simulated color.
     */
    private function simulate_achromatopsia($color) {
        $rgb = $this->handle_color_conversion($color, 'rgb');
        $gray = ($rgb['r'] * 0.299 + $rgb['g'] * 0.587 + $rgb['b'] * 0.114);

        return sprintf('#%02x%02x%02x', $gray, $gray, $gray);
    }

    /**
     * Apply color matrix transformation
     *
     * @param array $rgb RGB values.
     * @param array $matrix Transformation matrix.
     * @return string Transformed color in hex.
     */
    private function apply_color_matrix($rgb, $matrix) {
        $result = [
            'r' => 0,
            'g' => 0,
            'b' => 0
        ];

        $rgb_array = [$rgb['r'], $rgb['g'], $rgb['b']];

        for ($i = 0; $i < 3; $i++) {
            for ($j = 0; $j < 3; $j++) {
                $result[array_keys($result)[$i]] += $matrix[$i][$j] * $rgb_array[$j];
            }
        }

        // Ensure values are within bounds
        foreach ($result as &$value) {
            $value = max(0, min(255, round($value)));
        }

        return sprintf('#%02x%02x%02x', $result['r'], $result['g'], $result['b']);
    }

    /**
     * Get complementary color
     *
     * @param array $hsl HSL values.
     * @return string Complementary color in hex.
     */
    private function get_complementary($hsl) {
        $hsl['h'] = ($hsl['h'] + 180) % 360;
        return $this->color_utility->hsl_to_hex($hsl);
    }

    /**
     * Get analogous colors
     *
     * @param array $hsl HSL values.
     * @return array Analogous colors in hex.
     */
    private function get_analogous($hsl) {
        return [
            $this->color_utility->hsl_to_hex(['h' => ($hsl['h'] - 30 + 360) % 360, 's' => $hsl['s'], 'l' => $hsl['l']]),
            $this->color_utility->hsl_to_hex(['h' => ($hsl['h'] + 30) % 360, 's' => $hsl['s'], 'l' => $hsl['l']])
        ];
    }

    /**
     * Get triadic colors
     *
     * @param array $hsl HSL values.
     * @return array Triadic colors in hex.
     */
    private function get_triadic($hsl) {
        return [
            $this->color_utility->hsl_to_hex(['h' => ($hsl['h'] + 120) % 360, 's' => $hsl['s'], 'l' => $hsl['l']]),
            $this->color_utility->hsl_to_hex(['h' => ($hsl['h'] + 240) % 360, 's' => $hsl['s'], 'l' => $hsl['l']])
        ];
    }

    /**
     * Get split complementary colors
     *
     * @param array $hsl HSL values.
     * @return array Split complementary colors in hex.
     */
    private function get_split_complementary($hsl) {
        $complement_hue = ($hsl['h'] + 180) % 360;
        return [
            $this->color_utility->hsl_to_hex(['h' => ($complement_hue - 30 + 360) % 360, 's' => $hsl['s'], 'l' => $hsl['l']]),
            $this->color_utility->hsl_to_hex(['h' => ($complement_hue + 30) % 360, 's' => $hsl['s'], 'l' => $hsl['l']])
        ];
    }

    /**
     * Get tetradic colors
     *
     * @param array $hsl HSL values.
     * @return array Tetradic colors in hex.
     */
    private function get_tetradic($hsl) {
        return [
            $this->color_utility->hsl_to_hex(['h' => ($hsl['h'] + 90) % 360, 's' => $hsl['s'], 'l' => $hsl['l']]),
            $this->color_utility->hsl_to_hex(['h' => ($hsl['h'] + 180) % 360, 's' => $hsl['s'], 'l' => $hsl['l']]),
            $this->color_utility->hsl_to_hex(['h' => ($hsl['h'] + 270) % 360, 's' => $hsl['s'], 'l' => $hsl['l']])
        ];
    }

    /**
     * Get closest color name
     *
     * @param string $color Hex color code.
     * @return string Color name.
     */
    private function get_closest_color_name($color) {
        $rgb = $this->handle_color_conversion($color, 'rgb');
        $min_distance = PHP_FLOAT_MAX;
        $closest_name = '';

        foreach ($this->get_color_name_map() as $name => $values) {
            $distance = sqrt(
                pow($rgb['r'] - $values['r'], 2) +
                pow($rgb['g'] - $values['g'], 2) +
                pow($rgb['b'] - $values['b'], 2)
            );

            if ($distance < $min_distance) {
                $min_distance = $distance;
                $closest_name = $name;
            }
        }

        return $closest_name;
    }

    /**
     * Get color category
     *
     * @param array $hsl HSL values.
     * @return string Color category.
     */
    private function get_color_category($hsl) {
        if ($hsl['s'] < 10) {
            return $hsl['l'] < 20 ? 'black' : ($hsl['l'] > 80 ? 'white' : 'gray');
        }

        $hue = $hsl['h'];
        if ($hue < 30) return 'red';
        if ($hue < 60) return 'orange';
        if ($hue < 90) return 'yellow';
        if ($hue < 150) return 'green';
        if ($hue < 210) return 'cyan';
        if ($hue < 270) return 'blue';
        if ($hue < 330) return 'purple';
        return 'red';
    }

    /**
     * Get semantic associations
     *
     * @param array $hsl HSL values.
     * @return array Semantic associations.
     */
    private function get_semantic_associations($hsl) {
        $category = $this->get_color_category($hsl);

        $associations = [
            'red' => ['energy', 'passion', 'danger', 'excitement'],
            'orange' => ['creativity', 'adventure', 'confidence', 'sociability'],
            'yellow' => ['happiness', 'optimism', 'intellect', 'attention'],
            'green' => ['nature', 'growth', 'harmony', 'freshness'],
            'cyan' => ['communication', 'clarity', 'calm', 'focus'],
            'blue' => ['trust', 'depth', 'stability', 'professionalism'],
            'purple' => ['royalty', 'luxury', 'mystery', 'spirituality'],
            'black' => ['power', 'elegance', 'mystery', 'sophistication'],
            'white' => ['purity', 'cleanliness', 'simplicity', 'peace'],
            'gray' => ['neutrality', 'balance', 'calmness', 'professionalism']
        ];

        return $associations[$category] ?? [];
    }

    /**
     * Get industry usage
     *
     * @param array $hsl HSL values.
     * @return array Industry associations.
     */
    private function get_industry_usage($hsl) {
        $category = $this->get_color_category($hsl);

        $industries = [
            'red' => ['food', 'entertainment', 'retail', 'technology'],
            'orange' => ['food', 'retail', 'technology', 'creative'],
            'yellow' => ['food', 'retail', 'construction', 'children'],
            'green' => ['environment', 'health', 'education', 'finance'],
            'cyan' => ['healthcare', 'technology', 'science', 'education'],
            'blue' => ['finance', 'technology', 'healthcare', 'corporate'],
            'purple' => ['luxury', 'beauty', 'creative', 'education'],
            'black' => ['luxury', 'fashion', 'technology', 'automotive'],
            'white' => ['healthcare', 'technology', 'retail', 'minimalist'],
            'gray' => ['corporate', 'technology', 'automotive', 'professional']
        ];

        return $industries[$category] ?? [];
    }

    /**
     * Analyze brand alignment
     *
     * @param array $hsl HSL values.
     * @return array Brand alignment analysis.
     */
    private function analyze_brand_alignment($hsl) {
        $category = $this->get_color_category($hsl);

        $brand_traits = [
            'personality' => $this->get_brand_personality($category),
            'target_audience' => $this->get_target_audience($category),
            'marketing_style' => $this->get_marketing_style($category),
            'industry_fit' => $this->get_industry_usage($hsl)
        ];

        return $brand_traits;
    }

    /**
     * Get brand personality
     *
     * @param string $category Color category.
     * @return array Brand personality traits.
     */
    private function get_brand_personality($category) {
        $personalities = [
            'red' => ['bold', 'energetic', 'passionate', 'dynamic'],
            'orange' => ['friendly', 'cheerful', 'confident', 'adventurous'],
            'yellow' => ['optimistic', 'creative', 'youthful', 'warm'],
            'green' => ['natural', 'balanced', 'peaceful', 'growth-oriented'],
            'cyan' => ['professional', 'clean', 'focused', 'efficient'],
            'blue' => ['trustworthy', 'professional', 'reliable', 'calm'],
            'purple' => ['luxurious', 'creative', 'wise', 'sophisticated'],
            'black' => ['powerful', 'sophisticated', 'luxurious', 'modern'],
            'white' => ['pure', 'clean', 'simple', 'modern'],
            'gray' => ['balanced', 'professional', 'mature', 'classic']
        ];

        return $personalities[$category] ?? [];
    }

    /**
     * Get target audience
     *
     * @param string $category Color category.
     * @return array Target audience characteristics.
     */
    private function get_target_audience($category) {
        $audiences = [
            'red' => ['young adults', 'action-oriented', 'bold', 'energetic'],
            'orange' => ['young people', 'creative types', 'social', 'adventurous'],
            'yellow' => ['youth', 'creative professionals', 'optimistic', 'intellectual'],
            'green' => ['health-conscious', 'nature lovers', 'balanced individuals', 'growth-minded'],
            'cyan' => ['professionals', 'tech-savvy', 'focused individuals', 'modern'],
            'blue' => ['professionals', 'conservative', 'trustworthy', 'reliable'],
            'purple' => ['luxury seekers', 'creative professionals', 'status-conscious', 'sophisticated'],
            'black' => ['luxury market', 'sophisticated', 'modern', 'powerful'],
            'white' => ['minimalists', 'modern', 'clean-conscious', 'sophisticated'],
            'gray' => ['professionals', 'mature audience', 'conservative', 'balanced']
        ];

        return $audiences[$category] ?? [];
    }

    /**
     * Get marketing style
     *
     * @param string $category Color category.
     * @return array Marketing style characteristics.
     */
    private function get_marketing_style($category) {
        $styles = [
            'red' => ['bold', 'direct', 'action-oriented', 'attention-grabbing'],
            'orange' => ['friendly', 'casual', 'energetic', 'playful'],
            'yellow' => ['optimistic', 'creative', 'cheerful', 'attention-getting'],
            'green' => ['natural', 'balanced', 'growth-focused', 'sustainable'],
            'cyan' => ['clean', 'professional', 'focused', 'modern'],
            'blue' => ['professional', 'trustworthy', 'corporate', 'reliable'],
            'purple' => ['luxury', 'creative', 'premium', 'sophisticated'],
            'black' => ['premium', 'sophisticated', 'luxury', 'modern'],
            'white' => ['clean', 'simple', 'modern', 'pure'],
            'gray' => ['professional', 'balanced', 'classic', 'mature']
        ];

        return $styles[$category] ?? [];
    }

    /**
     * Get color name map
     *
     * @return array Color name map.
     */
    private function get_color_name_map() {
        return [
            'red' => ['r' => 255, 'g' => 0, 'b' => 0],
            'green' => ['r' => 0, 'g' => 255, 'b' => 0],
            'blue' => ['r' => 0, 'g' => 0, 'b' => 255],
            // ... Add more color definitions as needed
        ];
    }

    /**
     * Analyze cultural significance
     */
    public function analyze_cultural_significance($color, $regions = []) {
        return [
            'cultural_meanings' => $this->get_cultural_meanings($color, $regions),
            'regional_preferences' => $this->analyze_regional_preferences($color),
            'religious_significance' => $this->get_religious_significance($color),
            'historical_context' => $this->get_historical_context($color)
        ];
    }

    /**
     * Analyze UI/UX implications
     */
    public function analyze_ui_ux_impact($color) {
        return [
            'readability_metrics' => $this->analyze_readability($color),
            'interface_recommendations' => $this->get_interface_recommendations($color),
            'mobile_considerations' => $this->analyze_mobile_display($color),
            'dark_mode_adaptation' => $this->suggest_dark_mode_variants($color)
        ];
    }

    /**
     * Analyze media compatibility
     */
    public function analyze_media_compatibility($color) {
        return [
            'print_considerations' => $this->analyze_print_output($color),
            'screen_display_variations' => $this->analyze_screen_display($color),
            'color_space_conversions' => $this->analyze_color_space_compatibility($color),
            'device_gamut_coverage' => $this->analyze_device_gamut($color)
        ];
    }

    /**
     * Enhanced accessibility analysis
     */
    public function analyze_enhanced_accessibility($color) {
        return [
            'color_blindness_alternatives' => $this->suggest_colorblind_alternatives($color),
            'high_contrast_variants' => $this->generate_high_contrast_variants($color),
            'low_vision_adaptations' => $this->suggest_low_vision_adaptations($color),
            'voice_description' => $this->generate_voice_description($color)
        ];
    }

    /**
     * Enhanced brand analysis
     */
    public function analyze_brand_extended($color) {
        return [
            'social_media_performance' => $this->analyze_social_media_impact($color),
            'marketing_channel_suitability' => $this->analyze_channel_suitability($color),
            'demographic_appeal' => $this->analyze_demographic_appeal($color),
            'brand_personality_metrics' => $this->analyze_brand_personality_fit($color)
        ];
    }

    /**
     * Analyze environmental impact
     */
    public function analyze_environmental_context($color) {
        return [
            'natural_light_appearance' => $this->analyze_natural_light_behavior($color),
            'artificial_light_appearance' => $this->analyze_artificial_light_behavior($color),
            'seasonal_lighting_impact' => $this->analyze_seasonal_lighting($color),
            'environmental_associations' => $this->get_environmental_associations($color)
        ];
    }

    /**
     * Analyze animation suitability
     */
    public function analyze_animation_suitability($color) {
        return [
            'transition_recommendations' => $this->suggest_color_transitions($color),
            'animation_compatibility' => $this->analyze_animation_compatibility($color),
            'motion_effects' => $this->suggest_motion_effects($color),
            'interaction_states' => $this->generate_interaction_states($color)
        ];
    }

    /**
     * Get cultural meanings for specific regions
     *
     * @param string $color Hex color code.
     * @param array  $regions Array of region codes.
     * @return array Cultural meanings by region.
     */
    private function get_cultural_meanings($color, $regions) {
        $category = $this->get_color_category($this->handle_color_conversion($color, 'hsl'));
        $meanings = [];

        foreach ($regions as $region) {
            $meanings[$region] = $this->get_regional_color_meanings($category, $region);
        }

        return $meanings;
    }

    /**
     * Analyze regional preferences
     *
     * @param string $color Hex color code.
     * @return array Regional preference analysis.
     */
    private function analyze_regional_preferences($color) {
        $hsl = $this->handle_color_conversion($color, 'hsl');

        return [
            'popularity_metrics' => $this->get_regional_popularity_metrics($hsl),
            'cultural_context' => $this->get_cultural_context($hsl),
            'regional_variations' => $this->get_regional_variations($hsl),
            'market_data' => $this->get_market_data($hsl)
        ];
    }

    /**
     * Get religious significance
     *
     * @param string $color Hex color code.
     * @return array Religious significance data.
     */
    private function get_religious_significance($color) {
        $hsl = $this->handle_color_conversion($color, 'hsl');
        $category = $this->get_color_category($hsl);

        return [
            'religious_contexts' => $this->get_religious_contexts($category),
            'ceremonial_uses' => $this->get_ceremonial_uses($category),
            'symbolic_meanings' => $this->get_religious_symbols($category),
            'traditional_applications' => $this->get_traditional_applications($category)
        ];
    }

    /**
     * Get historical context
     *
     * @param string $color Hex color code.
     * @return array Historical context data.
     */
    private function get_historical_context($color) {
        $hsl = $this->handle_color_conversion($color, 'hsl');
        $category = $this->get_color_category($hsl);

        return [
            'historical_usage' => $this->get_historical_usage_data($category),
            'cultural_evolution' => $this->get_cultural_evolution($category),
            'artistic_significance' => $this->get_artistic_significance($category),
            'historical_events' => $this->get_historical_events($category)
        ];
    }

    /**
     * Get regional color meanings
     *
     * @param string $category Color category.
     * @param string $region Region code.
     * @return array Regional meanings.
     */
    private function get_regional_color_meanings($category, $region) {
        $regional_meanings = [
            'east_asia' => [
                'red' => ['luck', 'prosperity', 'happiness', 'celebration'],
                'white' => ['death', 'mourning', 'purity', 'cleanliness'],
                'gold' => ['wealth', 'royalty', 'power', 'strength'],
                // Add more colors and meanings
            ],
            'western' => [
                'white' => ['purity', 'peace', 'marriage', 'cleanliness'],
                'black' => ['mourning', 'sophistication', 'power', 'elegance'],
                'red' => ['passion', 'danger', 'excitement', 'importance'],
                // Add more colors and meanings
            ],
            // Add more regions
        ];

        return $regional_meanings[$region][$category] ?? [];
    }

    /**
     * Analyze readability metrics
     *
     * @param string $color Hex color code.
     * @return array Readability analysis.
     */
    private function analyze_readability($color) {
        $text_sizes = [12, 14, 16, 18, 24, 32];
        $backgrounds = ['#FFFFFF', '#000000', '#F0F0F0', '#202020'];
        $results = [];

        foreach ($text_sizes as $size) {
            $results[$size] = [];
            foreach ($backgrounds as $bg) {
                $contrast = $this->color_utility->calculate_contrast_ratio($color, $bg);
                $results[$size][$bg] = [
                    'contrast_ratio' => $contrast,
                    'meets_wcag_aa' => $this->meets_wcag_aa($contrast, $size),
                    'meets_wcag_aaa' => $this->meets_wcag_aaa($contrast, $size),
                    'recommended_weight' => $this->get_recommended_font_weight($contrast, $size)
                ];
            }
        }

        return $results;
    }

    /**
     * Get interface recommendations
     *
     * @param string $color Hex color code.
     * @return array Interface recommendations.
     */
    private function get_interface_recommendations($color) {
        $lab = $this->handle_color_conversion($color, 'lab');

        return [
            'primary_actions' => [
                'suitable_for_buttons' => $this->is_suitable_for_buttons($lab),
                'recommended_states' => $this->get_recommended_states($color),
                'hover_colors' => $this->generate_hover_colors($color),
                'active_colors' => $this->generate_active_colors($color)
            ],
            'secondary_elements' => [
                'border_colors' => $this->generate_border_colors($color),
                'background_usage' => $this->get_background_recommendations($lab),
                'icon_usage' => $this->get_icon_recommendations($lab)
            ],
            'text_usage' => [
                'minimum_size' => $this->get_minimum_text_size($color),
                'optimal_weights' => $this->get_optimal_font_weights($color),
                'background_pairings' => $this->get_background_pairings($color)
            ]
        ];
    }

    /**
     * Analyze mobile display considerations
     *
     * @param string $color Hex color code.
     * @return array Mobile display analysis.
     */
    private function analyze_mobile_display($color) {
        return [
            'touch_targets' => [
                'minimum_size' => $this->get_minimum_touch_size($color),
                'contrast_requirements' => $this->get_touch_contrast_requirements($color),
                'spacing_recommendations' => $this->get_touch_spacing_recommendations($color)
            ],
            'visibility' => [
                'outdoor_readability' => $this->analyze_outdoor_visibility($color),
                'night_mode_adaptation' => $this->get_night_mode_adaptation($color),
                'glare_resistance' => $this->analyze_glare_resistance($color)
            ],
            'performance' => [
                'battery_impact' => $this->calculate_battery_impact($color),
                'rendering_performance' => $this->analyze_rendering_performance($color)
            ]
        ];
    }

    /**
     * Analyze print output considerations
     *
     * @param string $color Hex color code.
     * @return array Print analysis.
     */
    private function analyze_print_output($color) {
        $cmyk = $this->handle_color_conversion($color, 'cmyk');

        return [
            'color_profile' => [
                'cmyk_values' => $cmyk,
                'ink_coverage' => $this->calculate_ink_coverage($cmyk),
                'dot_gain' => $this->estimate_dot_gain($cmyk),
                'color_accuracy' => $this->analyze_print_accuracy($color)
            ],
            'paper_types' => [
                'coated' => $this->analyze_coated_paper_performance($cmyk),
                'uncoated' => $this->analyze_uncoated_paper_performance($cmyk),
                'specialty' => $this->analyze_specialty_paper_performance($cmyk)
            ],
            'printer_compatibility' => [
                'offset' => $this->analyze_offset_printing($cmyk),
                'digital' => $this->analyze_digital_printing($cmyk),
                'large_format' => $this->analyze_large_format_printing($cmyk)
            ]
        ];
    }

    /**
     * Analyze screen display variations
     *
     * @param string $color Hex color code.
     * @return array Screen display analysis.
     */
    private function analyze_screen_display($color) {
        return [
            'color_spaces' => [
                'srgb' => $this->analyze_srgb_compatibility($color),
                'adobe_rgb' => $this->analyze_adobe_rgb_compatibility($color),
                'p3' => $this->analyze_p3_compatibility($color)
            ],
            'display_types' => [
                'lcd' => $this->analyze_lcd_display($color),
                'oled' => $this->analyze_oled_display($color),
                'e_ink' => $this->analyze_e_ink_compatibility($color)
            ],
            'viewing_conditions' => [
                'angle_dependency' => $this->analyze_viewing_angles($color),
                'ambient_light' => $this->analyze_ambient_light_impact($color),
                'screen_reflectance' => $this->analyze_screen_reflectance($color)
            ]
        ];
    }

    /**
     * Calculate ink coverage
     *
     * @param array $cmyk CMYK values.
     * @return float Ink coverage percentage.
     */
    private function calculate_ink_coverage($cmyk) {
        return min(400, $cmyk['c'] + $cmyk['m'] + $cmyk['y'] + $cmyk['k']);
    }

    /**
     * Estimate dot gain
     *
     * @param array $cmyk CMYK values.
     * @return array Dot gain estimates.
     */
    private function estimate_dot_gain($cmyk) {
        $dot_gain = [];
        foreach ($cmyk as $channel => $value) {
            $dot_gain[$channel] = $this->calculate_channel_dot_gain($value);
        }
        return $dot_gain;
    }

    /**
     * Calculate channel dot gain
     *
     * @param float $value Channel value.
     * @return float Dot gain percentage.
     */
    private function calculate_channel_dot_gain($value) {
        // Standard dot gain curve approximation
        if ($value < 25) {
            return $value * 0.16; // 16% gain in highlights
        } elseif ($value < 75) {
            return $value * 0.14; // 14% gain in midtones
        } else {
            return $value * 0.12; // 12% gain in shadows
        }
    }

    /**
     * Analyze print accuracy
     *
     * @param string $color Hex color code.
     * @return array Print accuracy analysis.
     */
    private function analyze_print_accuracy($color) {
        $lab = $this->handle_color_conversion($color, 'lab');

        return [
            'gamut_coverage' => $this->calculate_gamut_coverage($lab),
            'metameric_index' => $this->calculate_metameric_index($lab),
            'delta_e' => $this->calculate_delta_e($lab)
        ];
    }

    /**
     * Check if color is suitable for buttons
     *
     * @param array $lab LAB color values.
     * @return array Button suitability analysis.
     */
    private function is_suitable_for_buttons($lab) {
        return [
            'suitable' => $lab['l'] > 45 && $lab['l'] < 85,
            'visibility_score' => $this->calculate_visibility_score($lab),
            'attention_value' => $this->calculate_attention_value($lab),
            'recommendations' => [
                'primary' => $lab['l'] > 60,
                'secondary' => $lab['l'] < 60,
                'size_adjustments' => $this->get_size_recommendations($lab)
            ]
        ];
    }

    /**
     * Generate hover state colors
     *
     * @param string $color Hex color code.
     * @return array Hover state colors.
     */
    private function generate_hover_colors($color) {
        $hsl = $this->handle_color_conversion($color, 'hsl');

        return [
            'lighten' => $this->color_utility->hsl_to_hex([
                'h' => $hsl['h'],
                's' => $hsl['s'],
                'l' => min(100, $hsl['l'] + 10)
            ]),
            'darken' => $this->color_utility->hsl_to_hex([
                'h' => $hsl['h'],
                's' => $hsl['s'],
                'l' => max(0, $hsl['l'] - 10)
            ]),
            'saturate' => $this->color_utility->hsl_to_hex([
                'h' => $hsl['h'],
                's' => min(100, $hsl['s'] + 10),
                'l' => $hsl['l']
            ])
        ];
    }

    /**
     * Calculate visibility score
     *
     * @param array $lab LAB color values.
     * @return float Visibility score.
     */
    private function calculate_visibility_score($lab) {
        $luminance_weight = 0.5;
        $chroma_weight = 0.3;
        $optimal_luminance = 65;

        $luminance_score = 100 - abs($lab['l'] - $optimal_luminance);
        $chroma_score = sqrt(pow($lab['a'], 2) + pow($lab['b'], 2)) / 1.28;

        return ($luminance_score * $luminance_weight + $chroma_score * $chroma_weight) /
               ($luminance_weight + $chroma_weight);
    }

    /**
     * Analyze color space compatibility
     *
     * @param string $color Hex color code.
     * @return array Color space compatibility analysis.
     */
    private function analyze_color_space_compatibility($color) {
        $rgb = $this->handle_color_conversion($color, 'rgb');
        $xyz = $this->color_utility->rgb_to_xyz($rgb);

        return [
            'srgb_coverage' => $this->analyze_srgb_coverage($xyz),
            'adobe_rgb_coverage' => $this->analyze_adobe_rgb_coverage($xyz),
            'p3_coverage' => $this->analyze_p3_coverage($xyz),
            'recommendations' => $this->get_color_space_recommendations($xyz)
        ];
    }

    /**
     * Analyze sRGB coverage
     *
     * @param array $xyz XYZ color values.
     * @return array sRGB coverage analysis.
     */
    private function analyze_srgb_coverage($xyz) {
        return [
            'in_gamut' => $this->is_in_srgb_gamut($xyz),
            'coverage_percentage' => $this->calculate_srgb_coverage_percentage($xyz),
            'out_of_gamut_channels' => $this->get_out_of_gamut_channels($xyz),
            'gamut_mapping_suggestion' => $this->suggest_gamut_mapping($xyz)
        ];
    }

    /**
     * Calculate metameric index
     *
     * @param array $lab LAB color values.
     * @return array Metameric index analysis.
     */
    private function calculate_metameric_index($lab) {
        return [
            'daylight' => $this->calculate_daylight_metamerism($lab),
            'incandescent' => $this->calculate_incandescent_metamerism($lab),
            'fluorescent' => $this->calculate_fluorescent_metamerism($lab),
            'overall_index' => $this->calculate_overall_metamerism($lab)
        ];
    }

    /**
     * Analyze device-specific display characteristics
     *
     * @param string $color Hex color code.
     * @return array Device-specific analysis.
     */
    private function analyze_device_display($color) {
        $lab = $this->handle_color_conversion($color, 'lab');

        return [
            'mobile' => [
                'amoled_optimization' => $this->optimize_for_amoled($lab),
                'power_efficiency' => $this->calculate_power_efficiency($lab),
                'daylight_visibility' => $this->analyze_daylight_visibility($lab)
            ],
            'tablet' => [
                'viewing_angle_impact' => $this->calculate_viewing_angle_impact($lab),
                'reflection_handling' => $this->analyze_reflection_handling($lab),
                'color_shift' => $this->calculate_color_shift($lab)
            ],
            'desktop' => [
                'monitor_calibration' => $this->get_monitor_calibration_data($lab),
                'gamma_response' => $this->analyze_gamma_response($lab),
                'color_banding' => $this->analyze_color_banding($lab)
            ]
        ];
    }

    /**
     * Analyze print production variables
     *
     * @param string $color Hex color code.
     * @return array Print production analysis.
     */
    private function analyze_print_production($color) {
        $cmyk = $this->handle_color_conversion($color, 'cmyk');

        return [
            'press_conditions' => [
                'humidity_sensitivity' => $this->analyze_humidity_sensitivity($cmyk),
                'temperature_impact' => $this->analyze_temperature_impact($cmyk),
                'drying_time' => $this->estimate_drying_time($cmyk)
            ],
            'substrate_compatibility' => [
                'coated_papers' => $this->analyze_coated_compatibility($cmyk),
                'uncoated_papers' => $this->analyze_uncoated_compatibility($cmyk),
                'specialty_materials' => $this->analyze_specialty_materials($cmyk)
            ],
            'production_considerations' => [
                'press_speed' => $this->recommend_press_speed($cmyk),
                'registration_tolerance' => $this->calculate_registration_tolerance($cmyk),
                'trapping_requirements' => $this->calculate_trapping_requirements($cmyk)
            ]
        ];
    }

    /**
     * Validate color input
     *
     * @param string $color Hex color code.
     * @throws \InvalidArgumentException If color format is invalid.
     * @return bool True if valid.
     */
    private function validate_color($color) {
        if (!is_string($color)) {
            throw new \InvalidArgumentException('Color must be a string');
        }

        if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color)) {
            throw new \InvalidArgumentException(
                'Invalid hex color format. Expected #RGB or #RRGGBB'
            );
        }

        return true;
    }

    /**
     * Analyze color accessibility for different vision types
     *
     * @param string $color Hex color code.
     * @return array Accessibility analysis for different vision types.
     * @throws \RuntimeException If color conversion fails.
     */
    private function analyze_color_vision_accessibility($color) {
        try {
            $this->validate_color($color);
            $rgb = $this->handle_color_conversion($color, 'rgb');

            return [
                'protanopia' => $this->simulate_protanopia($rgb),
                'deuteranopia' => $this->simulate_deuteranopia($rgb),
                'tritanopia' => $this->simulate_tritanopia($rgb),
                'achromatopsia' => $this->simulate_achromatopsia($rgb),
                'recommendations' => [
                    'alternative_colors' => $this->get_accessible_alternatives($rgb),
                    'pattern_suggestions' => $this->suggest_patterns($rgb),
                    'text_guidelines' => $this->get_text_guidelines($rgb)
                ]
            ];
        } catch (\Exception $e) {
            throw new \RuntimeException(
                'Failed to analyze color vision accessibility: ' . $e->getMessage()
            );
        }
    }

    /**
     * Analyze animation and transition suitability
     *
     * @param string $color Hex color code.
     * @return array Animation suitability analysis.
     * @throws \RuntimeException If analysis fails.
     */
    private function analyze_animation_suitability($color) {
        try {
            $this->validate_color($color);
            $lab = $this->handle_color_conversion($color, 'lab');

            return [
                'transition_properties' => [
                    'recommended_duration' => $this->calculate_transition_duration($lab),
                    'easing_functions' => $this->suggest_easing_functions($lab),
                    'performance_impact' => $this->assess_performance_impact($lab)
                ],
                'animation_guidelines' => [
                    'optimal_keyframes' => $this->generate_keyframes($color),
                    'motion_paths' => $this->suggest_motion_paths($lab),
                    'timing_recommendations' => $this->get_timing_recommendations($lab)
                ],
                'accessibility_considerations' => [
                    'reduced_motion' => $this->get_reduced_motion_alternatives($lab),
                    'flash_assessment' => $this->assess_flash_risk($lab),
                    'motion_sensitivity' => $this->analyze_motion_sensitivity($lab)
                ]
            ];
        } catch (\Exception $e) {
            throw new \RuntimeException(
                'Failed to analyze animation suitability: ' . $e->getMessage()
            );
        }
    }

    /**
     * Generate keyframes for color transitions
     *
     * @param string $color Hex color code.
     * @return array Keyframe colors and timing.
     * @throws \RuntimeException If keyframe generation fails.
     */
    private function generate_keyframes($color) {
        try {
            $this->validate_color($color);
            $hsl = $this->handle_color_conversion($color, 'hsl');
            $keyframes = [];

            // Generate 5 keyframe points
            for ($i = 0; $i <= 4; $i++) {
                $percentage = $i * 25;
                $keyframes[$percentage] = [
                    'color' => $this->calculate_keyframe_color($hsl, $percentage),
                    'timing' => $this->calculate_keyframe_timing($percentage),
                    'easing' => $this->get_keyframe_easing($percentage)
                ];
            }

            return $keyframes;
        } catch (\Exception $e) {
            throw new \RuntimeException(
                'Failed to generate keyframes: ' . $e->getMessage()
            );
        }
    }

    /**
     * Analyze environmental lighting adaptation
     *
     * @param string $color Hex color code.
     * @return array Environmental adaptation analysis.
     * @throws \RuntimeException If analysis fails.
     */
    private function analyze_environmental_adaptation($color) {
        try {
            $this->validate_color($color);
            $lab = $this->handle_color_conversion($color, 'lab');

            return [
                'lighting_conditions' => [
                    'bright_daylight' => $this->analyze_daylight_appearance($lab),
                    'office_lighting' => $this->analyze_office_appearance($lab),
                    'dim_lighting' => $this->analyze_dim_appearance($lab),
                    'dark_environment' => $this->analyze_dark_appearance($lab)
                ],
                'display_adjustments' => [
                    'brightness_compensation' => $this->calculate_brightness_compensation($lab),
                    'contrast_adaptation' => $this->calculate_contrast_adaptation($lab),
                    'color_temperature' => $this->suggest_color_temperature($lab)
                ],
                'recommendations' => [
                    'optimal_viewing_conditions' => $this->get_optimal_conditions($lab),
                    'alternative_colors' => $this->get_environmental_alternatives($lab),
                    'usage_guidelines' => $this->get_environmental_guidelines($lab)
                ]
            ];
        } catch (\Exception $e) {
            throw new \RuntimeException(
                'Failed to analyze environmental adaptation: ' . $e->getMessage()
            );
        }
    }

    /**
     * Calculate performance metrics for color rendering
     *
     * @param string $color Hex color code.
     * @return array Performance metrics.
     * @throws \RuntimeException If calculation fails.
     */
    private function calculate_performance_metrics($color) {
        try {
            $this->validate_color($color);
            $rgb = $this->handle_color_conversion($color, 'rgb');

            return [
                'rendering_cost' => [
                    'cpu_impact' => $this->calculate_cpu_impact($rgb),
                    'gpu_utilization' => $this->calculate_gpu_utilization($rgb),
                    'memory_usage' => $this->estimate_memory_usage($rgb)
                ],
                'optimization_suggestions' => [
                    'caching_strategy' => $this->suggest_caching_strategy($rgb),
                    'rendering_technique' => $this->suggest_rendering_technique($rgb),
                    'performance_tweaks' => $this->get_performance_tweaks($rgb)
                ],
                'device_specific' => [
                    'mobile_optimizations' => $this->get_mobile_optimizations($rgb),
                    'desktop_optimizations' => $this->get_desktop_optimizations($rgb),
                    'high_dpi_considerations' => $this->analyze_high_dpi_impact($rgb)
                ]
            ];
        } catch (\Exception $e) {
            throw new \RuntimeException(
                'Failed to calculate performance metrics: ' . $e->getMessage()
            );
        }
    }

    /**
     * Handle color conversion errors
     *
     * @param string $color Hex color code.
     * @param string $target_space Target color space.
     * @return array|false Conversion result or false on failure.
     */
    private function handle_color_conversion($color, $target_space) {
        try {
            $this->log_debug("Attempting color conversion to $target_space: $color");

            if (!$this->validate_color($color)) {
                throw new \InvalidArgumentException("Invalid color format: $color");
            }

            switch ($target_space) {
                case 'rgb':
                    $result = $this->color_utility->hex_to_rgb($color);
                    break;
                case 'lab':
                    $result = $this->color_utility->hex_to_lab($color);
                    break;
                case 'cmyk':
                    $result = $this->color_utility->hex_to_cmyk($color);
                    break;
                default:
                    throw new \InvalidArgumentException("Unsupported color space: $target_space");
            }

            $this->log_debug("Conversion successful: " . json_encode($result));
            return $result;

        } catch (\Exception $e) {
            $this->handle_error($e, "Color conversion failed");
            return false;
        }
    }

    /**
     * Handle calculation errors
     *
     * @param callable $calculation Function to execute.
     * @param array    $params Parameters for calculation.
     * @param mixed    $default Default value on failure.
     * @return mixed Calculation result or default value.
     */
    private function handle_calculation($calculation, $params, $default = null) {
        try {
            $this->log_debug("Starting calculation with params: " . json_encode($params));

            if (!is_callable($calculation)) {
                throw new \InvalidArgumentException("Invalid calculation function provided");
            }

            $result = call_user_func_array($calculation, $params);

            if ($result === false || $result === null) {
                throw new \RuntimeException("Calculation returned invalid result");
            }

            $this->log_debug("Calculation successful: " . json_encode($result));
            return $result;

        } catch (\Exception $e) {
            $this->handle_error($e, "Calculation failed");
            return $default;
        }
    }

    /**
     * Logging and debugging methods
     */

    /**
     * Log debug message
     *
     * @param string $message Debug message.
     * @param array  $context Additional context.
     * @return void
     */
    private function log_debug($message, $context = []) {
        if ($this->debug_mode) {
            $this->logger->debug($message, $context);

            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("[Color Metrics Debug] $message");
            }
        }
    }

    /**
     * Log error message
     *
     * @param string     $message Error message.
     * @param \Exception $e Exception object.
     * @return void
     */
    private function log_error($message, \Exception $e = null) {
        $context = [
            'timestamp' => current_time('mysql'),
            'error_message' => $e ? $e->getMessage() : $message,
            'trace' => $e ? $e->getTraceAsString() : debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
        ];

        $this->logger->error($message, $context);
        $this->error_log[] = $context;
        $this->last_error = $context;

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("[Color Metrics Error] $message");
        }
    }

    /**
     * Get error log
     *
     * @return array Error log.
     */
    public function get_error_log() {
        return $this->error_log;
    }

    /**
     * Get last error
     *
     * @return array|null Last error context.
     */
    public function get_last_error() {
        return $this->last_error;
    }

    /**
     * Clear error log
     *
     * @return void
     */
    public function clear_error_log() {
        $this->error_log = [];
        $this->last_error = null;
    }

    /**
     * Set debug mode
     *
     * @param bool $mode Debug mode state.
     * @return void
     */
    public function set_debug_mode($mode) {
        $this->debug_mode = (bool) $mode;
        $this->log_debug("Debug mode " . ($mode ? "enabled" : "disabled"));
    }

    /**
     * Performance monitoring methods
     */

    /**
     * Start performance monitoring
     *
     * @param string $operation Operation name.
     * @return float Start time.
     */
    private function start_monitoring($operation) {
        $start_time = microtime(true);
        $this->log_debug("Starting operation: $operation");
        return $start_time;
    }

    /**
     * End performance monitoring
     *
     * @param string $operation Operation name.
     * @param float  $start_time Start time.
     * @return void
     */
    private function end_monitoring($operation, $start_time) {
        $end_time = microtime(true);
        $duration = round(($end_time - $start_time) * 1000, 2);

        $this->log_debug("Completed operation: $operation", [
            'duration_ms' => $duration,
            'memory_usage' => memory_get_usage(true)
        ]);
    }

    /**
     * Monitor method execution
     *
     * @param string   $operation Operation name.
     * @param callable $callback Function to monitor.
     * @param array    $params Parameters for callback.
     * @return mixed Result of callback.
     */
    private function monitor_execution($operation, $callback, $params = []) {
        $start_time = $this->start_monitoring($operation);

        try {
            $result = call_user_func_array($callback, $params);
            $this->end_monitoring($operation, $start_time);
            return $result;
        } catch (\Exception $e) {
            $this->log_error("Operation failed: $operation", $e);
            $this->end_monitoring($operation, $start_time);
            throw $e;
        }
    }

    /**
     * Get cached calculation or compute and cache
     *
     * @param string   $key Cache key.
     * @param callable $callback Calculation callback.
     * @param array    $params Calculation parameters.
     * @return mixed Calculation result.
     */
    private function get_cached_calculation($key, $callback, $params = []) {
        $cache_key = $this->generate_cache_key($key, $params);

        if ($this->has_valid_cache($cache_key)) {
            $this->log_debug("Cache hit for: $cache_key");
            return $this->calculation_cache[$cache_key]['value'];
        }

        $result = call_user_func_array($callback, $params);
        $this->cache_calculation($cache_key, $result);
        return $result;
    }

    /**
     * Batch processing for multiple colors
     *
     * @param array $colors Array of hex colors.
     * @param string $analysis_type Type of analysis to perform.
     * @return array Analysis results.
     */
    public function batch_analyze_colors($colors, $analysis_type = 'full') {
        $results = [];
        $errors = [];

        foreach ($colors as $color) {
            try {
                $results[$color] = match($analysis_type) {
                    'basic' => $this->get_basic_metrics($color),
                    'accessibility' => $this->get_accessibility_metrics($color),
                    'print' => $this->analyze_print_output($color),
                    'screen' => $this->analyze_screen_display($color),
                    'full' => $this->analyze_color($color),
                    default => throw new \InvalidArgumentException("Invalid analysis type: $analysis_type")
                };
            } catch (\Exception $e) {
                $errors[$color] = $e->getMessage();
                $this->log_error("Batch analysis failed for color: $color", $e);
            }
        }

        return [
            'results' => $results,
            'errors' => $errors
        ];
    }

    /**
     * Color palette optimization
     */
    public function optimize_palette($colors, $criteria = []) {
        $optimized = [];
        $criteria = array_merge([
            'contrast_ratio' => 4.5,
            'harmony_threshold' => 0.7,
            'accessibility_score' => 90,
            'print_compatibility' => true
        ], $criteria);

        foreach ($colors as $color) {
            $optimized[$color] = [
                'original' => $color,
                'optimized' => $this->optimize_color($color, $criteria),
                'metrics' => $this->get_optimization_metrics($color, $criteria)
            ];
        }

        return $optimized;
    }

    /**
     * Data export/import functionality
     */
    public function export_color_data($colors, $format = 'json') {
        $data = [
            'metadata' => [
                'version' => '1.0',
                'generated' => current_time('mysql'),
                'count' => count($colors)
            ],
            'colors' => array_map([$this, 'analyze_color'], $colors)
        ];

        return match($format) {
            'json' => json_encode($data, JSON_PRETTY_PRINT),
            'csv' => $this->convert_to_csv($data),
            'xml' => $this->convert_to_xml($data),
            default => throw new \InvalidArgumentException("Unsupported format: $format")
        };
    }

    public function import_color_data($data, $format = 'json') {
        try {
            $imported = match($format) {
                'json' => json_decode($data, true),
                'csv' => $this->parse_csv($data),
                'xml' => $this->parse_xml($data),
                default => throw new \InvalidArgumentException("Unsupported format: $format")
            };

            return $this->validate_imported_data($imported);
        } catch (\Exception $e) {
            $this->log_error("Import failed", $e);
            return false;
        }
    }

    /**
     * Internationalization support
     */
    private function get_localized_color_name($color) {
        $names = [
            'en' => $this->get_english_color_name($color),
            'es' => $this->get_spanish_color_name($color),
            'fr' => $this->get_french_color_name($color),
            'de' => $this->get_german_color_name($color),
            'ja' => $this->get_japanese_color_name($color)
        ];

        $locale = get_locale();
        return $names[$locale] ?? $names['en'];
    }

    /**
     * Unit conversion utilities
     */
    public function convert_color_units($color, $from_unit, $to_unit) {
        return $this->get_cached_calculation(
            "unit_conversion_{$from_unit}_{$to_unit}",
            function() use ($color, $from_unit, $to_unit) {
                return match([$from_unit, $to_unit]) {
                    ['hex', 'rgb'] => $this->color_utility->hex_to_rgb($color),
                    ['rgb', 'cmyk'] => $this->color_utility->rgb_to_cmyk($color),
                    ['rgb', 'hsl'] => $this->color_utility->rgb_to_hsl($color),
                    ['rgb', 'lab'] => $this->color_utility->rgb_to_lab($color),
                    default => throw new \InvalidArgumentException("Unsupported conversion: $from_unit to $to_unit")
                };
            }
        );
    }

    /**
     * Color accessibility scoring
     */
    public function calculate_accessibility_score($color) {
        $metrics = [
            'contrast_ratio' => $this->calculate_contrast_ratios($color),
            'color_blindness' => $this->analyze_color_vision_accessibility($color),
            'readability' => $this->analyze_readability($color)
        ];

        return [
            'overall_score' => $this->compute_overall_accessibility_score($metrics),
            'detailed_scores' => $metrics,
            'recommendations' => $this->get_accessibility_recommendations($metrics)
        ];
    }

    /**
     * Color combination recommendations
     */
    public function get_color_combinations($color) {
        return [
            'complementary' => $this->get_complementary_colors($color),
            'analogous' => $this->get_analogous_colors($color),
            'triadic' => $this->get_triadic_colors($color),
            'split_complementary' => $this->get_split_complementary_colors($color),
            'tetradic' => $this->get_tetradic_colors($color),
            'monochromatic' => $this->get_monochromatic_colors($color),
            'accessibility_variants' => $this->get_accessible_variants($color)
        ];
    }

    /**
     * Color naming conventions
     */
    public function get_standardized_color_name($color) {
        return [
            'common_name' => $this->get_common_name($color),
            'pantone' => $this->get_pantone_name($color),
            'ral' => $this->get_ral_name($color),
            'ncs' => $this->get_ncs_name($color),
            'html' => $this->get_html_name($color),
            'localized' => $this->get_localized_color_name($color)
        ];
    }

    /**
     * Supporting methods for accessibility scoring
     */

    /**
     * Calculate contrast ratios for different backgrounds
     *
     * @param string $color Hex color code.
     * @return array Contrast ratios.
     */
    private function calculate_contrast_ratios($color) {
        try {
            $backgrounds = ['#FFFFFF', '#000000', '#808080'];
            $ratios = [];

            foreach ($backgrounds as $bg) {
                $ratios[$bg] = $this->color_utility->calculate_contrast_ratio($color, $bg);
            }

            return [
                'ratios' => $ratios,
                'meets_wcag_aa' => $this->meets_wcag_aa_requirements($ratios),
                'meets_wcag_aaa' => $this->meets_wcag_aaa_requirements($ratios)
            ];
        } catch (\Exception $e) {
            $this->log_error("Contrast ratio calculation failed", $e);
            return false;
        }
    }

    /**
     * Check WCAG AA compliance
     *
     * @param array $ratios Contrast ratios.
     * @return bool True if compliant.
     */
    private function meets_wcag_aa_requirements($ratios) {
        $min_ratio = min($ratios);
        return $min_ratio >= 4.5;
    }
}
