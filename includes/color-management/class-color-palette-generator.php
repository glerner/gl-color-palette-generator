<?php
/**
 * Color Palette Generator Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Color_Management
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GL_Color_Palette_Generator\Color_Management;

use GL_Color_Palette_Generator\Providers\AI_Provider_Factory;
use GL_Color_Palette_Generator\Providers\Provider_Interface;
use GL_Color_Palette_Generator\Interfaces\Color_Constants;
use GL_Color_Palette_Generator\Interfaces\Color_Palette_Generator_Interface;
use GL_Color_Palette_Generator\Models\Color_Palette;
use GL_Color_Palette_Generator\Types\Provider_Config;
use GL_Color_Palette_Generator\Types\Color_Types;
use WP_Error;

/**
 * Class Color_Palette_Generator
 *
 * Generates color palettes using various color theory algorithms.
 */
class Color_Palette_Generator implements Color_Palette_Generator_Interface {
    /**
     * Color utility instance
     *
     * @var Color_Utility
     */
    private Color_Utility $color_utility;

    /**
     * Constructor
     *
     * @param Color_Utility $color_utility Color utility instance.
     */
    public function __construct(Color_Utility $color_utility) {
        $this->color_utility = $color_utility;
    }

    /**
     * Generate a new color palette
     *
     * @param array $options Generation options.
     * @return Color_Palette|WP_Error Generated palette or error.
     */
    public function generate_palette(array $options = []): Color_Palette|WP_Error {
        try {
            $base_color = $options['base_color'] ?? $this->generate_random_color();
            $algorithm = $options['algorithm'] ?? 'monochromatic';

            if ($base_color === '' || !Color_Types::is_valid_hex_color($base_color)) {
                return new WP_Error('invalid_color', __('Invalid base color provided', 'gl-color-palette-generator'));
            }

            switch ($algorithm) {
                case 'complementary':
                    return $this->generate_complementary($base_color, $options);
                case 'analogous':
                    return $this->generate_analogous($base_color, $options);
                case 'triadic':
                    return $this->generate_triadic($base_color, $options);
                case 'monochromatic':
                    return $this->generate_monochromatic($base_color, $options);
                default:
                    return new WP_Error('invalid_algorithm', __('Invalid algorithm specified', 'gl-color-palette-generator'));
            }
        } catch (\Exception $e) {
            return new WP_Error('generation_failed', $e->getMessage());
        }
    }

    /**
     * Generate complementary color palette
     *
     * @param string $base_color Base color to build from.
     * @param array  $options    Generation options.
     * @return Color_Palette|WP_Error Generated palette or error.
     */
    public function generate_complementary(string $base_color, array $options = []): Color_Palette|WP_Error {
        try {
            $hsl = $this->color_utility->hex_to_hsl($base_color);
            $complement_hsl = $hsl;
            $complement_hsl['h'] = ($hsl['h'] + 180) % 360;

            $colors = [
                'base' => $base_color,
                'complement' => $this->color_utility->hsl_to_hex($complement_hsl)
            ];

            return new Color_Palette($colors);
        } catch (\Exception $e) {
            return new WP_Error('complementary_generation_failed', $e->getMessage());
        }
    }

    /**
     * Generate analogous color palette
     *
     * @param string $base_color Base color to build from.
     * @param array  $options    Generation options.
     * @return Color_Palette|WP_Error Generated palette or error.
     */
    public function generate_analogous(string $base_color, array $options = []): Color_Palette|WP_Error {
        try {
            $hsl = $this->color_utility->hex_to_hsl($base_color);
            $angle = $options['angle'] ?? 30;

            $colors = [
                'base' => $base_color,
                'analogous1' => $this->color_utility->hsl_to_hex([
                    'h' => ($hsl['h'] + $angle) % 360,
                    's' => $hsl['s'],
                    'l' => $hsl['l']
                ]),
                'analogous2' => $this->color_utility->hsl_to_hex([
                    'h' => ($hsl['h'] - $angle + 360) % 360,
                    's' => $hsl['s'],
                    'l' => $hsl['l']
                ])
            ];

            return new Color_Palette($colors);
        } catch (\Exception $e) {
            return new WP_Error('analogous_generation_failed', $e->getMessage());
        }
    }

    /**
     * Generate triadic color palette
     *
     * @param string $base_color Base color to build from.
     * @param array  $options    Generation options.
     * @return Color_Palette|WP_Error Generated palette or error.
     */
    public function generate_triadic(string $base_color, array $options = []): Color_Palette|WP_Error {
        try {
            $hsl = $this->color_utility->hex_to_hsl($base_color);

            $colors = [
                'base' => $base_color,
                'triad1' => $this->color_utility->hsl_to_hex([
                    'h' => ($hsl['h'] + 120) % 360,
                    's' => $hsl['s'],
                    'l' => $hsl['l']
                ]),
                'triad2' => $this->color_utility->hsl_to_hex([
                    'h' => ($hsl['h'] + 240) % 360,
                    's' => $hsl['s'],
                    'l' => $hsl['l']
                ])
            ];

            return new Color_Palette($colors);
        } catch (\Exception $e) {
            return new WP_Error('triadic_generation_failed', $e->getMessage());
        }
    }

    /**
     * Generate monochromatic color palette
     *
     * @param string $base_color Base color to build from.
     * @param array  $options    Generation options.
     * @return Color_Palette|WP_Error Generated palette or error.
     */
    public function generate_monochromatic(string $base_color, array $options = []): Color_Palette|WP_Error {
        try {
            $hsl = $this->color_utility->hex_to_hsl($base_color);
            $count = $options['count'] ?? 5;
            $step = 100 / ($count + 1);

            $colors = ['base' => $base_color];
            for ($i = 1; $i < $count; $i++) {
                $new_hsl = $hsl;
                $new_hsl['l'] = min(100, max(0, $step * $i));
                $colors["shade$i"] = $this->color_utility->hsl_to_hex($new_hsl);
            }

            return new Color_Palette($colors);
        } catch (\Exception $e) {
            return new WP_Error('monochromatic_generation_failed', $e->getMessage());
        }
    }

    /**
     * Generate a color palette from user-given context
     *
     * @param string $prompt JSON string containing user context
     * @return Color_Palette|WP_Error Generated palette or error
     */
    public function generate_from_prompt(string $prompt): Color_Palette|WP_Error {
        try {
            // Parse context from JSON
            $context = json_decode($prompt, true);
            if ($context === null || !is_array($context)) {
                return new WP_Error('invalid_context', __('Invalid context data provided', 'gl-color-palette-generator'));
            }

            // Check if we have enough context for AI generation
            if (
                (!isset($context['business_type']) || $context['business_type'] === '') &&
                (!isset($context['target_audience']) || $context['target_audience'] === '') &&
                (!isset($context['desired_mood']) || $context['desired_mood'] === '')
            ) {
                return new WP_Error(
                    'insufficient_context',
                    __('Please provide more context about your business, audience, or desired mood', 'gl-color-palette-generator')
                );
            }

            // Get AI-generated colors
            $colors = $this->get_ai_generated_colors($this->build_ai_prompt($context));
            if (is_wp_error($colors)) {
                return $colors;
            }

            return new Color_Palette($colors);
        } catch (\Exception $e) {
            return new WP_Error('prompt_generation_failed', $e->getMessage());
        }
    }

    /**
     * Build AI prompt from user context
     *
     * @param array $context User input context
     * @return string Formatted prompt for AI service
     */
    protected function build_ai_prompt(array $context): string {
        $prompt_parts = [
            "You are a color palette expert for websites, with full knowledge of color mood, color contrast, and Accessibility Guidelines for color contrast. You are also a WordPress theme.json developer.",
            "",
            "Generate a website color palette with the following requirements:",
            "",
            "Business Description:",
            $context['business_type'] ?? 'Not specified',
            "",
            "Target Audience:",
            $context['target_audience'] ?? 'Not specified',
            "",
            "Desired Mood:",
            $context['desired_mood'] ?? 'Not specified',
            "",
            "Requirements:",
            "- Generate exactly 4 colors: primary, secondary, tertiary, and accent",
            "- For each color, generate an \"artistic\" name and a description of the emotion or reaction it evokes",
            "- Use medium luminance values that allow for both lighter and darker variations",
            "- Colors should reflect the desired mood and appeal to the target audience",
            "- Consider cultural implications and color psychology",
            "",
            "Return the colors and descriptions in JSON format like this:",
            json_encode(Color_Constants::AI_RESPONSE_FORMAT, JSON_PRETTY_PRINT),
            "",
            "For each color, explain:",
            "1. How it reflects the business values",
            "2. Why it appeals to the target audience",
            "3. What emotions or reactions it may evoke",
            "4. How it complements the other colors"
        ];

        return implode("\n", $prompt_parts);
    }

    /**
     * Get colors from AI service
     *
     * @param string $prompt Formatted prompt for AI
     * @return array|WP_Error Array of colors or error
     */
    protected function get_ai_generated_colors(string $prompt): array|WP_Error {
        try {
            // Get the configured AI provider
            $provider_factory = new AI_Provider_Factory();
            $provider_config = new Provider_Config([
                'api_key' => get_option('gl_cpg_ai_api_key'),
                'provider_type' => get_option('gl_cpg_ai_provider', 'openai')
            ]);

            $provider = $provider_factory->get_provider($provider_config->get_provider_type(), $provider_config);
            if (is_wp_error($provider)) {
                throw new \Exception($provider->get_error_message());
            }

            // Generate the palette
            $result = $provider->generate_palette([
                'prompt' => $prompt,
                'num_colors' => 4,
                'options' => Color_Constants::AI_CONFIG
            ]);

            if (is_wp_error($result)) {
                throw new \Exception($result->get_error_message());
            }

            // Extract hex codes and save description
            $colors = [];
            if (isset($result['colors']) && is_array($result['colors'])) {
                foreach ($result['colors'] as $role => $color_data) {
                    $colors[$role] = $color_data['hex'] ?? $color_data;  // Fallback for old format
                }
            }

            // Save palette description if available
            if (isset($result['palette_story'])) {
                update_option('gl_cpg_last_palette_story', $result['palette_story']);
            }

            return $colors;
        } catch (\Exception $e) {
            return new WP_Error('ai_generation_failed', $e->getMessage());
        }
    }

    /**
     * Verify WCAG compliance of a color palette
     *
     * @param Color_Palette $palette Palette to verify
     * @return bool True if compliant
     * @internal
     */
    protected function verify_wcag_compliance(Color_Palette $palette): bool {
        $colors = $palette->get_colors();
        foreach ($colors as $color) {
            $contrast_with_light = $this->color_utility->get_contrast_ratio(
                $color,
                Color_Constants::COLOR_OFF_WHITE
            );
            $contrast_with_dark = $this->color_utility->get_contrast_ratio(
                $color,
                Color_Constants::COLOR_NEAR_BLACK
            );

            if ($contrast_with_light < Color_Constants::WCAG_CONTRAST_MIN &&
                $contrast_with_dark < Color_Constants::WCAG_CONTRAST_MIN) {
                return false;
            }
        }
        return true;
    }

    /**
     * Adjust colors to meet WCAG compliance
     *
     * @param Color_Palette $palette Palette to adjust
     * @return Color_Palette Adjusted palette
     * @internal
     */
    protected function adjust_for_wcag_compliance(Color_Palette $palette): Color_Palette {
        $colors = $palette->get_colors();
        $adjusted_colors = [];

        foreach ($colors as $role => $color) {
            $contrast_with_light = $this->color_utility->get_contrast_ratio(
                $color,
                Color_Constants::COLOR_OFF_WHITE
            );
            $contrast_with_dark = $this->color_utility->get_contrast_ratio(
                $color,
                Color_Constants::COLOR_NEAR_BLACK
            );

            if ($contrast_with_light < Color_Constants::WCAG_CONTRAST_MIN &&
                $contrast_with_dark < Color_Constants::WCAG_CONTRAST_MIN) {
                // Adjust color lightness until it meets contrast requirements
                $hsl = $this->color_utility->hex_to_hsl($color);
                if ($hsl['l'] > 0.5) {
                    $hsl['l'] = max(0, $hsl['l'] - 0.1);
                } else {
                    $hsl['l'] = min(1, $hsl['l'] + 0.1);
                }
                $adjusted_colors[$role] = $this->color_utility->hsl_to_hex($hsl);
            } else {
                $adjusted_colors[$role] = $color;
            }
        }

        return new Color_Palette($adjusted_colors);
    }

    /**
     * Get available generation algorithms
     *
     * @return array List of available algorithms.
     */
    public function get_available_algorithms(): array {
        return [
            'monochromatic' => __('Monochromatic', 'gl-color-palette-generator'),
            'complementary' => __('Complementary', 'gl-color-palette-generator'),
            'analogous' => __('Analogous', 'gl-color-palette-generator'),
            'triadic' => __('Triadic', 'gl-color-palette-generator')
        ];
    }

    /**
     * Get default generation options
     *
     * @return array Default options.
     */
    public function get_default_options(): array {
        return [
            'algorithm' => 'monochromatic',
            'count' => 5,
            'angle' => 30,
            'contrast_ratio' => Color_Constants::WCAG_CONTRAST_AA
        ];
    }

    /**
     * Generate a random color
     *
     * @return string Random color in hex format
     */
    protected function generate_random_color(): string {
        return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
    }
}
