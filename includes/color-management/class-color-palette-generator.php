<?php
/**
 * Main Color Palette Generator Plugin Class
 *
 * @package GLColorPalette
 * @since 1.0.0
 */

namespace GLColorPalette;

/**
 * Main plugin class handling initialization and integration
 */
class Color_Palette_Generator {
    /**
     * Plugin instance
     * @var self
     */
    private static $instance = null;

    /**
     * AI Generator instance
     * @var Color_AI_Generator
     */
    private $ai_generator;

    /**
     * Color Analysis instance
     * @var Color_Analysis
     */
    private $analyzer;

    /**
     * Color Palette instance
     * @var Color_Palette
     */
    private $palette;

    /**
     * Get plugin instance
     *
     * @return self Plugin instance
     */
    public static function get_instance(): self {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_components();
        $this->register_hooks();
    }

    /**
     * Initialize plugin components
     */
    private function init_components(): void {
        // Get API key from options
        $api_key = get_option('gl_color_palette_api_key', '');
        $naming_preference = get_option('gl_color_palette_naming', 'both');

        $this->ai_generator = new Color_AI_Generator($api_key, $naming_preference);
        $this->analyzer = new Color_Analysis();
        $this->palette = new Color_Palette();
    }

    /**
     * Register WordPress hooks
     */
    private function register_hooks(): void {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_ajax_generate_palette', [$this, 'ajax_generate_palette']);
    }

    /**
     * Generate color palette with complete analysis
     *
     * @param array $context Business and design context
     * @return array Complete palette data with analysis
     */
    public function generate_palette(array $context): array {
        try {
            // Set context for AI generation
            $this->ai_generator->set_context($context);

            // Generate base palette
            $base_palette = $this->ai_generator->generate_palette();

            // Create color palette instance
            foreach ($base_palette as $role => $color) {
                $this->palette->add_color($color['hex'], [
                    'role' => $role,
                    'name' => $color['name'],
                    'feeling' => $color['feeling']
                ]);
            }

            // Generate variations
            $this->palette->generate_all_variations();

            // Perform complete analysis
            $analysis = $this->analyzer->analyze_complete($this->palette);

            return [
                'base_palette' => $base_palette,
                'variations' => $this->palette->get_all_variations(),
                'analysis' => $analysis,
                'css_variables' => $this->generate_css_variables(),
                'preview_html' => $this->generate_preview_html()
            ];

        } catch (\Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate CSS custom properties
     *
     * @return string CSS variables
     */
    private function generate_css_variables(): string {
        $css = ":root {\n";

        foreach ($this->palette->get_all_colors() as $role => $data) {
            $base_name = str_replace('_', '-', $role);
            $css .= "  --color-{$base_name}: {$data['hex']};\n";

            if (isset($data['variations'])) {
                foreach ($data['variations'] as $variant => $v_data) {
                    $css .= "  --color-{$base_name}-{$variant}: {$v_data['hex']};\n";
                }
            }
        }

        $css .= "}\n";
        return $css;
    }

    /**
     * Generate preview HTML
     *
     * @return string Preview HTML with color samples
     */
    private function generate_preview_html(): string {
        ob_start();
        ?>
        <div class="color-palette-preview">
            <?php foreach ($this->palette->get_all_colors() as $role => $data): ?>
                <div class="color-group">
                    <div class="color-sample" style="background-color: <?php echo esc_attr($data['hex']); ?>">
                        <span class="color-name"><?php echo esc_html($data['name']); ?></span>
                        <span class="color-hex"><?php echo esc_html($data['hex']); ?></span>
                    </div>
                    <?php if (isset($data['variations'])): ?>
                        <div class="color-variations">
                            <?php foreach ($data['variations'] as $variant => $v_data): ?>
                                <div class="variation-sample" style="background-color: <?php echo esc_attr($v_data['hex']); ?>">
                                    <span class="variation-name"><?php echo esc_html($variant); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
} 
