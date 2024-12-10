<?php
namespace GLColorPalette\Tests\Integration;

use WP_Mock\Tools\TestCase;
use GLColorPalette\Providers\Provider_Interface;

abstract class Provider_Integration_Test_Case extends TestCase {
    protected Provider_Interface $provider;
    protected array $test_params = [
        'prompt' => 'Modern tech company',
        'count' => 5,
        'format' => 'hex'
    ];

    protected function maybe_skip_test() {
        $creds = $this->get_test_credentials();
        if (empty($creds['api_key'])) {
            $this->markTestSkipped('API credentials not available');
        }
    }

    abstract protected function get_test_credentials(): array;

    protected function assertNotWPError($result) {
        $this->assertFalse(is_wp_error($result), 
            $result instanceof \WP_Error ? $result->get_error_message() : '');
    }
}
