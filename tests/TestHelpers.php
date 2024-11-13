<?php
/**
 * Test Helper Functions
 *
 * @package    GLColorPalette
 * @author     George Lerner
 * @link       https://website-tech.glerner.com/
 * @since      1.0.0
 */

namespace GLColorPalette\Tests;

class TestHelpers {
    /**
     * Create a mock provider with test credentials
     *
     * @param string $provider_class The provider class name
     * @param array  $credentials    Test credentials
     * @return object
     */
    public static function create_mock_provider($provider_class, $credentials = []) {
        $default_credentials = [
            'api_key' => 'test_key',
            'model' => 'test_model',
            'resource_name' => 'test_resource',
            'deployment_id' => 'test_deployment'
        ];

        return new $provider_class(array_merge($default_credentials, $credentials));
    }

    /**
     * Assert that a color is a valid hex code
     *
     * @param string $color The color to test
     * @return void
     */
    public static function assertValidHexColor($color) {
        $pattern = '/#[a-fA-F0-9]{6}/';
        if (!preg_match($pattern, $color)) {
            throw new \PHPUnit\Framework\AssertionFailedError(
                sprintf('Color "%s" is not a valid hex code', $color)
            );
        }
    }

    /**
     * Assert that an array contains valid hex colors
     *
     * @param array $colors Array of colors to test
     * @param int   $expected_count Expected number of colors
     * @return void
     */
    public static function assertValidColorPalette($colors, $expected_count = 5) {
        if (!is_array($colors)) {
            throw new \PHPUnit\Framework\AssertionFailedError('Colors must be an array');
        }

        if (count($colors) !== $expected_count) {
            throw new \PHPUnit\Framework\AssertionFailedError(
                sprintf('Expected %d colors, got %d', $expected_count, count($colors))
            );
        }

        foreach ($colors as $color) {
            self::assertValidHexColor($color);
        }
    }
} 
