# Color Palette Export Formats

This document explains the available export formats and how to use them effectively in your WordPress themes and plugins.

## Basic Formats

### CSS Custom Properties
```css
:root {
  --color-primary: #1e88e5;
  --color-secondary: #ff4081;
}
```
Use in your CSS:
```css
.button {
  background-color: var(--color-primary);
  color: white;
}
```

### SCSS Variables
```scss
$color-primary: #1e88e5;
$color-secondary: #ff4081;
```
Use in your SCSS:
```scss
.button {
  background-color: $color-primary;
  color: white;
}
```

### JSON
```json
{
  "primary": "#1e88e5",
  "secondary": "#ff4081"
}
```
Perfect for:
- API responses
- JavaScript applications
- Data storage

## Framework Integration

### Tailwind CSS
```js
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      colors: {
        'primary': '#1e88e5',
        'secondary': '#ff4081'
      }
    }
  }
}
```
Use in your HTML:
```html
<button class="bg-primary text-white">
  Click me
</button>
```

### Bootstrap
```scss
// Custom color variables
$color-primary: #1e88e5;
$color-secondary: #ff4081;

// Theme colors
$theme-colors: (
  'primary': $color-primary,
  'secondary': $color-secondary
);
```
Use in your HTML:
```html
<button class="btn btn-primary">
  Click me
</button>
```

## Visual Formats

### SVG Swatches
The SVG export format is versatile and can be used in many ways:

1. Color Swatch Display
```php
$options = [
    'width' => 300,
    'height' => 50,
    'spacing' => 2,
    'rounded' => 4
];
$svg = $exporter->export($palette, 'svg', $options);
```

2. UI Elements
```php
// Create a button background
$options = [
    'width' => 120,
    'height' => 40,
    'rounded' => 20  // Fully rounded ends
];
$svg = $exporter->export($palette, 'svg', $options);
```

3. Logo Backgrounds
```php
// Create a circular logo background
$options = [
    'width' => 100,
    'height' => 100,
    'rounded' => 50  // Makes it circular
];
$svg = $exporter->export($palette, 'svg', $options);
```

### Adobe Swatch Exchange (ASE)
The ASE format allows you to use your website's colors in Adobe Creative Suite applications:

1. Export your palette to ASE
2. Import into Adobe Photoshop/Illustrator via Swatches panel
3. Use for creating matching graphics and marketing materials

## Usage Examples

### Theme Development
```php
// Get palette from your theme
$palette = get_theme_colors();

// Create color exporter
$exporter = new Color_Palette_Export();

// Export for different purposes
$css = $exporter->export($palette, 'css');
$tailwind = $exporter->export($palette, 'tailwind');
$bootstrap = $exporter->export($palette, 'bootstrap');

// Create a color swatch preview
$swatch = $exporter->export($palette, 'svg', [
    'width' => 200,
    'height' => 30,
    'spacing' => 1,
    'rounded' => 2
]);
```

### Plugin Development
```php
// Export colors for JavaScript use
$json = $exporter->export($palette, 'json');
wp_localize_script('your-script', 'siteColors', json_decode($json, true));

// Create admin color picker preview
$preview = $exporter->export($palette, 'svg', [
    'width' => 150,
    'height' => 20
]);
```

## Best Practices

1. **Cache Exports**: Cache the exported formats when possible to improve performance
2. **Validate Colors**: Ensure all colors are valid hex codes before exporting
3. **Use Prefixes**: Add appropriate prefixes to avoid naming conflicts
4. **Responsive SVGs**: Use percentage values for width/height in SVGs when appropriate
5. **Framework Consistency**: Match your framework's naming conventions when exporting
