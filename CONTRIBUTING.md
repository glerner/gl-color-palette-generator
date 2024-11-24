# Contributing to Color Palette Generator

Thank you for your interest in contributing to the Color Palette Generator! This document provides guidelines and steps for contributing.

## Development Setup

1. Clone the repository:

```bash
git clone https://github.com/yourusername/gl-color-palette-generator.git
```


2. Set up a local WordPress development environment (we recommend [Local](https://localwp.com/) or [VVV](https://varyingvagrantvagrants.org/))

3. Symlink or copy the plugin to your WordPress plugins directory:

```bash
ln -s /path/to/gl-color-palette-generator /path/to/wordpress/wp-content/plugins/
```


4. Install dependencies:

```bash
composer install
```


5. Activate the plugin in WordPress admin

6. For development testing, you can set test configuration in wp-config.php:

```php
define('GL_COLOR_PALETTE_TEST_API_KEY', 'your_test_key_here');
define('GL_COLOR_PALETTE_AI_PROVIDER', 'openai'); / Available: 'openai', 'anthropic'

/ Optional: Provider-specific settings
define('GL_COLOR_PALETTE_OPENAI_MODEL', 'gpt-4'); / Default: gpt-4
define('GL_COLOR_PALETTE_ANTHROPIC_MODEL', 'claude-3-sonnet'); / Default: claude-3-sonnet
```

## Development Environment Details

### WordPress Development Settings

1. Enable debug mode in wp-config.php:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', true);
```


2. Install recommended development plugins:
- Query Monitor
- Debug Bar
- WordPress Debug Tools

### Unit Testing

1. Set up the test environment:

```bash
./bin/install-wp-tests.sh wordpress_test root 'your_password' localhost latest
```

2. Run the tests:

```bash
composer test
```


2. Creating new tests:
- Place test files in the `tests` directory
- Name test files as `test-*.php` or `class-test-*.php`
- Extend `WP_UnitTestCase`

### Development Workflow

1. Create a new branch for each feature/fix
2. Write/update tests for your changes
3. Ensure all tests pass
4. Update documentation as needed
5. Submit a pull request

## Coding Standards

This project follows the WordPress Coding Standards. To check your code:

```bash
composer phpcs
```


To automatically fix some coding standard issues:

```bash
composer phpcbf
```


## Pull Request Process

1. Create a new branch for your feature/fix:

```bash
git checkout -b feature/your-feature-name
```


2. Make your changes and commit them using conventional commits:

```bash
git commit -m "feat: Add new color analysis feature"
git commit -m "fix: Resolve contrast calculation issue"
git commit -m "docs: Update API documentation"
```


3. Push to your fork and create a Pull Request with:
   - Clear description of the changes
   - Any relevant issue numbers
   - Screenshots if UI changes are involved

4. Wait for the pull request to be reviewed and merged

Thank you for your contributions!

## Code of Conduct

### Our Pledge

We pledge to make participation in our project a harassment-free experience for everyone, regardless of age, body size, disability, ethnicity, gender identity and expression, level of experience, nationality, personal appearance, race, religion, or sexual identity and orientation.

### Our Standards

Examples of behavior that contributes to creating a positive environment include:
- Using welcoming and inclusive language
- Being respectful of differing viewpoints and experiences
- Gracefully accepting constructive criticism
- Focusing on what is best for the community
- Showing empathy towards other community members

### Enforcement

Instances of abusive, harassing, or otherwise unacceptable behavior may be reported by contacting the project team. All complaints will be reviewed and investigated promptly and fairly.

## License

By contributing to this project, you agree that your contributions will be licensed under the GPL v2 or later license.
