<?php
namespace GLColorPalette;

/**
 * Long Term Adaptations System
 * 
 * Tracks and analyzes long-term effects of color exposure on users,
 * providing personalized recommendations and environmental optimization.
 */
class LongTermAdaptations {
    /** @var array Adaptation mapping structure */
    private const LONG_TERM_ADAPTATIONS = [
        'neural_plasticity' => [
            'calming_environments' => [
                'blue' => [
                    'cortical_adaptations' => [
                        'stress_response_network' => [
                            'baseline_adjustment' => [
                                'cortisol_regulation' => [
                                    'resting_levels' => ['reduction' => '15-20%', 'stability' => 'improved'],
                                    'diurnal_rhythm' => ['optimization' => 'enhanced', 'consistency' => 'increased'],
                                    'timeline' => ['onset' => '3-4 weeks', 'stabilization' => '2-3 months']
                                ],
                                'amygdala_reactivity' => [
                                    'threshold_changes' => ['elevation' => 'significant', 'sustainability' => 'long_term'],
                                    'emotional_processing' => ['efficiency' => 'improved', 'balance' => 'enhanced']
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];

    /** @var array Adaptation monitor */
    private $adaptation_monitor;

    /** @var array Chronic exposure analyzer */
    private $chronic_exposure_analyzer;

    /**
     * Constructor
     */
    public function __construct() {
        $this->adaptation_monitor = [];
        $this->chronic_exposure_analyzer = [];
    }

    /**
     * Get adaptation map
     *
     * @return array Adaptation mapping
     */
    public function get_adaptation_map(): array {
        return self::LONG_TERM_ADAPTATIONS;
    }

    /**
     * Track color exposure
     *
     * @param array $exposure_data Exposure data
     * @return bool Success status
     */
    public function track_exposure(array $exposure_data): bool {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return false;
        }

        if (!isset($this->adaptation_monitor[$user_id])) {
            $this->adaptation_monitor[$user_id] = [];
        }

        $this->adaptation_monitor[$user_id][] = array_merge(
            $exposure_data,
            ['timestamp' => $exposure_data['timestamp'] ?? current_time('timestamp')]
        );

        return true;
    }

    /**
     * Get accumulated effects
     *
     * @return array Accumulated effects data
     */
    public function get_accumulated_effects(): array {
        $user_id = get_current_user_id();
        if (!$user_id || !isset($this->adaptation_monitor[$user_id])) {
            return [];
        }

        $exposures = $this->adaptation_monitor[$user_id];
        $total_duration = array_sum(array_column($exposures, 'duration'));
        $weighted_intensity = 0;

        foreach ($exposures as $exposure) {
            $weighted_intensity += $exposure['intensity'] * ($exposure['duration'] / $total_duration);
        }

        return [
            'stress_response' => $this->calculate_stress_response($weighted_intensity),
            'cortisol_levels' => $this->calculate_cortisol_levels($total_duration, $weighted_intensity),
            'cognitive_performance' => $this->calculate_cognitive_impact($exposures)
        ];
    }

    /**
     * Analyze chronic exposure
     *
     * @return array Analysis results
     */
    public function analyze_chronic_exposure(): array {
        $user_id = get_current_user_id();
        if (!$user_id || !isset($this->adaptation_monitor[$user_id])) {
            return [];
        }

        $exposures = $this->adaptation_monitor[$user_id];
        $chronic_patterns = $this->identify_chronic_patterns($exposures);
        $adaptation_timeline = $this->calculate_adaptation_timeline($exposures);

        return [
            'neural_changes' => $this->analyze_neural_changes($chronic_patterns),
            'behavioral_patterns' => $this->analyze_behavioral_patterns($chronic_patterns),
            'adaptation_timeline' => $adaptation_timeline
        ];
    }

    /**
     * Set baseline measurements
     *
     * @param array $measurements Baseline measurements
     * @return bool Success status
     */
    public function set_baseline_measurements(array $measurements): bool {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return false;
        }

        if (!isset($this->chronic_exposure_analyzer[$user_id])) {
            $this->chronic_exposure_analyzer[$user_id] = [];
        }

        $this->chronic_exposure_analyzer[$user_id]['baseline'] = array_merge(
            $measurements,
            ['timestamp' => current_time('timestamp')]
        );

        return true;
    }

    /**
     * Measure adaptation progress
     *
     * @return array Progress measurements
     */
    public function measure_adaptation_progress(): array {
        $user_id = get_current_user_id();
        if (!$user_id || !isset($this->chronic_exposure_analyzer[$user_id])) {
            return [];
        }

        $baseline = $this->chronic_exposure_analyzer[$user_id]['baseline'];
        $current = $this->calculate_current_measurements();

        return [
            'current_measurements' => $current,
            'improvement_metrics' => $this->calculate_improvements($baseline, $current),
            'adaptation_rate' => $this->calculate_adaptation_rate($baseline, $current)
        ];
    }

    /**
     * Get environment optimization recommendations
     *
     * @param array $parameters Environment parameters
     * @return array Recommendations
     */
    public function get_environment_optimization_recommendations(array $parameters): array {
        return [
            'color_scheme' => $this->recommend_color_scheme($parameters),
            'exposure_schedule' => $this->generate_exposure_schedule($parameters),
            'intensity_levels' => $this->calculate_optimal_intensity($parameters)
        ];
    }

    /**
     * Get adaptation analytics
     *
     * @return array Analytics data
     */
    public function get_adaptation_analytics(): array {
        $user_id = get_current_user_id();
        if (!$user_id || !isset($this->adaptation_monitor[$user_id])) {
            return [];
        }

        $exposures = $this->adaptation_monitor[$user_id];

        return [
            'exposure_patterns' => $this->analyze_exposure_patterns($exposures),
            'adaptation_curves' => $this->generate_adaptation_curves($exposures),
            'effectiveness_metrics' => $this->calculate_effectiveness_metrics($exposures),
            'long_term_trends' => $this->analyze_long_term_trends($exposures)
        ];
    }

    /**
     * Set user profile
     *
     * @param array $profile User profile data
     * @return bool Success status
     */
    public function set_user_profile(array $profile): bool {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return false;
        }

        if (!isset($this->chronic_exposure_analyzer[$user_id])) {
            $this->chronic_exposure_analyzer[$user_id] = [];
        }

        $this->chronic_exposure_analyzer[$user_id]['profile'] = $profile;

        return true;
    }

    /**
     * Get personalized recommendations
     *
     * @return array Recommendations
     */
    public function get_personalized_recommendations(): array {
        $user_id = get_current_user_id();
        if (!$user_id || !isset($this->chronic_exposure_analyzer[$user_id])) {
            return [];
        }

        $profile = $this->chronic_exposure_analyzer[$user_id]['profile'];
        $exposures = $this->adaptation_monitor[$user_id] ?? [];

        return [
            'daily_schedule' => $this->generate_daily_schedule($profile),
            'environment_settings' => $this->calculate_optimal_settings($profile),
            'adaptation_strategies' => $this->develop_adaptation_strategies($profile, $exposures)
        ];
    }

    /**
     * Calculate stress response
     *
     * @param float $weighted_intensity Weighted intensity
     * @return array Stress response data
     */
    private function calculate_stress_response(float $weighted_intensity): array {
        return [
            'reduction' => min(100, $weighted_intensity * 100),
            'stability' => $weighted_intensity >= 0.7 ? 'high' : 'moderate'
        ];
    }

    /**
     * Calculate cortisol levels
     *
     * @param int $total_duration Total duration
     * @param float $weighted_intensity Weighted intensity
     * @return array Cortisol levels data
     */
    private function calculate_cortisol_levels(int $total_duration, float $weighted_intensity): array {
        $exposure_hours = $total_duration / 3600;
        $impact = min(1.0, $exposure_hours * $weighted_intensity / 24);

        return [
            'reduction_percentage' => $impact * 100,
            'stability_score' => $impact >= 0.8 ? 'optimal' : 'improving'
        ];
    }

    /**
     * Calculate cognitive impact
     *
     * @param array $exposures Exposure data
     * @return array Cognitive impact data
     */
    private function calculate_cognitive_impact(array $exposures): array {
        $total_impact = 0;
        foreach ($exposures as $exposure) {
            $total_impact += $exposure['intensity'] * ($exposure['duration'] / 3600);
        }

        return [
            'focus_improvement' => min(100, $total_impact * 10),
            'mental_clarity' => $total_impact >= 5 ? 'enhanced' : 'normal'
        ];
    }

    /**
     * Identify chronic patterns
     *
     * @param array $exposures Exposure data
     * @return array Chronic patterns
     */
    private function identify_chronic_patterns(array $exposures): array {
        $patterns = [];
        $daily_exposures = [];

        foreach ($exposures as $exposure) {
            $date = date('Y-m-d', $exposure['timestamp']);
            if (!isset($daily_exposures[$date])) {
                $daily_exposures[$date] = [];
            }
            $daily_exposures[$date][] = $exposure;
        }

        foreach ($daily_exposures as $date => $day_exposures) {
            $patterns[$date] = [
                'total_duration' => array_sum(array_column($day_exposures, 'duration')),
                'average_intensity' => array_sum(array_column($day_exposures, 'intensity')) / count($day_exposures)
            ];
        }

        return $patterns;
    }

    /**
     * Calculate adaptation timeline
     *
     * @param array $exposures Exposure data
     * @return array Timeline data
     */
    private function calculate_adaptation_timeline(array $exposures): array {
        if (empty($exposures)) {
            return [];
        }

        $first_exposure = min(array_column($exposures, 'timestamp'));
        $last_exposure = max(array_column($exposures, 'timestamp'));
        $duration_days = ($last_exposure - $first_exposure) / 86400;

        return [
            'initial_exposure' => date('Y-m-d', $first_exposure),
            'current_stage' => $this->determine_adaptation_stage($duration_days),
            'estimated_completion' => date('Y-m-d', $first_exposure + (90 * 86400))
        ];
    }

    /**
     * Determine adaptation stage
     *
     * @param float $duration_days Duration in days
     * @return string Adaptation stage
     */
    private function determine_adaptation_stage(float $duration_days): string {
        if ($duration_days < 14) {
            return 'initial_adaptation';
        } elseif ($duration_days < 45) {
            return 'intermediate_adaptation';
        } else {
            return 'stable_adaptation';
        }
    }

    /**
     * Calculate current measurements
     *
     * @return array Current measurements
     */
    private function calculate_current_measurements(): array {
        $user_id = get_current_user_id();
        $recent_exposures = array_slice(
            $this->adaptation_monitor[$user_id],
            -10
        );

        $measurements = [
            'stress_response' => 0,
            'cognitive_performance' => 0,
            'emotional_stability' => 0
        ];

        foreach ($recent_exposures as $exposure) {
            $impact = $exposure['intensity'] * ($exposure['duration'] / 3600);
            $measurements['stress_response'] += $impact * 0.2;
            $measurements['cognitive_performance'] += $impact * 0.15;
            $measurements['emotional_stability'] += $impact * 0.25;
        }

        return array_map(function($value) {
            return min(1.0, $value / count($recent_exposures));
        }, $measurements);
    }

    /**
     * Calculate improvements
     *
     * @param array $baseline Baseline measurements
     * @param array $current Current measurements
     * @return array Improvement metrics
     */
    private function calculate_improvements(array $baseline, array $current): array {
        $improvements = [];
        foreach ($baseline as $metric => $value) {
            if (isset($current[$metric])) {
                $improvements[$metric] = [
                    'change' => ($current[$metric] - $value) * 100,
                    'status' => $current[$metric] > $value ? 'improved' : 'declined'
                ];
            }
        }
        return $improvements;
    }

    /**
     * Calculate adaptation rate
     *
     * @param array $baseline Baseline measurements
     * @param array $current Current measurements
     * @return array Adaptation rate data
     */
    private function calculate_adaptation_rate(array $baseline, array $current): array {
        $total_change = 0;
        $metrics_count = 0;

        foreach ($baseline as $metric => $value) {
            if (isset($current[$metric])) {
                $total_change += abs($current[$metric] - $value);
                $metrics_count++;
            }
        }

        $average_change = $metrics_count > 0 ? $total_change / $metrics_count : 0;

        return [
            'rate' => $average_change * 100,
            'classification' => $this->classify_adaptation_rate($average_change)
        ];
    }

    /**
     * Classify adaptation rate
     *
     * @param float $rate Adaptation rate
     * @return string Classification
     */
    private function classify_adaptation_rate(float $rate): string {
        if ($rate > 0.5) {
            return 'rapid';
        } elseif ($rate > 0.2) {
            return 'moderate';
        } else {
            return 'gradual';
        }
    }
}
