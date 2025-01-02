<?php

namespace GL_Color_Palette_Generator\Interfaces;

use GL_Color_Palette_Generator\Models\Color_Palette;
use WP_Error;

/**
 * Color Palette Optimizer Interface
 *
 * Interface for optimizing color palettes.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 * @since 1.0.0
 */
interface Color_Palette_Optimizer_Interface {
    /**
     * Optimize a color palette
     *
     * @param Color_Palette $palette Palette to optimize
     * @param array        $options Optimization options
     * @return Color_Palette|WP_Error Optimized palette or error
     */
    public function optimize_palette(Color_Palette $palette, array $options = []): Color_Palette|WP_Error;

    /**
     * Get supported optimization options
     *
     * @return array List of supported options
     */
    public function get_supported_options(): array;
}
