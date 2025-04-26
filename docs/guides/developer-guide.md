# Developer Guide

## Setting Up the Development Environment

### Prerequisites
- WordPress 6.2 or higher
- PHP 8.0 or higher
- Node.js 16+
- Composer
- Git
- Local WordPress development environment (e.g., Local by Flywheel, Lando, MAMP, etc.)

### Environment Configuration

1. Copy the sample testing environment file:
   ```bash
   cp .env.sample.testing .env.testing
   ```

2. Edit `.env.testing` with your local WordPress path:
   ```bash
   # Only WordPress path needs to be configured here
   FILESYSTEM_WP_ROOT=/path/to/your/wordpress

   # Add any AI service API keys if needed
   OPENAI_API_KEY=your_key_here
   ```

   Alternatively, you can set the WordPress path as an environment variable:
   ```bash
   export FILESYSTEM_WP_ROOT=/path/to/your/wordpress
   ```

   Note: Other WordPress configuration (database, test settings, etc.) is handled automatically by `bin/setup-plugin-tests.sh`, which gets settings from your Lando environment or wp-config.php.

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
The plugin uses PHPUnit for testing, with separate suites for unit and integration tests. The testing environment is configured through Composer and phpunit.xml.



1. Clean and reinstall dependencies:
   ```bash
   # Clean existing files and cache
   cd ~/sites/gl-color-palette-generator
   rm -rf vendor/ composer.lock .phpunit.result.cache
   composer clear-cache --no-interaction

   # Install dependencies and optimize autoloader
   composer update --no-interaction
   composer check-platform-reqs
   composer dump-autoload -o
   ```

2. Sync plugin to WordPress plugins directory:
   (still from within `gl-color-palette-generator` source directory)
   ```bash
   ./bin/sync-to-wp.sh
   ```

3. Set up WordPress test environment:
   ```bash
   cd ~/sites/wordpress
   lando ssh -c "cd wp-content/plugins/gl-color-palette-generator && bash ./bin/setup-plugin-tests.sh"
   ```

### Running Tests

The plugin has several test suites configured:

1. Unit tests
2. Integration tests
3. Code coverage reports (HTML and text)

#### Unit and integration tests

composer test

Full example:
```bash
# Run all tests (unit and integration)
cd ~/sites/gl-color-palette-generator/ && \
bash ./bin/sync-to-wp.sh && \
cd ~/sites/wordpress && \
composer test
```

#### Unit tests

composer test:unit

Full example:
```bash
# Run only unit tests
cd ~/sites/gl-color-palette-generator/ && \
bash ./bin/sync-to-wp.sh && \
cd ~/sites/wordpress && \
composer test:unit
```

#### Integration tests:

composer test:integration

Full example:
```bash
# Run only integration tests
cd ~/sites/gl-color-palette-generator/ && \
bash ./bin/sync-to-wp.sh && \
cd ~/sites/wordpress && \
composer test:integration
```

#### HTML code coverage report:

composer coverage

Full example:
```bash
# Generate code coverage report
cd ~/sites/gl-color-palette-generator/ && \
bash ./bin/sync-to-wp.sh && \
cd ~/sites/wordpress && \
composer coverage
```

#### Text-based code coverage report:

composer coverage:text

Full example:
```bash
# Generate text-based coverage report
cd ~/sites/gl-color-palette-generator/ && \
bash ./bin/sync-to-wp.sh && \
cd ~/sites/wordpress && \
composer coverage:text
```

### Test Organization

The tests are organized into several directories under `tests/`:
- `unit/` - Basic unit tests
- `integration/` - WordPress integration tests
- `color-management/` - Color-related functionality tests
- `core/` - Core plugin functionality tests
- `generators/` - Palette generation tests
- `providers/` - Data provider tests
- `settings/` - Plugin settings tests
- `security/` - Security-related tests

#### Test Types

The plugin uses three main types of tests, each with a specific purpose and base class:

1. **Unit Tests**
   - Extend `GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case`
   - Test isolated components with no WordPress dependencies
   - Focus on pure PHP functionality and logic
   - Located in `tests/unit/` directory
   - Can use various mocking approaches (no mocks, Mockery, PHPUnit's createMock)

2. **WP-Mock Tests**
   - Extend `GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case`
   - Test code that uses WordPress functions but doesn't need a full WordPress environment
   - Use WP_Mock to simulate WordPress functions and hooks
   - Faster than integration tests but can test WordPress-dependent code

3. **Integration Tests**
   - Extend `GL_Color_Palette_Generator\Tests\Base\Integration_Test_Case`
   - Test code that requires a full WordPress environment
   - Test interactions with WordPress core, database, and hooks
   - Located in `tests/integration/` directory
   - Include tests for live API integrations with external services

#### Test Naming Conventions

The project follows these naming conventions for test files:

1. **Test Classes** (classes that contain actual tests):
   - Should follow the `Test_*` pattern (e.g., `Test_Ajax_Handler`, `Test_Color_Palette`)
   - The name should reflect what component they're testing
   - Example: `Test_Settings_Manager` tests the `Settings_Manager` class

2. **Base Classes** (abstract classes that test classes extend):
   - Follow a different naming pattern: `*_Test_Case`
   - Located in the `GL_Color_Palette_Generator\Tests\Base` namespace
   - Three standard base classes:
     - `Unit_Test_Case` - For unit tests
     - `WP_Mock_Test_Case` - For WP-Mock tests
     - `Integration_Test_Case` - For integration tests

This distinction in naming patterns helps differentiate between the infrastructure classes (base classes) and the actual test implementations.

For more details on mocking approaches used in unit tests, see the [Test Plan](../../tests/TEST-PLAN.md#mocking-approaches-in-unit-tests).

### Test Configuration

The plugin uses a comprehensive PHPUnit configuration (`phpunit.xml`) that includes:

1. Code Coverage Settings:
   - Includes all PHP files in the `includes/` directory
   - Excludes `vendor/` and `tests/` directories
   - Generates both HTML and Clover coverage reports

2. WordPress Test Environment Variables:
   - Database configuration
   - WordPress installation paths
   - Test site settings

3. Test Suites:
   - Integration tests in `./tests/integration/`
   - Unit tests across multiple feature-specific directories

4. Code Coverage Annotations:
   - `@codeCoverageIgnore`: Add this to the docblock of any class or file that should be excluded from code coverage reports. Use this for:
     - Abstract base classes that aren't meant to be tested directly (e.g., [Unit_Test_Case](cci:2://file:///home/george/sites/gl-color-palette-generator/tests/class-unit-test-case.php:28:0-45:1))
     - Interfaces and traits that contain no testable code
     - Legacy or deprecated code that is kept for reference but no longer actively tested
     - Bootstrap or configuration files that set up the testing environment

Other useful coverage annotations:

- `@codeCoverageIgnoreStart` and `@codeCoverageIgnoreEnd`: Exclude specific sections of code from coverage reports
- `@codeCoverageIgnoreMethod`: Exclude a specific method from coverage reports
- `@codeCoverageIgnoreClass`: Exclude a specific class from coverage reports

```php
   /**
    * Base class for unit tests
    *
    * @package GL_Color_Palette_Generator
    * @subpackage Tests
    * @codeCoverageIgnore
    */
   abstract class Unit_Test_Case extends \WP_UnitTestCase {
       // ...
   }
```

```php
// @codeCoverageIgnoreStart
if (defined('DOING_AJAX')) {
    // Legacy AJAX handling code
}
// @codeCoverageIgnoreEnd
```
```php
/**
 * @codeCoverageIgnoreMethod
 */
private function deprecatedHelper() {
    // Old helper method kept for backward compatibility
}
```
```php
/**
 * @codeCoverageIgnoreClass
 */
class Deprecated_Class {
    // Old class kept for backward compatibility
}
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

#### Visual Studio Code Recommended Extensions

We recommend installing the following VSCode extensions for the best development experience:

1. **PHP Intelephense** (bmewburn.vscode-intelephense-client)
   - Provides PHP code intelligence including:
     - Code completion and IntelliSense
     - Error detection
     - Code navigation and refactoring tools
     - Type checking

2. **WordPress Hooks IntelliSense** (johnbillion.vscode-wordpress-hooks)
   - Provides autocomplete for WordPress hooks:
     - Action and filter suggestions
     - Hook documentation
     - Parameter hints for hook callbacks

Both extensions work together: PHP Intelephense handles general PHP language features, while WordPress Hooks IntelliSense adds WordPress-specific functionality.

> **Note:** If you encounter PHP_CodeSniffer errors about missing extensions (tokenizer, xmlwriter, SimpleXML), install them with: `sudo apt-get install php-xml php-tokenizer`

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

> **Note:** Using Composer's phpcbf command works more reliably than trying to install and configure Visual Studio Code extensions for PHP code formatting. The command-line approach ensures consistent formatting across all development environments.

# Check JavaScript/TypeScript code
npm run lint

# Fix JavaScript/TypeScript code
npm run lint:fix
```

## Contributing

Please see our [Contributing Guide](../../CONTRIBUTING.md) for details on our development process and how to submit pull requests.
