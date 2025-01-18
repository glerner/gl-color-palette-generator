<?php
declare(strict_types=1);

namespace GL_Color_Palette_Generator\Tests\Settings;

use GL_Color_Palette_Generator\Settings\Settings_Manager;
use GL_Color_Palette_Generator\Settings\Settings_Types;
use GL_Color_Palette_Generator\Exceptions\Palette_Generation_Exception;
use WP_Mock;
use WP_Mock\Tools\TestCase;

class Test_Settings_Manager extends WP_Mock_Test_Case {
    private Settings_Manager $settings_manager;

    public function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();
        $this->settings_manager = new Settings_Manager();
    }

    public function tearDown(): void {
        WP_Mock::tearDown();
        parent::tearDown();
    }

    public function test_init(): void {
        WP_Mock::expectActionAdded('admin_init', [$this->settings_manager, 'register_settings']);
        $this->settings_manager->init();
        $this->assertConditionsMet();
    }

    public function test_register_settings(): void {
        WP_Mock::userFunction('register_setting', [
            'times' => 1,
            'args' => [
                'gl_cpg_settings',
                'gl_cpg_options',
                [
                    'type' => 'array',
                    'sanitize_callback' => [$this->settings_manager, 'sanitize_settings'],
                    'default' => Settings_Types::DEFAULT_SETTINGS,
                ],
            ],
        ]);

        WP_Mock::userFunction('add_settings_section', [
            'times' => 1,
            'args' => [
                'gl_cpg_main_section',
                $this->anything(),
                [$this->settings_manager, 'render_section'],
                'gl_cpg_settings',
            ],
        ]);

        WP_Mock::userFunction('add_settings_field', [
            'times' => '>=' . count(Settings_Types::get_field_definitions()),
        ]);

        $this->settings_manager->register_settings();
        $this->assertConditionsMet();
    }

    public function test_render_section(): void {
        WP_Mock::userFunction('esc_html__', [
            'args' => ['Configure your color palette generator settings below.', 'gl-color-palette-generator'],
            'return' => 'Configure your color palette generator settings below.',
        ]);

        ob_start();
        $this->settings_manager->render_section();
        $output = ob_get_clean();

        $this->assertStringContainsString('Configure your color palette generator settings below.', $output);
    }

    public function test_render_field_select(): void {
        $args = [
            'key' => 'ai_provider',
            'field' => [
                'type' => 'select',
                'title' => 'AI Provider',
                'options' => Settings_Types::AI_PROVIDERS,
            ],
        ];

        WP_Mock::userFunction('esc_attr', [
            'return_arg' => true,
        ]);

        WP_Mock::userFunction('esc_html', [
            'return_arg' => true,
        ]);

        WP_Mock::userFunction('selected', [
            'return' => 'selected="selected"',
        ]);

        ob_start();
        $this->settings_manager->render_field($args);
        $output = ob_get_clean();

        $this->assertStringContainsString('<select', $output);
        $this->assertStringContainsString('</select>', $output);
        foreach (Settings_Types::AI_PROVIDERS as $key => $label) {
            $this->assertStringContainsString($key, $output);
            $this->assertStringContainsString($label, $output);
        }
    }

    public function test_render_field_password(): void {
        $args = [
            'key' => 'api_key',
            'field' => [
                'type' => 'password',
                'title' => 'API Key',
            ],
        ];

        WP_Mock::userFunction('esc_attr', [
            'return_arg' => true,
        ]);

        ob_start();
        $this->settings_manager->render_field($args);
        $output = ob_get_clean();

        $this->assertStringContainsString('<input type="password"', $output);
        $this->assertStringContainsString('class="regular-text"', $output);
    }

    public function test_render_field_number(): void {
        $args = [
            'key' => 'cache_duration',
            'field' => [
                'type' => 'number',
                'title' => 'Cache Duration',
                'min' => 0,
                'max' => 86400,
            ],
        ];

        WP_Mock::userFunction('esc_attr', [
            'return_arg' => true,
        ]);

        ob_start();
        $this->settings_manager->render_field($args);
        $output = ob_get_clean();

        $this->assertStringContainsString('<input type="number"', $output);
        $this->assertStringContainsString('min="0"', $output);
        $this->assertStringContainsString('max="86400"', $output);
        $this->assertStringContainsString('class="small-text"', $output);
    }

    public function test_sanitize_settings_success(): void {
        $input = [
            'ai_provider' => 'openai',
            'api_key' => 'sk-1234567890abcdef1234567890abcdef1234567890abcdef',
            'cache_duration' => 3600,
            'max_colors' => 10,
            'default_colors' => 5,
            'enable_analytics' => true,
            'rate_limit' => 60,
            'debug_mode' => false,
        ];

        $output = $this->settings_manager->sanitize_settings($input);

        $this->assertEquals('openai', $output['ai_provider']);
        $this->assertEquals('sk-1234567890abcdef1234567890abcdef1234567890abcdef', $output['api_key']);
        $this->assertEquals(3600, $output['cache_duration']);
        $this->assertEquals(10, $output['max_colors']);
        $this->assertEquals(5, $output['default_colors']);
        $this->assertTrue($output['enable_analytics']);
        $this->assertEquals(60, $output['rate_limit']);
        $this->assertFalse($output['debug_mode']);
    }

    public function test_sanitize_settings_missing_required(): void {
        $this->expectException(Palette_Generation_Exception::class);
        $this->expectExceptionMessage('Field AI Provider is required');

        $input = [
            'cache_duration' => 3600,
        ];

        $this->settings_manager->sanitize_settings($input);
    }

    public function test_sanitize_settings_invalid_value(): void {
        $this->expectException(Palette_Generation_Exception::class);
        $this->expectExceptionMessage('Invalid value for Cache Duration');

        $input = [
            'ai_provider' => 'openai',
            'api_key' => 'sk-1234567890abcdef1234567890abcdef1234567890abcdef',
            'cache_duration' => -1,
        ];

        $this->settings_manager->sanitize_settings($input);
    }

    public function test_get_option(): void {
        WP_Mock::userFunction('get_option', [
            'args' => ['gl_cpg_options', []],
            'return' => ['ai_provider' => 'openai'],
        ]);

        $value = $this->settings_manager->get_option('ai_provider');
        $this->assertEquals('openai', $value);

        $default_value = $this->settings_manager->get_option('nonexistent_key');
        $this->assertEquals(Settings_Types::DEFAULT_SETTINGS['nonexistent_key'] ?? null, $default_value);
    }

    public function test_update_option_success(): void {
        WP_Mock::userFunction('update_option', [
            'args' => ['gl_cpg_options', $this->anything()],
            'return' => true,
        ]);

        $result = $this->settings_manager->update_option('cache_duration', 7200);
        $this->assertTrue($result);
    }

    public function test_update_option_invalid_key(): void {
        $this->expectException(Palette_Generation_Exception::class);
        $this->expectExceptionMessage('Invalid setting key: invalid_key');

        $this->settings_manager->update_option('invalid_key', 'value');
    }

    public function test_update_option_invalid_value(): void {
        $this->expectException(Palette_Generation_Exception::class);
        $this->expectExceptionMessage('Invalid value for Cache Duration');

        $this->settings_manager->update_option('cache_duration', -1);
    }
}
