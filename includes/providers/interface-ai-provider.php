<?php
/**
 * AI Provider Interface
 *
 * Defines the contract for AI providers that generate color palettes.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Providers
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Providers;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AI Provider interface
 */
interface AI_Provider {
    /**
     * Generate a color palette based on a prompt
     *
     * @param string $prompt      Text prompt describing desired palette
     * @param int    $num_colors  Number of colors to generate (2-10)
     * @param array  $options     Additional provider-specific options
     * @return array{colors: array<string>, metadata: array} Generated palette data
     * @throws \Exception If generation fails
     */
    public function generate_palette(string $prompt, int $num_colors = 5, array $options = []): array;

    /**
     * Get provider name
     *
     * @return string Provider identifier
     */
    public function get_name(): string;

    /**
     * Get provider display name
     *
     * @return string Provider display name
     */
    public function get_display_name(): string;

    /**
     * Check if provider is configured and ready
     *
     * @return bool True if ready, false otherwise
     */
    public function is_ready(): bool;

    /**
     * Get provider configuration requirements
     *
     * @return array Configuration field definitions
     */
    public function get_config_fields(): array;

    /**
     * Validate provider configuration
     *
     * @param array $config Configuration to validate
     * @return bool True if valid, false otherwise
     */
    public function validate_config(array $config): bool;
}
