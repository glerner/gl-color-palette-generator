<?php
/**
 * Settings Page Handler
 *
 * @package GLColorPalette
 * @since 1.0.0
 */

namespace GLColorPalette\Admin;

/**
 * Handles the settings page functionality
 */
class Settings_Page {
    /**
     * Initialize the settings page
     */
    public function init(): void {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Add the settings page to the menu
     */
    public function add_settings_page(): void {
        add_submenu_page(
            'gl-color-palette',
            __('Settings', 'gl-color-palette-generator'),
            __('Settings', 'gl-color-palette-generator'),
            'manage_options',
            'gl-color-palette-settings',
            [$this, 'render_settings_page']
        );
    }

    /**
     * Register settings
     */
    public function register_settings(): void {
        register_setting('gl_color_palette_settings', 'gl_color_palette_ai_provider');
        register_setting('gl_color_palette_settings', 'gl_color_palette_api_key');
        register_setting('gl_color_palette_settings', 'gl_color_palette_openai_model');
        register_setting('gl_color_palette_settings', 'gl_color_palette_anthropic_model');

        add_settings_section(
            'gl_color_palette_ai_settings',
            __('AI Provider Settings', 'gl-color-palette-generator'),
            [$this, 'render_ai_settings_section'],
            'gl-color-palette-settings'
        );

        add_settings_field(
            'gl_color_palette_ai_provider',
            __('AI Provider', 'gl-color-palette-generator'),
            [$this, 'render_provider_field'],
            'gl-color-palette-settings',
            'gl_color_palette_ai_settings'
        );

        add_settings_field(
            'gl_color_palette_api_key',
            __('API Key', 'gl-color-palette-generator'),
            [$this, 'render_api_key_field'],
            'gl-color-palette-settings',
            'gl_color_palette_ai_settings'
        );
    }

    /**
     * Render the settings page
     */
    public function render_settings_page(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_GET['settings-updated'])) {
            add_settings_error(
                'gl_color_palette_messages',
                'gl_color_palette_message',
                __('Settings Saved', 'gl-color-palette-generator'),
                'updated'
            );
        }

        settings_errors('gl_color_palette_messages');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('gl_color_palette_settings');
                do_settings_sections('gl-color-palette-settings');
                submit_button('Save Settings');
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render the AI settings section
     */
    public function render_ai_settings_section(): void {
        echo '<p>' . esc_html__('Configure your AI provider settings below.', 'gl-color-palette-generator') . '</p>';
    }

    /**
     * Render the provider selection field
     */
    public function render_provider_field(): void {
        $provider = get_option('gl_color_palette_ai_provider', 'openai');
        ?>
        <select name="gl_color_palette_ai_provider" id="gl_color_palette_ai_provider">
            <option value="openai" <?php selected($provider, 'openai'); ?>>
                <?php esc_html_e('OpenAI', 'gl-color-palette-generator'); ?>
            </option>
            <option value="anthropic" <?php selected($provider, 'anthropic'); ?>>
                <?php esc_html_e('Anthropic', 'gl-color-palette-generator'); ?>
            </option>
        </select>
        <?php
    }

    /**
     * Render the API key field
     */
    public function render_api_key_field(): void {
        $api_key = get_option('gl_color_palette_api_key', '');
        ?>
        <input type="password"
               name="gl_color_palette_api_key"
               id="gl_color_palette_api_key"
               value="<?php echo esc_attr($api_key); ?>"
               class="regular-text">
        <?php
    }
} 
