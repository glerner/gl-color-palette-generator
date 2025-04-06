# Contributing to GL Color Palette Generator

Thank you for your interest in contributing to the GL Color Palette Generator! This document provides guidelines and steps for contributing.

## Documentation

Key documentation to review before contributing:

1. [API Documentation](docs/API.md) - REST API endpoints and usage
2. [Theme Color Guide](docs/THEME-COLOR-GUIDE.md) - Essential guide for theme developers implementing color variations
3. [Database Setup](docs/database-setup.md) - MySQL setup for running tests
4. [Color Harmonies](docs/color-harmonies.md) - Adding new color harmony types

Additional documentation can be found in the `docs/` directory, including:
- Developer guides in `docs/guides/`
- API details in `docs/API/`
- Example implementations in `docs/examples/`

## Development Setup

1. Clone the repository:

```bash
git clone https://github.com/glerner/gl-color-palette-generator.git
```

2. Set up Lando development environment:

```bash
# Copy the example Lando configuration
cp .lando.example.yml ~/sites/wordpress/.lando.yml

# Start Lando
cd ~/sites/wordpress && lando start

# Install WordPress and the plugin
cd ~/sites/wordpress && lando install:wordpress
```

3. Configure Xdebug for Code Coverage:

The Lando configuration already includes Xdebug setup. To verify it's working:

```bash
# Check if Xdebug is enabled
lando php -m | grep xdebug

# Verify Xdebug settings
lando php -i | grep xdebug
```

If Xdebug is not showing up:
```bash
# Install Xdebug in the Lando container
lando ssh -c "sudo pecl install xdebug"

# Create Xdebug log file with proper permissions
sudo touch /tmp/xdebug.log
sudo chmod 666 /tmp/xdebug.log

# Restart Lando
lando restart
```

To verify the log file is working:
```bash
# Check log file permissions
ls -l /tmp/xdebug.log

# Test writing to log
lando php -r "xdebug_info();"

# View log contents
tail -f /tmp/xdebug.log
```

To run tests with code coverage:
```bash
# Generate HTML coverage report
lando test:coverage

# Run specific test suites
lando test:unit      # Run unit tests
lando test:mock      # Run WP Mock tests
lando test:api       # Run API tests
lando test:core      # Run core tests
lando test:admin     # Run admin tests
lando test:providers # Run provider tests

# Run all tests
lando test
```

The coverage reports will help identify which parts of the code need more test coverage.

3. Set up the test environment:

```bash
./bin/setup-plugin-tests.sh
```

This script will:
- Install composer dependencies
- Rebuild the Lando environment
- Install the WordPress test suite
- Configure the test database
- Generate wp-tests-config.php for WordPress integration tests

For detailed information about the test database setup, see [docs/database-setup.md](docs/database-setup.md).

## Testing

### PHPUnit Tests

Run the complete test suite:
```bash
composer test
```

### PHPStan Static Analysis

The project uses [PHPStan](https://phpstan.org/) for static analysis to catch coding errors early. It helps identify issues like:
- Undefined classes and types
- Invalid property types
- Undefined constants
- Invalid parameter types
- Unsafe comparisons
- And more

The base configuration in `phpstan.neon` sets:
- Default level 1
- Analysis of `includes/` directory
- Exclusion of `tests/` and `vendor/` directories
- Custom bootstrap file

```bash
# Run analysis with default level (1)
cd ~/sites/gl-color-palette-generator
vendor/bin/phpstan analyse --error-format=table --no-progress

# or specify file(s) to analyze
vendor/bin/phpstan analyse --error-format=table --no-progress includes/color-management/class-color-wheel.php includes/color-management/class-color-combination-engine.php

# Run with specific level (e.g., level 5)
vendor/bin/phpstan analyse --error-format=table --no-progress --level=5
```

PHPStan groups errors by file for easier fixing. Example output:
```
------ ----------------------------------------------------------
  Line   color-management/class-color-harmonization.php
------ ----------------------------------------------------------
  40     Property has unknown class Color_Analyzer as its type
  84     Access to undefined constant Color_Constants::SATURATION_STEP
  288    Construct empty() is not allowed. Use more strict comparison
------ ----------------------------------------------------------
```

PHPStan Level Guidelines:
- Level 1 (Default): Basic checks for initial development
  - Undefined classes, properties, and methods
  - Basic type checks

- Level 5: Recommended for feature development and public APIs
  - Stricter type checking
  - Dead code detection
  - Logical contradictions

- Level 9: For critical components (use with caution)
  - Most strict rules
  - Complex type checking
  - Early runtime error detection

See [PHPStan documentation](https://phpstan.org/user-guide/rule-levels) for details about each level.

### Code Coverage

Generate code coverage report:
```bash
composer test-coverage
```

### Test Environment Setup

#### Initial Setup of PHPUnit Tests

1. First, sync your plugin to WordPress:
```bash
cd ~/sites/gl-color-palette-generator/
./bin/sync-to-wp.sh
```

2. Then run the test setup script through Lando:
```bash
cd ~/sites/wordpress
lando ssh -c "cd wp-content/plugins/gl-color-palette-generator && bash ./bin/setup-plugin-tests.sh"
```

This setup script will:
- Read WordPress configuration from `/app/wp-config.php`
- Use Lando database configuration for tests
- Generate `wp-tests-config.php`
- Set up the test database (`wordpress_test`)
- Install WordPress test framework

#### Running Integration Tests

After setup is complete, you can run integration tests:

```bash
# 1. Go to plugin source directory
cd ~/sites/gl-color-palette-generator/

# 2. Sync plugin files
bash ./bin/sync-to-wp.sh

# 3. Go to plugin directory in WordPress
cd ~/sites/wordpress/wp-content/plugins/gl-color-palette-generator

# 4. Run integration tests
lando test:integration
```

Required files and directories:
- `$WP_ROOT/wp-content/plugins/wordpress-develop` - WordPress development environment
- `$WP_ROOT/wp-content/plugins/wordpress-develop/tests/phpunit/includes/functions.php` - WordPress test framework
- `tests/wp-tests-config.php` - Test configuration file (generated by setup script)

### Running Tests

The plugin uses PHPUnit for testing. We provide a simple test runner script that makes it easy to run different types of tests.

First, sync your plugin code to WordPress:
```bash
cd ~/sites/gl-color-palette-generator && \
bash ./bin/sync-to-wp.sh && \
cd ~/sites/wordpress/wp-content/plugins/gl-color-palette-generator
```

This syncs your plugin from:
- Plugin source: `~/sites/gl-color-palette-generator`
to:
- WordPress root: `~/sites/wordpress`
- Plugin destination: `~/sites/wordpress/wp-content/plugins/gl-color-palette-generator`

#### Unit Tests and WP Mock Tests

```bash
# Run all unit tests
bin/test.sh --unit

# Run WP Mock tests
bin/test.sh --mock

# Run specific test suites
bin/test.sh --providers  # Provider tests
bin/test.sh --api       # API tests
bin/test.sh --admin     # Admin tests
bin/test.sh --core      # Core tests

# Generate code coverage report
bin/test.sh --unit --coverage
```

#### Integration Tests

Integration tests require setting the WP_ROOT environment variable. You can either:

Set WP_ROOT first:
```bash
export WP_ROOT=/app
bin/test.sh --integration
```

Or combine it with the test command:
```bash
WP_ROOT=/app bin/test.sh --integration
```

You can also combine the sync and test steps in one command:
```bash
cd ~/sites/gl-color-palette-generator && \
bash ./bin/sync-to-wp.sh && \
cd ~/sites/wordpress/wp-content/plugins/gl-color-palette-generator && \
WP_ROOT=/app bin/test.sh --integration
```

For more options, run:
```bash
bin/test.sh --help
```

The original PHPUnit parameters are still available if needed (run from `~/sites/wordpress/wp-content/plugins/gl-color-palette-generator`):
```bash
vendor/bin/phpunit --testsuite unit
vendor/bin/phpunit --group wp-mock
vendor/bin/phpunit --bootstrap=tests/bootstrap-wp-mock.php
```

### When to Use Each Test Type

1. **WP-Mock Tests** - Unit tests that mock WordPress functions:
   - Used for testing external API interactions and isolated functionality
   - Doesn't require WordPress database or functions
   - Faster execution and more precise control

2. **Integration Tests** - Tests that require WordPress:
   - Used for testing WordPress-specific functionality
   - Requires WordPress test environment
   - Tests actual WordPress hooks, filters, and database operations

### Test Organization

Tests are organized into three main suites:

1. **WP_Mock Tests** (`--group wp-mock`):
   - Located in `tests/providers` and `tests/api`
   - Used for testing external API interactions and isolated functionality
   - Doesn't require WordPress database or functions
   - Faster execution and more precise control

2. **WP_UnitTestCase Tests** (`--group wp-unit`):
   - Located in `tests/core`, `tests/admin`, and `tests/settings`
   - Used for testing WordPress-specific functionality
   - Requires WordPress test environment
   - Tests actual WordPress hooks, filters, and database operations

3. **Integration Tests** (`--group integration`):
   - Located in `tests/integration`
   - Tests full system integration
   - Requires complete WordPress environment

### Creating New Tests

Choose the appropriate test type based on what you're testing:

- Use **WP_Mock** when testing:
  - External API interactions
  - Complex business logic
  - Code that doesn't heavily interact with WordPress core
  - Functions that need precise control over return values

- Use **WP_UnitTestCase** when testing:
  - WordPress hooks and filters
  - Database operations
  - WordPress core function integration
  - Plugin activation/deactivation
  - WordPress admin interfaces
  - Theme integration

Example WP_Mock test:
```php
class My_Provider_Test extends \WP_Mock\Tools\TestCase {
    protected $provider;

    public function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();
        $this->provider = new My_Provider(['api_key' => 'test_key']);
    }

    public function tearDown(): void {
        WP_Mock::tearDown();
        parent::tearDown();
    }

    public function test_api_call() {
        WP_Mock::userFunction('wp_remote_post')->once()->andReturn([
            'response' => ['code' => 200],
            'body' => json_encode(['success' => true])
        ]);

        $result = $this->provider->make_api_call();
        $this->assertTrue($result);
    }
}
```

Example WP_UnitTestCase test:
```php
class My_WordPress_Test extends WP_UnitTestCase {
    public function test_wordpress_integration() {
        $post_id = $this->factory->post->create();
        $this->assertGreaterThan(0, $post_id);

        $post = get_post($post_id);
        $this->assertInstanceOf('WP_Post', $post);
    }
}
```

## Sync Scripts

First, set up your environment variables for your plugin source and WordPress root:

```bash
# For local development:
export PLUGIN_SOURCE=~/sites/gl-color-palette-generator
export WP_ROOT=~/sites/wordpress

# For Lando:
export PLUGIN_SOURCE=~/sites/gl-color-palette-generator
export WP_ROOT=/app
```

The plugin includes two sync scripts for different purposes:

1. `bin/sync-to-wp.sh` - Syncs the plugin files to your local WordPress test environment:
   ```bash
   cd $PLUGIN_SOURCE && \
   ./bin/sync-to-wp.sh
   ```
   This copies all files needed for development and testing from:
   - Plugin source: `$PLUGIN_SOURCE`
   to:
   - WordPress root: `$WP_ROOT`
   - Plugin destination: `$WP_ROOT/wp-content/plugins/gl-color-palette-generator`

2. `bin/sync-to-production.sh` - Creates a clean production copy without development files:
   ```bash
   ./bin/sync-to-production.sh ~/plugin-releases/gl-color-palette-generator
   ```
   This excludes development files (tests, configs, docs) and installs only production dependencies.

## Contributing Guidelines

### Development Environment Details

#### WordPress Development Settings

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
./bin/setup-plugin-tests.sh
```

2. Run the tests:

```bash
composer test
```

### Development Workflow

1. Create a new branch for each feature/fix
2. Write/update tests for your changes
3. Ensure all tests pass
4. Update documentation as needed
5. Submit a pull request

## Coding Standards

This project follows the WordPress Coding Standards. 

To check your code, use PHP-CS-Fixer.

To install PHP-CS-Fixer using Composer, you can use the following command:
```bash
composer global require friendsofphp/php-cs-fixer
```

Then check your code:
```bash
composer phpcs
```

### Fixing Coding Standards Issues

To install PHPCBF via Composer, you can use the following command:
```bash
composer require --dev squizlabs/php_codesniffer
```

There are two ways to automatically fix coding standard issues:

1. **Using Composer script** (runs PHPCBF on the entire project):

```bash
composer phpcbf
```

2. **Using PHPCBF directly** (recommended for specific files):

```bash
# Fix a specific file
./vendor/bin/phpcbf path/to/file.php

# Example:
./vendor/bin/phpcbf includes/interfaces/interface-color-constants.php
```

> **Important Note**: While VS Code extensions for PHPCS/PHPCBF exist, they have proven unreliable and difficult to configure properly. Multiple attempts to set up these extensions have failed, while the command line version works immediately without configuration issues. **Always use the command line approach for consistent results.**

The direct PHPCBF command is more efficient when working with specific files and provides detailed output about which issues were fixed and which require manual intervention.

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
