# GL Color Palette Generator

Generate beautiful and accessible color palettes for your WordPress website using AI.

## Description

GL Color Palette Generator (named with "GL" to distinguish it from other color palette plugins) is a WordPress plugin that helps you create and manage color palettes for your website design. It uses AI to generate harmonious color combinations while ensuring accessibility standards are met.

This plugin is designed to work with the TwentyTwentyFour theme (and future WordPress default themes) and is part of the course materials for ["WordPress Websites: From Overwhelm to Mastery"](https://website-overwhelm-to-mastery.glerner.com/).

**Author:** [George Lerner](https://website-tech.glerner.com/)
**Plugin URI:** [https://github.com/glerner/gl-color-palette-generator](https://github.com/glerner/gl-color-palette-generator)

### Features

- AI-powered color palette generation
- Multiple AI provider support (OpenAI, Anthropic, etc.)
- Accessibility checking (WCAG 2.1)
- Color scheme analysis
- Cultural context awareness
- Export/Import functionality
- WordPress block editor integration
- REST API endpoints

## Development

For development setup and contribution guidelines, please see:
- [Contributing Guide](CONTRIBUTING.md) - Setup instructions and contribution guidelines
- [Database Setup](docs/database-setup.md) - MySQL setup for running tests
- [API Documentation](docs/API.md) - REST API endpoints and usage

### Requirements

- WordPress 6.2 or higher
- PHP 8.0 or higher
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
