<?php

namespace GL_Color_Palette_Generator\Color_Management\Interfaces;

use GL_Color_Palette_Generator\Models\Color_Palette;

/**
 * Color Palette Renderer Interface
 *
 * Defines the contract for rendering color palettes in various formats.
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface ColorPaletteRendererInterface {
    /**
     * Renders a color palette.
     *
     * @param Color_Palette $palette Palette to render.
     * @param string       $format  Output format (html, svg, etc.).
     * @param array        $options Rendering options.
     * @return string Rendered output.
     */
    public function render_palette(Color_Palette $palette, string $format = 'html', array $options = []): string;

    /**
     * Renders a color swatch.
     *
     * @param string $color   Color to render.
     * @param string $format  Output format.
     * @param array  $options Rendering options.
     * @return string Rendered swatch.
     */
    public function render_swatch(string $color, string $format = 'html', array $options = []): string;

    /**
     * Renders color information.
     *
     * @param string $color   Color to render info for.
     * @param array  $options Rendering options.
     * @return string Rendered color information.
     */
    public function render_color_info(string $color, array $options = []): string;

    /**
     * Gets supported render formats.
     *
     * @return array List of supported formats.
     */
    public function get_supported_formats(): array;

    /**
     * Gets format-specific render options.
     *
     * @param string $format Format to get options for.
     * @return array Format options.
     */
    public function get_format_options(string $format): array;

    /**
     * Validates rendering options.
     *
     * @param array  $options Options to validate.
     * @param string $format  Format to validate against.
     * @return bool True if valid.
     */
    public function validate_options(array $options, string $format): bool;
}
