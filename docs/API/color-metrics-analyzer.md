# Color Metrics Analyzer

## Overview
The Color Metrics Analyzer provides comprehensive color analysis capabilities, including accessibility scoring, color combinations, and media compatibility analysis.

## Class Reference

```php
namespace GL_Color_Palette_Generator\Color_Management;

class Color_Metrics_Analyzer implements \GL_Color_Palette_Generator\Interfaces\Color_Metrics_Analyzer
```

## Features

### Color Analysis
- Basic color metrics
- Perceptual analysis
- Cultural significance
- Psychological effects

### Accessibility Analysis
- WCAG compliance checking
- Color blindness considerations
- Readability metrics
- Contrast ratios

### Media Compatibility
- Print output analysis
- Screen display optimization
- Device-specific considerations

## Usage

### Basic Analysis

```php
$analyzer = new Color_Metrics_Analyzer();
$analysis = $analyzer->analyze_color('#FF0000');
```

### Accessibility Scoring

```php
$score = $analyzer->calculate_accessibility_score('#000000');
print_r($score['detailed_scores']);
```

### Color Combinations

```php
$combinations = $analyzer->get_color_combinations('#FF0000');
print_r($combinations['complementary']);
```

## Error Handling

The analyzer includes comprehensive error handling:

```php
try {
    $analysis = $analyzer->analyze_color('#FF0000');
} catch (InvalidColorException $e) {
    // Handle invalid color format
} catch (AnalysisException $e) {
    // Handle analysis errors
}
```

## Performance Optimization

### Caching
Results are automatically cached for improved performance:

```php
// First call - computed
$first = $analyzer->analyze_color('#FF0000');

// Second call - retrieved from cache
$second = $analyzer->analyze_color('#FF0000');
```

### Batch Processing
Process multiple colors efficiently:

```php
$colors = ['#FF0000', '#00FF00', '#0000FF'];
$results = $analyzer->batch_analyze_colors($colors);
```

## Integration

### WordPress Integration
The analyzer integrates seamlessly with WordPress:

```php
// In your plugin or theme
add_action('init', function() {
    $analyzer = new Color_Metrics_Analyzer();
    // Your analysis code here
});
```

### Third-Party Integration
Can be used with external color management systems:

```php
$analyzer = new Color_Metrics_Analyzer();
$analysis = $analyzer->analyze_color($external_color);
```

## See Also
- [Color Utility Documentation](color-utility.md)
- [Color Palette Generator Documentation](color-palette-generator.md)
- [Accessibility Checker Documentation](accessibility-checker.md)

## Hooks and Filters

### Available Hooks

```php
// Before color analysis
add_action('before_color_analysis', function($color) {
    // Your code here
});

// After color analysis
add_action('after_color_analysis', function($color, $results) {
    // Your code here
}, 10, 2);

// Before metrics calculation
add_action('before_metrics_calculation', function($color) {
    // Your code here
});
```

### Available Filters

```php
// Filter analysis results
add_filter('color_analysis_results', function($results, $color) {
    // Modify results
    return $results;
}, 10, 2);

// Filter metric calculations
add_filter('color_metrics', function($metrics, $color) {
    // Modify metrics
    return $metrics;
}, 10, 2);
```

## Database Schema

### Color Metrics Table

```sql
CREATE TABLE `{prefix}_color_metrics` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `color_hex` varchar(7) NOT NULL,
    `metrics_data` longtext NOT NULL,
    `created_at` datetime NOT NULL,
    `updated_at` datetime NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `color_hex` (`color_hex`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Analysis Cache Table

```sql
CREATE TABLE `{prefix}_color_analysis_cache` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `color_hex` varchar(7) NOT NULL,
    `analysis_type` varchar(50) NOT NULL,
    `analysis_data` longtext NOT NULL,
    `created_at` datetime NOT NULL,
    `expires_at` datetime NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `color_analysis` (`color_hex`, `analysis_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## Performance Benchmarks

### Single Color Analysis

| Operation | Average Time | Peak Memory |
|-----------|--------------|-------------|
| Basic Analysis | 0.05s | 2MB |
| Full Analysis | 0.15s | 5MB |
| With Cache | 0.01s | 1MB |

### Batch Processing

| Batch Size | Average Time | Peak Memory |
|------------|--------------|-------------|
| 10 colors | 0.5s | 10MB |
| 50 colors | 2.0s | 25MB |
| 100 colors | 3.5s | 45MB |

### Optimization Tips

1. Enable result caching for frequent operations
2. Use batch processing for multiple colors
3. Implement lazy loading for detailed metrics
4. Consider database indexing for large datasets

### Memory Management

```php
// Configure memory limits
$analyzer->set_memory_limit('256M');

// Enable garbage collection
$analyzer->enable_gc_after_analysis(true);

// Set batch size limits
$analyzer->set_max_batch_size(50);
```
