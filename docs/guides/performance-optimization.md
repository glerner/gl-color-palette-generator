# Performance Optimization Guide

## Overview

This guide covers best practices for optimizing the performance of the GL Color Palette Generator plugin. It includes recommendations for caching, database queries, and resource management.

## Caching Strategies

### Object Cache

The plugin uses WordPress object caching for frequently accessed data:

```php
// Example of proper cache usage
$cache_key = 'gl_color_palette_' . md5(serialize($params));
$result = wp_cache_get($cache_key);

if (false === $result) {
    $result = generate_color_palette($params);
    wp_cache_set($cache_key, $result, '', 3600); // Cache for 1 hour
}
```

### Transients

For data that needs to persist across requests:

```php
// Example of transient usage
$palette_data = get_transient('gl_color_palette_recent');
if (false === $palette_data) {
    $palette_data = fetch_recent_palettes();
    set_transient('gl_color_palette_recent', $palette_data, DAY_IN_SECONDS);
}
```

## Database Optimization

### Query Optimization

1. Use proper indexing:
   ```sql
   CREATE INDEX gl_color_palette_type_idx ON {$wpdb->prefix}gl_color_palettes (palette_type);
   ```

2. Batch operations:
   ```php
   // Instead of multiple single inserts
   $wpdb->query("INSERT INTO table (col1, col2) VALUES " . implode(',', $value_sets));
   ```

### Data Structure

Optimize table structures for performance:
```sql
CREATE TABLE {$wpdb->prefix}gl_color_palettes (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    colors JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX name_idx (name),
    INDEX created_at_idx (created_at)
) ENGINE=InnoDB;
```

## Resource Management

### Memory Usage

1. Limit array sizes:
   ```php
   // Use generators for large datasets
   function get_all_palettes() {
       $offset = 0;
       $limit = 100;
       
       while ($palettes = fetch_palettes($offset, $limit)) {
           foreach ($palettes as $palette) {
               yield $palette;
           }
           $offset += $limit;
       }
   }
   ```

2. Clean up temporary data:
   ```php
   // Release memory after processing
   function process_large_dataset() {
       $result = heavy_operation();
       $processed = process_data($result);
       unset($result); // Free memory
       return $processed;
   }
   ```

### API Rate Limiting

Implement rate limiting for API endpoints:
```php
// Example rate limiting implementation
$rate_limiter = new Rate_Limiter();
$limit = $rate_limiter->check_limit($user_id);

if (!$limit->is_allowed()) {
    return new WP_Error(
        'rate_limit_exceeded',
        'Rate limit exceeded. Please try again later.',
        ['status' => 429]
    );
}
```

## Monitoring and Profiling

### Performance Monitoring

Use the built-in Performance Monitor:
```php
$monitor = new Performance_Monitor();
$monitor->start_measurement('operation_name');

// Your operation here

$duration = $monitor->end_measurement('operation_name');
if ($duration > 1.0) { // More than 1 second
    $monitor->log_performance_issue('operation_name', $duration);
}
```

### Debug Logging

Enable performance logging in development:
```php
// In wp-config.php
define('GL_COLOR_PALETTE_PERFORMANCE_LOGGING', true);

// In your code
if (defined('GL_COLOR_PALETTE_PERFORMANCE_LOGGING') && GL_COLOR_PALETTE_PERFORMANCE_LOGGING) {
    error_log("Performance data: " . json_encode($performance_data));
}
```

## Best Practices

1. **Lazy Loading**
   - Load resources only when needed
   - Use WordPress's built-in script dependencies

2. **Image Optimization**
   - Use appropriate image formats
   - Implement lazy loading for images
   - Cache generated color swatches

3. **AJAX Optimization**
   - Combine multiple requests when possible
   - Use proper nonce verification
   - Implement request debouncing

4. **Database Interactions**
   - Use prepared statements
   - Implement connection pooling
   - Cache query results

## Performance Testing

Run performance tests regularly:
```bash
# Run performance test suite
composer run test-performance

# Profile specific components
composer run profile-component -- --component=palette-generator
```

## Troubleshooting

Common performance issues and solutions:

1. **Slow Palette Generation**
   - Check AI provider response times
   - Verify cache implementation
   - Monitor memory usage

2. **High Database Load**
   - Review query patterns
   - Check index usage
   - Implement query caching

3. **API Response Times**
   - Monitor rate limits
   - Implement request queuing
   - Use appropriate timeouts

## Additional Resources

- [WordPress Performance Documentation](https://developer.wordpress.org/plugins/performance/)
- [MySQL Optimization Guide](https://dev.mysql.com/doc/refman/8.0/en/optimization.html)
- [PHP Performance Best Practices](https://www.php.net/manual/en/features.gc.performance-considerations.php)
