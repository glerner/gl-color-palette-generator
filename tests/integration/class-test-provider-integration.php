<?php
namespace GLColorPalette\Tests\Integration;

use WP_UnitTestCase;
use GLColorPalette\Providers\Provider;
use WP_Error;

abstract class Test_Provider_Integration extends WP_UnitTestCase {
    protected Provider $provider;
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

    public function assertNotWPError($actual, $message = '') {
        $this->assertFalse(is_wp_error($actual), 
            $message ?: ($actual instanceof WP_Error ? $actual->get_error_message() : ''));
    }
}
