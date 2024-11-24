# WCAG Guidelines Reference

## Overview
This guide explains the Web Content Accessibility Guidelines (WCAG) as they relate to color usage in the GL Color Palette Generator.

## Contrast Requirements

### Text Contrast

| Content Type | WCAG AA | WCAG AAA |
|-------------|---------|-----------|
| Normal Text (< 18pt) | 4.5:1 | 7:1 |
| Large Text (≥ 18pt) | 3:1 | 4.5:1 |
| UI Components | 3:1 | Not defined |

### Implementation Examples

```php
// Check text contrast
$checker = new Accessibility_Checker();
$ratio = $checker->check_text_contrast('#000000', '#FFFFFF', [
    'size' => 'normal',
    'weight' => 'regular'
]);

// Check UI component contrast
$ui_ratio = $checker->check_component_contrast('#FF0000', '#FFFFFF');
```

## Color Vision Deficiency

### Types of Color Blindness

1. **Protanopia** (Red-blind)
2. **Deuteranopia** (Green-blind)
3. **Tritanopia** (Blue-blind)
4. **Achromatopsia** (Complete color blindness)

### Testing Requirements

```php
// Test for all CVD types
$cvd_test = $checker->test_color_vision_deficiency('#FF0000', [
    'types' => ['protanopia', 'deuteranopia', 'tritanopia'],
    'background' => '#FFFFFF'
]);
```

## Success Criteria

### 1.4.3 Contrast (Minimum)
- Level AA
- Applies to text and images of text
- Minimum contrast ratio of 4.5:1

### 1.4.6 Contrast (Enhanced)
- Level AAA
- Applies to text and images of text
- Minimum contrast ratio of 7:1

### 1.4.11 Non-text Contrast
- Level AA
- Applies to UI components and graphical objects
- Minimum contrast ratio of 3:1

## Best Practices

1. **Always Test Both Foreground and Background**
```php
$palette_test = $checker->test_palette_combination([
    'foreground' => '#000000',
    'background' => '#FFFFFF',
    'ui_elements' => '#FF0000'
]);
```

2. **Consider Multiple Visual Conditions**
```php
$accessibility_test = $checker->test_visual_conditions([
    'lighting' => ['bright', 'dim'],
    'distance' => ['normal', 'far'],
    'motion' => true
]);
```

3. **Provide Alternative Color Schemes**
```php
$alternatives = $checker->generate_accessible_alternatives('#FF0000', [
    'maintain_brand' => true,
    'wcag_level' => 'AAA'
]);
```

## Tools and Resources

### Built-in Tools

```php
// Contrast checker
$contrast = $checker->quick_contrast_check('#000000', '#FFFFFF');

// Color blindness simulator
$simulation = $checker->simulate_color_blindness('#FF0000', 'protanopia');

// Accessibility report
$report = $checker->generate_accessibility_report($palette);
```

### External Resources

1. WebAIM Contrast Checker
2. Colorblinding Chrome Extension
3. WCAG 2.1 Official Documentation

## Common Issues and Solutions

### Problem: Insufficient Text Contrast

```php
// Find minimum acceptable background color
$suggested_background = $checker->find_acceptable_background('#FF0000', [
    'wcag_level' => 'AA',
    'text_size' => 'normal'
]);
```

### Problem: Color-Only Information

```php
// Generate patterns for color differentiation
$patterns = $checker->generate_patterns_for_colors([
    '#FF0000' => 'dots',
    '#00FF00' => 'lines',
    '#0000FF' => 'crosses'
]);
```

## See Also
- [Accessibility Checker Documentation](../API/accessibility-checker.md)
- [Color Metrics Analyzer Documentation](../API/color-metrics-analyzer.md)
- [Official WCAG Documentation](https://www.w3.org/WAI/WCAG21/quickref/) 
