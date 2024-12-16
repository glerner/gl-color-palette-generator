<?php
namespace GL_Color_Palette_Generator\Tests;

use GL_Color_Palette_Generator\Providers\Provider;
use WP_Mock\Tools\TestCase;

/**
 * Base class for provider mock tests
 */
abstract class Test_Provider_Mock extends TestCase {
    protected Provider $provider;

    protected array $test_params = [
        'prompt' => 'Modern tech company',
        'count' => 5,
        'format' => 'hex'
    ];

    public function setUp(): void {
        parent::setUp();
        $this->maybe_skip_test();
        $this->setup_wp_mocks();
    }

    protected function maybe_skip_test(): void {
        $creds = $this->get_test_credentials();
        if (empty($creds['api_key'])) {
            $this->markTestSkipped('API credentials not available');
        }
    }

    protected function setup_wp_mocks(): void {
        // Mock wp_remote_post
        \WP_Mock::userFunction('wp_remote_post', [
            'return' => [
                'response' => ['code' => 200],
                'body' => json_encode([
                    'choices' => [
                        ['text' => '#FF0000, #00FF00, #0000FF, #FFFF00, #FF00FF']
                    ]
                ])
            ]
        ]);

        // Mock wp_remote_get
        \WP_Mock::userFunction('wp_remote_get', [
            'return' => [
                'response' => ['code' => 200],
                'body' => json_encode([
                    'colors' => ['#FF0000', '#00FF00', '#0000FF', '#FFFF00', '#FF00FF']
                ])
            ]
        ]);

        // Mock wp_remote_retrieve_response_code
        \WP_Mock::userFunction('wp_remote_retrieve_response_code', [
            'return' => 200
        ]);

        // Mock wp_remote_retrieve_body
        \WP_Mock::userFunction('wp_remote_retrieve_body', [
            'return' => json_encode([
                'choices' => [
                    ['text' => '#FF0000, #00FF00, #0000FF, #FFFF00, #FF00FF']
                ]
            ])
        ]);

        // Mock wp_json_encode
        \WP_Mock::userFunction('wp_json_encode', [
            'return' => function($data) {
                return json_encode($data);
            }
        ]);

        // Mock esc_html__
        \WP_Mock::userFunction('esc_html__', [
            'return' => function($text) {
                return $text;
            }
        ]);
    }

    abstract protected function get_test_credentials(): array;
}
