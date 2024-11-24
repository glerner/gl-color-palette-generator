# Color Palette Generator

## Overview
The Color Palette Generator creates harmonious color combinations using advanced color theory and AI-powered suggestions.

## Features
- AI-powered palette generation
- Color harmony rules
- Accessibility compliance
- Print/digital optimization
- Brand color integration

## Basic Usage

```php
$generator = new Color_Palette_Generator();

// Generate from base color
$palette = $generator->generate_palette('#FF0000', [
    'colors' => 5,
    'harmony' => 'complementary',
    'accessibility' => 'wcag_aa'
]);

// Generate with AI suggestions
$ai_palette = $generator->generate_ai_palette('#FF0000', [
    'style' => 'modern',
    'context' => 'corporate',
    'mood' => 'professional'
]);

// Generate with constraints
$constrained = $generator->generate_palette('#FF0000', [
    'min_contrast' => 4.5,
    'print_safe' => true,
    'color_blind_safe' => true
]);
```

## Configuration Options

### Harmony Rules
```php
$harmonies = [
    'complementary',
    'analogous',
    'triadic',
    'split_complementary',
    'tetradic',
    'monochromatic'
];

// Generate using specific harmony
$palette = $generator->generate_palette('#FF0000', [
    'harmony' => $harmonies[0]
]);
```

### Accessibility Settings
```php
$accessibility = [
    'wcag_level' => 'AAA',
    'color_blind_safe' => true,
    'minimum_contrast' => 7
];

// Generate accessible palette
$palette = $generator->generate_palette('#FF0000', [
    'accessibility' => $accessibility
]);
```

## Advanced Features

### AI Integration

```php
// Use specific AI provider
$palette = $generator->generate_ai_palette('#FF0000', [
    'provider' => 'openai',
    'model' => 'gpt-4',
    'temperature' => 0.7
]);

// Get AI suggestions
$suggestions = $generator->get_ai_suggestions('#FF0000', [
    'style' => 'minimalist',
    'industry' => 'technology'
]);
```

### Batch Generation

```php
$base_colors = ['#FF0000', '#00FF00', '#0000FF'];
$batch_palettes = $generator->batch_generate_palettes($base_colors, [
    'colors_per_palette' => 5,
    'harmony' => 'complementary'
]);
```

## Error Handling

```php
try {
    $palette = $generator->generate_palette('#FF0000');
} catch (GenerationException $e) {
    // Handle generation errors
} catch (ValidationException $e) {
    // Handle validation errors
} catch (AIProviderException $e) {
    // Handle AI provider errors
}
```

## Integration Examples

### WordPress Integration
```php
add_action('init', function() {
    $generator = new Color_Palette_Generator();

    // Register palette post type
    register_post_type('color_palette', [
        'public' => true,
        'label' => 'Color Palettes'
    ]);
});
```

## See Also
- [Color Utility Documentation](color-utility.md)
- [Color Metrics Analyzer Documentation](color-metrics-analyzer.md)
- [AI Provider Documentation](ai-provider-integration.md)

## Performance Optimization

### Caching Strategies

```php
// Enable result caching
$generator->enable_caching([
    'ttl' => 3600,  // Cache for 1 hour
    'max_items' => 100
]);

// Generate with cache
$cached_palette = $generator->generate_palette('#FF0000', [
    'use_cache' => true
]);
```

### Batch Processing

```php
// Process multiple palettes efficiently
$settings = [
    'modern' => ['style' => 'modern', 'colors' => 5],
    'vintage' => ['style' => 'vintage', 'colors' => 4],
    'minimal' => ['style' => 'minimal', 'colors' => 3]
];

$batch_results = $generator->batch_generate([
    'base_color' => '#FF0000',
    'variations' => $settings
]);
```

## Export/Import

### Export Formats

```php
// Export palette to different formats
$palette = $generator->generate_palette('#FF0000');

$css = $generator->export_palette($palette, 'css');
$scss = $generator->export_palette($palette, 'scss');
$json = $generator->export_palette($palette, 'json');
```

### Import Options

```php
// Import from various sources
$from_css = $generator->import_palette($css_string, 'css');
$from_json = $generator->import_palette($json_string, 'json');
$from_file = $generator->import_palette('palette.scss', 'file');
```

## AI Provider Comparison

### Provider Features

| Provider | Models | Specialties | Cost | Response Time |
|----------|--------|-------------|------|---------------|
| OpenAI | GPT-4, GPT-3.5 | General purpose, Creative | $$$ | Fast |
| Anthropic | Claude | Technical accuracy | $$ | Medium |
| PaLM | PaLM 2 | Pattern recognition | $$ | Fast |
| Custom ML | Various | Specialized tasks | $ | Fastest |

### Integration Examples

```php
// OpenAI integration
$palette = $generator->generate_ai_palette('#FF0000', [
    'provider' => 'openai',
    'model' => 'gpt-4',
    'temperature' => 0.7
]);

// Anthropic integration
$palette = $generator->generate_ai_palette('#FF0000', [
    'provider' => 'anthropic',
    'model' => 'claude-2',
    'max_tokens_to_sample' => 150
]);
```

## Generation Algorithms Explained

### Color Harmony Methods

```php
// Complementary colors
$complementary = $generator->generate_complementary('#FF0000');

// Analogous colors with custom angle
$analogous = $generator->generate_analogous('#FF0000', [
    'angle' => 30,
    'count' => 3
]);

// Custom harmony rules
$custom = $generator->generate_custom_harmony('#FF0000', [
    'angles' => [60, 120, 180],
    'saturation_variance' => 0.1,
    'lightness_variance' => 0.2
]);
```

### Algorithm Types

1. **Rule-Based Generation**
   - Color wheel mathematics
   - Predefined harmony rules
   - Contrast calculations

2. **AI-Assisted Generation**
   - Pattern recognition
   - Context awareness
   - Style matching

3. **Hybrid Approaches**
   - AI-enhanced harmony rules
   - Context-aware adjustments
   - Accessibility optimization

### Performance Metrics

| Algorithm | Speed | Quality | Memory Usage |
|-----------|-------|---------|--------------|
| Rule-Based | Fast | Good | Low |
| AI-Assisted | Slow | Excellent | High |
| Hybrid | Medium | Very Good | Medium |

## Customization Guide

### Custom Generators

```php
// Create custom generator
class Brand_Palette_Generator extends Base_Generator {
    protected function generate_palette($base_color, $options) {
        // Custom generation logic
        return [
            'primary' => $base_color,
            'secondary' => $this->generate_secondary($base_color),
            'accent' => $this->generate_accent($base_color)
        ];
    }
}

// Register custom generator
$generator->register_generator('brand', Brand_Palette_Generator::class);
```

### Custom Rules

```php
// Add custom harmony rule
$generator->add_harmony_rule('brand_specific', function($color) {
    return [
        'primary' => $color,
        'secondary' => adjust_hue($color, 180),
        'accent' => adjust_saturation($color, -20)
    ];
});

// Add custom constraints
$generator->add_constraint('brand_guidelines', function($palette) {
    // Check against brand guidelines
    return $this->validate_brand_colors($palette);
});
```

### Extension Points

```php
// Add generation filter
add_filter('palette_generation_result', function($palette, $base_color) {
    // Modify generated palette
    return $palette;
}, 10, 2);

// Add pre-generation hook
add_action('before_palette_generation', function($options) {
    // Prepare for generation
});

// Add post-generation hook
add_action('after_palette_generation', function($palette, $stats) {
    // Process generation results
}, 10, 2);
```
