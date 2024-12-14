<?php
/**
 * Color Scheme Generator Interface
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 */

namespace GL_Color_Palette_Generator\Interfaces;

use WP_Error;

/**
 * Interface for generating color schemes
 */
interface Color_Scheme_Generator_Interface {
    /**
     * Generate a color scheme from a base color
     *
     * @param string $base_color Base color in hex format
     * @param array  $options Generation options
     * @return array|WP_Error Array of colors or error
     */
    public function generate_scheme($base_color, $options = []);

    /**
     * Generate a monochromatic scheme
     *
     * @param string $base_color Base color in hex format
     * @param int    $count Number of colors to generate
     * @return array|WP_Error Array of colors or error
     */
    public function generate_monochromatic($base_color, $count = 5);

    /**
     * Generate an analogous scheme
     *
     * @param string $base_color Base color in hex format
     * @param int    $count Number of colors to generate
     * @return array|WP_Error Array of colors or error
     */
    public function generate_analogous($base_color, $count = 5);

    /**
     * Generate a complementary scheme
     *
     * @param string $base_color Base color in hex format
     * @param int    $count Number of colors to generate
     * @return array|WP_Error Array of colors or error
     */
    public function generate_complementary($base_color, $count = 4);

    /**
     * Generate a split complementary scheme
     *
     * @param string $base_color Base color in hex format
     * @param int    $count Number of colors to generate
     * @return array|WP_Error Array of colors or error
     */
    public function generate_split_complementary($base_color, $count = 3);

    /**
     * Generate a triadic scheme
     *
     * @param string $base_color Base color in hex format
     * @param int    $count Number of colors to generate
     * @return array|WP_Error Array of colors or error
     */
    public function generate_triadic($base_color, $count = 3);

    /**
     * Generate a tetradic scheme
     *
     * @param string $base_color Base color in hex format
     * @param int    $count Number of colors to generate
     * @return array|WP_Error Array of colors or error
     */
    public function generate_tetradic($base_color, $count = 4);

    /**
     * Generate a custom scheme based on color theory rules
     *
     * @param string $base_color Base color in hex format
     * @param array  $rules Color theory rules to apply
     * @return array|WP_Error Array of colors or error
     */
    public function generate_custom_scheme($base_color, $rules);

    /**
     * Generate a scheme from an image
     *
     * @param string $image_path Path to image file
     * @param array  $options Extraction options
     * @return array|WP_Error Array of colors or error
     */
    public function generate_from_image($image_path, $options = []);

    /**
     * Generate a scheme based on a theme or mood
     *
     * @param string $theme Theme or mood name
     * @param array  $options Generation options
     * @return array|WP_Error Array of colors or error
     */
    public function generate_themed_scheme($theme, $options = []);

    /**
     * Adjust scheme colors for better contrast
     *
     * @param array $colors Array of colors in hex format
     * @param array $options Adjustment options
     * @return array|WP_Error Adjusted colors or error
     */
    public function adjust_scheme_contrast($colors, $options = []);

    /**
     * Get available color scheme types
     *
     * @return array List of available scheme types
     */
    public function get_available_schemes();

    /**
     * Get color theory rules for scheme generation
     *
     * @return array List of available color theory rules
     */
    public function get_color_theory_rules();

    /**
     * Validate a generated scheme
     *
     * @param array $colors Array of colors in hex format
     * @param array $rules Validation rules
     * @return bool|WP_Error True if valid, error if not
     */
    public function validate_scheme($colors, $rules = []);
}
