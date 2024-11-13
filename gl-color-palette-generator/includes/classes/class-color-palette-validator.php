<?php
/**
 * Color Palette Validator Class
 *
 * @package GLColorPalette
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GLColorPalette;

use GLColorPalette\Interfaces\ColorPaletteValidatorInterface;
use GLColorPalette\ColorPalette;

/**
 * Validates color palettes.
 */
class ColorPaletteValidator implements ColorPaletteValidatorInterface {
    /**
     * Validation errors.
     *
     * @var array
     */
    private array $errors = [];

    /**
     * Validation rules.
     *
     * @var array
     */
    private array $validation_rules = [
        'name' => [
            'required' => true,
            'type' => 'string',
            'min_length' => 1,
            'max_length' => 100
        ],
        'colors' => [
            'required' => true,
            'type' => 'array',
            'min_items' => 1,
            'max_items' => 100
        ],
        'metadata' => [
            'required' => false,
            'type' => 'array'
        ]
    ];

    /**
     * Metadata validation rules.
     *
     * @var array
     */
    private array $metadata_rules = [
        'type' => [
            'type' => 'string',
            'allowed' => ['custom', 'system', 'imported']
        ],
        'tags' => [
            'type' => 'array',
            'item_type' => 'string'
        ],
        'created_at' => [
            'type' => 'string',
            'format' => 'datetime'
        ],
        'updated_at' => [
            'type' => 'string',
            'format' => 'datetime'
        ],
        'author' => [
            'type' => 'string'
        ],
        'version' => [
            'type' => 'string',
            'pattern' => '/^\d+\.\d+\.\d+$/'
        ]
    ];

    /**
     * Validates a color palette.
     *
     * @param ColorPalette $palette Palette to validate.
     * @return bool True if valid.
     */
    public function validatePalette(ColorPalette $palette): bool {
        $this->errors = [];

        $data = [
            'name' => $palette->getName(),
            'colors' => $palette->getColors(),
            'metadata' => $palette->getMetadata()
        ];

        if (!$this->validateStructure($data)) {
            return false;
        }

        foreach ($data['colors'] as $index => $color) {
            if (!$this->validateColorFormat($color)) {
                $this->errors[] = "Invalid color format at index {$index}: {$color}";
                return false;
            }
        }

        if (!empty($data['metadata']) && !$this->validateMetadata($data['metadata'])) {
            return false;
        }

        return empty($this->errors);
    }

    /**
     * Gets validation errors.
     *
     * @return array List of validation errors.
     */
    public function getErrors(): array {
        return $this->errors;
    }

    /**
     * Validates color format.
     *
     * @param string $color Color to validate.
     * @return bool True if valid.
     */
    public function validateColorFormat(string $color): bool {
        // Validate hex color format (#RGB or #RRGGBB)
        return (bool) preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color);
    }

    /**
     * Validates palette structure.
     *
     * @param array $data Palette data.
     * @return bool True if valid.
     */
    public function validateStructure(array $data): bool {
        foreach ($this->validation_rules as $field => $rules) {
            if ($rules['required'] && !isset($data[$field])) {
                $this->errors[] = "Missing required field: {$field}";
                return false;
            }

            if (isset($data[$field])) {
                if (gettype($data[$field]) !== $rules['type']) {
                    $this->errors[] = "Invalid type for {$field}: expected {$rules['type']}";
                    return false;
                }

                if ($rules['type'] === 'string' && isset($rules['min_length'])) {
                    if (strlen($data[$field]) < $rules['min_length']) {
                        $this->errors[] = "{$field} is too short";
                        return false;
                    }
                }

                if ($rules['type'] === 'string' && isset($rules['max_length'])) {
                    if (strlen($data[$field]) > $rules['max_length']) {
                        $this->errors[] = "{$field} is too long";
                        return false;
                    }
                }

                if ($rules['type'] === 'array' && isset($rules['min_items'])) {
                    if (count($data[$field]) < $rules['min_items']) {
                        $this->errors[] = "{$field} has too few items";
                        return false;
                    }
                }

                if ($rules['type'] === 'array' && isset($rules['max_items'])) {
                    if (count($data[$field]) > $rules['max_items']) {
                        $this->errors[] = "{$field} has too many items";
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Validates palette metadata.
     *
     * @param array $metadata Metadata to validate.
     * @return bool True if valid.
     */
    public function validateMetadata(array $metadata): bool {
        foreach ($metadata as $key => $value) {
            if (isset($this->metadata_rules[$key])) {
                $rules = $this->metadata_rules[$key];

                if (gettype($value) !== $rules['type']) {
                    $this->errors[] = "Invalid type for metadata.{$key}: expected {$rules['type']}";
                    return false;
                }

                if (isset($rules['allowed']) && !in_array($value, $rules['allowed'])) {
                    $this->errors[] = "Invalid value for metadata.{$key}";
                    return false;
                }

                if (isset($rules['format']) && $rules['format'] === 'datetime') {
                    if (!strtotime($value)) {
                        $this->errors[] = "Invalid datetime format for metadata.{$key}";
                        return false;
                    }
                }

                if (isset($rules['pattern']) && !preg_match($rules['pattern'], $value)) {
                    $this->errors[] = "Invalid format for metadata.{$key}";
                    return false;
                }

                if (isset($rules['item_type']) && is_array($value)) {
                    foreach ($value as $item) {
                        if (gettype($item) !== $rules['item_type']) {
                            $this->errors[] = "Invalid item type in metadata.{$key}";
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * Gets validation rules.
     *
     * @return array List of validation rules.
     */
    public function getValidationRules(): array {
        return [
            'palette' => $this->validation_rules,
            'metadata' => $this->metadata_rules
        ];
    }
}
