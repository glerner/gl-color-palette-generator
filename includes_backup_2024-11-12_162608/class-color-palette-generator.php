<?php
/**
 * Main plugin class
 *
 * @package GLColorPalette
 * @since 1.0.0
 */

namespace GLColorPalette;

/**
 * Class ColorPaletteGenerator
 *
 * Handles the core functionality of the color palette generator plugin.
 *
 * @since 1.0.0
 */
class ColorPaletteGenerator {
    /**
     * Instance of this class.
     *
     * @since 1.0.0
     * @var ColorPaletteGenerator|null
     */
    private static ?ColorPaletteGenerator $instance = null;

    /**
     * The current version of the plugin.
     *
     * @since 1.0.0
     * @var string
     */
    protected string $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since 1.0.0
     */
    private function __construct() {
        $this->version = GL_COLOR_PALETTE_VERSION;
        $this->load_dependencies();
        $this->setup_hooks();
    }

    /**
     * Main ColorPaletteGenerator Instance.
     *
     * Ensures only one instance is loaded or can be loaded.
     *
     * @since 1.0.0
     * @return ColorPaletteGenerator
     */
    public static function get_instance(): ColorPaletteGenerator {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since 1.0.0
     * @return void
     */
    private function load_dependencies(): void {
        // Load admin and public classes
        require_once GL_COLOR_PALETTE_PATH . 'includes/admin/class-admin.php';
        require_once GL_COLOR_PALETTE_PATH . 'includes/public/class-public.php';
        require_once GL_COLOR_PALETTE_PATH . 'includes/api/class-color-api.php';
    }

    /**
     * Register all hooks related to the plugin functionality.
     *
     * @since 1.0.0
     * @return void
     */
    private function setup_hooks(): void {
        // Admin hooks
        add_action('admin_menu', [$this, 'add_plugin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);

        // AJAX handlers
        add_action('wp_ajax_generate_palette', [$this, 'handle_palette_generation']);
        add_action('wp_ajax_save_palette', [$this, 'handle_palette_save']);

        // Public hooks
        add_action('wp_enqueue_scripts', [$this, 'enqueue_public_assets']);
        add_shortcode('color_palette', [$this, 'render_color_palette']);
    }

    /**
     * Add plugin menu items.
     *
     * @since 1.0.0
     * @return void
     */
    public function add_plugin_menu(): void {
        add_menu_page(
            __('Color Palette Generator', 'gl-color-palette-generator'),
            __('Color Palettes', 'gl-color-palette-generator'),
            'manage_options',
            'gl-color-palettes',
            [$this, 'render_admin_page'],
            'dashicons-art',
            30
        );
    }

    /**
     * Generate a new color palette based on input parameters.
     *
     * @since 1.0.0
     * @param array $params Color generation parameters
     * @return array|WP_Error Array of colors or WP_Error on failure
     */
    public function generate_palette(array $params) {
        try {
            $api = new ColorAPI();
            return $api->generate_palette($params);
        } catch (\Exception $e) {
            return new \WP_Error('palette_generation_failed', $e->getMessage());
        }
    }

    // ... Additional methods will be added below
} 
