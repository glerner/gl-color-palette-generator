<?php

namespace GL_Color_Palette_Generator\Interfaces;

use WP_Error;

/**
 * Color Palette Generator Interface
 *
 * Defines the contract for generating color palettes programmatically.
 * Supports traditional color wheel harmonies, WordPress-specific schemes,
 * and AI-powered generation methods.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface Color_Palette_Generator_Interface {
    /**
     * Generates a new color palette.
     *
     * @param array $options {
     *     Optional. Generation options.
     *     @type string $base_color    Base color to build palette around.
     *     @type string $scheme_type   Type of color scheme (see Color_Constants::COLOR_SCHEMES).
     *     @type int    $color_count   Number of colors in palette (default 5).
     *     @type array  $constraints   Color constraints (brightness, saturation ranges).
     *     @type array  $business_context {
     *         Optional. Business and brand context for AI generation.
     *         @type string $description    Business description.
     *         @type string $industry       Business industry.
     *         @type string $target_audience Target audience description.
     *         @type string $mood           Desired mood/emotional impact.
     *     }
     *     @type array  $image_data {
     *         Optional. Image-based generation data.
     *         @type string $image_path     Path to uploaded image.
     *         @type string $context_type   How to use image: 'extract' or 'ai-enhance'.
     *     }
     * }
     * @return array|WP_Error {
     *     Generated palette data or error object.
     *     @type array  $colors {
     *         Array of colors with roles as keys.
     *         @type array $primary {
     *             @type string $hex     Hex color code.
     *             @type string $name    Human-readable color name.
     *             @type string $emotion Description of emotional impact.
     *         }
     *         @type array $secondary Similar structure to primary.
     *         @type array $accent    Similar structure to primary.
     *         @type array $contrast  Similar structure to primary.
     *     }
     *     @type string $scheme_type   Scheme type used.
     *     @type array  $inspiration {
     *         Optional. Source of inspiration.
     *         @type string $type      'image' or 'business'.
     *         @type string $source    Description of inspiration source.
     *     }
     * }
     * @throws \InvalidArgumentException If criteria are invalid.
     */
    public function generate_palette(array $options = []): array|WP_Error;

    /**
     * Generates a complementary color palette.
     *
     * @param string $base_color Base color to build from.
     * @param array  $options    Generation options.
     * @return array|WP_Error Generated palette data or error.
     */
    public function generate_complementary(string $base_color, array $options = []): array|WP_Error;

    /**
     * Generates an analogous color palette.
     *
     * @param string $base_color Base color to build from.
     * @param array  $options    Generation options.
     * @return array|WP_Error Generated palette data or error.
     */
    public function generate_analogous(string $base_color, array $options = []): array|WP_Error;

    /**
     * Generates an analogous with complement color palette.
     *
     * @param string $base_color Base color to build from.
     * @param array  $options    Generation options.
     * @return array|WP_Error Generated palette data or error.
     */
    public function generate_analogous_complement(string $base_color, array $options = []): array|WP_Error;

    /**
     * Generates a triadic color palette.
     *
     * @param string $base_color Base color to build from.
     * @param array  $options    Generation options.
     * @return array|WP_Error Generated palette data or error.
     */
    public function generate_triadic(string $base_color, array $options = []): array|WP_Error;

    /**
     * Generates a monochromatic color palette.
     *
     * @param string $base_color Base color to build from.
     * @param array  $options    Generation options.
     * @return array|WP_Error Generated palette data or error.
     */
    public function generate_monochromatic(string $base_color, array $options = []): array|WP_Error;

    /**
     * Generates a split-complementary color palette.
     *
     * @param string $base_color Base color to build from.
     * @param array  $options    Generation options.
     * @return array|WP_Error Generated palette data or error.
     */
    public function generate_split_complementary(string $base_color, array $options = []): array|WP_Error;

    /**
     * Generates a tetradic (double complementary) color palette.
     *
     * @param string $base_color Base color to build from.
     * @param array  $options    Generation options.
     * @return array|WP_Error Generated palette data or error.
     */
    public function generate_tetradic(string $base_color, array $options = []): array|WP_Error;

    /**
     * Generates a square color palette.
     *
     * @param string $base_color Base color to build from.
     * @param array  $options    Generation options.
     * @return array|WP_Error Generated palette data or error.
     */
    public function generate_square(string $base_color, array $options = []): array|WP_Error;

    /**
     * Generates a monochromatic palette with accent color.
     *
     * @param string $base_color Base color to build from.
     * @param array  $options    Generation options.
     * @return array|WP_Error Generated palette data or error.
     */
    public function generate_monochromatic_accent(string $base_color, array $options = []): array|WP_Error;

    /**
     * Generates a dual tone palette with neutral grays.
     *
     * @param string $base_color Base color to build from.
     * @param array  $options    Generation options.
     * @return array|WP_Error Generated palette data or error.
     */
    public function generate_dual_tone(string $base_color, array $options = []): array|WP_Error;

    /**
     * Generates a neutral palette with one vibrant accent.
     *
     * @param string $base_color Base color to build from.
     * @param array  $options    Generation options.
     * @return array|WP_Error Generated palette data or error.
     */
    public function generate_neutral_pop(string $base_color, array $options = []): array|WP_Error;

    /**
     * Generates a palette using AI analysis.
     *
     * @param array $options {
     *     Generation options.
     *     @type array  $business_context Business and brand context.
     *     @type array  $image_data      Optional image data for AI use.
     *     @type array  $constraints     Optional color constraints.
     * }
     * @return array|WP_Error Generated palette data or error.
     */
    public function generate_ai_palette(array $options = []): array|WP_Error;

    /**
     * Generates a palette from an image.
     *
     * @param string $image_path  Path to the image.
     * @param array  $options {
     *     Optional. Generation options.
     *     @type string $context_type How to use image: 'extract' or 'ai-enhance'.
     *     @type array  $constraints  Color constraints.
     * }
     * @return array|WP_Error Generated palette data or error.
     */
    public function generate_from_image(string $image_path, array $options = []): array|WP_Error;

    /**
     * Gets available generation algorithms.
     *
     * @return array List of available algorithms.
     */
    public function get_available_algorithms(): array;

    /**
     * Gets default generation options.
     *
     * @return array Default options.
     */
    public function get_default_options(): array;
}
