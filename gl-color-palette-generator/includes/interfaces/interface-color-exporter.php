<?php

namespace GLColorPalette\Interfaces;

/**
 * Color Exporter Interface
 *
 * Defines the contract for exporting color palettes in various formats.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface ColorExporter {
    /**
     * Exports a color palette to various formats.
     *
     * @param array $palette Color palette data.
     * @param string $format Export format ('css', 'scss', 'json', 'theme.json', etc.).
     * @param array $options {
     *     Optional. Export options.
     *     @type string $variable_prefix    Prefix for CSS variables.
     *     @type bool   $include_metadata   Include palette metadata.
     *     @type string $naming_convention  Variable naming style ('kebab', 'camel', etc.).
     *     @type bool   $minify            Minify output.
     * }
     * @return array {
     *     Export results.
     *     @type string $content     Formatted export content.
     *     @type string $filename    Suggested filename.
     *     @type string $mime_type   Content MIME type.
     *     @type array  $metadata    Export metadata.
     * }
     * @throws \InvalidArgumentException If format is unsupported or palette is invalid.
     */
    public function export_palette(array $palette, string $format, array $options = []): array;

    /**
     * Gets list of supported export formats.
     *
     * @return array {
     *     Supported formats information.
     *     @type array $formats {
     *         Format details indexed by format ID.
     *         @type string $name        Human-readable format name.
     *         @type string $extension   File extension.
     *         @type string $mime_type   MIME type.
     *         @type array  $options     Supported options.
     *     }
     * }
     */
    public function get_supported_formats(): array;

    /**
     * Validates palette data for export.
     *
     * @param array $palette Palette to validate.
     * @param string $format Target export format.
     * @return array {
     *     Validation results.
     *     @type bool   $is_valid    Whether palette is valid for export.
     *     @type array  $errors      Validation errors if any.
     *     @type array  $warnings    Validation warnings if any.
     * }
     */
    public function validate_for_export(array $palette, string $format): array;

    /**
     * Generates export preview.
     *
     * @param array $palette Palette data.
     * @param string $format Export format.
     * @param array $options Export options.
     * @return string Preview of exported content.
     */
    public function generate_preview(array $palette, string $format, array $options = []): string;
} 
