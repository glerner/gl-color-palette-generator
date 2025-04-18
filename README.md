# GL Color Palette Generator

Generate beautiful and accessible color palettes for your WordPress website using AI.

## Description

GL Color Palette Generator (named with "GL" to distinguish it from other color palette plugins) is a WordPress plugin that helps you create and manage color palettes for your website design. It uses AI to generate harmonious color combinations, while ensuring accessibility standards are met. It makes multiple theme variations, of the same 3 or 4 colors, for you to choose from.

### Theme Variations

Theme variations are optional design configurations within a block theme that allow you to change the look of your website without switching to a different theme. This plugin generates 6 or 24 harmonious variations for you to choose from.

To switch between variations:
1. Go to your WordPress dashboard, navigate to Appearance > Editor
2. Under Design, click on Styles
3. Click on a variation in the left panel to preview how your site looks
4. Once you're satisfied with a variation, click the "Save" button to apply it to your site

This plugin is designed to work with the TwentyTwentyFour theme (and future WordPress default themes) and is part of the course materials for ["WordPress Websites: From Overwhelm to Mastery"](https://website-overwhelm-to-mastery.glerner.com/).

**Author:** [George Lerner](https://website-tech.glerner.com/)
**Plugin URI:** [https://github.com/glerner/gl-color-palette-generator](https://github.com/glerner/gl-color-palette-generator)

### Features

#### Color Generation
- Generate 6 or 24 AI-powered theme variations
- 1-3 main colors (primary, secondary, tertiary) with 1 accent color
- 4 variations per color (lighter, light, dark, darker)
- Neutral colors plus black/white
- AI-generated palette based on business and ideal client questionnaire
- Manual color input with automatic WCAG AAA contrast adjustment

#### Color Properties
- Artist-inspired palette names (e.g., "Energetic Sunrise")
- Emotional attributes (e.g., Joyful, Calm, Professional)
- Hex codes with optional functional names
- Color scheme analysis and harmony validation

#### Accessibility Features
- WCAG AAA contrast compliance checking
- Visually distinct colors for better usability
- Light colors optimized for black text readability
- Dark colors optimized for white text readability

#### Output Options
- WordPress color palette display
- Theme Style Variations (theme.json)
- Light and Dark themes with browser switching via CSS light-dark()
- Downloadable .zip of all variations
- Export/Import functionality
- WordPress block editor integration
- REST API endpoints

## Development

For development setup and contribution guidelines, please see:
- [Contributing Guide](CONTRIBUTING.md) - Setup instructions and contribution guidelines
- [Database Setup](docs/database-setup.md) - MySQL setup for running tests
- [API Documentation](docs/API.md) - REST API endpoints and usage

### Requirements

- WordPress 6.2 to latest (as of April 2025 6.7.2)
- PHP 8.1 or higher
- Node.js 16+ (for development)
- Composer (for development)

## Installation

### Installation from GitHub

To install the plugin directly from GitHub:

1. From your WordPress plugins directory, run:
   ```bash
   cd wp-content/plugins && \
   git clone https://github.com/glerner/gl-color-palette-generator.git && \
   cd gl-color-palette-generator && \
   composer install && \
   npm install && \
   npm run build
   ```

2. Activate the plugin through the WordPress admin interface

3. Configure your AI provider settings in the plugin settings

### Development Setup

If you want to contribute to the plugin development:

1. Clone the repository to your development environment:
   ```bash
   git clone https://github.com/glerner/gl-color-palette-generator.git
   cd gl-color-palette-generator
   ```

2. Install dependencies:
   ```bash
   composer install
   npm install
   ```

3. Build frontend assets:
   ```bash
   npm run build
   ```

   For development with auto-rebuild:
   ```bash
   npm run dev
   ```

4. For running tests and setting up the test environment, see the [Contributing Guide](CONTRIBUTING.md)

## Documentation

- [Getting Started](docs/getting-started.md)
- [API Documentation](docs/API.md)
- [Developer Guide](docs/guides/developer-guide.md)
- [Contributing](CONTRIBUTING.md)

## Support

For support:
1. Check the [documentation](docs/)
2. [Open an issue](https://github.com/glerner/gl-color-palette-generator/issues) on GitHub
3. Contact through [the plugin website](https://website-tech.glerner.com/contact)

## Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) and [Code of Conduct](CODE_OF_CONDUCT.md).

## Security

For security issues, please see our [Security Policy](SECURITY.md).

## Author

- **George Lerner**
- Website: [https://website-tech.glerner.com/](https://website-tech.glerner.com/)
- GitHub: [@glerner](https://github.com/glerner)

## License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a list of changes.
