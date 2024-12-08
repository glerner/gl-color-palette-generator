# GL Color Palette Generator

Generate beautiful and accessible color palettes for your WordPress website using AI.

## Description

GL Color Palette Generator is a WordPress plugin that helps you create and manage color palettes for your website design. It uses AI to generate harmonious color combinations while ensuring accessibility standards are met.

**Author:** [George Lerner](https://website-tech.glerner.com/)
**Plugin URI:** [https://github.com/GeorgeLerner/gl-color-palette-generator](https://github.com/GeorgeLerner/gl-color-palette-generator)

### Features

- AI-powered color palette generation
- Multiple AI provider support (OpenAI, Anthropic, etc.)
- Accessibility checking (WCAG 2.1)
- Color scheme analysis
- Cultural context awareness
- Export/Import functionality
- WordPress block editor integration
- REST API endpoints

### Requirements

- WordPress 6.2 or higher
- PHP 8.0 or higher
- Node.js 16+ (for development)
- Composer (for development)

## Installation

### Production

1. Download the plugin from [GitHub](https://github.com/GeorgeLerner/gl-color-palette-generator)
2. Upload to your WordPress plugins directory
3. Activate the plugin through the WordPress admin interface
4. Configure your AI provider settings

### Development

1. Clone the repository:
   ```bash
   git clone https://github.com/GeorgeLerner/gl-color-palette-generator.git
   ```

2. Install dependencies:
   ```bash
   composer install
   npm install
   ```

3. Set up WordPress test environment:
   ```bash
   # For local development with Git (recommended):
   bin/install-wp-tests-git.sh wordpress_test root '' localhost latest

   # For local development with SVN:
   bin/install-wp-tests.sh wordpress_test root '' localhost latest

   # For Lando with Git (recommended):
   lando ssh -c "/app/wp-content/plugins/gl-color-palette-generator/bin/install-wp-tests-git.sh wordpress wordpress wordpress database"

   # For Lando with SVN:
   lando ssh -c "/app/wp-content/plugins/gl-color-palette-generator/bin/install-wp-tests.sh wordpress wordpress wordpress database"
   ```

4. Build assets:
   ```bash
   npm run build
   ```

5. Run tests:
   ```bash
   # For local development:
   composer test
   npm test

   # For Lando:
   lando composer test
   lando npm test
   ```

## Documentation

- [Getting Started](docs/getting-started.md)
- [API Documentation](docs/API.md)
- [Developer Guide](docs/guides/developer-guide.md)
- [Contributing](CONTRIBUTING.md)

## Support

For support:
1. Check the [documentation](docs/)
2. [Open an issue](https://github.com/GeorgeLerner/gl-color-palette-generator/issues) on GitHub
3. Contact through [the plugin website](https://website-tech.glerner.com/contact)

## Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) and [Code of Conduct](CODE_OF_CONDUCT.md).

## Security

For security issues, please see our [Security Policy](SECURITY.md).

## Author

- **George Lerner**
- Website: [https://website-tech.glerner.com/](https://website-tech.glerner.com/)
- GitHub: [@GeorgeLerner](https://github.com/GeorgeLerner)

## License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a list of changes.
