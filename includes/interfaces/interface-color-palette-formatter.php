<?php

namespace GLColorPalette\Interfaces;

/**
 * Color Palette Formatter Interface
 *
 * Defines the contract for formatting color palettes and their values.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface ColorPaletteFormatter {
    /**
     * Formats color values.
     *
     * @param array $colors Colors to format.
     * @param string $format Target format ('hex', 'rgb', 'hsl', 'hsv').
     * @param array $options {
     *     Optional. Format options.
     *     @type bool   $uppercase     Uppercase hex.
     *     @type bool   $shorthand     Use shorthand.
     *     @type string $notation      Value notation.
     *     @type array  $precision     Decimal precision.
     * }
     * @return array {
     *     Format results.
     *     @type array  $formatted     Formatted colors.
     *     @type array  $original      Original values.
     *     @type array  $conversions   Conversion data.
     *     @type array  $metadata      Format metadata.
     * }
     */
    public function format_colors(array $colors, string $format, array $options = []): array;

    /**
     * Formats palette structure.
     *
     * @param array $palette Palette to format.
     * @param array $options {
     *     Optional. Structure options.
     *     @type string $structure     Data structure.
     *     @type array  $fields        Field options.
     *     @type array  $ordering      Field ordering.
     *     @type array  $grouping      Data grouping.
     * }
     * @return array {
     *     Structure results.
     *     @type array  $formatted     Formatted palette.
     *     @type array  $structure     Structure info.
     *     @type array  $mapping       Field mapping.
     *     @type array  $metadata      Format metadata.
     * }
     */
    public function format_structure(array $palette, array $options = []): array;

    /**
     * Formats palette metadata.
     *
     * @param array $metadata Metadata to format.
     * @param array $options {
     *     Optional. Metadata options.
     *     @type array  $fields        Field selection.
     *     @type array  $formatting    Value formatting.
     *     @type array  $validation    Field validation.
     *     @type array  $defaults      Default values.
     * }
     * @return array {
     *     Metadata results.
     *     @type array  $formatted     Formatted metadata.
     *     @type array  $validation    Validation results.
     *     @type array  $defaults      Applied defaults.
     *     @type array  $metadata      Format metadata.
     * }
     */
    public function format_metadata(array $metadata, array $options = []): array;

    /**
     * Formats palette output.
     *
     * @param array $palette Palette to format.
     * @param string $format Output format.
     * @param array $options {
     *     Optional. Output options.
     *     @type array  $structure     Output structure.
     *     @type array  $formatting    Value formatting.
     *     @type array  $filters       Output filters.
     *     @type array  $metadata      Output metadata.
     * }
     * @return array {
     *     Output results.
     *     @type mixed  $output        Formatted output.
     *     @type array  $format        Format info.
     *     @type array  $applied       Applied options.
     *     @type array  $metadata      Format metadata.
     * }
     */
    public function format_output(array $palette, string $format, array $options = []): array;
} 
