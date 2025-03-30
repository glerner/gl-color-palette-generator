# PHPUnit Testing Tutorial for Large Projects

This tutorial provides a comprehensive guide to setting up and organizing PHPUnit tests for large PHP projects, especially those with WordPress integration. It's based on lessons learned from the GL Color Palette Generator project.

## Table of Contents

- [Test Types and When to Use Each](#test-types-and-when-to-use-each)
- [Directory Structure](#directory-structure)
- [Base Test Classes](#base-test-classes)
- [Naming Conventions](#naming-conventions)
- [Namespace Organization](#namespace-organization)
- [Determining the Right Test Type](#determining-the-right-test-type)
- [Mocking Strategies](#mocking-strategies)
- [Test Isolation](#test-isolation)
- [Managing Test Dependencies](#managing-test-dependencies)
- [Continuous Integration Setup](#continuous-integration-setup)
- [Test Analysis and Maintenance](#test-analysis-and-maintenance)

## Test Types and When to Use Each

### 1. Unit Tests

- **Purpose**: Test individual components in isolation
- **When to use**: For classes/functions with minimal external dependencies
- **Characteristics**:
  - Fast execution
  - No database or filesystem access
  - No WordPress core dependencies
  - Dependencies are mocked or stubbed
- **Example scenarios**:
  - Utility classes
  - Data transformation functions
  - Business logic that doesn't rely on WordPress

### 2. WP-Mock Tests

- **Purpose**: Test WordPress-dependent code without a full WordPress environment
- **When to use**: For code that uses WordPress functions but doesn't need database access
- **Characteristics**:
  - Medium execution speed
  - Mocks WordPress functions and hooks
  - No actual WordPress loading
  - Good for testing plugin hooks, filters, and actions
- **Example scenarios**:
  - Code that uses `add_action()` or `add_filter()`
  - Functions that call WordPress utility functions
  - Admin page rendering that uses WordPress functions

### 3. Integration Tests

- **Purpose**: Test code that interacts with WordPress core, database, or external services
- **When to use**: When you need to test actual WordPress behavior or database interactions
- **Characteristics**:
  - Slower execution
  - Requires a test WordPress database
  - Tests actual integration with WordPress
  - May include API calls to external services
- **Example scenarios**:
  - Database operations
  - WordPress option handling
  - REST API endpoints
  - Live API integrations

## Directory Structure

Organize your tests to mirror your source code structure:

```
project-root/
├── src/
│   ├── core/
│   ├── admin/
│   └── ...
├── tests/
│   ├── bootstrap/
│   │   ├── bootstrap.php
│   │   ├── bootstrap-integration.php
│   │   └── bootstrap-wp-mock.php
│   ├── base/
│   │   ├── unit-test-case.php
│   │   ├── wp-mock-test-case.php
│   │   └── integration-test-case.php
│   ├── unit/
│   │   ├── core/
│   │   └── ...
│   ├── wp-mock/
│   │   ├── admin/
│   │   └── ...
│   ├── integration/
│   │   ├── core/
│   │   └── ...
│   └── TEST-PLAN.md
├── phpunit.xml
├── phpunit-integration.xml
└── phpunit-wp-mock.xml
```

Key points:
- Separate configuration files for each test type
- Bootstrap files for different test environments
- Base test classes in their own directory
- Test directories mirror source code structure

## Base Test Classes

Create base test classes for each test type to ensure consistent setup:

### Unit Test Case

```php
namespace Project\Tests\Base;

class Unit_Test_Case extends \PHPUnit\Framework\TestCase {
    protected function setUp(): void {
        parent::setUp();
        // Common setup for unit tests
    }
    
    protected function tearDown(): void {
        // Common teardown for unit tests
        parent::tearDown();
    }
}
```

### WP-Mock Test Case

```php
namespace Project\Tests\Base;

class WP_Mock_Test_Case extends \PHPUnit\Framework\TestCase {
    protected function setUp(): void {
        parent::setUp();
        \WP_Mock::setUp();
    }
    
    protected function tearDown(): void {
        \WP_Mock::tearDown();
        parent::tearDown();
    }
}
```

### Integration Test Case

```php
namespace Project\Tests\Base;

class Integration_Test_Case extends \PHPUnit\Framework\TestCase {
    protected function setUp(): void {
        parent::setUp();
        // Setup WordPress test environment
    }
    
    protected function tearDown(): void {
        // Cleanup WordPress test environment
        parent::tearDown();
    }
}
```

## Naming Conventions

Consistent naming helps maintain clarity:

### Test Files

- **Pattern**: `test-{class-being-tested}.php`
- **Examples**:
  - `test-settings-manager.php`
  - `test-api-client.php`

### Test Classes

- **Pattern**: `Test_{ClassBeingTested}`
- **Examples**:
  - `Test_Settings_Manager`
  - `Test_API_Client`

### Base Classes

- **Pattern**: `{Type}_Test_Case`
- **Examples**:
  - `Unit_Test_Case`
  - `WP_Mock_Test_Case`
  - `Integration_Test_Case`

### Test Methods

- **Pattern**: `test_{method_being_tested}_{scenario}`
- **Examples**:
  - `test_get_option_returns_default_when_not_set()`
  - `test_process_data_handles_invalid_input()`

## Namespace Organization

Organize namespaces to mirror your directory structure:

```php
// For a unit test in tests/unit/core/test-settings-manager.php
namespace Project\Tests\Unit\Core;

use Project\Tests\Base\Unit_Test_Case;
use Project\Core\Settings_Manager;

class Test_Settings_Manager extends Unit_Test_Case {
    // Test methods
}
```

Key principles:
- Match namespace to directory path
- Use consistent casing (typically snake_case for files, PascalCase for classes)
- Import the appropriate base test case
- Import the class being tested

## Determining the Right Test Type

Follow these guidelines to choose the appropriate test type:

1. **Start with unit tests** when possible
   - Can the code be tested in isolation?
   - Are external dependencies minimal or easily mockable?

2. **Use WP-Mock tests** when:
   - Code calls WordPress functions
   - No database access is needed
   - WordPress hooks/filters are used

3. **Use integration tests** when:
   - Code interacts with the database
   - WordPress core behavior needs to be tested
   - Testing external API integrations

Decision flowchart:
```
Does the code use WordPress functions? 
├── No → Unit Test
└── Yes → Does it need database or WordPress core behavior?
    ├── No → WP-Mock Test
    └── Yes → Integration Test
```

## Mocking Strategies

Different test types require different mocking approaches:

### For Unit Tests

Even though unit tests focus on testing components in isolation, they often need mocks for several reasons:

- **Dependency Isolation**: To test a class without being affected by its dependencies
- **Controlled Testing Environment**: To create predictable test conditions
- **Verifying Interactions**: To ensure your class correctly interacts with dependencies
- **Testing Edge Cases**: To easily simulate error conditions or rare scenarios
- **Performance**: To avoid slow operations from real dependencies

Common mocking approaches for unit tests:

1. **No Mocks**: For simple classes with no dependencies or with simple value objects as dependencies
2. **PHPUnit's createMock**: For simple interface mocking when you just need basic method stubs
3. **Mockery**: For more complex mocking scenarios requiring sophisticated expectations

Example with Mockery:
```php
public function test_process_data_calls_validator() {
    $validator = \Mockery::mock('Project\Validator');
    $validator->shouldReceive('validate')
        ->once()
        ->with('test-data')
        ->andReturn(true);
        
    $processor = new Data_Processor($validator);
    $processor->process_data('test-data');
}
```

### For WP-Mock Tests

Use WP_Mock to mock WordPress functions:

```php
public function test_register_hooks_adds_actions() {
    \WP_Mock::expectActionAdded('init', [$this->instance, 'initialize']);
    \WP_Mock::expectFilterAdded('the_content', [$this->instance, 'filter_content']);
    
    $this->instance->register_hooks();
}
```

### For Integration Tests

Minimize mocking, but use it for external services:

```php
public function test_save_option_stores_in_database() {
    // No mocking of WordPress functions
    $this->instance->save_option('test_key', 'test_value');
    
    // Verify using WordPress functions
    $this->assertEquals('test_value', get_option('plugin_prefix_test_key'));
}
```

## Test Isolation

Ensure tests don't affect each other:

1. **Reset state** in setUp() and tearDown()
2. **Use transactions** for database tests
3. **Mock time-dependent functions** for consistent results
4. **Create unique test data** to avoid collisions

Example:
```php
protected function setUp(): void {
    parent::setUp();
    
    // Create unique test data
    $this->test_id = uniqid('test_');
    $this->test_data = ['name' => 'Test ' . $this->test_id];
}

protected function tearDown(): void {
    // Clean up test data
    delete_option('plugin_prefix_' . $this->test_id);
    
    parent::tearDown();
}
```

## Managing Test Dependencies

Handle dependencies efficiently:

1. **Composer for PHP dependencies**:
   ```json
   "require-dev": {
       "phpunit/phpunit": "^9.0",
       "mockery/mockery": "^1.4",
       "10up/wp_mock": "^0.4",
       "yoast/phpunit-polyfills": "^1.0"
   }
   ```

2. **Separate bootstrap files** for different test types
3. **Environment variables** for configuration
4. **Test fixtures** for common test data

## Continuous Integration Setup

Configure CI for reliable testing:

1. **Run different test suites separately**:
   ```yaml
   - name: Run unit tests
     run: vendor/bin/phpunit --configuration phpunit.xml
     
   - name: Run WP-Mock tests
     run: vendor/bin/phpunit --configuration phpunit-wp-mock.xml
     
   - name: Run integration tests
     run: vendor/bin/phpunit --configuration phpunit-integration.xml
   ```

2. **Set up test databases** for integration tests
3. **Cache dependencies** to speed up builds
4. **Generate coverage reports**

## Test Analysis and Maintenance

Keep your test suite healthy:

1. **Regular analysis** of test coverage
2. **Refactor tests** when source code changes
3. **Automated tools** to check test quality
4. **Document test patterns** for team consistency

Example test analyzer script:
```bash
#!/bin/bash
# Analyze test files to ensure they're in the right directory
# and using the correct base class

find tests/ -name "test-*.php" | while read file; do
    # Check if test uses WordPress functions
    if grep -q "wp_" "$file" && ! grep -q "WP_Mock" "$file"; then
        echo "$file might need to be a WP-Mock or integration test"
    fi
    
    # Check base class
    if grep -q "extends Unit_Test_Case" "$file" && grep -q "wp_" "$file"; then
        echo "$file extends Unit_Test_Case but uses WordPress functions"
    fi
done
```

---

By following these guidelines, you can create a well-organized, maintainable test suite for large PHP projects. This approach separates different types of tests, ensures proper isolation, and makes it easier to maintain tests as your project evolves.
