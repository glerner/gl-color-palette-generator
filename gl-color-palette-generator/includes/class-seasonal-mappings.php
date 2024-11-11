<?php
namespace GLColorPalette;

class SeasonalMappings {
    // Comprehensive seasonal color mappings
    private const SEASONAL_MAPPINGS = [
        'east_asian' => [
            'spring' => [
                'primary' => [
                    'cherry_blossom' => [
                        'hex' => '#FFB7C5',
                        'meanings' => ['renewal', 'ephemeral beauty', 'new beginnings'],
                        'occasions' => ['hanami', 'spring festivals'],
                        'combinations' => ['soft greens', 'white', 'light blue']
                    ],
                    'young_bamboo' => [
                        'hex' => '#C8E6C9',
                        'meanings' => ['growth', 'vitality', 'fresh starts'],
                        'applications' => ['spring fashion', 'seasonal packaging']
                    ]
                ],
                'festivals' => ['lunar new year', 'spring festival'],
                'themes' => ['renewal', 'growth', 'awakening']
            ],
            'summer' => [
                'primary' => [
                    'azure' => [
                        'hex' => '#007FFF',
                        'meanings' => ['cooling', 'water', 'clarity'],
                        'applications' => ['summer goods', 'seasonal marketing']
                    ],
                    'lotus' => [
                        'hex' => '#E4717A',
                        'meanings' => ['purity', 'enlightenment', 'summer beauty'],
                        'occasions' => ['buddhist festivals', 'summer celebrations']
                    ]
                ],
                'festivals' => ['dragon boat', 'star festival'],
                'themes' => ['vitality', 'brightness', 'energy']
            ],
            'autumn' => [
                'primary' => [
                    'maple' => [
                        'hex' => '#A6341B',
                        'meanings' => ['maturity', 'transformation', 'harvest'],
                        'occasions' => ['moon viewing', 'harvest festivals']
                    ],
                    'chrysanthemum' => [
                        'hex' => '#FFC30B',
                        'meanings' => ['nobility', 'autumn beauty', 'longevity'],
                        'applications' => ['traditional art', 'seasonal decor']
                    ]
                ],
                'festivals' => ['mid-autumn', 'harvest celebration'],
                'themes' => ['abundance', 'reflection', 'gratitude']
            ],
            'winter' => [
                'primary' => [
                    'snow' => [
                        'hex' => '#FFFAFA',
                        'meanings' => ['purity', 'silence', 'contemplation'],
                        'applications' => ['winter designs', 'seasonal branding']
                    ],
                    'pine' => [
                        'hex' => '#2E4830',
                        'meanings' => ['perseverance', 'endurance', 'evergreen'],
                        'occasions' => ['new year', 'winter solstice']
                    ]
                ],
                'festivals' => ['winter solstice', 'new year preparations'],
                'themes' => ['endurance', 'reflection', 'preparation']
            ]
        ],

        'european' => [
            'spring' => [
                'primary' => [
                    'pastel_palette' => [
                        'colors' => ['#E0BBE4', '#957DAD', '#D291BC', '#FEC8D8'],
                        'meanings' => ['renewal', 'easter', 'growth'],
                        'applications' => ['spring fashion', 'easter celebrations']
                    ],
                    'fresh_green' => [
                        'hex' => '#90EE90',
                        'meanings' => ['new growth', 'fresh starts', 'vitality'],
                        'occasions' => ['spring festivals', 'garden parties']
                    ]
                ],
                'festivals' => ['easter', 'may day'],
                'themes' => ['renewal', 'awakening', 'freshness']
            ],
            'summer' => [
                'primary' => [
                    'mediterranean' => [
                        'colors' => ['#0073CF', '#FFFFFF', '#F5F5DC'],
                        'meanings' => ['sea', 'sun', 'leisure'],
                        'applications' => ['summer fashion', 'beach themes']
                    ],
                    'lavender' => [
                        'hex' => '#E6E6FA',
                        'meanings' => ['provence', 'relaxation', 'summer bloom'],
                        'occasions' => ['summer festivals', 'garden parties']
                    ]
                ],
                'festivals' => ['midsummer', 'harvest beginning'],
                'themes' => ['vibrancy', 'leisure', 'abundance']
            ]
        ],

        'nordic' => [
            'summer_light' => [
                'primary' => [
                    'midnight_sun' => [
                        'colors' => ['#FFF4E0', '#FFE4C4', '#87CEEB'],
                        'meanings' => ['endless day', 'summer joy', 'nature'],
                        'applications' => ['summer design', 'festivities']
                    ]
                ],
                'festivals' => ['midsummer', 'summer solstice'],
                'themes' => ['light', 'celebration', 'nature']
            ],
            'winter_dark' => [
                'primary' => [
                    'aurora' => [
                        'colors' => ['#88C0D0', '#81A1C1', '#5E81AC'],
                        'meanings' => ['northern lights', 'winter sky', 'mystery'],
                        'applications' => ['winter design', 'holiday themes']
                    ]
                ],
                'festivals' => ['santa lucia', 'winter solstice'],
                'themes' => ['coziness', 'reflection', 'warmth']
            ]
        ],

        'tropical' => [
            'wet_season' => [
                'primary' => [
                    'monsoon' => [
                        'colors' => ['#4A777A', '#728C00', '#005F5F'],
                        'meanings' => ['rain', 'growth', 'abundance'],
                        'applications' => ['seasonal design', 'traditional art']
                    ]
                ],
                'themes' => ['renewal', 'growth', 'life']
            ],
            'dry_season' => [
                'primary' => [
                    'sun_earth' => [
                        'colors' => ['#CC5500', '#DAA520', '#CD853F'],
                        'meanings' => ['heat', 'earth', 'preservation'],
                        'applications' => ['seasonal products', 'traditional crafts']
                    ]
                ],
                'themes' => ['endurance', 'warmth', 'tradition']
            ]
        ],

        'desert' => [
            'cool_season' => [
                'primary' => [
                    'desert_bloom' => [
                        'colors' => ['#FFB6C1', '#DDA0DD', '#E6E6FA'],
                        'meanings' => ['brief beauty', 'renewal', 'life'],
                        'applications' => ['seasonal celebrations', 'art']
                    ]
                ],
                'themes' => ['renewal', 'life', 'celebration']
            ],
            'hot_season' => [
                'primary' => [
                    'desert_heat' => [
                        'colors' => ['#CD853F', '#DEB887', '#F4A460'],
                        'meanings' => ['protection', 'endurance', 'tradition'],
                        'applications' => ['traditional design', 'protective gear']
                    ]
                ],
                'themes' => ['protection', 'endurance', 'tradition']
            ]
        ]
    ];

    /**
     * Get seasonal colors for specific culture and season
     */
    public function get_seasonal_colors($culture, $season) {
        if (isset(self::SEASONAL_MAPPINGS[$culture][$season])) {
            return self::SEASONAL_MAPPINGS[$culture][$season];
        }
        return null;
    }

    /**
     * Get festival colors for specific culture and season
     */
    public function get_festival_colors($culture, $season) {
        $seasonal_data = $this->get_seasonal_colors($culture, $season);
        if ($seasonal_data && isset($seasonal_data['festivals'])) {
            return array_map(function($festival) use ($culture) {
                return $this->get_festival_specific_colors($culture, $festival);
            }, $seasonal_data['festivals']);
        }
        return [];
    }

    /**
     * Get seasonal themes
     */
    public function get_seasonal_themes($culture, $season) {
        $seasonal_data = $this->get_seasonal_colors($culture, $season);
        if ($seasonal_data && isset($seasonal_data['themes'])) {
            return $seasonal_data['themes'];
        }
        return [];
    }

    /**
     * Get seasonal color combinations
     */
    public function get_seasonal_combinations($culture, $season) {
        $seasonal_data = $this->get_seasonal_colors($culture, $season);
        $combinations = [];

        if ($seasonal_data && isset($seasonal_data['primary'])) {
            foreach ($seasonal_data['primary'] as $type => $data) {
                if (isset($data['combinations'])) {
                    $combinations[$type] = $data['combinations'];
                }
            }
        }

        return $combinations;
    }

    /**
     * Get seasonal application guidelines
     */
    public function get_seasonal_applications($culture, $season) {
        $seasonal_data = $this->get_seasonal_colors($culture, $season);
        $applications = [];

        if ($seasonal_data && isset($seasonal_data['primary'])) {
            foreach ($seasonal_data['primary'] as $type => $data) {
                if (isset($data['applications'])) {
                    $applications[$type] = $data['applications'];
                }
            }
        }

        return $applications;
    }
}
