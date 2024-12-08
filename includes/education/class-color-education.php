<?php
namespace GLColorPalette;

/**
 * Color Education System
 *
 * Provides structured learning about color theory, interactive elements,
 * and adaptive learning features.
 */
class ColorEducation {
    /** @var array Education modules structure */
    private const EDUCATION_MODULES = [
        'fundamentals' => [
            'color_theory_basics' => [
                'introduction' => [
                    'concepts' => [
                        'what_is_color' => [
                            'topics' => [
                                'light_spectrum' => [
                                    'content' => 'Understanding visible light and wavelengths',
                                    'interactive_elements' => [
                                        'spectrum_visualizer' => [
                                            'type' => 'interactive_slider',
                                            'range' => '380nm-700nm',
                                            'visual_feedback' => 'real_time_color_change'
                                        ],
                                        'wavelength_explorer' => [
                                            'type' => 'interactive_graph',
                                            'features' => ['zoom', 'wavelength_selection', 'color_preview']
                                        ]
                                    ],
                                    'exercises' => [
                                        'wavelength_matching' => [
                                            'difficulty' => 'beginner',
                                            'points' => 10,
                                            'time_limit' => '5 minutes'
                                        ]
                                    ]
                                ],
                                'color_perception' => [
                                    'content' => 'How human eyes process color',
                                    'interactive_demo' => 'cone_cell_simulation',
                                    'practical_examples' => ['optical_illusions', 'color_blindness_tests']
                                ]
                            ],
                            'learning_objectives' => [
                                'understand_light_nature',
                                'grasp_color_perception_basics',
                                'recognize_spectrum_components'
                            ]
                        ]
                    ]
                ]
            ],
            'color_properties' => [
                'hue' => [
                    'content' => 'Understanding color hue and its properties',
                    'interactive_elements' => [
                        'hue_wheel' => [
                            'type' => 'interactive_wheel',
                            'features' => ['rotation', 'selection', 'comparison']
                        ]
                    ],
                    'exercises' => [
                        'hue_identification' => [
                            'difficulty' => 'beginner',
                            'points' => 15,
                            'time_limit' => '3 minutes'
                        ]
                    ],
                    'learning_objectives' => [
                        'understand_hue_concept',
                        'identify_primary_colors',
                        'recognize_hue_relationships'
                    ]
                ],
                'saturation' => [
                    'content' => 'Color intensity and saturation levels',
                    'interactive_elements' => [
                        'saturation_slider' => [
                            'type' => 'interactive_slider',
                            'range' => '0-100%',
                            'visual_feedback' => 'live_color_update'
                        ]
                    ],
                    'exercises' => [
                        'saturation_matching' => [
                            'difficulty' => 'intermediate',
                            'points' => 20,
                            'time_limit' => '4 minutes'
                        ]
                    ]
                ],
                'value' => [
                    'content' => 'Understanding brightness and value',
                    'interactive_elements' => [
                        'value_scale' => [
                            'type' => 'interactive_scale',
                            'range' => '0-100%',
                            'steps' => 10
                        ]
                    ]
                ]
            ]
        ],
        'advanced_theory' => [
            'color_harmony' => [
                'complementary_colors' => [
                    'content' => 'Understanding complementary color relationships',
                    'interactive_elements' => [
                        'complement_finder' => [
                            'type' => 'interactive_wheel',
                            'features' => ['complement_highlight', 'dynamic_update']
                        ]
                    ],
                    'exercises' => [
                        'find_complements' => [
                            'difficulty' => 'advanced',
                            'points' => 25,
                            'time_limit' => '5 minutes'
                        ]
                    ]
                ],
                'color_schemes' => [
                    'content' => 'Creating harmonious color combinations',
                    'interactive_elements' => [
                        'scheme_builder' => [
                            'type' => 'interactive_palette',
                            'features' => ['drag_drop', 'harmony_check', 'export']
                        ]
                    ],
                    'exercises' => [
                        'create_scheme' => [
                            'difficulty' => 'expert',
                            'points' => 30,
                            'time_limit' => '10 minutes'
                        ]
                    ]
                ]
            ],
            'color_psychology' => [
                'emotional_impact' => [
                    'content' => 'How colors affect emotions and behavior',
                    'interactive_elements' => [
                        'mood_board' => [
                            'type' => 'interactive_board',
                            'features' => ['color_selection', 'emotion_mapping']
                        ]
                    ],
                    'exercises' => [
                        'emotion_matching' => [
                            'difficulty' => 'intermediate',
                            'points' => 20,
                            'time_limit' => '6 minutes'
                        ]
                    ]
                ]
            ]
        ],
        'practical_applications' => [
            'digital_design' => [
                'web_colors' => [
                    'content' => 'Understanding web-safe colors and RGB',
                    'interactive_elements' => [
                        'color_picker' => [
                            'type' => 'interactive_tool',
                            'features' => ['hex_input', 'rgb_sliders', 'preview']
                        ]
                    ],
                    'exercises' => [
                        'convert_colors' => [
                            'difficulty' => 'advanced',
                            'points' => 25,
                            'time_limit' => '5 minutes'
                        ]
                    ]
                ]
            ],
            'accessibility' => [
                'contrast_ratios' => [
                    'content' => 'Understanding and implementing WCAG color contrast guidelines',
                    'interactive_elements' => [
                        'contrast_checker' => [
                            'type' => 'interactive_tool',
                            'features' => ['wcag_validation', 'suggestion_engine']
                        ]
                    ],
                    'exercises' => [
                        'fix_contrast' => [
                            'difficulty' => 'expert',
                            'points' => 35,
                            'time_limit' => '8 minutes'
                        ]
                    ]
                ]
            ]
        ]
    ];

    private const COLOR_RELATIONSHIPS = [
        'analogous' => 'Colors that are next to each other on the color wheel. Creates a harmonious, comfortable design.',
        'complementary' => 'Colors opposite each other on the color wheel. Creates high contrast and visual impact.',
        'triadic' => 'Three colors equally spaced on the color wheel. Creates vibrant, balanced designs.',
        'monochromatic' => 'Different shades and tints of the same color. Creates a cohesive, sophisticated look.',
        'split-complementary' => 'A base color and two colors adjacent to its complement. Provides high contrast while being easier to balance than complementary colors.',
        'tetradic' => 'Two pairs of complementary colors. Creates a rich, dynamic color scheme best used with one dominant color.',
        'square' => 'Four colors evenly spaced on the color wheel. Creates a balanced, vibrant design when used with varying intensities.'
    ];

    /** @var array Learning progress tracker */
    private $learning_tracker;

    /** @var array Progress analyzer */
    private $progress_analyzer;

    /** @var array User interaction manager */
    private $interaction_manager;

    /**
     * Constructor
     */
    public function __construct() {
        $this->learning_tracker = [];
        $this->progress_analyzer = [];
        $this->interaction_manager = [];
    }

    /**
     * Get all education modules
     *
     * @return array Education modules structure
     */
    public function get_modules(): array {
        return self::EDUCATION_MODULES;
    }

    /**
     * Get learning objectives for a specific module
     *
     * @param string $module_path Dot-notation path to module
     * @return array Learning objectives
     */
    public function get_learning_objectives(string $module_path): array {
        $path_parts = explode('.', $module_path);
        $current = self::EDUCATION_MODULES;

        foreach ($path_parts as $part) {
            if (!isset($current[$part])) {
                return [];
            }
            $current = $current[$part];
        }

        return $current['learning_objectives'] ?? [];
    }

    /**
     * Get interactive elements for a module
     *
     * @param string $module_path Dot-notation path to module
     * @return array Interactive elements
     */
    public function get_interactive_elements(string $module_path): array {
        $path_parts = explode('.', $module_path);
        $current = self::EDUCATION_MODULES;

        foreach ($path_parts as $part) {
            if (!isset($current[$part])) {
                return [];
            }
            $current = $current[$part];
        }

        return $current['interactive_elements'] ?? [];
    }

    /**
     * Complete an exercise
     *
     * @param string $exercise_path Path to exercise
     * @param array $results Exercise results
     * @return bool Success status
     */
    public function complete_exercise(string $exercise_path, array $results): bool {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return false;
        }

        // Store exercise results
        $this->learning_tracker[$user_id][$exercise_path] = [
            'completed_at' => current_time('mysql'),
            'score' => $results['score'],
            'time_taken' => $results['time_taken']
        ];

        // Update progress
        $this->update_user_progress($user_id, $exercise_path, $results);

        return true;
    }

    /**
     * Get user progress
     *
     * @return array Progress data
     */
    public function get_user_progress(): array {
        $user_id = get_current_user_id();
        if (!$user_id || !isset($this->learning_tracker[$user_id])) {
            return [
                'completed_exercises' => 0,
                'exercises' => []
            ];
        }

        return [
            'completed_exercises' => count($this->learning_tracker[$user_id]),
            'exercises' => $this->learning_tracker[$user_id]
        ];
    }

    /**
     * Get next recommended content based on performance
     *
     * @return array Recommended content
     */
    public function get_next_recommended_content(): array {
        $user_id = get_current_user_id();
        if (!$user_id || !isset($this->learning_tracker[$user_id])) {
            return [
                'type' => 'standard',
                'content' => self::EDUCATION_MODULES['fundamentals']
            ];
        }

        // Analyze performance
        $avg_score = $this->calculate_average_score($user_id);

        if ($avg_score < 5) {
            return [
                'type' => 'remedial',
                'content' => [
                    'module' => 'fundamentals',
                    'focus' => 'basic_concepts',
                    'additional_resources' => true
                ]
            ];
        }

        return [
            'type' => 'advanced',
            'content' => [
                'module' => 'advanced_theory',
                'focus' => 'color_harmonics',
                'challenges' => true
            ]
        ];
    }

    /**
     * Get user achievements
     *
     * @return array Achievements data
     */
    public function get_user_achievements(): array {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return ['badges' => [], 'points' => 0];
        }

        $points = $this->calculate_total_points($user_id);
        $badges = $this->calculate_earned_badges($user_id);

        return [
            'badges' => $badges,
            'points' => $points
        ];
    }

    /**
     * Get content adapted for specific learning style
     *
     * @param string $style Learning style
     * @return array Adapted content
     */
    public function get_content_for_learning_style(string $style): array {
        $content = [
            'visual' => [
                'diagrams' => true,
                'color_wheels' => true,
                'infographics' => true
            ],
            'kinesthetic' => [
                'interactive_exercises' => true,
                'hands_on_tools' => true,
                'practice_projects' => true
            ],
            'auditory' => [
                'video_lectures' => true,
                'audio_guides' => true,
                'discussions' => true
            ]
        ];

        return $content[$style] ?? [];
    }

    /**
     * Get accessibility features
     *
     * @return array Accessibility features
     */
    public function get_accessibility_features(): array {
        return [
            'high_contrast' => true,
            'screen_reader_support' => true,
            'keyboard_navigation' => true,
            'text_to_speech' => true,
            'font_size_adjustment' => true
        ];
    }

    /**
     * Get progress analytics
     *
     * @return array Analytics data
     */
    public function get_progress_analytics(): array {
        $user_id = get_current_user_id();
        if (!$user_id || !isset($this->learning_tracker[$user_id])) {
            return [];
        }

        $exercises = $this->learning_tracker[$user_id];
        $scores = array_column($exercises, 'score');
        $times = array_column($exercises, 'time_taken');

        return [
            'average_score' => array_sum($scores) / count($scores),
            'time_spent' => array_sum($times),
            'learning_curve' => $this->calculate_learning_curve($exercises),
            'strengths' => $this->identify_strengths($exercises),
            'areas_for_improvement' => $this->identify_improvements($exercises)
        ];
    }

    /**
     * Calculate average score
     *
     * @param int $user_id User ID
     * @return float Average score
     */
    private function calculate_average_score(int $user_id): float {
        if (!isset($this->learning_tracker[$user_id])) {
            return 0.0;
        }

        $scores = array_column($this->learning_tracker[$user_id], 'score');
        return array_sum($scores) / count($scores);
    }

    /**
     * Calculate total points
     *
     * @param int $user_id User ID
     * @return int Total points
     */
    private function calculate_total_points(int $user_id): int {
        if (!isset($this->learning_tracker[$user_id])) {
            return 0;
        }

        $points = 0;
        foreach ($this->learning_tracker[$user_id] as $exercise) {
            $points += $exercise['score'];
        }

        return $points;
    }

    /**
     * Calculate earned badges
     *
     * @param int $user_id User ID
     * @return array Earned badges
     */
    private function calculate_earned_badges(int $user_id): array {
        if (!isset($this->learning_tracker[$user_id])) {
            return [];
        }

        $points = $this->calculate_total_points($user_id);
        $badges = [];

        if ($points >= 100) {
            $badges[] = 'color_master';
        }
        if ($points >= 50) {
            $badges[] = 'theory_expert';
        }
        if ($points >= 25) {
            $badges[] = 'spectrum_explorer';
        }

        return $badges;
    }

    /**
     * Calculate learning curve
     *
     * @param array $exercises Exercise data
     * @return array Learning curve data
     */
    private function calculate_learning_curve(array $exercises): array {
        $curve = [];
        $exercise_count = count($exercises);

        for ($i = 0; $i < $exercise_count; $i++) {
            $window_scores = array_slice(array_column($exercises, 'score'), max(0, $i - 5), min(5, $i + 1));
            $curve[] = array_sum($window_scores) / count($window_scores);
        }

        return $curve;
    }

    /**
     * Identify user strengths
     *
     * @param array $exercises Exercise data
     * @return array Strengths
     */
    private function identify_strengths(array $exercises): array {
        $strengths = [];
        $topics = [];

        foreach ($exercises as $path => $data) {
            $topic = explode('.', $path)[0];
            if (!isset($topics[$topic])) {
                $topics[$topic] = [];
            }
            $topics[$topic][] = $data['score'];
        }

        foreach ($topics as $topic => $scores) {
            $avg = array_sum($scores) / count($scores);
            if ($avg >= 8) {
                $strengths[] = $topic;
            }
        }

        return $strengths;
    }

    /**
     * Identify areas for improvement
     *
     * @param array $exercises Exercise data
     * @return array Areas for improvement
     */
    private function identify_improvements(array $exercises): array {
        $improvements = [];
        $topics = [];

        foreach ($exercises as $path => $data) {
            $topic = explode('.', $path)[0];
            if (!isset($topics[$topic])) {
                $topics[$topic] = [];
            }
            $topics[$topic][] = $data['score'];
        }

        foreach ($topics as $topic => $scores) {
            $avg = array_sum($scores) / count($scores);
            if ($avg < 6) {
                $improvements[] = $topic;
            }
        }

        return $improvements;
    }

    /**
     * Update user progress
     *
     * @param int $user_id User ID
     * @param string $exercise_path Exercise path
     * @param array $results Exercise results
     */
    private function update_user_progress(int $user_id, string $exercise_path, array $results): void {
        if (!isset($this->progress_analyzer[$user_id])) {
            $this->progress_analyzer[$user_id] = [
                'total_exercises' => 0,
                'total_time' => 0,
                'average_score' => 0
            ];
        }

        $analyzer = &$this->progress_analyzer[$user_id];
        $analyzer['total_exercises']++;
        $analyzer['total_time'] += $results['time_taken'];
        $analyzer['average_score'] = (
            ($analyzer['average_score'] * ($analyzer['total_exercises'] - 1)) +
            $results['score']
        ) / $analyzer['total_exercises'];
    }
}
