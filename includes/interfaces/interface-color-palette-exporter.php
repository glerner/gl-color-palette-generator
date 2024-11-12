<?php

namespace GLColorPalette\Interfaces;

/**
 * Color Palette Exporter Interface
 *
 * Defines the contract for exporting color palettes to various formats.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface ColorPaletteExporter {
    /**
     * Exports palette to file.
     *
     * @param array $palette Palette to export.
     * @param string $format Export format ('json', 'xml', 'yaml', 'csv').
     * @param array $options {
     *     Optional. Export options.
     *     @type string $path          Output path.
     *     @type array  $structure     Data structure.
     *     @type array  $formatting    Output formatting.
     *     @type array  $compression   File compression.
     *     @type array  $metadata      Export metadata.
     * }
     * @return array {
     *     Export results.
     *     @type string $file          Exported file path.
     *     @type array  $stats         Export statistics.
     *     @type array  $validation    Export validation.
     *     @type array  $metadata      Export metadata.
     * }
     */
    public function export_to_file(array $palette, string $format, array $options = []): array;

    /**
     * Exports palette to code.
     *
     * @param array $palette Palette to export.
     * @param string $language Target language ('css', 'scss', 'less', 'js').
     * @param array $options {
     *     Optional. Code options.
     *     @type string $format        Code format.
     *     @type array  $variables     Variable options.
     *     @type array  $formatting    Code formatting.
     *     @type array  $comments      Documentation.
     * }
     * @return array {
     *     Code export results.
     *     @type string $code          Generated code.
     *     @type array  $variables     Variable mapping.
     *     @type array  $documentation Code documentation.
     *     @type array  $metadata      Export metadata.
     * }
     */
    public function export_to_code(array $palette, string $language, array $options = []): array;

    /**
     * Exports palette to design tool.
     *
     * @param array $palette Palette to export.
     * @param string $tool Target tool ('sketch', 'figma', 'adobe').
     * @param array $options {
     *     Optional. Tool options.
     *     @type string $version       Tool version.
     *     @type array  $format        Tool format.
     *     @type array  $swatches      Swatch options.
     *     @type array  $metadata      Tool metadata.
     * }
     * @return array {
     *     Tool export results.
     *     @type string $file          Export file path.
     *     @type array  $swatches      Swatch data.
     *     @type array  $compatibility Tool compatibility.
     *     @type array  $metadata      Export metadata.
     * }
     */
    public function export_to_tool(array $palette, string $tool, array $options = []): array;

    /**
     * Validates export format.
     *
     * @param array $export Export data to validate.
     * @param array $rules {
     *     Optional. Validation rules.
     *     @type array  $format        Format requirements.
     *     @type array  $structure     Structure rules.
     *     @type array  $constraints   Export constraints.
     *     @type array  $compatibility Format compatibility.
     * }
     * @return array {
     *     Validation results.
     *     @type bool   $valid         Validation status.
     *     @type array  $errors        Validation errors.
     *     @type array  $warnings      Validation warnings.
     *     @type array  $metadata      Validation metadata.
     * }
     */
    public function validate_export(array $export, array $rules = []): array;
} 
