<?php
namespace GLColorPalette\Tests\Integration;

use WP_UnitTestCase;

/**
 * Base class for provider integration tests
 */
abstract class Provider_Integration_Test_Case extends WP_UnitTestCase {
    protected $provider;
    protected $test_params = [
        'base_color' => '#FF0000',
        'mode' => 'analogous',
        'count' => 5
    ];

    /**
     * Skip test if API credentials are not configured
     */
    protected function maybe_skip_test() {
        $credentials = $this->get_test_credentials();
        if (empty($credentials)) {
            $this->markTestSkipped('API credentials not configured.');
        }
    }

    /**
     * Get test credentials from environment or constants
     */
    protected function get_test_credentials() {
        return [];
    }

    /**
     * Validate palette response format
     */
    protected function validate_palette_response($colors) {
        $this->assertIsArray($colors, 'Response should be an array');
        $this->assertCount($this->test_params['count'], $colors, 'Should return requested number of colors');

        foreach ($colors as $color) {
            $this->assertMatchesRegularExpression('/^#[A-Fa-f0-9]{6}$/', $color, 'Each color should be a valid hex code');
        }
    }
} 
