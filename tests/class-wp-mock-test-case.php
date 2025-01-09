<?php
/**
 * Base Test Case for WordPress Mock Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests;

use WP_Mock;
use Mockery;
use WP_Mock\Tools\TestCase;

/**
 * Base Test Case class that provides WP_Mock integration
 * Extends our base Test_Case to maintain Mockery support
 */
class WP_Mock_Test_Case extends Unit_Test_Case {

    /**
     * Stores WordPress functions that have been mocked
     *
     * @var array
     */
    protected $mocked_functions = [];

    /**
     * Set up the test environment
     * Initializes WP_Mock for each test
     */
    protected function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();
    }

    /**
     * Clean up the test environment
     * Tears down WP_Mock after each test
     */
    protected function tearDown(): void {
        foreach ($this->mocked_functions as $function_name) {
            if (function_exists($function_name)) {
                runkit_function_remove($function_name);
            }
        }
        $this->mocked_functions = [];
        WP_Mock::tearDown();
        parent::tearDown();
    }

    /**
     * Assert that all expected filters were called
     * This method should be called at the end of each test
     */
    protected function assertHooksWereCalled() {
        $this->assertTrue(WP_Mock::expectedFiltersCalled());
        $this->assertTrue(WP_Mock::expectedActionsCalled());
    }

    /**
     * Mock a WordPress function
     *
     * @param string $function_name Name of function to mock
     * @param mixed  $return_value  Value the function should return
     * @param int    $times        Number of times the function should be called
     */
    protected function mock_function(string $function_name, $return_value, int $times = 1): void {
        $this->mocked_functions[] = $function_name;
        WP_Mock::userFunction($function_name, [
            'times' => $times,
            'return' => $return_value,
        ]);
    }

    /**
     * Mock a WordPress filter or action hook
     *
     * @param string $hook_name   Name of the hook to mock
     * @param mixed  $return_value Value to return when hook is applied
     * @param int    $priority    Priority of the hook (default: 10)
     * @param int    $args        Number of arguments the hook accepts (default: 1)
     */
    protected function mock_hook(string $hook_name, $return_value, int $priority = 10, int $args = 1): void {
        WP_Mock::onFilter($hook_name)
            ->with(...array_fill(0, $args, WP_Mock\Functions::type('mixed')))
            ->reply($return_value);
    }

    /**
     * Mock WordPress option getting/setting
     *
     * @param string $option_name Option name to mock
     * @param mixed  $value       Value to return for the option
     */
    protected function mock_option(string $option_name, $value): void {
        $this->mock_function('get_option', $value);
        $this->mock_function('update_option', true);
    }

    /**
     * Mock WordPress AJAX functionality
     */
    protected function mock_ajax(): void {
        if (!defined('DOING_AJAX')) {
            define('DOING_AJAX', true);
        }
        $this->mock_function('wp_send_json_success', null);
        $this->mock_function('wp_send_json_error', null);
    }

    /**
     * Mock WordPress capability check
     *
     * @param string $capability Capability to check
     * @param bool   $has_cap    Whether the user has the capability
     */
    protected function mock_capability(string $capability, bool $has_cap = true): void {
        $this->mock_function('current_user_can', $has_cap);
    }

    /**
     * Mock WordPress nonce verification
     *
     * @param string $nonce_name Name of the nonce
     * @param bool   $is_valid   Whether the nonce is valid
     */
    protected function mock_nonce(string $nonce_name, bool $is_valid = true): void {
        $this->mock_function('wp_verify_nonce', $is_valid);
        $this->mock_function('wp_create_nonce', 'mocked_nonce');
    }

    /**
     * Mock WordPress post data
     *
     * @param array $post_data Post data to mock
     */
    protected function mock_post(array $post_data): void {
        global $post;
        $post = (object) array_merge([
            'ID' => 1,
            'post_type' => 'post',
            'post_status' => 'publish'
        ], $post_data);
    }

    /**
     * Mock WordPress REST API request
     *
     * @param array $request_data Request data to mock
     * @return WP_REST_Request Mock request object
     */
    protected function mock_rest_request(array $request_data = []): object {
        $request = $this->getMockBuilder('WP_REST_Request')
            ->disableOriginalConstructor()
            ->getMock();

        foreach ($request_data as $key => $value) {
            $request->method('get_param')
                ->with($key)
                ->willReturn($value);
        }

        return $request;
    }

    /**
     * Mock WordPress dependencies (scripts/styles)
     *
     * @param string $handle Asset handle
     * @param array  $deps   Dependencies array
     */
    protected function mock_dependencies(string $handle, array $deps = []): void {
        $this->mock_function('wp_register_script', true);
        $this->mock_function('wp_register_style', true);
        $this->mock_function('wp_enqueue_script', true);
        $this->mock_function('wp_enqueue_style', true);
        $this->mock_function('wp_scripts', (object)['registered' => []]);
        $this->mock_function('wp_styles', (object)['registered' => []]);
    }

    /**
     * Mock WordPress admin notices
     */
    protected function mock_admin_notices(): void {
        $this->mock_function('add_settings_error', true);
        $this->mock_function('settings_errors', []);
        $this->mock_function('admin_url', 'http://example.com/wp-admin/');
    }

    /**
     * Mock WordPress database operations
     *
     * @param mixed $result Result to return from database operations
     */
    protected function mock_wpdb($result = true): void {
        global $wpdb;
        $wpdb = $this->getMockBuilder('wpdb')
            ->disableOriginalConstructor()
            ->getMock();
        $wpdb->method('prepare')->willReturn('prepared_query');
        $wpdb->method('query')->willReturn($result);
        $wpdb->prefix = 'wp_';
    }

    /**
     * Mock WordPress plugin functions
     *
     * @param string $plugin_file Main plugin file path
     */
    protected function mock_plugin_functions(string $plugin_file): void {
        $this->mock_function('plugin_basename', $plugin_file);
        $this->mock_function('plugins_url', 'http://example.com/wp-content/plugins');
        $this->mock_function('plugin_dir_path', dirname($plugin_file) . '/');
        $this->mock_function('plugin_dir_url', 'http://example.com/wp-content/plugins/' . basename(dirname($plugin_file)) . '/');
    }

}
