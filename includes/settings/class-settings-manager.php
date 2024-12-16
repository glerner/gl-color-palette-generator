<?php
declare(strict_types=1);

/**
 * Settings Manager Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Settings
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Settings;

use GL_Color_Palette_Generator\Exceptions\Palette_Generation_Exception;
use GL_Color_Palette_Generator\Settings\Settings_Migrator;
use GL_Color_Palette_Generator\Settings\Settings_Validator;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Settings_Manager
 */
class Settings_Manager {
    /**
     * Settings options
     *
     * @var array
     */
    private array $options;

    /**
     * Settings fields
     *
     * @var array
     */
    private array $fields;

    /**
     * Constructor
     */
    public function __construct() {
        $this->fields = Settings_Types::get_field_definitions();
        $this->options = $this->get_options();
    }

    /**
     * Initialize the settings
     *
     * @return void
     */
    public function init(): void {
        // Run migrations if needed
        Settings_Migrator::maybe_migrate();
        
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Initialize default settings
     *
     * @return void
     */
    public static function init_settings(): void {
        $default_settings = Settings_Types::DEFAULT_SETTINGS;
        
        // Only set defaults if they don't exist
        if (!get_option('gl_cpg_options')) {
            update_option('gl_cpg_options', $default_settings);
        }
    }

    /**
     * Register settings
     *
     * @return void
     */
    public function register_settings(): void {
        register_setting(
            'gl_cpg_settings',
            'gl_cpg_options',
            [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitize_settings'],
                'default' => Settings_Types::DEFAULT_SETTINGS,
            ]
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
     *
     * @return void
     */
    private function add_settings_fields(): void {
        foreach ($this->fields as $key => $field) {
            add_settings_field(
                'gl_cpg_' . $key,
                $field['title'],
                [$this, 'render_field'],
                'gl_cpg_settings',
                'gl_cpg_main_section',
                [
                    'key' => $key,
                    'field' => $field,
                ]
            );
        }
    }

    /**
     * Render settings section
     *
     * @return void
     */
    public function render_section(): void {
        echo '<p>' . esc_html__('Configure your color palette generator settings below.', 'gl-color-palette-generator') . '</p>';
    }

    /**
     * Render settings field
     *
     * @param array $args Field arguments
     * @return void
     */
    public function render_field(array $args): void {
        $key = $args['key'];
        $field = $args['field'];
        $value = $this->get_option($key);
        $name = 'gl_cpg_options[' . $key . ']';
        $id = 'gl_cpg_' . $key;

        switch ($field['type']) {
            case 'select':
                echo '<select id="' . esc_attr($id) . '" name="' . esc_attr($name) . '">';
                foreach ($field['options'] as $option_key => $option_label) {
                    echo '<option value="' . esc_attr($option_key) . '" ' . selected($value, $option_key, false) . '>';
                    echo esc_html($option_label);
                    echo '</option>';
                }
                echo '</select>';
                break;

            case 'password':
                echo '<input type="password" id="' . esc_attr($id) . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" class="regular-text">';
                break;

            case 'checkbox':
                echo '<input type="checkbox" id="' . esc_attr($id) . '" name="' . esc_attr($name) . '" value="1" ' . checked($value, true, false) . '>';
                break;

            case 'number':
                echo '<input type="number" id="' . esc_attr($id) . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '"';
                if (isset($field['min'])) {
                    echo ' min="' . esc_attr($field['min']) . '"';
                }
                if (isset($field['max'])) {
                    echo ' max="' . esc_attr($field['max']) . '"';
                }
                echo ' class="small-text">';
                break;

            default:
                echo '<input type="text" id="' . esc_attr($id) . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" class="regular-text">';
        }

        if (!empty($field['description'])) {
            echo '<p class="description">' . esc_html($field['description']) . '</p>';
        }
    }

    /**
     * Sanitize settings
     *
     * @param array $input Input values
     * @return array Sanitized values
     * @throws Palette_Generation_Exception If validation fails
     */
    public function sanitize_settings(array $input): array {
        $output = [];

        foreach ($this->fields as $key => $field) {
            if (!isset($input[$key]) && $field['required']) {
                throw new Palette_Generation_Exception(
                    sprintf(__('Field %s is required', 'gl-color-palette-generator'), $field['title']),
                    'SETTINGS_VALIDATION_ERROR'
                );
            }

            $value = $input[$key] ?? Settings_Types::DEFAULT_SETTINGS[$key];

            // Type casting based on field type
            switch ($field['type']) {
                case 'number':
                    $value = (int) $value;
                    break;
                case 'checkbox':
                    $value = (bool) $value;
                    break;
                default:
                    $value = (string) $value;
            }

            // Validate using field validator
            if (!call_user_func($field['validator'], $value)) {
                throw new Palette_Generation_Exception(
                    sprintf(__('Invalid value for %s', 'gl-color-palette-generator'), $field['title']),
                    'SETTINGS_VALIDATION_ERROR'
                );
            }

            $output[$key] = $value;
        }

        // Run complex validation
        Settings_Validator::validate_settings($output);

        return $output;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function get_options(): array {
        $options = get_option('gl_cpg_options', []);
        return wp_parse_args($options, Settings_Types::DEFAULT_SETTINGS);
    }

    /**
     * Get option
     *
     * @param string $key Option key
     * @return mixed
     */
    public function get_option(string $key) {
        return $this->options[$key] ?? Settings_Types::DEFAULT_SETTINGS[$key] ?? null;
    }

    /**
     * Update option
     *
     * @param string $key Option key
     * @param mixed $value Option value
     * @return bool
     * @throws Palette_Generation_Exception If validation fails
     */
    public function update_option(string $key, $value): bool {
        if (!isset($this->fields[$key])) {
            throw new Palette_Generation_Exception(
                sprintf(__('Invalid setting key: %s', 'gl-color-palette-generator'), $key),
                'SETTINGS_VALIDATION_ERROR'
            );
        }

        $field = $this->fields[$key];
        
        // Type casting based on field type
        switch ($field['type']) {
            case 'number':
                $value = (int) $value;
                break;
            case 'checkbox':
                $value = (bool) $value;
                break;
            default:
                $value = (string) $value;
        }

        // Validate using field validator
        if (!call_user_func($field['validator'], $value)) {
            throw new Palette_Generation_Exception(
                sprintf(__('Invalid value for %s', 'gl-color-palette-generator'), $field['title']),
                'SETTINGS_VALIDATION_ERROR'
            );
        }

        // Create temporary settings array for cross-field validation
        $temp_settings = $this->options;
        $temp_settings[$key] = $value;
        Settings_Validator::validate_settings($temp_settings);

        $this->options[$key] = $value;
        return update_option('gl_cpg_options', $this->options);
    }
}
