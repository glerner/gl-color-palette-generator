---
title: Implement Light/Dark Mode Theme Support
labels: enhancement, theme, accessibility
priority: high
status: proposed
created: 2024-12-25

---

# Implement Light/Dark Mode Theme Support

## Description
Add support for automatic light/dark mode switching using CSS `light-dark()` function in generated theme variations, following WordPress's new guidance on block theme styling.

## Background
WordPress now recommends using the CSS `light-dark()` function for theme color schemes, as detailed in:
https://developer.wordpress.org/news/2024/12/mastering-light-and-dark-mode-styling-in-block-themes/

## Implementation Requirements

### 1. Color Palette Generation
- Generate paired light/dark versions of each color
- Ensure both versions meet WCAG contrast requirements
- Maintain color relationships in both modes
- Use `Color_Constants::WCAG_CONTRAST_TARGET` for both modes

### 2. Theme.json Updates
```json
{
  "styles": {
    "color": {
      "background": "light-dark(var(--wp--preset--color--background-light), var(--wp--preset--color--background-dark))",
      "text": "light-dark(var(--wp--preset--color--text-light), var(--wp--preset--color--text-dark))"
    }
  }
}
```

### 3. Style Variations
- Generate both light and dark versions of each variation
- Create combined variations using `light-dark()`
- Update naming convention to indicate mode support

### 4. Color Utility Updates
- Add methods to generate light/dark pairs
- Implement contrast checking for both modes
- Add utility functions for `light-dark()` CSS generation

### 5. UI Updates
- Add preview toggle for light/dark modes
- Show contrast ratios for both modes
- Display paired colors side by side

## Technical Implementation

1. Color Generation:
```php
// Example approach
public function generate_light_dark_pair($base_color) {
    return [
        'light' => $this->optimize_for_light_mode($base_color),
        'dark' => $this->optimize_for_dark_mode($base_color)
    ];
}
```

2. Theme.json Generation:
```php
// Example structure
$theme_json = [
    'version' => 2,
    'settings' => [
        'color' => [
            'palette' => [
                [
                    'name' => 'Background',
                    'slug' => 'background',
                    'color' => [
                        'light' => '#ffffff',
                        'dark' => '#1a1a1a'
                    ]
                ]
            ]
        ]
    ]
];
```

## Files to Update

### 1. Theme JSON Generation
**File**: `includes/export/class-theme-json-generator.php`
- Update `generate_theme_json_variations()` to combine light/dark modes
- Modify `generate_color_palette()` to use `light-dark()`
- Update `get_base_theme_json()` to include mode-specific settings

Current:
```php
'color' => '#ff0000'
```

New:
```php
'color' => 'light-dark(#ff6666, #990000)'
```

### 2. Export System
**File**: `includes/export/class-color-export-system.php`
- Update `export_theme_json()` to handle combined light/dark modes
- Modify theme variation export to include mode-specific data

### 3. CSS Utilities
**File**: `includes/export/class-css-utilities.php`
- Add helper methods for generating `light-dark()` CSS
- Update color value formatting

### 4. Color Constants
**File**: `includes/interfaces/interface-color-constants.php`
- Add constants for light/dark mode preferences
- Define standard light/dark color pairs

### 5. Tests
**Files**:
- `tests/export/test-theme-json-generator.php`
- `tests/export/test-color-export-system.php`
- Update tests to verify `light-dark()` implementation
- Add tests for mode-specific color validation

## Example Theme.json Structure

```json
{
  "version": 2,
  "settings": {
    "color": {
      "palette": [
        {
          "slug": "primary",
          "name": "Primary",
          "color": "light-dark(var(--wp--preset--color--primary-light), var(--wp--preset--color--primary-dark))"
        },
        {
          "slug": "background",
          "name": "Background",
          "color": "light-dark(#ffffff, #1a1a1a)"
        }
      ]
    }
  },
  "styles": {
    "color": {
      "background": "light-dark(var(--wp--preset--color--background-light), var(--wp--preset--color--background-dark))",
      "text": "light-dark(var(--wp--preset--color--text-light), var(--wp--preset--color--text-dark))"
    }
  }
}
```

## Migration Steps
1. Update color generation to create light/dark pairs
2. Modify theme.json structure to use `light-dark()`
3. Update export system to handle new format
4. Add tests for light/dark mode support
5. Update documentation

## Backward Compatibility
- Maintain support for themes without `light-dark()`
- Provide fallback for older WordPress versions
- Document migration path for existing themes

## Testing Requirements
1. Verify contrast ratios in both modes
2. Test system preference detection
3. Test manual mode switching
4. Verify smooth transitions
5. Test across different browsers

## Success Criteria
- [ ] All colors meet WCAG contrast requirements in both modes
- [ ] Theme.json correctly implements `light-dark()`
- [ ] Style variations work in both modes
- [ ] Smooth transitions between modes
- [ ] Proper system preference detection
- [ ] Documentation updated

## Related
- Color accessibility checks
- Theme variation generation
- Style editor integration

## Notes
- Consider adding a preference override option
- Document best practices for theme developers
- Consider adding transition timing options
