# Color Palette Generator API Documentation

## Rate Limiting

The API implements rate limiting to ensure fair usage and system stability.

### Rate Limit Headers

All API responses include the following headers:
- `X-RateLimit-Limit`: Maximum number of requests allowed per window
- `X-RateLimit-Remaining`: Number of requests remaining in current window
- `X-RateLimit-Reset`: Timestamp when the rate limit window resets

### Rate Limit Configuration

Default limits:
- 1000 requests per hour for authenticated users
- 100 requests per hour for unauthenticated users

When rate limit is exceeded, the API returns:
- Status code: `429 Too Many Requests`
- Error message explaining when you can retry

### Best Practices

1. Monitor rate limit headers in responses
2. Implement exponential backoff when limits are reached
3. Cache responses when possible
4. Use authentication for higher limits

## Provider Configuration

The plugin supports multiple AI providers through a standardized configuration interface.

### Adding a New Provider

1. Create a new provider configuration class that extends `Provider_Config`:

```php
namespace GL_Color_Palette_Generator\Providers;
class New_Provider_Config extends Provider_Config {
    /**
     * Returns the provider name
     * @return string
     */
    public function get_name(): string {
        return 'new_provider';
    }
    /**
     * Implement other required methods...
     */
}
```

### Register the provider in `includes/class-provider-registry.php`

### Available Providers

#### OpenAI
- Models: GPT-4, GPT-3.5 Turbo
- API Key Format: `sk-...`
- Endpoint: `https://api.openai.com/v1/chat/completions`

#### Anthropic
- Models: Claude 3 Sonnet, Claude 3 Opus, Claude 3 Haiku
- API Key Format: `sk-ant-...`
- Endpoint: `https://api.anthropic.com/v1/messages`

## Color Analysis

### Harmony Analysis

The `analyze_harmony()` method provides:
- Color scheme detection
- Temperature analysis
- Contrast calculations
- Balance measurements
- Accessibility recommendations

### Usage Example
```php
$analyzer = new Color_Analysis();
$colors = [
'primary' => ['hex' => '#2C3E50'],
'secondary' => ['hex' => '#E74C3C']
];
$analysis = $analyzer->analyze_harmony($colors);
```

## Hooks and Filters

### Actions
```php
/**
 * Fired before generating a color palette
 * @param array $context Generation context
 */
do_action('gl_color_palette_before_generate', $context);

/**
 * Fired after generating a color palette
 * @param array $palette Generated palette
 * @param array $context Generation context
 */
do_action('gl_color_palette_after_generate', $palette, $context);
```

### Filters
```php
/**
 * Filter the AI provider configuration
 * @param array $config Provider configuration
 * @param string $provider_name Provider name
 * @return array Modified provider configuration
 */
$config = apply_filters('gl_color_palette_provider_config', $config, $provider_name);

/**
 * Filter the generated palette before returning
 * @param array $palette Generated palette
 * @param array $context Generation context
 * @return array Modified palette
 */
$palette = apply_filters('gl_color_palette_generated_palette', $palette, $context);
```

## Advanced Usage

### Custom Color Generation

You can implement custom color generation logic by creating a class that implements the `Color_Generator` interface:

```php
namespace YourNamespace;
use GL_Color_Palette_Generator\Interfaces\Color_Generator;
class Custom_Generator implements Color_Generator {
    /**
     * Generates a color palette
     * @return array
     */
    public function generate_palette(): array {
        // Your custom generation logic
        return [
            'primary' => ['hex' => '#000000'],
            'secondary' => ['hex' => '#FFFFFF']
        ];
    }

    /**
     * Sets the generation context
     * @param array $context
     */
    public function set_context(array $context): void {
        // Handle context data
    }
}
```

### Color Utilities

```php
// Convert hex to RGB
$rgb = Color_Utils::hex_to_rgb('#FF0000');
// Returns: ['r' => 255, 'g' => 0, 'b' => 0]

// Calculate relative luminance
$luminance = Color_Utils::get_relative_luminance('#FF0000');

// Convert RGB to HSL
$hsl = Color_Utils::rgb_to_hsl(255, 0, 0);
// Returns: ['h' => 0, 's' => 100, 'l' => 50]
```

### Event Handling

```php
// Register a custom event handler
add_action('gl_color_palette_before_generate', function($context) {
    // Log generation attempt
    error_log('Generating palette with context: ' . print_r($context, true));
});

// Modify the generated palette
add_filter('gl_color_palette_generated_palette', function($palette, $context) {
    // Add custom metadata
    $palette['metadata'] = [
        'generated_at' => current_time('mysql'),
        'context' => $context
    ];
    return $palette;
}, 10, 2);
```

## API Endpoints

### Generate Palette

```php
// Generate a palette
POST /wp-json/gl-color-palette/v1/generate
{
    "context": {
        "industry": "technology",
        "mood": "professional"
    }
}
```

### Analyze Colors

```php
// Analyze existing colors
POST /wp-json/gl-color-palette/v1/analyze
{
    "colors": [
        {"hex": "#FF0000"},
        {"hex": "#00FF00"}
    ]
}
```

## Error Handling

```php
use GL_Color_Palette_Generator\Exceptions\Provider_Exception;
use GL_Color_Palette_Generator\Exceptions\Validation_Exception;
try {
    $generator->generate_palette();
} catch (Provider_Exception $e) {
    // Handle API provider errors
} catch (Validation_Exception $e) {
    // Handle validation errors
}
```

php:tests/test-ajax-handler.php
```php
<?php
namespace GL_Color_Palette_Generator\Tests;
use GL_Color_Palette_Generator\Ajax_Handler;
use WP_UnitTestCase;
class Test_Ajax_Handler extends WP_UnitTestCase {
    private $ajax_handler;
    private $user_id;

    public function setUp(): void {
        parent::setUp();
        $this->ajax_handler = new Ajax_Handler();
        // Create and set admin user
        $this->user_id = $this->factory->user->create([
            'role' => 'administrator'
        ]);
        wp_set_current_user($this->user_id);
    }

    public function tearDown(): void {
        parent::tearDown();
        wp_delete_user($this->user_id);
    }

    /**
     * @test
     */
    public function test_test_api_connection_requires_nonce(): void {
        $this->expectException('WPAjaxDieStopException');
        $POST['provider'] = 'openai';
        $POST['api_key'] = 'sk-test123';
        $this->handleAjax('gl_test_api_connection');
    }

    /**
     * @test
     */
    public function test_test_api_connection_requires_admin(): void {
        wp_set_current_user(0);
        $POST['provider'] = 'openai';
        $POST['api_key'] = 'sk-test123';
        $POST['nonce'] = wp_create_nonce('gl_color_palette_nonce');
        try {
            $this->handleAjax('gl_test_api_connection');
        } catch (\WPAjaxDieContinueException $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertFalse($response['success']);
            $this->assertStringContainsString('Insufficient permissions', $response['data']['message']);
        }
    }

    /**
     * @test
     */
    public function test_test_api_connection_validates_provider(): void {
        $POST['provider'] = 'invalid_provider';
        $POST['api_key'] = 'sk-test123';
        $POST['nonce'] = wp_create_nonce('gl_color_palette_nonce');
        try {
            $this->handleAjax('gl_test_api_connection');
        } catch (\WPAjaxDieContinueException $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertFalse($response['success']);
            $this->assertStringContainsString('Invalid provider', $response['data']['message']);
        }
    }

    /**
     * @test
     */
    public function test_save_settings_saves_valid_data(): void {
        $POST['nonce'] = wp_create_nonce('gl_color_palette_nonce');
        $POST['settings'] = [
            'ai_provider' => 'openai',
            'api_key' => 'sk-test123',
            'openai_model' => 'gpt-4'
        ];
        try {
            $this->handleAjax('gl_save_settings');
        } catch (\WPAjaxDieContinueException $e) {
            $response = json_decode($e->getMessage(), true);
            $this->assertTrue($response['success']);
            // Verify settings were saved
            $this->assertEquals('openai', get_option('gl_color_palette_ai_provider'));
            $this->assertEquals('sk-test123', get_option('gl_color_palette_api_key'));
            $this->assertEquals('gpt-4', get_option('gl_color_palette_openai_model'));
        }
    }
}
```

css:assets/css/admin/components.css
```css
/* Color Preview Component */
.color-preview {
    display: flex;
    align-items: center;
    margin: 15px 0;
    padding: 15px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.color-swatch {
    width: 40px;
    height: 40px;
    border-radius: 4px;
    margin-right: 15px;
    border: 1px solid rgba(0, 0, 0, 0.1);
}

.color-info {
    flex-grow: 1;
}

.color-hex {
    font-family: monospace;
    font-size: 14px;
    color: #666;
}

.color-name {
    font-weight: 500;
    margin-bottom: 5px;
}

/* Color Palette Grid */
.palette-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

/* Analysis Card */
.analysis-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
    margin: 15px 0;
}

.analysis-header {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
}

.analysis-icon {
    margin-right: 10px;
    color: #2271b1;
}

.analysis-title {
    font-size: 16px;
    font-weight: 500;
    margin: 0;
}

.analysis-content {
    color: #666;
}

/* Accessibility Indicators */
.accessibility-indicator {
    display: inline-flex;
    align-items: center;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 500;
    margin-right: 10px;
}

.accessibility-indicator.wcag-aa {
    background: #e6f3e6;
    color: #1e4620;
}

.accessibility-indicator.wcag-aaa {
    background: #d4edda;
    color: #155724;
}

.accessibility-indicator.wcag-fail {
    background: #f8d7da;
    color: #721c24;
}

/* Loading States */
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 100;
}

.loading-spinner {
    border: 3px solid #f3f3f3;
    border-top: 3px solid #2271b1;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Toast Notifications */
.toast-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
}

.toast {
    background: #333;
    color: #fff;
    padding: 12px 24px;
    border-radius: 4px;
    margin-top: 10px;
    display: flex;
    align-items: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    animation: slideIn 0.3s ease-out;
}

.toast.success {
    background: #28a745;
}

.toast.error {
    background: #dc3545;
}

@keyframes slideIn {
    from { transform: translateX(100%); }
    to { transform: translateX(0); }
}
