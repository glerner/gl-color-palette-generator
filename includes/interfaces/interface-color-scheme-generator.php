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
    public function generate_scheme(string $base_color, array $options = []): array|WP_Error;

    /**
     * Generate a monochromatic scheme
     *
     * @param string $base_color Base color in hex format
     * @param int    $count Number of colors to generate
     * @return array|WP_Error Array of colors or error
     */
    public function generate_monochromatic(string $base_color, int $count = 5): array|WP_Error;

    /**
     * Generate an analogous scheme
     *
     * @param string $base_color Base color in hex format
     * @param int    $count Number of colors to generate
     * @return array|WP_Error Array of colors or error
     */
    public function generate_analogous(string $base_color, int $count = 5): array|WP_Error;

    /**
     * Generate a complementary scheme
     *
     * @param string $base_color Base color in hex format
     * @param int    $count Number of colors to generate
     * @return array|WP_Error Array of colors or error
     */
    public function generate_complementary(string $base_color, int $count = 4): array|WP_Error;

    /**
     * Generate a split complementary scheme
     *
     * @param string $base_color Base color in hex format
     * @param int    $count Number of colors to generate
     * @return array|WP_Error Array of colors or error
     */
    public function generate_split_complementary(string $base_color, int $count = 3): array|WP_Error;

    /**
     * Generate a triadic scheme
     *
     * @param string $base_color Base color in hex format
     * @param int    $count Number of colors to generate
     * @return array|WP_Error Array of colors or error
     */
    public function generate_triadic(string $base_color, int $count = 3): array|WP_Error;

    /**
     * Generate a tetradic scheme
     *
     * @param string $base_color Base color in hex format
     * @param int    $count Number of colors to generate
     * @return array|WP_Error Array of colors or error
     */
    public function generate_tetradic(string $base_color, int $count = 4): array|WP_Error;

    /**
     * Generate a custom scheme based on color theory rules
     *
     * @param string $base_color Base color in hex format
     * @param array  $rules Color theory rules to apply
     * @return array|WP_Error Array of colors or error
     */
    public function generate_custom_scheme(string $base_color, array $rules): array|WP_Error;

    /**
     * Generate a scheme from an image
     *
     * @param string $image_path Path to image file
     * @param array  $options Extraction options
     * @return array|WP_Error Array of colors or error
     */
    public function generate_from_image(string $image_path, array $options = []): array|WP_Error;

    /**
     * Generate a scheme based on a theme or mood
     *
     * @param string $theme Theme or mood name
     * @param array  $options Generation options
     * @return array|WP_Error Array of colors or error
     */
    public function generate_themed_scheme(string $theme, array $options = []): array|WP_Error;

    /**
     * Adjust scheme colors for better contrast
     *
     * @param array $colors Array of colors in hex format
     * @param array $options Adjustment options
     * @return array|WP_Error Adjusted colors or error
     */
    public function adjust_scheme_contrast(array $colors, array $options = []): array|WP_Error;

    /**
     * Get available color scheme types
     *
     * @return array List of available scheme types
     */
    public function get_available_schemes(): array;

    /**
     * Get color theory rules for scheme generation
     *
     * @return array List of available color theory rules
     */
    public function get_color_theory_rules(): array;

    /**
     * Validate a generated scheme
     *
     * @param array $colors Array of colors in hex format
     * @param array $rules Validation rules
     * @return bool|WP_Error True if valid, error if not
     */
    public function validate_scheme(array $colors, array $rules = []): bool|WP_Error;
}
