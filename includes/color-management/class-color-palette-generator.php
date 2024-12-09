<?php
declare(strict_types=1);

/**
 * Color Palette Generator Class
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\Color_Management;

use GL_Color_Palette_Generator\Settings\Settings_Manager;
use GL_Color_Palette_Generator\AI\AI_Provider_Factory;
use GL_Color_Palette_Generator\AI\AI_Provider_Interface;
use GL_Color_Palette_Generator\Types\Color_Types;
use GL_Color_Palette_Generator\Exceptions\PaletteGenerationException;

/**
 * Class Color_Palette_Generator
 */
class Color_Palette_Generator {
    /**
     * Settings manager instance
     *
     * @var Settings_Manager
     */
    private Settings_Manager $settings;

    /**
     * AI provider instance
     *
     * @var AI_Provider_Interface
     */
    private AI_Provider_Interface $ai_provider;

    /**
     * Constructor
     *
     * @throws PaletteGenerationException If AI provider initialization fails
     */
    public function __construct() {
        $this->settings = new Settings_Manager();
        $this->init_ai_provider();
    }

    /**
     * Initialize AI provider
     *
     * @throws PaletteGenerationException If provider initialization fails
     * @return void
     */
    private function init_ai_provider(): void {
        try {
            $provider_name = $this->settings->get_setting('ai_provider', 'openai');
            $api_key = $this->settings->get_setting('api_key');

            if (!is_string($provider_name) || !is_string($api_key)) {
                throw new \InvalidArgumentException('Invalid provider settings');
            }

            $factory = new AI_Provider_Factory();
            $this->ai_provider = $factory->create_provider($provider_name, $api_key);
        } catch (\Exception $e) {
            throw new PaletteGenerationException(
                sprintf(
                    __('Failed to initialize AI provider: %s', 'gl-color-palette-generator'),
                    $e->getMessage()
                ),
                0,
                $e
            );
        }
    }

    /**
     * Generate color palette from prompt
     *
     * @param string $prompt User prompt for palette generation.
     * @return array{colors: array<string>, metadata: array} Generated palette data
     * @throws PaletteGenerationException If palette generation fails
     */
    public function generate_from_prompt(string $prompt): array {
        if (empty(trim($prompt))) {
            throw new \InvalidArgumentException(
                __('Prompt cannot be empty', 'gl-color-palette-generator')
            );
        }

        // Check cache first
        $cached_palette = $this->get_cached_palette($prompt);
        if ($cached_palette !== null) {
            return $cached_palette;
        }

        try {
            // Generate palette using AI
            $colors = $this->generate_colors($prompt);

            // Validate and process colors
            $palette = $this->process_colors($colors);

            // Cache the result
            $this->cache_palette($prompt, $palette);

            return $palette;
        } catch (\Exception $e) {
            throw new PaletteGenerationException(
                sprintf(
                    __('Failed to generate palette: %s', 'gl-color-palette-generator'),
                    $e->getMessage()
                ),
                0,
                $e
            );
        }
    }

    /**
     * Generate colors using AI provider
     *
     * @param string $prompt User prompt.
     * @return array Raw color data from AI.
     * @throws \Exception If AI generation fails.
     */
    private function generate_colors(string $prompt): array {
        $system_prompt = $this->get_system_prompt();
        $formatted_prompt = $this->format_user_prompt($prompt);

        try {
            $response = $this->ai_provider->generate_response($system_prompt, $formatted_prompt);
            return $this->parse_ai_response($response);
        } catch (\Exception $e) {
            throw new PaletteGenerationException(
                sprintf(
                    __('Failed to generate colors: %s', 'gl-color-palette-generator'),
                    $e->getMessage()
                ),
                0,
                $e
            );
        }
    }

    /**
     * Get system prompt for AI
     *
     * @return string
     */
    private function get_system_prompt(): string {
        return <<<EOT
You are a color palette generation assistant. Generate harmonious color palettes based on user prompts.
Return exactly 5 colors in hex format (#RRGGBB), one per line.
Consider color theory principles like complementary colors, analogous colors, and color psychology.
Ensure sufficient contrast between colors for accessibility.
EOT;
    }

    /**
     * Format user prompt for AI
     *
     * @param string $prompt Raw user prompt.
     * @return string
     */
    private function format_user_prompt(string $prompt): string {
        return sprintf(
            "Generate a color palette for: %s\nProvide exactly 5 colors in hex format (#RRGGBB), one per line.",
            $prompt
        );
    }

    /**
     * Parse AI response into color array
     *
     * @param string $response AI response text.
     * @return array
     * @throws \Exception If response format is invalid.
     */
    private function parse_ai_response(string $response): array {
        $colors = array_filter(
            array_map('trim', explode("\n", $response)),
            function($line) {
                return !empty($line) && preg_match('/^#[0-9A-Fa-f]{6}$/', $line);
            }
        );

        if (count($colors) !== 5) {
            throw new \Exception(__('Invalid AI response format', 'gl-color-palette-generator'));
        }

        return $colors;
    }

    /**
     * Process and validate colors
     *
     * @param array $colors Raw color array.
     * @return array Processed color array.
     * @throws \Exception If color validation fails.
     */
    private function process_colors(array $colors): array {
        $processed = array_map(
            function($color) {
                // Ensure lowercase hex format
                $color = strtolower($color);

                // Validate hex format
                if (!preg_match('/^#[0-9a-f]{6}$/', $color)) {
                    throw new \Exception(
                        sprintf(
                            __('Invalid color format: %s', 'gl-color-palette-generator'),
                            $color
                        )
                    );
                }

                return $color;
            },
            $colors
        );

        // Check color distinctiveness
        $color_util = new Color_Utility();
        if (!$color_util->are_colors_distinct($processed)) {
            throw new \Exception(
                __('Generated colors are not visually distinct enough', 'gl-color-palette-generator')
            );
        }

        return $processed;
    }

    /**
     * Get cached palette
     *
     * @param string $prompt User prompt.
     * @return array|null Cached palette or null if not found.
     */
    private function get_cached_palette(string $prompt): ?array {
        $cache_key = 'gl_cpg_' . md5($prompt);
        $cached = wp_cache_get($cache_key, 'gl-color-palette-generator');

        if ($cached !== false) {
            return json_decode($cached, true);
        }

        return null;
    }

    /**
     * Cache generated palette
     *
     * @param string $prompt User prompt.
     * @param array  $palette Generated palette.
     */
    private function cache_palette(string $prompt, array $palette): void {
        $cache_key = 'gl_cpg_' . md5($prompt);
        $cache_duration = $this->settings->get_setting('cache_duration', 3600);

        wp_cache_set(
            $cache_key,
            wp_json_encode($palette),
            'gl-color-palette-generator',
            $cache_duration
        );
    }
}
