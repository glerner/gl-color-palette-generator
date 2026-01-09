---
title: Improve Test Bootstrap Organization and Documentation
labels: enhancement, testing, documentation
assignees:
priority: medium
---

# Improve Test Bootstrap Organization and Documentation

## Current Situation
We have multiple bootstrap files with overlapping functionality and unclear usage:
1. `bootstrap.php` - General bootstrap file
2. `bootstrap/wp.php` - WordPress-specific bootstrap
3. `bootstrap/wp-mock.php` - WP_Mock bootstrap
4. `bootstrap/unit.php` - Unit test bootstrap

This leads to:
- Confusion about which bootstrap file to use for different test types
- Fragile dependency on class loading order
- Potential conflicts between WordPress and WP_Mock setups
- Duplicate mock function definitions
- Inconsistent environment setup

## Proposed Solution

### 1. Document Test Types and Required Bootstrap
Add PHPDoc blocks to each test file indicating required bootstrap type:

```php
/**
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 * @bootstrap wp-mock    // Indicates this test uses WP_Mock bootstrap
 */
class Test_Color_Metrics_Analyzer extends Test_Case {
```
Other option: `@bootstrap wp`

### 2. Consolidate Bootstrap Files
- `bootstrap.php` → Main entry point that delegates to specific bootstrappers
- `bootstrap/wp.php` → WordPress integration tests
- `bootstrap/wp-mock.php` → Unit tests with WP_Mock
- `bootstrap/common.php` → Shared functionality

### 3. Create Bootstrap Documentation
Add `tests/README.md` explaining:
- Test types and their purposes
- When to use each bootstrap type
- How to run different test suites
- Common testing patterns and best practices

### 4. Improve Test Organization
```
tests/
├── README.md
├── bootstrap.php
├── bootstrap/
│   ├── common.php      # Shared functionality
│   ├── wp.php          # WordPress integration
│   └── wp-mock.php     # Unit tests with WP_Mock
├── unit/               # Unit tests using WP_Mock
└── integration/        # Integration tests using WordPress
```

## Implementation Strategy

### Phase 1: Infrastructure Setup
1. [ ] Update dependency configuration
   - [x] Add required packages to composer.json
   - [x] Update phpunit.xml with new bootstrap paths
   - [x] Add autoloading rules for test classes
2. [x] Create bootstrap directory structure
   - [x] Move existing bootstrap files to `tests/bootstrap/`
   - [x] Create `common.php` for shared functionality
3. [x] Create base test case classes
   - [x] Unit_Test_Case for pure PHP tests
   - [x] WP_Mock_Test_Case for WordPress mocking
   - [x] Integration_Test_Case for WordPress integration
4. [ ] Implement smart bootstrap detection
   - [ ] Directory-based fallback (`unit/*` → wp-mock, `integration/*` → wp)
   - [ ] Annotation-based override capability
5. [ ] Create initial documentation
   - [ ] Bootstrap types and their purposes
   - [ ] Directory structure conventions
   - [ ] How to override defaults with annotations

### Phase 2: Gradual Migration
1. [ ] Add `@bootstrap` annotation to new test files as they're created
2. [ ] Add annotation when modifying existing test files (Boy Scout Rule)
3. [ ] Update documentation with examples and best practices
4. [ ] Create PR template that reminds developers to add bootstrap annotation

### Phase 3: Validation and Enforcement
1. [ ] Add CI check that warns about missing bootstrap annotations
2. [ ] Create GitHub Action to validate bootstrap usage
3. [ ] Add automated tests for bootstrap system
4. [ ] Update documentation with validation rules

### Phase 4: Completion
1. [ ] Review remaining files without annotations
2. [ ] Plan dedicated sprint to add missing annotations
3. [ ] Update documentation to reflect final state
4. [ ] Verify all test files work with both fallback and annotation methods

## Benefits

1. Clear documentation of test requirements
2. Reduced confusion about bootstrap selection
3. Easier maintenance of test infrastructure
4. Better separation of concerns
5. Improved onboarding experience
6. No immediate disruption to existing tests
7. Gradual, manageable migration path

## Success Criteria

- [ ] All new test files include bootstrap annotation
- [ ] Documentation explains when to use each bootstrap type
- [ ] No duplicate mock functions across bootstrap files
- [ ] CI/CD pipeline successfully validates bootstrap usage
- [ ] New developers can easily understand and run tests
- [ ] Existing tests continue to work during migration
- [ ] Clear migration path for legacy test files

## Migration Impact
- **New Files**: Should include `@bootstrap` annotation (with fallback if missing)
- **Existing Files**: Will use directory-based fallback, can be explicitly annotated
- **CI/CD**: Warning for missing annotations, but fallback ensures tests still run
- **Development Flow**: Minimal impact, clear defaults with optional overrides
- **Long-term**: Keep both mechanisms for flexibility and safety

## Current Status
- Basic unit test structure implemented with separate bootstraps for unit, wp-mock, and integration tests
- Some directories moved to appropriate test suites

## TODO
- Review and categorize remaining test directories:
- [ ] tests/classes/ (likely wp-mock)
- [ ] tests/export/ (likely wp-mock)
- [ ] tests/includes/ (mixed, needs per-file review)
- [ ] tests/interfaces/ (mixed, needs per-file review)
- [ ] tests/performance/ (likely wp-mock)
- [ ] tests/ui/ (likely wp-mock)

## Criteria for Categorization
- Unit tests: Pure PHP, no WordPress dependencies
- WP-Mock tests: Requires WordPress functions/classes but can use mocks
- Integration tests: Requires full WordPress environment

## Additional Tasks
- [ ] Document test organization in README.md
- [ ] Update test running scripts if needed
- [ ] Consider creating test suite specific base classes
- [ ] Add PHPStan/static analysis rules to help identify pure PHP vs WordPress-dependent code
- [ ] Create test suite templates for each type (unit, wp-mock, integration)
- [ ] Add examples of each test type in documentation

## Test Suite Organization
Current:
```php
<testsuite name="unit">
    <directory suffix=".php">./tests/unit/</directory>
    <directory suffix=".php">./tests/utils</directory>
    <directory suffix=".php">./tests/types</directory>
</testsuite>
```

# Test Bootstrap Robustness Improvements

## ✅ Completed Changes

1. Created three distinct base test case classes:
   - `tests/class-unit-test-case.php` - For pure unit tests with Mockery
   - `tests/class-wp-mock-test-case.php` - For WordPress function mocking
   - [tests/class-integration-test-case.php](cci:7://file:///home/george/sites/gl-color-palette-generator/tests/class-integration-test-case.php:0:0-0:0) - For WordPress integration tests

2. Reorganized test class hierarchy:
   - Unit tests now extend `Unit_Test_Case`
   - WP_Mock tests extend `WP_Mock_Test_Case`
   - Integration tests extend `Integration_Test_Case`

3. Updated test files:
   - Moved WP_Mock tests from unit/ to wp-mock/
   - Updated class extensions in all test files
   - Maintained proper separation of concerns

## 🔄 Next Steps

1. Review and update bootstrap files:
   - Ensure proper initialization for each test type
   - Verify autoloading works correctly
   - Check WP_Mock setup

2. Consider moving remaining files to appropriate directories:
   - Pure unit tests in `tests/unit/`
   - WP_Mock tests in `tests/wp-mock/`
   - Integration tests in `tests/integration/`

3. Run full test suite to verify changes

## Example Usage

```php
// Pure Unit Test
class Test_Color_Metrics_Analyzer extends Unit_Test_Case {
    // Pure unit tests with Mockery support
}

// WordPress Mock Test
class Test_WordPress_Functions extends WP_Mock_Test_Case {
    // Tests that need WordPress function mocking
}

// Integration Test
class Test_WordPress_Integration extends Integration_Test_Case {
    // Full WordPress integration tests
}
```
