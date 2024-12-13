<?php declare(strict_types=1);

/**
 * Color Harmonizer Interface
 *
 * Defines the contract for creating and analyzing color harmonies.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Interfaces;

/**
 * Color Harmonizer Interface
 *
 * @since 1.0.0
 */
interface Color_Harmonizer_Interface {
    /**
     * Generates harmonious colors based on a base color.
     *
     * @param string $base_color Base color in hex format.
     * @param string $harmony_type Type of harmony ('complementary', 'analogous', 'triadic', etc.).
     * @param array $options {
     *     Optional. Generation options.
     *     @type int    $count           Number of colors to generate.
     *     @type float  $angle_offset    Custom angle offset for harmony.
     *     @type bool   $include_base    Include base color in result.
     *     @type array  $saturation_range Allowed saturation range.
     *     @type array  $lightness_range  Allowed lightness range.
     * }
     * @return array {
     *     Harmony generation results.
     *     @type array  $colors          Generated harmonious colors.
     *     @type array  $relationships   Color relationship descriptions.
     *     @type array  $angles          Color wheel angles used.
     *     @type float  $harmony_score   Overall harmony score (0-1).
     * }
     * @throws \InvalidArgumentException If base color or harmony type is invalid.
     */
    public function generate_harmony(string $base_color, string $harmony_type, array $options = []): array;

    /**
     * Analyzes harmony between given colors.
     *
     * @param array $colors Array of colors to analyze.
     * @return array {
     *     Harmony analysis results.
     *     @type string $harmony_type    Detected harmony type.
     *     @type float  $harmony_score   Overall harmony score (0-1).
     *     @type array  $relationships   Color relationships found.
     *     @type array  $improvements    Suggested improvements.
     *     @type array  $metrics {
     *         Detailed harmony metrics.
     *         @type float $hue_spacing    Score for hue distribution.
     *         @type float $value_balance  Score for value balance.
     *         @type float $saturation_coherence Score for saturation coherence.
     *     }
     * }
     */
    public function analyze_harmony(array $colors): array;

    /**
     * Suggests harmony improvements.
     *
     * @param array $colors Current color set.
     * @param string $target_harmony Desired harmony type.
     * @return array {
     *     Improvement suggestions.
     *     @type array  $adjustments     Suggested color adjustments.
     *     @type array  $alternatives    Alternative color options.
     *     @type string $explanation     Explanation of suggestions.
     *     @type float  $impact_score    Potential improvement score.
     * }
     */
    public function suggest_improvements(array $colors, string $target_harmony): array;

    /**
     * Gets available harmony types and their properties.
     *
     * @return array {
     *     Harmony types information.
     *     @type array $harmonies {
     *         Harmony details indexed by type.
     *         @type string $name        Human-readable name.
     *         @type string $description Usage description.
     *         @type array  $angles      Traditional angle patterns.
     *         @type array  $use_cases   Recommended use cases.
     *     }
     * }
     */
    public function get_harmony_types(): array;
} 
