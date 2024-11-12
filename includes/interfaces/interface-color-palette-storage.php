<?php

namespace GLColorPalette\Interfaces;

use GLColorPalette\ColorPalette;

/**
 * Color Palette Storage Interface
 *
 * Defines the contract for storing and retrieving color palettes.
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface ColorPaletteStorageInterface {
    /**
     * Saves a color palette.
     *
     * @param ColorPalette $palette Palette to save.
     * @return int|false Palette ID on success, false on failure.
     */
    public function savePalette(ColorPalette $palette): int|false;

    /**
     * Retrieves a color palette by ID.
     *
     * @param int $id Palette ID.
     * @return ColorPalette|null Retrieved palette or null if not found.
     */
    public function getPalette(int $id): ?ColorPalette;

    /**
     * Updates an existing color palette.
     *
     * @param int          $id      Palette ID.
     * @param ColorPalette $palette Updated palette data.
     * @return bool True on success, false on failure.
     */
    public function updatePalette(int $id, ColorPalette $palette): bool;

    /**
     * Deletes a color palette.
     *
     * @param int $id Palette ID.
     * @return bool True on success, false on failure.
     */
    public function deletePalette(int $id): bool;

    /**
     * Lists all color palettes with optional filtering.
     *
     * @param array $filters Optional. Filter criteria.
     * @return array List of palettes.
     */
    public function listPalettes(array $filters = []): array;

    /**
     * Searches for palettes matching criteria.
     *
     * @param array $criteria Search criteria.
     * @return array Matching palettes.
     */
    public function searchPalettes(array $criteria): array;
}
