<?php

namespace GLColorPalette\Interfaces;

use GLColorPalette\ColorPalette;

/**
 * Color Palette Renderer Interface
 *
 * Defines the contract for rendering color palettes in various formats.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface ColorPaletteRendererInterface {
    /**
     * Renders a color palette.
     *
     * @param ColorPalette $palette Palette to render.
     * @param string       $format  Output format (html, svg, etc.).
     * @param array        $options Rendering options.
     * @return string Rendered output.
     */
    public function renderPalette(ColorPalette $palette, string $format = 'html', array $options = []): string;

    /**
     * Renders a color swatch.
     *
     * @param string $color   Color to render.
     * @param string $format  Output format.
     * @param array  $options Rendering options.
     * @return string Rendered swatch.
     */
    public function renderSwatch(string $color, string $format = 'html', array $options = []): string;

    /**
     * Renders color information.
     *
     * @param string $color   Color to render info for.
     * @param array  $options Rendering options.
     * @return string Rendered color information.
     */
    public function renderColorInfo(string $color, array $options = []): string;

    /**
     * Gets supported rendering formats.
     *
     * @return array List of supported formats.
     */
    public function getSupportedFormats(): array;

    /**
     * Gets format-specific options.
     *
     * @param string $format Format to get options for.
     * @return array Format options.
     */
    public function getFormatOptions(string $format): array;

    /**
     * Validates rendering options.
     *
     * @param array  $options Options to validate.
     * @param string $format  Format to validate against.
     * @return bool True if valid.
     */
    public function validateOptions(array $options, string $format): bool;
}
