# Classes, Models, and MVC Architecture in WordPress Plugins

## Object-Oriented Concepts

### Classes
Classes are the fundamental building blocks of object-oriented programming. They define:
- **Properties** (data)
- **Methods** (behavior)
- **Visibility** (public, protected, private)

In our plugin, classes represent both data structures and services:
```php
class Color_Palette {
    private array $colors;
    private array $metadata;

    public function add_color(string $color): void {
        // Implementation
    }
}
```

### Objects
Objects are instances of classes. They represent concrete entities in our system:
```php
$palette = new Color_Palette();
$palette->add_color('#FF0000');
```

### Interfaces
Interfaces define contracts that classes must fulfill. They specify what methods a class must implement without defining how:
```php
interface Color_Palette_Generator_Interface {
    public function generate_palette(array $options): Color_Palette;
}
```

## Models in Software Architecture

### What is a Model?
A model represents a domain entity and encapsulates:
1. **Data structure** - The properties that define the entity
2. **Business rules** - Validation and constraints
3. **Data operations** - Methods to manipulate the entity's state

Models typically:
- Represent real-world concepts (Color_Palette, Color_Scheme)
- Contain little to no application logic
- Focus on data integrity and structure

### Model Classes vs. Other Types of Classes
- **Model classes** focus on representing domain entities and their relationships (e.g., `Palette`, `Color`)
- **Service classes** focus on performing operations on models (e.g., `Palette_Generator`, `Color_Converter`)
- **Utility classes** provide helper functions not tied to specific models (e.g., `Array_Utils`, `String_Utils`)

## MVC Architecture

### Model-View-Controller Pattern
MVC is an architectural pattern that separates an application into three components:

1. **Model**: Represents data and business logic
   - Color_Palette, Color_Scheme models
   - Data validation rules
   - Business constraints

2. **View**: Presents data to users
   - WordPress admin pages
   - Shortcode output
   - Block editor components
   - Theme.json output

3. **Controller**: Handles user input and updates models/views
   - WordPress hooks and actions
   - AJAX handlers
   - REST API endpoints

### Services in Extended MVC

Modern MVC often includes a Service layer that sits between Models and Controllers:

**Services**: Encapsulate business logic and operations on models
- Perform complex calculations or transformations
- Implement business rules that span multiple models
- Provide utility functions for models and controllers

**Where Color Contrast Checking Belongs**:
Color contrast checking is a perfect example of Service layer functionality because:
- It involves calculations (contrast ratios, WCAG compliance)
- It operates on model data (colors) but isn't about storing/retrieving that data
- It implements business rules (accessibility standards)
- It may be used by multiple controllers or views

Example service:
```php
class Accessibility_Service {
    public function check_contrast(string $foreground, string $background): float {
        // Calculate contrast ratio
        return $ratio;
    }

    public function meets_wcag_aa(string $foreground, string $background): bool {
        $ratio = $this->check_contrast($foreground, $background);
        return $ratio >= 4.5; // AA standard for normal text
    }
}
```

### MVC in WordPress Plugins

WordPress doesn't strictly enforce MVC, but we can adapt the pattern:

- **Models**: Our data classes (Color_Palette, Color_Scheme)
- **Services**: Business logic (Contrast_Checker, Palette_Generator)
- **Views**: Admin pages, shortcodes, blocks
- **Controllers**: Hook callbacks, AJAX handlers

## Application to GL Color Palette Generator

### Recommended Structure

Following our naming conventions where models use base names and services use action-specific suffixes:

#### Palette Management
Contains palette-related models and services:
```
includes/palette/
  ├── class-palette.php                 # Model representing a color palette
  ├── class-palette-generator.php       # Service for generating palettes
  ├── class-palette-storage.php         # Service for storing/retrieving palettes
  └── class-palette-transformer.php     # Service for transforming palettes
```

#### Color Manipulation
Contains color-related classes:
```
includes/color/
  ├── class-color.php                   # Model representing a single color
  ├── class-color-accessibility.php     # Service for accessibility checks
  ├── class-color-converter.php         # Service for color format conversion
  └── class-color-namer.php             # Service for naming colors
```

#### User Interface
Contains UI components and controllers:
```
includes/ui/
  ├── class-admin-page.php              # Main admin interface
  ├── class-settings-page.php           # Plugin settings page
  ├── components/
  │   ├── class-palette-display.php     # Component for displaying palettes
  │   └── class-business-questionnaire.php # Form for gathering business context
  └── blocks/
      ├── class-palette-block.php       # Block for embedding palettes
      └── class-palette-generator-block.php # Block for generating palettes
```

### Benefits of This Approach

1. **Clear separation of concerns**:
   - Models focus on data structure and validation
   - Services focus on operations and transformations
   - Controllers focus on WordPress integration

2. **Improved testability**:
   - Models can be tested in isolation
   - Services can be tested with mock models
   - Controllers can be tested with WP_Mock

3. **Better maintainability**:
   - Each class has a single responsibility
   - Dependencies are explicit and manageable
   - New features can be added without modifying existing code

4. **Efficient testing with groups**:
   - Tests can be run together as groups
   - Architectural component test groups (across all Models, Services, or Controllers)
   - Feature-centric test groups can span multiple components
   - Faster feedback cycles by running only relevant tests for the area being worked on

## Practical Example: Color Palette Generation

### Before (Mixed Responsibilities)
```php
class Palette {
    private $colors = [];

    public function generate_from_image($image_path) {
        // Extract colors from image
        // Set properties
        return $this;
    }

    public function export_to_css() {
        // Generate CSS
    }
}
```

### After (Separation of Concerns)

**Model (includes/palette/class-palette.php)**:
```php
namespace GL_Color_Palette_Generator\Palette;

class Palette {
    private $colors = [];

    public function add_color($color) {
        // Validate and add color
    }

    public function get_colors() {
        return $this->colors;
    }
}
```

**Service (includes/palette/class-palette-generator.php)**:
```php
namespace GL_Color_Palette_Generator\Palette;

class Palette_Generator {
    public function generate_from_image($image_path) {
        $palette = new Palette();
        // Extract colors from image
        // Add colors to palette
        return $palette;
    }
}
```

**Formatter Service (includes/output/class-css-formatter.php)**:
```php
namespace GL_Color_Palette_Generator\Output;

use GL_Color_Palette_Generator\Palette\Palette;

class CSS_Formatter {
    public function format_palette(Palette $palette) {
        // Generate CSS from palette
    }
}
```

## Conclusion

Adopting a clear architectural approach with proper separation between models, services, and controllers will significantly improve the GL Color Palette Generator plugin. This structure will:

1. Eliminate duplicate implementations
2. Clarify responsibilities
3. Improve testability
4. Enhance maintainability
5. Make the codebase more approachable for new developers

As we rebuild the plugin, we should focus on creating a clean domain model first, then building services that operate on that model, and finally integrating with WordPress through controllers.

## Test File Naming Conventions for MVC Architecture

To maintain clarity and avoid naming conflicts, test files should follow these conventions:

### Directory Structure

```
tests/
  ├── unit/
  │   ├── models/             # Tests for model classes
  │   ├── services/           # Tests for service classes
  │   └── interfaces/         # Tests for interfaces
  ├── wp-mock/
  │   ├── controllers/        # Tests for WordPress integration
  │   └── views/              # Tests for output rendering
  └── integration/            # End-to-end tests
```

### File Naming

1. **Model Tests**:
   - Filename: `test-{model-name}.php`
   - Class: `Test_{Model_Name}`
   - Example: `test-color-palette.php` with class `Test_Color_Palette`

2. **Service Tests**:
   - Filename: `test-{service-name}.php`
   - Class: `Test_{Service_Name}`
   - Example: `test-palette-generator-service.php` with class `Test_Palette_Generator_Service`

3. **Interface Tests**:
   - Filename: `test-{interface-name}.php`
   - Class: `Test_{Interface_Name}_Interface`
   - Example: `test-color-palette-generator.php` with class `Test_Color_Palette_Generator_Interface`

4. **Controller Tests**:
   - Filename: `test-{controller-name}.php`
   - Class: `Test_{Controller_Name}`
   - Example: `test-admin-controller.php` with class `Test_Admin_Controller`

5. **View Tests**:
   - Filename: `test-{view-name}.php`
   - Class: `Test_{View_Name}_View`
   - Example: `test-palette-preview.php` with class `Test_Palette_Preview_View`

### Namespace Conventions

Each test should be in a namespace that reflects its location and purpose:

```php
namespace GL_Color_Palette_Generator\Tests\Unit\Models;
// or
namespace GL_Color_Palette_Generator\Tests\WP_Mock\Controllers;
```

#### Namespace Import Approaches

PHP offers three ways to reference namespaced elements:

1. **Relative after this namespace** (relative import):
   ```php
   // In namespace GL_Color_Palette_Generator\Tests
   use Unit\Models\Color_Palette_Test;  // Refers to GL_Color_Palette_Generator\Tests\Unit\Models\Color_Palette_Test
   ```

2. **Fully qualified from current namespace** (explicit path):
   ```php
   // In namespace GL_Color_Palette_Generator\Tests
   use GL_Color_Palette_Generator\Tests\Unit\Models\Color_Palette_Test;  // Explicitly starts from current namespace
   ```

3. **Fully qualified** (absolute from global namespace):
   ```php
   // In any namespace
   use \GL_Color_Palette_Generator\Tests\Unit\Models\Color_Palette_Test;  // Always refers to the same class
   use \WP_Post;  // WordPress core classes should use this approach
   ```

   This approach is particularly important for WordPress core classes and functions, which are in the global namespace. Always use a leading backslash when referencing WordPress core elements.

The "fully qualified from current namespace" approach (option 2) is recommended for clarity and maintainability. It explicitly shows the full path while maintaining flexibility for your code to be embedded in other namespace hierarchies if needed.

However, when directly referencing a class from a completely different namespace tree within your code (not in a use statement), consider using the leading backslash for clarity. Without it, PHP first looks in the current namespace before checking the global namespace.

By following these conventions, we can avoid the duplicate class issues we've encountered and create a more maintainable test suite that aligns with our MVC architecture.
