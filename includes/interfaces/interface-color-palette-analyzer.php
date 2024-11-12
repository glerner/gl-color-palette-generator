<?php

namespace GLColorPalette\Interfaces;

/**
 * Color Palette Analyzer Interface
 *
 * Defines the contract for analyzing color palettes and their properties.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface ColorPaletteAnalyzer {
    /**
     * Analyzes color harmony.
     *
     * @param array $palette Palette to analyze.
     * @param array $options {
     *     Optional. Harmony options.
     *     @type array  $schemes       Harmony schemes.
     *     @type array  $thresholds    Harmony thresholds.
     *     @type array  $weights       Color weights.
     *     @type array  $metadata      Analysis metadata.
     * }
     * @return array {
     *     Harmony results.
     *     @type array  $relationships Color relationships.
     *     @type array  $scores        Harmony scores.
     *     @type array  $suggestions   Improvement suggestions.
     *     @type array  $metadata      Analysis metadata.
     * }
     */
    public function analyze_harmony(array $palette, array $options = []): array;

    /**
     * Analyzes color contrast.
     *
     * @param array $palette Palette to analyze.
     * @param array $options {
     *     Optional. Contrast options.
     *     @type array  $ratios        Target ratios.
     *     @type array  $combinations  Color combinations.
     *     @type array  $standards     Accessibility standards.
     *     @type array  $metadata      Analysis metadata.
     * }
     * @return array {
     *     Contrast results.
     *     @type array  $ratios        Contrast ratios.
     *     @type array  $compliance    Standards compliance.
     *     @type array  $suggestions   Improvement suggestions.
     *     @type array  $metadata      Analysis metadata.
     * }
     */
    public function analyze_contrast(array $palette, array $options = []): array;

    /**
     * Analyzes color distribution.
     *
     * @param array $palette Palette to analyze.
     * @param array $options {
     *     Optional. Distribution options.
     *     @type array  $metrics       Distribution metrics.
     *     @type array  $ranges        Value ranges.
     *     @type array  $weights       Color weights.
     *     @type array  $metadata      Analysis metadata.
     * }
     * @return array {
     *     Distribution results.
     *     @type array  $metrics       Distribution metrics.
     *     @type array  $balance       Color balance.
     *     @type array  $suggestions   Balance suggestions.
     *     @type array  $metadata      Analysis metadata.
     * }
     */
    public function analyze_distribution(array $palette, array $options = []): array;

    /**
     * Analyzes color psychology.
     *
     * @param array $palette Palette to analyze.
     * @param array $options {
     *     Optional. Psychology options.
     *     @type array  $associations  Color associations.
     *     @type array  $context       Usage context.
     *     @type array  $culture      Cultural factors.
     *     @type array  $metadata      Analysis metadata.
     * }
     * @return array {
     *     Psychology results.
     *     @type array  $meanings      Color meanings.
     *     @type array  $emotions      Emotional impact.
     *     @type array  $suggestions   Usage suggestions.
     *     @type array  $metadata      Analysis metadata.
     * }
     */
    public function analyze_psychology(array $palette, array $options = []): array;
} 
