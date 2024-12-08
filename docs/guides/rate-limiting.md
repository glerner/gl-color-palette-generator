# Rate Limiting Guide

## Overview

The GL Color Palette Generator implements rate limiting to ensure fair usage of the API and protect system resources. This guide explains how rate limiting works and how to configure it.

## Implementation Details

### Token Bucket Algorithm

We use a token bucket algorithm for rate limiting:
- Each user/IP gets a bucket of tokens
- Tokens regenerate over time
- Each API request consumes one token
- When no tokens are available, requests are rejected

### Storage

Rate limit data is stored using WordPress transients:
- Efficient and automatically cleaned up
- Supports multi-site installations
- Handles concurrent requests safely

### Configuration

Default settings in `includes/core/class-rate-limiter.php`:
```php
private const DEFAULT_WINDOW = 3600;        // 1 hour window
private const DEFAULT_MAX_REQUESTS = 1000;  // requests per window
```

Customize using filters:
```php
// Modify rate limit window
add_filter('gl_cpg_rate_limit_window', function($window) {
    return 1800; // 30 minutes
});

// Modify max requests
add_filter('gl_cpg_rate_limit_max_requests', function($max, $user_id) {
    if (user_can($user_id, 'manage_options')) {
        return 2000; // Higher limit for admins
    }
    return $max;
}, 10, 2);
```

## Client Implementation

### Handling Rate Limits

Example JavaScript implementation:
```javascript
async function generatePalette() {
    try {
        const response = await fetch('/wp-json/gl-color-palette/v1/palettes', {
            method: 'POST',
            // ... other options
        });
        
        // Check rate limit headers
        const remaining = response.headers.get('X-RateLimit-Remaining');
        const reset = response.headers.get('X-RateLimit-Reset');
        
        if (response.status === 429) {
            // Rate limit exceeded
            const retryAfter = response.headers.get('Retry-After');
            console.log(`Rate limit exceeded. Retry after ${retryAfter} seconds`);
            return;
        }
        
        // Process successful response
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error:', error);
    }
}
```

### Best Practices

1. **Monitor Headers**
   - Track remaining requests
   - Schedule requests around reset times
   - Implement graceful degradation

2. **Caching**
   ```javascript
   const cache = new Map();
   
   async function getCachedPalette(key) {
       if (cache.has(key)) {
           return cache.get(key);
       }
       
       const palette = await generatePalette();
       cache.set(key, palette);
       return palette;
   }
   ```

3. **Exponential Backoff**
   ```javascript
   async function fetchWithBackoff(url, options, maxRetries = 3) {
       for (let i = 0; i < maxRetries; i++) {
           try {
               const response = await fetch(url, options);
               if (response.status !== 429) return response;
               
               const retryAfter = response.headers.get('Retry-After');
               await new Promise(resolve => 
                   setTimeout(resolve, (retryAfter || Math.pow(2, i)) * 1000)
               );
           } catch (error) {
               if (i === maxRetries - 1) throw error;
           }
       }
   }
   ```

## Testing

The plugin includes tests for rate limiting functionality:

```php
class Test_Rate_Limiter extends WP_UnitTestCase {
    public function test_rate_limit_exceeded() {
        $limiter = new Rate_Limiter();
        $identifier = 'test_user_1';
        
        // Exhaust rate limit
        for ($i = 0; $i < 1000; $i++) {
            $limiter->check_limit($identifier);
        }
        
        // Next request should fail
        $this->assertFalse($limiter->check_limit($identifier));
    }
}
```

Run rate limit tests:
```bash
composer test tests/includes/core/test-class-rate-limiter.php
```
