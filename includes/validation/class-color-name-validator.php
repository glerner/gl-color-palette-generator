<?php
namespace GLColorPalette;

class ColorNameValidator {
    private $error_handler;
    private $profanity_filter;
    private $trademark_checker;
    private $language_detector;

    // Validation rules configuration
    private $default_rules = [
        'max_words' => 3,
        'min_length' => 3,
        'max_length' => 30,
        'allowed_characters' => 'a-zA-Z0-9\s-',
        'forbidden_words' => [],
        'required_language' => 'en',
        'check_trademark' => true,
        'check_profanity' => true,
        'check_cultural' => true,
        'check_readability' => true,
        'check_uniqueness' => true,
        'check_pronunciation' => true
    ];

    public function __construct() {
        $this->error_handler = new ErrorHandler();
        $this->profanity_filter = new ProfanityFilter();
        $this->trademark_checker = new TrademarkChecker();
        $this->language_detector = new LanguageDetector();
    }

    /**
     * Validate color name
     */
    public function validate($name, $context = [], $rules = []) {
        $rules = array_merge($this->default_rules, $rules);
        $validation_results = [];

        try {
            // Basic validation
            $validation_results['basic'] = $this->validate_basic_rules($name, $rules);

            // Language validation
            $validation_results['language'] = $this->validate_language($name, $rules);

            // Content validation
            $validation_results['content'] = $this->validate_content($name, $rules);

            // Context validation
            $validation_results['context'] = $this->validate_context($name, $context, $rules);

            // Trademark validation
            $validation_results['trademark'] = $this->validate_trademark($name, $rules);

            // Cultural validation
            $validation_results['cultural'] = $this->validate_cultural_sensitivity($name, $context, $rules);

            // Readability validation
            $validation_results['readability'] = $this->validate_readability($name, $rules);

            // Pronunciation validation
            $validation_results['pronunciation'] = $this->validate_pronunciation($name, $rules);

            // Uniqueness validation
            $validation_results['uniqueness'] = $this->validate_uniqueness($name, $context, $rules);

            // Check if any validation failed
            foreach ($validation_results as $type => $result) {
                if (!$result['valid']) {
                    throw new ValidationException(
                        $result['message'],
                        ErrorCodes::VALIDATION_FAILED,
                        ['type' => $type, 'details' => $result]
                    );
                }
            }

            return true;

        } catch (ValidationException $e) {
            $this->error_handler->handle_error(
                $e->getCode(),
                $e->getMessage(),
                array_merge(['name' => $name], $e->getContext())
            );
            return false;
        }
    }

    /**
     * Validate basic rules
     */
    private function validate_basic_rules($name, $rules) {
        $result = ['valid' => true, 'message' => ''];

        // Check length
        if (strlen($name) < $rules['min_length']) {
            return [
                'valid' => false,
                'message' => sprintf(
                    __('Name too short (minimum %d characters)', 'color-palette-generator'),
                    $rules['min_length']
                )
            ];
        }

        if (strlen($name) > $rules['max_length']) {
            return [
                'valid' => false,
                'message' => sprintf(
                    __('Name too long (maximum %d characters)', 'color-palette-generator'),
                    $rules['max_length']
                )
            ];
        }

        // Check word count
        $word_count = str_word_count($name);
        if ($word_count > $rules['max_words']) {
            return [
                'valid' => false,
                'message' => sprintf(
                    __('Too many words (maximum %d words)', 'color-palette-generator'),
                    $rules['max_words']
                )
            ];
        }

        // Check allowed characters
        if (!preg_match('/^[' . $rules['allowed_characters'] . ']+$/u', $name)) {
            return [
                'valid' => false,
                'message' => __('Name contains invalid characters', 'color-palette-generator')
            ];
        }

        return $result;
    }

    /**
     * Validate language
     */
    private function validate_language($name, $rules) {
        if (!$rules['required_language']) {
            return ['valid' => true, 'message' => ''];
        }

        $detected_language = $this->language_detector->detect($name);

        if ($detected_language !== $rules['required_language']) {
            return [
                'valid' => false,
                'message' => sprintf(
                    __('Name should be in %s (detected: %s)', 'color-palette-generator'),
                    $rules['required_language'],
                    $detected_language
                )
            ];
        }

        return ['valid' => true, 'message' => ''];
    }

    /**
     * Validate content
     */
    private function validate_content($name, $rules) {
        // Check profanity
        if ($rules['check_profanity'] && $this->profanity_filter->contains_profanity($name)) {
            return [
                'valid' => false,
                'message' => __('Name contains inappropriate content', 'color-palette-generator')
            ];
        }

        // Check forbidden words
        $forbidden_words = $rules['forbidden_words'];
        foreach ($forbidden_words as $word) {
            if (stripos($name, $word) !== false) {
                return [
                    'valid' => false,
                    'message' => sprintf(
                        __('Name contains forbidden word: %s', 'color-palette-generator'),
                        $word
                    )
                ];
            }
        }

        return ['valid' => true, 'message' => ''];
    }

    /**
     * Validate trademark conflicts
     */
    private function validate_trademark($name, $rules) {
        if (!$rules['check_trademark']) {
            return ['valid' => true, 'message' => ''];
        }

        $trademark_result = $this->trademark_checker->check($name);

        if ($trademark_result['conflict']) {
            return [
                'valid' => false,
                'message' => sprintf(
                    __('Name conflicts with trademark: %s', 'color-palette-generator'),
                    $trademark_result['trademark']
                )
            ];
        }

        return ['valid' => true, 'message' => ''];
    }

    /**
     * Validate cultural sensitivity
     */
    private function validate_cultural_sensitivity($name, $context, $rules) {
        if (!$rules['check_cultural']) {
            return ['valid' => true, 'message' => ''];
        }

        $cultural_checker = new CulturalSensitivityChecker();
        $sensitivity_result = $cultural_checker->check($name, [
            'market' => $context['market'] ?? 'global',
            'language' => $context['language'] ?? 'en',
            'culture' => $context['culture'] ?? 'western'
        ]);

        if (!$sensitivity_result['appropriate']) {
            return [
                'valid' => false,
                'message' => $sensitivity_result['reason']
            ];
        }

        return ['valid' => true, 'message' => ''];
    }

    /**
     * Validate readability
     */
    private function validate_readability($name, $rules) {
        if (!$rules['check_readability']) {
            return ['valid' => true, 'message' => ''];
        }

        $readability_checker = new ReadabilityChecker();
        $readability_score = $readability_checker->analyze($name);

        if ($readability_score < 0.7) { // 70% readability threshold
            return [
                'valid' => false,
                'message' => __('Name is difficult to read', 'color-palette-generator')
            ];
        }

        return ['valid' => true, 'message' => ''];
    }

    /**
     * Validate pronunciation
     */
    private function validate_pronunciation($name, $rules) {
        if (!$rules['check_pronunciation']) {
            return ['valid' => true, 'message' => ''];
        }

        $pronunciation_checker = new PronunciationChecker();
        $pronunciation_result = $pronunciation_checker->analyze($name);

        if (!$pronunciation_result['pronounceable']) {
            return [
                'valid' => false,
                'message' => __('Name is difficult to pronounce', 'color-palette-generator')
            ];
        }

        return ['valid' => true, 'message' => ''];
    }

    /**
     * Validate uniqueness
     */
    private function validate_uniqueness($name, $context, $rules) {
        if (!$rules['check_uniqueness']) {
            return ['valid' => true, 'message' => ''];
        }

        global $wpdb;

        // Check existing color names in the database
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}color_names
             WHERE name LIKE %s AND context = %s",
            $name,
            $context['usage'] ?? 'general'
        ));

        if ($existing > 0) {
            return [
                'valid' => false,
                'message' => __('Name already exists in this context', 'color-palette-generator')
            ];
        }

        return ['valid' => true, 'message' => ''];
    }
}
