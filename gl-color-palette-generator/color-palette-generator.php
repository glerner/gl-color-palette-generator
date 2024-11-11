<?php
/*
Plugin Name: Color Palette Generator
Description: Generates accessible color palettes with variations and theme.json configurations
Version: 1.0
Author: George Lerner, https://website-tech.glerner.com/contact
*/

namespace GLColorPalette;

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'includes/class-contrast-checker.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-name-generator.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-settings-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-preview-generator.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-file-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-theme-json-generator.php';

class ColorPaletteGenerator {
    private $colors = [];
    private $naming_preference = '';
    private $name_generator;
    private $settings;
    private $file_handler;
    private $theme_json_generator;
    private $contrast_checker;

    public function __construct() {
        $this->contrast_checker = new ContrastChecker();

        $this->settings = new ColorPaletteGeneratorSettings();

        // Get OpenAI API key from WordPress options
        $openai_api_key = get_option('color_palette_generator_openai_key');

        $this->name_generator = new ColorNameGenerator(
            get_option('color_naming_preference', 'both'),
            $openai_api_key
        );

        $this->theme_json_generator = new ThemeJsonGenerator($this->contrast_checker);

        $this->file_handler = new FileHandler();

        add_action('admin_init', [$this, 'register_settings']);

        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_scheduled_delete', [$this->file_handler, 'cleanup']);
    }

    public function add_admin_menu() {
        add_menu_page(
            'Color Palette Generator',
            'Color Palette',
            'manage_options',
            'color-palette-generator',
            [$this, 'render_admin_page'],
            'dashicons-art'
        );
    }

    public function render_admin_page() {
        include plugin_dir_path(__FILE__) . 'templates/admin-form.php';
    }

    // Add settings page for API key
    public function register_settings() {
        register_setting('color_palette_generator', 'color_palette_generator_openai_key');
        register_setting('color_palette_generator', 'color_naming_preference');
    }

    public function enqueue_preview_assets() {
        wp_enqueue_style(
            'color-palette-preview',
            plugin_dir_url(__FILE__) . 'assets/css/preview-styles.css',
            [],
            '1.0.0'
        );
    }

    public function generate_previews($colors) {
        $preview_generator = new PreviewGenerator();

        // Generate palette preview
        $palette_preview = $preview_generator->generate_palette_preview($colors);

        // Generate variation previews
        $variation_previews = [];
        $variations = $this->theme_json_generator->generate_style_variations();

        foreach ($variations as $variation) {
            $variation_previews[] = [
                'title' => $variation['title'],
                'html' => $preview_generator->generate_variation_preview($variation)
            ];
        }

        return [
            'palette' => $palette_preview,
            'variations' => $variation_previews
        ];
    }

    /**
     * Process and save generated files
     */
    public function save_generated_files($theme_json, $variations) {
        try {
            // Save theme.json
            if (!$this->file_handler->save_theme_json($theme_json)) {
                throw new Exception('Failed to save theme.json');
            }

            // Save style variations
            $saved_files = $this->file_handler->save_style_variations($variations);
            if (empty($saved_files)) {
                throw new Exception('Failed to save style variations');
            }

            // Create ZIP archive
            $zip_info = $this->file_handler->create_zip_archive();

            // Schedule cleanup
            $this->file_handler->cleanup($zip_info);

            return [
                'success' => true,
                'message' => 'Files generated successfully',
                'download_url' => $zip_info['url'],
                'filename' => $zip_info['filename']
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Enqueue admin scripts and styles
     *
     * @param string $hook Current admin page hook
     */
    public function enqueue_assets($hook) {
        // Only load on our plugin's page
        if ('toplevel_page_color-palette-generator' !== $hook) {
            return;
        }

        // Enqueue CSS
        wp_enqueue_style(
            'color-palette-generator-admin',
            plugin_dir_url(__FILE__) . 'assets/css/admin.css',
            [],
            COLOR_PALETTE_GENERATOR_VERSION
        );

        // Add WordPress color picker
        wp_enqueue_style('wp-color-picker');

        // Enqueue admin-settings.js
        wp_enqueue_script(
            'color-palette-generator-admin-settings',
            plugin_dir_url(__FILE__) . 'assets/js/admin-settings.js',
            ['jquery'],
            COLOR_PALETTE_GENERATOR_VERSION,
            true
        );

        // Enqueue main admin.js
        wp_enqueue_script(
            'color-palette-generator-admin',
            plugin_dir_url(__FILE__) . 'assets/js/admin.js',
            ['jquery', 'wp-color-picker', 'color-palette-generator-admin-settings'],
            COLOR_PALETTE_GENERATOR_VERSION,
            true
        );

        // Localize script with data
        wp_localize_script(
            'color-palette-generator-admin',
            'colorPaletteGenerator',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('color_palette_generator_nonce'),
                'strings' => [
                    'generating' => __('Generating palette...', 'color-palette-generator'),
                    'error' => __('Error generating palette', 'color-palette-generator'),
                    'success' => __('Palette generated successfully!', 'color-palette-generator'),
                    'confirmReset' => __('Are you sure you want to reset settings?', 'color-palette-generator')
                ]
            ]
        );
    }
}

new ColorPaletteGenerator();
