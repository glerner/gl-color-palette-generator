<?php
/**
 * Color Palette Generator
 *
 * @package GLColorPalette
 * @since 1.0.0
 */

namespace GLColorPalette;

use GLColorPalette\Providers\AI_Provider_Factory;

/**
 * Class Color_Palette_Generator
 *
 * Generates color palettes using various AI providers.
 * Handles the communication with different AI services and validates
 * both input parameters and generated palettes.
 *
 * @since 1.0.0
 *
 * @property AI_Provider_Factory $provider_factory Factory for creating AI providers
 * @property Color_Palette_Validator $validator Validator for palettes and parameters
 */
class Color_Palette_Generator {
    /**
     * AI Provider Factory instance
     * @var AI_Provider_Factory
     */
    private $provider_factory;

    /**
     * Validator instance
     * @var Color_Palette_Validator
     */
    private $validator;

    /**
     * Constructor
     */
    public function __construct() {
        $this->provider_factory = new AI_Provider_Factory();
        $this->validator = new Color_Palette_Validator();
    }

    /**
     * Generate a color palette
     *
     * @param string $theme Theme description for the palette
     * @param array  $options {
     *     Optional. Array of generation options.
     *
     *     @type int    $count           Number of colors to generate. Default 5.
     *     @type string $provider        AI provider to use. Default 'openai'.
     *     @type array  $provider_options Provider-specific options.
     * }
     * @return Color_Palette|WP_Error Generated palette or error on failure
     */
    public function generate(string $theme, array $options = []) {
        $default_options = [
            'count' => 5,
            'provider' => 'openai',
            'provider_options' => []
        ];

        $options = wp_parse_args($options, $default_options);

        // Validate parameters
        $validation = $this->validator->validate_generation_params([
            'theme' => $theme,
            'count' => $options['count']
        ]);

        if (is_wp_error($validation)) {
            return $validation;
        }

        try {
            // Get the AI provider
            $provider = $this->provider_factory->create_provider(
                $options['provider'],
                $options['provider_options']
            );

            // Generate colors using the provider
            $colors = $provider->generate_palette([
                'theme' => $theme,
                'count' => $options['count']
            ]);

            if (is_wp_error($colors)) {
                return $colors;
            }

            // Create and return the palette
            $palette = new Color_Palette($colors, [
                'theme' => $theme,
                'provider' => $options['provider'],
                'name' => sprintf(
                    __('%s Palette', 'gl-color-palette-generator'),
                    ucfirst($theme)
                )
            ]);

            // Validate the generated palette
            $validation = $this->validator->validate_palette($palette);
            if (is_wp_error($validation)) {
                return $validation;
            }

            return $palette;

        } catch (\Exception $e) {
            return new \WP_Error(
                'generation_failed',
                __('Failed to generate palette', 'gl-color-palette-generator'),
                ['exception' => $e->getMessage()]
            );
        }
    }

    /**
     * Get available providers
     *
     * Returns a list of all available AI providers that can be used
     * for palette generation.
     *
     * @since 1.0.0
     * @return array List of available provider IDs
     */
    public function get_available_providers(): array {
        return $this->provider_factory->get_available_providers();
    }

    /**
     * Get provider requirements
     *
     * Returns the configuration requirements for a specific provider.
     *
     * @since 1.0.0
     * @param string $provider Provider ID
     * @return array Provider configuration requirements
     */
    public function get_provider_requirements(string $provider): array {
        return $this->provider_factory->get_provider_requirements($provider);
    }
}
