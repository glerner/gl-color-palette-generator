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
use GL_Color_Palette_Generator\Color_Management\Color_Scheme_Generator;
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
     * Scheme generator instance
     *
     * @var Color_Scheme_Generator
     */
    private Color_Scheme_Generator $scheme_generator;

    /**
     * Constructor
     *
     * @param Color_Utility $color_utility Color utility instance.
     */
    public function __construct(Color_Utility $color_utility) {
        $this->color_utility = $color_utility;
        $this->scheme_generator = new Color_Scheme_Generator($this->color_utility);
    }

    /**
     * Generate a new color palette
     *
     * @param array $options Generation options.
     * @return Color_Palette|WP_Error Generated palette or error.
     */
    public function generate_palette(array $options = []): Color_Palette|WP_Error {
        try {
            $scheme_type = $options['scheme_type'] ?? Color_Constants::SCHEME_MONOCHROMATIC;

            // Handle AI-generated palettes
            if ($scheme_type === Color_Constants::SCHEME_AI_GENERATED) {
                return $this->generate_ai_palette($options);
            }

            // Handle traditional color wheel schemes
            $base_color = $options['base_color'] ?? $this->generate_random_color();
            if ($base_color === '' || !Color_Types::is_valid_hex_color($base_color)) {
                return new WP_Error('invalid_color', __('Invalid base color provided', 'gl-color-palette-generator'));
            }

            return $this->generate_color_wheel_palette($base_color, $scheme_type, $options);
        } catch (\Exception $e) {
            return new WP_Error('generation_failed', $e->getMessage());
        }
    }

    /**
     * Generate an AI-powered color palette
     *
     * @param array $options Generation options including business context and image data.
     * @return Color_Palette Generated palette.
     */
    public function generate_ai_palette(array $options): Color_Palette {
        $ai_provider = AI_Provider_Factory::create_provider();
        $business_context = $options['business_context'] ?? [];
        $image_data = $options['image_data'] ?? null;

        // Check if we have enough context (any field with content is fine for now)
        if (empty($business_context) && empty($image_data)) {
            throw new \InvalidArgumentException('Need either business context or image for AI generation');
        }

        // Prepare the generation context
        $context = [
            'business' => $business_context,
            'image' => $image_data,
            'constraints' => $options['constraints'] ?? [],
            'accessibility' => $options['accessibility'] ?? true
        ];

        // Generate base colors using AI
        if ($image_data) {
            if ($image_data['context_type'] === 'extract') {
                $colors = $this->extract_colors_from_image($image_data['image_path']);
            } else {
                // AI enhancement for web-optimized colors
                $colors = $ai_provider->generate_colors_from_image($image_data['image_path'], $context, 'ai-enhance');
            }
        } else {
            $colors = $ai_provider->generate_themed_colors($context);
        }

        // Always ensure WCAG compliance
        $colors = $this->ensure_accessibility($colors);

        // Create palette with metadata
        return new Color_Palette([
            'colors' => $colors,  // Already has roles from AI or extract_colors_from_image
            'scheme_type' => Color_Constants::SCHEME_AI_GENERATED,
            'inspiration' => [
                'type' => $image_data ? 'image' : 'business',
                'source' => $this->get_inspiration_source($options)
            ]
        ]);
    }

    /**
     * Extract colors directly from an image using existing code
     *
     * @param string $image_path Path to the image file.
     * @return array Array of color data in format matching Color_Constants::AI_RESPONSE_FORMAT
     */
    private function extract_colors_from_image(string $image_path): array {
        // Get colors from image
        $hex_colors = $this->color_utility->extract_colors($image_path);

        // Format to match AI response format, though not done using AI
        $response = [];
        $roles = ['primary', 'secondary', 'accent', 'contrast'];
        $fallbacks = ['#000000', '#FFFFFF', '#808080', '#404040'];

        foreach ($roles as $i => $role) {
            $hex = $hex_colors[$i] ?? $fallbacks[$i];
            $response[$role] = [
                'hex' => $hex,
                'name' => $this->color_utility->get_color_name($hex),
                'emotion' => 'Extracted from image'
            ];
        }

        return $response;
    }

    /**
     * Ensure colors meet WCAG accessibility requirements
     *
     * @param array $colors Colors in AI response format
     * @return array Adjusted colors in same format
     */
    private function ensure_accessibility(array $colors): array {
        // Keep all color data, just adjust hex values if needed
        foreach ($colors as $role => $color_data) {
            $adjusted_hex = $this->color_utility->ensure_contrast($color_data['hex']);
            $colors[$role]['hex'] = $adjusted_hex;
            // Update name if color changed
            if ($adjusted_hex !== $color_data['hex']) {
                $colors[$role]['name'] = $this->color_utility->get_color_name($adjusted_hex);
            }
        }
        return $colors;
    }

    /**
     * Get the inspiration source description
     *
     * @param array $options Generation options.
     * @return string Source description.
     */
    private function get_inspiration_source(array $options): string {
        if (isset($options['image_data'])) {
            return basename($options['image_data']['image_path']);
        }
        if (isset($options['business_context']['description'])) {
            return $options['business_context']['description'];
        }
        return 'Generated theme';
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
            "You are a color palette expert for websites, with full knowledge of color mood and psychology, color contrast, and color harmony. You are also a WordPress theme.json developer.",
            "",
            "Generate base colors for a website color palette with the following requirements:",
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
 * Generate a complementary color scheme
 *
 * @param string $base_color Base color in hex format
 * @param array  $options    Generation options
 * @return array|WP_Error Color scheme or error
 */
public function generate_complementary(string $base_color, array $options = []): array|WP_Error {
    $count = $options['count'] ?? self::COMPLEMENTARY_COLORS;
    $validated_count = $this->validate_count($count, 'complementary');
    if (is_wp_error($validated_count)) {
        return $validated_count;
    }
    return $this->scheme_generator->generate_complementary($base_color, $validated_count);
}

/**
 * Generate an analogous color scheme
 *
 * @param string $base_color Base color in hex format
 * @param array  $options    Generation options
 * @return array|WP_Error Color scheme or error
 */
public function generate_analogous(string $base_color, array $options = []): array|WP_Error {
    $count = $options['count'] ?? self::ANALOGOUS_COLORS;
    $validated_count = $this->validate_count($count, 'analogous');
    if (is_wp_error($validated_count)) {
        return $validated_count;
    }
    return $this->scheme_generator->generate_analogous($base_color, $validated_count);
}

/**
 * Generate an analogous complement color scheme
 *
 * @param string $base_color Base color in hex format
 * @param array  $options    Generation options
 * @return array|WP_Error Color scheme or error
 */
public function generate_analogous_complement(string $base_color, array $options = []): array|WP_Error {
    $count = $options['count'] ?? self::ANALOGOUS_COMPLEMENT_COLORS;
    $validated_count = $this->validate_count($count, 'analogous_complement');
    if (is_wp_error($validated_count)) {
        return $validated_count;
    }
    return $this->scheme_generator->generate_analogous_complement($base_color, $validated_count);
}

/**
 * Generate a triadic color scheme
 *
 * @param string $base_color Base color in hex format
 * @param array  $options    Generation options
 * @return array|WP_Error Color scheme or error
 */
public function generate_triadic(string $base_color, array $options = []): array|WP_Error {
    $count = $options['count'] ?? self::TRIADIC_COLORS;
    $validated_count = $this->validate_count($count, 'triadic');
    if (is_wp_error($validated_count)) {
        return $validated_count;
    }
    return $this->scheme_generator->generate_triadic($base_color, $validated_count);
}

/**
 * Generate a split complementary color scheme
 *
 * @param string $base_color Base color in hex format
 * @param array  $options    Generation options
 * @return array|WP_Error Color scheme or error
 */
public function generate_split_complementary(string $base_color, array $options = []): array|WP_Error {
    $count = $options['count'] ?? self::SPLIT_COMPLEMENTARY_COLORS;
    $validated_count = $this->validate_count($count, 'split_complementary');
    if (is_wp_error($validated_count)) {
        return $validated_count;
    }
    return $this->scheme_generator->generate_split_complementary($base_color, $validated_count);
}

/**
 * Generate a tetradic color scheme
 *
 * @param string $base_color Base color in hex format
 * @param array  $options    Generation options
 * @return array|WP_Error Color scheme or error
 */
public function generate_tetradic(string $base_color, array $options = []): array|WP_Error {
    $count = $options['count'] ?? self::TETRADIC_COLORS;
    $validated_count = $this->validate_count($count, 'tetradic');
    if (is_wp_error($validated_count)) {
        return $validated_count;
    }
    return $this->scheme_generator->generate_tetradic($base_color, $validated_count);
}

/**
 * Generate a square color scheme
 *
 * @param string $base_color Base color in hex format
 * @param array  $options    Generation options
 * @return array|WP_Error Color scheme or error
 */
public function generate_square(string $base_color, array $options = []): array|WP_Error {
    $count = $options['count'] ?? self::SQUARE_COLORS;
    $validated_count = $this->validate_count($count, 'square');
    if (is_wp_error($validated_count)) {
        return $validated_count;
    }
    return $this->scheme_generator->generate_square($base_color, $validated_count);
}

/**
 * Generate a monochromatic color scheme
 *
 * @param string $base_color Base color in hex format
 * @param array  $options    Generation options
 * @return array|WP_Error Color scheme or error
 */
public function generate_monochromatic(string $base_color, array $options = []): array|WP_Error {
    $count = $options['count'] ?? self::MONOCHROMATIC_COLORS;
    $validated_count = $this->validate_count($count, 'monochromatic');
    if (is_wp_error($validated_count)) {
        return $validated_count;
    }

    return $this->scheme_generator->generate_monochromatic($base_color, $validated_count);
}

/**
 * Generate a monochromatic accent color scheme
 *
 * @param string $base_color Base color in hex format
 * @param array  $options    Generation options
 * @return array|WP_Error Color scheme or error
 */
public function generate_monochromatic_accent(string $base_color, array $options = []): array|WP_Error {
    $count = $options['count'] ?? self::MONOCHROMATIC_ACCENT_COLORS;
    $validated_count = $this->validate_count($count, 'monochromatic_accent');
    if (is_wp_error($validated_count)) {
        return $validated_count;
    }
    return $this->scheme_generator->generate_monochromatic_accent($base_color, $validated_count);
}


/**
 * Generate a compound color scheme
 *
 * @param string $base_color Base color in hex format
 * @param array  $options    Generation options
 * @return array|WP_Error Color scheme or error
 */
public function generate_compound(string $base_color, array $options = []): array|WP_Error {
    return $this->scheme_generator->generate_compound($base_color);
}

/**
 * Generate a dual tone color scheme
 *
 * @param string $base_color Base color in hex format
 * @param array  $options    Generation options
 * @return array|WP_Error Color scheme or error
 */
public function generate_dual_tone(string $base_color, array $options = []): array|WP_Error {
    $count = $options['count'] ?? self::DUAL_TONE_COLORS;
    $validated_count = $this->validate_count($count, 'dual_tone');
    if (is_wp_error($validated_count)) {
        return $validated_count;
    }
    return $this->scheme_generator->generate_dual_tone($base_color, $validated_count);
}

/**
 * Generate a neutral pop color scheme
 *
 * @param string $base_color Base color in hex format
 * @param array  $options    Generation options
 * @return array|WP_Error Color scheme or error
 */
public function generate_neutral_pop(string $base_color, array $options = []): array|WP_Error {
    $count = $options['count'] ?? self::NEUTRAL_POP_COLORS;
    $validated_count = $this->validate_count($count, 'neutral_pop');
    if (is_wp_error($validated_count)) {
        return $validated_count;
    }
    return $this->scheme_generator->generate_neutral_pop($base_color, $validated_count);
}


/**
 * Validate color count for a given scheme type
 *
 * @param int    $count       Number of colors requested
 * @param string $scheme_type Type of color scheme
 * @return int|WP_Error Validated count or error
 */
private function validate_count(int $count, string $scheme_type): int|WP_Error {
    $required_colors = match($scheme_type) {
        'complementary' => self::COMPLEMENTARY_COLORS,
        'analogous' => self::ANALOGOUS_COLORS,
        'triadic' => self::TRIADIC_COLORS,
        'tetradic' => self::TETRADIC_COLORS,
        'square' => self::SQUARE_COLORS,
        'monochromatic' => self::MONOCHROMATIC_COLORS,
        'compound' => self::COMPOUND_COLORS,
        default => 5
    };

    if ($count !== $required_colors) {
        return new WP_Error(
            'invalid_count',
            sprintf('%s schemes require exactly %d colors', $scheme_type, $required_colors)
        );
    }
    return $count;
}

    /**
     * Generate a color palette from an image
     *
     * @param string $image_path Path to the image file
     * @param array  $options    Optional. Additional options for image processing
     * @return array|WP_Error Color palette data or error on failure
     */
    public function generate_from_image($image_path, $options = []) {
        // Validate image path
        if (!file_exists($image_path)) {
            return new WP_Error(
                'invalid_image',
                __('Image file not found', 'gl-color-palette-generator')
            );
        }

        try {
            // Extract dominant colors from image
            $colors = $this->color_utility->extract_colors_from_image($image_path, $options);

            if (empty($colors)) {
                return new WP_Error(
                    'extraction_failed',
                    __('Failed to extract colors from image', 'gl-color-palette-generator')
                );
            }

            // Create and return the palette
            return [
                'colors' => $colors,
                'source' => 'image',
                'metadata' => [
                    'image_path' => $image_path,
                    'extraction_method' => $options['method'] ?? 'dominant',
                    'timestamp' => time()
                ]
            ];
        } catch (\Exception $e) {
            return new WP_Error(
                'generation_failed',
                $e->getMessage()
            );
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
        return array_keys(Color_Constants::COLOR_SCHEMES);
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
            'contrast_ratio' => Color_Constants::WCAG_CONTRAST_TARGET
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
