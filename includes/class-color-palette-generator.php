<?php
/**
 * Main Color Palette Generator Class
 *
 * @package GLColorPalette
 * @since 1.0.0
 */

namespace GLColorPalette;

use GLColorPalette\Providers\AI_Provider_Factory;
use GLColorPalette\Interfaces\Color_Processor;

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
     * The AI provider factory instance.
     *
     * @since 1.0.0
     * @var AI_Provider_Factory
     */
    private AI_Provider_Factory $provider_factory;

    /**
     * The color processor instance.
     *
     * @since 1.0.0
     * @var Color_Processor
     */
    private Color_Processor $color_processor;

    /**
     * Initialize the class and set its properties.
     *
     * @since 1.0.0
     */
    private function __construct() {
        $this->version = GL_COLOR_PALETTE_VERSION;
        $this->provider_factory = new AI_Provider_Factory();
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
        require_once GL_COLOR_PALETTE_PATH . 'includes/class-admin-interface.php';
        require_once GL_COLOR_PALETTE_PATH . 'includes/class-color-processor.php';
        require_once GL_COLOR_PALETTE_PATH . 'includes/class-color-analytics.php';
        require_once GL_COLOR_PALETTE_PATH . 'includes/class-color-cache.php';
    }

    /**
     * Register all hooks related to the plugin functionality.
     *
     * @since 1.0.0
     * @return void
     */
    private function setup_hooks(): void {
        add_action('admin_menu', [$this, 'add_plugin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_ajax_generate_palette', [$this, 'handle_palette_generation']);
        add_action('wp_ajax_save_palette', [$this, 'handle_palette_save']);
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
     * Enqueue admin-specific scripts and styles.
     *
     * @since 1.0.0
     * @param string $hook The current admin page hook
     * @return void
     */
    public function enqueue_admin_assets(string $hook): void {
        if ('toplevel_page_gl-color-palettes' !== $hook) {
            return;
        }

        wp_enqueue_style(
            'gl-color-palette-admin',
            GL_COLOR_PALETTE_URL . 'assets/css/admin.css',
            [],
            $this->version
        );

        wp_enqueue_script(
            'gl-color-palette-admin',
            GL_COLOR_PALETTE_URL . 'assets/js/admin.js',
            ['jquery', 'wp-color-picker'],
            $this->version,
            true
        );

        wp_localize_script('gl-color-palette-admin', 'glColorPalette', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('gl_color_palette_nonce'),
        ]);
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
            $provider = $this->provider_factory->get_provider();
            return $provider->generate_palette($params);
        } catch (\Exception $e) {
            return new \WP_Error('palette_generation_failed', $e->getMessage());
        }
    }

    /**
     * Handle AJAX request for palette generation.
     *
     * @since 1.0.0
     * @return void
     */
    public function handle_palette_generation(): void {
        check_ajax_referer('gl_color_palette_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        $params = filter_input_array(INPUT_POST, [
            'base_color' => FILTER_SANITIZE_STRING,
            'mode' => FILTER_SANITIZE_STRING,
            'count' => FILTER_VALIDATE_INT,
        ]);

        $result = $this->generate_palette($params);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success($result);
    }

    /**
     * Handle AJAX request for saving a palette.
     *
     * @since 1.0.0
     * @return void
     */
    public function handle_palette_save(): void {
        check_ajax_referer('gl_color_palette_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        $palette_data = filter_input(INPUT_POST, 'palette', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $name = sanitize_text_field(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));

        if (empty($palette_data) || empty($name)) {
            wp_send_json_error('Invalid palette data');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'gl_color_palettes';

        $result = $wpdb->insert(
            $table_name,
            [
                'name' => $name,
                'colors' => json_encode($palette_data),
                'user_id' => get_current_user_id(),
                'created_at' => current_time('mysql'),
            ],
            ['%s', '%s', '%d', '%s']
        );

        if (false === $result) {
            wp_send_json_error('Failed to save palette');
        }

        wp_send_json_success([
            'id' => $wpdb->insert_id,
            'message' => __('Palette saved successfully', 'gl-color-palette-generator'),
        ]);
    }

    /**
     * Render the admin page content.
     *
     * @since 1.0.0
     * @return void
     */
    public function render_admin_page(): void {
        require_once GL_COLOR_PALETTE_PATH . 'templates/admin-form.php';
    }

    /**
     * Get the plugin version.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_version(): string {
        return $this->version;
    }
} 
