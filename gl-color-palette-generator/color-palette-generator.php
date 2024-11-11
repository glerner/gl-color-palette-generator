<?php
/*
Plugin Name: Color Palette Generator
Description: Generates accessible color palettes with variations and theme.json configurations
Version: 1.0
Author: George Lerner, https://website-tech.glerner.com/contact
*/

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'includes/class-contrast-checker.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-name-generator.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-settings-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-preview-generator.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-file-handler.php';

class ColorPaletteGenerator {
    private $colors = [];
    private $naming_preference = '';
    private $name_generator;
    private $settings;
    private $file_handler;

    public function __construct() {
        $this->settings = new ColorPaletteGeneratorSettings();
        // Get OpenAI API key from WordPress options
        $openai_api_key = get_option('color_palette_generator_openai_key');

        // Initialize name generator with preferences and API key
        $this->name_generator = new ColorNameGenerator(
            get_option('color_naming_preference', 'both'),
            $openai_api_key
        );

        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);

        $this->file_handler = new FileHandler();

        // Add cleanup hook
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
}

new ColorPaletteGenerator();
