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

### Models vs. Regular Classes
- **Models** focus on representing domain entities and their relationships
- **Service classes** focus on performing operations on models
- **Utility classes** provide helper functions not tied to specific models

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

### Current Issues
1. **Duplicate implementations**: Multiple Color_Palette classes in different namespaces
2. **Unclear responsibilities**: Mixing model and service functionality
3. **Inconsistent naming**: Lack of clear distinction between models and services

### Recommended Structure

#### Models Namespace
Contains domain entities:
```
Models/
  ├── Color_Palette.php
  ├── Color_Scheme.php
  ├── Theme.php
  └── User_Preferences.php
```

#### Services Namespace
Contains classes that operate on models:
```
Services/
  ├── Palette_Generator.php
  ├── Theme_Builder.php
  ├── Color_Converter.php
  ├── Accessibility_Service.php
  ├── Contrast_Checker.php
  └── Analytics_Service.php
```

#### Controllers Namespace
Contains WordPress integration points:
```
Controllers/
  ├── Admin_Controller.php
  ├── Block_Controller.php
  ├── REST_Controller.php
  └── Shortcode_Controller.php
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

## Practical Example: Color Palette Generation

### Before (Mixed Responsibilities)
```php
class Color_Palette {
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

**Model**:
```php
class Color_Palette {
    private $colors = [];

    public function add_color($color) {
        // Validate and add color
    }

    public function get_colors() {
        return $this->colors;
    }
}
```

**Service**:
```php
class Palette_Generator_Service {
    public function generate_from_image($image_path) {
        $palette = new Color_Palette();
        // Extract colors from image
        // Add colors to palette
        return $palette;
    }
}
```

**Formatter Service**:
```php
class Palette_Formatter_Service {
    public function export_to_css(Color_Palette $palette) {
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
