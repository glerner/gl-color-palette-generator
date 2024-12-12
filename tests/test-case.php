<?php
namespace GL_Color_Palette_Generator\Tests;

use WP_Mock\Tools\TestCase as WP_Mock_TestCase;
use Yoast\PHPUnitPolyfills\TestCases\TestCase as Polyfill_TestCase;

/**
 * Base test case class for all plugin tests
 */
abstract class TestCase extends WP_Mock_TestCase {
    use \Yoast\PHPUnitPolyfills\Polyfills\AssertIsType;
    use \Yoast\PHPUnitPolyfills\Polyfills\AssertStringContains;

    /**
     * Set up test environment
     */
    protected function set_up() {
        parent::set_up();
        \WP_Mock::setUp();
    }

    /**
     * Tear down test environment
     */
    protected function tear_down() {
        \WP_Mock::tearDown();
        parent::tear_down();
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object $object     Instantiated object that we will run method on.
     * @param string $method_name Method name to call.
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    protected function invoke_method( $object, $method_name, array $parameters = [] ) {
        $reflection = new \ReflectionClass( get_class( $object ) );
        $method = $reflection->getMethod( $method_name );
        $method->setAccessible( true );

        return $method->invokeArgs( $object, $parameters );
    }

    /**
     * Get protected/private property of a class.
     *
     * @param object $object      Instantiated object that we will get property from.
     * @param string $property_name Property to get.
     *
     * @return mixed Property value.
     */
    protected function get_private_property( $object, $property_name ) {
        $reflection = new \ReflectionClass( get_class( $object ) );
        $property = $reflection->getProperty( $property_name );
        $property->setAccessible( true );

        return $property->getValue( $object );
    }
}
