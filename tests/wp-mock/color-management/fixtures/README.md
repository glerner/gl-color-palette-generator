# Test Fixtures

This directory contains test fixtures used for the GL Color Palette Generator plugin tests. Test fixtures are sample files and data that provide consistent, known inputs for tests, ensuring that tests are deterministic and repeatable.

## Current Fixtures

- `desert-sunset.jpg` - Sample image used for testing color extraction and palette generation from images

## Purpose of Test Fixtures

Test fixtures serve several important purposes:

1. **Consistent Test Data**:
   - Provide known, unchanging inputs for tests
   - Ensure tests are deterministic and repeatable

2. **Isolation from External Dependencies**:
   - Allow tests to run without network access
   - Prevent tests from being affected by external service changes

3. **Testing Edge Cases**:
   - Provide carefully crafted inputs for testing boundary conditions
   - Allow testing of error handling with invalid inputs

## Types of Fixtures for Color Palette Generator

For the GL Color Palette Generator plugin, the following types of fixtures are useful:

### Images
- Sample images for testing color extraction
- Images with known color profiles
- Edge cases (monochromatic, extremely colorful, etc.)

### Palette Data
- Sample palette JSON files
- Color collections in various formats
- Import/export test data

### API Responses
- Mock responses from external color APIs
- Sample data from Color Pizza API
- AI provider response samples

### CSS Templates
- Sample CSS output templates
- Theme.json examples
- Light/dark mode variations

## Recommended Organization

For the project rewrite, we recommend organizing fixtures by type:

```
tests/
  fixtures/
    images/         # Sample images for color extraction tests
    data/           # Sample color & palette data in various formats
    api-responses/  # Mock responses from external APIs
    html/           # HTML snippets or templates
    css-templates/  # Sample CSS output templates or snippets
```

## Best Practices for Test Fixtures

1. **Keep fixtures small** - Only include what's necessary for tests
2. **Document the purpose** of each fixture
3. **Version control** fixtures alongside code
4. **Organize by functionality** rather than test type
5. **Use realistic data** that represents actual use cases

## Adding New Fixtures

When adding new fixtures:

1. Place them in the appropriate subdirectory
2. Add a comment in this README explaining their purpose
3. Reference them in tests using relative paths
4. Consider adding a small comment in the test explaining the fixture's purpose

## Note for PHPStan

Test fixtures are excluded from PHPStan analysis since they are test data, not code to be analyzed. The exclusion is configured in the PHPStan configuration files.
