<?php
/**
 * Test Autoloader Class
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 */

namespace GL_Color_Palette_Generator\Tests\System;

use GL_Color_Palette_Generator\System\Autoloader;
use PHPUnit\Framework\TestCase;
use Mockery;

/**
 * Class Test_Autoloader
 */
class Test_Autoloader extends TestCase {
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
    protected function setUp(): void {
        parent::setUp();
        $this->autoloader = new Autoloader();
    }

    /**
     * Teardown test environment
     */
    protected function tearDown(): void {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test autoloader registration
     */
    public function test_register() {
        Autoloader::register();

        $registered = spl_autoload_functions();
        $found = false;

        foreach ($registered as $loader) {
            if (is_array($loader) && $loader[0] instanceof Autoloader) {
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

        / Create a mock file
        $file_path = GL_CPG_PLUGIN_DIR . 'includes/core/class-setup.php';
        $dir_path = dirname($file_path);

        if (!is_dir($dir_path)) {
            mkdir($dir_path, 0777, true);
        }

        file_put_contents($file_path, '<?php class Setup {}');

        $this->autoloader->autoload($class_name);

        $this->assertTrue(class_exists($class_name, false));

        / Cleanup
        unlink($file_path);
        rmdir($dir_path);
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
