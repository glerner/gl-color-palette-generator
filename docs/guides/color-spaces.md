# Color Space Fundamentals

## Overview
Understanding color spaces is crucial for accurate color management and reproduction across different media.

## Common Color Spaces

### RGB (Red, Green, Blue)

```php
// RGB color space examples
$rgb = $utility->convert_to_rgb('#FF0000');
print_r($rgb); // Array('r' => 255, 'g' => 0, 'b' => 0)

// RGB color manipulation
$lighter = $utility->adjust_rgb($rgb, [
    'r' => +20,
    'g' => +20,
    'b' => +20
]);
```

### HSL (Hue, Saturation, Lightness)

```php
// HSL color space examples
$hsl = $utility->convert_to_hsl('#FF0000');
print_r($hsl); // Array('h' => 0, 's' => 100, 'l' => 50)

// HSL color manipulation
$variant = $utility->adjust_hsl($hsl, [
    'h' => +30,    // Shift hue
    's' => -10,    // Reduce saturation
    'l' => +5      // Increase lightness
]);
```

### CMYK (Cyan, Magenta, Yellow, Key/Black)

```php
// CMYK color space examples
$cmyk = $utility->convert_to_cmyk('#FF0000');
print_r($cmyk); // Array('c' => 0, 'm' => 100, 'y' => 100, 'k' => 0)

// CMYK color manipulation
$print_safe = $utility->adjust_cmyk($cmyk, [
    'total_ink' => 300,  // Maximum ink coverage
    'preserve_black' => true
]);
```

## Color Space Conversion

### Conversion Accuracy

| From/To | RGB | HSL | CMYK | LAB |
|---------|-----|-----|------|-----|
| RGB | - | High | Medium | High |
| HSL | High | - | Medium | Medium |
| CMYK | Medium | Medium | - | Medium |
| LAB | High | Medium | Medium | - |

### Conversion Examples

```php
// Convert between spaces
$color = new Color('#FF0000');

$rgb = $color->to_rgb();
$hsl = $color->to_hsl();
$cmyk = $color->to_cmyk();
$lab = $color->to_lab();

// Round-trip conversion
$original = $color->to_cmyk()->to_rgb()->to_hex();
```

## Gamut Considerations

### Gamut Mapping

```php
// Check if color is in gamut
$in_gamut = $utility->is_in_gamut('#FF0000', 'srgb');

// Map out-of-gamut colors
$mapped = $utility->map_to_gamut('#FF0000', [
    'target_space' => 'srgb',
    'method' => 'perceptual'
]);
```

### Common Gamuts

| Color Space | Gamut Size | Best For |
|------------|------------|----------|
| sRGB | Smallest | Web/Screen |
| Adobe RGB | Medium | Photography |
| ProPhoto RGB | Largest | Professional |

## Color Management

### ICC Profiles

```php
// Apply ICC profile
$managed = $utility->apply_icc_profile('#FF0000', [
    'profile' => 'sRGB IEC61966-2.1',
    'intent' => 'perceptual'
]);

// Convert between profiles
$converted = $utility->convert_between_profiles(
    '#FF0000',
    'sRGB IEC61966-2.1',
    'Adobe RGB (1998)'
);
```

### Device-Independent Color

```php
// Convert to device-independent space
$lab = $utility->to_device_independent('#FF0000');

// Convert back to device space
$rgb = $utility->to_device_space($lab, [
    'space' => 'sRGB',
    'intent' => 'relative_colorimetric'
]);
```

## Best Practices

### Color Space Selection

1. **Web Content**
```php
// Optimize for web
$web_safe = $utility->optimize_for_web('#FF0000', [
    'space' => 'sRGB',
    'format' => 'hex'
]);
```

2. **Print Production**
```php
// Optimize for print
$print_safe = $utility->optimize_for_print('#FF0000', [
    'space' => 'CMYK',
    'total_ink' => 300
]);
```

3. **Cross-Media**
```php
// Create cross-media safe colors
$universal = $utility->create_universal_color('#FF0000', [
    'spaces' => ['sRGB', 'CMYK', 'LAB'],
    'preserve_appearance' => true
]);
```

## See Also
- [Color Utility Documentation](../API/color-utility.md)
- [Color Metrics Analyzer Documentation](../API/color-metrics-analyzer.md)
- [Media Analysis Documentation](../API/media-analysis.md) 
