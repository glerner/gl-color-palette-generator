<?php
/**
 * Tests for Autoloader class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\System
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\System;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\System\Autoloader;
use Mockery;

/**
 * Tests for the Autoloader class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\System
 */
class Test_Autoloader extends Unit_Test_Case {
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * Autoloader instance
     *
     * @var Autoloader
     */
    private $autoloader;

    /**
     * Setup test environment
     */
    public function setUp(): void {
        if (class_exists('Composer\Autoload\ClassLoader')) {
            $this->markTestSkipped('Autoloader tests are skipped when using Composer autoloader');
        }
        parent::setUp();
        $this->autoloader = new Autoloader();
    }

    /**
     * Teardown test environment
     */
    public function tearDown(): void {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test autoloader registration
     */
    public function test_register() {

        // Store existing autoloaders
        $existing_autoloaders = spl_autoload_functions() ?: [];
        $initial_count = count($existing_autoloaders);

        // Register our autoloader
        Autoloader::register();

        // Get new autoloaders
        $new_autoloaders = spl_autoload_functions() ?: [];
        $new_count = count($new_autoloaders);

        // Assert that a new autoloader was added
        $this->assertEquals($initial_count + 1, $new_count, 'Autoloader was not added to the autoload stack');

        // Find and remove our autoloader
        $found = false;
        foreach ($new_autoloaders as $loader) {
            if (is_array($loader) &&
                isset($loader[0]) &&
                get_class($loader[0]) === Autoloader::class) {
                spl_autoload_unregister($loader);
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Autoloader was not properly registered');
    }

    /**
     * Test class autoloading with valid namespace
     */
    public function test_autoload_valid_namespace() {
        $class_name = 'GL_Color_Palette_Generator\Core\Setup';

        // Create a mock file
        $file_path = GL_CPG_PLUGIN_DIR . 'includes/core/class-setup.php';
        $dir_path = dirname($file_path);

        if (!is_dir($dir_path)) {
            mkdir($dir_path, 0777, true);
        }

        // Create a test class file
        $class_content = '<?php
        namespace GL_Color_Palette_Generator\Core;
        class Setup {
            public static function test_method() {
                return true;
            }
        }';

        file_put_contents($file_path, $class_content);

        try {
            // Test the autoloader
            $result = $this->autoloader->autoload($class_name);

            // Verify file was included
            $this->assertTrue(file_exists($file_path));
            $this->assertFileIsReadable($file_path);

            // Clean up
            unlink($file_path);
            rmdir($dir_path);

            // Assert autoloader returned true
            $this->assertTrue($result);
        } catch (\Exception $e) {
            // Clean up even if test fails
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            if (is_dir($dir_path)) {
                rmdir($dir_path);
            }
            throw $e;
        }
    }

    /**
     * Test class autoloading with invalid namespace
     */
    public function test_autoload_invalid_namespace() {
        $class_name = 'Invalid\Namespace\Class';

        $result = $this->autoloader->autoload($class_name);

        $this->assertNull($result);
    }

    /**
     * Test file path generation
     */
    public function test_get_file_path() {
        $class_name = 'GL_Color_Palette_Generator\Core\Setup';

        $expected = GL_CPG_PLUGIN_DIR . 'includes/core/class-setup.php';

        $reflection = new \ReflectionClass($this->autoloader);
        $method = $reflection->getMethod('get_file_path');
        $method->setAccessible(true);

        $result = $method->invoke($this->autoloader, $class_name);

        $this->assertEquals($expected, $result);
    }
}
