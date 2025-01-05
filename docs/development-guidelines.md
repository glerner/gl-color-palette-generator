# GL Color Palette Generator - Development Guidelines

## Core Development Rules
1. **Color Constants**: Always use constants from `includes/interfaces/interface-color-constants.php` for:
   - Color roles and relationships
   - WCAG contrast requirements
   - Design constraints
   - Never hardcode color values or contrast ratios

2. **Color Utilities**: Use established utility functions from `Color_Utility` class for:
   - Color calculations
   - Format conversions
   - Accessibility checks
   - Never duplicate existing color manipulation functions

3. **Accessibility Standards**:
   - Use `WCAG_CONTRAST_TARGET` as primary contrast target
   - Fall back to `WCAG_CONTRAST_MIN` only when target contrast cannot be met
   - Never reference 'AA' or 'AAA' levels directly
   - Never hardcode contrast ratios

4. **Testing Protocol**:
   - Never test in plugin source folder
   - Testing environment: `~/sites/wordpress` (defined in `./bin/setup-plugin-tests.sh`)
   - Always copy files before testing using `./bin/sync-to-wp.sh`
   - Run integration tests with WordPress test framework, in the testing environment
   - Run unit tests with WP_Mock

5. **Code Organization**:
   - Follow PSR-4 autoloading standards
   - Use interfaces for type declarations
   - Implement dependency injection
   - Follow WordPress coding standards

6. **Error Handling**:
   - Use `WP_Error` for WordPress integration
   - Implement proper exception handling
   - Provide meaningful error messages
   - Log errors appropriately

7. **Documentation**:
   - Follow PHPDoc standards
   - Document all public methods
   - Include code examples where helpful
   - See CONTRIBUTING.md for pull request and coding style guidelines

## Constants and Paths
1. **Plugin Constants**:
   - Always use `GL_CPG_*` prefix for plugin constants
   - Core constants defined in plugin main file (`gl-color-palette-generator.php`):
     - `GL_CPG_VERSION`
     - `GL_CPG_PLUGIN_FILE`
     - `GL_CPG_PLUGIN_DIR`
     - `GL_CPG_PLUGIN_URL`
   - Never create duplicate or alternative constant names
   - Never use hardcoded paths

## Static Analysis and Type Safety
1. **PHPStan Guidelines**:
   - Maintain Level 5 compliance
   - Test bootstrap files defined in phpstan.neon
   - Document undefined constants with `@phpstan-type`
   - Pay attention to constant availability in different scopes
   - Ensure proper type hints and return types
   - Common issues to avoid:
     - Never use empty(), use strict comparison instead
     - Use === and !== for boolean comparisons
     - Avoid loose comparisons (== or !=)
     - Always initialize class properties
     - Check for null before using optional parameters
     - Verify method return types match interface definitions
     - Ensure array access on variables that are definitely arrays
     - Don't mix return types (e.g., array|false or string|null)

2. **Bug Prevention**:
   - Prior AI introduced many type-related bugs
   - Always verify interface implementations are complete
   - Check that method signatures match their interfaces exactly
   - Don't trust existing PHPDoc comments - verify against actual code
   - Watch for missing property declarations
   - Be suspicious of any loose type comparisons
   - Question any complex inheritance hierarchies

3. **Interface Usage**:
   - Interfaces are being refactored - DO NOT assume current interfaces are correct
   - Analyze what each interface SHOULD be based on its purpose and best practices
   - Design the ideal interface first, then update implementations to match
   - Document interface changes in CHANGELOG.md
   - Keep interfaces focused (e.g., Color_Constants for color-related constants only)
   - Check existing implementations before changing interfaces

## Code Organization
1. **Namespace and Use Statements**:
   - Always verify namespace matches directory structure
   - Check for missing or unnecessary use statements
   - Follow PSR-4 namespace conventions
   - Keep namespaces aligned with plugin prefix (`GL_Color_Palette_Generator`)

2. **File Structure**:
   - One class per file
   - Filename must match class name
   - Place interfaces in `includes/interfaces`
   - Place abstract classes in `includes/abstracts`
   - Place utilities in `includes/utils`

## Common Over-Design Issues
1. **Service Layer Complexity**:
   - Example: Color_API_Integration depends on non-existent API_Connector, Data_Synchronizer, Cache_Manager
   - Keep API integrations simple - we're just generating color palettes
   - Don't add enterprise-level features like caching/syncing unless specifically needed
   - Question any class that depends on multiple other service classes

2. **Missing Dependencies**:
   - PHPStan will show "unknown class" errors for over-designed features
   - These often indicate scope creep or unnecessary complexity
   - Consider removing the feature or simplifying it drastically
   - Example errors:
     ```
     Property has unknown class GL_Color_Palette_Generator\Services\API_Connector as its type
     Call to method connect() on an unknown class
     ```

3. **Dependency Chains**:
   - Watch for classes that require many other classes to work
   - Each dependency increases complexity and maintenance burden
   - Ask: "Does this feature directly help WordPress theme developers?"
   - If not, consider removing or simplifying

## Project Scope and Clean-up
1. **Target Users**:
   - WordPress website theme developers
   - Small business website developers
   - Focus on simplicity and ease of use

2. **Code Review Needed**:
   - Several files were over-designed beyond plugin scope
   - Some features were planned but never fully implemented
   - Ask if unclear whether code or entire files should be deleted
   - Prefer removing unused/incomplete features over maintaining them

## Project Overview
The GL Color Palette Generator creates accessible color palettes for WordPress themes with:

### Core Features
1. **Color Generation**:
   - 1-3 main colors (primary, secondary, tertiary)
   - 1 accent color
   - 4 variations per color (lighter, light, dark, darker)
   - Neutral colors plus black/white

2. **Color Properties**:
   - Artist Name (e.g., "Energetic Sunrise")
   - Emotional Feeling (e.g., Joyful)
   - Hex code (e.g., #f5db37)
   - Optional functional names

3. **Accessibility Requirements**:
   - Meet WCAG contrast targets
   - Visually distinct colors
   - Light colors readable with black text
   - Dark colors readable with white text

4. **Output Formats**:
   - WordPress color palette display
   - Theme Style Variations (theme.json)
   - Light and Dark themes, browser switchable using CSS light-dark()
   - Style Variation permutations
   - Downloadable .zip of variations

### Testing Notes
- Keep adding lines like " * @bootstrap wp-mock" to specify which PHPUnit bootstrap method to use, see .github/issues/improve-test-bootstrap-robustness.md