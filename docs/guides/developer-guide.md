# Developer Guide

## Setting Up the Development Environment

### Prerequisites
- WordPress 6.2 or higher
- PHP 8.0 or higher
- Node.js 16+
- Composer
- Git

### Local Development Setup

1. Clone the repository:
   ```bash
   git clone https://github.com/GeorgeLerner/gl-color-palette-generator.git
   cd gl-color-palette-generator
   ```

2. Install dependencies:
   ```bash
   composer install
   npm install
   ```

### Testing Environment Setup

The plugin includes two scripts for setting up the WordPress testing environment:

1. `bin/install-wp-tests-git.sh` (Recommended)
   - Uses Git to clone the WordPress test suite
   - Faster and more reliable than SVN
   - No additional dependencies required
   - Works well in CI/CD environments

2. `bin/install-wp-tests.sh`
   - Traditional SVN-based script
   - Requires SVN to be installed
   - Included for compatibility with existing workflows

To set up the testing environment:

```bash
# Using Git (recommended):
bin/install-wp-tests-git.sh wordpress_test root '' localhost latest

# Using SVN:
bin/install-wp-tests.sh wordpress_test root '' localhost latest
```

For Lando environments:
```bash
# Using Git (recommended):
lando ssh -c "/app/wp-content/plugins/gl-color-palette-generator/bin/install-wp-tests-git.sh wordpress wordpress wordpress database"

# Using SVN:
lando ssh -c "/app/wp-content/plugins/gl-color-palette-generator/bin/install-wp-tests.sh wordpress wordpress wordpress database"
```

### Running Tests

After setting up the testing environment:

```bash
# Run PHP tests
composer test

# Run JavaScript tests
npm test

# Run both
composer test && npm test
```

For Lando environments:
```bash
lando composer test
lando npm test
```

## Development Workflow

### Code Organization
- `src/` - TypeScript/JavaScript source files
- `includes/` - PHP classes and functions
- `tests/` - Test files
- `assets/` - Static assets (CSS, images, etc.)

### Building Assets

```bash
# Development build with watch
npm run start

# Production build
npm run build
```

### Coding Standards

This plugin follows WordPress coding standards. To check your code:

```bash
# Check PHP code
composer run phpcs

# Fix PHP code
composer run phpcbf

# Check JavaScript/TypeScript code
npm run lint

# Fix JavaScript/TypeScript code
npm run lint:fix
```

## Contributing

Please see our [Contributing Guide](../../CONTRIBUTING.md) for details on our development process and how to submit pull requests.
