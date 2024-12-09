<?php
/**
 * Color Types Class
 *
 * Defines strict types and validation for color-related data structures
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Types
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Types;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Color Types class
 */
class Color_Types {
    /**
     * Validates a hex color code
     *
     * @param string $color Hex color code to validate
     * @return bool True if valid, false otherwise
     */
    public static function is_valid_hex_color(string $color): bool {
        return (bool) preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', $color);
    }

    /**
     * Validates palette metadata structure
     *
     * @param array $metadata Metadata to validate
     * @return bool True if valid, false otherwise
     */
    public static function is_valid_metadata(array $metadata): bool {
        $required_fields = ['name', 'description', 'theme', 'created', 'modified', 'provider'];
        
        foreach ($required_fields as $field) {
            if (!isset($metadata[$field]) || !is_string($metadata[$field])) {
                return false;
            }
        }

        if (!isset($metadata['tags']) || !is_array($metadata['tags'])) {
            return false;
        }

        foreach ($metadata['tags'] as $tag) {
            if (!is_string($tag)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validates API provider options
     *
     * @param array $options Provider options to validate
     * @return bool True if valid, false otherwise
     */
    public static function is_valid_provider_options(array $options): bool {
        $allowed_fields = ['model', 'temperature', 'max_tokens', 'top_p', 'frequency_penalty', 'presence_penalty'];
        
        foreach ($options as $key => $value) {
            if (!in_array($key, $allowed_fields, true)) {
                return false;
            }

            if ($key === 'model' && !is_string($value)) {
                return false;
            }

            if (in_array($key, ['temperature', 'top_p', 'frequency_penalty', 'presence_penalty'], true)) {
                if (!is_float($value) || $value < 0 || $value > 1) {
                    return false;
                }
            }

            if ($key === 'max_tokens' && (!is_int($value) || $value < 1)) {
                return false;
            }
        }

        return true;
    }
}
