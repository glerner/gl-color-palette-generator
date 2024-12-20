# Contributing to GL Color Palette Generator

Thank you for your interest in contributing to the GL Color Palette Generator! This document provides guidelines and steps for contributing.

## Documentation

The following documentation is available to help you:

1. [API Documentation](docs/API.md) - REST API endpoints and usage
2. [Database Setup](docs/database-setup.md) - MySQL setup for running tests

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

### Running Tests

The plugin uses PHPUnit for testing. We provide a simple test runner script that makes it easy to run different types of tests.

First, sync your plugin code to WordPress:
```bash
cd ~/sites/gl-color-palette-generator && \
./bin/sync-to-wp.sh && \
cd ~/sites/wordpress/wp-content/plugins/gl-color-palette-generator
```

Then run tests using our test runner:

```bash
# Run all unit tests
bin/test.sh --unit

# Run WP Mock tests
bin/test.sh --mock

# Run integration tests
bin/test.sh --integration

# Run specific test suites
bin/test.sh --providers  # Provider tests
bin/test.sh --api       # API tests
bin/test.sh --admin     # Admin tests
bin/test.sh --core      # Core tests

# Run all tests
bin/test.sh --all

# Generate code coverage report
bin/test.sh --unit --coverage
```

For more options, run:
```bash
bin/test.sh --help
```

You can combine the sync and test steps in one command:
```bash
cd ~/sites/gl-color-palette-generator && \
./bin/sync-to-wp.sh && \
cd ~/sites/wordpress/wp-content/plugins/gl-color-palette-generator && \
bin/test.sh --unit  # or any other test option
```

The original PHPUnit parameters are still available if needed:
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

The plugin includes two sync scripts for different purposes:

1. `bin/sync-to-wp.sh` - Syncs the plugin files to your local WordPress test environment:
   ```bash
   ./bin/sync-to-wp.sh
   ```
   This copies all files needed for development and testing.

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
