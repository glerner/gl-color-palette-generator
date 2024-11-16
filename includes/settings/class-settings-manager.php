<?php
namespace GLColorPalette;

class SettingsManager {
    private $option_name = 'color_palette_generator_settings';
    private $settings = [];
    private $defaults = [
        'naming_service' => 'local', // local, openai, or custom
        'api_key' => '',
        'default_theme' => 'twentytwentyfour',
        'cache_duration' => 7 * DAY_IN_SECONDS,
        'max_variations' => 16,
        'accessibility' => [
            'min_contrast_ratio' => 4.5,
            'check_color_blindness' => true,
            'enforce_wcag_aa' => true,
            'enforce_wcag_aaa' => false
        ],
        'export_options' => [
            'include_theme_json' => true,
            'include_variations' => true,
            'include_css' => true,
            'minify_output' => false
        ],
        'default_colors' => [
            'primary' => '#000000',
            'secondary' => '#000000',
            'tertiary' => '#000000',
            'accent' => '#000000'
        ]
    ];

    public function __construct() {
        add_action('admin_init', [$this, 'register_settings']);
        $this->load_settings();
    }

    public function register_settings() {
        register_setting(
            'color_palette_generator_settings',
            'color_palette_generator_options',
            [$this, 'validate_settings']
        );

        $this->add_settings_sections();
        $this->add_settings_fields();
    }

    private function add_settings_sections() {
        add_settings_section(
            'ai_provider_section',
            __('AI Provider Settings', 'color-palette-generator'),
            [$this, 'render_provider_section'],
            'color-palette-generator'
        );

        add_settings_section(
            'color_options_section',
            __('Color Generation Options', 'color-palette-generator'),
            [$this, 'render_color_section'],
            'color-palette-generator'
        );
    }

    private function add_settings_fields() {
        // AI Provider fields
        add_settings_field(
            'ai_provider',
            __('AI Provider', 'color-palette-generator'),
            [$this, 'render_provider_field'],
            'color-palette-generator',
            'ai_provider_section'
        );

        // API Key field
        add_settings_field(
            'api_key',
            __('API Key', 'color-palette-generator'),
            [$this, 'render_api_key_field'],
            'color-palette-generator',
            'ai_provider_section'
        );
    }

    public function render_section_header($args) {
        switch($args['id']) {
            case 'color_palette_general':
                echo '<p>' . __('Configure the general settings for the Color Palette Generator.', 'color-palette-generator') . '</p>';
                break;
        }
    }

    public function render_select_field($args) {
        $value = $this->get_setting($args['id']);
        ?>
        <select id="<?php echo esc_attr($args['id']); ?>"
                name="<?php echo esc_attr($this->option_name . '[' . $args['id'] . ']'); ?>">
            <?php foreach($args['options'] as $key => $label): ?>
                <option value="<?php echo esc_attr($key); ?>"
                        <?php selected($value, $key); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if(!empty($args['description'])): ?>
            <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php endif;
    }

    public function render_accessibility_fields() {
        $accessibility = $this->get_setting('accessibility');
        ?>
        <fieldset>
            <label>
                <input type="number"
                       name="<?php echo esc_attr($this->option_name . '[accessibility][min_contrast_ratio]'); ?>"
                       value="<?php echo esc_attr($accessibility['min_contrast_ratio']); ?>"
                       step="0.1"
                       min="1"
                       max="21">
                <?php _e('Minimum Contrast Ratio', 'color-palette-generator'); ?>
            </label>
            <br>
            <label>
                <input type="checkbox"
                       name="<?php echo esc_attr($this->option_name . '[accessibility][check_color_blindness]'); ?>"
                       <?php checked($accessibility['check_color_blindness']); ?>>
                <?php _e('Check Color Blindness Compatibility', 'color-palette-generator'); ?>
            </label>
            <br>
            <label>
                <input type="checkbox"
                       name="<?php echo esc_attr($this->option_name . '[accessibility][enforce_wcag_aa]'); ?>"
                       <?php checked($accessibility['enforce_wcag_aa']); ?>>
                <?php _e('Enforce WCAG AA Standards', 'color-palette-generator'); ?>
            </label>
        </fieldset>
        <?php
    }

    public function render_export_fields() {
        $export = $this->get_setting('export_options');
        ?>
        <fieldset>
            <label>
                <input type="checkbox"
                       name="<?php echo esc_attr($this->option_name . '[export_options][include_theme_json]'); ?>"
                       <?php checked($export['include_theme_json']); ?>>
                <?php _e('Include theme.json', 'color-palette-generator'); ?>
            </label>
            <br>
            <label>
                <input type="checkbox"
                       name="<?php echo esc_attr($this->option_name . '[export_options][include_variations]'); ?>"
                       <?php checked($export['include_variations']); ?>>
                <?php _e('Include Style Variations', 'color-palette-generator'); ?>
            </label>
            <br>
            <label>
                <input type="checkbox"
                       name="<?php echo esc_attr($this->option_name . '[export_options][minify_output]'); ?>"
                       <?php checked($export['minify_output']); ?>>
                <?php _e('Minify Output', 'color-palette-generator'); ?>
            </label>
        </fieldset>
        <?php
    }

    public function render_color_fields() {
        $colors = $this->get_setting('default_colors');
        ?>
        <div class="default-colors-grid">
            <?php foreach($colors as $role => $color): ?>
                <div class="color-field">
                    <label>
                        <?php echo esc_html(ucfirst($role)); ?>
                        <input type="text"
                               class="color-picker"
                               name="<?php echo esc_attr($this->option_name . '[default_colors][' . $role . ']'); ?>"
                               value="<?php echo esc_attr($color); ?>">
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    public function sanitize_settings($input) {
        $sanitized = wp_parse_args($input, $this->defaults);

        // Sanitize API key
        $sanitized['api_key'] = sanitize_text_field($input['api_key']);

        // Sanitize accessibility settings
        $sanitized['accessibility']['min_contrast_ratio'] =
            floatval($input['accessibility']['min_contrast_ratio']);
        $sanitized['accessibility']['check_color_blindness'] =
            isset($input['accessibility']['check_color_blindness']);
        $sanitized['accessibility']['enforce_wcag_aa'] =
            isset($input['accessibility']['enforce_wcag_aa']);

        // Sanitize export options
        $sanitized['export_options']['include_theme_json'] =
            isset($input['export_options']['include_theme_json']);
        $sanitized['export_options']['include_variations'] =
            isset($input['export_options']['include_variations']);
        $sanitized['export_options']['minify_output'] =
            isset($input['export_options']['minify_output']);

        // Sanitize default colors
        foreach($input['default_colors'] as $role => $color) {
            $sanitized['default_colors'][$role] = sanitize_hex_color($color);
        }

        return $sanitized;
    }

    public function get_setting($key = null) {
        if(is_null($key)) {
            return $this->settings;
        }

        return isset($this->settings[$key]) ? $this->settings[$key] : null;
    }

    private function load_settings() {
        $this->settings = wp_parse_args(
            get_option($this->option_name, []),
            $this->defaults
        );
    }

    // Add settings validation
    public function validate_settings($settings) {
        $validator = new SettingsValidator();
        return $validator->validate($settings);
    }
}
