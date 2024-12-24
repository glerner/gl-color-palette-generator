<?php
/**
 * Tests for Long Term Adaptations
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Education
 */

namespace GL_Color_Palette_Generator\Tests\Education;

use GL_Color_Palette_Generator\Tests\Test_Case_Integration;
use GL_Color_Palette_Generator\Education\Long_Term_Adaptations;

/**
 * Test Long Term Adaptations functionality
 */
class Test_Long_Term_Adaptations extends Test_Case_Integration {
    private Long_Term_Adaptations $adaptations;

    public function setUp(): void {
        parent::setUp();
        $this->adaptations = new Long_Term_Adaptations();
    }

    public function test_get_adaptation_map(): void {
        $map = $this->adaptations->get_adaptation_map();
        
        $this->assertIsArray($map);
        $this->assertArrayHasKey('neural_plasticity', $map);
        
        // Check calming environments structure
        $calming = $map['neural_plasticity']['calming_environments'];
        $this->assertArrayHasKey('blue', $calming);
        
        // Verify cortical adaptations
        $cortical = $calming['blue']['cortical_adaptations'];
        $this->assertArrayHasKey('stress_response_network', $cortical);
    }

    public function test_track_exposure_effects(): void {
        $user_id = $this->factory->user->create();
        wp_set_current_user($user_id);
        
        // Track exposure to calming environment
        $result = $this->adaptations->track_exposure([
            'environment' => 'calming',
            'color' => 'blue',
            'duration' => 3600, // 1 hour
            'intensity' => 0.7
        ]);
        
        $this->assertTrue($result);
        
        // Check accumulated effects
        $effects = $this->adaptations->get_accumulated_effects();
        $this->assertArrayHasKey('stress_response', $effects);
        $this->assertArrayHasKey('cortisol_levels', $effects);
    }

    public function test_analyze_chronic_exposure(): void {
        $user_id = $this->factory->user->create();
        wp_set_current_user($user_id);
        
        // Simulate multiple exposures
        for ($i = 0; $i < 30; $i++) { // 30 days
            $this->adaptations->track_exposure([
                'environment' => 'calming',
                'color' => 'blue',
                'duration' => 3600,
                'intensity' => 0.7,
                'timestamp' => strtotime("-{$i} days")
            ]);
        }
        
        // Analyze chronic effects
        $analysis = $this->adaptations->analyze_chronic_exposure();
        
        $this->assertArrayHasKey('neural_changes', $analysis);
        $this->assertArrayHasKey('behavioral_patterns', $analysis);
        $this->assertArrayHasKey('adaptation_timeline', $analysis);
    }

    public function test_measure_adaptation_progress(): void {
        $user_id = $this->factory->user->create();
        wp_set_current_user($user_id);
        
        // Set baseline measurements
        $baseline = $this->adaptations->set_baseline_measurements([
            'stress_response' => 0.8,
            'cognitive_performance' => 0.6,
            'emotional_stability' => 0.7
        ]);
        
        $this->assertTrue($baseline);
        
        // Track progress over time
        $progress = $this->adaptations->measure_adaptation_progress();
        
        $this->assertArrayHasKey('current_measurements', $progress);
        $this->assertArrayHasKey('improvement_metrics', $progress);
        $this->assertArrayHasKey('adaptation_rate', $progress);
    }

    public function test_environmental_optimization(): void {
        // Test optimization recommendations
        $recommendations = $this->adaptations->get_environment_optimization_recommendations([
            'space_type' => 'office',
            'primary_activity' => 'focused_work',
            'user_sensitivity' => 0.8
        ]);
        
        $this->assertArrayHasKey('color_scheme', $recommendations);
        $this->assertArrayHasKey('exposure_schedule', $recommendations);
        $this->assertArrayHasKey('intensity_levels', $recommendations);
    }

    public function test_adaptation_analytics(): void {
        $user_id = $this->factory->user->create();
        wp_set_current_user($user_id);
        
        // Generate sample data
        for ($i = 0; $i < 90; $i++) { // 90 days
            $this->adaptations->track_exposure([
                'environment' => 'calming',
                'color' => 'blue',
                'duration' => rand(1800, 7200),
                'intensity' => rand(5, 9) / 10,
                'timestamp' => strtotime("-{$i} days")
            ]);
        }
        
        // Get analytics
        $analytics = $this->adaptations->get_adaptation_analytics();
        
        $this->assertArrayHasKey('exposure_patterns', $analytics);
        $this->assertArrayHasKey('adaptation_curves', $analytics);
        $this->assertArrayHasKey('effectiveness_metrics', $analytics);
        $this->assertArrayHasKey('long_term_trends', $analytics);
    }

    public function test_personalization_system(): void {
        $user_id = $this->factory->user->create();
        wp_set_current_user($user_id);
        
        // Set user preferences and sensitivity
        $profile = $this->adaptations->set_user_profile([
            'color_sensitivity' => 0.9,
            'adaptation_rate' => 'moderate',
            'preferred_environments' => ['calming', 'focusing'],
            'schedule_constraints' => ['workday' => '9-17', 'breaks' => '2']
        ]);
        
        $this->assertTrue($profile);
        
        // Get personalized recommendations
        $recommendations = $this->adaptations->get_personalized_recommendations();
        
        $this->assertArrayHasKey('daily_schedule', $recommendations);
        $this->assertArrayHasKey('environment_settings', $recommendations);
        $this->assertArrayHasKey('adaptation_strategies', $recommendations);
    }

    public function tearDown(): void {
        parent::tearDown();
    }
}
