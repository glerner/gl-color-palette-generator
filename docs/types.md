# Type System Documentation

## Overview

The GL Color Palette Generator uses strict type checking and validation to ensure data integrity and prevent runtime errors. This document outlines the type system and validation rules used throughout the plugin.

## Color Types

The `Color_Types` class provides validation for color-related data structures.

### Hex Color Validation

Colors must be in valid hexadecimal format:
- Must start with '#'
- Must be either 3 or 6 hexadecimal characters (after the #)
- Valid examples: '#000000', '#FFFFFF', '#123', '#ABC'

```php
Color_Types::is_valid_hex_color('#000000'); // true
Color_Types::is_valid_hex_color('000000');  // false (missing #)
```

### Metadata Validation

Palette metadata must conform to the following structure:
```php
array{
    name: string,        // Required
    description: string, // Required
    theme: string,       // Required
    created: string,     // Required
    modified: string,    // Required
    provider: string,    // Required
    tags: array<string>  // Required
}
```

### Provider Options

AI Provider options must conform to the following structure:
```php
array{
    model?: string,              // Optional
    temperature?: float,         // Optional, 0.0 to 1.0
    max_tokens?: int,           // Optional, > 0
    top_p?: float,              // Optional, 0.0 to 1.0
    frequency_penalty?: float,   // Optional, 0.0 to 1.0
    presence_penalty?: float     // Optional, 0.0 to 1.0
}
```

## Type Safety Features

1. Strict Types
   - All PHP files use `declare(strict_types=1)`
   - All method parameters and return types are explicitly typed
   - Array shapes are documented using PHPDoc

2. Input Validation
   - All user input is validated before processing
   - API responses are validated for correct format
   - Color codes are validated for correct format

3. Error Handling
   - Type mismatches throw `TypeError`
   - Invalid values throw `InvalidArgumentException`
   - API errors throw `Exception`

## Testing

The type system is thoroughly tested:
- Unit tests for all validation methods
- Test cases for valid and invalid inputs
- Edge case testing for all data types

See `tests/types/test-color-types.php` for test examples.
