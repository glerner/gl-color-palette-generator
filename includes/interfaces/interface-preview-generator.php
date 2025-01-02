<?php
/**
 * Preview Generator Interface
 *
 * Defines the contract for generating color palette previews.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Interfaces;

/**
 * Interface PreviewGenerator
 *
 * Provides methods for generating visual previews of color palettes.
 */
interface PreviewGenerator {
    /**
     * Generate a preview of a color palette
     *
     * @param array $palette {
     *     Color palette data
     *     @type array  $colors     List of hex color codes
     *     @type array  $metadata   Optional palette metadata
     *     @type string $format     Optional preview format override
     * }
     * @return string Generated preview (HTML, SVG, or image data URI)
     * @throws \InvalidArgumentException If palette data is invalid
     * @throws \RuntimeException If preview generation fails
     */
    public function generate_preview(array $palette): string;

    /**
     * Get list of supported preview formats
     *
     * @return array {
     *     Available preview formats
     *     @type string[] $formats     List of format identifiers
     *     @type array    $capabilities Format-specific capabilities
     *     @type array    $constraints  Format-specific constraints
     * }
     */
    public function get_preview_formats(): array;

    /**
     * Customize preview generation options
     *
     * @param array $options {
     *     Preview customization options
     *     @type string $format        Output format ('html', 'svg', 'png', etc.)
     *     @type string $layout        Layout style ('grid', 'list', 'circular')
     *     @type array  $dimensions    Preview dimensions (width, height)
     *     @type array  $typography    Typography settings (font, size, etc.)
     *     @type bool   $show_labels   Whether to show color labels
     *     @type bool   $show_values   Whether to show color values
     *     @type array  $custom_css    Custom CSS rules to apply
     * }
     * @throws \InvalidArgumentException If options are invalid
     */
    public function customize_preview(array $options): void;

    /**
     * Generate a preview with mock content
     *
     * @param array $palette Color palette to use
     * @param string $content_type Type of mock content ('website', 'button', 'card')
     * @return string Generated preview with mock content
     * @throws \InvalidArgumentException If content type is unsupported
     */
    public function generate_mock_preview(array $palette, string $content_type): string;

    /**
     * Get preview as specific format
     *
     * @param array  $palette Color palette data
     * @param string $format  Desired format ('png', 'jpg', 'svg', etc.)
     * @param array  $options Format-specific options
     * @return string Generated preview in specified format
     * @throws \InvalidArgumentException If format is unsupported
     */
    public function get_preview_as(array $palette, string $format, array $options = []): string;
}
