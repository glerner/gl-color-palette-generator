<?php declare(strict_types=1);
/**
 * Color Metrics Analyzer Interface
 *
 * Defines the contract for analyzing color metrics including perceptual,
 * psychological, and accessibility measurements.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Interfaces;

/**
 * Color Metrics Analyzer Interface
 *
 * @since 1.0.0
 */
interface Color_Metrics_Analyzer_Interface {
    /**
     * Analyze comprehensive color metrics
     *
     * @param string $color Hex color code
     * @return array Color metrics analysis
     */
    public function analyze_color(string $color): array;

    /**
     * Get basic color metrics
     *
     * @param string $color Hex color code
     * @return array Basic metrics data
     */
    public function get_basic_metrics(string $color): array;
}
