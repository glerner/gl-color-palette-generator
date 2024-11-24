<?php
namespace GLColorPalette;

class CulturalMappings {
    / Comprehensive cultural color mappings
    private const COLOR_MAPPINGS = [
        'east_asian' => [
            'red' => [
                'meanings' => ['prosperity', 'luck', 'joy', 'celebration', 'vitality'],
                'occasions' => ['new year', 'weddings', 'festivals'],
                'taboos' => ['writing names in red'],
                'business' => ['positive branding', 'traditional marketing'],
                'seasons' => ['spring', 'summer']
            ],
            'gold' => [
                'meanings' => ['wealth', 'prosperity', 'abundance', 'royalty'],
                'occasions' => ['business gifts', 'luxury items'],
                'pairings' => ['red', 'black'],
                'business' => ['premium branding', 'financial services']
            ],
            'white' => [
                'meanings' => ['death', 'mourning', 'purity', 'cleanliness'],
                'occasions' => ['funerals', 'medical settings'],
                'cautions' => ['gift wrapping', 'celebration contexts'],
                'modern_use' => ['minimalist design', 'tech products']
            ]
        ],

        'south_asian' => [
            'red' => [
                'meanings' => ['marriage', 'fertility', 'prosperity', 'spirituality'],
                'occasions' => ['weddings', 'religious ceremonies', 'festivals'],
                'significance' => ['sindoor', 'bridal wear', 'religious marks'],
                'business' => ['traditional branding', 'textile industry']
            ],
            'saffron' => [
                'meanings' => ['sacred', 'spiritual', 'strength', 'courage'],
                'occasions' => ['religious ceremonies', 'national events'],
                'significance' => ['religious robes', 'flags'],
                'cautions' => ['casual or commercial use']
            ]
        ],

        'middle_eastern' => [
            'green' => [
                'meanings' => ['paradise', 'faith', 'growth', 'prosperity'],
                'occasions' => ['religious events', 'celebrations'],
                'significance' => ['religious symbolism', 'national identity'],
                'business' => ['halal products', 'traditional branding']
            ],
            'blue' => [
                'meanings' => ['protection', 'spirituality', 'heaven'],
                'occasions' => ['decorative arts', 'architecture'],
                'significance' => ['evil eye', 'decorative tiles'],
                'modern_use' => ['corporate design', 'tech sector']
            ]
        ],

        'african' => [
            'kente_colors' => [
                'gold' => ['royalty', 'wealth', 'spiritual purity'],
                'yellow' => ['fertility', 'prosperity', 'beauty'],
                'green' => ['growth', 'spiritual renewal', 'harmony'],
                'blue' => ['peace', 'harmony', 'love'],
                'red' => ['political passion', 'sacrifice', 'struggle'],
                'black' => ['maturity', 'spiritual energy', 'antiquity']
            ],
            'adinkra_colors' => [
                'meanings' => ['communication', 'wisdom', 'spirituality'],
                'usage' => ['traditional ceremonies', 'textiles', 'art']
            ]
        ],

        'latin_american' => [
            'bright_colors' => [
                'meanings' => ['life', 'celebration', 'culture', 'nature'],
                'occasions' => ['festivals', 'art', 'architecture'],
                'significance' => ['cultural identity', 'traditional crafts'],
                'modern_use' => ['fashion', 'design', 'branding']
            ],
            'earth_tones' => [
                'meanings' => ['heritage', 'tradition', 'earth connection'],
                'applications' => ['traditional art', 'textiles', 'pottery']
            ]
        ],

        'nordic' => [
            'white' => [
                'meanings' => ['purity', 'snow', 'light', 'simplicity'],
                'applications' => ['interior design', 'minimalism'],
                'seasonal' => ['winter', 'summer solstice'],
                'design_principles' => ['hygge', 'lagom']
            ],
            'natural_tones' => [
                'meanings' => ['connection to nature', 'simplicity', 'functionality'],
                'applications' => ['interior design', 'product design'],
                'business' => ['sustainable branding', 'eco-friendly products']
            ]
        ],

        'indigenous_australian' => [
            'ochre' => [
                'meanings' => ['earth connection', 'ceremony', 'tradition'],
                'occasions' => ['ceremonies', 'art', 'storytelling'],
                'significance' => ['cultural heritage', 'land connection'],
                'cautions' => ['respectful usage', 'cultural permission']
            ],
            'natural_colors' => [
                'meanings' => ['dreamtime', 'land', 'stories'],
                'applications' => ['traditional art', 'ceremonies']
            ]
        ],

        'pacific_islander' => [
            'ocean_colors' => [
                'meanings' => ['sea connection', 'navigation', 'life'],
                'applications' => ['art', 'textiles', 'ceremonies'],
                'significance' => ['maritime heritage', 'natural world']
            ],
            'earth_tones' => [
                'meanings' => ['land', 'tradition', 'community'],
                'applications' => ['tapa cloth', 'traditional crafts']
            ]
        ],

        'contemporary_global' => [
            'tech_colors' => [
                'blue' => ['trust', 'professionalism', 'stability'],
                'white' => ['simplicity', 'cleanliness', 'efficiency'],
                'gray' => ['neutrality', 'balance', 'sophistication']
            ],
            'eco_colors' => [
                'green' => ['sustainability', 'environment', 'health'],
                'earth_tones' => ['natural', 'organic', 'authentic']
            ],
            'social_media' => [
                'platform_colors' => ['brand recognition', 'digital identity'],
                'trending_colors' => ['contemporary', 'youth culture']
            ]
        ]
    ];

    /**
     * Get cultural meanings for a specific color and culture
     */
    public function get_cultural_meanings($color, $culture) {
        if (isset(self::COLOR_MAPPINGS[$culture])) {
            foreach (self::COLOR_MAPPINGS[$culture] as $color_name => $attributes) {
                if ($this->color_matches($color, $color_name)) {
                    return $attributes;
                }
            }
        }
        return null;
    }

    /**
     * Get cultural color combinations
     */
    public function get_cultural_combinations($culture) {
        $combinations = [];

        if (isset(self::COLOR_MAPPINGS[$culture])) {
            foreach (self::COLOR_MAPPINGS[$culture] as $color => $data) {
                if (isset($data['pairings'])) {
                    $combinations[$color] = $data['pairings'];
                }
            }
        }

        return $combinations;
    }

    /**
     * Get cultural usage guidelines
     */
    public function get_usage_guidelines($color, $culture) {
        $guidelines = [];

        if (isset(self::COLOR_MAPPINGS[$culture])) {
            foreach (self::COLOR_MAPPINGS[$culture] as $color_name => $data) {
                if ($this->color_matches($color, $color_name)) {
                    $guidelines = [
                        'occasions' => $data['occasions'] ?? [],
                        'cautions' => $data['cautions'] ?? [],
                        'business' => $data['business'] ?? [],
                        'modern_use' => $data['modern_use'] ?? []
                    ];
                }
            }
        }

        return $guidelines;
    }

    /**
     * Check for cultural taboos
     */
    public function check_cultural_taboos($color, $culture, $context) {
        $warnings = [];

        if (isset(self::COLOR_MAPPINGS[$culture])) {
            foreach (self::COLOR_MAPPINGS[$culture] as $color_name => $data) {
                if ($this->color_matches($color, $color_name) && isset($data['taboos'])) {
                    foreach ($data['taboos'] as $taboo) {
                        if ($this->context_matches($context, $taboo)) {
                            $warnings[] = $taboo;
                        }
                    }
                }
            }
        }

        return $warnings;
    }

    /**
     * Analyze cultural significance
     */
    public function analyze_cultural_significance($palette) {
        $regions = $this->get_supported_regions();
        $significance_map = [];

        foreach ($regions as $region) {
            $significance_map[$region] = [
                'positive_associations' => $this->get_positive_associations($palette, $region),
                'negative_associations' => $this->get_negative_associations($palette, $region),
                'cultural_contexts' => $this->get_cultural_contexts($palette, $region),
                'usage_recommendations' => $this->get_usage_recommendations($palette, $region)
            ];
        }

        return $significance_map;
    }

    /**
     * Get regional color preferences
     */
    public function get_regional_preferences($region) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cultural_color_preferences';

        $preferences = $wpdb->get_results($wpdb->prepare(
            "SELECT color_hex, preference_score, context
             FROM $table_name
             WHERE region = %s
             ORDER BY preference_score DESC",
            $region
        ));

        return $this->process_regional_preferences($preferences);
    }

    /**
     * Generate culturally appropriate variations
     */
    public function generate_cultural_variations($palette, $target_regions) {
        $variations = [];

        foreach ($target_regions as $region) {
            $variations[$region] = [
                'adapted_palette' => $this->adapt_palette_for_region($palette, $region),
                'cultural_notes' => $this->get_cultural_notes($palette, $region),
                'usage_guidelines' => $this->generate_regional_guidelines($palette, $region),
                'market_considerations' => $this->get_market_considerations($region)
            ];
        }

        return $variations;
    }
}
