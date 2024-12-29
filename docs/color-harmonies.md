# Adding New Color Harmonies

The plugin's color harmony system is built around the color wheel and traditional color theory principles. This guide explains how to add new harmony types.

## Overview

Color harmonies are defined in two places:
1. Rules and angles in `interface-color-constants.php`
2. Calculation logic in `class-color-wheel.php`

## Implementation Steps

### 1. Define Harmony Rules

Add your harmony definition to `interface-color-constants.php`:

```php
const COLOR_HARMONY_RULES = [
    'your-harmony-type' => [
        'angle' => 60,  // Angle between colors on the wheel
    ],
];
```

### 2. Implement the Calculation

Add your harmony calculation method to `class-color-wheel.php`:

```php
private function calculate_your_harmony(array $hsl, array $options = []): array {
    $colors = [$this->color_utility->hsl_to_hex($hsl)];  // Start with base color
    
    // Get angle from constants or options
    $angle = $options['angle'] ?? 
        Color_Constants::COLOR_HARMONY_RULES['your-harmony-type']['angle'];
    
    // Calculate additional colors based on your harmony rules
    $new_hsl = $hsl;
    $new_hsl['h'] = ($hsl['h'] + $angle) % 360;  // Example calculation
    $colors[] = $this->color_utility->hsl_to_hex($new_hsl);
    
    return $colors;
}
```

### 3. Register the Harmony

Add your harmony type to the switch statement in `calculate_harmonies()`:

```php
switch ($harmony_type) {
    case 'your-harmony-type':
        $harmonies = $this->calculate_your_harmony($hsl, $options);
        break;
}
```

### 4. Testing

1. Add unit tests in `tests/color-management/test-class-color-wheel.php`
2. Test with various base colors
3. Test with different angle options

## Best Practices

1. Follow established color theory principles
2. Use meaningful angles based on color wheel theory
3. Document the psychological/emotional impact of the harmony
4. Consider cultural implications of color combinations

## Example: Split-Complementary

Here's a real example of implementing split-complementary harmony:

```php
// In interface-color-constants.php
const COLOR_HARMONY_RULES = [
    'split-complementary' => [
        'angle' => 30,  // Split angle from complement
    ],
];

// In class-color-wheel.php
private function calculate_split_complementary(array $hsl, array $options = []): array {
    $colors = [$this->color_utility->hsl_to_hex($hsl)];
    $angle = Color_Constants::COLOR_HARMONY_RULES['split-complementary']['angle'];
    
    // Get complementary color
    $comp_hsl = $hsl;
    $comp_hsl['h'] = ($hsl['h'] + 180) % 360;
    
    // Add split colors
    for ($i = -1; $i <= 1; $i += 2) {
        $new_hsl = $comp_hsl;
        $new_hsl['h'] = ($comp_hsl['h'] + ($angle * $i) + 360) % 360;
        $colors[] = $this->color_utility->hsl_to_hex($new_hsl);
    }
    
    return $colors;
}
```
