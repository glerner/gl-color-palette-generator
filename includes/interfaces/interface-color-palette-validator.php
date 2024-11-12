<?php

namespace GLColorPalette\Interfaces;

use GLColorPalette\ColorPalette;

/**
 * Color Palette Validator Interface
 *
 * Defines the contract for validating color palettes and their properties.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface ColorPaletteValidatorInterface {
    /**
     * Validates a color palette.
     *
     * @param ColorPalette $palette Palette to validate.
     * @return bool True if valid.
     */
    public function validatePalette(ColorPalette $palette): bool;

    /**
     * Gets validation errors.
     *
     * @return array List of validation errors.
     */
    public function getErrors(): array;

    /**
     * Validates color format.
     *
     * @param string $color Color to validate.
     * @return bool True if valid.
     */
    public function validateColorFormat(string $color): bool;

    /**
     * Validates palette structure.
     *
     * @param array $data Palette data.
     * @return bool True if valid.
     */
    public function validateStructure(array $data): bool;

    /**
     * Validates palette metadata.
     *
     * @param array $metadata Metadata to validate.
     * @return bool True if valid.
     */
    public function validateMetadata(array $metadata): bool;

    /**
     * Gets validation rules.
     *
     * @return array List of validation rules.
     */
    public function getValidationRules(): array;
}
