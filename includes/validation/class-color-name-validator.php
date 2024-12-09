<?php
namespace GLColorPalette\Validation;

/**
 * Color Name Validator
 *
 * Validates color names for appropriateness, readability, and uniqueness.
 */
class Color_Name_Validator {
    /** @var array Validation rules configuration */
    private $rules = [
        'length' => [
            'min' => 3,
            'max' => 50
        ],
        'words' => [
            'min' => 1,
            'max' => 3
        ],
        'allowed_chars' => '/^[a-zA-Z0-9\s\-]+$/',
        'forbidden_words' => [
            'offensive',
            'trademark',
            'copyrighted'
        ]
    ];

    private $errors = [];
    private $warnings = [];

    /**
     * Validate a color name
     *
     * @param string $name Color name to validate
     * @param array $context Additional context for validation
     * @return bool True if valid
     */
    public function validate($name, $context = []) {
        $this->errors = [];
        $this->warnings = [];

        try {
            // Basic validation
            $this->validate_length($name);

            // Language validation
            $this->validate_language($name);

            // Content validation
            $this->validate_content($name);

            // Context validation
            $this->validate_context($name, $context);

            // Check uniqueness
            $this->validate_uniqueness($name, $context);

            return empty($this->errors);

        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    /**
     * Validate name length
     *
     * @param string $name Color name to validate
     */
    private function validate_length($name) {
        $length = strlen($name);

        if ($length < $this->rules['length']['min']) {
            $this->errors[] = sprintf(
                'Name too short (minimum %d characters)',
                $this->rules['length']['min']
            );
        }

        if ($length > $this->rules['length']['max']) {
            $this->errors[] = sprintf(
                'Name too long (maximum %d characters)',
                $this->rules['length']['max']
            );
        }
    }

    /**
     * Validate language usage
     *
     * @param string $name Color name to validate
     */
    private function validate_language($name) {
        // Check word count
        $words = str_word_count($name);
        if ($words < $this->rules['words']['min'] || 
            $words > $this->rules['words']['max']) {
            $this->errors[] = sprintf(
                'Name should have %d-%d words',
                $this->rules['words']['min'],
                $this->rules['words']['max']
            );
        }

        // Check allowed characters
        if (!preg_match($this->rules['allowed_chars'], $name)) {
            $this->errors[] = 'Name contains invalid characters';
        }
    }

    /**
     * Validate content appropriateness
     *
     * @param string $name Color name to validate
     */
    private function validate_content($name) {
        $name_lower = strtolower($name);

        // Check forbidden words
        foreach ($this->rules['forbidden_words'] as $word) {
            if (strpos($name_lower, $word) !== false) {
                $this->errors[] = 'Name contains inappropriate content';
                break;
            }
        }
    }

    /**
     * Validate name in context
     *
     * @param string $name Color name to validate
     * @param array $context Validation context
     */
    private function validate_context($name, $context) {
        if (empty($context)) {
            return;
        }

        // Check if name fits theme
        if (isset($context['theme']) && 
            !$this->name_fits_theme($name, $context['theme'])) {
            $this->warnings[] = 'Name may not fit the theme';
        }

        // Check if name matches color properties
        if (isset($context['color']) && 
            !$this->name_matches_color($name, $context['color'])) {
            $this->warnings[] = 'Name may not match color properties';
        }
    }

    /**
     * Validate name uniqueness
     *
     * @param string $name Color name to validate
     * @param array $context Validation context
     */
    private function validate_uniqueness($name, $context) {
        if (isset($context['existing_names'])) {
            $similar = $this->find_similar_names($name, $context['existing_names']);
            if (!empty($similar)) {
                $this->warnings[] = sprintf(
                    'Similar names exist: %s',
                    implode(', ', array_slice($similar, 0, 3))
                );
            }
        }
    }

    /**
     * Check if name fits theme
     *
     * @param string $name Color name to check
     * @param string $theme Theme to check against
     * @return bool True if name fits theme
     */
    private function name_fits_theme($name, $theme) {
        // Add theme-specific validation logic
        return true;
    }

    /**
     * Check if name matches color properties
     *
     * @param string $name Color name to check
     * @param array $color Color properties
     * @return bool True if name matches color
     */
    private function name_matches_color($name, $color) {
        // Add color property matching logic
        return true;
    }

    /**
     * Find similar existing names
     *
     * @param string $name Color name to check
     * @param array $existing_names Existing names to check against
     * @return array Similar names found
     */
    private function find_similar_names($name, $existing_names) {
        $similar = [];
        foreach ($existing_names as $existing) {
            if (levenshtein(strtolower($name), strtolower($existing)) < 3) {
                $similar[] = $existing;
            }
        }
        return $similar;
    }

    /**
     * Get validation errors
     *
     * @return array Validation errors
     */
    public function get_errors() {
        return $this->errors;
    }

    /**
     * Get validation warnings
     *
     * @return array Validation warnings
     */
    public function get_warnings() {
        return $this->warnings;
    }
}
