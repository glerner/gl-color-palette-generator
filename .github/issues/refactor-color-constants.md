# Refactor Color Constants from Interface to Class

## Description
The `interface-color-constants.php` file in `includes/interfaces/` is not actually an interface but a constants container. It should be refactored to better reflect its purpose and follow our architecture patterns.

## Current State
- File: `includes/interfaces/interface-color-constants.php`
- Type: Interface (incorrectly)
- Purpose: Contains color-related constants used throughout the plugin
- Location: In interfaces/ directory despite not being an interface

## Proposed Changes
1. Rename file to `class-color-constants.php`
2. Move to `includes/types/` directory
3. Change from interface to class
4. Update all references to use new namespace
5. Add `@bootstrap wp-mock` since it's a pure constants container

## Impact
### Files to Update
- All files that import Color_Constants interface
- Test files that use these constants
- Documentation referencing the constants

### No Functional Changes
- All constant values remain the same
- No change to how constants are used
- Only architectural/organizational improvement

## Implementation Steps
1. Create new file in types/
2. Copy constants to new location
3. Update file header and class definition
4. Update namespace references
5. Run tests to verify no breaks
6. Remove old interface file
7. Update documentation

## Related
- Color management classes
- Theme generation
- Color validation
