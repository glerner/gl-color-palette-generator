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
use GL_Color_Palette_Generator\Interfaces\Color_Scheme_Generator_Interface;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;

/**
 * Class Color_Palette_Generator
 */
class Color_Palette_Generator implements Color_Scheme_Generator_Interface {
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
Return colors for the following roles:
- {Color_Constants::COLOR_ROLE_PRIMARY}: Main brand color
- {Color_Constants::COLOR_ROLE_SECONDARY}: Supporting brand color
- {Color_Constants::COLOR_ROLE_ACCENT}: Highlight or call-to-action color
- {Color_Constants::COLOR_ROLE_BACKGROUND}: Page background color
- {Color_Constants::COLOR_ROLE_TEXT}: Main text color

Ensure colors meet these requirements:
- Minimum contrast ratio between text and background: {Color_Constants::WCAG_CONTRAST_TARGET}
- Maximum contrast ratio to prevent eye strain: {Color_Constants::CONTRAST_MAX}
- Saturation range: {Color_Constants::MIN_SATURATION_RANGE} to {Color_Constants::MAX_SATURATION}

Consider color theory principles like complementary colors, analogous colors, and color psychology.
Return colors in hex format (#RRGGBB), one per line with role prefix.
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
            "Generate a color palette for: %s\nProvide colors for primary, secondary, accent, background, and text roles in hex format (#RRGGBB), one per line with role prefix.",
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
        $required_roles = [
            Color_Constants::COLOR_ROLE_PRIMARY,
            Color_Constants::COLOR_ROLE_SECONDARY,
            Color_Constants::COLOR_ROLE_ACCENT,
            Color_Constants::COLOR_ROLE_BACKGROUND,
            Color_Constants::COLOR_ROLE_TEXT
        ];

        $colors = [];
        $lines = array_filter(array_map('trim', explode("\n", $response)));

        foreach ($lines as $line) {
            if (preg_match('/^([a-z_]+):\s*(#[0-9A-Fa-f]{6})$/i', $line, $matches)) {
                $role = strtolower($matches[1]);
                $color = $matches[2];
                if (in_array($role, $required_roles)) {
                    $colors[$role] = $color;
                }
            }
        }

        if (count($colors) !== count($required_roles)) {
            throw new \Exception(__('Missing required color roles in AI response', 'gl-color-palette-generator'));
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

    /**
     * Generate a color scheme from a base color
     *
     * @param string $base_color Base color in hex format
     * @param array  $options Generation options
     * @return array|WP_Error Array of colors or error
     */
    public function generate_scheme($base_color, $options = []) {
        try {
            $scheme_type = $options['type'] ?? 'analogous';
            $count = $options['count'] ?? 5;

            switch ($scheme_type) {
                case 'monochromatic':
                    return $this->generate_monochromatic($base_color, $count);
                case 'analogous':
                    return $this->generate_analogous($base_color, $count);
                case 'complementary':
                    return $this->generate_complementary($base_color, $count);
                case 'split_complementary':
                    return $this->generate_split_complementary($base_color, $count);
                case 'triadic':
                    return $this->generate_triadic($base_color, $count);
                case 'tetradic':
                    return $this->generate_tetradic($base_color, $count);
                default:
                    return new \WP_Error(
                        'invalid_scheme_type',
                        __('Invalid scheme type specified', 'gl-color-palette-generator')
                    );
            }
        } catch (\Exception $e) {
            return new \WP_Error(
                'scheme_generation_failed',
                $e->getMessage()
            );
        }
    }

    /**
     * Generate a monochromatic scheme
     *
     * @param string $base_color Base color in hex format
     * @param int    $count Number of colors to generate
     * @return array|WP_Error Array of colors or error
     */
    public function generate_monochromatic($base_color, $count = 5) {
        try {
            $color_util = new Color_Utility();
            return $color_util->generate_monochromatic($base_color, $count);
        } catch (\Exception $e) {
            return new \WP_Error(
                'monochromatic_generation_failed',
                $e->getMessage()
            );
        }
    }

    /**
     * Generate an analogous scheme
     *
     * @param string $base_color Base color in hex format
     * @param int    $count Number of colors to generate
     * @return array|WP_Error Array of colors or error
     */
    public function generate_analogous($base_color, $count = 5) {
        try {
            $color_util = new Color_Utility();
            return $color_util->generate_analogous($base_color, $count);
        } catch (\Exception $e) {
            return new \WP_Error(
                'analogous_generation_failed',
                $e->getMessage()
            );
        }
    }

    /**
     * Generate a complementary scheme
     *
     * @param string $base_color Base color in hex format
     * @param int    $count Number of colors to generate
     * @return array|WP_Error Array of colors or error
     */
    public function generate_complementary($base_color, $count = 4) {
        try {
            $color_util = new Color_Utility();
            return $color_util->generate_complementary($base_color, $count);
        } catch (\Exception $e) {
            return new \WP_Error(
                'complementary_generation_failed',
                $e->getMessage()
            );
        }
    }

    /**
     * Generate a split complementary scheme
     *
     * @param string $base_color Base color in hex format
     * @param int    $count Number of colors to generate
     * @return array|WP_Error Array of colors or error
     */
    public function generate_split_complementary($base_color, $count = 3) {
        try {
            $color_util = new Color_Utility();
            return $color_util->generate_split_complementary($base_color, $count);
        } catch (\Exception $e) {
            return new \WP_Error(
                'split_complementary_generation_failed',
                $e->getMessage()
            );
        }
    }

    /**
     * Generate a triadic scheme
     *
     * @param string $base_color Base color in hex format
     * @param int    $count Number of colors to generate
     * @return array|WP_Error Array of colors or error
     */
    public function generate_triadic($base_color, $count = 3) {
        try {
            $color_util = new Color_Utility();
            return $color_util->generate_triadic($base_color, $count);
        } catch (\Exception $e) {
            return new \WP_Error(
                'triadic_generation_failed',
                $e->getMessage()
            );
        }
    }

    /**
     * Generate a tetradic scheme
     *
     * @param string $base_color Base color in hex format
     * @param int    $count Number of colors to generate
     * @return array|WP_Error Array of colors or error
     */
    public function generate_tetradic($base_color, $count = 4) {
        try {
            $color_util = new Color_Utility();
            return $color_util->generate_tetradic($base_color, $count);
        } catch (\Exception $e) {
            return new \WP_Error(
                'tetradic_generation_failed',
                $e->getMessage()
            );
        }
    }

    /**
     * Generate a custom scheme based on color theory rules
     *
     * @param string $base_color Base color in hex format
     * @param array  $rules Color theory rules to apply
     * @return array|WP_Error Array of colors or error
     */
    public function generate_custom_scheme($base_color, $rules) {
        try {
            $color_util = new Color_Utility();
            return $color_util->generate_custom_scheme($base_color, $rules);
        } catch (\Exception $e) {
            return new \WP_Error(
                'custom_scheme_generation_failed',
                $e->getMessage()
            );
        }
    }

    /**
     * Generate a scheme from an image
     *
     * @param string $image_path Path to image file
     * @param array  $options Extraction options
     * @return array|WP_Error Array of colors or error
     */
    public function generate_from_image($image_path, $options = []) {
        try {
            $color_util = new Color_Utility();
            return $color_util->extract_colors_from_image($image_path, $options);
        } catch (\Exception $e) {
            return new \WP_Error(
                'image_extraction_failed',
                $e->getMessage()
            );
        }
    }

    /**
     * Generate a scheme based on a theme or mood
     *
     * @param string $theme Theme or mood name
     * @param array  $options Generation options
     * @return array|WP_Error Array of colors or error
     */
    public function generate_themed_scheme($theme, $options = []) {
        try {
            // Use AI to generate a themed color scheme
            $prompt = sprintf(
                'Generate a color palette for theme: %s. Consider %s style and mood.',
                $theme,
                $options['style'] ?? 'modern'
            );
            
            $colors = $this->generate_from_prompt($prompt);
            return $colors['colors'] ?? [];
        } catch (\Exception $e) {
            return new \WP_Error(
                'themed_scheme_generation_failed',
                $e->getMessage()
            );
        }
    }

    /**
     * Adjust scheme colors for better contrast
     *
     * @param array $colors Array of colors in hex format
     * @param array $options Adjustment options
     * @return array|WP_Error Adjusted colors or error
     */
    public function adjust_scheme_contrast($colors, $options = []) {
        try {
            $color_util = new Color_Utility();
            return $color_util->adjust_contrast($colors, $options);
        } catch (\Exception $e) {
            return new \WP_Error(
                'contrast_adjustment_failed',
                $e->getMessage()
            );
        }
    }

    /**
     * Get available color scheme types
     *
     * @return array List of available scheme types
     */
    public function get_available_schemes() {
        return [
            'monochromatic',
            'analogous',
            'complementary',
            'split_complementary',
            'triadic',
            'tetradic',
            'custom',
            'themed',
            'from_image'
        ];
    }

    /**
     * Get color theory rules for scheme generation
     *
     * @return array List of available color theory rules
     */
    public function get_color_theory_rules() {
        return [
            'harmony' => [
                'complementary',
                'analogous',
                'triadic',
                'tetradic',
                'split_complementary',
                'monochromatic'
            ],
            'contrast' => [
                'high',
                'medium',
                'low'
            ],
            'saturation' => [
                'vibrant',
                'muted',
                'pastel'
            ],
            'brightness' => [
                'light',
                'medium',
                'dark'
            ]
        ];
    }

    /**
     * Validate a generated scheme
     *
     * @param array $colors Array of colors in hex format
     * @param array $rules Validation rules
     * @return bool|WP_Error True if valid, error if not
     */
    public function validate_scheme($colors, $rules = []) {
        try {
            $color_util = new Color_Utility();
            
            // Basic color format validation
            foreach ($colors as $color) {
                if (!preg_match('/^#[0-9a-f]{6}$/i', $color)) {
                    return new \WP_Error(
                        'invalid_color_format',
                        sprintf(__('Invalid color format: %s', 'gl-color-palette-generator'), $color)
                    );
                }
            }

            // Check color distinctiveness if required
            if (!isset($rules['skip_distinctiveness']) || !$rules['skip_distinctiveness']) {
                if (!$color_util->are_colors_distinct($colors)) {
                    return new \WP_Error(
                        'colors_not_distinct',
                        __('Colors are not visually distinct enough', 'gl-color-palette-generator')
                    );
                }
            }

            // Check contrast if required
            if (isset($rules['min_contrast']) && is_numeric($rules['min_contrast'])) {
                if (!$color_util->check_contrast($colors, $rules['min_contrast'])) {
                    return new \WP_Error(
                        'insufficient_contrast',
                        __('Colors do not meet minimum contrast requirements', 'gl-color-palette-generator')
                    );
                }
            }

            return true;
        } catch (\Exception $e) {
            return new \WP_Error(
                'validation_failed',
                $e->getMessage()
            );
        }
    }
}
