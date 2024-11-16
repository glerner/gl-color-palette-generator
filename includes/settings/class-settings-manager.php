<?php
/**
 * Settings Manager Class
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\Settings;

/**
 * Class Settings_Manager
 */
class Settings_Manager {
    /**
     * Settings options
     *
     * @var array
     */
    private $options;

    /**
     * Initialize the settings
     */
    public function init() {
        add_action('admin_init', [$this, 'register_settings']);
        $this->options = $this->get_default_options();
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting(
            'gl_cpg_settings',
            'gl_cpg_options',
            [$this, 'sanitize_settings']
        );

        add_settings_section(
            'gl_cpg_main_section',
            __('Main Settings', 'gl-color-palette-generator'),
            [$this, 'render_section'],
            'gl_cpg_settings'
        );

        $this->add_settings_fields();
    }

    /**
     * Add settings fields
     */
    private function add_settings_fields() {
        $fields = [
            'ai_provider' => [
                'title' => __('AI Provider', 'gl-color-palette-generator'),
                'type' => 'select',
                'options' => [
                    'openai' => 'OpenAI',
                    'anthropic' => 'Anthropic',
                    'palm' => 'PaLM',
                    'cohere' => 'Cohere',
                ],
            ],
            'api_key' => [
                'title' => __('API Key', 'gl-color-palette-generator'),
                'type' => 'password',
            ],
            'cache_duration' => [
                'title' => __('Cache Duration (seconds)', 'gl-color-palette-generator'),
                'type' => 'number',
            ],
        ];

        foreach ($fields as $key => $field) {
            add_settings_field(
                'gl_cpg_' . $key,
                $field['title'],
                [$this, 'render_field'],
                'gl_cpg_settings',
                'gl_cpg_main_section',
                [
                    'key' => $key,
                    'type' => $field['type'],
                    'options' => $field['options'] ?? [],
                ]
            );
        }
    }

    /**
     * Render settings section
     */
    public function render_section() {
        echo '<p>' . esc_html__('Configure your color palette generator settings below.', 'gl-color-palette-generator') . '</p>';
    }

    /**
     * Render settings field
     *
     * @param array $args Field arguments.
     */
    public function render_field($args) {
        $options = get_option('gl_cpg_options', $this->get_default_options());
        $value = $options[$args['key']] ?? '';

        switch ($args['type']) {
            case 'select':
                echo '<select name="gl_cpg_options[' . esc_attr($args['key']) . ']">';
                foreach ($args['options'] as $option_value => $option_label) {
                    echo '<option value="' . esc_attr($option_value) . '" ' .
                         selected($value, $option_value, false) . '>' .
                         esc_html($option_label) . '</option>';
                }
                echo '</select>';
                break;

            case 'password':
                echo '<input type="password" name="gl_cpg_options[' . esc_attr($args['key']) . ']" value="' .
                     esc_attr($value) . '" class="regular-text">';
                break;

            default:
                echo '<input type="' . esc_attr($args['type']) . '" name="gl_cpg_options[' .
                     esc_attr($args['key']) . ']" value="' . esc_attr($value) . '" class="regular-text">';
        }
    }

    /**
     * Sanitize settings
     *
     * @param array $input Input array to sanitize.
     * @return array
     */
    public function sanitize_settings($input) {
        $sanitized = [];

        if (isset($input['ai_provider'])) {
            $sanitized['ai_provider'] = sanitize_text_field($input['ai_provider']);
        }

        if (isset($input['api_key'])) {
            $sanitized['api_key'] = sanitize_text_field($input['api_key']);
        }

        if (isset($input['cache_duration'])) {
            $sanitized['cache_duration'] = absint($input['cache_duration']);
        }

        return $sanitized;
    }

    /**
     * Get default options
     *
     * @return array
     */
    private function get_default_options() {
        return [
            'ai_provider' => 'openai',
            'api_key' => '',
            'cache_duration' => 3600,
        ];
    }

    /**
     * Get setting value
     *
     * @param string $key Setting key.
     * @param mixed  $default Default value.
     * @return mixed
     */
    public function get_setting($key, $default = null) {
        $options = get_option('gl_cpg_options', $this->get_default_options());
        return $options[$key] ?? $default;
    }
}
