<?php
/**
 * AI Color Service Interface
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 */

namespace GL_Color_Palette_Generator\Interfaces;

use WP_Error;

/**
 * Interface AI_Color_Service
 *
 * Defines the contract for AI-powered color services
 *
 * @since 1.0.0
 */
interface AI_Color_Service {
    /**
     * Generate color scheme based on input parameters
     *
     * @param array $params Color generation parameters
     * @return array|WP_Error Generated color scheme or error
     */
    public function generate_color_scheme($params);

    /**
     * Analyze color combination
     *
     * @param array $colors Array of colors to analyze
     * @return array|WP_Error Analysis results or error
     */
    public function analyze_color_combination($colors);

    /**
     * Get color recommendations
     *
     * @param array $context Context for recommendations
     * @return array|WP_Error Color recommendations or error
     */
    public function get_color_recommendations($context);

    /**
     * Optimize color palette
     *
     * @param array $palette Current color palette
     * @param array $constraints Optimization constraints
     * @return array|WP_Error Optimized palette or error
     */
    public function optimize_color_palette($palette, $constraints = []);
}
