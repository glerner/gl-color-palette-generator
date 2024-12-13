<?php
/**
 * Core Plugin Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Core
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Core;

use GL_Color_Palette_Generator\Core\Setup;
use GL_Color_Palette_Generator\Core\Assets;
use GL_Color_Palette_Generator\Core\REST_API;
use GL_Color_Palette_Generator\Core\Error_Handler;
use GL_Color_Palette_Generator\Core\Logger;
use GL_Color_Palette_Generator\Core\Plugin_Info;
use GL_Color_Palette_Generator\Core\Rate_Limiter;
use GL_Color_Palette_Generator\Core\Update_Checker;

/**
 * Main plugin core class
 */
class Core {
    private $dependency_manager;

    /**
     * Initialize the core system
     */
    public function init() {
        // Initialize dependency manager
        $this->dependency_manager = new GLColorPaletteDependencyManager();

        if (!$this->dependency_manager->init()) {
            add_action('admin_notices', [$this, 'show_dependency_errors']);
            return false;
        }

        // Load core components
        $this->load_core_components();

        // Register hooks if components loaded successfully
        if (empty($this->dependency_manager->get_errors())) {
            $this->register_hooks();
            return true;
        }

        return false;
    }

    /**
     * Load required traits
     */
    private function load_traits() {
        require_once GL_COLOR_PALETTE_PLUGIN_DIR . 'includes/trait-database-tables.php';
    }

    /**
     * Initialize all component classes
     */
    private function init_components() {
        $this->dependency_manager = new DependencyManager();
        $this->settings_manager = new SettingsManager();
        $this->error_handler = new ErrorHandler();
        $this->init_providers();
        $this->init_color_services();
    }

    /**
     * Initialize color processing services
     */
    private function init_color_services() {
        $this->color_processor = new ColorProcessor();
        $this->palette_generator = new PaletteGenerator(
            $this->color_processor,
            new ContrastChecker(),
            new ColorHarmonization()
        );
    }

    /**
     * Initialize AI providers
     */
    private function init_providers() {
        $provider_factory = new Providers\AiProviderFactory();
        $this->ai_provider = $provider_factory->create_provider(
            get_option('color_palette_generator_ai_provider', 'openai')
        );
    }

    /**
     * Register WordPress hooks
     */
    private function register_hooks() {
        // Admin hooks
        add_action('admin_menu', [$this->components['admin_interface'], 'register_menus']);
        add_action('admin_notices', [$this->components['admin_notices'], 'display_notices']);
        add_action('admin_enqueue_scripts', [$this->components['admin_interface'], 'enqueue_assets']);

        // AJAX handlers
        add_action('wp_ajax_gl_generate_palette', [$this->components['palette_generator'], 'ajax_generate']);
        add_action('wp_ajax_gl_analyze_palette', [$this->components['color_analytics'], 'ajax_analyze']);
        add_action('wp_ajax_gl_export_palette', [$this->components['color_exporter'], 'ajax_export']);

        // REST API endpoints
        add_action('rest_api_init', [$this, 'register_rest_routes']);

        // Cron jobs
        add_action('gl_daily_cleanup', [$this->components['color_cache'], 'cleanup']);
        add_action('gl_process_analytics', [$this->components['color_analytics'], 'process_queue']);
    }

    /**
     * Register REST API routes
     */
    private function register_rest_routes() {
        // Register routes for each component that needs them
        $this->components['palette_manager']->register_routes();
        $this->components['color_analytics']->register_routes();
        $this->components['documentation_generator']->register_routes();
    }

    /**
     * Get component instance
     */
    public function get_component($name) {
        return $this->dependency_manager->get_component($name);
    }

    /**
     * Get AI provider instance
     */
    public function get_provider($name) {
        return isset($this->providers[$name]) ? $this->providers[$name] : null;
    }

    private function load_core_components() {
        $core_components = [
            'color_processor',
            'palette_generator',
            'palette_manager',
            'admin_interface',
            'settings_manager',
            // ... other core components
        ];

        foreach ($core_components as $component) {
            $this->dependency_manager->load_component($component);
        }
    }

    public function show_dependency_errors() {
        $errors = $this->dependency_manager->get_errors();
        if (!empty($errors)) {
            echo '<div class="error"><p>';
            echo '<strong>GL Color Palette Generator Error:</strong><br>';
            echo implode('<br>', $errors);
            echo '</p></div>';
        }
    }

    /**
     * Handle plugin activation
     */
    public function activate() {
        $setup = new Setup();
        $setup->create_tables();
        $setup->set_default_options();
        flush_rewrite_rules();
    }
}
