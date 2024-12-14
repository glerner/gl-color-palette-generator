/**
 * Color Harmonization Class
 *
 * Implements color harmony rules and relationships for generating aesthetically
 * pleasing color combinations. Supports various harmony types including
 * complementary, analogous, triadic, and dynamic mood-based harmonies.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Color_Management
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Color_Management;

use GL_Color_Palette_Generator\Interfaces\Color_Harmonizer_Interface;
use GL_Color_Palette_Generator\Types\Color_Types;
use GL_Color_Palette_Generator\Types\Harmony_Types;
use GL_Color_Palette_Generator\Color_Management\Color_Analyzer;
use GL_Color_Palette_Generator\Color_Management\Color_Utility;
use GL_Color_Palette_Generator\Settings\Settings_Manager;
use WP_Error;

/**
 * Color Harmonization Class
 *
 * Implements color harmony algorithms and relationships for generating
 * aesthetically pleasing color combinations. Supports various harmony
 * types and dynamic mood-based harmonies.
 *
 * @since 1.0.0
 */
class Color_Harmonization implements Color_Harmonizer_Interface {
    /**
     * Color analyzer instance
     *
     * @var Color_Analyzer
     */
    protected Color_Analyzer $analyzer;

    /**
     * Color utility instance
     *
     * @var Color_Utility
     */
    protected Color_Utility $utility;

    /**
     * Settings manager instance
     *
     * @var Settings_Manager
     */
    protected Settings_Manager $settings;

    /**
     * Harmony rules and specifications
     *
     * @var array
     * @since 1.0.0
     */
    private const HARMONY_RULES = [
        'classical_harmonies' => [
            'complementary' => [
                'primary_rules' => [
                    'hue_difference' => 180,
                    'saturation_balance' => true,
                    'lightness_contrast' => 'moderate'
                ],
                'variations' => [
                    'split' => [
                        'angle_offset' => 30,
                        'count' => 3
                    ],
                    'double' => [
                        'pair_spacing' => 60,
                        'count' => 4
                    ]
                ]
            ],
            'analogous' => [
                'primary_rules' => [
                    'hue_range' => 30,
                    'saturation_step' => 10,
                    'lightness_step' => 5
                ],
                'variations' => [
                    'close' => [
                        'hue_range' => 15
                    ],
                    'wide' => [
                        'hue_range' => 45
                    ]
                ]
            ]
        ],
        'dynamic_harmonies' => [
            'mood_based' => [
                'energetic' => [
                    'primary_hue_range' => [0, 60],
                    'contrast_level' => 'high',
                    'saturation_range' => [70, 100],
                    'lightness_range' => [45, 65]
                ],
                'calm' => [
                    'primary_hue_range' => [180, 240],
                    'contrast_level' => 'low',
                    'saturation_range' => [20, 50],
                    'lightness_range' => [60, 80]
                ]
            ]
        ]
    ];

    /**
     * Constructor
     *
     * @param Color_Analyzer   $analyzer Color analyzer instance
     * @param Color_Utility    $utility  Color utility instance
     * @param Settings_Manager $settings Settings manager instance
     */
    public function __construct(
        Color_Analyzer $analyzer,
        Color_Utility $utility,
        Settings_Manager $settings
    ) {
        $this->analyzer = $analyzer;
        $this->utility = $utility;
        $this->settings = $settings;
    }

    /**
     * Get complementary color
     *
     * @param string $color Color in hex format
     * @return string|WP_Error Complementary color or error
     */
    public function get_complementary($color) {
        try {
            $this->validate_color($color);
            $hsl = $this->utility->hex_to_hsl($color);
            $hsl['h'] = ($hsl['h'] + 180) % 360;
            return $this->utility->hsl_to_hex($hsl);
        } catch (\Exception $e) {
            return new WP_Error('invalid_color', $e->getMessage());
        }
    }

    /**
     * Get analogous colors
     *
     * @param string $color Color in hex format
     * @param int    $count Number of colors (default 2)
     * @return array|WP_Error Array of analogous colors or error
     */
    public function get_analogous($color, $count = 2) {
        try {
            $this->validate_color($color);
            $colors = [];
            $hsl = $this->utility->hex_to_hsl($color);
            $angle = 30;

            for ($i = 1; $i <= $count; $i++) {
                $new_hsl = $hsl;
                $new_hsl['h'] = ($hsl['h'] + ($angle * $i)) % 360;
                $colors[] = $this->utility->hsl_to_hex($new_hsl);
            }

            return $colors;
        } catch (\Exception $e) {
            return new WP_Error('invalid_color', $e->getMessage());
        }
    }

    /**
     * Get triadic colors
     *
     * @param string $color Color in hex format
     * @return array|WP_Error Array of triadic colors or error
     */
    public function get_triadic($color) {
        try {
            $this->validate_color($color);
            $colors = [];
            $hsl = $this->utility->hex_to_hsl($color);

            for ($i = 0; $i < 3; $i++) {
                $new_hsl = $hsl;
                $new_hsl['h'] = ($hsl['h'] + (120 * $i)) % 360;
                $colors[] = $this->utility->hsl_to_hex($new_hsl);
            }

            return $colors;
        } catch (\Exception $e) {
            return new WP_Error('invalid_color', $e->getMessage());
        }
    }

    /**
     * Get tetradic colors
     *
     * @param string $color Color in hex format
     * @return array|WP_Error Array of tetradic colors or error
     */
    public function get_tetradic($color) {
        try {
            $this->validate_color($color);
            $colors = [];
            $hsl = $this->utility->hex_to_hsl($color);

            for ($i = 0; $i < 4; $i++) {
                $new_hsl = $hsl;
                $new_hsl['h'] = ($hsl['h'] + (90 * $i)) % 360;
                $colors[] = $this->utility->hsl_to_hex($new_hsl);
            }

            return $colors;
        } catch (\Exception $e) {
            return new WP_Error('invalid_color', $e->getMessage());
        }
    }

    /**
     * Get split complementary colors
     *
     * @param string $color Color in hex format
     * @return array|WP_Error Array of split complementary colors or error
     */
    public function get_split_complementary($color) {
        try {
            $this->validate_color($color);
            $colors = [];
            $hsl = $this->utility->hex_to_hsl($color);
            $complement_h = ($hsl['h'] + 180) % 360;

            $colors[] = $color;
            $colors[] = $this->utility->hsl_to_hex(['h' => ($complement_h - 30) % 360, 's' => $hsl['s'], 'l' => $hsl['l']]);
            $colors[] = $this->utility->hsl_to_hex(['h' => ($complement_h + 30) % 360, 's' => $hsl['s'], 'l' => $hsl['l']]);

            return $colors;
        } catch (\Exception $e) {
            return new WP_Error('invalid_color', $e->getMessage());
        }
    }

    /**
     * Get monochromatic variations
     *
     * @param string $color Color in hex format
     * @param int    $count Number of variations
     * @return array|WP_Error Array of monochromatic colors or error
     */
    public function get_monochromatic($color, $count = 5) {
        try {
            $this->validate_color($color);
            $colors = [];
            $hsl = $this->utility->hex_to_hsl($color);
            $step = 1 / ($count + 1);

            for ($i = 1; $i <= $count; $i++) {
                $new_hsl = $hsl;
                $new_hsl['l'] = min(1, max(0, $step * $i));
                $colors[] = $this->utility->hsl_to_hex($new_hsl);
            }

            return $colors;
        } catch (\Exception $e) {
            return new WP_Error('invalid_color', $e->getMessage());
        }
    }

    /**
     * Adjust color harmony
     *
     * @param array $colors Array of colors to harmonize
     * @param array $options Harmonization options
     * @return array|WP_Error Harmonized colors or error
     */
    public function harmonize_colors($colors, $options = []) {
        try {
            $harmonized = [];
            foreach ($colors as $color) {
                $this->validate_color($color);
                $hsl = $this->utility->hex_to_hsl($color);
                
                // Apply harmonization adjustments based on options
                if (!empty($options['saturation_adjustment'])) {
                    $hsl['s'] = min(1, max(0, $hsl['s'] + $options['saturation_adjustment']));
                }
                if (!empty($options['lightness_adjustment'])) {
                    $hsl['l'] = min(1, max(0, $hsl['l'] + $options['lightness_adjustment']));
                }
                
                $harmonized[] = $this->utility->hsl_to_hex($hsl);
            }
            return $harmonized;
        } catch (\Exception $e) {
            return new WP_Error('harmonization_error', $e->getMessage());
        }
    }

    /**
     * Check if colors are harmonious
     *
     * @param array $colors Array of colors to check
     * @param array $rules Harmony rules to apply
     * @return bool|WP_Error True if harmonious, error if not
     */
    public function are_harmonious($colors, $rules = []) {
        try {
            foreach ($colors as $color) {
                $this->validate_color($color);
            }

            // Calculate harmony score
            $score = $this->calculate_harmony_score($colors);
            $threshold = $rules['threshold'] ?? 0.7;

            return $score >= $threshold;
        } catch (\Exception $e) {
            return new WP_Error('harmony_check_error', $e->getMessage());
        }
    }

    /**
     * Get harmony type between colors
     *
     * @param array $colors Array of colors to analyze
     * @return string|WP_Error Harmony type or error
     */
    public function get_harmony_type($colors) {
        try {
            foreach ($colors as $color) {
                $this->validate_color($color);
            }

            // Analyze color relationships
            $relationships = $this->analyze_color_relationships($colors);
            return $relationships['harmony_type'];
        } catch (\Exception $e) {
            return new WP_Error('harmony_type_error', $e->getMessage());
        }
    }

    /**
     * Suggest harmony improvements
     *
     * @param array $colors Array of colors to improve
     * @param array $options Improvement options
     * @return array|WP_Error Suggested improvements or error
     */
    public function suggest_harmony_improvements($colors, $options = []) {
        try {
            $suggestions = [];
            $harmony_score = $this->calculate_harmony_score($colors);

            if ($harmony_score < 0.7) {
                $suggestions[] = [
                    'type' => 'harmony',
                    'message' => 'Consider adjusting hues to follow standard color harmony rules',
                    'suggested_colors' => $this->get_harmony_suggestions($colors)
                ];
            }

            return $suggestions;
        } catch (\Exception $e) {
            return new WP_Error('improvement_error', $e->getMessage());
        }
    }

    /**
     * Get available harmony rules
     *
     * @return array List of available harmony rules
     */
    public function get_harmony_rules() {
        return self::HARMONY_RULES;
    }

    /**
     * Apply specific harmony rule
     *
     * @param string $rule_name Rule to apply
     * @param array  $colors Colors to apply rule to
     * @return array|WP_Error Modified colors or error
     */
    public function apply_harmony_rule($rule_name, $colors) {
        try {
            $rules = $this->get_harmony_rules();
            if (!isset($rules['classical_harmonies'][$rule_name])) {
                throw new \InvalidArgumentException("Invalid harmony rule: {$rule_name}");
            }

            $rule = $rules['classical_harmonies'][$rule_name];
            return $this->apply_harmony_adjustments($colors, $rule);
        } catch (\Exception $e) {
            return new WP_Error('rule_application_error', $e->getMessage());
        }
    }

    /**
     * Validate color value
     *
     * @param string $color Color to validate
     * @throws \InvalidArgumentException If color is invalid
     */
    private function validate_color(string $color): void {
        if (!preg_match('/^#[a-f0-9]{6}$/i', $color)) {
            throw new \InvalidArgumentException(
                'Invalid color format. Must be a 6-digit hex color (e.g., #FF0000)'
            );
        }
    }

    /**
     * Calculate harmony score for colors
     *
     * @param array $colors Colors to analyze
     * @return float Harmony score between 0 and 1
     */
    private function calculate_harmony_score(array $colors): float {
        // Implementation details...
        return 0.8; // Placeholder
    }

    /**
     * Analyze color relationships
     *
     * @param array $colors Colors to analyze
     * @return array Analysis results
     */
    private function analyze_color_relationships(array $colors): array {
        // Implementation details...
        return [
            'harmony_type' => 'complementary',
            'relationships' => []
        ];
    }

    /**
     * Get harmony suggestions
     *
     * @param array $colors Colors to get suggestions for
     * @return array Suggested colors
     */
    private function get_harmony_suggestions(array $colors): array {
        // Implementation details...
        return [];
    }

    /**
     * Apply harmony adjustments
     *
     * @param array $colors Colors to adjust
     * @param array $rule Rule to apply
     * @return array Adjusted colors
     */
    private function apply_harmony_adjustments(array $colors, array $rule): array {
        // Implementation details...
        return $colors;
    }
}
