<?php
/**
 * Tests for Logger class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests
 */

namespace GL_Color_Palette_Generator\Tests\Core;

use PHPUnit\Framework\TestCase;
use GL_Color_Palette_Generator\Core\Logger;

/**
 * Class Test_Logger
 *
 * @coversDefaultClass \GL_Color_Palette_Generator\Core\Logger
 */
class Test_Logger extends TestCase {
    /**
     * Test log file path
     *
     * @var string
     */
    private $test_log_file;

    /**
     * Logger instance
     *
     * @var Logger
     */
    private $logger;

    /**
     * Set up test environment
     */
    public function setUp(): void {
        parent::setUp();
        $this->test_log_file = GL_CPG_PLUGIN_DIR . 'logs/test.log';
        $this->logger = new Logger($this->test_log_file);
    }

    /**
     * Clean up test environment
     */
    public function tearDown(): void {
        if (file_exists($this->test_log_file)) {
            unlink($this->test_log_file);
        }
        parent::tearDown();
    }

    /**
     * Test log file creation
     *
     * @covers ::__construct
     * @covers ::ensure_log_directory
     */
    public function test_log_file_creation() {
        $this->assertFileExists(dirname($this->test_log_file));
    }

    /**
     * Test logging levels
     *
     * @covers ::log
     * @covers ::emergency
     * @covers ::alert
     * @covers ::critical
     * @covers ::error
     * @covers ::warning
     * @covers ::notice
     * @covers ::info
     * @covers ::debug
     */
    public function test_logging_levels() {
        $methods = [
            'emergency',
            'alert',
            'critical',
            'error',
            'warning',
            'notice',
            'info',
            'debug'
        ];

        foreach ($methods as $method) {
            $message = "Test {$method} message";
            $result = $this->logger->$method($message);
            $this->assertTrue($result);
            
            $log_content = file_get_contents($this->test_log_file);
            $this->assertStringContainsString(strtoupper($method), $log_content);
            $this->assertStringContainsString($message, $log_content);
        }
    }

    /**
     * Test context interpolation
     *
     * @covers ::interpolate
     * @covers ::format_message
     */
    public function test_context_interpolation() {
        $message = "User {username} performed {action}";
        $context = [
            'username' => 'john_doe',
            'action' => 'login'
        ];

        $this->logger->info($message, $context);
        
        $log_content = file_get_contents($this->test_log_file);
        $this->assertStringContainsString('john_doe', $log_content);
        $this->assertStringContainsString('login', $log_content);
    }

    /**
     * Test minimum log level filtering
     *
     * @covers ::log
     */
    public function test_minimum_log_level() {
        $error_logger = new Logger($this->test_log_file, 'error');
        
        // This should be logged
        $error_logger->error('Test error');
        
        // This should not be logged
        $error_logger->info('Test info');
        
        $log_content = file_get_contents($this->test_log_file);
        $this->assertStringContainsString('Test error', $log_content);
        $this->assertStringNotContainsString('Test info', $log_content);
    }
}
