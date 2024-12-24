<?php declare(strict_types=1);

namespace GL_Color_Palette_Generator\Interfaces;

use WP_Error;

/**
 * Color Metrics Analyzer Interface
 *
 * Defines the contract for analyzing and measuring color metrics and relationships.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 * @since 1.0.0
 */
interface Color_Metrics_Analyzer {
    /**
     * Analyzes a single color's metrics.
     *
     * @param string $color Color to analyze (hex format)
     * @return array|WP_Error Analysis results or error
     */
    public function analyze_color(string $color);

    /**
     * Analyzes print output characteristics.
     *
     * @param string $color Color to analyze
     * @return array|WP_Error Print analysis results or error
     */
    public function analyze_print_output(string $color);

    /**
     * Analyzes screen display characteristics.
     *
     * @param string $color Color to analyze
     * @return array|WP_Error Screen analysis results or error
     */
    public function analyze_screen_display(string $color);

    /**
     * Analyzes color display across different devices.
     *
     * @param string $color Color to analyze
     * @return array|WP_Error Device analysis results or error
     */
    public function analyze_device_display(string $color);
}
