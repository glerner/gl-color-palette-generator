namespace GL_Color_Palette_Generator\Processors;

use GL_Color_Palette_Generator\Color_Management\Color_Analyzer;
use GL_Color_Palette_Generator\Accessibility\Accessibility_Checker;
use GL_Color_Palette_Generator\Settings\Settings_Manager;
use GL_Color_Palette_Generator\Cache\Color_Cache;
use GL_Color_Palette_Generator\Color_Utility;

/**
 * Class Palette_Generator
 * 
 * Generates color palettes based on various harmonies and accessibility requirements.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Processors
 * @since 1.0.0
 */
class Palette_Generator {
    private $color_analyzer;
    private $accessibility_checker;
    private $settings;
    private $cache;
    private $color_utility;

    /** Color harmony types */
    private const HARMONY_TYPES = [
        'monochromatic',
        'analogous',
        'complementary',
        'split_complementary',
        'triadic',
        'tetradic',
        'square',
        'compound'
    ];

    /** Palette sizes */
    private const PALETTE_SIZES = [
        'minimal' => 3,
        'standard' => 5,
        'extended' => 7,
        'complete' => 9
    ];

    public function __construct() {
        $this->color_analyzer = new Color_Analyzer();
        $this->accessibility_checker = new Accessibility_Checker();
        $this->settings = new Settings_Manager();
        $this->cache = new Color_Cache();
        $this->color_utility = new Color_Utility();
    }

    /**
     * Generate a complete color palette
     */
    public function generate_palette($base_color, $options = []) {
        $harmony_type = $options['harmony'] ?? 'complementary';
        $palette_size = $options['size'] ?? 'standard';
        $context = $options['context'] ?? [];

        try {
            /** Get base color properties */
            $base_properties = $this->color_analyzer->analyze_color($base_color);

            /** Generate harmony colors */
            $harmony_colors = $this->generate_harmony_colors(
                $base_color,
                $harmony_type,
                self::PALETTE_SIZES[$palette_size]
            );

            /** Adjust for accessibility */
            $accessible_colors = $this->ensure_accessibility($harmony_colors, $context);

            /** Generate tints and shades */
            $extended_colors = $this->generate_tints_and_shades($accessible_colors);

            /** Organize palette */
            $palette = $this->organize_palette($extended_colors, $options);

            /** Add metadata */
            $palette['metadata'] = $this->generate_palette_metadata(
                $base_color,
                $harmony_type,
                $palette
            );

            return $palette;

        } catch (Exception $e) {
            throw new PaletteGenerationException(
                "Failed to generate palette: " . $e->getMessage(),
                ErrorCodes::PALETTE_GENERATION_FAILED
            );
        }
    }

    /**
     * Generate harmony colors
     */
    private function generate_harmony_colors($base_color, $harmony_type, $count) {
        $hsl = $this->color_analyzer->hex_to_hsl($base_color);
        $colors = [$base_color];

        switch ($harmony_type) {
            case 'monochromatic':
                $colors = $this->generate_monochromatic($hsl, $count);
                break;

            case 'analogous':
                $colors = $this->generate_analogous($hsl, $count);
                break;

            case 'complementary':
                $colors = $this->generate_complementary($hsl, $count);
                break;

            case 'split_complementary':
                $colors = $this->generate_split_complementary($hsl, $count);
                break;

            case 'triadic':
                $colors = $this->generate_triadic($hsl, $count);
                break;

            case 'tetradic':
                $colors = $this->generate_tetradic($hsl, $count);
                break;

            case 'square':
                $colors = $this->generate_square($hsl, $count);
                break;

            case 'compound':
                $colors = $this->generate_compound($hsl, $count);
                break;
        }

        return $colors;
    }

    /**
     * Generate monochromatic colors
     */
    private function generate_monochromatic($hsl, $count) {
        $colors = [];
        $step = 1 / ($count - 1);

        for ($i = 0; $i < $count; $i++) {
            $lightness = max(0, min(1, $step * $i));
            $colors[] = $this->color_analyzer->hsl_to_hex([
                $hsl[0],
                $hsl[1],
                $lightness
            ]);
        }

        return $colors;
    }

    /**
     * Generate analogous colors
     */
    private function generate_analogous($hsl, $count) {
        $colors = [];
        $hue_step = 30 / ($count - 1);

        for ($i = 0; $i < $count; $i++) {
            $hue = ($hsl[0] + ($hue_step * $i - 30)) % 360;
            $colors[] = $this->color_analyzer->hsl_to_hex([
                $hue,
                $hsl[1],
                $hsl[2]
            ]);
        }

        return $colors;
    }

    /**
     * Ensure colors meet accessibility requirements
     *
     * @param array $colors Colors to check
     * @param array $context Context for accessibility checks
     * @return array Adjusted colors meeting accessibility requirements
     */
    private function ensure_accessibility($colors, $context) {
        $accessible_colors = [];
        $background = $context['background'] ?? self::COLOR_OFF_WHITE;

        foreach ($colors as $color) {
            $contrast_ratio = $this->color_utility->get_contrast_ratio(
                $color,
                $background
            );

            if ($contrast_ratio < self::CONTRAST_THRESHOLD_MIN) {
                $color = $this->adjust_for_contrast(
                    $color,
                    $background,
                    self::CONTRAST_THRESHOLD_TARGET
                );
            }

            $accessible_colors[] = $color;
        }

        return $accessible_colors;
    }

    /**
     * Generate tints and shades for each color
     *
     * @param array $colors Base colors to generate tints and shades for
     * @return array Array of colors with their tints and shades
     */
    private function generate_tints_and_shades($colors) {
        $extended = [];

        foreach ($colors as $color) {
            $hsl = $this->color_analyzer->hex_to_hsl($color);
            $variations = [];

            /** Generate shades (darker) */
            for ($i = 1; $i <= 4; $i++) {
                $lightness = max(0, $hsl[2] - ($i * 0.1));
                $variations['shade_' . $i] = $this->color_analyzer->hsl_to_hex([
                    $hsl[0],
                    $hsl[1],
                    $lightness
                ]);
            }

            /** Generate tints (lighter) */
            for ($i = 1; $i <= 4; $i++) {
                $lightness = min(1, $hsl[2] + ($i * 0.1));
                $variations['tint_' . $i] = $this->color_analyzer->hsl_to_hex([
                    $hsl[0],
                    $hsl[1],
                    $lightness
                ]);
            }

            $extended = array_merge($extended, $variations);
        }

        return $extended;
    }

    /**
     * Organize palette
     */
    private function organize_palette($colors, $options) {
        return [
            'primary' => $colors[0],
            'secondary' => $colors[1] ?? null,
            'accent' => $colors[2] ?? null,
            'variations' => array_slice($colors, 3),
            'combinations' => $this->generate_combinations($colors),
            'accessibility' => $this->generate_accessibility_report($colors)
        ];
    }

    /**
     * Generate palette metadata
     */
    private function generate_palette_metadata($base_color, $harmony_type, $palette) {
        return [
            'base_color' => $base_color,
            'harmony_type' => $harmony_type,
            'color_count' => count($palette['variations']) + 3,
            'generation_date' => current_time('mysql'),
            'accessibility_score' => $this->calculate_accessibility_score($palette),
            'harmony_score' => $this->calculate_harmony_score($palette),
            'color_properties' => $this->color_analyzer->analyze_color($base_color)
        ];
    }

    /**
     * Generate color palette with AI assistance
     */
    public function generate_ai_palette($base_color, $options = []) {
        try {
            $ai_service = new AiColorService($this->get_active_provider());
            $colors = $ai_service->generate_palette($base_color, $options);

            return $this->validate_and_process_palette($colors);
        } catch (Exception $e) {
            $this->error_handler->log_error($e->getMessage());
            return false;
        }
    }

    /**
     * Validate and process generated palette
     */
    private function validate_and_process_palette($colors) {
        $validator = new PaletteValidator();
        if (!$validator->validate($colors)) {
            throw new Exception('Invalid color palette generated');
        }

        $processed_colors = $this->color_processor->process_colors($colors);
        $this->cache_palette($processed_colors);

        return $processed_colors;
    }

    /**
     * Cache generated palette
     */
    private function cache_palette($colors) {
        $cache = new ColorCache();
        $cache->store($colors);
    }
}
