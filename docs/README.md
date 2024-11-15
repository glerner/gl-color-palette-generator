# Color Palette Generator

A WordPress plugin that generates accessible, psychologically-informed color palettes using AI.

## Features

- AI-powered color palette generation based on business context
- Accessibility analysis ensuring WCAG 2.1 compliance
- Psychological color analysis
- Color harmony and temperature analysis
- Exportable CSS variables
- Comprehensive color variation generation

## Technical Documentation

### Color Analysis

The `Color_Analysis` class provides comprehensive color analysis including:

- Harmony detection (complementary, triadic, etc.)
- Temperature analysis (warm/cool balance)
- Contrast ratio calculations
- Balance analysis (lightness/saturation distribution)

#### Harmony Analysis

The harmony analysis examines relationships between colors and provides:
- Color scheme type detection
- Balance measurements
- Temperature characteristics
- Contrast level analysis
- Recommendations for improvements

#### Temperature Analysis

Colors are categorized by temperature based on their hue:
- Warm: 0° - 60° (reds to yellows)
- Cool: 180° - 240° (cyans to blues)
- Neutral: Other hues

#### Contrast Analysis

Contrast ratios are calculated according to WCAG 2.1 guidelines:
- Minimum contrast (AA): 4.5:1
- Enhanced contrast (AAA): 7:1
- Large text requirements: 3:1 (AA) and 4.5:1 (AAA)

### Usage

<?php
// Initialize analyzer
$analyzer = new Color_Analysis();

// Analyze a color palette
$colors = [
    'primary' => ['hex' => '#2C3E50'],
    'secondary' => ['hex' => '#E74C3C'],
    'accent' => ['hex' => '#3498DB']
];

$analysis = $analyzer->analyze_harmony($colors);

// Access results
$scheme_type = $analysis['scheme_type'];
$temperature = $analysis['temperature'];
$recommendations = $analysis['recommendations'];
?>

### Color Palette Generation

The `Color_AI_Generator` class handles AI-powered palette generation:

<?php
// Initialize generator with OpenAI API key
$generator = new Color_AI_Generator('your-api-key');

// Set business context
$generator->set_context([
    'industry_type' => 'technology',
    'psychological_impact' => [
        'first_impression' => 'trust',
        'lasting_impression' => 'innovation'
    ]
]);

// Generate palette
$palette = $generator->generate_palette();
?>

### Accessibility Features

- Automatic contrast checking against WCAG 2.1 guidelines
- Generation of accessible color variations
- Contrast ratio calculations for text readability
- Recommendations for improving accessibility

### CSS Variable Export

Generated palettes can be exported as CSS custom properties:

```css
:root {
    --color-primary: #2C3E50;
    --color-primary-light: #3E5871;
    --color-primary-dark: #1A2530;
    --color-secondary: #E74C3C;
    /* ... additional variations ... */
}
```

## Installation

1. Upload the plugin files to `/wp-content/plugins/gl-color-palette-generator`
2. Activate the plugin through the WordPress plugins screen
3. Configure your OpenAI API key in the settings
4. Access the generator via the admin menu

## Requirements

- WordPress 5.8+
- PHP 8.0+
- OpenAI API key
- Modern web browser supporting CSS custom properties

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct and the process for submitting pull requests.

## License

This project is licensed under the GPL v2 or later - see the [LICENSE.md](LICENSE.md) file for details.
