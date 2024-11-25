<?php
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

/**
 * Class Color_Palette_Generator
 */
class Color_Palette_Generator {
    /**
     * Settings manager instance
     *
     * @var Settings_Manager
     */
    private $settings;

    /**
     * AI provider instance
     *
     * @var AI_Provider_Interface
     */
    private $ai_provider;

    /**
     * Constructor
     */
    public function __construct() {
        $this->settings = new Settings_Manager();
        $this->init_ai_provider();
    }

    /**
     * Initialize AI provider
     */
    private function init_ai_provider() {
        $provider_name = $this->settings->get_setting('ai_provider', 'openai');
        $api_key = $this->settings->get_setting('api_key');

        $factory = new AI_Provider_Factory();
        $this->ai_provider = $factory->create_provider($provider_name, $api_key);
    }

    /**
     * Generate color palette from prompt
     *
     * @param string $prompt User prompt for palette generation.
     * @return array Array of hex color codes.
     * @throws \Exception If palette generation fails.
     */
    public function generate_from_prompt($prompt) {
        // Check cache first
        $cached_palette = $this->get_cached_palette($prompt);
        if ($cached_palette !== false) {
            return $cached_palette;
        }

        // Generate palette using AI
        $colors = $this->generate_colors($prompt);

        // Validate and process colors
        $palette = $this->process_colors($colors);

        // Cache the result
        $this->cache_palette($prompt, $palette);

        return $palette;
    }

    /**
     * Generate colors using AI provider
     *
     * @param string $prompt User prompt.
     * @return array Raw color data from AI.
     * @throws \Exception If AI generation fails.
     */
    private function generate_colors($prompt) {
        $system_prompt = $this->get_system_prompt();
        $formatted_prompt = $this->format_user_prompt($prompt);

        try {
            $response = $this->ai_provider->generate_response($system_prompt, $formatted_prompt);
            return $this->parse_ai_response($response);
        } catch (\Exception $e) {
            throw new \Exception(
                sprintf(
                    __('Failed to generate palette: %s', 'gl-color-palette-generator'),
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Get system prompt for AI
     *
     * @return string
     */
    private function get_system_prompt() {
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
    private function format_user_prompt($prompt) {
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
    private function parse_ai_response($response) {
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
    private function process_colors($colors) {
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
     * @return array|false Cached palette or false if not found.
     */
    private function get_cached_palette($prompt) {
        $cache_key = 'gl_cpg_' . md5($prompt);
        $cached = wp_cache_get($cache_key, 'gl-color-palette-generator');

        if ($cached !== false) {
            return json_decode($cached, true);
        }

        return false;
    }

    /**
     * Cache generated palette
     *
     * @param string $prompt User prompt.
     * @param array  $palette Generated palette.
     */
    private function cache_palette($prompt, $palette) {
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
