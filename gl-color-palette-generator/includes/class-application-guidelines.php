<?php

class ApplicationGuidelines {
    private $color_analyzer;
    private $accessibility_checker;

    // Detailed application guidelines
    private const GUIDELINES = [
        'digital_interfaces' => [
            'web' => [
                'primary_navigation' => [
                    'background' => [
                        'rules' => ['contrast_ratio >= 4.5', 'avoid_pure_black'],
                        'states' => [
                            'default' => ['opacity' => 1.0],
                            'hover' => ['lighten' => '10%'],
                            'active' => ['darken' => '5%'],
                            'disabled' => ['opacity' => 0.6]
                        ],
                        'responsive' => [
                            'mobile' => ['increase_contrast' => '5%'],
                            'dark_mode' => ['invert_lightness' => true]
                        ]
                    ],
                    'text' => [
                        'rules' => ['contrast_ratio >= 7.0', 'avoid_pure_white'],
                        'hierarchy' => [
                            'primary' => ['opacity' => 1.0],
                            'secondary' => ['opacity' => 0.8],
                            'tertiary' => ['opacity' => 0.6]
                        ]
                    ]
                ],
                'buttons' => [
                    'primary' => [
                        'background' => [
                            'rules' => ['saturation >= 60%', 'brightness >= 45%'],
                            'states' => [
                                'default' => ['base_color'],
                                'hover' => ['lighten' => '15%', 'transition' => '0.3s'],
                                'active' => ['darken' => '10%'],
                                'focus' => ['outline_color' => 'complement']
                            ]
                        ],
                        'text' => [
                            'rules' => ['contrast_ratio >= 4.5'],
                            'states' => ['hover' => ['adjust_for_contrast']]
                        ]
                    ],
                    'secondary' => [
                        'background' => [
                            'rules' => ['saturation <= 20%', 'brightness >= 90%'],
                            'states' => [
                                'default' => ['opacity' => 0.9],
                                'hover' => ['opacity' => 1.0],
                                'active' => ['opacity' => 0.8]
                            ]
                        ]
                    ]
                ]
            ],
            'mobile' => [
                'touch_targets' => [
                    'primary' => [
                        'size' => ['min' => '44px', 'recommended' => '48px'],
                        'spacing' => ['min' => '8px', 'recommended' => '12px'],
                        'states' => [
                            'pressed' => ['darken' => '15%', 'scale' => '0.98'],
                            'feedback' => ['ripple_effect' => true]
                        ]
                    ]
                ],
                'system_bars' => [
                    'status_bar' => [
                        'rules' => ['match_brand_color', 'sufficient_contrast'],
                        'platforms' => [
                            'ios' => ['light_content', 'dark_content'],
                            'android' => ['system_ui_flags']
                        ]
                    ]
                ]
            ]
        ],

        'print_materials' => [
            'branding' => [
                'logo' => [
                    'primary' => [
                        'rules' => ['spot_color_ready', 'scalable'],
                        'variations' => [
                            'full_color' => ['cmyk', 'pantone'],
                            'monochrome' => ['black', 'white', 'grayscale'],
                            'restricted' => ['single_color', 'two_color']
                        ]
                    ],
                    'clearspace' => [
                        'minimum' => '2x_height',
                        'recommended' => '3x_height'
                    ]
                ],
                'typography' => [
                    'primary_color' => [
                        'rules' => ['readability_first', 'consistent_reproduction'],
                        'sizes' => [
                            'minimum' => ['dark' => '6pt', 'light' => '8pt'],
                            'recommended' => ['body' => '10pt', 'display' => '12pt']
                        ]
                    ]
                ]
            ],
            'marketing' => [
                'collateral' => [
                    'business_cards' => [
                        'colors' => [
                            'primary' => ['brand_colors'],
                            'secondary' => ['complement_colors'],
                            'text' => ['high_contrast']
                        ],
                        'finishes' => [
                            'allowed' => ['spot_uv', 'foil', 'emboss'],
                            'restrictions' => ['color_matching_required']
                        ]
                    ],
                    'brochures' => [
                        'color_usage' => [
                            'covers' => ['full_brand_palette'],
                            'interior' => ['reduced_palette'],
                            'text' => ['readability_optimized']
                        ]
                    ]
                ]
            ]
        ],

        'environmental' => [
            'signage' => [
                'exterior' => [
                    'primary' => [
                        'rules' => [
                            'visibility' => ['distance_readable', 'weather_resistant'],
                            'illumination' => ['day_visible', 'night_visible'],
                            'materials' => ['uv_resistant', 'fade_resistant']
                        ]
                    ],
                    'colors' => [
                        'main' => ['brand_primary', 'high_contrast'],
                        'secondary' => ['support_wayfinding'],
                        'accessibility' => ['ada_compliant']
                    ]
                ],
                'interior' => [
                    'wayfinding' => [
                        'primary' => ['clear_contrast', 'consistent_system'],
                        'emergency' => ['high_visibility', 'standard_compliant']
                    ]
                ]
            ],
            'retail_space' => [
                'zones' => [
                    'entrance' => [
                        'colors' => ['welcoming', 'brand_aligned'],
                        'lighting' => ['enhance_colors', 'maintain_accuracy']
                    ],
                    'display' => [
                        'primary' => ['product_enhancing'],
                        'accent' => ['attention_drawing'],
                        'seasonal' => ['adaptable_scheme']
                    ]
                ]
            ]
        ],

        'product_design' => [
            'physical' => [
                'materials' => [
                    'primary' => [
                        'rules' => ['color_accuracy', 'durability'],
                        'finishes' => ['matte', 'gloss', 'texture'],
                        'variations' => ['batch_consistency']
                    ]
                ],
                'packaging' => [
                    'primary' => [
                        'rules' => ['shelf_impact', 'brand_recognition'],
                        'variations' => ['size_range', 'material_types']
                    ]
                ]
            ],
            'digital' => [
                'ui_elements' => [
                    'primary' => [
                        'rules' => ['consistent_display', 'device_adaptation'],
                        'states' => ['interactive', 'static', 'disabled']
                    ]
                ]
            ]
        ]
    ];

    /**
     * Get specific application guidelines
     */
    public function get_guidelines($category, $subcategory = null) {
        if (isset(self::GUIDELINES[$category])) {
            if ($subcategory && isset(self::GUIDELINES[$category][$subcategory])) {
                return self::GUIDELINES[$category][$subcategory];
            }
            return self::GUIDELINES[$category];
        }
        return null;
    }

    /**
     * Generate implementation specifications
     */
    public function generate_specifications($category, $context = []) {
        $guidelines = $this->get_guidelines($category);
        if (!$guidelines) return null;

        return [
            'specifications' => $this->compile_specifications($guidelines),
            'requirements' => $this->compile_requirements($guidelines),
            'constraints' => $this->compile_constraints($guidelines, $context),
            'recommendations' => $this->compile_recommendations($guidelines),
            'examples' => $this->generate_examples($guidelines)
        ];
    }

    /**
     * Validate color application
     */
    public function validate_application($colors, $category, $context = []) {
        $guidelines = $this->get_guidelines($category);
        if (!$guidelines) return false;

        $validation = [
            'is_valid' => true,
            'issues' => [],
            'warnings' => [],
            'suggestions' => []
        ];

        // Validate against guidelines
        foreach ($colors as $key => $color) {
            $result = $this->validate_color_usage($color, $guidelines, $context);
            if (!$result['is_valid']) {
                $validation['is_valid'] = false;
                $validation['issues'] = array_merge($validation['issues'], $result['issues']);
            }
            $validation['warnings'] = array_merge($validation['warnings'], $result['warnings']);
            $validation['suggestions'] = array_merge($validation['suggestions'], $result['suggestions']);
        }

        return $validation;
    }
} 
