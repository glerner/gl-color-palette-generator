<?php
/**
 * Base test case class for integration tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 * @bootstrap wp
 */

namespace GL_Color_Palette_Generator\Tests\Integration;

use GL_Color_Palette_Generator\Tests\Unit\Test_Case;

/**
 * Base test case class for integration tests
 */
abstract class Test_Case_Integration extends Test_Case {
    /**
     * Set up test environment
     */
    public function setUp(): void {
        parent::setUp();

        // Additional integration test setup
        $this->init_integration_environment();
    }

    /**
     * Tear down test environment
     */
    public function tearDown(): void {
        // Clean up integration test environment
        $this->cleanup_integration_environment();

        parent::tearDown();
    }

    /**
     * Initialize integration test environment
     */
    protected function init_integration_environment() {
        // Set up WordPress test environment
        if (!defined('ABSPATH')) {
            define('ABSPATH', '/app/wp-content/');
        }

        // Initialize WordPress globals
        global $wpdb, $current_site, $current_blog, $wp_rewrite, $shortcode_tags, $wp, $wp_filter;

        // Set up admin environment if needed
        if (defined('WP_ADMIN') && WP_ADMIN) {
            set_current_screen('dashboard');
        }
    }

    /**
     * Clean up integration test environment
     */
    protected function cleanup_integration_environment() {
        // Reset WordPress globals
        global $wpdb;
        $wpdb->query('ROLLBACK');
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object $object     Instantiated object that we will run method on.
     * @param string $methodName Method name to call.
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    protected function invoke_method(&$object, $methodName, array $parameters = array()) {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Assert that a WordPress action is registered
     *
     * @param string $tag Action tag
     * @param string $function_to_check Function name or class method
     * @param int    $priority Priority (optional)
     */
    protected function assertActionRegistered($tag, $function_to_check, $priority = 10) {
        global $wp_filter;

        $this->assertArrayHasKey(
            $tag,
            $wp_filter,
            "Action '$tag' is not registered"
        );

        $action_found = false;
        if (isset($wp_filter[$tag][$priority])) {
            foreach ($wp_filter[$tag][$priority] as $function) {
                if (is_array($function_to_check)) {
                    // Check class method
                    if (is_array($function['function']) &&
                        get_class($function['function'][0]) === get_class($function_to_check[0]) &&
                        $function['function'][1] === $function_to_check[1]) {
                        $action_found = true;
                        break;
                    }
                } else {
                    // Check function name
                    if ($function['function'] === $function_to_check) {
                        $action_found = true;
                        break;
                    }
                }
            }
        }

        $this->assertTrue(
            $action_found,
            sprintf(
                "Action '%s' with function '%s' at priority %d is not registered",
                $tag,
                is_array($function_to_check) ? get_class($function_to_check[0]) . '::' . $function_to_check[1] : $function_to_check,
                $priority
            )
        );
    }

    /**
     * Assert that a WordPress filter is registered
     *
     * @param string $tag Filter tag
     * @param string $function_to_check Function name or class method
     * @param int    $priority Priority (optional)
     */
    protected function assertFilterRegistered($tag, $function_to_check, $priority = 10) {
        global $wp_filter;

        $this->assertArrayHasKey(
            $tag,
            $wp_filter,
            "Filter '$tag' is not registered"
        );

        $filter_found = false;
        if (isset($wp_filter[$tag][$priority])) {
            foreach ($wp_filter[$tag][$priority] as $function) {
                if (is_array($function_to_check)) {
                    // Check class method
                    if (is_array($function['function']) &&
                        get_class($function['function'][0]) === get_class($function_to_check[0]) &&
                        $function['function'][1] === $function_to_check[1]) {
                        $filter_found = true;
                        break;
                    }
                } else {
                    // Check function name
                    if ($function['function'] === $function_to_check) {
                        $filter_found = true;
                        break;
                    }
                }
            }
        }

        $this->assertTrue(
            $filter_found,
            sprintf(
                "Filter '%s' with function '%s' at priority %d is not registered",
                $tag,
                is_array($function_to_check) ? get_class($function_to_check[0]) . '::' . $function_to_check[1] : $function_to_check,
                $priority
            )
        );
    }
}
