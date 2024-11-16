<?php
/**
 * Test Settings Manager Class
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\Tests\Settings;

use GL_Color_Palette_Generator\Settings\Settings_Manager;
use PHPUnit\Framework\TestCase;
use Brain\Monkey\Functions;
use Mockery;

/**
 * Class Test_Settings_Manager
 */
class Test_Settings_Manager extends TestCase {
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * Settings Manager instance
     *
     * @var Settings_Manager
     */
    private $settings_manager;

    /**
     * Setup test environment
     */
    protected function setUp(): void {
        parent::setUp();
        \Brain\Monkey\setUp();
        $this->settings_manager = new Settings_Manager();
    }

    /**
     * Teardown test environment
     */
    protected function tearDown(): void {
        \Brain\Monkey\tearDown();
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test settings initialization
     */
    public function test_init() {
        Functions\expect('add_action')
            ->once()
            ->with('admin_init', [$this->settings_manager, 'register_settings']);

        $this->settings_manager->init();
    }

    /**
     * Test settings registration
     */
    public function test_register_settings() {
        Functions\expect('register_setting')
            ->once()
            ->with(
                'gl_cpg_settings',
                'gl_cpg_options',
                [$this->settings_manager, 'sanitize_settings']
            );

        Functions\expect('add_settings_section')
            ->once()
            ->with(
                'gl_cpg_main_section',
                Mockery::type('string'),
                [$this->settings_manager, 'render_section'],
                'gl_cpg_settings'
            );

        Functions\expect('add_settings_field')
            ->atLeast()
            ->times(3);

        $this->settings_manager->register_settings();
    }

    /**
     * Test settings sanitization
     */
    public function test_sanitize_settings() {
        Functions\expect('sanitize_text_field')
            ->times(2)
            ->andReturnUsing(function($input) { return $input; });

        $input = [
            'ai_provider' => 'openai',
            'api_key' => 'test_key',
            'cache_duration' => '3600'
        ];

        $sanitized = $this->settings_manager->sanitize_settings($input);

        $this->assertEquals('openai', $sanitized['ai_provider']);
        $this->assertEquals('test_key', $sanitized['api_key']);
        $this->assertEquals(3600, $sanitized['cache_duration']);
    }

    /**
     * Test get setting with default
     */
    public function test_get_setting_with_default() {
        Functions\expect('get_option')
            ->once()
            ->with('gl_cpg_options', Mockery::type('array'))
            ->andReturn([]);

        $result = $this->settings_manager->get_setting('nonexistent', 'default');
        $this->assertEquals('default', $result);
    }

    /**
     * Test get setting with existing value
     */
    public function test_get_setting_with_value() {
        Functions\expect('get_option')
            ->once()
            ->with('gl_cpg_options', Mockery::type('array'))
            ->andReturn(['test_key' => 'test_value']);

        $result = $this->settings_manager->get_setting('test_key', 'default');
        $this->assertEquals('test_value', $result);
    }
} 
