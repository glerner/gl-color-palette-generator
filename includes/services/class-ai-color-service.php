<?php
namespace GLColorPalette;

class AIColorService {
    private $settings;
    private $error_handler;
    private $cache;
    private $current_provider;
    private $rate_limiter;

    private $providers = [
        'openai' => OpenAIProvider::class,
        'anthropic' => AnthropicProvider::class,
        'palm' => PalmProvider::class,
        'local' => LocalAIProvider::class
    ];

    public function __construct($settings = null) {
        $this->settings = $settings ?? new SettingsManager();
        $this->error_handler = new ErrorHandler();
        $this->cache = new ColorCache();
        $this->rate_limiter = new RateLimiter();

        $this->initialize_provider();
    }

    /**
     * Initialize the selected AI provider
     */
    private function initialize_provider() {
        $provider = $this->settings->get_setting('ai_provider', 'openai');

        if (!isset($this->providers[$provider])) {
            throw new Exception("Invalid AI provider: {$provider}");
        }

        $provider_class = $this->providers[$provider];
        $this->current_provider = new $provider_class(
            $this->settings->get_setting('api_key'),
            $this->settings->get_setting('ai_settings')
        );
    }

    /**
     * Generate color name using AI
     */
    public function generate_color_name($hex, $context = [], $options = []) {
        try {
            / Check rate limits
            if (!$this->rate_limiter->can_process()) {
                throw new Exception("Rate limit exceeded");
            }

            / Generate prompt
            $prompt = $this->create_prompt($hex, $context, $options);

            / Get AI response
            $response = $this->current_provider->generate(
                $prompt,
                $this->get_generation_parameters($options)
            );

            / Process and validate response
            $processed_name = $this->process_response($response, $hex, $context);

            / Update rate limiter
            $this->rate_limiter->record_request();

            return $processed_name;

        } catch (Exception $e) {
            $this->error_handler->handle_error(
                ErrorCodes::API_GENERATION_FAILED,
                $e->getMessage(),
                ['hex' => $hex, 'context' => $context]
            );
            return null;
        }
    }

    /**
     * Create sophisticated prompt for AI
     */
    private function create_prompt($hex, $context, $options) {
        $color_data = $this->analyze_color($hex);
        $context_data = $this->process_context($context);
        $cultural_preferences = $this->get_cultural_preferences($options);

        $prompt = [
            'system' => $this->get_system_prompt($context_data),
            'user' => $this->format_user_prompt($color_data, $context_data, $cultural_preferences)
        ];

        return $prompt;
    }

    /**
     * Get system prompt
     */
    private function get_system_prompt($context) {
        return <<<PROMPT
You are a color naming expert with deep knowledge of color theory, psychology, and cultural significance.
Your task is to generate creative, meaningful, and contextually appropriate names for colors.

Guidelines for color naming:
- Names should be memorable and evocative
- Consider the context: {$context['purpose']}
- Respect cultural sensitivities and preferences
- Avoid potentially offensive or inappropriate terms
- Names should be 1-3 words maximum
- Consider brand voice: {$context['brand_voice']}
- Ensure names are easy to pronounce and remember

Please provide the color name in the following format:
NAME: [color name]
RATIONALE: [brief explanation]
ALTERNATIVES: [2-3 alternative names]
PROMPT;
    }

    /**
     * Format user prompt with color analysis
     */
    private function format_user_prompt($color_data, $context, $cultural_prefs) {
        return <<<PROMPT
Please generate a name for a color with the following properties:

COLOR SPECIFICATIONS:
- HEX: {$color_data['hex']}
- RGB: {$color_data['rgb']['r']}, {$color_data['rgb']['g']}, {$color_data['rgb']['b']}
- HSL: {$color_data['hsl']['h']}°, {$color_data['hsl']['s']}%, {$color_data['hsl']['l']}%
- Color Family: {$color_data['family']}
- Brightness: {$color_data['brightness']}
- Saturation: {$color_data['saturation']}

CONTEXT:
- Industry: {$context['industry']}
- Target Audience: {$context['audience']}
- Usage: {$context['usage']}
- Mood: {$context['mood']}
- Season: {$context['season']}

CULTURAL CONSIDERATIONS:
- Primary Market: {$cultural_prefs['market']}
- Language: {$cultural_prefs['language']}
- Cultural Associations: {$cultural_prefs['associations']}
- Avoid: {$cultural_prefs['restrictions']}

Additional Requirements:
{$this->format_additional_requirements($context)}
PROMPT;
    }

    /**
     * Process and validate AI response
     */
    private function process_response($response, $hex, $context) {
        $parsed = $this->parse_ai_response($response);

        if (!$this->validate_color_name($parsed['name'], $context)) {
            throw new Exception("Generated name failed validation");
        }

        / Store additional data for future reference
        $this->store_name_metadata([
            'hex' => $hex,
            'name' => $parsed['name'],
            'alternatives' => $parsed['alternatives'],
            'rationale' => $parsed['rationale'],
            'context' => $context
        ]);

        return $parsed['name'];
    }

    /**
     * Analyze color properties
     */
    private function analyze_color($hex) {
        $analyzer = new ColorAnalyzer();

        return [
            'hex' => $hex,
            'rgb' => $analyzer->hex_to_rgb($hex),
            'hsl' => $analyzer->rgb_to_hsl($analyzer->hex_to_rgb($hex)),
            'family' => $analyzer->get_color_family($hex),
            'brightness' => $analyzer->calculate_brightness($hex),
            'saturation' => $analyzer->calculate_saturation($hex),
            'temperature' => $analyzer->get_color_temperature($hex),
            'contrast' => $analyzer->calculate_contrast($hex),
            'harmony' => $analyzer->analyze_harmony($hex)
        ];
    }

    /**
     * Process context information
     */
    private function process_context($context) {
        return array_merge([
            'industry' => $context['industry'] ?? 'general',
            'audience' => $context['audience'] ?? 'general',
            'usage' => $context['usage'] ?? 'general',
            'mood' => $context['mood'] ?? 'neutral',
            'season' => $context['season'] ?? 'any',
            'brand_voice' => $context['brand_voice'] ?? 'professional',
            'purpose' => $context['purpose'] ?? 'general use'
        ], $context);
    }

    /**
     * Get cultural preferences and restrictions
     */
    private function get_cultural_preferences($options) {
        $locale = $options['locale'] ?? get_locale();
        $market = $options['market'] ?? 'global';

        return [
            'market' => $market,
            'language' => $this->get_preferred_language($locale),
            'associations' => $this->get_cultural_associations($market),
            'restrictions' => $this->get_cultural_restrictions($market)
        ];
    }

    /**
     * Store metadata about generated names
     */
    private function store_name_metadata($data) {
        $metadata_key = 'color_name_metadata_' . md5($data['hex']);
        set_transient($metadata_key, $data, WEEK_IN_SECONDS);
    }

    /**
     * Validate generated color name
     */
    private function validate_color_name($name, $context) {
        $validator = new ColorNameValidator();

        return $validator->validate($name, [
            'max_words' => 3,
            'min_length' => 3,
            'max_length' => 30,
            'allowed_characters' => 'a-zA-Z0-9\s-',
            'context' => $context
        ]);
    }

    /**
     * Get AI generation parameters
     */
    private function get_generation_parameters($options) {
        return [
            'temperature' => $options['creativity'] ?? 0.7,
            'max_tokens' => 150,
            'top_p' => 0.9,
            'frequency_penalty' => 0.5,
            'presence_penalty' => 0.5,
            'stop' => ["\n\n"]
        ];
    }
}
