<?php

namespace GLColorPalette\Interfaces;

/**
 * Color Palette Converter Interface
 *
 * Defines the contract for converting color palettes between different formats and spaces.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface Color_Palette_Converter_Interface {
    /**
     * Converts between color spaces.
     *
     * @param array $colors Colors to convert.
     * @param string $from Source color space.
     * @param string $to Target color space.
     * @param array $options {
     *     Optional. Conversion options.
     *     @type array  $precision     Decimal precision.
     *     @type array  $gamut         Gamut mapping.
     *     @type array  $profiles      Color profiles.
     *     @type array  $metadata      Conversion metadata.
     * }
     * @return array {
     *     Conversion results.
     *     @type array  $converted     Converted colors.
     *     @type array  $original      Original colors.
     *     @type array  $mapping       Conversion mapping.
     *     @type array  $metadata      Conversion metadata.
     * }
     */
    public function convert_color_space(array $colors, string $from, string $to, array $options = []): array;

    /**
     * Converts between formats.
     *
     * @param array $palette Palette to convert.
     * @param string $format Target format.
     * @param array $options {
     *     Optional. Format options.
     *     @type array  $structure     Output structure.
     *     @type array  $formatting    Value formatting.
     *     @type array  $validation    Format validation.
     *     @type array  $metadata      Format metadata.
     * }
     * @return array {
     *     Format results.
     *     @type mixed  $converted     Converted palette.
     *     @type array  $original      Original palette.
     *     @type array  $format        Format details.
     *     @type array  $metadata      Conversion metadata.
     * }
     */
    public function convert_format(array $palette, string $format, array $options = []): array;

    /**
     * Converts between standards.
     *
     * @param array $palette Palette to convert.
     * @param string $standard Target standard.
     * @param array $options {
     *     Optional. Standard options.
     *     @type array  $compliance    Compliance rules.
     *     @type array  $mapping       Standard mapping.
     *     @type array  $validation    Standard validation.
     *     @type array  $metadata      Standard metadata.
     * }
     * @return array {
     *     Standard results.
     *     @type array  $converted     Converted palette.
     *     @type array  $compliance    Compliance results.
     *     @type array  $mapping       Standard mapping.
     *     @type array  $metadata      Conversion metadata.
     * }
     */
    public function convert_standard(array $palette, string $standard, array $options = []): array;

    /**
     * Converts between systems.
     *
     * @param array $palette Palette to convert.
     * @param string $system Target system.
     * @param array $options {
     *     Optional. System options.
     *     @type array  $mapping       System mapping.
     *     @type array  $compatibility System compatibility.
     *     @type array  $validation    System validation.
     *     @type array  $metadata      System metadata.
     * }
     * @return array {
     *     System results.
     *     @type array  $converted     Converted palette.
     *     @type array  $compatibility Compatibility info.
     *     @type array  $mapping       System mapping.
     *     @type array  $metadata      Conversion metadata.
     * }
     */
    public function convert_system(array $palette, string $system, array $options = []): array;
}
