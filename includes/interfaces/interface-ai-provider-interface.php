<?php
/**
 * AI Provider Interface
 *
 * Defines the contract for AI service providers that generate color palettes.
 * This interface ensures consistent implementation across different AI providers.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Interfaces;

/**
 * Interface AI_Provider_Interface
 *
 * Defines the contract for AI service providers that generate color palettes.
 * Includes methods for palette generation, provider information, and capabilities.
 *
 * @since 1.0.0
 */
interface AI_Provider_Interface {
    /**
     * Generate a color palette based on input parameters.
     *
     * @param string $prompt     Text prompt describing desired palette
     * @param int    $num_colors Number of colors to generate
     * @param array  $options    Provider-specific options
     * @return array Generated color palette
     */
    public function generate_colors(string $prompt, int $num_colors, array $options = []): array;

    /**
     * Get the provider's name.
     *
     * @return string Provider name
     */
    public function get_name(): string;

    /**
     * Get the provider's display name.
     *
     * @return string Provider display name
     */
    public function get_display_name(): string;

    /**
     * Get the provider's capabilities.
     *
     * @return array Provider capabilities
     */
    public function get_capabilities(): array;
}
