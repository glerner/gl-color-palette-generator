<?php
declare(strict_types=1);

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

use GL_Color_Palette_Generator\Exceptions\PaletteGenerationException;

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
     * @param string $prompt     Text prompt describing desired palette
     * @param int    $num_colors Number of colors to generate (2-10)
     * @param array{
     *     model?: string,
     *     temperature?: float,
     *     max_tokens?: int,
     *     top_p?: float,
     *     frequency_penalty?: float,
     *     presence_penalty?: float
     * } $options Additional provider-specific options
     * @return array{
     *     colors: array<string>,
     *     metadata: array{
     *         theme: string,
     *         mood: string,
     *         description: string,
     *         provider: string,
     *         model?: string,
     *         timestamp: int
     *     }
     * } Generated palette data
     * @throws PaletteGenerationException If generation fails
     * @throws \InvalidArgumentException If input parameters are invalid
     */
    public function generate_palette(string $prompt, int $num_colors = 5, array $options = []): array;

    /**
     * Get provider name
     *
     * @return string Provider identifier (e.g., 'openai', 'anthropic')
     */
    public function get_name(): string;

    /**
     * Get provider display name
     *
     * @return string Provider display name (e.g., 'OpenAI', 'Anthropic')
     */
    public function get_display_name(): string;

    /**
     * Check if provider is configured and ready
     *
     * @return bool True if ready, false otherwise
     */
    public function is_ready(): bool;

    /**
     * Get provider capabilities
     *
     * @return array{
     *     max_colors: int,
     *     supports_streaming: bool,
     *     supports_batch: bool,
     *     supports_style_transfer: bool,
     *     max_prompt_length: int,
     *     rate_limit: array{
     *         requests_per_minute: int,
     *         tokens_per_minute: int
     *     }
     * }
     */
    public function get_capabilities(): array;

    /**
     * Validate provider options
     *
     * @param array $options Options to validate
     * @return bool True if valid, false otherwise
     */
    public function validate_options(array $options): bool;
}
