<?php

class AdminInterface {
    private $plugin_slug = 'color-palette-generator';
    private $capability = 'manage_options';

    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu_pages']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_ajax_generate_palette', [$this, 'handle_generate_palette']);
    }

    /**
     * Add menu pages
     */
    public function add_menu_pages() {
        add_menu_page(
            'Color Palette Generator',
            'Color Palette',
            $this->capability,
            $this->plugin_slug,
            [$this, 'render_main_page'],
            'dashicons-art',
            30
        );
    }

    /**
     * Enqueue necessary assets
     */
    public function enqueue_assets($hook) {
        if (strpos($hook, $this->plugin_slug) === false) {
            return;
        }

        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');

        wp_enqueue_style(
            'color-palette-generator-admin',
            plugin_dir_url(dirname(__FILE__)) . 'assets/css/admin.css',
            [],
            '1.0.0'
        );

        wp_enqueue_script(
            'color-palette-generator-admin',
            plugin_dir_url(dirname(__FILE__)) . 'assets/js/admin.js',
            ['jquery', 'wp-color-picker'],
            '1.0.0',
            true
        );

        wp_localize_script(
            'color-palette-generator-admin',
            'colorPaletteAdmin',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('color_palette_generator'),
                'strings' => [
                    'generating' => __('Generating palette...', 'color-palette-generator'),
                    'success' => __('Palette generated successfully!', 'color-palette-generator'),
                    'error' => __('Error generating palette', 'color-palette-generator'),
                    'downloading' => __('Preparing download...', 'color-palette-generator')
                ]
            ]
        );
    }

    /**
     * Render main admin page
     */
    public function render_main_page() {
        if (!current_user_can($this->capability)) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        ?>
        <div class="wrap color-palette-generator">
            <h1><?php _e('Color Palette Generator', 'color-palette-generator'); ?></h1>

            <div class="palette-generator-container">
                <form id="palette-generator-form" class="palette-form">
                    <?php wp_nonce_field('generate_palette', 'palette_nonce'); ?>

                    <div class="color-inputs">
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                            <div class="color-input-group">
                                <label for="color-<?php echo $i; ?>">
                                    <?php printf(__('Color %d', 'color-palette-generator'), $i); ?>
                                </label>
                                <input type="text"
                                       id="color-<?php echo $i; ?>"
                                       name="colors[]"
                                       class="color-picker"
                                       value="#000000">
                            </div>
                        <?php endfor; ?>
                    </div>

                    <div class="naming-preference">
                        <label for="naming-preference">
                            <?php _e('Color Naming Style', 'color-palette-generator'); ?>
                        </label>
                        <select id="naming-preference" name="naming_preference">
                            <option value="descriptive">
                                <?php _e('Descriptive Names', 'color-palette-generator'); ?>
                            </option>
                            <option value="functional">
                                <?php _e('Functional Names', 'color-palette-generator'); ?>
                            </option>
                            <option value="both">
                                <?php _e('Both', 'color-palette-generator'); ?>
                            </option>
                        </select>
                    </div>

                    <div class="theme-selection">
                        <label for="base-theme">
                            <?php _e('Target Theme', 'color-palette-generator'); ?>
                        </label>
                        <select id="base-theme" name="base_theme">
                            <?php foreach ($this->get_available_themes() as $theme): ?>
                                <option value="<?php echo esc_attr($theme['stylesheet']); ?>">
                                    <?php echo esc_html($theme['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="submit-section">
                        <button type="submit" class="button button-primary">
                            <?php _e('Generate Palette', 'color-palette-generator'); ?>
                        </button>
                    </div>
                </form>

                <div id="preview-container" class="preview-container"></div>
                <div id="download-container" class="download-container"></div>
            </div>
        </div>
        <?php
    }

    /**
     * Get available themes
     */
    private function get_available_themes() {
        $themes = wp_get_themes();
        $available_themes = [];

        foreach ($themes as $theme) {
            if ($theme->get('ThemeURI')) {
                $available_themes[] = [
                    'name' => $theme->get('Name'),
                    'stylesheet' => $theme->get_stylesheet()
                ];
            }
        }

        return $available_themes;
    }

    /**
     * Handle AJAX palette generation
     */
    public function handle_generate_palette() {
        check_ajax_referer('color_palette_generator', 'nonce');

        if (!current_user_can($this->capability)) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }

        $colors = isset($_POST['colors']) ? array_map('sanitize_hex_color', $_POST['colors']) : [];
        $naming_preference = isset($_POST['naming_preference']) ?
            sanitize_text_field($_POST['naming_preference']) : 'both';
        $base_theme = isset($_POST['base_theme']) ?
            sanitize_text_field($_POST['base_theme']) : 'twentytwentyfour';

        try {
            // Process colors and generate files
            $generator = new ColorPaletteGenerator();
            $result = $generator->process_colors($colors, $naming_preference, $base_theme);

            wp_send_json_success($result);
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }
} 
