<?php

namespace GLColorPalette\Interfaces;

/**
 * Color Palette Importer Interface
 *
 * Defines the contract for importing color palettes from various sources.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface ColorPaletteImporter {
    /**
     * Imports palette from file.
     *
     * @param string $file_path Path to import file.
     * @param array $options {
     *     Optional. Import options.
     *     @type string $format        File format.
     *     @type array  $validation    Import validation.
     *     @type array  $transform     Data transformations.
     *     @type array  $mapping       Field mapping.
     *     @type array  $metadata      Import metadata.
     * }
     * @return array {
     *     Import results.
     *     @type array  $palette       Imported palette.
     *     @type array  $validation    Validation results.
     *     @type array  $transformations Applied transforms.
     *     @type array  $metadata      Import metadata.
     * }
     */
    public function import_from_file(string $file_path, array $options = []): array;

    /**
     * Imports palette from URL.
     *
     * @param string $url Source URL.
     * @param array $options {
     *     Optional. URL options.
     *     @type array  $headers       Request headers.
     *     @type array  $auth          Authentication.
     *     @type array  $validation    Import validation.
     *     @type array  $transform     Data transformations.
     * }
     * @return array {
     *     URL import results.
     *     @type array  $palette       Imported palette.
     *     @type array  $source        Source information.
     *     @type array  $validation    Validation results.
     *     @type array  $metadata      Import metadata.
     * }
     */
    public function import_from_url(string $url, array $options = []): array;

    /**
     * Imports palette from design tool.
     *
     * @param string $tool_file Tool file path.
     * @param string $tool Tool identifier.
     * @param array $options {
     *     Optional. Tool options.
     *     @type string $version       Tool version.
     *     @type array  $extraction    Color extraction.
     *     @type array  $validation    Import validation.
     *     @type array  $mapping       Asset mapping.
     * }
     * @return array {
     *     Tool import results.
     *     @type array  $palette       Imported palette.
     *     @type array  $assets        Imported assets.
     *     @type array  $validation    Validation results.
     *     @type array  $metadata      Import metadata.
     * }
     */
    public function import_from_tool(string $tool_file, string $tool, array $options = []): array;

    /**
     * Validates import data.
     *
     * @param array $import_data Data to validate.
     * @param array $rules {
     *     Optional. Validation rules.
     *     @type array  $schema        Data schema.
     *     @type array  $constraints   Data constraints.
     *     @type array  $formats       Format validation.
     *     @type array  $requirements  Data requirements.
     * }
     * @return array {
     *     Validation results.
     *     @type bool   $valid         Validation status.
     *     @type array  $errors        Validation errors.
     *     @type array  $warnings      Validation warnings.
     *     @type array  $metadata      Validation metadata.
     * }
     */
    public function validate_import(array $import_data, array $rules = []): array;
} 
