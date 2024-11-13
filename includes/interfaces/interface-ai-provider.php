<?php
/**
 * AI Provider Interface
 *
 * @package GLColorPalette
 * @subpackage Interfaces
 * @since 1.0.0
 */

namespace GLColorPalette\Interfaces;

/**
 * Interface AI_Provider
 *
 * Defines the contract for AI service providers that generate color palettes.
 */
interface AI_Provider {
    /**
     * Generate a color palette based on input parameters.
     *
     * @since 1.0.0
     * @param array $params {
     *     Parameters for palette generation.
     *     @type string $base_color    Base color in hex format (e.g., '#FF0000').
     *     @type string $mode          Palette mode ('analogous', 'complementary', etc.).
     *     @type int    $count         Number of colors to generate (2-10).
     * }
     * @return array|WP_Error Array of hex colors or WP_Error on failure.
     */
    public function generate_palette(array $params);

    /**
     * Validate API credentials.
     *
     * @since 1.0.0
     * @return bool|WP_Error True if valid, WP_Error on failure.
     */
    public function validate_credentials();

    /**
     * Get provider-specific configuration requirements.
     *
     * @since 1.0.0
     * @return array Array of required configuration keys.
     */
    public function get_requirements(): array;
} 
