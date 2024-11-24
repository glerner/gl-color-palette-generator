# Color Utility

## Overview
The Color Utility class provides essential color conversion and manipulation functions.

## Features
- Color space conversions (RGB, HSL, CMYK, LAB)
- Color manipulation (lighten, darken, saturate)
- Color validation and normalization
- Mathematical color operations

## Usage Examples

```php
$utility = new Color_Utility();

// Color Space Conversions
$rgb = $utility->hex_to_rgb('#FF0000');
print_r($rgb); // Array('r' => 255, 'g' => 0, 'b' => 0)

$hsl = $utility->rgb_to_hsl($rgb);
print_r($hsl); // Array('h' => 0, 's' => 100, 'l' => 50)

$cmyk = $utility->rgb_to_cmyk($rgb);
print_r($cmyk); // Array('c' => 0, 'm' => 100, 'y' => 100, 'k' => 0)

// Color Manipulation
$lighter = $utility->lighten('#FF0000', 20);
$darker = $utility->darken('#FF0000', 20);
$saturated = $utility->saturate('#FF0000', 20);

// Validation
if ($utility->is_valid_hex('#FF0000')) {
    $normalized = $utility->normalize_hex('#f00');
    echo $normalized; // #FF0000
}
```

## API Reference

### Color Conversion Methods

#### hex_to_rgb($hex)
Converts hexadecimal color to RGB values.

#### rgb_to_hsl($rgb)
Converts RGB color to HSL values.

#### rgb_to_cmyk($rgb)
Converts RGB color to CMYK values.

#### rgb_to_lab($rgb)
Converts RGB color to LAB values.

### Color Manipulation Methods

#### lighten($color, $amount)
Lightens a color by the specified percentage.

#### darken($color, $amount)
Darkens a color by the specified percentage.

#### saturate($color, $amount)
Increases color saturation by the specified percentage.

### Validation Methods

#### is_valid_hex($hex)
Checks if a hex color code is valid.

#### normalize_hex($hex)
Normalizes shorthand hex codes to full format.

## Error Handling

```php
try {
    $rgb = $utility->hex_to_rgb('invalid');
} catch (InvalidColorException $e) {
    echo 'Invalid color format: ' . $e->getMessage();
}
```

## Integration Examples

```php
// WordPress Color Picker Integration
add_action('admin_enqueue_scripts', function() {
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');

    // Your color utility integration code
    $utility = new Color_Utility();
    // ...
});
```

## Performance Considerations

### Caching
Color conversions are automatically cached for improved performance:

```php
// First conversion - computed
$rgb1 = $utility->hex_to_rgb('#FF0000');

// Second conversion - retrieved from cache
$rgb2 = $utility->hex_to_rgb('#FF0000');
```

### Batch Processing
Process multiple colors efficiently:

```php
$colors = ['#FF0000', '#00FF00', '#0000FF'];
$batch_results = $utility->batch_convert_colors($colors, 'rgb');
```

## Best Practices

### Input Validation
Always validate color inputs:

```php
if (!$utility->is_valid_hex($color)) {
    throw new InvalidColorException('Invalid hex color code');
}
```

### Error Handling
Implement proper error handling:

```php
try {
    $rgb = $utility->hex_to_rgb($color);
    $hsl = $utility->rgb_to_hsl($rgb);
} catch (ColorConversionException $e) {
    // Handle conversion errors
} catch (InvalidColorException $e) {
    // Handle invalid color format
}
```

## Advanced Usage

### Custom Color Spaces

```php
// Convert to custom color space
$custom = $utility->convert_color_space('#FF0000', 'custom', [
    'gamma' => 2.2,
    'white_point' => 'D65'
]);
```

### Color Distance Calculations

```php
// Calculate color difference
$distance = $utility->calculate_color_distance('#FF0000', '#FF0100', 'cie2000');
```

### Gamut Mapping

```php
// Map colors to specific gamut
$mapped = $utility->map_to_gamut('#FF0000', [
    'space' => 'srgb',
    'method' => 'clipping'
]);
```

## Troubleshooting

Common issues and their solutions:

1. Invalid Color Formats
```php
// Fix invalid hex codes
$fixed = $utility->normalize_hex($invalid_hex);
```

2. Color Space Conversion Issues
```php
// Check if color is in gamut
if ($utility->is_in_gamut($color, 'srgb')) {
    // Process color
}
```

## See Also
- [Color Metrics Analyzer Documentation](color-metrics-analyzer.md)
- [Color Palette Generator Documentation](color-palette-generator.md)
- [Media Analysis Documentation](media-analysis.md)

## Unit Conversion Tables

### RGB to Other Formats

| RGB | HEX | HSL | CMYK |
|-----|-----|-----|------|
| rgb(255, 0, 0) | #FF0000 | hsl(0, 100%, 50%) | cmyk(0, 100, 100, 0) |
| rgb(0, 255, 0) | #00FF00 | hsl(120, 100%, 50%) | cmyk(100, 0, 100, 0) |
| rgb(0, 0, 255) | #0000FF | hsl(240, 100%, 50%) | cmyk(100, 100, 0, 0) |

### Color Space Ranges

| Format | Range | Example |
|--------|-------|---------|
| RGB | 0-255 | rgb(255, 255, 255) |
| HSL | H: 0-360, S: 0-100%, L: 0-100% | hsl(360, 100%, 100%) |
| CMYK | 0-100% | cmyk(100%, 100%, 100%, 100%) |
| LAB | L: 0-100, a: -128-127, b: -128-127 | lab(100, 0, 0) |

## Color Space Comparison

### Advantages and Disadvantages

| Color Space | Pros | Cons | Best For |
|------------|------|------|-----------|
| RGB | Web standard, Direct display | Not intuitive | Digital display |
| HSL | Intuitive, Easy to adjust | Not perceptually uniform | UI/UX design |
| CMYK | Print accurate | Limited gamut | Print design |
| LAB | Perceptually uniform | Complex calculations | Color matching |

### Gamut Coverage

```php
// Check if color is in gamut
$in_gamut = $utility->is_in_gamut('#FF0000', 'srgb');

// Get gamut coverage percentage
$coverage = $utility->calculate_gamut_coverage('#FF0000', [
    'srgb' => true,
    'adobe_rgb' => true,
    'p3' => true
]);
```

## Optimization Tips

### Caching Strategies

```php
// Enable conversion caching
$utility->enable_caching([
    'conversions' => true,
    'calculations' => true,
    'ttl' => 3600
]);

// Batch convert colors
$colors = ['#FF0000', '#00FF00', '#0000FF'];
$converted = $utility->batch_convert($colors, 'rgb', 'hsl');
```

### Performance Optimization

1. Use batch operations for multiple colors
2. Enable caching for frequent conversions
3. Choose appropriate color spaces for operations
4. Implement lazy loading for complex calculations

### Memory Usage

```php
// Monitor memory usage
$stats = $utility->get_performance_stats();

// Configure batch processing
$utility->set_batch_size(100);
$utility->set_memory_limit('128M');
```

### Optimization Examples

```php
// Optimize for performance
$utility->optimize_for_performance([
    'enable_cache' => true,
    'batch_size' => 50,
    'lazy_load' => true
]);

// Optimize for accuracy
$utility->optimize_for_accuracy([
    'high_precision' => true,
    'use_lab' => true
]);
```

## Color Space Conversion Pipeline

```php
// Define custom conversion pipeline
$pipeline = $utility->create_conversion_pipeline([
    'rgb_to_lab',
    'lab_to_lch',
    'lch_to_hsl'
]);

// Process color through pipeline
$result = $pipeline->process('#FF0000');
```

## See Also
- [Color Space Fundamentals](../guides/color-spaces.md)
- [Performance Optimization Guide](../guides/optimization.md)
- [Color Management Best Practices](../guides/best-practices.md)
