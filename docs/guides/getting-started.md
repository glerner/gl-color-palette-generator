# Getting Started with GL Color Palette Generator

## Installation

### Via Composer

```bash
composer require gl/color-palette-generator
```

### Manual Installation

1. Download the latest release
2. Upload to your WordPress plugins directory
3. Activate through WordPress admin panel

## Basic Configuration

```php
// Initialize the plugin
add_action('plugins_loaded', function() {
    $config = [
        'api_key' => 'your_api_key',
        'cache_enabled' => true,
        'debug_mode' => false
    ];

    GL_Color_Palette_Generator::init($config);
});
```

## Quick Start

### Generate a Simple Palette

```php
// Create a basic palette
$generator = new Color_Palette_Generator();
$palette = $generator->generate_palette('#FF0000', [
    'colors' => 5,
    'harmony' => 'complementary'
]);
```

### Check Accessibility

```php
// Verify color accessibility
$checker = new Accessibility_Checker();
$is_accessible = $checker->check_contrast('#000000', '#FFFFFF');
```

### Save a Palette

```php
// Save palette for later use
$storage = new Palette_Storage_Manager();
$palette_id = $storage->save_palette([
    'name' => 'My First Palette',
    'colors' => ['#FF0000', '#00FF00', '#0000FF'],
    'tags' => ['basic', 'rgb']
]);
```

## Core Components

### Color Utility

```php
// Basic color manipulation
$utility = new Color_Utility();
$darker = $utility->darken('#FF0000', 20);
$saturated = $utility->saturate('#FF0000', 10);
```

### AI Integration

```php
// Generate AI-powered palette
$ai_generator = new AI_Provider();
$suggestion = $ai_generator->generate_suggestion([
    'base_color' => '#FF0000',
    'style' => 'modern',
    'context' => 'website'
]);
```

### Settings Management

```php
// Configure plugin settings
$settings = new Settings_Manager();
$settings->update('color_cache_ttl', 3600);
$settings->update('ai_provider', 'openai');
```

## WordPress Integration

### Admin Interface

```php
// Add color picker to admin
add_action('admin_enqueue_scripts', function() {
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('gl-color-palette',
        plugins_url('js/color-palette.js', __FILE__),
        ['wp-color-picker']
    );
});
```

### Shortcodes

```php
// Register palette display shortcode
add_shortcode('gl_palette', function($atts) {
    $palette_id = $atts['id'] ?? null;
    if (!$palette_id) return '';

    $storage = new Palette_Storage_Manager();
    $palette = $storage->get_palette($palette_id);

    return render_palette($palette);
});
```

## Common Use Cases

### Brand Color Management

```php
// Save brand colors
$brand_manager = new Brand_Color_Manager();
$brand_manager->set_primary_color('#FF0000');
$brand_manager->set_secondary_colors(['#00FF00', '#0000FF']);

// Generate brand-safe palettes
$palette = $generator->generate_brand_palette([
    'maintain_identity' => true,
    'accessibility' => 'AA'
]);
```

### Theme Integration

```php
// Add palette to theme customizer
add_action('customize_register', function($wp_customize) {
    $palette_control = new Palette_Customizer_Control(
        $wp_customize,
        'theme_palette',
        [
            'label' => 'Theme Colors',
            'section' => 'colors'
        ]
    );
});
```

## Next Steps

1. Explore [Advanced Features](advanced-features.md)
2. Review [Best Practices](best-practices.md)
3. Check [API Documentation](../API/README.md)

## Troubleshooting

### Common Issues

```php
// Debug color generation
$debug = new Debug_Manager();
$debug->analyze_generation_issue($palette_id, [
    'include_logs' => true,
    'trace_api_calls' => true
]);

// Validate configuration
$validator = new Config_Validator();
$issues = $validator->check_configuration([
    'check_permissions' => true,
    'verify_api_access' => true
]);
```

## See Also
- [Color Utility Documentation](../API/color-utility.md)
- [Accessibility Guide](wcag-guidelines.md)
- [Performance Optimization](optimization.md) 
