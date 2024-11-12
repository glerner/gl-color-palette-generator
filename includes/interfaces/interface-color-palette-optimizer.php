<?php

namespace GLColorPalette\Interfaces;

/**
 * Color Palette Optimizer Interface
 *
 * Defines the contract for optimizing color palettes for various purposes.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface ColorPaletteOptimizer {
    /**
     * Optimizes for accessibility.
     *
     * @param array $palette Palette to optimize.
     * @param array $options {
     *     Optional. Accessibility options.
     *     @type array  $standards     Target standards.
     *     @type array  $constraints   Color constraints.
     *     @type array  $priorities    Optimization priorities.
     *     @type array  $metadata      Optimization metadata.
     * }
     * @return array {
     *     Optimization results.
     *     @type array  $optimized     Optimized palette.
     *     @type array  $improvements  Accessibility improvements.
     *     @type array  $compliance    Standards compliance.
     *     @type array  $metadata      Optimization metadata.
     * }
     */
    public function optimize_accessibility(array $palette, array $options = []): array;

    /**
     * Optimizes for harmony.
     *
     * @param array $palette Palette to optimize.
     * @param array $options {
     *     Optional. Harmony options.
     *     @type array  $schemes       Target schemes.
     *     @type array  $thresholds    Harmony thresholds.
     *     @type array  $constraints   Color constraints.
     *     @type array  $metadata      Optimization metadata.
     * }
     * @return array {
     *     Optimization results.
     *     @type array  $optimized     Optimized palette.
     *     @type array  $improvements  Harmony improvements.
     *     @type array  $scores        Harmony scores.
     *     @type array  $metadata      Optimization metadata.
     * }
     */
    public function optimize_harmony(array $palette, array $options = []): array;

    /**
     * Optimizes for performance.
     *
     * @param array $palette Palette to optimize.
     * @param array $options {
     *     Optional. Performance options.
     *     @type array  $targets       Performance targets.
     *     @type array  $compression   Color compression.
     *     @type array  $formats       Output formats.
     *     @type array  $metadata      Optimization metadata.
     * }
     * @return array {
     *     Optimization results.
     *     @type array  $optimized     Optimized palette.
     *     @type array  $improvements  Performance improvements.
     *     @type array  $metrics       Performance metrics.
     *     @type array  $metadata      Optimization metadata.
     * }
     */
    public function optimize_performance(array $palette, array $options = []): array;

    /**
     * Optimizes for display.
     *
     * @param array $palette Palette to optimize.
     * @param array $options {
     *     Optional. Display options.
     *     @type array  $devices       Target devices.
     *     @type array  $environments  Display environments.
     *     @type array  $calibration   Color calibration.
     *     @type array  $metadata      Optimization metadata.
     * }
     * @return array {
     *     Optimization results.
     *     @type array  $optimized     Optimized palette.
     *     @type array  $improvements  Display improvements.
     *     @type array  $profiles      Device profiles.
     *     @type array  $metadata      Optimization metadata.
     * }
     */
    public function optimize_display(array $palette, array $options = []): array;
} 
