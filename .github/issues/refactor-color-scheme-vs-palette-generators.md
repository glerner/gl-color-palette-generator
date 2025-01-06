# Refactor Color Scheme Generator vs Color Palette Generator

## Current Structure Analysis

### Color_Scheme_Generator (Low-Level)
- Focused on pure color theory calculations
- Handles specific color scheme types (monochromatic, analogous, etc.)
- Performs basic color math and transformations
- Currently has some image processing that should be moved

### Color_Palette_Generator (High-Level)
- Handles AI-enhanced palette generation
- Manages context-aware color role assignment
- Uses Color_Scheme_Generator for basic operations
- Has sophisticated image analysis with AI integration

## Issues

1. Method overlap between classes causing confusion
2. Some responsibilities are in the wrong class
3. Similar method names with different implementations
4. Unclear separation of concerns

## Proposed Changes

### Methods to Move/Refactor

#### Move to Color_Palette_Generator Only
- [generate_from_image()](cci:1://file:///home/george/sites/gl-color-palette-generator/tests/wp-mock/color-management/test-class-color-scheme-generator.php:317:4-345:5) (with AI enhancement)
- `extract_colors_from_image()` (with role assignment)
- All AI-related color generation methods

#### Keep in Color_Scheme_Generator
- All basic color scheme generation methods:
  - [generate_monochromatic()](cci:1://file:///home/george/sites/gl-color-palette-generator/tests/wp-mock/color-management/test-class-color-scheme-generator.php:75:4-94:5)
  - [generate_analogous()](cci:1://file:///home/george/sites/gl-color-palette-generator/tests/wp-mock/color-management/test-class-color-scheme-generator.php:96:4-115:5)
  - [generate_complementary()](cci:1://file:///home/george/sites/gl-color-palette-generator/tests/wp-mock/color-management/test-class-color-scheme-generator.php:117:4-136:5)
  - [generate_triadic()](cci:1://file:///home/george/sites/gl-color-palette-generator/tests/wp-mock/color-management/test-class-color-scheme-generator.php:159:4-178:5)
  - etc.

### Interface Updates Needed
1. Update `interface-color-scheme-generator.php` to remove image-related methods
2. Update `interface-color-palette-generator.php` to include all AI and image processing methods

## Benefits
- Clear separation of concerns
- More maintainable codebase
- Better testability
- Clearer documentation
- Easier to extend functionality

## Implementation Steps
1. Move image processing methods to Color_Palette_Generator
2. Update interfaces
3. Update tests to reflect new structure
4. Update documentation
5. Add deprecation notices if needed

## Questions to Consider
- Should we rename the classes to better reflect their responsibilities?
- Do we need additional interfaces for AI-specific operations?
- Should we create a separate class for image processing?

Labels: refactor, architecture, enhancement
