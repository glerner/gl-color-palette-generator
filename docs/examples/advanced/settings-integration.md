# Advanced Settings Integration Examples

## Custom Settings Page

```php
class Custom_Settings_Page {
    private $settings;

    public function __construct() {
        $this->settings = new Settings_Manager();
        add_action('admin_menu', [$this, 'register_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function register_page() {
        add_menu_page(
            'Color Palette Settings',
            'Color Settings',
            'manage_options',
            'color-settings',
            [$this, 'render_page'],
            'dashicons-art'
        );
    }

    public function render_page() {
        // Custom rendering logic
        include plugin_dir_path(__FILE__) . 'templates/settings-page.php';
    }

    public function register_settings() {
        // Register custom settings sections and fields
        $this->settings->register_custom_settings([
            'sections' => [
                'general' => [
                    'title' => 'General Settings',
                    'fields' => [
                        'default_palette_size' => [
                            'type' => 'number',
                            'label' => 'Default Palette Size',
                            'default' => 5
                        ],
                        'color_format' => [
                            'type' => 'select',
                            'label' => 'Color Format',
                            'options' => [
                                'hex' => 'Hexadecimal',
                                'rgb' => 'RGB',
                                'hsl' => 'HSL'
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }
}
```

## Settings Validation and Sanitization

```php
// Add custom validation
$settings->add_validator('api_key', function($value) {
    if (empty($value)) {
        add_settings_error(
            'api_key',
            'api_key_error',
            'API Key is required'
        );
        return false;
    }
    return $value;
});

// Add custom sanitization
$settings->add_sanitizer('color_format', function($value) {
    $allowed = ['hex', 'rgb', 'hsl'];
    return in_array($value, $allowed) ? $value : 'hex';
});
``` 
