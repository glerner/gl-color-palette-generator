Refactor: Align ML Color Engine naming with Color Constants Interface

The color naming system in `ML_Color_Engine::generate_color_name()` is inconsistent with the color standards defined in `Color_Constants` interface.

## Current Implementation
In `includes/generators/class-ml-color-engine.php`:
- Uses fixed hue ranges for basic color names (e.g., Red: 0-30°)
- Simple light/dark variations without WCAG consideration
- Basic color naming that doesn't align with theme structure

## Expected Behavior
Should align with `includes/interfaces/interface-color-constants.php`:
- Use defined `COLOR_WHEEL_ROLES` for primary/secondary/tertiary colors
- Follow `COLOR_VARIATIONS` for light/dark variations
- Respect `THEME_PALETTE_STRUCTURE` for naming consistency
- Consider `WCAG_CONTRAST` constants for variations

## Proposed Changes
1. Refactor `generate_color_name()` to:
   - Use color wheel roles from `Color_Constants::COLOR_WHEEL_ROLES`
   - Apply variations based on `Color_Constants::COLOR_VARIATIONS`
   - Generate names that fit `Color_Constants::THEME_PALETTE_STRUCTURE`
   - Validate variations against WCAG contrast requirements

2. Add integration between ML engine and color constants:
   - Import and use constants from the interface
   - Validate generated colors against defined standards
   - Ensure consistent naming across the codebase

3. Update tests to verify:
   - Color names match defined roles
   - Variations meet WCAG requirements
   - Generated palettes follow theme structure

## Tasks
- [ ] Update `generate_color_name()` method
- [ ] Add integration with `Color_Constants` interface
- [ ] Update unit tests
- [ ] Document new color naming approach
- [ ] Validate WCAG compliance
