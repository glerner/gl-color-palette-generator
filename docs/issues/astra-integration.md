# Add Astra Theme Integration

## Description
Add support for exporting color palettes to Astra Theme's global colors.

## Implementation Details
Astra Theme uses the WordPress customizer and a specific format for global colors:

```php
[
  'global-color-palette' => [
    [
      'slug' => 'palette-1',
      'color' => '#1e88e5',
      'name' => 'Primary'
    ]
  ]
]
```

We need to:
1. Create a new export format `astra`
2. Add integration with Astra Theme customizer
3. Support both Astra Pro and free version
4. Add filter for color mapping

## References
- [Astra Developer Docs](https://wpastra.com/developers/)
- [Theme Customizer API](https://developer.wordpress.org/themes/customize-api/)
