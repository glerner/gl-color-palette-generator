<?php

namespace GL_Color_Palette_Generator\Interfaces;

/**
 * Color Palette Storage Interface
 *
 * Defines the contract for storing and retrieving color palettes.
 *
 * @package GL_Color_Palette_Generator
 * @since   1.0.0
 */
interface Color_Palette_Storage_Interface {
    /**
     * Save a palette to the database
     *
     * @param array  $colors Array of colors in the palette
     * @param string $prompt Optional. The prompt used to generate the palette
     * @param array  $metadata Optional. Additional metadata about the palette
     * @return int|false The ID of the saved palette or false on failure
     */
    public function save_palette(array $colors, string $prompt = '', array $metadata = []): int|false;

    /**
     * Get a palette by its ID
     *
     * @param int $id Palette ID
     * @return array|null Palette data or null if not found
     */
    public function get_palette(int $id): ?array;

    /**
     * Get palettes created within a date range
     *
     * @param string $start_date Start date in Y-m-d format
     * @param string $end_date End date in Y-m-d format
     * @return array Array of palettes
     */
    public function get_palettes_by_date_range(string $start_date, string $end_date): array;

    /**
     * Get recent palettes
     *
     * @param int $limit Number of palettes to return
     * @return array Array of palettes
     */
    public function get_recent_palettes(int $limit = 10): array;

    /**
     * Delete a palette
     *
     * @param int $id Palette ID
     * @return bool True on success, false on failure
     */
    public function delete_palette(int $id): bool;
}
