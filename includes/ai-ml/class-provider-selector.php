<?php
/**
 * AI Provider Selector Class
 *
 * Handles the selection and management of AI service providers based on
 * performance, cost, availability, and quality metrics.
 *
 * @package GL_Color_Palette_Generator
 * @subpackage AI_ML
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\AI_ML;

use GL_Color_Palette_Generator\Settings\Settings_Manager;
use GL_Color_Palette_Generator\Performance\Performance_Monitor;
use GL_Color_Palette_Generator\Business\Cost_Calculator;
use GL_Color_Palette_Generator\System\Availability_Checker;
use GL_Color_Palette_Generator\Analysis\Quality_Tracker;

/**
 * Class Provider_Selector
 *
 * Implements intelligent selection of AI providers based on multiple criteria
 * including performance metrics, cost constraints, and quality requirements.
 *
 * @since 1.0.0
 */
class Provider_Selector {
    /**
     * Settings manager instance
     *
     * @var Settings_Manager
     * @since 1.0.0
     */
    private $settings;

    /**
     * Performance monitoring instance
     *
     * @var Performance_Monitor
     * @since 1.0.0
     */
    private $performance_monitor;

    /**
     * Cost calculation instance
     *
     * @var Cost_Calculator
     * @since 1.0.0
     */
    private $cost_calculator;

    /**
     * Availability checking instance
     *
     * @var Availability_Checker
     * @since 1.0.0
     */
    private $availability_checker;

    /**
     * Quality tracking instance
     *
     * @var Quality_Tracker
     * @since 1.0.0
     */
    private $quality_tracker;

    /**
     * Provider capabilities and characteristics
     *
     * @var array
     */
    private $provider_specs = [
        'openai' => [
            'models' => [
                'gpt-4' => [
                    'quality_score' => 0.95,
                    'cost_per_1k' => 0.03,
                    'max_tokens' => 8192,
                    'creative_strength' => 0.9,
                    'response_time' => 2.0
                ],
                'gpt-3.5-turbo' => [
                    'quality_score' => 0.85,
                    'cost_per_1k' => 0.002,
                    'max_tokens' => 4096,
                    'creative_strength' => 0.8,
                    'response_time' => 1.0
                ]
            ],
            'features' => ['streaming', 'function_calling', 'json_mode'],
            'reliability' => 0.99,
            'rate_limits' => [
                'rpm' => 500,
                'tpm' => 90000
            ]
        ],
        'anthropic' => [
            'models' => [
                'claude-3-opus' => [
                    'quality_score' => 0.93,
                    'cost_per_1k' => 0.015,
                    'max_tokens' => 4096,
                    'creative_strength' => 0.85,
                    'response_time' => 2.5
                ]
            ],
            'features' => ['streaming', 'json_mode'],
            'reliability' => 0.98,
            'rate_limits' => [
                'rpm' => 450,
                'tpm' => 100000
            ]
        ],
        // ... additional providers
    ];

    /**
     * Constructor
     *
     * Initializes the provider selector with required instances.
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->settings = new Settings_Manager();
        $this->performance_monitor = new Performance_Monitor();
        $this->cost_calculator = new Cost_Calculator();
        $this->availability_checker = new Availability_Checker();
        $this->quality_tracker = new Quality_Tracker();
    }

    /**
     * Select optimal provider based on requirements
     *
     * @param array $requirements
     * @return array
     * @since 1.0.0
     */
    public function select_provider($requirements = []) {
        // Get available providers
        $available_providers = $this->get_available_providers();

        // Filter by requirements
        $eligible_providers = $this->filter_eligible_providers(
            $available_providers,
            $requirements
        );

        // Score providers
        $scored_providers = $this->score_providers(
            $eligible_providers,
            $requirements
        );

        // Select best provider
        $selected_provider = $this->select_best_provider($scored_providers);

        // Log selection
        $this->log_provider_selection($selected_provider, $requirements);

        return $selected_provider;
    }

    /**
     * Get currently available providers
     *
     * @return array
     * @since 1.0.0
     */
    private function get_available_providers() {
        $factory = new Providers\AiProviderFactory();
        $providers = $factory->get_available_providers();

        foreach ($providers as $key => &$provider) {
            $provider['status'] = $this->check_provider_status($key);
            $provider['features'] = $this->get_provider_features($key);
            $provider['pricing'] = $this->get_provider_pricing($key);
            $provider['requirements'] = $this->get_provider_requirements($key);
        }

        return $providers;
    }

    /**
     * Filter providers by requirements
     *
     * @param array $providers
     * @param array $requirements
     * @return array
     * @since 1.0.0
     */
    private function filter_eligible_providers($providers, $requirements) {
        return array_filter($providers, function($provider_data) use ($requirements) {
            // Check budget constraints
            if (isset($requirements['max_cost'])) {
                if ($provider_data['current_costs']['cost_per_request'] > $requirements['max_cost']) {
                    return false;
                }
            }

            // Check quality requirements
            if (isset($requirements['min_quality'])) {
                if ($provider_data['quality_metrics']['average_score'] < $requirements['min_quality']) {
                    return false;
                }
            }

            // Check feature requirements
            if (isset($requirements['required_features'])) {
                foreach ($requirements['required_features'] as $feature) {
                    if (!in_array($feature, $provider_data['features'])) {
                        return false;
                    }
                }
            }

            // Check response time requirements
            if (isset($requirements['max_response_time'])) {
                if ($provider_data['current_performance']['avg_response_time'] >
                    $requirements['max_response_time']) {
                    return false;
                }
            }

            // Check token requirements
            if (isset($requirements['required_tokens'])) {
                $has_suitable_model = false;
                foreach ($provider_data['models'] as $model) {
                    if ($model['max_tokens'] >= $requirements['required_tokens']) {
                        $has_suitable_model = true;
                        break;
                    }
                }
                if (!$has_suitable_model) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * Score providers based on requirements
     *
     * @param array $providers
     * @param array $requirements
     * @return array
     * @since 1.0.0
     */
    private function score_providers($providers, $requirements) {
        $scored = [];

        foreach ($providers as $provider => $data) {
            $score = 0;

            // Quality score (0-40 points)
            $score += $data['quality_metrics']['average_score'] * 40;

            // Performance score (0-20 points)
            $performance_score = $this->calculate_performance_score($data['current_performance']);
            $score += $performance_score * 20;

            // Cost efficiency score (0-20 points)
            $cost_score = $this->calculate_cost_efficiency_score($data['current_costs']);
            $score += $cost_score * 20;

            // Reliability score (0-20 points)
            $reliability_score = $this->calculate_reliability_score($data);
            $score += $reliability_score * 20;

            // Apply requirement-specific weights
            $score = $this->apply_requirement_weights($score, $data, $requirements);

            $scored[$provider] = [
                'provider' => $provider,
                'score' => $score,
                'metrics' => [
                    'quality' => $data['quality_metrics']['average_score'],
                    'performance' => $performance_score,
                    'cost_efficiency' => $cost_score,
                    'reliability' => $reliability_score
                ]
            ];
        }

        return $scored;
    }

    /**
     * Calculate performance score
     *
     * @param array $performance_data
     * @return float
     * @since 1.0.0
     */
    private function calculate_performance_score($performance_data) {
        $response_time_score = 1 - min(1, $performance_data['avg_response_time'] / 5);
        $success_rate_score = $performance_data['success_rate'];
        $throughput_score = min(1, $performance_data['requests_per_minute'] / 100);

        return ($response_time_score + $success_rate_score + $throughput_score) / 3;
    }

    /**
     * Calculate cost efficiency score
     *
     * @param array $cost_data
     * @return float
     * @since 1.0.0
     */
    private function calculate_cost_efficiency_score($cost_data) {
        $base_cost_score = 1 - min(1, $cost_data['cost_per_request'] / 0.05);
        $volume_discount_score = $cost_data['volume_discount_rate'];

        return ($base_cost_score + $volume_discount_score) / 2;
    }

    /**
     * Calculate reliability score
     *
     * @param array $provider_data
     * @return float
     * @since 1.0.0
     */
    private function calculate_reliability_score($provider_data) {
        $uptime_score = $provider_data['reliability'];
        $error_rate_score = 1 - $provider_data['current_performance']['error_rate'];
        $rate_limit_score = min(1, $provider_data['rate_limits']['rpm'] / 1000);

        return ($uptime_score + $error_rate_score + $rate_limit_score) / 3;
    }

    /**
     * Apply requirement-specific weights
     *
     * @param float $score
     * @param array $provider_data
     * @param array $requirements
     * @return float
     * @since 1.0.0
     */
    private function apply_requirement_weights($score, $provider_data, $requirements) {
        if (isset($requirements['priority'])) {
            switch ($requirements['priority']) {
                case 'quality':
                    $score *= $provider_data['quality_metrics']['average_score'];
                    break;
                case 'speed':
                    $score *= (1 - min(1, $provider_data['current_performance']['avg_response_time'] / 5));
                    break;
                case 'cost':
                    $score *= (1 - min(1, $provider_data['current_costs']['cost_per_request'] / 0.05));
                    break;
                case 'reliability':
                    $score *= $provider_data['reliability'];
                    break;
            }
        }

        return $score;
    }

    /**
     * Select best provider based on scores
     *
     * @param array $scored_providers
     * @return array
     * @since 1.0.0
     */
    private function select_best_provider($scored_providers) {
        uasort($scored_providers, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return array_slice($scored_providers, 0, 1, true);
    }

    /**
     * Log provider selection
     *
     * @param array $selected_provider
     * @param array $requirements
     * @since 1.0.0
     */
    private function log_provider_selection($selected_provider, $requirements) {
        $log_data = [
            'timestamp' => current_time('mysql'),
            'selected_provider' => $selected_provider,
            'requirements' => $requirements,
            'available_providers' => $this->get_available_providers(),
            'selection_metrics' => [
                'quality_score' => $selected_provider['metrics']['quality'],
                'performance_score' => $selected_provider['metrics']['performance'],
                'cost_efficiency' => $selected_provider['metrics']['cost_efficiency'],
                'reliability' => $selected_provider['metrics']['reliability']
            ]
        ];

        // Log to database or monitoring system
        do_action('color_palette_provider_selection', $log_data);
    }

    /**
     * Select optimal provider
     *
     * @param array $requirements
     * @return array
     * @since 1.0.0
     */
    public function select_optimal_provider($requirements = []) {
        $providers = $this->get_available_providers();
        $scores = [];

        foreach ($providers as $key => $provider) {
            $scores[$key] = $this->calculate_provider_score($provider, $requirements);
        }

        arsort($scores);
        return [
            'selected_provider' => key($scores),
            'score_breakdown' => $scores,
            'reasoning' => $this->get_selection_reasoning($scores)
        ];
    }

    /**
     * Validate provider configuration
     *
     * @param string $provider
     * @param array $config
     * @return array
     * @since 1.0.0
     */
    public function validate_provider_configuration($provider, $config) {
        try {
            $factory = new Providers\AiProviderFactory();
            $provider_instance = $factory->create_provider($provider);

            $validation_result = $provider_instance->validate_configuration($config);

            return [
                'is_valid' => $validation_result['valid'],
                'messages' => $validation_result['messages'],
                'recommendations' => $this->get_configuration_recommendations($provider, $validation_result)
            ];
        } catch (Exception $e) {
            return [
                'is_valid' => false,
                'messages' => [$e->getMessage()],
                'recommendations' => $this->get_error_recommendations($e)
            ];
        }
    }
}
