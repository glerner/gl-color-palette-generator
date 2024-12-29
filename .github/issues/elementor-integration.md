# Add Elementor Color Integration

## Description
Add support for exporting color palettes to Elementor's global colors system.

## Implementation Details
Elementor stores global colors in the WordPress database using the `elementor_global_colors` option. The format is:

```php
[
  'id' => 'primary',
  'title' => 'Primary',
  'color' => '#1e88e5'
]
```

We need to:
1. Create a new export format `elementor`
2. Add option to sync with Elementor's global colors
3. Add filter for color names mapping

## References
- [Elementor Developer Docs](https://developers.elementor.com/)
- [Global Colors API](https://developers.elementor.com/global-colors/)
