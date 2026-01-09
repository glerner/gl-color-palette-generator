# Add Divi Builder Integration

## Description
Add support for exporting color palettes to Divi Builder's global colors.

## Implementation Details
Divi stores global colors in the WordPress database using theme mods and a custom format:

```php
[
  'primary' => [
    'value' => '#1e88e5',
    'name' => 'Primary Color'
  ]
]
```

We need to:
1. Create a new export format `divi`
2. Add integration with Divi Theme Builder
3. Support Divi's color management system
4. Add filter for color name mapping

## References
- [Divi Developer Docs](https://www.elegantthemes.com/documentation/developers/)
- [Theme Mods API](https://developer.wordpress.org/themes/customize-api/)
