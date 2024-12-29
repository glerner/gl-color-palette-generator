# Add Kadence Theme Integration

## Description
Add support for exporting color palettes to Kadence Theme's color system.

## Implementation Details
Kadence Theme uses a custom format for storing global colors in the customizer:

```php
[
  'palette' => [
    [
      'color' => '#1e88e5',
      'name' => 'Primary',
      'slug' => 'palette1'
    ]
  ]
]
```

We need to:
1. Create a new export format `kadence`
2. Add integration with Kadence Theme customizer
3. Support Kadence Blocks color palette
4. Add filter for color mapping

## References
- [Kadence Developer Docs](https://www.kadencewp.com/developers/)
- [Theme Customizer API](https://developer.wordpress.org/themes/customize-api/)
