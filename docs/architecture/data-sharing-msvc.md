# Data and Configuration Sharing in MSVC Architecture

This document outlines how data and configuration are shared between components in our Model-Service-View-Controller (MSVC) architecture. It addresses common pitfalls in component communication and provides practical patterns for implementation.

## Core Principles

1. **Explicit Dependencies**: Components should explicitly declare what they need
2. **Single Source of Truth**: Data should have one authoritative source
3. **Unidirectional Data Flow**: Data should flow in a predictable direction
4. **Separation of Concerns**: Each component has a specific responsibility

## MSVC Data Flow Architecture

### Model-Service-View-Controller Explained

Our MSVC architecture extends traditional MVC by adding a Service layer:

1. **Models**: Data structures and business logic
   - Represent color palettes, individual colors, and their properties
   - Contain validation rules and data transformation methods
   - Example: `Color_Palette` model containing color data and relationships

2. **Services**: Business operations and external integrations
   - Handle complex operations like palette generation algorithms
   - Manage external API interactions (OpenAI, Adobe Color, etc.)
   - Provide reusable functionality across controllers
   - Example: `Palette_Generator_Service` that creates new palettes

3. **Views**: User interface presentation
   - Render data for users in the admin area or frontend
   - Handle display logic and formatting
   - Example: `Palette_Display_View` that renders a color palette

4. **Controllers**: Request handling and coordination
   - Process user input and coordinate responses
   - Delegate to appropriate services and models
   - Example: `Admin_Controller` handling form submissions

### Data Flow Patterns

#### 1. Configuration Data Flow

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│ Config File │────▶│ Config      │────▶│ Service     │────▶│ Model       │
│ (.env)      │     │ Repository  │     │ Layer       │     │ Layer       │
└─────────────┘     └─────────────┘     └─────────────┘     └─────────────┘
                           │                   │                   │
                           ▼                   ▼                   ▼
                    ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
                    │ Controller  │◀────│ View        │◀────│ Template    │
                    │ Layer       │     │ Layer       │     │ Tags        │
                    └─────────────┘     └─────────────┘     └─────────────┘
```

#### 2. Color Palette Data Flow

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│ User Input  │────▶│ Controller  │────▶│ Service     │────▶│ Model       │
│ (Form/API)  │     │ Layer       │     │ Layer       │     │ Layer       │
└─────────────┘     └─────────────┘     └─────────────┘     └─────────────┘
                                                                   │
                                                                   ▼
┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│ User        │◀────│ View        │◀────│ Controller  │◀────│ Database    │
│ Interface   │     │ Layer       │     │ Layer       │     │ Layer       │
└─────────────┘     └─────────────┘     └─────────────┘     └─────────────┘
```

## Configuration Management

### Configuration Repository

**Bad Approach (Problematic)**: Having each class directly access configuration from multiple sources (environment variables, .env files, database settings in wp-config.php, etc.). This creates maintenance nightmares, makes testing difficult, and leads to inconsistent configuration handling.

**Better Approach**: Use a dedicated Configuration Repository:

```php
namespace GL_Color_Palette_Generator\Core;

/**
 * Central configuration repository
 */
class Config_Repository {
    private static $instance = null;
    private $settings = [];

    /**
     * Get configuration value
     *
     * @param string $key     Configuration key
     * @param mixed  $default Default value if not found
     * @return mixed
     */
    public function get(string $key, $default = null) {
        return $this->settings[$key] ?? $default;
    }

    /**
     * Set configuration value
     *
     * @param string $key   Configuration key
     * @param mixed  $value Configuration value
     */
    public function set(string $key, $value): void {
        $this->settings[$key] = $value;
    }

    /**
     * Get singleton instance
     *
     * @return Config_Repository
     */
    public static function get_instance(): Config_Repository {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load configuration from file
     *
     * @param string $file_path Path to configuration file
     */
    public function load_from_file(string $file_path): void {
        if (file_exists($file_path)) {
            $config = include $file_path;
            if (is_array($config)) {
                $this->settings = array_merge($this->settings, $config);
            }
        }
    }
}
```

### Service Container

**Bad Approach (Problematic)**: Using global variables to store and share application state (like settings, database connections, or service instances). This creates hidden dependencies, makes testing nearly impossible, and leads to unpredictable behavior when multiple components modify the same global state.

**Better Approach**: Use a Service Container for dependency management:

```php
namespace GL_Color_Palette_Generator\Core;

/**
 * Service container for dependency management
 */
class Service_Container {
    private static $instance = null;
    private $services = [];

    /**
     * Register a service
     *
     * @param string   $id      Service identifier
     * @param callable $factory Service factory function
     */
    public function register(string $id, callable $factory): void {
        $this->services[$id] = [
            'factory' => $factory,
            'instance' => null,
        ];
    }

    /**
     * Get a service
     *
     * @param string $id Service identifier
     * @return mixed
     * @throws \Exception If service not found
     */
    public function get(string $id) {
        if (!isset($this->services[$id])) {
            throw new \Exception("Service not found: $id");
        }

        if ($this->services[$id]['instance'] === null) {
            $this->services[$id]['instance'] = ($this->services[$id]['factory'])($this);
        }

        return $this->services[$id]['instance'];
    }

    /**
     * Get singleton instance
     *
     * @return Service_Container
     */
    public static function get_instance(): Service_Container {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
```

## Practical Implementation

### Bootstrap Process

```php
// In plugin main file
function gl_cpg_init() {
    // Load configuration
    $config = GL_Color_Palette_Generator\Core\Config_Repository::get_instance();
    $config->load_from_file(GL_CPG_PLUGIN_DIR . 'config/settings.php');

    // Set up service container
    $container = GL_Color_Palette_Generator\Core\Service_Container::get_instance();

    // Register services
    $container->register('palette_generator', function($c) {
        return new GL_Color_Palette_Generator\Services\Palette_Generator_Service(
            $c->get('config'),
            $c->get('api_client')
        );
    });

    $container->register('config', function($c) {
        return GL_Color_Palette_Generator\Core\Config_Repository::get_instance();
    });

    $container->register('api_client', function($c) {
        $config = $c->get('config');
        return new GL_Color_Palette_Generator\Services\API_Client(
            $config->get('api_key'),
            $config->get('api_endpoint')
        );
    });

    // Initialize controllers
    $admin_controller = new GL_Color_Palette_Generator\Controllers\Admin_Controller($container);
    $admin_controller->init();
}
```

### Using Services in Controllers

```php
namespace GL_Color_Palette_Generator\Controllers;

use GL_Color_Palette_Generator\Core\Service_Container;

class Admin_Controller {
    private $container;
    private $palette_generator;

    public function __construct(Service_Container $container) {
        $this->container = $container;
        $this->palette_generator = $container->get('palette_generator');
    }

    public function init(): void {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_post_generate_palette', [$this, 'handle_generate_palette']);
    }

    public function handle_generate_palette(): void {
        // Get form data
        $base_color = sanitize_text_field($_POST['base_color'] ?? '');
        $mood = sanitize_text_field($_POST['mood'] ?? '');

        // Use service to generate palette
        $palette = $this->palette_generator->generate_from_base_color($base_color, $mood);

        // Store in database and redirect
        // ...
    }
}
```

### Using Models

```php
namespace GL_Color_Palette_Generator\Models;

class Color_Palette {
    private $id;
    private $name;
    private $colors = [];
    private $mood;

    public function __construct(string $name, array $colors, string $mood = '') {
        $this->name = $name;
        $this->set_colors($colors);
        $this->mood = $mood;
    }

    public function set_colors(array $colors): void {
        // Validate colors
        foreach ($colors as $role => $hex) {
            $this->colors[$role] = new Color($hex, $role);
        }
    }

    public function get_color(string $role): ?Color {
        return $this->colors[$role] ?? null;
    }

    public function get_primary_color(): ?Color {
        return $this->get_color('primary');
    }

    public function get_secondary_color(): ?Color {
        return $this->get_color('secondary');
    }

    // Other methods...
}
```

### Using Views

```php
namespace GL_Color_Palette_Generator\Views;

use GL_Color_Palette_Generator\Models\Color_Palette;

class Palette_Display_View {
    public function render(Color_Palette $palette): string {
        $primary = $palette->get_primary_color();
        $secondary = $palette->get_secondary_color();

        ob_start();
        ?>
        <div class="color-palette">
            <h3><?php echo esc_html($palette->get_name()); ?></h3>
            <div class="color-samples">
                <div class="color-sample primary" style="background-color: <?php echo esc_attr($primary->get_hex()); ?>">
                    <span class="color-name"><?php echo esc_html($primary->get_role()); ?></span>
                    <span class="color-hex"><?php echo esc_html($primary->get_hex()); ?></span>
                </div>
                <div class="color-sample secondary" style="background-color: <?php echo esc_attr($secondary->get_hex()); ?>">
                    <span class="color-name"><?php echo esc_html($secondary->get_role()); ?></span>
                    <span class="color-hex"><?php echo esc_html($secondary->get_hex()); ?></span>
                </div>
                <!-- Additional colors -->
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
```

## Common Pitfalls and Solutions

### 1. Global State Dependency

**Problem**: Similar to the PHPUnit framework's `$loaded_settings` issue, relying on global variables for configuration.

**Solution**: Use the Service Container pattern to explicitly inject dependencies.

### 2. Direct Environment Access

**Problem**: Using functions like `getenv()` directly in components.

**Solution**: Access configuration only through the Config_Repository.

### 3. Tight Coupling Between Components

**Problem**: Classes directly instantiating their dependencies.

**Solution**: Use dependency injection through constructors.

### 4. Duplicated Configuration Logic

**Problem**: Multiple components implementing similar configuration loading logic.

**Solution**: Centralize configuration management in the Config_Repository.

### 5. Inconsistent Data Access

**Problem**: Accessing the same data through different paths.

**Solution**: Establish clear data flow patterns and stick to them.

## Testing Considerations

1. **Unit Testing**: Test each component in isolation with mocked dependencies
2. **Integration Testing**: Test how components work together
3. **Configuration Testing**: Verify configuration is correctly loaded and accessed

This approach ensures that our Color Palette Generator will avoid the data sharing issues we encountered in other projects, while maintaining a clean, maintainable architecture.
