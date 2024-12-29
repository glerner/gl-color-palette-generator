<?php

namespace GL_Color_Palette_Generator\Interfaces;

use GL_Color_Palette_Generator\Models\Color_Palette;
use WP_Error;

/**
 * Color Palette Generator Interface
 *
 * Defines the contract for generating color palettes programmatically.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

/**
 * Interface Color_Palette_Generator_Interface
 *
 * Defines methods for generating color palettes.
 */
interface Color_Palette_Generator_Interface {
    /**
     * Generates a new color palette.
     *
     * @param array $options Generation options.
     * @return Color_Palette|WP_Error Generated palette or error.
     */
    public function generate_palette(array $options = []): Color_Palette|WP_Error;

    /**
     * Generates a complementary color palette.
     *
     * @param string $base_color Base color to build from.
     * @param array  $options    Generation options.
     * @return Color_Palette|WP_Error Generated palette or error.
     */
    public function generate_complementary(string $base_color, array $options = []): Color_Palette|WP_Error;

    /**
     * Generates an analogous color palette.
     *
     * @param string $base_color Base color to build from.
     * @param array  $options    Generation options.
     * @return Color_Palette|WP_Error Generated palette or error.
     */
    public function generate_analogous(string $base_color, array $options = []): Color_Palette|WP_Error;

    /**
     * Generates a triadic color palette.
     *
     * @param string $base_color Base color to build from.
     * @param array  $options    Generation options.
     * @return Color_Palette|WP_Error Generated palette or error.
     */
    public function generate_triadic(string $base_color, array $options = []): Color_Palette|WP_Error;

    /**
     * Generates a monochromatic color palette.
     *
     * @param string $base_color Base color to build from.
     * @param array  $options    Generation options.
     * @return Color_Palette|WP_Error Generated palette or error.
     */
    public function generate_monochromatic(string $base_color, array $options = []): Color_Palette|WP_Error;

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
