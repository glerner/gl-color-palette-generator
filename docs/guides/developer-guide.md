# Developer Guide

## Setting Up the Development Environment

### Prerequisites
- WordPress 6.2 or higher
- PHP 8.0 or higher
- Node.js 16+
- Composer
- Git
- Local WordPress development environment (e.g., Local by Flywheel, Lando, MAMP, etc.)

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

The plugin tests run in your WordPress development environment. The source code lives in a separate directory and is synced to your WordPress plugins directory for testing.

1. Set up WordPress test suite in your development environment:
   ```bash
   # Inside Lando environment
   ./bin/install-wp-tests.sh wordpress_test db user pass db latest

   # For other environments, adjust credentials:
   ./bin/install-wp-tests.sh wordpress_test <db-user> <db-pass> <db-host> latest
   ```

2. Sync plugin to WordPress plugins directory:
   ```bash
   # The sync script copies files to your WordPress plugins directory
   ./bin/sync-to-wp.sh
   ```

3. Run PHPUnit tests:
   ```bash
   # Inside Lando environment
   cd /app/wordpress/wp-content/plugins/gl-color-palette-generator
   phpunit

   # For other environments
   cd /path/to/wordpress/wp-content/plugins/gl-color-palette-generator
   phpunit
   ```

### Environment-Specific Setup

#### Using Lando
```bash
# Install test suite (generic format)
lando ssh
./bin/install-wp-tests.sh wordpress_test db user pass db latest

# Or using Lando defaults
lando ssh -c "bash bin/install-wp-tests.sh wordpress wordpress wordpress database"

# Run tests
lando ssh
cd /app/wordpress/wp-content/plugins/gl-color-palette-generator
phpunit
```

#### Using Local by Flywheel
```bash
# Connect to shell
cd ~/Local Sites/your-site/app/public
# Install test suite (adjust credentials)
/path/to/plugin/bin/install-wp-tests.sh wordpress_test root root localhost latest
```

#### Using MAMP
```bash
# Install test suite
./bin/install-wp-tests.sh wordpress_test root root localhost:8889 latest
```

### Debugging with Xdebug

The plugin is configured to support Xdebug debugging in your development environment. 

#### VSCode Setup

1. Install the PHP Debug extension
2. Add this configuration to your `.vscode/launch.json`:
   ```json
   {
     "version": "0.2.0",
     "configurations": [
       {
         "name": "Listen for Xdebug",
         "type": "php",
         "request": "launch",
         "port": 9003,
         "pathMappings": {
           "/app/wordpress/wp-content/plugins/gl-color-palette-generator": "${workspaceFolder}"
         }
       }
     ]
   }
   ```

#### Debugging Tests

1. In VSCode:
   - Set breakpoints in your test files by clicking in the left margin
   - Start the PHP Debug listener (F5 or click the "Run and Debug" icon)
   - The debug toolbar will appear when the listener is active

2. Run your tests:
   ```bash
   # Inside Lando environment
   lando test

   # Or manually with Xdebug enabled
   XDEBUG_MODE=debug XDEBUG_CONFIG="client_host=host.docker.internal client_port=9003" phpunit
   ```

3. When a breakpoint is hit:
   - Execution will pause at the breakpoint
   - Use the debug toolbar to step through code
   - Inspect variables in the debug sidebar
   - Use the debug console to evaluate expressions

#### Running Tests with Xdebug

```bash
# Inside Lando environment
lando test

# Or manually with Xdebug enabled
XDEBUG_MODE=debug XDEBUG_CONFIG="client_host=host.docker.internal client_port=9003" phpunit
```

#### Troubleshooting Xdebug

If Xdebug isn't connecting:

1. Verify Xdebug is enabled:
   ```bash
   lando ssh -c "php -v"
   ```

2. Check Xdebug logs:
   ```bash
   lando ssh -c "tail -f /tmp/xdebug.log"
   ```

3. Ensure your IDE is listening for debug connections

4. Verify your firewall allows connections on port 9003

### Test Organization

- Unit tests are located in the `tests` directory
- Test files follow the naming convention `test-*.php`
- Integration tests are in `tests/integration`
- Each test file corresponds to a source file in the main plugin directory

### Running Tests

```bash
# Inside Lando environment
cd /app/wordpress/wp-content/plugins/gl-color-palette-generator
phpunit

# Run JavaScript tests
npm test
```

### Continuous Integration

Tests are automatically run in GitHub Actions on:
- Pull requests
- Pushes to main branch
- Release creation

The CI environment uses a standardized WordPress test setup to ensure consistent test results across different development environments.

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
