# User Interface Constants

## Overview
This document inventories the constants used by the user interface components of the GL Color Palette Generator plugin. These constants define important values for UI rendering, form handling, and display options.

## Constants Listing

### GL_Color_Palette_Generator\UI\Constants

#### ADMIN_PAGE_SLUG
- **Value**: `'gl-color-palette-generator'`
- **Purpose**: Defines the slug for the main admin page
- **Usage**: Used in registering admin menus and for URL generation

#### SETTINGS_PAGE_SLUG
- **Value**: `'gl-color-palette-generator-settings'`
- **Purpose**: Defines the slug for the settings page
- **Usage**: Used in registering the settings page and for URL generation

#### ASSETS_VERSION
- **Value**: `'1.0.0'` (will match plugin version)
- **Purpose**: Version string for assets to enable cache busting
- **Usage**: Used when enqueueing scripts and styles

#### CORE_PALETTE_COLORS
- **Value**: `['primary', 'secondary', 'tertiary', 'accent', 'text-color', 'main-background']`
- **Purpose**: Defines the core colors in a palette
- **Usage**: Used when generating and displaying the main palette colors
- **Notes**:
  - 'primary', 'secondary', 'tertiary': Main brand/theme colors
  - 'accent': Highlight or call-to-action color
  - 'text-color': Text color (typically near-black in light mode, near-white in dark mode)
  - 'main-background': Page background color (typically near-white in light mode, near-black in dark mode)
  - For light/dark mode, 'text-color' and 'main-background' values are swapped or inverted

#### WORDPRESS_THEME_COLORS
- **Value**: `['base', 'contrast', 'primary', 'secondary', 'accent-1', 'accent-2', ...]`
- **Purpose**: WordPress theme.json standard color naming
- **Usage**: Used when generating theme.json output
- **Notes**:
  - 'base': Maps to our 'main-background' (page background color)
  - 'contrast': Maps to our 'text-color' (text color)
  - 'primary', 'secondary': Maps directly to our internal structure
  - 'accent-1', 'accent-2', etc.: Maps to our 'accent', 'tertiary', etc.
  - For dark mode, we'll use CSS `light-dark()` function to include both light and dark mode colors in a single theme.json and style.css
  - WordPress themes use different naming conventions than our internal structure
  - Our plugin will map our internal structure to WordPress conventions when generating theme.json

#### COLOR_VARIATIONS
- **Value**: `['lighter', 'light', 'default', 'dark', 'darker']`
- **Purpose**: Defines the variations for each core color, tints and hues of the user-supplied or AI-supplied original color
- **Usage**: Used when generating and displaying color variations

#### NEUTRAL_STEPS
- **Value**: `9` (neutral-100 through neutral-900)
- **Purpose**: Number of steps in the neutral color scale
- **Usage**: Used when generating and displaying neutral colors

#### SYSTEM_COLORS
- **Value**: `['highlight-selection', 'highlight-marker', 'status-success', 'status-error', 'status-warning', 'status-info']`
- **Purpose**: Defines system-specific colors for UI feedback
- **Usage**: Used for status indicators and highlighting

#### UTILITY_COLORS
- **Value**: `['white', 'black', 'transparent']`
- **Purpose**: Defines utility colors available in all palettes
- **Usage**: Used as universal colors across the interface

#### THEME_PALETTE_STRUCTURE
- **Value**: Complex array structure defining the complete palette organization
- **Purpose**: Provides the full structure for theme palette generation
- **Usage**: Used as the master reference for palette generation and display
- **Reference**: Directly maps to `interface-color-constants.php` THEME_PALETTE_STRUCTURE (to be *renamed*)

#### DEFAULT_DISPLAY_MODE
- **Value**: `'grid'`
- **Purpose**: Default display mode for palettes
- **Usage**: Used when rendering palettes without specified display mode

### GL_Color_Palette_Generator\UI\Components\Form_Constants

#### FORM_ACTION
- **Value**: `'gl_generate_palette'`
- **Purpose**: Action name for form submission
- **Usage**: Used in nonce creation and form processing

#### NONCE_NAME
- **Value**: `'gl_palette_generator_nonce'`
- **Purpose**: Name of the nonce field for form security
- **Usage**: Used to validate form submissions

#### REQUIRED_FIELDS
- **Value**: `['primary_color']`
- **Purpose**: List of required form fields
- **Usage**: Used in form validation

### GL_Color_Palette_Generator\UI\Blocks\Block_Constants

#### BLOCK_NAMESPACE
- **Value**: `'gl-color-palette-generator'`
- **Purpose**: Namespace for all plugin blocks
- **Usage**: Used when registering blocks

#### PALETTE_BLOCK_NAME
- **Value**: `'palette'`
- **Purpose**: Name for the palette display block
- **Usage**: Used to register and identify the palette block

#### GENERATOR_BLOCK_NAME
- **Value**: `'generator'`
- **Purpose**: Name for the palette generator block
- **Usage**: Used to register and identify the generator block

#### PREVIEW_BLOCK_NAME
- **Value**: `'preview'`
- **Purpose**: Name for the palette preview block
- **Usage**: Used to register and identify the preview block

### GL_Color_Palette_Generator\UI\Display_Constants

#### COLOR_DISPLAY_FORMATS
- **Value**: `['hex', 'rgb', 'hsl', 'name']`
- **Purpose**: Available formats for displaying color values
- **Usage**: Used in color information display

#### PALETTE_DISPLAY_MODES
- **Value**: `['grid', 'list', 'compact', 'detailed']`
- **Purpose**: Available display modes for palettes
- **Usage**: Used when rendering palettes with different layouts

#### ACCESSIBILITY_LEVELS
- **Value**: `['AA', 'AAA']`
- **Purpose**: WCAG accessibility levels to check
- **Usage**: Used when displaying accessibility information

#### PALETTE_VARIATION_TYPES
- **Value**: `['light', 'dark', 'monochromatic', 'analogous', 'complementary', 'triadic']`
- **Purpose**: Types of palette variations that can be displayed
- **Usage**: Used when generating and displaying palette variations
