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
     * Generate harmonious color scheme
     *
     * @param string $base_color Base color in hex format
     * @param string $harmony_type Type of harmony to generate
     * @param array  $options Additional options for harmony generation
     * @return array Array of harmonious colors
     */
    public function generate_harmony(
        string $base_color,
        string $harmony_type,
        array $options = []
    ): array {
        $this->validate_color($base_color);
        $harmony_type = strtolower($harmony_type);

        return match($harmony_type) {
            Harmony_Types::COMPLEMENTARY => $this->generate_complementary($base_color, $options),
            Harmony_Types::ANALOGOUS => $this->generate_analogous($base_color, $options),
            Harmony_Types::TRIADIC => $this->generate_triadic($base_color, $options),
            Harmony_Types::SPLIT_COMPLEMENTARY => $this->generate_split_complementary($base_color, $options),
            Harmony_Types::TETRADIC => $this->generate_tetradic($base_color, $options),
            Harmony_Types::SQUARE => $this->generate_square($base_color, $options),
            Harmony_Types::MONOCHROMATIC => $this->generate_monochromatic($base_color, $options),
            default => throw new \InvalidArgumentException("Invalid harmony type: {$harmony_type}")
        };
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
     * Generate harmonious color combination
     *
     * @param string $base_color
     * @param string $harmony_type
     * @param array  $context
     *
     * @return array
     */
    public function generate_harmony_combination(string $base_color, string $harmony_type, array $context = []): array {
        $harmony_rules = $this->get_harmony_rules($harmony_type);
        if (!$harmony_rules) return null;

        $colors = $this->calculate_harmony_colors($base_color, $harmony_rules);
        $adjusted_colors = $this->apply_contextual_adjustments($colors, $context);

        return [
            'colors' => $adjusted_colors,
            'relationships' => $this->analyze_color_relationships($adjusted_colors),
            'balance_metrics' => $this->calculate_balance_metrics($adjusted_colors),
            'application_guidelines' => $this->generate_application_guidelines($adjusted_colors, $context)
        ];
    }

    /**
     * Validate color harmony
     *
     * @param array  $colors
     * @param string $harmony_type
     * @param array  $context
     *
     * @return array
     */
    public function validate_harmony(array $colors, string $harmony_type, array $context = []): array {
        $validation = [
            'is_valid' => true,
            'contrast_scores' => $this->calculate_contrast_scores($colors),
            'balance_metrics' => $this->calculate_balance_metrics($colors),
            'harmony_strength' => $this->calculate_harmony_strength($colors),
            'contextual_fit' => $this->evaluate_contextual_fit($colors, $context),
            'recommendations' => []
        ];

        // Add specific recommendations if needed
        if ($validation['contrast_scores']['minimum'] < 4.5) {
            $validation['is_valid'] = false;
            $validation['recommendations'][] = [
                'type' => 'contrast_adjustment',
                'details' => 'Increase contrast for better accessibility'
            ];
        }

        return $validation;
    }

    /**
     * Generate harmonic combinations
     *
     * @param string $base_color
     *
     * @return array
     */
    public function generate_harmonic_combinations(string $base_color): array {
        return [
            'complementary' => $this->get_complementary_colors($base_color),
            'analogous' => $this->get_analogous_colors($base_color),
            'triadic' => $this->get_triadic_colors($base_color),
            'split_complementary' => $this->get_split_complementary($base_color),
            'tetradic' => $this->get_tetradic_colors($base_color),
            'square' => $this->get_square_colors($base_color)
        ];
    }

    /**
     * Optimize color harmony
     *
     * @param array $palette
     *
     * @return array
     */
    public function optimize_harmony(array $palette): array {
        $current_harmony = $this->analyze_harmony($palette);
        $optimization_needed = $this->check_optimization_needed($current_harmony);

        if ($optimization_needed) {
            return [
                'optimized_palette' => $this->perform_harmony_optimization($palette),
                'harmony_score' => $this->calculate_harmony_score($palette),
                'adjustments_made' => $this->get_optimization_adjustments(),
                'harmony_analysis' => $this->analyze_optimized_harmony()
            ];
        }

        return [
            'status' => 'harmony_optimal',
            'current_score' => $current_harmony['score'],
            'analysis' => $current_harmony
        ];
    }

    /**
     * Generate harmony variations
     *
     * @param array $palette
     *
     * @return array
     */
    public function generate_harmony_variations(array $palette): array {
        return [
            'monochromatic' => $this->generate_monochromatic_variation($palette),
            'warm_harmony' => $this->generate_warm_variation($palette),
            'cool_harmony' => $this->generate_cool_variation($palette),
            'neutral_harmony' => $this->generate_neutral_variation($palette),
            'vibrant_harmony' => $this->generate_vibrant_variation($palette)
        ];
    }
}
